<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-21
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 后台菜单控制器
 */
class Menu extends Base
{
	// 菜单模型
	protected $modelMenu;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelMenu = model('Menu');
		$menu_gorup = config('menu_gorup');

		$list  = $this->modelMenu
			->field(true)
			->column('*')
		;

		$tree = new \app\common\org\TreeList();
		$tree_list = $tree->toFormatTree($list);

		$this->assign('menu_gorup', $menu_gorup);
		$this->assign('tree_list', $tree_list);

	}

	/**
	 * 列表展示
	 */
	public function index()
	{
		$map = [];

		// 按昵称搜索
        if ($this->request->param('keyword')) {
            $map['title'] = ['like', '%'. $this->request->param('keyword') . '%'];
        }

		if ($this->request->isAjax()) {
			$count = $this->modelMenu->where($map)->count();

			$list  = $this->modelMenu
				->where($map)
				->field(true)
				->page($this->modelMenu->getPageNow(), $this->modelMenu->getPageLimit())
				->column('*')
			;

			if (!$list) {
				return $this->error('信息不存在');
			}

			foreach ($list as $key => $val) {
	            $list[$key]['parentid_node'] = ($val['pid']) ? 'class = "child-of-node-' . $val['pid'] . '"' : '';
	        }

	        if (!empty($list)) {
	            $tree = new \app\common\org\TreeList();
	             
	            $list = $tree->toFormatTree($list);
	        }


			$this->assign('list', $list);

			$html = $this->fetch('index_ajax');

			$data = [
				'list'  => $html,
				'count' => $count,
				'limit' => $this->modelMenu->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}
		
		return $this->fetch(); 
	}

	/**
	 * 菜单信息页面
	 */
	public function menuInfo()
	{
		$id = $this->request->param('id');

		// 查询所有父节点
		$par = db('menu')->field('title, id')->select();
		$this->assign('par', $par);

		$info = array();

		// 判断是否有id传入
		if ($id) {
			$info = $this->modelMenu->get($id);
			$this->assign('info',$info);
		}
		return $this->fetch('menu_info');
	}

	/**
	 * 新增菜单
	 */
	public function add()
	{	
		// 判断请求
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			
			$result = $this->modelMenu->save($data);
			if ($result) {
				$this->success('新增成功','index');
			}
			$this->error('新增失败');
		}
		$this->error('新增失败');
	}

	/**
	 * 编辑菜单
	 */
	public function edit()
	{
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			
			$result = $this->modelMenu->update($data);
			if ($result) {
				$this->success('编辑成功','index');
			}
			$this->error('编辑失败');
		}
		$this->error('编辑失败');
	}
}