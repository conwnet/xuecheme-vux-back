<?php
namespace app\index\controller;

use app\index\model;
use think\Request;

class Coach extends Base
{

    public function login() {
        $username = $this->input['username'];
        $password = $this->input['password'];
        $user = model\User::check($username, $password);

        if($user) {
            $data = ['token' => sha1(time() . rand()), 'timeout' => time() + 7200];
            $user->save($data);
            return $this->json($data);
        } else {
            return $this->json([], '1000', '密码错误！');
        }
    }

    public function getCoachList() {
        $user = $this->auth();
        if($user) {
            if($user->role == 0)
                $coaches = model\User::where(['role' => 1, 'username' => $user->coach_mobile])->select();
            else $coaches = [$user];
        } else {
            $coaches = model\User::where(['role' => 1])->select();
        }
        $ret = [];
        foreach($coaches as $coach) {
            $ret[] = [
                'src'=> 'http://placehold.it/80x80',
                'title' => $coach->nick ? $coach->nick : '教练',
                'desc' => '快来预约吧~让我看看你是不是老司机！',
                'url' => '/coaches/' . $coach->id
            ];
        }
        return $this->json([ 'coaches' => $ret ]);
    }

    public function getPlan($id) {
        $ret = model\Plan::find();
        $plan = $ret ? json_decode($ret->val, true) : [];
        $courses = [];
        $time = time() + 86400;
        $year = date('Y', $time);
        $month = date('m', $time);
        $date = date('d', $time);
        $cs = model\Course::where(['year' => $year, 'month' => $month, 'date' => $date, 'coach_id' => $id])->select();
        $len = ($cs ? count($cs) : 0);
        foreach ($plan as $course) {
            $start = $course['start_hour'] * 3600 + $course['start_min'] * 60;
            $end = $course['end_hour'] * 3600 + $course['end_min'] * 60;
            $found = false;
            for($i = 0; $i < $len; $i++) {
                if($start == $cs[$i]->start && $end == $cs[$i]->end) { $found = true; break; }
            }
            $courses[] = [
                'start' => $start,
                'end' => $end,
                'status' => $found
            ];
        }
        return $this->json(['data' => $courses]);
    }

    public function addCourse () {
        $user = $this->auth();
        if(!$user) return $this->json([], 2001, '请先登录');
        $start = $this->input['start'];
        $end = $this->input['end'];
        $coach_id = $this->input['coach_id'];
        $coach = model\User::where(['username' => $user->coach_mobile])->find();
        if(!$coach || $coach_id != $coach->id)
            return $this->json([], 2002, '你没有权限预约这位教练哦');
        $time = time() + 86400;
        $course = model\Course::where([
            'coach_id' => $coach_id,
            'user_id' => $user->id,
            'year' => date('Y', $time),
            'month' => date('m', $time),
            'date' => date('d', $time)])->find();
        if($course) return $this->json([], 2003, '您当天已经有预约的课程了');
        $course = model\Course::where([
            'coach_id' => $coach_id,
            'start' => $start,
            'end' => $end,
            'year' => date('Y', $time),
            'month' => date('m', $time),
            'date' => date('d', $time)])->find();
        if($course) return $this->json([], 2003, '您手慢啦，这节课已经被预约');
        model\Course::create([
            'user_id' => $user->id,
            'coach_id' => $coach_id,
            'start' => $start,
            'end' => $end,
            'year' => date('Y', $time),
            'month' => date('m', $time),
            'date' => date('d', $time)])->find();
        return $this->json([], 2004, '预约成功！');
    }
}
