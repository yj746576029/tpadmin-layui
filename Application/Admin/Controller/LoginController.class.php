<?php

namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller
{
    public function index()
    {
        // $salt = substr(md5(uniqid(true)), 0, 4);
        // $newPassword = md5(md5('123456') . $salt);
        if (IS_POST) {
            $userName = I('post.user_name');
            $password = I('post.password');
            $vercode = I('post.vercode');
            $user = M('User')->where(['user_name' => $userName])->find();
            if (!$user) {
                $this->ajaxReturn(['code'=>1,'msg'=>'用户不存在']);
            } else {
                if ($user['password'] === md5(md5($password) . $user['salt'])) {
                    if ($this->checkVerify($vercode)) {
                        unset($user['password'], $user['salt']);
                        session('user', $user);
                        $this->ajaxReturn(['code'=>0,'msg'=>'登录成功']);
                    } else {
                        $this->ajaxReturn(['code'=>1,'msg'=>'验证码错误']);
                    }
                } else {
                    $this->ajaxReturn(['code'=>1,'msg'=>'密码错误']);
                }
            }
        } else {
            $this->display();
        }
    }

    public function logout()
    {
        session('user', null);
        $this->ajaxReturn(['code'=>0,'msg'=>'退出成功']);
    }

    public function verify()
    {
        $verify = new \Think\Verify();
        $verify->length   = 4;
        $verify->entry();
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    private function checkVerify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}
