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
	public function _initMenu()
	{
		$this->modelMenu = model('Menu');
	}

	/**
	 * 列表展示
	 */
	public function index()
	{
		$map = [];

		if ($this->request->isAjax()) {
			$count = $this->modelMenu->where($map)->count();

			$list  = $this->modelMenu
				->where($map)
				->page($this->modelMenu->getPageNow()) 
		}
	}
}