<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends Controller{
    public function index(){
        //处理空控制器
        $this->error('非法操作');
    }
}