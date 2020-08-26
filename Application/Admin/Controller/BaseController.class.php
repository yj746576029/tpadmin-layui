<?php

namespace Admin\Controller;

use Think\Controller;

class BaseController extends Controller
{

    public function _empty($name)
    {
        //空操作
        $this->display('/404');
    }

    public function _initialize()
    {
        
        // $this->checkLogin();
        $this->assign('sidebar', create_menu());
        // $this->checkAuth();
    }

    /**
     * 校验登录状态
     *
     * @return void
     */
    // private function checkLogin()
    // {
    //     if (!session('?user')) {
    //         $this->redirect('admin/login/index');
    //         die;
    //     }
    //     $this->assign('menu', create_menu());
    // }

    /**
     * 校验操作权限
     *
     * @return void
     */
    // public function checkAuth()
    // {
    //     // $module = MODULE_NAME;
    //     $controller = strtolower(CONTROLLER_NAME);
    //     $action = strtolower(ACTION_NAME);
    //     $authArr = auth_list();
        
    //     $permArr = [
    //         'c' => false, //控制器是否有权限
    //         'a' => false, //方法是否有权限
    //     ];
    //     foreach ($authArr as $v) {
    //         if ($v['rule'] === $controller) {
    //             $permArr['c'] = true;
    //         }
    //         if ($v['rule'] === $controller . '/' . $action) {
    //             $permArr['a'] = true;
    //         }
    //     }
    //     if (!($permArr['c'] || $permArr['a'])) {
    //         $noCheckArr=['index/index'];//忽略校验的控制器/方法
    //         if(!in_array($controller . '/' . $action,$noCheckArr)){
    //             $this->error('您没有权限');die;
    //         }
    //     }
    // }
}
