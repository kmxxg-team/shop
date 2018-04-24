<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-8
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 配送地区控制器
 */
class Region extends Base
{
	// 地区模型
	protected $modelRegion;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelRegion = model('Region');
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
			$count = $this->modelRegion->where($map)->count();

			$list  = $this->modelRegion
				->where($map)
				->field(true)
				->page($this->modelRegion->getPageNow(), $this->modelRegion->getPageLimit())
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
				'limit' => $this->modelRegion->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}
		
		return $this->fetch(); 
	}
}