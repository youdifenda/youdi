<?php

/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/9
 * Time: 15:17
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{


    public function _initConfig(){
        $arrConfig = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config',$arrConfig);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        /**
         * 添加配置中的路由
         */
//        $arr = Yaf_Registry::get('config')->routes;
//        echo '<pre>';
//        var_dump($arr);
        $router->addConfig(Yaf_Registry::get('config')->routes);

//        $route = new Yaf_Route_Simple("m", "c", "a");
//        $router->addRoute("name", $route);


//        Yaf_Dispatcher::getInstance()->getRouter()->addRoute(
//            "paging_example",
//            new Yaf_Route_Regex(
//                "#^/user/([0-9]+)#",
//   array('controller' => 'user',
//       'action' => 'show'),
//   array(1 => 'ident'))
//        );
    }
}