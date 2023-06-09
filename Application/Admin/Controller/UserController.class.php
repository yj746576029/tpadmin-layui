<?php

namespace Admin\Controller;

use Admin\Controller\BaseController;

class UserController extends BaseController
{
    public function index()
    {
        if(I('get.render')){
            $condition['is_super'] = 0;
            $page = empty(I('get.page')) ? 1 : I('get.page');
            $pageSize = empty(I('get.limit')) ? 15 : I('get.limit');
            if (!empty(I('get.user_name'))) {
                $condition['user_name'] = I('get.user_name');
            }
            if (!empty(I('get.mobile'))) {
                $condition['mobile'] = I('get.mobile');
            }
            if (!empty(I('get.email'))) {
                $condition['email'] = I('get.email');
            }
            $count=M('User')->where($condition)->count();
            $list = D('User')->relation(true)->where($condition)->order('id desc')->page($page . ',' . $pageSize)->select();
            foreach($list as $k=>$v){
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
                $roleArr=[];
                foreach($v['role'] as $vv){
                    array_push($roleArr,$vv['role_name']);
                }
                $list[$k]['role']=implode('|',$roleArr);
            }
            $this->ajaxReturn(['code'=>0,'msg'=>'','count'=>$count,'data'=>$list]);
        }
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
                    $this->ajaxReturn(['code'=>0,'msg'=>'添加成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['code'=>1,'msg'=>'添加失败']);
                }
            } else {
                M()->rollback();
                $this->ajaxReturn(['code'=>1,'msg'=>'添加失败']);
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
            $id = I('post.id');
            $data['user_name'] = I('post.user_name');
            if (!empty(I('post.password'))) {
                $user=M('User')->field('salt')->where(['id' => $id])->find();
                $data['password'] = md5(md5(I('post.password')) . $user['salt']);
            }
            $data['mobile'] = I('post.mobile');
            $data['email'] = I('post.email');
            $data['update_time'] = time();
            $data['is_super'] = 0;
            M()->startTrans();
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
                    $this->ajaxReturn(['code'=>0,'msg'=>'编辑成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['code'=>1,'msg'=>'编辑失败']);
                }
            } else {
                M()->rollback();
                $this->ajaxReturn(['code'=>1,'msg'=>'编辑失败']);
            }
        } else {
            $id = I('get.id');
            $user = D('User')->relation(true)->where(['id' => $id])->find();
            $role_ids = [];
            foreach ($user['role'] as $v) {
                array_push($role_ids, $v['id']);
            }
            $user['role_ids'] = $role_ids;
            $roleList = M('Role')->field('id,role_name')->where(['status' => 1])->select();
            $this->assign('list', $roleList);
            $this->assign('item', $user);
            $this->display();
        }
    }

    public function del()
    {
        $id = I('post.id');
        M()->startTrans();
        $res = M('User')->where(['id' => $id])->delete();
        if ($res) {
            D('UserRole')->where(['user_id' => $id])->delete();
            M()->commit();
            $this->ajaxReturn(['code'=>0,'msg'=>'删除成功']);
        } else {
            M()->rollback();
            $this->ajaxReturn(['code'=>1,'msg'=>'删除失败']);
        }
    }

    public function batchdel()
    {
        $ids = I('post.ids');
        M()->startTrans();
        $res = M('User')->where(['id' => ['IN',$ids]])->delete();
        if ($res) {
            D('UserRole')->where(['user_id' => ['IN',$ids]])->delete();
            M()->commit();
            $this->ajaxReturn(['code'=>0,'msg'=>'删除成功']);
        } else {
            M()->rollback();
            $this->ajaxReturn(['code'=>1,'msg'=>'删除失败']);
        }
    }
}
