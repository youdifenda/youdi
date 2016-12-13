<?php
/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/8
 * Time: 12:16
 */

class IndexController extends Yaf_Controller_Abstract {
    public function indexAction() {//默认Action
        $page = $this->getRequest();
        // var_dump($page);
        echo 'this is page :'.$page->getParam('page').'<br>';
        $this->getView()->assign("content", "Hello World");
    }


    public function secondAction(){
        echo 'this is second Action <br>';
        return false;
    }

    public function showAction(){
        echo 'this is Index Module showAction <br>';
        return false;
    }
    //支付测试
    public function ceshiAction(){
        echo 'this is Index Module ceshiAction <br>';

    }
    public function zfcgAction(){
        $json = UserController::baseJson();
        $json['code'] = 0;
        $json['message'] = 'success';
        $json['data'] = 0;
        $json = json_encode($json);
        echo  $json;
        return false;
    }
}