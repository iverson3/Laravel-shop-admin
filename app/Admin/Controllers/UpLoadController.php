<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic;


// 文档地址  http://image.intervention.io/getting_started/configuration

class UpLoadController extends Controller
{
    use ModelForm;

    /**
     * Storage instance.
     *
     * @var string
     */
    protected $storage = '';
    protected $preUrl = '';

    protected $useUniqueName = false;

    /**
     * File name.
     *
     * @var null
     */
    protected $name = null;

    /**
     * Upload directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * 针对editor.md所写的图片上传控制器
     *
     * @param  Request $requst
     * @return Response
     */
    public function postUploadPicture(Request $request)
    {
        if ($request->hasFile('wang-editor-image-file')) {
            //
            $file = $request->file('wang-editor-image-file');
            $data = $request->all();
            $rules = [
                'wang-editor-image-file'    => 'max:5120',
            ];
            $messages = [
                'wang-editor-image-file.max'    => '文件过大,文件大小不得超出5MB',
            ];
            
            $validator = Validator($data, $rules, $messages);
//            $validator = $this->validate($data, $rules, $messages);

            $res = 'error|失败原因为：非法传参';
            if ($validator->passes()) {
                $realPath = $file->getRealPath();
                $destPath = 'uploads/content/';
                $savePath = $destPath.''.date('Y', time());
                is_dir($savePath) || mkdir($savePath);  //如果不存在则创建年目录
                $savePath = $savePath.'/'.date('md', time());
                is_dir($savePath) || mkdir($savePath);  //如果不存在则创建月日目录
                $name = $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();
                $check_ext = in_array($ext, ['gif', 'jpg', 'jpeg', 'png'], true);
                if ($check_ext) {
                    $uniqid = uniqid().'_'.date('s');
                    $oFile = $uniqid.'o.'.$ext;
                    $fullfilename = '/'.$savePath.'/'.$oFile;  //原始完整路径
                    if ($file->isValid()) {
                        $uploadSuccess = $file->move($savePath, $oFile);  //移动文件
                        $oFilePath = $savePath.'/'.$oFile;
                        $res = $fullfilename;
                    } else {
                        $res = 'error|失败原因为：文件校验失败';
                    }
                } else {
                    $res = 'error|失败原因为：文件类型不允许,请上传常规的图片(gif、jpg、jpeg与png)文件';
                }
            } else {
                $res = 'error|'.$validator->messages()->first();
            }
        }
        return $res;
    }

    public function postUploadImg(Request $request){
        if ($request->hasFile('wang-editor-image-file')) {
            //
            $file = $request->file('wang-editor-image-file');
            $data = $request->all();
            $rules = [
                'wang-editor-image-file'    => 'max:5120',
            ];
            $messages = [
                'wang-editor-image-file.max'    => '文件过大,文件大小不得超出5MB',
            ];

            $validator = Validator($data, $rules, $messages);
//            $validator = $this->validate($data, $rules, $messages);

            $res = 'error|失败原因为：非法传参';
            if ($validator->passes()) {


                $ext = $file->getClientOriginalExtension();
                $check_ext = in_array($ext, ['gif', 'jpg', 'jpeg', 'png'], true);
                if ($check_ext) {

                    $this->disk(config('admin.upload.disk'));

                    $this->directory = 'content/'.date('Y', time()).'/'.date('md', time())."/";
                    $this->name = $this->getStoreName($file);

                    $this->renameIfExists($file);

                    $target = $this->directory.$this->name;

                    $this->storage->put($target, file_get_contents($file->getRealPath()));
                    $this->storage->makeDirectory($this->directory.'/s300/');

                    $localPath = $this->storage->getDriver()->getAdapter()-> getPathPrefix();

                    //--------------宽度过大-------------------
                    $image = ImageManagerStatic::make($localPath.$target);
                    if($image->width()>600){
                        $image->resize(600, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }

                    //--------------添加水印-------------------
                    $image->insert(public_path('/img/logo.png'), 'bottom-right', 15, 10);
                    $namearr = explode('.', $this->name);
                    $image->save($localPath.$this->directory.$namearr[0].'_logo.'.$namearr[1]);

                    //-------------缩略图----------------------
                    if($image->width()>$image->height()){
                        $image->resize(300, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });

                    }else{
                        $image->resize(null, 300, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }

                    $image->save($localPath.$this->directory.'/s300/'.$namearr[0].'_logo.'.$namearr[1]);


                    if ($file->isValid()) {
//                        $res = $this->objectUrl($target);
                        $res = $this->objectUrl($this->directory.$namearr[0].'_logo.'.$namearr[1]);
                    } else {
                        $res = 'error|失败原因为：文件校验失败';
                    }

                } else {
                    $res = 'error|失败原因为：文件类型不允许,请上传常规的图片(gif、jpg、jpeg与png)文件';
                }
            } else {
                $res = 'error|'.$validator->messages()->first();
            }
        }
        return $res;
    }

    public function disk($disk)
    {
        $this->storage = Storage::disk($disk);

        return $this;
    }

    public function renameIfExists(UploadedFile $file)
    {
        if ($this->storage->exists("$this->directory/$this->name")) {
            $this->name = $this->generateUniqueName($file);
        }
    }

    /**
     * Get store name of upload file.
     *
     * @param UploadedFile $file
     *
     * @return string
     */
    protected function getStoreName(UploadedFile $file)
    {
        if ($this->useUniqueName) {
            return $this->generateUniqueName($file);
        }

        if (is_callable($this->name)) {
            $callback = $this->name->bindTo($this);

            return call_user_func($callback, $file);
        }

        if (is_string($this->name)) {
            return $this->name;
        }

        return $file->getClientOriginalName();
    }


    public function objectUrl($path)
    {

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if($this->preUrl == ''){
            $url = config('admin.upload.host');
        }else{
            if(count(explode($this->preUrl,$path))>1){
                $url = config('admin.upload.host');
            }else{
                $url = config('admin.upload.host').$this->preUrl;
            }

        }

        return rtrim($url, '/').'/'.trim($path, '/');
    }

    protected function generateUniqueName(UploadedFile $file)
    {
        return md5(uniqid()).'.'.$file->guessExtension();
    }

}