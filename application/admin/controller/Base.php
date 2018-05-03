<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-15
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Controller;
use think\Config;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;
use think\response\Redirect;
use think\Url;

/**
 * 后台公共控制器
 */
class Base extends Controller 
{
    // 不需要验证权限的节点
    protected $noaccessUrlArr = [
        'admin/admin/login', 
        'admin/admin/logout', 
        'admin/index/index',
    ];

    /**
     * 系统初始化
     */
    protected function _initialize()
    {
        $request= \think\Request::instance();
        // 过滤不需要登录验证的行为
        if (in_array($request->action(), array('login', 'logout', 'vertify','index'))) {

        } else {
            // 判断是否登录
            if (session('admin_id') > 0) {
                // 是否为超级管理员
                if (!isRoot()) {
                    $this->checkPriv();
                }
            } else {
                $this->error('请先登录', url('Admin/login'));
            }
        }
        
        $this->publicAssign();

         //菜单设置
        if (!$this->request->isAjax()) {
            $this->assign("__MENU__", $this->getMenus());
        }

        $this->_initAdmin(); 
    }

    /**
     * 后台初始化
     */
    protected function _initAdmin(){}

    /**
     * 设置页面meta
     */
    protected function setMeta($meta_title = ''){
        $this->assign('meta_title', $meta_title);
    }

    /**
     * 管理员权限检测
     */
    public function checkPriv()
    {   
        // 获取当前请求控制器和方法
        $request = \think\Request::instance();
        $ctl     = $request->controller();
        $act     = $request->action();
        // 权限信息
        $act_list = session('act_list');
        // 无需验证权限
        $uneed_check = array('login', 'logout','modifypwd');
        if ($ctl == 'Index' || $act_list == 'all') {
            // 超级管理员无需验证
            return true;
         } elseif ($request->isAjax() || strpos($act,'ajax') !== false || in_array($act, $uneed_check)) {
            // 所有ajax请求无需验证
            return true;
         } else {
            $right = db('system_menu')
                ->where('id', 'in', $act_list)
                ->cache(true)
                ->column('id, right')
            ;

            $role_right = null;
            foreach ($right as $val) {
                // 连接权限信息
                $role_right .= $val.',';
            }
            // 将字符串打散为数组
            $role_right = explode(',', $role_right);

            // 检查匹配权限
            if (!in_array($ctl.'@'.$act, $role_right)) {
                $this->error('您没有操作权限['.($ctl.'@'.$act).'],请联系超管分配', url('admin/login'));
            }
         }
    }

    /**
     * 配置信息输出到模板
     */
    public function publicAssign()
    {
        $shop_config = array();
        $tp_config = db('config')->cache(true)->select();
        // 拼接配置信息
        foreach ($tp_config as $k => $v) {
            $shop_config[$v['inc_type'].'_'.$v['name']] = $v['value'];
        }
        $this->assign('shop_config', $shop_config);
    }

    /**
     * 获取菜单
     *
     * @return array 菜单列表
     */
    public function getMenus()
    {
        // 获取当前访问地址
        $current_url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());

        // 获取顶级主菜单
        $main_map['pid'] = 0;
        $main_map['module'] = 'admin';
        $main_map['status'] = 1;
        $main_map['type'] = 1;

        $menus = [];

        $main_list = db('menu')->where($main_map)->order('sort asc,id asc')->select();
        foreach ($main_list as $key => $item) {
            // 获取子菜单标识
            $sub_tag = db('menu')->where(['pid' => $item['id']])->value('id');
            
            // 没有子菜单 并且 不在公开节点内
            if (!in_array($item['name'], $this->noaccessUrlArr)) {
                // 不是超级管理员进行权限验证
                if (!isRoot()) {
                    $this->checkPriv();
                }
            }

            if ($current_url == $item['name']) {
                $item['class'] = 'layui-this';
            } else {
                $item['class'] = '';
            }

            $menus['main'][$item['id']] = $item;
        }

