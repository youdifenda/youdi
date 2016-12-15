<?php

/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/14
 * Time: 11:56
 */
define('ROOT', dirname(__FILE__) . '/');
require_once 'db/DbHelp.php';
class QuestionController extends Yaf_Controller_Abstract
{



    // public function abcdAction(){
    //     $dbHelp = DbHelp::getInstance();
    // }
    //提交问题
    public function submitAction()
    {
        //content 文字 type 类型，是哪种问题，
        //basePrice 基础价格  addPrice 增加价格
        //listenPrice 听一次的价格
        //totalPrice 总计价格
        //userId 向谁提交问题

        $request = $this->getRequest();
        $type = $request->getPost('type');
        $content = $request->getPost('content', "");//提问内容
        $basePrice = $request->getPost('basePrice', "0");//获取提问价格
        $addPrice = $request->getPost('addPrice', "0");//追加价格
//        $listenPrice = $request->getPost('listenPrice');
        $listenPrice = 1;//听一次价格
        $answerut = $request->getPost('answerut');//获取回答问题的人
        $totalPrice = $basePrice + $addPrice;//获的总价
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
                $bill->ip = $this->getIP();
                $bill->remarks = $user['user_name'] . '提出了问题，支付了' . $totalPrice . '元钱';
                $bill->create_time = date('Y-m-d H:i:s');
                $dbHelp->store($bill);//账单表插入数据

                R::commit();
                $json = UserController::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json = json_encode($json);
                echo $json;
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
//        echo "<img src=".'../file/'.'Desert.jpg'.">";
    }

    //需要得到被提问人的信息
    public function initQuestionAction()
    {
        $request = $this->getRequest();
        $userId = $request->getPost('userId');
        return $this->getView();
    }

    //最新
    public function recentAction()
    {
        header('Access-Control-Allow-Origin: *');
        $page = $this->getRequest()->getPost('page');

        $value = array(($page) * 10 , ($page + 1) * 10);
        // file_put_contents('D:a.txt',$value);
        $sqlAnswer = ' ORDER BY createtime DESC LIMIT ?,? ';
        $result = $this->getAnswer($value, $sqlAnswer);
        foreach ($result as  &$value) {
            $value['imgs']=@ltrim($value['imgs'],'../');
        }
        // file_put_contents('D:a.txt',$result);
        $json = UserController::baseJson();
        $json['data'] = $result;
        $json['code'] = 0;
        $json['message'] = 'success';
        $json = json_encode($json);
        echo $json;
        // file_put_contents('D:a.txt',$json);
        return false;
    }

    //最热
    public function hottestAction()
    {   
        header('Access-Control-Allow-Origin: *');
        $page = $this->getRequest()->getPost('page');
        $sql = 'order by listennum desc,createtime desc LIMIT ?,?';
        $value = array(($page) * 10, ($page + 1) * 10);
        $result = $this->getAnswer($value, $sql);
        //查询是否收藏


        // var_dump($result);
        foreach ($result as  &$value) {
            $value['imgs']=@ltrim($value['imgs'],'../');
            $value['collect']=$this->getCollect($value['id'],$value['ut']);    
        }


        $json = UserController::baseJson();
        $json['data'] = $result;
        $json['code'] = 0;
        $json['message'] = 'success';
        $json = json_encode($json);
        // file_put_contents("D:a.txt",$result);
        echo $json;
        return false;
        
        
    }


    //查询是否收藏
    public function getCollect($answerid, $ut){
        
        $dbHelp = DbHelp::getInstance();
        $table = 'collect';
        $sql = ' answerid = ? and ut = ?';
        $value = array($answerid, $ut);
        $collect = $dbHelp->findOne($table, $sql, $value);
        // var_dump($collect);
        //收藏了该问题
        if (!empty($collect)) {
            $result = 1;
        } else {
            $result = 0;
        }
        return $result;
        file_put_contents('d:a.txt',$result);
    }

