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
     * 获取管理员列表信息json数据
     */
    public function adminJson()
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
            ->select()
        ;

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

        return json($list);
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
