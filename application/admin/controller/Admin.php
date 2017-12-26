<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com> 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-15
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Db;
use think\Session;
use app\admin\controller\Base;

/**
 * 后台管理员控制器
 */
class Admin extends Base
{   
    // 管理员模型
    protected $modelAdmin;

    /**
     * 初始化
     */
    public function _initAdmin()
    {
        $this->modelAdmin = model('Admin');
    }

    /**
     * 列表展示
     */
    public function index()
    {
        // 接收表单传值
        $data = input('param.');

        // 处理搜索条件
        $map = [];

        // 关键词：按照用户名和邮箱进行搜索
        if (!empty($data['keyword'])) {
            $map['user_name | email'] = array('like', '%'.$data['keyword'].'%');
        }

        // 角色ID
        if (!empty($data['role_id'])) {
            $map['role_id'] = array('eq', $data['role_id']);
        }

        // 查询权限表 注意：是 role_id => role_name 的一维关联数组
        $role = Db::name('admin_role')->column('role_id, role_name');
        $this->assign('role', $role);

        // 若为AJAX
        if ($this->request->isAjax()) {
            // list用于存放：包含所属角色名的 管理员信息数组
            $list = array();
            $count = $this->modelAdmin->where($map)->count();

            //查询管理员表
            $res = $this->modelAdmin
                ->where($map)
                ->order('admin_id desc')
                ->page($this->modelAdmin->getPageNow(), $this->modelAdmin->getPageLimit())
                ->select()
            ;

            if (!$res) {
                return $this->error('信息不存在');
            }

            // 将角色名称信息 添加进list数组
            if ($res && $role) {
                foreach ($res as $val) {
                    $val['role'] =  $role[$val['role_id']];
                    $list[] = $val;
                }
            }

            $this->assign('list', $list);
            $html = $this->fetch('index_ajax');

            $data = [
                'list'  => $html,
                'count' => $count,
                'limit' => $this->modelAdmin->getPageLimit()
            ];

            $this->success('获取成功', '', $data);
        }

        return $this->fetch();
    }

    /**
     * 管理员信息页面
     */
    public function adminInfo()
    {
        $admin_id = input('admin_id');

        // 根据id查询管理员信息
        $info = array();
        if ($admin_id) {
            $info = $this->modelAdmin->get($admin_id);
            $info['password'] = '';
            $this->assign('info', $info);
        }

        // 根据是否有id 判断操作是新增或修改
        $act = empty($admin_id) ? 'add' : 'edit';
        $this->assign('act', $act);

        // 角色列表
        $role = Db::name('admin_role')->select();
        $this->assign('role', $role);

        return $this->fetch('admin_info');
    }

    /**
     * 新增管理员
     */
    public function add()
    {
        // 接收表单传值
        $data = input('param.');

        unset($data['admin_id']);           
        $data['add_time'] = time();

        if (Db::name('admin')->where("user_name", $data['user_name'])->count()) {
            return $this->error('此用户名已被注册，请更换');
        } else {
            $result = Db::name('admin')
                ->strict(false)
                ->insert($data)
            ;
        }

        // 结果反馈
        if ($result >= 0) {
            return $this->success('新增成功', 'index');
        } else {
            return $this->error('新增失败');
        }
    }

    /**
     * 编辑管理员
     */
    public function edit()
    {
        // 接收表单传值
        $data = input('param.');

        // 密码字段处理
        if(empty($data['password'])){
            unset($data['password']);
        }else{
            $data['password'] = encrypt($data['password']);
        }

        $result = Db::name('admin')
            ->where('admin_id', $data['admin_id'])
            ->strict(false)
            ->update($data)
        ;

        // 结果反馈
        if ($result >= 0) {
            return $this->success('修改成功', 'index');
        } else {
            return $this->error('修改失败');
        }
    }

    /**
     * 删除管理员
     */
    public function del()
    {
        // 接收传值
        $data = input('param.');

        if ($data['admin_id'] > 1) {
            $result = Db::name('admin')->where('admin_id', $data['admin_id'])->delete();
        }

        // 结果反馈
        if ($result >= 0) {
            return $this->success('删除成功', 'index');
        } else {
            return $this->error('删除失败');
        }
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        // 检测是否已登录
        if (isLogin()) {
            $this->error('您已登陆',url('Index/index'));
        }
        // 登录行为
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 登录数据校验
            if (!empty($data['user_name']) && !empty($data['password'])) {
                // 密码加密
                $data['password'] = encrypt($data['password']);
                $info = db('admin')
                    ->where($data)
                    ->join('tp_admin_role', 'tp_admin.role_id = tp_admin_role.role_id','INNER')
                    ->find()
                ;
                //检测登陆行为
                if ($info) {
                    // 登录成功 更新登录时间ip
                    $update = array(
                        'last_login' => time(),
                        'last_ip'    => request()->ip());
                    db('admin')
                        ->where('admin_id = '.$info['admin_id'])
                        ->update($update)
                    ;
                    //记录session
                    updateSession($info);
                    //跳转路径
                    $url = session('from_url') ? session('from_url') : url('Index/index');
                    $this->success('登陆成功',$url);
                } else {
                    $this->error('账号密码不正确！', '', $data);
                }
            } else {
                $this->error('登录名或密码不能为空', '', $data);
            }
        }
        return $this->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        //清空session信息
        session(null);
        session::clear();
        $this->success('注销成功', url('Admin/login'));
    }

    /**
     * 修改当前管理员密码
     */
    public function modifyPwd()
    {    
        $id = session('admin_id');
        // 获取密码 
        $data = $this->request->post();
        $oldPwd = $data['oldPwd'];
        $newPwd = $data['newPwd'];
        $newPwdCheck = $data['newPwdCheck']; 

        if ($id) {
            $info = db('admin')
                ->where("admin_id", $id)
                ->find()
            ;
            $info['password'] = '';
            $this->assign('info',$info);
        }

        // 对新旧密码加密处理
        $enOldPwd = encrypt($oldPwd);
        $enNewPwd = encrypt($newPwd);
        $admin = db('admin')
            ->where('admin_id', $id)
            ->find()
        ;
        // 验证密码格式
        if (!$admin || $admin['password'] != $enOldPwd) {
            $this->error('旧密码不正确');
        } else if ($newPwd != $newPwdCheck) {
            $this->error('两次密码不一致');
        } else {
            $row = db('admin')
                ->where('admin_id', $id)
                ->update(array('password' => $enNewPwd))
            ;
            //返回值判断修改状态
            if ($row) {
                $this->success('修改成功',url('Index/index'));
            } else {
                $this->error('修改失败');
            }
        }
    }     
}