    //得到问题的数据
    public function getQuestion($page, $sql)
    {
        $dbHelp = DbHelp::getInstance();
        $table = 'question';
        $value = array(($page) * 10, ($page + 1) * 10);
        $array = $dbHelp->findAll($table, $sql, $value);
        $result = array();
        $i = 1;
        foreach ($array as $item) {
            $j = $item->getProperties();
            $result[$i] = $j;
            $i++;
        }
        return $result;
    }
    // 获得单个问题的数据
    public static function getAnswerOne($questionid)
    {
        $dbHelp = DbHelp::getInstance();
        $table = 'answer';
        $sql = 'questionid = ?';
        $value = array($questionid);
        $answer = $dbHelp->findOne($table, $sql, $value);
        $answer = $answer->getProperties();
        $result['answerContent'] = $answer['content'];
        $result['answerType'] = $answer['type'];
        $result['fileurl'] = $answer['fileurl'];
        $result['answerUT'] = $answer['ut'];
        $result['answerId'] = $answer['id'];
        $result['answerId'] = $answer['user_name'];
        $result['answerTime'] = self::showTime($answer['createtime']);
        return $result;
    }
    //获得问答的详细信息
    public static function getAnswer($value, $sql)
    {

        $dbHelp = DbHelp::getInstance();
        $table = 'answer';
        $array = $dbHelp->findAll($table, $sql, $value);
        // file_put_contents('d:a.txt',$array);
       // var_dump($array);
       // exit;

        $result = array();
        $i = 0;
        foreach ($array as $item) {
            $j = $item->getProperties();
            //通过$j查找问题的内容
            // file_put_contents('d:a.txt',$j);

            $time = self::showTime($j['createtime']);
            $j['time'] = $time;

            //查找问题题目
            $questionid = $j['questionid'];
            $table = 'question';
            $sql = ' id = ?';
            $value = array($questionid);
            $question = $dbHelp->findOne($table, $sql, $value);
            file_put_contents('d:a.txt',$question);
            if (empty($question)) {
                continue;
            } else {
                $question = $question->getProperties();
                $j['questioncontent'] = $question['content'];
                $j['listenprice'] = $question['listenprice'];
                //获取旁听数;
                $j['listennum'] = $question['listennum'];

                //查找用户表，获取用户信息
                $table = 'user';
                $sql = 'ut = ?';
                $ut = $j['ut'];
                $value = array($ut);
                $user = $dbHelp->findOne($table, $sql, $value);

                if ($user == null) {
                    continue;
                } else {
                    $user = $user->getProperties();

                    //还可以添加用户头像，用户公司
                    $j['user_name'] = $user['user_name'];
                    $j['imgs'] = $user['imgs'];
                    $j['honor'] = $user['honor'];
                    $j['abstract'] = $user['abstract'];

                    //是否登录,是否已经付钱
                    //是否是图文 type  0: 图文 , 1: 录音
                    $ut = UserController::isLogin();
                    if ($ut != -1) {
                        $j['isLogin'] = 1;

                        //判断是否已经付钱
                        $id = $j['id'];
                        $table = 'listen';
                        $sql = " answerid = ? and ut = ?";
                        $value = array($id, $ut);
                        $listen = $dbHelp->findOne($table, $sql, $value);
                        // 1 已经付过钱了  0 还没付钱
                        if (!empty($listen)) {
                            $j['payment'] = 1;
                        } else {
                            $j['payment'] = 0;
                        }
                        if ($j['ut'] == UserController::isLogin()) {
                            $j['payment'] = 1;
                        }

                        //获取旁听数
                        // $sql = 'answerid = ?';
                        // $value = array($id);
                        // $listen = $dbHelp->findAll($table, $sql, $value);
                        // $length = sizeof($listen);
                        // $j['listennum'] = $length;

                    } else {
                        $j['isLogin'] = 0;
                    }
//                    $j['team'] = $user['team'];
                    $result[$i] = $j;
                    $i++;
                }
            }
        }
        return $result;
    }


