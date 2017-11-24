<?php
namespace app\index\controller;

use app\index\model;
use think\Request;

class User extends Base
{

    public function login()
    {
        $username = $this->input['username'];
        $password = $this->input['password'];
        $user = model\User::check($username, $password);

        if($user) {
            $data = ['token' => sha1(time() . rand()), 'timeout' => time() + 7200];
            $user->save($data);
            return $this->json($data);
        } else {
            return $this->json([], 1000, '密码错误！');
        }
    }

    public function getUserInfo() {
        $user = $this->auth();
        return $this->json([
            'nick' => $user->nick,
            'age' => $user->age,
            'addr' => $user->addr
        ]);
    }

    public function setUserInfo() {
        $user = $this->auth();
        $info = $this->input;
        if($user) {
            $user->save([
                'nick' => $info['nick'],
                'age' => $info['age'],
                'addr' => $info['addr']
            ]);
            return $this->json([]);
        } else {
            return $this->json([], 1001, '身份验证失败');
        }
    }

    public function changePassword() {
        $user = $this->auth();
        if(!$user) return $this->json([], 3001, '请先登录！');
        $user = model\User::check($user->username, $this->input['old_pass']);
        if(!$user) return $this->json([], 3001, '旧密码不正确！');
        $new_pass = $this->input['new_pass'];
        if(strlen($new_pass) < 4)
            return $this->json([], 3001, '新密码长度太短了！');
        $user->save(['password'=> sha1($new_pass)]);
        return $this->json([], 3001, '修改成功！');
    }

    public function getCourseList($type) {
        $user = $this->auth();
        $courses = model\Course::where(['user_id' => $user->id])->order(['year', 'month', 'date'], 'desc')->select();
        $ret = [];
        foreach ($courses as $course) {
            $time = mktime(0, 0, 0, $course->month, $course->date, $course->year) + $course->start;
            if($type == 0) {
                if($time > time()) {
                    $coach = model\User::where(['id' => $course->coach_id])->find();
                    if($coach) {
                        $ret[] = [
                            'year' => $course->year,
                            'month' => $course->month,
                            'date' => $course->date,
                            'start' => $course->start,
                            'end' => $course->end,
                            'coach_nick' => $coach->nick,
                            'coach_mobile' => $coach->username,
                            'user_nick' => $user->nick,
                            'user_mobile' => $user->username
                        ];
                    }
                }
            } else {
                if($time <= time()) {
                    $coach = model\User::where(['id' => $course->coach_id])->find();
                    if($coach) {
                        $ret[] = [
                            'year' => $course->year,
                            'month' => $course->month,
                            'date' => $course->date,
                            'start' => $course->start,
                            'end' => $course->end,
                            'coach_nick' => $coach->nick,
                            'coach_mobile' => $coach->username,
                            'user_nick' => $user->nick,
                            'user_mobile' => $user->username
                        ];
                    }
                }
            }
        }
        return $this->json(['data' =>$ret]);
    }
}
