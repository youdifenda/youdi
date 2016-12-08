<?php
/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/14
 * Time: 15:58
 */
define('ROOT', dirname(__FILE__) . '/');

class FileController extends Yaf_Controller_Abstract {
    public function getFileAction(){

        $request = $this->getRequest();
        $fileName = $request->getParam('fileName');

//        var_dump($fileName);

        $dir = ROOT . 'upload/';  //要获取的目录
        $filePath = $dir . $fileName;
//        header("Content-type:image/jpeg");
//////        $img = imagecreatefromjpeg($filePath);
//////        imagejpeg($img);
//////        imagedestroy($img);
////
        readfile($filePath);


        return false;
    }
}