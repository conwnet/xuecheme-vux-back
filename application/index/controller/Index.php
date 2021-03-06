<?php
/**
 * Created by PhpStorm.
 * User: netcon
 * Date: 17-10-4
 * Time: 下午10:41
 */

namespace app\index\controller;

use app\index\model;

class Index extends Auth
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        if(!session('id')) {
            $this->redirect('/signin');
        }
    }

    public function index() {
        return $this->fetch();
    }

    public function menu () {
        return $this->fetch();
    }

    public function student() {

        $page = input('page', 0);
        $search = input('search', null);
        $count = model\User::where('role', 0)->count();
        if($page < 0 || $page > $count / 10) $page = 0;
        $m = model\User::where("role", 0);
        if($search) $m = $m->where('username', 'like', '%' . $search . '%');
        $rets = $m->limit($page * 10, 10)->select();
        $students = [];
        foreach($rets as $k => $v) {
            $students[] = [
                'idx' => $k + 1,
                'coach_mobile' => $v->coach_mobile,
                'id' => $v['id'],
                'username' => $v['username']
            ];
        }

        $this->assign('count', $count);
        $this->assign('students', $students);
        $this->assign('page', $page);

        return $this->fetch();
    }

    public function getCourseList() {
        $id = input('id');
        $user = model\User::where('role', 0)->where('id', $id)->find();
        if(!$user) return 'User Not Found';
        $courses = model\Course::where(['user_id' => $user->id])->order(['year', 'month', 'date'], 'desc')->select();
        $ret = [];
        foreach ($courses as $course) {
            $time = mktime(0, 0, 0, $course->month, $course->date, $course->year) + $course->start;
            if ($time > time()) {
                $ret[] = [
                    'year' => $course->year,
                    'month' => $course->month,
                    'date' => $course->date,
                    'start' => $course->start,
                    'end' => $course->end
                ];
            }
        }
        return json($ret);
    }

    public function addStudent() {
        $username = input("mobile");
        $password = input('password');
        $coach_mobile = input('coach_mobile');
        if(!preg_match("/^1[34578]{1}\d{9}$/", $username))
            return '学员手机号码格式不正确！';
        if(strlen($password) < 6)
            return '密码格式不正确';
        if(model\User::where('username', $username)->find())
            return '此用户已存在！';
        if(!preg_match("/^1[34578]{1}\d{9}$/", $coach_mobile))
            return '教练手机号码格式不正确！';
        $coach = model\User::where('username', $coach_mobile)->where('role', 1)->find();
        if(!$coach)
            return '该教练不存在！';
        model\User::create([
            'username' => $username,
            'password' => sha1($password),
            'coach_mobile' => $coach_mobile,
            'role' => 0
        ]);
        return'ok';
    }

    public function modifyStudent() {
        $id = input('id');
        $password = input('password');
        if(strlen($password) < 6)
            return '密码格式不正确';
        $user = model\User::where('role', 0)->where('id', $id)->find();
        if($user) {
            $user->save(['password' => sha1($password)]);
            return 'ok';
        } else {
            return '用户不存在';
        }        $coach = model\User::where(['username' => $user->coach_mobile])->find();

    }

    public function saveCourse() {
        $id = input('id');
        $courseList = json_decode(input('courseList'));

        $user = model\User::where('role', 0)->where('id', $id)->find();
        $coach = model\User::where(['username' => $user->coach_mobile])->find();

        $plan = model\Plan::find();
        $cs = json_decode($plan->val);

        foreach ($courseList as $item) {
            $found = false;
            foreach ($cs as $c) {
                if($c->start_hour == $item->start_hour && $c->start_min == $item->start_min
                    && $c->end_hour == $item->end_hour && $c->end_min  == $item->end_min)
                    $found = true;
            }
            if(!$found) return "保存失败， $item->start_hour:$item->start_min - $item->end_hour:$item->end_min 不在课程表中！";

            $ret = model\Course::where([
                'coach_id' => $coach->id,
                'start' => $item->start_hour * 3600 + $item->start_min * 60,
                'end' => $item->end_hour * 3600 + $item->end_min * 60,
                'year' => $item->year,
                'month' => $item->month,
                'date' => $item->date])->find();
            if($ret && $ret->user_id != $id) {
                $u = model\User::where(['id' => $ret->user_id])->find();
                if ($ret) return "保存失败， $item->start_hour:$item->start_min - $item->end_hour:$item->end_min 已经被学员 $u->username 预约了！";
            }
        }

        $courses = model\Course::where(['user_id' => $user->id])->order(['year', 'month', 'date'], 'desc')->select();
        foreach ($courses as $course) {
            $time = mktime(0, 0, 0, $course->month, $course->date, $course->year) + $course->start;
            if ($time > time()) {
                $course->delete();
            }
        }

        foreach ($courseList as $item) {
            model\Course::create([
                'user_id' => $id,
                'coach_id' => $coach->id,
                'start' => $item->start_hour * 3600 + $item->start_min * 60,
                'end' => $item->end_hour * 3600 + $item->end_min * 60,
                'year' => $item->year,
                'month' => $item->month,
                'date' => $item->date]);
        }

        return 'ok';
    }

    public function deleteStudent() {
        $id = input('id');
        $user = model\User::where('role', 0)->where('id', $id)->find();
        if($user) {
            $user->delete();
            return 'ok';
        } else {
            return '用户不存在';
        }
    }

    public function coach() {

        $page = input('page', 0);
        $search = input('search', null);
        $count = model\User::where('role', 1)->count();
        if($page < 0 || $page > $count / 10) $page = 0;
        $m = model\User::where("role", 1);
        if($search) $m = $m->where('username', 'like', '%' . $search . '%');
        $rets = $m->limit($page * 10, 10)->select();
        $coaches = [];
        foreach($rets as $k => $v) {
            $coaches[] = [
                'idx' => $k + 1,
                'id' => $v['id'],
                'username' => $v['username']
            ];
        }

        $this->assign('count', $count);
        $this->assign('coaches', $coaches);
        $this->assign('page', $page);

        return $this->fetch();
    }

    public function addCoach() {
        $username = input("mobile");
        $password = input('password');
        if(!preg_match("/^1[34578]{1}\d{9}$/", $username))
            return '手机号码格式不正确！';
        if(strlen($password) < 6)
            return '密码格式不正确';
        if(model\User::where('username', $username)->find())
            return '此用户已存在！';
        model\User::create([
            'username' => $username,
            'password' => sha1($password),
            'role' => 1
        ]);
        return'ok';
    }

    public function modifyCoach() {
        $id = input('id');
        $password = input('password');
        if(strlen($password) < 6)
            return '密码格式不正确';
        $user = model\User::where('role', 1)->where('id', $id)->find();
        if($user) {
            $user->save(['password' => sha1($password)]);
            return 'ok';
        } else {
            return '用户不存在';
        }
    }

    public function deleteCoach() {
        $id = input('id');
        $user = model\User::where('role', 1)->where('id', $id)->find();
        if($user) {
            $user->delete();
            return 'ok';
        } else {
            return '用户不存在';
        }
    }

    public function plan() {

        $ret = model\Plan::find();
        $plan = $ret ? $ret->val : '[]';
        $this->assign('plan', $plan);

        return $this->fetch();
    }

    public function savePlan() {
        $val = input('val');
        if(!$val) $val = '[]';
        $plan = model\Plan::find();
        if($plan) {
            $plan->save(['val' => $val]);
            return 'ok';
        } else {
            return '保存失败！';
        }
    }

    public function signin() {
        return $this->fetch();
    }

    public function password() {
        return $this->fetch();
    }

    public function changePass() {
        $old_pass = input('old_pass');
        $new_pass = input('new_pass');
        $re_pass = input('re_pass');

        if($new_pass != $re_pass)
            return '两次密码输入不一致';

        if(strlen($new_pass) < 5)
            return '新密码格式不正确';

        $admin = model\Admin::find(session('id'));
        if($admin->password == sha1($old_pass)) {
            $admin->save(['password' => sha1($new_pass)]);
            return 'ok';
        } else {
            return '旧密码错误！';
        }
    }

    public function modifyCoachMobile() {

        $id = input('id');
        $coach_mobile = input('coach_mobile');

        if(!preg_match("/^1[34578]{1}\d{9}$/", $coach_mobile))
            return '教练手机号码格式不正确！';

        $coach = model\User::where('username', $coach_mobile)->where('role', 1)->find();
        if(!$coach)
            return '该教练不存在！';

        $user = model\User::where('role', 0)->where('id', $id)->find();
        if($user) {
            $user->save(['coach_mobile' => $coach_mobile]);
            return 'ok';
        } else {
            return '用户不存在';
        }

    }

    public function homePage() {
        return $this->fetch();
    }
}