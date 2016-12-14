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


    public function request($url,$https=true,$method='get',$data=null){
    //1初始化CURL
        $ch = curl_init($url);
        //设置相关参数
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        //判断是否位HTTPS请求
        if ($https === true) {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        }
        //判断传输方式是否是POST
        if ($method=='post') {
            curl_setopt($ch,CURLOPT_PORT,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
                
        }
        //发送请求
        $str = curl_exec($ch);
        //关闭连接
        curl_close($ch);
        //返回请求结果
        return $str;


    }

    function http_post_data($url, $data_string) 
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }




    public function payAction()
    {
        echo 'this is pay';
        $this->getView();
    }
    public function payOkAction()
    {
        $view = $this->getView();
        $request = $this->getRequest();
        $productFee=$request->getPost('productFee');
        $productId=$request->getPost('productId');
        $productDesc=$request->getPost('productDesc');
        $spbillCreateIp=QuestionController::getIP();
        // file_put_contents('d:a.txt',$spbillCreateIp);
          //1.url地址
          //模拟支付
        $url='http://www.tp.com';
          // $url = 'http://192.168.2.187:8080/payment/create/wx/payurl';
          //2.判断请求方式
        $data=array();
        $data['systemId']="02";//
        $data['channelId']="01";
        $data['productDesc']=$productDesc;
        $data['productFee']=$productFee;
        $data['productId']=$productId;
        $data['spbillCreateIp']=$spbillCreateIp;
        $data=json_encode($data);

          // file_put_contents('d:b.txt',$data);exit;
          //3.发送请求 
        // $content=$this->http_post_data($url,$data);
          // $content = $this->request($url,false);
          //4.处理数据返回值
        // $content;
          //返回数据为json格式
          // echo '<pre>';
          // print_r($content);
          // echo '</pre>';
          // echo $payurl = $content['1'];
        $data='www.youdi.com/apk/ceshi.html';
        $json = UserController::baseJson();
        $json['code'] = 0;
        $json['message'] = 'success';
        $json['data'] = $data;
        $json = json_encode($json);
        // file_put_contents('d:a.txt',$json);
        echo $json;

          // echo $content;


         //  if(empty($content)){
         //        $json = UserController::baseJson();
         //        $json['code'] = -1;
         //        $json['message'] = '支付失败';
         //        $json = json_encode($json);
         //        echo $json;
         //        return false;
         // }else{
         //    $json = UserController::baseJson();
         //        $json['code'] = 0;
         //        $json['message'] = 'success';
         //        $json['data'] = $content['1'];
         //        echo $json;
         //        file_put_contents("d:a.txt",$json);
         //    return false;
         // }
    }
    function payjieguoAction(){
            //处理数据生成订单插入三个表
            //content 文字 type 类型，是哪种问题，
            //basePrice 基础价格  addPrice 增加价格
            //listenPrice 听一次的价格
            //totalPrice 总计价格
            //userId 向谁提交问题
            //
            //
            $session=Yaf_Session::getInstance();
            //"basePrice": basePrice, "addPrice": addPrice
            //, "content": content, "answerut": answerut
            //将总价 问题  答题人  存入SESSION；
            $totalPrice = $session->get("amount");
            $content = $session->get("content");
            $answerut =  $session->get("answerut");
            $basePrice =  $session->get("basePrice");
            $addPrice =  $session->get("addPrice");
            $type =  $session->get("type");
            //取出SESSION;
            // $a=$session->get('answerut');
            // file_put_contents("d:a.txt",$a);

            // $request = $this->getRequest();
            // $type = $request->getPost('type');
            // $content = $request->getPost('content', "");//提问内容
            // $basePrice = $request->getPost('basePrice', "0");//获取提问价格
            // $addPrice = $request->getPost('addPrice', "0");//追加价格
    //        $listenPrice = $request->getPost('listenPrice');
            $listenPrice = 1;//听一次价格
            // $answerut = $request->getPost('answerut');//获取回答问题的人
            // $totalPrice = $basePrice + $addPrice;//获的总价
            date_default_timezone_set('PRC');// 设定用于一个脚本中所有日期时间函数的默认时区
            $createTime = date('Y-m-d H:i:s');

            $ut = UserController::isLogin();//登陆者

            //已经登录
            if ($ut != -1) {
                if($ut == $answerut){
                    $json = UserController::baseJson();
                    $json['code'] = 0;
                    $json['message'] = '不能向自己提问';
                    $json = json_encode($json);
                    echo  $json;
                    return false;
                }

                $dbHelp = DbHelp::getInstance();
                $table = 'question';
                $question = $dbHelp->dispense($table);//创建或更新表
                $question->ut = $ut;
                $question->type = $type;
                $question->baseprice = $basePrice;
                $question->addprice = $addPrice;
                $question->listenprice = $listenPrice;
                $question->totalprice = $totalPrice;
                $question->content = $content;
                $question->answerut = $answerut;
                $question->createtime = $createTime;

                $table = 'message';//消息表
                try {
                    R::begin();//开始一个事务 同时操作QUESTON / MESSAGE  /BILL 三个表
                    $dbHelp->store($question);//问题表插入数据

                    $message = $dbHelp->dispense($table);
                    $message->sendut = $ut;
                    $message->acceptedut = $answerut;
                    $message->content = $content;
                    $message->type = 1;
                    $dbHelp->store($message);//消息表插入数据

                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($ut);
                    $user = $dbHelp->findOne($table, $sql, $value);
                    $user->setAttr('balance', $user->getProperties()['balance'] - $totalPrice);
                    $dbHelp->store($user);

                    $table = 'bill';//账单表
                    $bill = $dbHelp->dispense($table);
                    $bill->status = 0;
                    $bill->ut = $ut;
                    $bill->type = 0;
                    $bill->amount = $totalPrice;
                    $bill->ip = QuestionController::getIP();    
                    $bill->remarks = $user['user_name'] . '提出了问题，支付了' . $totalPrice . '元钱';
                    $bill->create_time = date('Y-m-d H:i:s');
                    $dbHelp->store($bill);//账单表插入数据

                    R::commit();
                    $json = UserController::baseJson();
                    $json['code'] = 0;
                    $json['message'] = 'success';
                    $json = json_encode($json);
                    //支付成功问题插入成功
                    // echo $json;
                    unset($session->amount);
                    unset($session->content);
                    unset($session->answerut);
                    unset($session->basePrice);
                    unset($session->addPrice);
                    unset($session->type);
                    header("Location:http://www.youdi.com/apk/user/question.html");
                    return false;
                } catch (Exception $e) {
                    R::rollback();
                    $json = UserController::baseJson();
                    $json['code'] = -1;
                    $json['message'] = '提问失败';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                }

            } //还未登录
            else {
                $json = UserController::baseJson();
                $json['code'] = -1;
                $json['message'] = '未登录';
                $json = json_encode($json);
                echo $json;
                return false;
            }
    }
    //听支付
    public function listenpayAction(){
        echo 'this is listenpay';
        $this->getView();
    }
    //模拟支付
    public function listenpayOkAction(){
        $view = $this->getView();
        $request = $this->getRequest(); 
        $questionId=$request->getPost('questionId');
        $answerId=$request->getPost('answerId');
        //数据存入session
        $session=Yaf_Session::getInstance();
        //"basePrice": basePrice, "addPrice": addPrice
        //, "content": content, "answerut": answerut
        //将总价 问题  答题人  存入SESSION；
        $session->set("questionId", $questionId);
        $session->set("answerId", $answerId);

        $spbillCreateIp=QuestionController::getIP();

        $productFee=1;
          //1.url地址
          //模拟支付
          // $url = 'http://192.168.2.187:8080/payment/create/wx/payurl';
          //2.判断请求方式
        $data=array();
        $data['systemId']="02";//
        $data['channelId']="02";
        // $data['productDesc']=$productDesc;
        $data['productFee']=$productFee;
        // $data['productId']=$productId;
        $data['spbillCreateIp']=$spbillCreateIp;
        $data=json_encode($data);

          // file_put_contents('d:b.txt',$data);exit;
          //3.发送请求 
        // $content=$this->http_post_data($url,$data);
          // $content = $this->request($url,false);
          //4.处理数据返回值
        // $content;
          //返回数据为json格式
        $data='www.youdi.com/apk/ceshi.html';
        $json = UserController::baseJson();
        $json['code'] = 0;
        $json['message'] = 'success';
        $json['data'] = $data;
        $json = json_encode($json);
        echo $json;
        // $this->getView();
    }


    function listenpayjieguoAction(){
            //处理数据生成订单插入三个表
            $session=Yaf_Session::getInstance();
            //将总价 问题  答题人  存入SESSION；
            $answerId =  $session->get("answerId");
            $questionId =  $session->get("questionId");
            $answer =  QuestionController::getAnswerOne($questionId);
            $answerut=$answer['answerUT'];

            //取出SESSION;
            // $a=$session->get('answerut');
            // file_put_contents("d:a.txt",$a);
    //        $listenPrice = $request->getPost('listenPrice');
            $listenPrice = 1;//听一次价格
            $productFee = 1;
            date_default_timezone_set('PRC');// 设定用于一个脚本中所有日期时间函数的默认时区
            $createTime = date('Y-m-d H:i:s');

            $ut = UserController::isLogin();//登陆者
            // file_put_contents("d:a.txt",$ut);

            //已经登录
            if ($ut != -1) {
                if($ut == $answerut){
                    $json = UserController::baseJson();
                    $json['code'] = 0;
                    $json['message'] = '不能支付听自己的';
                    $json = json_encode($json);
                    echo  $json;
                    return false;
                }

                $dbHelp = DbHelp::getInstance();
                $table = 'listen';
                $listen = $dbHelp->dispense($table);//创建或更新表
                $listen->ut = $ut;
                $listen->answerid = $answerId;
                $listen->questionid = $questionId;
                $listen->listenprice = $listenPrice;
                $listen->createTime = $createTime;
                try {
                    R::begin();//开始一个事务 同时操作listen / MESSAGE  /BILL 三个表
                    // file_put_contents("d:a.txt",4);
                    $dbHelp->store($listen);//liaten表插入数据
                    $table = 'message';//消息表
                    $message = $dbHelp->dispense($table);
                    $message->sendut = $ut;
                    $message->acceptedut = $answerut;
                    $message->content = "您成功偷听了1次";
                    $message->type = 4;
                    $dbHelp->store($message);//消息表插入数据
                    // file_put_contents("d:a.txt",1);
                    //修改question表
                    $table = 'question';
                    // $question = $dbHelp->dispense($table);//创建或更新表
                    $sql = 'id = ? ';
                    $value = array($questionId);
                    $question = $dbHelp->findOne($table, $sql, $value);
                    $listennum=$question->getProperties()['listennum'] + 1;
                    //将旁听字段+1;
                    $question->setAttr('listennum', $listennum);
                    $dbHelp->store($question);

                    $table = 'answer';
                    $sql = 'id = ? ';
                    $value = array($answerId);
                    $answer = $dbHelp->findOne($table, $sql, $value);
                    $listennum=$answer->getProperties()['listennum'] + 1;
                    //将旁听字段+1;
                    $answer->setAttr('listennum', $listennum);
                    $dbHelp->store($answer);

                    $table = 'bill';//账单表
                    $bill = $dbHelp->dispense($table);
                    $bill->status = 0;
                    $bill->ut = $ut;
                    $bill->type = 0;
                    $bill->amount = $productFee;
                    $bill->ip = QuestionController::getIP();    
                    $bill->remarks = $user['user_name'] . '偷听了问题，支付了' . $productFee . '元钱';
                    $bill->create_time = date('Y-m-d H:i:s');
                    $dbHelp->store($bill);//账单表插入数据

                    R::commit();
                    $json = UserController::baseJson();
                    $json['code'] = 0;
                    $json['message'] = 'success';
                    $json = json_encode($json);
                    //支付成功偷听成功成功
                    // echo $json;
                    unset($session->answerId);
                    unset($session->questionId);
                    header("Location:http://www.youdi.com/apk/recent.html");
                    return false;
                } catch (Exception $e) {
                    R::rollback();
                    $json = UserController::baseJson();
                    $json['code'] = -1;
                    $json['message'] = '偷听失败';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                }

            } //还未登录
            else {
                $json = UserController::baseJson();
                $json['code'] = -1;
                $json['message'] = '未登录';
                $json = json_encode($json);
                echo $json;
                return false;
            }
    }

}