    public static function showTime($startdate)
    {
        $enddate = date('Y-m-d H:i:s');

        $date = floor((strtotime($enddate) - strtotime($startdate)) / 86400);
        $hour = floor((strtotime($enddate) - strtotime($startdate)) % 86400 / 3600);
        $minute = floor((strtotime($enddate) - strtotime($startdate)) % 86400 / 60);
        $second = floor((strtotime($enddate) - strtotime($startdate)) % 86400 % 60);
        //如果大于1天线是创建时间
        if ($date >= 30 || $date < 0) {
            $toY = date('Y', strtotime($enddate));
            $now = date('Y', strtotime($startdate));
            if ($toY != $now) {
                $time = date('Y-m-d', strtotime($startdate));
            } else {
                $time = date('m-d', strtotime($startdate));
            }
        } else {
            if ($date >= 1) {
                $time = "$date 天前";
            } else {
                if ($hour >= 1) {
                    $time = "$hour 时前";
                } else {
                    $time = "$minute 分前";
                }
            }
        }

        return $time;

    }

    //问题设置，用于设置基础价格
    public function setAction()
    {
        $dbHelp = DbHelp::getInstance();
        $request = $this->getRequest();
        $basePrice = $request->getPost('basePrice');
        $externalId = $request->getPost('ut');

    }

    //得到问答详情
    //最先写的
    public function detailAction()
    {
        $ut = UserController::isLogin();
        //登录成功
        if ($ut != -1) {
            $request = $this->getRequest();
            $answerId = $request->getPost('answerId');
            $value = array($answerId);
            $sql = ' id = ?';
            $result = $this->getAnswer($value, $sql);

            //因为问答详情的话，那么详情只会有一个
            //payment 0: 未付钱   1: 已付钱
            // if ($result[0]['payment'] == 0) {
                // $json = UserController::baseJson();
                // $json['code'] = -1;
                // $json['message'] = '未付款';
                // $json['data'] = array();
                // $json = json_encode($json);
                // echo $json;
                // return false;
            // } //已经付钱
            // else {
                $json = UserController::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json['data'] = $result;

                $json = json_encode($json);
                echo $json;
            // }
        } //登录失败
        else {
            $json = UserController::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json['data'] = array();
            $json = json_encode($json);
            echo $json;
        }
        return false;
    }

