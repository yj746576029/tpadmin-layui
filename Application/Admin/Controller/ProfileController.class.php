<?php

namespace Admin\Controller;

use Admin\Controller\BaseController;

class ProfileController extends BaseController
{
    public function index()
    {
        $user = session('user');
        $user = M('User')->where(['id' => $user['id']])->find();
        unset($user['password'], $user['salt']);
        $this->assign('user', $user);
        $this->display();
    }

    public function edit()
    {
        if (IS_POST) {
            $id = I('post.id');
            $data['realname'] = I('post.realname');
            $data['mobile'] = I('post.mobile');
            $data['email'] = I('post.email');
            $re = M('User')->where(['id' => $id])->save($data);
            if ($re) {
                $data['code']=0;
                $data['msg']='修改成功';
                $this->ajaxReturn($data);
            } else {
                $data['code']=1;
                $data['msg']='修改失败';
                $this->ajaxReturn($data);
            }
        }
    }

    public function password()
    {
        $user = session('user');
        $this->assign('user', $user);
        $this->display();
    }

    public function passwordEdit()
    {
        if (IS_POST) {
            $id = I('post.id');
            $password = I('post.password');
            $passwordNew = I('post.passwordNew');
            $repasswordNew = I('post.repasswordNew');
            if($passwordNew!==$repasswordNew){
                $data['code']=1;
                $data['msg']='两次密码不一致';
                $this->ajaxReturn($data);
            }
            $user = M('User')->where(['id' => $id])->find();
            if ($user['password'] === md5(md5($password) . $user['salt'])) {
                $data['password'] = md5(md5($passwordNew) . $user['salt']);
                $re = M('User')->where(['id' => $id])->save($data);
                if ($re) {
                    session('user', null);
                    $data['code']=0;
                    $data['msg']='修改成功，请重新登录';
                    $this->ajaxReturn($data);
                } else {
                    $data['code']=1;
                    $data['msg']='修改失败';
                    $this->ajaxReturn($data);
                }
            } else {
                $data['code']=1;
                $data['msg']='原始密码错误';
                $this->ajaxReturn($data);
                $this->error('原始密码错误');
            }
        }
    }
}
