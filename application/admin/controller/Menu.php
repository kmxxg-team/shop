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
	}

	/**
	 * 列表展示
	 */
	public function index()
	{
		$map = [];

		// 按昵称搜索
        if ($this->request->param('title')) {
            $map['title'] = ['like', '%'. $this->request->param('title') . '%'];
        }

        // 按手机号搜索
        if ($this->request->param('level')) {
            $map['level'] = ['like', '%'. $this->request->param('level') . '%'];
        }

		if ($this->request->isAjax()) {
			$count = $this->modelMenu->where($map)->count();

			$list  = $this->modelMenu
				->where($map)
				->page($this->modelMenu->getPageNow(), $this->modelMenu->getPageLimit())
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
				'limit' => $this->modelMenu->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}
		
		return $this->fetch(); 
	}

	/**
	 * 删除菜单
	 */
	public function delete()
	{
		if ($this->request->param('mod_id')) {
			$this->modelMenu->destroy($this->request->param('mod_id'));
			$this->success('删除成功');
		}
		$this->error('删除失败');
	}

	/**
	 * 菜单信息页面
	 */
	public function menuInfo()
	{
		$mod_id = $this->request->param('mod_id');

		$info = array();

		// 判断是否有id传入
		if ($mod_id) {
			$info = $this->modelMenu->get($mod_id);
			$this->assign('info',$info);
		} else {
			
		}

		return $this->fetch('menu_info');
	}
}