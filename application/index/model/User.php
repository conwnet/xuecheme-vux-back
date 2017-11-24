<?php

namespace app\index\model;

use think\Model;

class User extends Model
{
    //
    public static function check($username, $password) {
        $user = self::where('username', $username)->find();
        if($user && sha1($password) == $user->password) {
            return $user;
        } else {
            return null;
        }
    }

}
