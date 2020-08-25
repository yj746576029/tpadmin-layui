<?php

namespace Admin\Controller;

use Admin\Controller\BaseController;

class RoleController extends BaseController
{
    public function index()
    {
        $list = M('Role')->select();
        $this->assign('empty','<tr class="text-c"><td colspan="4">数据为空</td></tr>');
        $this->assign('list', $list);
        $this->display();
    }
    
    public function add()
    {
        if (IS_POST) {
            $data['role_name'] = I('post.role_name');
            $data['status'] = I('post.status');
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            M()->startTrans();
            $re = D('Role')->add($data);
            if ($re) {
                $auth_ids = I('post.auth_ids');
                $dataList=[];
                foreach($auth_ids as $v){
                    $item=array('role_id'=>$re,'auth_id'=>$v);
                    array_push($dataList,$item);
                }
                $res = D('RoleAuth')->addAll($dataList);
                if($res){
                    M()->commit();
                    $this->success('新增成功', U('admin/role/index'));
                }else{
                    M()->rollback();
                    $this->error('新增失败');
                }
            } else {
                M()->rollback();
                $this->error('新增失败');
            }
        } else {
            $authList = M('Auth')->field('id,auth_name,status,rule,parent_id,create_time,update_time')->select();
            $list = list_to_tree($authList);
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function edit()
    {
        if (IS_POST) {
            M()->startTrans();
            if(IS_AJAX){
                $id = I('post.id');
                $data['status'] = I('post.status');
                $data['update_time'] = time();
                $re = M('Role')->where(['id' => $id])->save($data);
                if ($re) {
                    M()->commit();
                    $this->ajaxReturn(['code'=>1,'msg'=>'成功']);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(['code'=>0,'msg'=>'失败']);
                }
            }else{
                $id = I('post.id');
                $data['role_name'] = I('post.role_name');
                $data['status'] = I('post.status');
                $data['update_time'] = time();
                $re = M('Role')->where(['id' => $id])->save($data);
                if ($re) {
                    D('RoleAuth')->where(['role_id'=>$id])->delete();
                    $auth_ids = I('post.auth_ids');
                    $dataList=[];
                    foreach($auth_ids as $v){
                        $item=array('role_id'=>$id,'auth_id'=>$v);
                        array_push($dataList,$item);
                    }
                    $res = D('RoleAuth')->addAll($dataList);
                    if($res){
                        M()->commit();
                        $this->success('编辑成功', U('admin/role/index'));
                    }else{
                        M()->rollback();
                        $this->error('编辑失败');
                    }
                } else {
                    M()->rollback();
                    $this->error('编辑失败');
                }
            }
        } else {
            $id = I('get.id');
            $role = D('Role')->relation(true)->where(['id' => $id])->find();
            $auth_ids=[];
            foreach($role['auth'] as $v){
                array_push($auth_ids,$v['id']);
            }
            $role['auth']=$auth_ids;
            $authList = M('Auth')->field('id,auth_name,status,rule,parent_id,create_time,update_time')->select();
            $list = list_to_tree($authList);
            $this->assign('list', $list);
            $this->assign('item', $role);
            $this->display();
        }
    }

    public function del()
    {
        $id = I('get.id');
        M()->startTrans();
        $res = M('Role')->where(['id' => $id])->delete();
        if ($res) {
            D('RoleAuth')->where(['role_id'=>$id])->delete();
            M()->commit();
            $this->success('删除成功', U('admin/role/index'));
        } else {
            M()->rollback();
            $this->error('删除失败');
        }
        
    }
}
