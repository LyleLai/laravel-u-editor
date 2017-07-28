<?php namespace Stevenyangecho\UEditor\Uploader;

use \Qiniu\Storage\UploadManager;
use \Qiniu\Auth;

/**
 *
 *
 * trait UploadQiniu
 *
 * 七牛 上传 类
 *
 * @package Stevenyangecho\UEditor\Uploader
 */
trait UploadQiniu
{
    /**
     * 获取文件路径
     * @return string
     */
    protected function getFilePath()
    {
        $fullName = $this->fullName;


        $fullName = ltrim($fullName, '/');


        return $fullName;
    }

    public function uploadQiniu($key, $content)
    {
        $upManager = new UploadManager();
        $auth = new Auth(config('filesystems.disks.qiniu.access_key'), config('filesystems.disks.qiniu.secret_key'));
        // $token = $auth->uploadToken(config('filesystems.disks.qiniu.bucket'));  // original
        $createTime = date('Y-m-d H:i:s');
        $policy = array(
            'callbackUrl' => env('QINIU_STORAGE_UPLOAD_NOTIFY', 'https://api.guanjiamiao.com/api/qiniu/upcallback'),
            'callbackBody' => '{"bucket":"$(bucket)", "etag":"$(etag)", "fname":"$(fname)", "fsize":"$(fsize)", "mimeType":"$(mimeType)", "endUser":"$(endUser)", "fkey":"$(fkey)", "description":"UEditor", "uid":"-1", "createTime":"' . $createTime .'"}'
        );  // Bavon added
        $token = $auth->uploadToken(config('filesystems.disks.qiniu.bucket'), null, 3600, $policy);    // Bavon revised
        $key = null;  // 这样才会使用七牛的hash后的文件名

        list($ret, $error) = $upManager->put($token, $key, $content);
        if ($error) {
            $this->stateInfo= $error->message();
        } else {
            //change $this->fullName ,return the url
            $url=rtrim(strtolower(config('filesystems.disks.qiniu.domain')),'/');
            $fullName = ltrim($this->fullName, '/');
            //$this->fullName=$url.'/'.$fullName;   // original
            $this->fullName=$url.'/'.$ret['hash'];  // Bavon revised
            $this->stateInfo = $this->stateMap[0];
        }
        return true;
    }
}