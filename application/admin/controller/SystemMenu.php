<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-23
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;

/**
 * 权限控制器
 */
class SystemMenu extends Base 
{   
	// 权限模型
	protected $modelSystemMenu;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelSystemMenu = model('SystemMenu');
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
            $map['name'] = array('like', '%'.$data['keyword'].'%');
        }

        // 若为AJAX
        if ($this->request->isAjax()) {
            $list = array();
            $count = $this->modelSystemMenu->where($map)->count();

            //查询表
            $list = $this->modelSystemMenu
                ->where($map)
                ->page($this->modelSystemMenu->getPageNow(), $this->modelSystemMenu->getPageLimit())
                ->select()
            ;

            if (!$list) {
                return $this->error('信息不存在');
            }

            $this->assign('list', $list);
            $html = $this->fetch('index_ajax');

            $data = [
                'list'  => $html,
                'count' => $count,
                'limit' => $this->modelSystemMenu->getPageLimit()
            ];

            $this->success('获取成功', '', $data);
        }

        return $this->fetch();
    }

    /**
     * 编辑权限
     */
    public function edit()
    {
        $id = input('id');

        // 根据ID查询信息 给模板展示
        $info = array();
        if ($id) {
            $info = $this->modelSystemMenu->get($id);
            $this->assign('info', $info);
        }

        // 获取配置文件里的right_group（权限分组）
        $right_group = config('right_group');

        // 获取admin模块下的所有控制器名称
        $plan_path = APP_PATH.'admin/controller';
        $plan_list = array();
        $dir_res = opendir($plan_path);
        while ($dir = readdir($dir_res)) {
            // 过滤'.', '..', '.svn'
            if (!in_array($dir, ['.', '..', '.svn'])) {
                $plan_list[] = basename($dir, '.php');
            }
        }

        // 接收到ajax请求
        if (Request::instance()->isAjax()) {
            $data = Request::instance()->param();
            
            // 更新查找到的记录
            $result = $info->allowField(true)->save($data);

            // 结果反馈
            if ($result >= 0) {
                $this->success('更新成功', 'index');
            } else {
                $this->error('更新失败');
            }
        }

        $this->assign('right_group', $right_group);
        $this->assign('plan_list', $plan_list);
        return $this->fetch('system_menu_info');
    }

    /**
     * 添加角色
     */
    public function add()
    {
        // 接收到ajax请求
        if (Request::instance()->isAjax()) {
            $data = Request::instance()->param();
            
            // 更新查找到的记录
            $result = $this->modelSystemMenu->allowField(true)->save($data);

            // 结果反馈
            if ($result) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        return $this->fetch('system_menu_info');
    }

    /**
     * 删除角色
     */
    public function del()
    {
        $id = input('role_id');
        $result = $this->modelSystemMenu->destroy($id);
        if ($result) {
            $this->success('删除成功', 'index');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * ajax获取:某controller所特有的方法,用于下拉菜单
     */
    function ajax_get_action()
     {
        $control = input('controller');
        $advContrl = get_class_methods("app\admin\controller\\". str_replace('.php','',$control));
        $baseContrl = get_class_methods("app\admin\controller\Base");
        $diffArray  = array_diff($advContrl,$baseContrl);

        $html = '';
        foreach ($diffArray as $val){
            $html .= "<option value='".$val."'>".$val."</option>";
        }
        exit($html);
     }
}
