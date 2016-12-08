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
}