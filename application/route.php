<?php

use \think\Route;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::post([

    '/addStudent' => 'index/index/addStudent',
    '/modifyStudent' => 'index/index/modifyStudent',
    '/deleteStudent' => 'index/index/deleteStudent',

    '/addCoach' => 'index/index/addCoach',
    '/modifyCoach' => 'index/index/modifyCoach',
    '/deleteCoach' => 'index/index/deleteCoach',

    '/savePlan' => 'index/index/savePlan',
    '/signin' => 'index/auth/signin',
    '/changePass' => 'index/index/changePass',








    '/api/login' => 'index/user/login',
    'api/userinfo' => 'index/user/setUserInfo',
    '/api/test' => 'index/user/test',
    'api/plan' => 'index/coach/addCourse',
    'api/change_pass' => 'index/user/changePassword'
]);

Route::get([
    '/' => 'index/index/index',
    '/menu' => 'index/index/menu',
    '/student' => 'index/index/student',
    '/coach' => 'index/index/coach',
    '/plan' => 'index/index/plan',
    '/signin' => 'index/auth/signin',
    '/signout' => 'index/auth/signout',
    '/password' => 'index/index/password',
    'h5' => 'index/app/h5',






    'api/userinfo' => 'index/user/getUserInfo',
    'api/coaches' => 'index/coach/getCoachList',
    'api/plan/:id' => 'index/coach/getPlan',
    'api/courses/:type' => 'index/user/getCourseList',

]);

