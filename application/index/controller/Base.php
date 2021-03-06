<?php
namespace app\index\controller;

use think\Controller;
use app\index\model;
use think\Request;

class Base extends Controller
{

    protected $input = null;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        $this->input = json_decode(file_get_contents('php://input'), true);
    }

    public function json($body, $errcode = 0, $errmsg = 'ok') {
        $body['errcode'] = $errcode;
        $body['errmsg'] = $errmsg;
        return json_encode($body);
    }

    public function getUser() {
        $token = Request::instance()->header('x-token');
        $user = model\User::where('token', $token)->find();
        if($user && time() < $user->timeout + 10) {
            return $user;
        } else return null;
    }

    public function auth() {
        $request = Request::instance();
        $token = $request->header('token');
        $user = model\User::get(['token' => $token]);
        return ($user && $user->timeout > time()) ? $user : null;
    }
}
