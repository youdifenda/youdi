<?php
/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/7
 * Time: 10:35
 */
header("Content-Type: text/html; charset=utf-8");
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app
    ->bootstrap()
    ->run();



