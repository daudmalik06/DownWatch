<?php
/**
 * Created by PhpStorm.
 * User: dawood.ikhlaq
 * Date: 02/04/2019
 * Time: 15:29
 */



include 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();



$watch = new \DownWatch\Watch();
try{
    $watch->work();
}catch (Exception $exception)
{
    echo 'Following error occured'.PHP_EOL;
    echo $exception->getMessage();
}