        //查询当前的父菜单id和菜单id
        $node = explode('/', $current_url);
        $hover_url = $node[0].'/'.$node[1].'/';

        
        $pid = db('menu')->where("pid !=0 AND name like '{$hover_url}%' AND status = 1")->value('pid');
        $id  = db('menu')->where("pid = 0 AND name like '{$hover_url}%' AND status = 1")->value('id');
        $pid = $pid ? $pid : $id;

        if (!$pid) {
            return $menus;
        }

        $sub_map['pid']  = $pid;
        $sub_map['type'] = 2;
        $sub_map['status'] = 1;

        $sub_row = db('menu')->where($sub_map)->order('sort asc,id asc')->column('*', 'id');

        if (!$sub_row) {
            return $menus; //如果没有子菜单直接返回
        }
        // $current_id  = db('menu')->where(array('status'=>1,'name'=>$current_url,'type'=>2))->value('pid');

        $sub_pid = db('menu')->where("pid !=0 AND name like '{$hover_url}%' AND status = 1")->value('id');
        // 给当前左侧子菜单激活属性
        $sub_row[$sub_pid]['class'] = 'layui-this';

        // 给主菜单激活属性
        foreach ($sub_row as $key => $item) {
            // 不在公开节点内
            if (!in_array($item['name'], $this->noaccessUrlArr)) {
                // 不是超级管理员进行权限验证
                if (!isRoot()) {
                    $this->checkPriv();
                }

            }

            $menus['_child'][$item['group']]['item'][$key] = $item;
        }

        // 给当前左侧菜单组激活属性
        $menus['_child'][$sub_row[$sub_pid]['group']]['class'] = 'layui-nav-itemed';
        if (!empty($menus['main'][$sub_row[$sub_pid]['pid']])) {
            $menus['main'][$sub_row[$sub_pid]['pid']]['class'] = 'layui-this';
        }

        return $menus;
    }

    /**
     * 设置一条或者多条数据的状态
     * @param $strict 严格模式要求处理的纪录的uid等于当前登陆用户UID
     */
    public function setStatus($model = '', $strict = false){
        if ($model =='') {
            $model = request()->controller();
        }
        $ids    = array_unique((array) input('ids/a', 0));
        $status = input('status');
        $setfield = input('setfield','status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        // 获取主键
        $status_model      = model($model);
        $model_primary_key = $status_model->getPk();

        // 获取id
        $ids                     = is_array($ids) ? implode(',', $ids) : $ids;
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map[$model_primary_key] = array('in', $ids);
        // 严格模式
        if ($strict) {
            $map['id'] = array('eq', is_login());
        }
        switch ($status) {
            // case 'forbid': // 禁用条目
            //     $data = array($setfield => 0);
            //     $this->editRow(
            //         $model,
            //         $data,
            //         $map,
            //         array('success' => '禁用成功', 'error' => '禁用失败')
            //     );
            //     break;
            // case 'resume': // 启用条目
            //     $data = array($setfield => 1);
            //     //$map  = array_merge(array($setfield => 0), $map);
            //     $this->editRow(
            //         $model,
            //         $data,
            //         $map,
            //         array('success' => '启用成功', 'error' => '启用失败')
            //     );
            //     break;

            case 'delete': 
                // 删除记录
                // 查询当前删除的项目是否有子代
                // 查询当前删除的项目是否有子代
                if (in_array('pid', $status_model->getTableFields())) {
                    $count = $status_model->where(array('pid' => array('in', $ids)))->count();
                    if ($count > 0) {
                        $this->error('无法删除，存在子项目！');
                    }
                }

                // 删除记录
                $result = $status_model->where($map)->delete();
                if ($result) {
                    $this->success('删除成功，不可恢复！');
                } else {
                    $this->error('删除失败');
                }
                break;
            default:
                $this->error('参数错误');
                break;
        }
    }
}
