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

        $this->checkLogin();
        $this->assign('sidebar', create_menu());
        $this->checkAuth();
    }

    /**
     * 校验登录状态
     *
     * @return void
     */
    private function checkLogin()
    {
        if (!session('?user')) {
            $this->redirect('Admin/Login/index');
            die;
        }
        $this->assign('menu', create_menu());
    }

    /**
     * 校验操作权限
     *
     * @return void
     */
    public function checkAuth()
    {
        // $module = MODULE_NAME;
        $controller = CONTROLLER_NAME;
        $action = ACTION_NAME;
        $authArr = auth_list();

        $authArrNew = [];
        foreach ($authArr as $v) {
            $v['level'] == 3 ? array_push($authArrNew, $v['rule']) : '';
        }

        //忽略校验的“控制器/方法”
        $noCheckArr = [
            'Index/index',
            'Index/console'
        ];
        // print_r($authArrNew);die;

        $authAll = array_merge($authArrNew, $noCheckArr); //所有允许访问的“控制器/方法”

        if (!in_array($controller . '/' . $action, $authAll)) {
            if(IS_POST){
                $this->ajaxReturn(['code'=>1,'msg'=>'没有权限']);
            }else{
                $this->display('/noPermission');
            }
            die;
        }
    }
}
