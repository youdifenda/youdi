<?php

/**
 * Created by PhpStorm.
 * User: len
 * Date: 2016/11/29
 * Time: 13:44
 */
class PayController extends Yaf_Controller_Abstract
{


    function orderAction()
    {
        $request = $this->getRequest();
        $ut = UserController::isLogin();
        if ($ut == -1) {
            $json = UserController::baseJson();
            $json['code'] = 0;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        }
        //生成订单有两种类型
        //一种是提问订单，一种是旁听订单
        //$type = $request->getPost('type');
        //$id = $request->getPost('id');
        //支付平台  0，微信支付  1，支付宝  2，银联支付
        $platform = $request->getPost('platform');
        //订单总金额
        $amount = $request->getPost('amount');

        $dbHelp = DbHelp::getInstance();
        $table = 'order';
        $order = $dbHelp->dispense($table);
        $order->status = 0;
        $order->ut = $ut;
        //支付方式的平台
        $order->pay_platform = $platform;
        //订单总金额
        $order->total_amount = $amount;

        $order->create_time = date('Y-m-d H:i:s');
        //生成订单号
        $order->order_sn = $this->get_order_sn();


        try{
            R::begin();
            $result = $dbHelp->store($order);
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($ut);
            $user = $dbHelp->findOne($table,$sql,$value);
            $user->setAttr('balance',$user->getProperties()['balance'] + $amount);
            $dbHelp->store($user);

            R::commit();
            $order = $order->getProperties();
            $json = UserController::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $order;
            $json = json_encode($json);
            echo  $json;
            return false;
        }
        catch (Exception $e){
            R::rollback();
            $json = UserController::baseJson();
            $json['code'] = -1;
            $json['message'] = '充值失败';
            $json = json_encode($json);
            echo  $json;
            return false;
        }


    }


    function get_order_sn()
    {
        $rand24 = mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999);
        $rand8 = substr($rand24, mt_rand(0, 16), 8);
        return date('ymd') . str_pad($rand8, 8, '0', STR_PAD_LEFT);
    }

}