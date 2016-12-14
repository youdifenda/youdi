<?php
/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/
 * Time: 14:2710
 */
require_once 'db/DbHelp.php';

class  UserController extends Yaf_Controller_Abstract
{

    public function indexAction()
    {
        echo 'this is Module =>user and Controller => user and Action => index' . '<br>';
        $request = $this->getRequest();
        $post = $request->getPost('page', "page");
        var_dump($request);
        echo 'Page : ->' . $post . '<br>';
        return true;
    }

    public function showAction()
    {
        echo 'this is show <br>';
        echo 'this is Module => user and Controller => Index and Action => show' . '<br>';
        if (isset($_SESSION['name'])) {
            echo 'Session UserName : ' . $_SESSION['name'] . '<br>';
        } else {
            echo 'Session is not set : ' . '<br>';
        }
        return false;
    }

    //登录模块
    public function loginAction()
    {
        header('Access-Control-Allow-Origin: *');
        $request = $this->getRequest();
        $userName = $request->getPost('userName', "");
        $password = $request->getPost('password', "");
        $code = $request->getPost('code', "");

//        $arr = array();
//        $arr['userName'] = $userName;
//        $arr["password"] = $password;
//        $arr['code'] = $code;
//        $arr['session_code'] = self::getSession('code');
//        echo json_encode($arr);
//        exit;


        //验证码正确
        if (strtolower(self::getSession('code')) == strtolower($code)) {

            $dbHelp = DbHelp::getInstance();
            $password = $this->generatorPassword($password);
            $value = array($userName, $password);
            $table = 'user';
            $sql = 'user_name = ? AND password = ?';
            $result = $dbHelp->findOne($table, $sql, $value);
            // file_put_contents('d:./a.txt',$result);//打印出来result

            //登录成功
            if (sizeof($result) > 0) {
                $properties = $result->getProperties();
                $ut = $properties['ut'];
                // file_put_contents('d:./a.txt',$ut);
                //第二次进入
                if (Yaf_Session::getInstance()->has('ut')) {
                    $Id = Yaf_Session::getInstance()->get('ut');
                    if ($ut != $Id) {
                        Yaf_Session::getInstance()->set('ut', $ut);
                    }
                } //第一次登录
                else {
                    Yaf_Session::getInstance()->set('ut', $ut);
                }
                $result = self::baseJson();
                $result['code'] = 0;
                $result['message'] = '登录成功';
                json_encode($result['data']);
                echo json_encode($result);
            } //登录失败
            else {
                $result = self::baseJson();
                $result['code'] = -1;
                $result['message'] = '登录失败';
                json_encode($result['data']);
                echo json_encode($result);
            }
            return false;
        } //验证码不正确
        else {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '验证码不正确';
            echo json_encode($json);
            return false;
        }


    }

    //注册模块
    public function registerAction()
    {
        $request = $this->getRequest();
        $userName = $request->getPost('userName', "");
        $password = $request->getPost('password', "");
        $code = $request->getPost('code', "");
        $confirmPassword = $request->getPost("confirmPassword", "");

//        $arr = array();
//        $arr['userName'] = $userName;
//        $arr["password"] = $password;
//        $arr['confirmPassword'] = $confirmPassword;
//        $arr['code'] = $code;
//        $arr['session_code'] = self::getSession('code');
//        echo json_encode($arr);
//        exit;
//        
        //验证码正确
        if (strtolower(self::getSession('code')) == strtolower($code)) {
            //正则验证用户名格式
            $pattern='/^[\w\_]{6,15}$/u';
            if(!preg_match($pattern,$userName)) {
                 $result = self::baseJson();
                        $result['code'] = -1;
                        $result['message'] = '用户名格式不正确';
                        $result['data'] = array();
                        echo json_encode($result);
                        return false;
            }
            else {
                //密码验证
                        /**
                         * $pattern：正则验证;
                         * Created by PhpStorm.
                         * user: zh
                         * Date: 2016/12/8
                         * Time: 16:08
                         */
                $pattern='/^[\w\_]{6,15}$/u';
                if(!preg_match($pattern,$password)) {
                    $result = self::baseJson();
                    $result['code'] = -1;
                    $result['message'] = '密码格式不正确';
                    $result['data'] = array();
                    echo json_encode($result);
                    return false;
                } else {

                //密码一致
                    if ($password == $confirmPassword) {
                        $password = $this->generatorPassword($password);
                        $dbHelp = DbHelp::getInstance();
                        $value = array($userName, $password);
                        $table = 'user';
                        $sql = ' user_name = ? AND password = ?';
                        $users = $dbHelp->findOne($table, $sql, $value);
                        // file_put_contents('D:/a.txt',$users);
                        
                        
                            if (sizeof($users) > 0) {
                                $result = self::baseJson();
                                $result['code'] = -1;
                                $result['message'] = '用户注册失败，已经有用户名';
                                $result['data'] = array();
                                echo json_encode($result);
                                return false;
                            } else {
                                $ut = $this->generateExternalId(8);
                                $table = 'user';
                                $user = $dbHelp->dispense($table);
                                $user->ut = $ut;
                                $user->userName = $userName;
                                $user->nick_name="YD_".$this->make_char();
                                $user->password = $password;
                                $val = $dbHelp->store($user);

                                if ($val != 0) {
                                    $result = self::baseJson();
                                    $result['code'] = 0;
                                    $result['message'] = '用户注册成功';
                                    $result['data'] = array();
                                    echo json_encode($result);
                                    return false;
                                } else {
                                    $result = self::baseJson();
                                    $result['code'] = -1;
                                    $result['message'] = '用户注册失败';
                                    $result['data'] = array();
                                    echo json_encode($result);
                                    return false;
                                }
                            }
                        
                    } //两次密码不一致
                    else {
                        $json = self::baseJson();
                        $json['code'] = -1;
                        $json['message'] = '两次密码输入不一致';
                        echo json_encode($json);
                        return false;
                    }
                }    
            }
        } //验证码不正确
        else {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '验证码不正确';
            echo json_encode($json);
            return false;
        }
    }
    //修改资料
    function modifyAction(){
        $ut = self::isLogin();
        $dbHelp = DbHelp::getInstance();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else{
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($ut);
            $user = $dbHelp->findOne($table, $sql, $value);
            $user = $user->getProperties();
            $result['nick_name'] = $user['nick_name'];
            $result['basePrice'] = $user['basePrice'];
            $result['honor'] = $user['honor'];
            $result['abstract'] = $user['abstract'];
            $result['imgs'] = $user['imgs'];
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            echo $json;
            return false;

        }
    }


