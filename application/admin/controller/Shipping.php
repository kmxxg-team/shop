<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-10
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 发货单控制器
 */
class Shipping extends Base
{
	// 发货单模型
	protected $modelShipping;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelShipping = model('Shipping');
	}

	/**
	 * 列表展示
	 */
	public function index()
	{
		$map = [];

		// 按昵称搜索
        if ($this->request->param('keyword')) {
            $map['name'] = ['like', '%'. $this->request->param('keyword') . '%'];
        }

		if ($this->request->isAjax()) {
			$count = $this->modelShipping->where($map)->count();

			$list  = $this->modelShipping
				->where($map)
				->page($this->modelShipping->getPageNow(), $this->modelShipping->getPageLimit())
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
				'limit' => $this->modelShipping->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}
		
		return $this->fetch(); 
	}

	/**
	 * 编辑信息
	 */
	public function shippingInfo()
	{
		$shipping_id = $this->request->param('shipping_id');

		$info = array();

		// 判断是否有id传入
		if ($shipping_id) {
			$info = $this->modelShipping->get($shipping_id);
			$this->assign('info',$info);
		}
		return $this->fetch('shipping_info');
	}

	/**
	 * 新增信息
	 */
	public function add()
	{	
		// 判断请求
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			
			$result = $this->modelShipping->save($data);
			if ($result) {
				$this->success('新增成功','index');
			}
			$this->error('新增失败');
		}
		$this->error('新增失败');
	}

	/**
	 * 编辑信息
	 */
	public function edit()
	{
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			
			$result = $this->modelShipping->update($data);
			if ($result) {
				$this->success('编辑成功','index');
			}
			$this->error('编辑失败');
		}
		$this->error('编辑失败');
	}
}