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
                ->order('id desc')
                ->page($this->modelSystemMenu->getPageNow(), $this->modelSystemMenu->getPageLimit())
                ->select()
            ;

            if (!$list) {
                return $this->error('信息不存在');
            }

            // 获取配置文件里的right_group（权限分组）
            $right_group = config('right_group');

            $this->assign('group', $right_group);
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
     * 权限信息页面展示
     */
    public function rightInfo()
    {
        $id = input('id');

        // 根据ID查询信息 给模板展示
        $info = array();
        if ($id) {
            $info = $this->modelSystemMenu->get($id);
            // 将权限码拆分成数组
            $info['right'] = explode(',', $info['right']);
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

            // 若权限码不为空，将权限码数组粘合成字符串
            if (!empty($data['right'])) {
                $data['right'] = implode(',', $data['right']);
            } else {
                $data['right'] = '';
            }

            // 更新查找到的记录
            $result = $this->modelSystemMenu
                ->allowField(true)
                ->save($data)
            ;

            // 结果反馈
            if ($result) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }
    }

    /**
     * 编辑权限
     */
    public function edit()
    {
        // 接收到ajax请求
        if (Request::instance()->isAjax()) {
            $data = Request::instance()->param();

            // 若权限码不为空，将权限码数组粘合成字符串
            if (!empty($data['right'])) {
                $data['right'] = implode(',', $data['right']);
            } else {
                $data['right'] = '';
            }

            // 更新查找到的记录
            $result = $this->modelSystemMenu
                ->allowField(true)
                ->update($data)
            ;

            // 结果反馈
            if ($result) {
                $this->success('更新成功', 'index');
            } else {
                $this->error('更新失败');
            }
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