    //获得问答的ANSWERID跟questionID
    public function simpleDetailAction(){
        $request = $this->getRequest();
        $answerId = $request->getPost('answerId');
        $dbHelp = DbHelp::getInstance();
        $ut = UserController::isLogin();
        $table = 'answer';
        $sql = 'id = ?';
        $value = array($answerId);
        $answer=array();
        $answer = $dbHelp->findOne($table, $sql, $value);
        // file_put_contents('d:v.txt',$answer);
        //输出
        $json = UserController::baseJson();
        $json['code'] = 0;
        $json['message'] = 'success';
        $json['data'] = $answer;
        $json = json_encode($json);
        echo $json;
        // file_put_contents('d:v.txt',$json);
        return false;

    }
    //获得问答详情细节
    public function mDetailAction()
    {
        $request = $this->getRequest();
        $answerId = $request->getPost('answerId');
        $dbHelp = DbHelp::getInstance();
        $result = array();
        $ut = UserController::isLogin();
        if ($ut == -1) {

        }
        try {

            //先查答案表，并且获得回答问题的用户信息
            $table = 'answer';
            $sql = 'id = ?';
            $value = array($answerId);
            $answer = $dbHelp->findOne($table, $sql, $value);
            $answer = $answer->getProperties();
            $result['answerContent'] = $answer['content'];
            $result['answerType'] = $answer['type'];
            $result['fileurl'] = $answer['fileurl'];
            $result['answerUT'] = $answer['ut'];
            $result['answerId'] = $answer['id'];
            $result['answerTime'] = self::showTime($answer['createtime']);
            //查询listen表获得旁听数
            $table = 'listen';
            $sql = 'answerid = ?';
            $value = array($answer['id']);
            $listen = $dbHelp->findAll($table, $sql, $value);
            $result['listenNum'] = sizeof($listen);
            //查询用户表  获得用户的基本信息
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($answer['ut']);
            $user = $dbHelp->findOne($table, $sql, $value);
            $result['answerImg'] = @ltrim($user['imgs'],"../");
            $result['answerUserName'] = $user['user_name'];
            $result['answerabstract'] = $user['abstract'];
            $result['answerhonor'] = $user['honor'];
            //查询关注表
            //登录用户是否关注了回答问题的用户
            $table = 'follow';
            $sql = 'ut = ? and followut = ?';
            if ($ut != -1) {
                $value = array($ut, $answer['ut']);
                $follow = $dbHelp->findOne($table, $sql, $value);
                //如果查询到了数据，即表示已经关注了回答问题的用户
                if (!empty($follow)) {
                    $result['follow'] = 1;
                } else {
                    $result['follow'] = 0;
                }
            } else {
                $result['follow'] = 0;
            }
            //查询收藏表，是否收藏了该问题
            $table = 'collect';
            $sql = ' answerid = ? and ut = ?';
            $value = array($answer['id'], $ut);
            $collect = $dbHelp->findOne($table, $sql, $value);
            //收藏了该问题
            if (!empty($collect)) {
                $result['collect'] = 1;
            } else {
                $result['collect'] = 0;
            }

            //查询问题表，获得问题的相关信息
            $table = 'question';
            $sql = ' id = ?';
            $value = array($answer['questionid']);
            $question = $dbHelp->findOne($table, $sql, $value);
            $result['questionContent'] = $question['content'];
            $result['questionPrice'] = $question['baseprice'] + $question['addprice'];
            $result['questionTime'] = self::showTime($question['createtime']);
            //查询用户表,获得问问题的用户的基本信息
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($question['ut']);
            $user = $dbHelp->findOne($table, $sql, $value);
            $result['questionUserName'] = $user['user_name'];
            $result['questionhonor'] = $user['honor'];
            // $result['questionabstract'] = $user['abstract'];
            $result['questionUserImg'] =  @ltrim($user['imgs'],"../");

            $json = UserController::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            echo $json;
            return false;
        } catch (Exception $exception) {
            $json = UserController::baseJson();
            $json['code'] = -1;
            $json['message'] = $exception->getMessage();
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }

    //回答问题页面，问题的详情
    public function showAction()
    {
        $request = $this->getRequest();
        $questionId = $request->getPost('id', "");
        $dbHelp = DbHelp::getInstance();

        $table = 'question';
        $sql = 'id = ?';
        $value = array($questionId);
        $question = $dbHelp->findOne($table, $sql, $value);
        //是否查找到问题
        if (!empty($question)) {
            $question = $question->getProperties();
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($question['ut']);
            $user = $dbHelp->findOne($table, $sql, $value);

            //是否查找到用户
            if (!empty($user)) {
                $user = $user->getProperties();
                $question['ut'] = $user['ut'];
                $question['imgs'] = $user['imgs'];
                $question['user_name'] = $user['user_name'];
//        $question['company'] = $user['company'];

                $json = UserController::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json['data'] = $question;
                $json = json_encode($json);
                echo $json;
            } //未查找到用户
            else {
                $json = UserController::baseJson();
                $json['code'] = -1;
                $json['message'] = '未找到用户';
                $json = json_encode($json);
                echo $json;
            }
        } //未查找到问题
        else {
            $json = UserController::baseJson();
            $json['code'] = -1;
            $json['message'] = '未找到问题';
            $json = json_encode($json);
            echo $json;
        }
        return false;
    }

    //回答问题
    public function submitAnswerAction()
    {

        $request = $this->getRequest();
        $content = $request->getPost('content');
        $type = $request->getPost('type');
        $questionid = $request->getPost('questionId');
        date_default_timezone_set('PRC');
        $createTime = date('Y-m-d H:i:s');
//        var_dump($createTime);
        $ut = UserController::isLogin();
        //已经登录
        if ($ut != -1) {
            $fileName = $this->saveFile();
            if (!empty($fileName))
                $url = '/upload/' . $fileName;
            else $url = '';
            $dbHelp = DbHelp::getInstance();

            $table = 'user';
            $sql = 'ut = ?';
            $value = array($ut);
            $user = $dbHelp->findOne($table, $sql, $value);
            $user = $user->getProperties();

            //如果答案表里已经有了答案，就不能再次回答这个问题
            $table = 'answer';
            $sql = 'questionid = ?';
            $value = array($questionid);
            $answer = $dbHelp->findOne($table,$sql,$value);
            if(!empty($answer)){
                $json = UserController::baseJson();
                $json['code'] = -1;
                $json['message'] = '不能重复回答问题';
                $json = json_encode($json);
                echo  $json;
                return false;
            }

            $answer = $dbHelp->dispense($table);
            $answer->ut = $ut;
            $answer->content = $content;
            $answer->fileurl = $url;
            $answer->user_name = $user['user_name'];
            $answer->createtime = $createTime;
            $answer->questionid = $questionid;
            $answer->type = $type;


            try{
                R::begin();
                $dbHelp->store($answer);
                //需要得到提问的人的相关的信息
                $table = 'question';
                $sql = 'id = ?';
                $value = array($questionid);
                $question = $dbHelp->findOne($table, $sql, $value);
                $question = $question->getProperties();

                $table ='user';
                $sql = 'ut = ?';
                $value = array($ut);
                $user = $dbHelp->findOne($table,$sql,$value);
                $user->setAttr('balance',$user->getProperties()['balance'] + $question['totalprice'] );
                $dbHelp->store($user);

                $table = 'user';
                $sql = 'ut = ?';
                $value = array($question['ut']);
                $user = $dbHelp->findOne($table, $sql, $value);
                $user = $user->getProperties();

                $table = 'message';
                $message = $dbHelp->dispense($table);
                $message->sendut = $ut;
                $message->acceptedut = $user['ut'];
                $message->content = $content;
                $message->type = 2;
                $dbHelp->store($message);
                R::commit();

                $json = UserController::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json = json_encode($json);
                echo $json;
                return false;
            }
            catch (Exception $e){
                R::rollback();
                $json = UserController::baseJson();
                $json['code'] = -1;
                $json['message'] = '回答失败';
                $json = json_encode($json);
                echo $json;
                return false;
            }
        } //还未登录
        else {
//            $this->redirect('/user/login');
        }
//        echo "<img src=".'../file/'.'Desert.jpg'.">";
        return false;
    }

    //保存文件
    public function saveFile()
    {
        $fileName = '';
        if (!empty($_FILES['file'])) {
            if (($_FILES["file"]["size"] < 2048000)         //小于2000KB大小
            ) {
                if ($_FILES["file"]["error"] > 0) {
//                echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
                } else {
                    if (file_exists("upload/" . $_FILES["file"]["name"])) {
//                    echo $_FILES["file"]["name"] . " already exists. ";
                    } else {
                        $fileName = time() . '' . $this->getIP();
                        $end = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1);
                        $fileName = md5($fileName) . '.' . $end;
                        move_uploaded_file($_FILES["file"]["tmp_name"],
                            "upload/" . $fileName);
                    }
                }
            } else {

            }
        }
        return $fileName;
    }

    //获取IP
    public static function getIP()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return ($ip);
    }
}