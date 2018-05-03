<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-18
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 退换货控制器
 */
class ReturnMoney extends Base
{
	// 发货单模型
	protected $modelOrder;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelOrder = model('Order');
	}

	/**
	 * 列表展示
	 */
	public function index()
	{
		$map = [];

		$map = ['order_status' => 3];
		$map['pay_status'] = ['neq', 0];
		// 按昵称搜索
        if ($this->request->param('keyword')) {
            $map['name'] = ['like', '%'. $this->request->param('keyword') . '%'];
        }

		if ($this->request->isAjax()) {
			$count = $this->modelOrder->where($map)->count();

			$list  = $this->modelOrder
				->where($map)
				->page($this->modelOrder->getPageNow(), $this->modelOrder->getPageLimit())
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
				'limit' => $this->modelOrder->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}
		
		return $this->fetch(); 
	}

	// /**
	//  * 菜单信息页面
	//  */
	// public function orderInfo()
	// {
	// 	$order_id = $this->request->param('order_id');

	// 	// 判断是否有id传入
	// 	if ($order_id) {
	// 		$info = $this->modelOrder->get($order_id);
	// 		$this->assign('info',$info);
	// 	}
	// 	return $this->fetch('order_info');
	// }

	// /**
	//  * 导出报表
	//  */
	// public function derive($file_name = '订单数据报表', $map = [])
	// {
	// 	$excel = new \app\common\org\Excel;
		
	// 	// // 到处筛选条件
	// 	// if (input('post.category')) {
	// 	// 	$children_ids = get_allchild_ids('goods_category', input('post.category'), 'id,pid', 'id', 'pid');
	// 	// 	$map['a.category_id'] = ['in', $children_ids];;
	// 	// }

	// 	// if (input('post.supplier')) {
	// 	// 	$map['a.supplier_id'] = input('post.supplier');
	// 	// }
		
	// 	// $start_time = strtotime(input('post.start_time', ''));
	// 	// $end_time   = strtotime(input('post.end_time', ''));
		
	// 	// if ($start_time && $end_time) {
	// 	// 	$map['a.buy_time'] = ['between', [$start_time, $end_time]];
	// 	// } else if ($start_time) {
	// 	// 	$map['a.buy_time'] = ['egt', $start_time];
	// 	// } else if ($end_time) {
	// 	// 	$map['a.buy_time'] = ['elt', $end_time];
	// 	// }

	// 	// if (input('post.name')) {
	// 	// 	$map['a.name']    = ['like', input('post.name')];
	// 	// }

	// 	// if (input('post.number')) {
	// 	// 	$map['a.number']  = ['like', input('post.number')];
	// 	// }

	// 	// if (input('post.ids/a')) {
	// 	// 	$map['a.id']      = ['in', input('post.ids/a')];
	// 	// }

	// 	// 组装数据 导出报表
	// 	$header = $this->modelOrder->getOrderFields();


	// 	$list = $this->Order
	// 		->where($map)
	// 		->select()
	// 	;
		
	// 	if (empty($list)) {
	// 		return $this->error('暂无数据');
	// 	}

	// 	$file_name = $file_name.date('YmdHis');
	// 	$excel->export($file_name, $header, $list);
	// }

	// /**
	//  * 删除订单
	//  */
	// public function delete()
	// {
	// 	if ($this->request->param('order_id')) {
	// 		$this->modelOrder->destroy($this->request->param('order_id'));
	// 		$this->success('删除成功');
	// 	}
	// 	$this->error('删除失败');
	// }

	/**
	 * 退款审批页
	 */
	public function info()
	{
		$order_id = $this->request->param('order_id');
		if (!$order_id) {
			return $this->error('数据错误');
		}

		$map['order_id'] = $order_id;
		$order_info = $this->modelOrder->where($map)->find();

		if (empty($order_info)) {
			return $this->error('暂无数据');
		}

		// 处理地区字段
		$order_info['country'] = db('region')
			->where(array('id' => $order_info['country']))
			->field('title')
			->find()
		;
		$order_info['country'] = $order_info['country']['title'];

		$order_info['province'] = db('region')
			->where(array('id' => $order_info['province']))
			->field('title')
			->find()
		;
		$order_info['province'] = $order_info['province']['title'];

		$order_info['city'] = db('region')
			->where(array('id' => $order_info['city']))
			->field('title')
			->find()
		;
		$order_info['city'] = $order_info['city']['title'];

		$order_info['district'] = db('region')
			->where(array('id' => $order_info['district']))
			->field('title')
			->find()
		;
		$order_info['district'] = $order_info['district']['title'];

		$order_info['twon'] = db('region')
			->where(array('id' => $order_info['twon']))
			->field('title')
			->find()
		;
		$order_info['twon'] = $order_info['twon']['title'];

		// 订单物品表字段处理
		$all = 0;
		foreach ($order_info->orderGoods as $key => $value) {
			$all = $all + $value['final_price'];
		}
		$order_info['all'] = $all;

		$this->assign('order_info', $order_info);
		return $this->fetch('info'); 
	}

	/**
	 * 退款状态审批
	 */
	public function audit()
	{
		$data = $this->request->param();
		
		if (empty($data['pay_status'])) {
			return $this->error('请选择审批状态');
		}

		$map['order_id'] = intval($data['order_id']);
		unset($data['order_id']);

		$res = $this->modelOrder->where($map)->update($data);

		if (!$res) {
			return $this->error('操作失败');
		}
		return $this->success('操作成功');

	}
}