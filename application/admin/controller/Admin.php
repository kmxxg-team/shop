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

use think\Controller;
use think\Db;
use think\Session;
use app\admin\controller\Base;

/**
 * 后台管理员控制器
 */
class Admin extends Base
{   
    /**
     * 首页
     */
    public function index()
    {
        // 接收表单传值
        $data = input('param.');

        // 处理搜索条件
        $map = array();

        if (!empty($data['keyword'])) {
            $condition = array('like', '%'.$keyword.'%');
            $map['user_name|email'] = array(
                $condition,
                $condition,
                $condition,
                '_multi' => true,
            );
        }

        //查询管理员表
        $res = Db::name('admin')
            ->where($map)
            ->order('admin_id')
            ->paginate(1)
        ;
        $page = $res->render();

        //查询权限表
        $role = Db::name('admin_role')->column('role_id, role_name');

        //将管理员所属角色名 存入数组
        $list = array();
        
        if ($res && $role) {
            foreach ($res as $val) {
                $val['role'] =  $role[$val['role_id']];
                $list[] = $val;
            }
        }

        $this->assign('list', $list);
        $this->assign('page', $page);
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
            $info = Db::name('admin')->find('admin_id');
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
     * 管理员增删改
     */
    public function adminHandle()
    {
        // 接收表单传值
        $data = input('post.');


        // 密码字段处理
        if(empty($data['password'])){
            unset($data['password']);
        }else{
            $data['password'] = encrypt($data['password']);
        }

        // 操作：新增
        if($data['act'] == 'add'){
            unset($data['admin_id']);           
            $data['add_time'] = time();
            if (Db::name('admin')->where("user_name", $data['user_name'])->count()) {
                return "此用户名已被注册，请更换";
            } else {
                $result = Db::name('admin')
                    ->strict(false)
                    ->insert($data)
                ;
            }
        }
        
        // 操作：修改
        if($data['act'] == 'edit'){
            $result = Db::name('admin')
                ->where('admin_id', $data['admin_id'])
                ->strict(false)
                ->update($data)
            ;
        }
        
        // 操作：删除
        if ($data['act'] == 'del' && $data['admin_id'] > 1) {
            $result = Db::name('admin')->where('admin_id', $data['admin_id'])->delete();
            exit(json_encode(1));
        }
        
        // 结果反馈
        if($result) {
            return '操作成功';
        } else {
            return '操作失败';
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
                $info = db('admin')->where($data)->join('tp_admin_role', 'tp_admin.role_id = tp_admin_role.role_id','INNER')->find();
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
                    $this->error('账号密码不正确！');
                }
            } else {
                $this->error('登录名或密码不能为空');
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
    public function modify_pwd()
    {
        $id   = input('admin_id/a', 0);
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

        if ($this->request->isPost()) {
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
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                }
            }
        }
        return $this->fetch();
    }
}
