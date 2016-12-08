<?php
/**
 * Created by PhpStorm.
 * user: len
 * Date: 2016/11/11
 * Time: 10:23
 */
require_once APP_PATH . "/application/library/".'rb.php';

class DbHelp
{

    private static $instance = null;
    private function __construct()
    {
        R::setup('mysql:host=localhost;dbname=youdi', 'root', 'root');
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

//    //私有克隆函数，防止外办克隆对象
//    private function __clone() {
//    }

    public function findOne($table, $sql, $value)
    {
        $result = R::findOne($table, $sql, $value);
        return ($result);
    }

    public function store($result)
    {
        $result = R::store($result);
        return $result;
    }

    public function getAssoc($sql, $value = '')
    {
        $result = R::getAssoc($sql, $value);
        return $result;
    }

    public function load($table, $id)
    {
        $result = R::load($table, $id);
        return $result;
    }

    public function exec($sql, $value = '')
    {
        $result = R::exec($sql, $value);
        return $result;
    }

    public function dispense($table)
    {
        $column = R::dispense($table);
        return $column;
    }

    public function getAll($sql, $value)
    {
        $result = R::getAll($sql, $value);
        return $result;
    }

    public function find($table, $sql = '', $value = ''){
        $result = R::find($table, $sql, $value);
        return $result;
    }
    public function findAll($table, $sql = '', $value = '')
    {
        $result = R::findAll($table, $sql, $value);
        return $result;
    }
    public function trash($bean){
        $result = R::trash($bean);
        return $result;
    }
}


//dispense()
//$post = R::dispense( 'post' );
//$post->title = 'My holiday';
//$id = R::store( $post );
//$post->name = '何强';
//$id = R::store($post);


//$post = R::dispense( 'book' );
//$post->content = 'Book Content';
//$post->name = 'Book Name2';
//$id = R::store($post);


//load()
//$post = R::load('post',1);
//echo $post->id.'<br>';
//echo $post['title'].'<br>';


//find()
//$post = R::find(
//    'post', ' title LIKE ?', ['%Holiday%'] );
//echo '<pre>';
//var_dump($post);
//
//$bean = R::convertToBean('post',$post);
//var_dump($bean);


//getAll()
//$books = R::getAll(
//    'SELECT * FROM post WHERE id < ? ',
//    [ 2 ] );
//echo '<pre>';
//var_dump($books);
//echo $books[0]['title'];


//findAll
//$post = R::findAll('post');
//foreach($post as $value){
//    echo $value->title.'<br>';
//    echo $value->name.'<br>';
//}


//findOne()
//$one = R::findOne('post','id = ?',[1]);
//echo '<pre>';
//var_dump($one);

//$one = R::findOne('post','id = :ID',[':ID' => 1]);
//echo '<pre>';
//var_dump($one);


//findMulti()
//$beans = R::findMulti( 'book,post', '
//        SELECT book.*, post.* FROM book
//        INNER JOIN book.id = post.id
//        WHERE book.content = ?
//    ', [ 'Book Content'] );
//echo '<pre>';
//var_dump($beans);


//oneToMany
//$shop = R::dispense('shop');
//$shop->name = 'shop';
//$product = R::dispense('product');
//$product->price = 25;
//$shop->ownProductList[] = $product;
//R::store($shop);