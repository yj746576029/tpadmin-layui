<?php
namespace Admin\Model;
use Think\Model\RelationModel;
class RoleModel extends RelationModel{
    protected $_link = array(
        'auth' => array(
            'mapping_type'      =>  self::MANY_TO_MANY,
            'foreign_key'       =>  'role_id',
            'relation_foreign_key'  =>  'auth_id',
            'relation_table'    =>  '__ROLE_AUTH__' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        )
    );
}