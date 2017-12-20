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

/**
 * 后台公共控制器
 */
class Base extends Controller 
{
    /**
     * 系统初始化
     */
    protected function _initialize()
    {
        $this->_initAdmin();

        $request= \think\Request::instance();
        // 过滤不需要登录验证的行为
        if (in_array($request->action(), array('login', 'logout', 'vertify'))) {

        } else {
            // 判断是否登录
            if (session('admin_id') > 0) {
                // 执行权限检测
                $this->check_priv();
            } else {
                $this->error('请先登录', url('Admin/login'), 1);
            }
        }
        $this->public_assign();
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
    public function check_priv()
    {   
        // 获取当前请求控制器和方法
        $request = \think\Request::instance();
        $ctl     = $request->controller();
        $act     = $request->action();
        // 权限信息
        $act_list = session('act_list');
        // 无需验证权限
        $uneed_check = array('login', 'logout');
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
                $this->error('您没有操作权限['.($ctl.'@'.$act).'],请联系超管分配', url('Index/index'));
            }
         }
    }

    /**
     * 配置信息输出到模板
     */
    public function public_assign()
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
     * 公共ajax数据返回
     */
    public function ajaxReturn($data, $type = 'json')
    {
        return json_encode($data);
    }
}
