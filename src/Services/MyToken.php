<?php

/**
 * Created by PhpStorm.
 * User: guillaume
 * Date: 01/11/16
 * Time: 13:44
 */
namespace App\Services;

use Silex\Application;

class MyToken {


    public function __construct(Application $app) {
        $this->init_token();
        $app['twig']->addGlobal('token', $this->getToken());
    }


    public function init_token(){
        $_SESSION['token'] = uniqid(rand(), true);
        $_SESSION['token_time'] =time();
    }


    public static function verif_token() {
        if (isset($_SESSION['token']) && isset($_SESSION['token_time']) && isset($_POST['token'])){
            if ($_SESSION['token'] == $_POST['token']){
                if ($_SESSION['token'] == $_POST['token']) {
                    $timestamp_ancien = time() - (30 * 60);
                    if ($_SESSION['token_time'] >= $timestamp_ancien){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getToken() {
        return $_SESSION['token'];
    }
}