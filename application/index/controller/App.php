<?php
/**
 * Created by PhpStorm.
 * User: netcon
 * Date: 17-10-4
 * Time: 下午10:41
 */

namespace app\index\controller;

use app\index\model;

class App extends Auth
{
    public function h5() {

        $url  = 'http://m.jiakaobaodian.com/';
        return $this->redirect($url);
    }
}