    //提交修改资料
    function submitModifyAction(){
        $request = $this->getRequest();
        $nick_name = $request->getPost('nick_name');
        $honor = $request->getPost('honor');
        $abstract = $request->getPost('abstract');
        $dbHelp = DbHelp::getInstance();
        $ut = self::isLogin();
        //未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else{
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($ut);
            // $time = QuestionController::showTime($result[$i]['createtime']);
            $user = $dbHelp->findOne($table, $sql, $value);
            $user->ut = $ut;
            $user->nick_name = $nick_name;
            $user->honor = $honor;
            $user->abstract = $abstract;
            // $user->imgs = $imgs;
            $val = $dbHelp->store($user);
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }
    //价格修改
    public function basePriceAction(){
        $request = $this->getRequest();
        $basePrice = $request->getPost('basePrice');
        $ut = self::isLogin();
        //未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else{
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($ut);
            // $time = QuestionController::showTime($result[$i]['createtime']);
            $user = $dbHelp->findOne($table, $sql, $value);
            $user->ut = $ut;
            $user->basePrice = $basePrice;
            $val = $dbHelp->store($user);
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json = json_encode($json);
            echo $json;
            return false;
        }

    }
    //修改密码
    public function modifyPwd(){

    }
    //生成随机昵称字符串
    function make_char( $length = 7 )
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 
        'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's', 
        't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D', 
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', 
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        // 在 $chars 中随机取 $length 个数组元素键名
        $keys = array_rand($chars, $length);
        $makechar = '';
        for($i = 0; $i < $length; $i++)
        {
            // 将 $length 个数组元素连接成字符串
            $makechar .= $chars[$keys[$i]];
        }
        return $makechar;
    }

    //我问
    public function iaskAction()
    {
        $ut = $this->isLogin();
        $dbHelp = DbHelp::getInstance();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $page = $this->getRequest()->getPost('page');
            $table = 'question';
            $result = $this->getList($page, $table, $ut);
            $length = sizeof($result);

            //state:   0： 有答案
            //         1： 未回答
            //         2： 已过期
            for ($i = 0; $i < $length; $i++) {
                $table = 'answer';
                $sql = 'questionid = ?';
                $value = array($result[$i]['id']);
                $time = QuestionController::showTime($result[$i]['createtime']);
                $result[$i]['time'] = $time;
                $answer = $dbHelp->findOne($table, $sql, $value);
                //如果答案为空
                if (!empty($answer)) {
                    $answer = $answer->getProperties();
                    //有答案
                    if (sizeof($answer) != 0) {
                        $result[$i]['state'] = 0;
                        $result[$i]['answerContent'] = $answer['content'];
                        $result[$i]['answerType'] = $answer['type'];
                        $result[$i]['answerFile'] = $answer['fileurl'];
                        $result[$i]['answerId'] = $answer['id'];
                        $userId = $answer['ut'];

                        //获取回答问题的用户基本信息
                        $table = 'user';
                        $sql = 'ut = ?';
                        $value = array($userId);
                        $user = $dbHelp->findOne($table, $sql, $value);
                        $user = $user->getProperties();
                        $result[$i]['answerUserName'] = $user['nick_name'];
                        $result[$i]['answerUserImg'] = $user['imgs'];
//                    $result[$i]['userDesc'] = $answer['fileurl'];

                        $table = 'listen';
                        $sql = " answer_id = ? and ut = ?";
                        $id = $answer['id'];
                        $value = array($id, $ut);
                        $listen = $dbHelp->findOne($table, $sql, $value);
                        // 1 已经付过钱了  0：还没付钱
                        if (!empty($listen)) {
                            $result[$i]['payment'] = 1;
                        } else {
                            $result[$i]['payment'] = 0;
                        }

                        //查看是否是自己提问的
                        //如果是自己提问的就无需付费
                        $sql = 'id = ? and ut = ?';
                        $value = array($answer['questionid'], $ut);
                        $table = 'question';
                        $question = $dbHelp->findOne($table, $sql, $value);
                        if (!empty($question)) $result[$i]['payment'] = 1;
                        else $result[$i]['payment'] = 0;

                    } //没有答案
                    else {
                        $result[$i]['state'] = 1;
                        $table = 'user';
                        $sql = 'ut = ?';
                        $value = array($ut);
                        $user = $dbHelp->findOne($table, $sql, $value);
                        $user = $user->getProperties();
                        $result[$i]['userName'] = $user['user_name'];
                        $result[$i]['userImg'] = $user['imgs'];

                        //已过期
                        //中文占3个字节
                        $str = $result[$i]['time'];
                        $day = substr($str, 0, strlen($str) - 7);
                        $type = substr($str, strlen($str) - 6, 3);

//                        echo 'day :'.$day.'<br>';
//                        echo 'type : '.$type.'<br>';

                        if (($type == '天' && $day >= 2) || !strpos($str, '前'))
                            $result[$i]['state'] = 2;
                    }
                } //没答案
                else {
                    $result[$i]['state'] = 1;
                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($ut);
                    $user = $dbHelp->findOne($table, $sql, $value);
                    $user = $user->getProperties();
                    $result[$i]['userName'] = $user['user_name'];
                    $result[$i]['userImg'] = $user['imgs'];

                    //已过期
                    //中文占3个字节
                    $str = $result[$i]['time'];
                    $day = substr($str, 0, strlen($str) - 7);
                    $type = substr($str, strlen($str) - 6, 3);

//                    echo 'day :'.$day.'<br>';
//                    echo 'type : '.$type.'<br>';
                    if (($type == '天' && $day >= 2) || !strpos($str, '前'))
                        $result[$i]['state'] = 2;
                }
            }
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }

    //我听
    public function listenAction()
    {
        $ut = $this->isLogin();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $page = $this->getRequest()->getPost('page');
            $table = 'listen';
            $result = $this->getList($page, $table, $ut);
            $length = sizeof($result);
            for ($i = 0; $i < $length; $i++) {
                $sql = 'id = ?';
                $value = array($result[$i]['answerid']);
                $answer = QuestionController::getAnswer($value, $sql);
                $answer = $answer[0];
                $result[$i] = $answer;
            }
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            file_put_contents('d:a.txt',$json);
            echo $json;
            return false;
        }
    }

    //消息提醒
    public function messageAction()
    {
        $ut = self::isLogin();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $dbHelp = DbHelp::getInstance();
            $table = 'message';
            $sql = 'acceptedut = ? and hide = ? or type = ? and hide = ?  ORDER BY createtime desc ';
            $value = array($ut, 1, 3, 1);
            $message = $dbHelp->findAll($table, $sql, $value);

            if (sizeof($message) == 0) {
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = '无信息';
                $json = json_encode($json);
                echo $json;
                return false;
            } else {
                $i = 0;
                $result = array();
                foreach ($message as $item) {
                    $result[$i] = $item->getProperties();
                    $result[$i]['time'] = QuestionController::showTime($result[$i]['createtime']);
                    //
//                    if ($result[$i]['type'] == 2) {
//
//                    }
//                    if ($result[$i]['type'] == 2) {
//
//                    }
                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($result[$i]['sendut']);
                    $user = $dbHelp->findOne($table, $sql, $value);
                    if (!empty($user)) {
                        $user = $user->getProperties();
                        $result[$i]['userName'] = $user['user_name'];
                        $result[$i]['imgs'] = $user['imgs'];
                    }
                    $i++;
                }
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json['data'] = $result;
                $json = json_encode($json);
                echo $json;
                return false;
            }

        }
    }

    //删除全部信息
    public function deleteMessageAction()
    {
        $dbHelp = DbHelp::getInstance();
        $ut = self::isLogin();
        $table = 'message';
        $sql = ' acceptedut = ?';
        $value = array($ut);
        $msg = $dbHelp->findAll($table, $sql, $value);

        if (!empty($msg)) {
            foreach ($msg as $item) {
                $dbHelp->trash($item);
            }
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json = json_encode($json);
            echo $json;
            return false;
        }

        $json = self::baseJson();
        $json['code'] = 0;
        $json['message'] = '无法删除系统信息';
        $json = json_encode($json);
        echo $json;
        return false;
    }
    //我答
    //向我提问的
    public function iresponseAction()
    {
        $ut = $this->isLogin();
        $dbHelp = DbHelp::getInstance();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $page = $this->getRequest()->getPost('page');
            $table = 'question';
            $sql = 'answerut = ? order by createtime desc';
            $value = array($ut);
            $array = $dbHelp->findAll($table, $sql, $value);

            $result = array();
            // file_put_contents("d:a.txt",$array);
            $i = 0;
            foreach ($array as $item) {
                $j = $item->getProperties();
                $result[$i] = $j;
                $i++;
            }
            for ($i = 0; $i < sizeof($result); $i++) {
                $table = 'answer';
                $sql = 'questionid = ? and ut = ?';
                $value = array($result[$i]['id'], $ut);
                $time = QuestionController::showTime($result[$i]['createtime']);
                $result[$i]['time'] = $time;
                $answer = $dbHelp->findOne($table, $sql, $value);
                $result[$i]['payment'] = 0;
                // var_dump($result);
                // file_put_contents('D:a.txt',$result);


                //如果答案不为空
                if (!empty($answer)) {
                    $answer = $answer->getProperties();
                    //有答案
                    //获取回答用户的相关信息

                    if (sizeof($answer) != 0) {
                        $result[$i]['state'] = 0;
                        $result[$i]['answerContent'] = $answer['content'];
                        $result[$i]['answerType'] = $answer['type'];
                        $result[$i]['answerFile'] = $answer['fileurl'];
                        $result[$i]['answerid'] = $answer['id'];
                        $userId = $answer['ut'];

                        //查询用户表
                        $table = 'user';
                        $sql = 'ut = ?';
                        $value = array($userId);
                        $user = $dbHelp->findOne($table, $sql, $value);
                        $user = $user->getProperties();
                        $result[$i]['answerUserName'] = $user['user_name'];
                        $result[$i]['answerUserImg'] = $user['imgs'];
//                    $result[$i]['userDesc'] = $answer['fileurl'];
//                      $result[$i]['state'] = 1;
                        $table = 'user';
                        $sql = 'ut = ?';
                        $value = array($result[$i]['ut']);
                        $user = $dbHelp->findOne($table, $sql, $value);
                        $user = $user->getProperties();
                        $result[$i]['userName'] = $user['user_name'];
                        $result[$i]['userImg'] = $user['imgs'];


                        //因为是我回答的向我提问的问题，所以无需付费，我也可以听自己的回答
//                        $table = 'listen';
//                        $sql = " answer_id = ? and ut = ?";
//                        $id = $answer['id'];
//                        $value = array($id, $ut);
//                        $listen = $dbHelp->findOne($table, $sql, $value);
//                        // 1 已经付过钱了  0：还没付钱
//                        if (!empty($listen)) {
//                            $result[$i]['payment'] = 1;
//                        } else {
//                            $result[$i]['payment'] = 0;
//                        }
                        if (self::isLogin() == $answer['ut']) {
                            $result[$i]['payment'] = 1;
                        }
                    } //没有答案
                    else {
                        $result[$i]['state'] = 1;
                        $table = 'user';
                        $sql = 'ut = ?';
                        $value = array($result[$i]['ut']);
                        $user = $dbHelp->findOne($table, $sql, $value);
                        $user = $user->getProperties();
                        $result[$i]['userName'] = $user['user_name'];
                        $result[$i]['userImg'] = $user['imgs'];

                        //已过期
                        //已过期
                        //中文占3个字节
                        $str = $result[$i]['time'];
                        $day = substr($str, 0, strlen($str) - 7);
                        $type = substr($str, strlen($str) - 6, 3);

//                    echo 'day :'.$day.'<br>';
//                    echo 'type : '.$type.'<br>';
                        if (($type == '天' && $day >= 2) || !strpos($str, '前'))
                            $result[$i]['state'] = 2;
                    }
                } //没答案
                else {
                    $result[$i]['state'] = 1;
                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($result[$i]['ut']);
                    $user = $dbHelp->findOne($table, $sql, $value);
                    $user = $user->getProperties();
                    $result[$i]['userName'] = $user['user_name'];
                    $result[$i]['userImg'] = $user['imgs'];

                    //已过期
                    //中文占3个字节
                    $str = $result[$i]['time'];
                    $day = substr($str, 0, strlen($str) - 7);
                    $type = substr($str, strlen($str) - 6, 3);
//                    echo 'day :'.$day.'<br>';
//                    echo 'type : '.$type.'<br>';
                    if (($type == '天' && $day >= 2) || !strpos($str, '前'))
                        $result[$i]['state'] = 2;
                }
            }
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }

    //关注 
    public function followAction()
    {
        $request = $this->getRequest();
        $userId = $request->Get('userId');
        $ut = self::isLogin();
        //未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } //        已经登录
        else {
            //不能自己关注自己
            if ($userId == $ut) {
                $json = self::baseJson();
                $json['code'] = -1;
                $json['message'] = '不能关注自己';
                $json = json_encode($json);
                echo $json;
                return false;
            }

            $dbHelp = DbHelp::getInstance();
            $table = 'follow';
            $sql = 'ut = ? and followut = ?';
            $value = array($ut, $userId);
            $follow = $dbHelp->findOne($table, $sql, $value);
            //取消关注
            if (!empty($follow)) {
                $dbHelp->trash($follow);
                //给USER表的fansnum字段-1;
                $table = 'user';
                $sql = 'ut = ? ';
                $value = array($userId);
                $user = $dbHelp->findOne($table, $sql, $value);
                $fansnum=$user->getProperties()['fansnum'] - 1;
                // //将fansnum字段+1;
                $user->setAttr('fansnum', $fansnum);
                $dbHelp->store($user);




                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = '取消关注';
                $json = json_encode($json);
                echo $json;
                return false;
            } //添加关注
            else {

                //采用事务同时操作两个数据库
                $table = 'follow';
                $follow = $dbHelp->dispense($table);
                $follow->ut = $ut;
                $follow->followut = $userId;
                $result = $dbHelp->store($follow);
                if ($result != -1) {
                    $json = self::baseJson();
                    //关注+1
                    $table = 'user';
                    //     // $question = $dbHelp->dispense($table);//创建或更新表
                    $sql = 'ut = ? ';
                    $value = array($userId);
                    $user = $dbHelp->findOne($table, $sql, $value);
                    // $listennum=$question->getProperties()['listennum'] + 1;
                    $fansnum=$user->getProperties()['fansnum'] + 1;
                    // //将旁听字段+1;
                    $user->setAttr('fansnum', $fansnum);
                    $dbHelp->store($user);


                    $json['code'] = 0;
                    $json['message'] = '关注成功';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                } else {
                    $json = self::baseJson();
                    $json['code'] = -1;
                    $json['message'] = '关注失败';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                }
            }
        }
    }

    //获得关注列表
    public function followListAction()
    {
        $ut = self::isLogin();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $request = $this->getRequest();
            $dbHelp = DbHelp::getInstance();
            $table = 'follow';
            $sql = 'ut = ?';
            $value = array($ut);
            $follow = $dbHelp->findAll($table, $sql, $value);
            // file_put_contents('D:a.txt',$follow);
            $result = array();
            $i = 0;
            if (sizeof($follow) != 0) {
                $length = sizeof($follow);
                foreach ($follow as $item) {
                    //通过followUt 查找用户表 获取被关注的用户的基本信息
                    $item = $item->getProperties();
                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($item['followut']);
                    $user = $dbHelp->findOne($table, $sql, $value);
                    $user = $user->getProperties();
                    $result[$i]['userName'] = $user['user_name'];
                    $result[$i]['userImg'] = $user['imgs'];
                    $result[$i]['followut'] = $user['ut'];
                    $result[$i]['fansNum'] = $user['fansnum'];

                    //查答案表。获取答复数
                    $table = 'answer';
                    $sql = 'ut = ?';
                    $value = array($user['ut']);
                    $answer = $dbHelp->findAll($table, $sql, $value);
                    $num = sizeof($answer);
                    $result[$i]['answerNum'] = $num;

                    //查关注表看是否关注
                    $i++;
                }
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json['data'] = $result;
                $json = json_encode($json);
                // file_put_contents('D:b.txt',$json);
                echo $json;
                return false;
            } else {
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json = json_encode($json);
                echo $json;
                return false;
            }

        }
    }

    //搜索
    public function searchAction()
    {
        $request = $this->getRequest();
        $keyWord = $request->getPost('keyWord');

        $table = 'user';
        $sql = 'user_name like ?';
        $value = array('%' . $keyWord . '%');
        $dbHelp = DbHelp::getInstance();
        $search = $dbHelp->findAll($table, $sql, $value);
//        var_dump($search);
//        var_dump($keyWord);
        if (!empty($search)) {
            $length = sizeof($search);
            $i = 0;
            $result = array();
            foreach ($search as $item) {
                $j = $item->getProperties();
                $result[$i] = $j;
                $i++;
            }
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = '没有相应的用户';
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }

    //我问，我答，我听的获得列表的信息
    public function getList($page, $table, $ut)
    {
        $dbHelp = DbHelp::getInstance();
        $sql = 'ut = ? ORDER BY createtime desc  LIMIT ?,?';
        $value = array($ut, ($page) * 10, ($page + 1) * 10);
        $array = $dbHelp->findAll($table, $sql, $value);

        $result = array();
        $i = 0;
        foreach ($array as $item) {
            $j = $item->getProperties();
            $result[$i] = $j;
            $i++;
        }
        return $result;
    }

    //旁听支付
    public function payAction()
    {
        $ut = self::isLogin();
        //未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } //已登录
        else {
            $request = $this->getRequest();
            $answerId = $request->getPost('answerId');

            $dbHelp = DbHelp::getInstance();
            $table = 'answer';
            $sql = 'id = ?';
            $value = array($answerId);
            $answerBean = $dbHelp->findOne($table, $sql, $value);
            $answer = $answerBean->getProperties();

            $questionid = $answer['questionid'];
            $answerUt = $answer['ut'];
            $table = 'question';
            $sql = ' id = ?';
            $value = array($questionid);
            $question = $dbHelp->findOne($table,$sql,$value);
            $questionUt = $question->getProperties()['ut'];

            $table = 'listen';
            $sql = 'ut = ? and answerid = ?';
            $value = array($ut, $answerId);
            $result = $dbHelp->findOne($table, $sql, $value);
            if (!empty($result)) {
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = '已付钱';
                $json = json_encode($json);
                echo $json;
                return false;
            } else {
                //这是旁听支付
                //需要给提问的人提成，也要给回答问题的人提成
                try {
                    R::begin();

                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($answerUt);
                    //回答问题的人得到的酬金
                    $user = $dbHelp->findOne($table,$sql,$value);
                    $user->setAttr('balance',$user->getProperties()['balance'] + 0.5);
                    $dbHelp->store($user);

                    //提问的人所得的酬金
                    $value = array($questionUt);
                    $user = $dbHelp->findOne($table,$sql,$value);
                    $user->setAttr('balance',$user->getProperties()['balance'] + 0.5);
                    $dbHelp->store($user);

                    //插入收听表
                    $table = 'listen';
                    $listen = $dbHelp->dispense($table);
                    $listen->ut = $ut;
                    $listen->answerid = $answerId;
                    $listen->questionid = $questionid;
                    $dbHelp->store($listen);

                    //扣除自己账户的余额
                    $table = 'user';
                    $sql = 'ut = ?';
                    $value = array($ut);
                    $user = $dbHelp->findOne($table,$sql,$value);
                    $user->setAttr('balance',$user->getProperties()['balance'] - 1);
                    $dbHelp->store($user);

                    //生成账单
                    $table = 'bill';
                    $bill = $dbHelp->dispense($table);
                    $bill->status = 0;
                    $bill->ut = $ut;
                    $bill->amount = 1;
                    $bill->ip = QuestionController::getIP();
                    $bill->remarks ='旁听了'.$answer['id'].'答案..';
                    $bill->create_time = date('Y-m-d H:i:s');
                    $dbHelp->store($bill);

                    R::commit();
                    $json = self::baseJson();
                    $json['code'] = 0;
                    $json['message'] = '支付成功';
                    $json = json_encode($json);
                    echo $json;
                    return false;

                } catch (Exception $e) {
                    R::rollback();
                    $json = self::baseJson();
                    $json['code'] = -1;
                    $json['message'] = '支付失败';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                }
            }
        }
    }

    //我的收藏
    public function showCollectionAction()
    {
        $ut = $this->isLogin();
        //未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } //已经登录
        else {
            $dbHelp = DbHelp::getInstance();
            $table = 'collect';
            $sql = 'ut = ?';
            $value = array($ut);
            $arr = $dbHelp->findAll($table, $sql, $value);

            $collect = array();
            $i = 0;
            foreach ($arr as $item) {
                $j = $item->getProperties();
                $collect[$i] = $j;
                $i++;
            }
            $length = sizeof($collect);
            $result = array();
            for ($i = 0; $i < $length; $i++) {
                $sql = 'id = ?';
                $value = array($collect[$i]['answerid']);
                $result[$i] = QuestionController::getAnswer($value, $sql)[0];
            }
            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json['data'] = $result;
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }
    //查找老师用户
    //财富达人
    public function teacherAction(){
        $ut = self::isLogin();
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } else {
            $dbHelp = DbHelp::getInstance();
            $table="user";
            $page = $this->getRequest()->getPost('page');
            $sql = 'stage = ? order by fansnum desc LIMIT ?,?';
            $value = array(1,($page) * 10, ($page + 1) * 10);
            $user = $dbHelp->findAll($table, $sql, $value);
            $i = 0;
            $result=array();
            if (sizeof($user) != 0) {
                $length = sizeof($user);
                foreach ($user as $item) {
                    $user = $item->getProperties();
                    $result[$i]['userName'] = $user['user_name'];
                    $result[$i]['userImg'] = $user['imgs'];
                    $result[$i]['followut'] = $user['ut'];
                    $result[$i]['fansNum'] = $user['fansnum'];

                    $table = 'answer';
                    $sql = 'ut = ?';
                    $value = array($user['ut']);
                    $answer = $dbHelp->findAll($table, $sql, $value);
                    $num = sizeof($answer);
                    $result[$i]['answerNum'] = $num;

                    file_put_contents('D:b.txt',2);
                    $table = 'follow';
                    $sql = 'ut = ? and followut = ?';
                    $value = array($ut,$result[$i]['followut']);
                    $follow = $dbHelp->findOne($table, $sql, $value);
                    $num = sizeof($follow);
                    if($num!= 0){
                        $result[$i]['follow'] = 1;
                    }else{
                        $result[$i]['follow'] = 0;
                    }

                    $i++;
                }
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json['data'] = $result;
                $json = json_encode($json);
                    
                // file_put_contents('D:b.txt',$json);
                echo $json;
                return false;
            }
        }
    }

    //批量取消收藏
    public function clearCollectAction()
    {
        $ut = self::isLogin();
        //用户未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } //用户已经登录
        else {
            $dbHelp = DbHelp::getInstance();
            $request = $this->getRequest();
            $ids = $request->getPost('ids');
            $answerId = explode(',', $ids);
            $length = sizeof($answerId);
            for ($i = 0; $i < $length; $i++) {
                $table = 'collect';
                $sql = 'answerid = ? and ut = ?';
                $value = array($answerId[$i], $ut);
                $collect = $dbHelp->findOne($table, $sql, $value);
                if (!empty($collect)) {
                    $dbHelp->trash($collect);
                }
            }

            $json = self::baseJson();
            $json['code'] = 0;
            $json['message'] = 'success';
            $json = json_encode($json);
            echo $json;
            return false;
        }
    }

    //用户基本信息
    public function informationAction()
    {
//        $ut = $this->isLogin();
        $userId = $this->getRequest()->getPost('ut');
        // file_put_contents('d:./d.txt',$userId);
//        if ($ut == -1) {
//            echo '用户未登录<br>';
//            return false;
//        } else {
        $ut = self::isLogin();
        $dbHelp = DbHelp::getInstance();
        $sql = ' ut = ?';
        $value = array($userId);
        $table = 'user';
        $user = $dbHelp->findOne($table, $sql, $value);
        // var_dump($user);exit;
        $user = $user->getProperties();
        // file_put_contents('d:d.txt',$user);

        //获得答复数
        $table = 'answer';
        $sql = ' ut = ?';
        $value = array($userId);
        $answer = $dbHelp->findAll($table, $sql, $value);
        $answercount = sizeof($answer);
        $user['answerCount'] = $answercount;


        //获得粉丝数
        $table = 'follow';
        $sql = ' followut = ?';
        $value = array($userId);
        $fans = $dbHelp->findAll($table, $sql, $value);
        $fanscount = sizeof($fans);
        $user['fansCount'] = $fanscount;
        $sql = 'followut = ? and ut = ?';
        $value = array($userId, $ut);
        $follow = $dbHelp->findOne($table, $sql, $value);
        if (!empty($follow)) $user['isFollow'] = 1;
        else $user['isFollow'] = 0;


        $json = self::baseJson();
        $json['code'] = 0;
        $json['message'] = 'success';
        $json['data'] = $user;
        $json = json_encode($json);
        // file_put_contents('d:./d.txt',$json);
        echo $json;
        return false;
//        }
    }

    //我的基本信息
    public function myInformationAction()
    {
        $ut = self::isLogin();

        //用户未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = "未登录";
            $json = json_encode($json);
            echo $json;
            return false;
        } //用户已经登录
        else {
            $dbHelp = DbHelp::getInstance();
            $table = 'user';
            $sql = 'ut = ?';
            $value = array($ut);
            $user = $dbHelp->findOne($table, $sql, $value);
            $user = $user->getProperties();

            if (sizeof($user) != 0) {
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = 'success';
                $json['data'] = $user;
                $json = json_encode($json);
                // file_put_contents('d:a.txt',$json);
                echo $json;
                return false;
            } //没有数据
            else {
                $json = self::baseJson();
                $json['code'] = -1;
                $json['message'] = "没有用户数据";
                $json = json_encode($json);
                echo $json;
                return false;
            }
        }
    }

    //生成随机验证码
    public function codeAction()
    {
        echo $this->getCode(4, 60, 20);
        return false;
    }

    //添加收藏和取消收藏
    public function collectAction()
    {
        $ut = self::isLogin();
        //未登录
        if ($ut == -1) {
            $json = self::baseJson();
            $json['code'] = -1;
            $json['message'] = '未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        } //已经登录
        else {
            $dbHelp = DbHelp::getInstance();
            $request = $this->getRequest();
            $answerid = $request->getPost('answerId');
            $table = 'collect';
            $sql = ' answerid = ? and ut = ?';
            $value = array($answerid, $ut);
            $collect = $dbHelp->findOne($table, $sql, $value);

            //已经收藏了该问题，现在就是取消收藏
            //删除相应信息
            if (!empty($collect)) {
                $dbHelp->trash($collect);
                $json = self::baseJson();
                $json['code'] = 0;
                $json['message'] = '取消收藏';
                $json = json_encode($json);
                echo $json;
                return false;
            } //添加收藏
            else {
                $collect = $dbHelp->dispense('collect');
                $collect->answerid = $answerid;
                $collect->ut = $ut;
                $result = $dbHelp->store($collect);
                if ($result > 0) {
                    $json = self::baseJson();
                    $json['code'] = 0;
                    $json['message'] = '收藏成功';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                } else {
                    $json = self::baseJson();
                    $json['code'] = -1;
                    $json['message'] = '收藏失败';
                    $json = json_encode($json);
                    echo $json;
                    return false;
                }
            }
        }
    }

    //查询登录用户的相关信息
    function queryAction()
    {
        $ut = UserController::isLogin();
        if ($ut == -1) {
            $json = UserController::baseJson();
            $json['code'] = -1;
            $json['message'] = '用户未登录';
            $json = json_encode($json);
            echo $json;
            return false;
        }
        $dbHelp = DbHelp::getInstance();
        $request = $this->getRequest();
        $basePrice = $request->getPost('basePrice');
        $addPrice = $request->getPost('addPrice');
        $content = $request->getPost('content');
        $answerut = $request->getPost('answerut');
        $table = 'user';
        $sql = 'ut = ?';
        $value = array($ut);
        $user = $dbHelp->findOne($table, $sql, $value);
        $amount = $basePrice + $addPrice;

        $session=Yaf_Session::getInstance();
        //"basePrice": basePrice, "addPrice": addPrice
        //, "content": content, "answerut": answerut
        //将总价 问题  答题人  存入SESSION；
        $session->set("basePrice", $basePrice);
        $session->set("addPrice", $addPrice);
        $session->set("amount", $amount);
        $session->set("content", $content);
        $session->set("answerut", $answerut);
        //取出SESSION;
        $a=$session->get('answerut');
        file_put_contents("d:a.txt",$a);


        if (empty($user)) {
            $json = UserController::baseJson();
            $json['code'] = -1;
            $json['message'] = '没有找到用户信息';
            $json = json_encode($json);
            echo $json;
            return false;
        }
        $user = $user->getProperties();
        $json = UserController::baseJson();
        $json['code'] = 0;
        $json['message'] = 'success';
        $json['data'] = $user;
        $json = json_encode($json);
        echo $json;
        return false;

    }

    function getCode($num, $w, $h)
    {
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }
        //将生成的验证码写入session，备验证页面使用
        Yaf_Session::getInstance()->set('code', $code);
        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im = imagecreate($w, $h);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);

        //画背景
        imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
        //画边框
//        imagerectangle($im, 0, 0, $w-1, $h-1, $gray);
        //imagefill($im, 0, 0, $bgcolor);


        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $num; $i++) {
            $strpos = rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 14);
        }
        imagepng($im);
        imagedestroy($im);
    }

    //生成随机数
    public function generateExternalId($length)
    {
        $pattern = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 61)};    //生成php随机数
        }
        return $key;
    }

    //判断用户是否登录
    public static function isLogin()
    {
        if (Yaf_Session::getInstance()->has('ut')) {
            $ut = Yaf_Session::getInstance()->get('ut');
            return $ut;
        } else {
            return -1;
        }
    }

    //输出的基础Json的样式
    public static function baseJson()
    {
        $result = array();
        $result['code'] = 0;
        $result['message'] = '';
        $result['data'] = array();
        return $result;
    }

    //生成的用户密码
    public function generatorPassword($password)
    {
        $password = $password . 'uSe5Pa';
        $password = md5($password);
        return $password;
    }

    public static function getSession($name)
    {
        if (Yaf_Session::getInstance()->has($name)) {
            return Yaf_Session::getInstance()->get($name);
        } else {
            return -1;
        }
    }

}