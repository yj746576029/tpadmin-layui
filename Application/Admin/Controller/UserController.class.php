<?php

namespace Admin\Controller;

use Admin\Controller\BaseController;

class UserController extends BaseController
{
    public function index()
    {
        $condition['is_super'] = 0;
        if (!empty(I('get.start_time'))) {
            $s = strtotime(I('get.start_time'));
            $condition['create_time'] = array('egt', $s);
            $this->assign('start_time', I('get.start_time'));
        }
        if (!empty(I('get.end_time'))) {
            $e = strtotime(I('get.end_time'));
            $condition['end_time'] = array('elt', $e);
            $this->assign('end_time', I('get.end_time'));
        }
        if (!empty(I('get.keywords'))) {
            $condition['user_name'] = array('like', '%' . I('get.keywords') . '%');
            $this->assign('keywords', I('get.keywords'));
        }
        $list = D('User')->relation(true)->where($condition)->select();
        $this->assign('empty', '<tr class="text-c"><td colspan="9">数据为空</td></tr>');
        $this->assign('list', $list);
        $this->display();
    }

    public function add()
    {
        if (IS_POST) {
            $data['user_name'] = I('post.user_name');
            $data['salt'] = substr(md5(uniqid(true)), 0, 4);
            if (!empty(I('post.password'))) {
                $data['password'] = md5(md5(I('post.password')) . $data['salt']);
            } else {
                $data['password'] = md5(md5('123456') . $data['salt']);
            }
            $data['mobile'] = I('post.mobile');
            $data['email'] = I('post.email');
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            $data['is_super'] = 0;
            M()->startTrans();
            $re = D('User')->add($data);
            if ($re) {
                $role_ids = I('post.role_ids');
                $dataList = [];
                foreach ($role_ids as $v) {
                    $item = array('user_id' => $re, 'role_id' => $v);
                    array_push($dataList, $item);
                }
                $res = D('UserRole')->addAll($dataList);
                if ($res) {
                    M()->commit();
                    $this->success('新增成功', U('admin/user/index'));
                } else {
                    M()->rollback();
                    $this->error('新增失败');
                }
            } else {
                M()->rollback();
                $this->error('新增失败');
            }
        } else {
            $roleList = M('Role')->field('id,role_name')->where(['status' => 1])->select();
            $this->assign('list', $roleList);
            $this->display();
        }
    }

    public function edit()
    {
        if (IS_POST) {
            M()->startTrans();
            $id = I('post.id');
            $data['user_name'] = I('post.user_name');
            if (!empty(I('post.password'))) {
                $data['salt'] = substr(md5(uniqid(true)), 0, 4);
                $data['password'] = md5(md5(I('post.password')) . $data['salt']);
            }
            $data['mobile'] = I('post.mobile');
            $data['email'] = I('post.email');
            $data['update_time'] = time();
            $data['is_super'] = 0;
            $re = M('User')->where(['id' => $id])->save($data);
            if ($re) {
                D('UserRole')->where(['user_id' => $id])->delete();
                $role_ids = I('post.role_ids');
                $dataList = [];
                foreach ($role_ids as $v) {
                    $item = array('user_id' => $id, 'role_id' => $v);
                    array_push($dataList, $item);
                }
                $res = D('UserRole')->addAll($dataList);
                if ($res) {
                    M()->commit();
                    $this->success('编辑成功', U('admin/user/index'));
                } else {
                    M()->rollback();
                    $this->error('编辑失败');
                }
            } else {
                M()->rollback();
                $this->error('编辑失败');
            }
        } else {
            $id = I('get.id');
            $user = D('User')->relation(true)->where(['id' => $id])->find();
            $role_ids = [];
            foreach ($user['role'] as $v) {
                array_push($role_ids, $v['id']);
            }
            $user['role'] = $role_ids;
            $roleList = M('Role')->field('id,role_name')->where(['status' => 1])->select();
            $this->assign('list', $roleList);
            $this->assign('item', $user);
            $this->display();
        }
    }

    public function del()
    {
        $id = I('get.id');
        M()->startTrans();
        $res = M('User')->where(['id' => $id])->delete();
        if ($res) {
            D('UserRole')->where(['user_id' => $id])->delete();
            M()->commit();
            $this->success('删除成功', U('admin/user/index'));
        } else {
            M()->rollback();
            $this->error('删除失败');
        }
    }
}
