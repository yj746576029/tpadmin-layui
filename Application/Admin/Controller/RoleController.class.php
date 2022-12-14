<?php

namespace Admin\Controller;

use Admin\Controller\BaseController;

class RoleController extends BaseController
{
    public function index()
    {
        if (I('get.render')) {
            $page = empty(I('get.page')) ? 1 : I('get.page');
            $pageSize = empty(I('get.limit')) ? 15 : I('get.limit');
            $count = M('Role')->count();
            $list = D('Role')->relation(true)->order('id desc')->page($page . ',' . $pageSize)->select();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
            $data['code'] = 0;
            $data['msg'] = '';
            $data['count'] = $count;
            $data['data'] = $list;
            $this->ajaxReturn($data);
        }
        $this->display();
    }

    public function add()
    {
        if (IS_POST) {
            $data['role_name'] = I('post.role_name');
            $data['status'] = intval(I('post.status'));
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            M()->startTrans();
            $re = D('Role')->add($data);
            if ($re) {
                $auth_ids = I('post.auth_ids');
                $dataList = [];
                foreach ($auth_ids as $v) {
                    $item = array('role_id' => $re, 'auth_id' => $v);
                    array_push($dataList, $item);
                }
                $res = D('RoleAuth')->addAll($dataList);
                if ($res) {
                    M()->commit();
                    $this->ajaxReturn(['code' => 0, 'msg' => '添加成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['code' => 1, 'msg' => '添加失败']);
                }
            } else {
                M()->rollback();
                $this->ajaxReturn(['code' => 1, 'msg' => '添加失败']);
            }
        } else {
            $authList = M('Auth')->field('id,auth_name as title,parent_id')->select();
            foreach ($authList as $k => $v) {
                $authList[$k]['field'] = 'auth_ids[]';
            }
            $list = list_to_tree($authList);
            $this->assign('list', json_encode($list));
            $this->display();
        }
    }

    public function edit()
    {
        if (IS_POST) {
            $id = I('post.id');
            if (I('post.type') == 'status') {
                $data['status'] = I('post.status');
                $data['update_time'] = time();
                $re = M('Role')->where(['id' => $id])->save($data);
                if ($re) {
                    M()->commit();
                    $this->ajaxReturn(['code' => 0, 'msg' => $data['status'] == 1 ? '开启成功' : '关闭成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['code' => 1, 'msg' => $data['status'] == 1 ? '开启失败' : '关闭失败']);
                }
            }
            $data['role_name'] = I('post.role_name');
            $data['status'] = I('post.status');
            $data['update_time'] = time();
            M()->startTrans();
            $re = M('Role')->where(['id' => $id])->save($data);
            if ($re) {
                D('RoleAuth')->where(['role_id' => $id])->delete();
                $auth_ids = I('post.auth_ids');
                $dataList = [];
                foreach ($auth_ids as $v) {
                    $item = array('role_id' => $id, 'auth_id' => $v);
                    array_push($dataList, $item);
                }
                $res = D('RoleAuth')->addAll($dataList);
                if ($res) {
                    M()->commit();
                    $this->ajaxReturn(['code' => 0, 'msg' => '编辑成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['code' => 1, 'msg' => '编辑失败']);
                }
            } else {
                M()->rollback();
                $this->ajaxReturn(['code' => 1, 'msg' => '编辑失败']);
            }
        } else {
            $id = I('get.id');
            $role = D('Role')->relation(true)->where(['id' => $id])->find();
            $auth_ids = [];
            foreach ($role['auth'] as $v) {
                $v['level'] == 3 ? array_push($auth_ids, $v['id']) : '';
            }
            $role['auth_ids'] = $auth_ids;
            $authList = M('Auth')->field('id,auth_name as title,parent_id')->select();
            foreach ($authList as $k => $v) {
                $authList[$k]['field'] = 'auth_ids[]';
            }
            $list = list_to_tree($authList);
            $this->assign('list', json_encode($list));
            $this->assign('auth_ids', json_encode($role['auth_ids']));
            $this->assign('item', $role);
            $this->display();
        }
    }

    public function del()
    {
        $id = I('post.id');
        M()->startTrans();
        $res = M('Role')->where(['id' => $id])->delete();
        if ($res) {
            D('RoleAuth')->where(['role_id' => $id])->delete();
            M()->commit();
            $this->ajaxReturn(['code' => 0, 'msg' => '删除成功']);
        } else {
            M()->rollback();
            $this->ajaxReturn(['code' => 1, 'msg' => '删除失败']);
        }
    }
}
