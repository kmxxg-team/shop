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
use think\Db;

/**
 * 发货单控制器
 */
class DeliveryDoc extends Base
{
	// 发货单模型
	protected $modelDeliveryDoc;
	protected $modelOrder;
	protected $modelOrderAction;
	protected $modelOrderGoods;
	protected $modelShipping;


	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelDeliveryDoc = model('DeliveryDoc');
		$this->modelOrder = model('Order');
		$this->modelOrderAction = model('OrderAction');
		$this->modelOrderGoods = model('OrderGoods');
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
			$count = $this->modelDeliveryDoc->where($map)->count();

			$list  = $this->modelDeliveryDoc
				->where($map)
				->page($this->modelDeliveryDoc->getPageNow(), $this->modelDeliveryDoc->getPageLimit())
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
				'limit' => $this->modelDeliveryDoc->getPageLimit(),
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
	// 		$info = $this->modelDeliveryDoc->get($order_id);
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
	// 	$header = $this->modelDeliveryDoc->getOrderFields();


	// 	$list = $this->modelDeliveryDoc
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
	// 		$this->modelDeliveryDoc->destroy($this->request->param('order_id'));
	// 		$this->success('删除成功');
	// 	}
	// 	$this->error('删除失败');
	// }

	/**
	 * 发货操作页
	 */
	public function delivery($temple = NULL)
	{
		$order_id = $this->request->param('order_id');

		if (!$order_id) {
			return $this->error('数据错误');
		}

		$map['order_id'] = $order_id;

		$doc_info = $this->modelDeliveryDoc->where($map)->find();

		if (empty($doc_info)) {
			return $this->error('暂无数据');
		}

		// 处理地区字段
		$doc_info['country'] = db('region')
			->where(array('id' => $doc_info['country']))
			->field('title')
			->find()
		;
		$doc_info['country'] = $doc_info['country']['title'];

		$doc_info['province'] = db('region')
			->where(array('id' => $doc_info['province']))
			->field('title')
			->find()
		;
		$doc_info['province'] = $doc_info['province']['title'];

		$doc_info['city'] = db('region')
			->where(array('id' => $doc_info['city']))
			->field('title')
			->find()
		;
		$doc_info['city'] = $doc_info['city']['title'];

		$doc_info['district'] = db('region')
			->where(array('id' => $doc_info['district']))
			->field('title')
			->find()
		;
		$doc_info['district'] = $doc_info['district']['title'];

		$doc_info['twon'] = db('region')
			->where(array('id' => $doc_info['twon']))
			->field('title')
			->find()
		;
		$doc_info['twon'] = $doc_info['twon']['title'];

		// 订单物品表字段处理
		$all = 0;
		foreach ($doc_info->orderGoods as $key => $value) {
			$all = $all + $value['final_price'];
		}
		$doc_info['all'] = $all;


		// 获取快递公司
		$shipping = db('shipping')
			->where(array('is_open' => 1))
			->field('shipping_code,shipping_name')
			->select()
		;

		// 获取用户备注
		$user_note = db('order')
			->where($map)
			->field('user_note')
			->find()
		;

		// 存在则处理
		if ($user_note) {
			$user_note = implode('', $user_note);
		}	
		
		$this->assign('user_note', $user_note);
		$this->assign('shipping', $shipping);
		$this->assign('doc_info', $doc_info);

		if ($temple == 'shipping_goods') {
			return $this->fetch($temple); 
		} else {
			return $this->fetch();
		}
		
	}

	/**
	 * 配送货物页
	 */
	public function shippingGoods()
	{
		return $this->delivery($temple = 'shipping_goods');
	}

	/**
	 * 确认发货
	 */
	public function deliveryConfirm()
	{
		if (!$this->request->isPost()) {
			return $this->error('数据错误');
		}
			
		$data = $this->request->param();

		$order_id = $data['order_id'];
		unset($data['order_id']);

		// 开启事务新增数据
		Db::startTrans();
		
		// 当选择发货
		if (isset($data['ids'])) {
			$ids = $data['ids'];
			unset($data['ids']);

			// 对ids进行操作
			foreach ($ids as $key => $value) {
				$data_goods[$key] = ['rec_id' => $value, 'is_send' => 1];
			}

			$res_goods = $this->modelOrderGoods->saveAll($data_goods);
		}

		if (!isset($res_goods)) {
			return $this->error('请选择发送货物');
		}
		
		// 更新发货表数据
		$res_doc = $this->modelDeliveryDoc->update($data);

		// 检测是否全部发货
		$map_send['order_id'] = $order_id;
		$map_send['is_send'] = 0;
		$not_send = $this->modelOrderGoods->where($map_send)->count();

		$map['order_id'] = $order_id;

		if ($not_send == 0) {
			// 全部已发货
			$res_order = $this->modelOrder
				->where($map)
				->update(['shipping_status' => 1, 'shipping_time' => time()])
			;
			
		} else {
			// 部分发货
			$res_order = $this->modelOrder
				->where($map)
				->update(['shipping_status' => 2, 'shipping_time' => time()])
			;
		}
	
		if ($res_goods && $res_doc && $res_order) {
			//提交事务
			Db::commit();

			// 写入发货记录
			$data_action = db('order')
				->where($map)
				->field('order_id, order_status, shipping_status, pay_status')
				->find()
			;
			// 补充字段
			$data_action['action_note'] = $data['note'];
			$data_action['log_time'] = time();
			$data_action['status_desc'] = '发送货物';

			$res_action = $this->modelOrderAction->save($data_action);

			return $this->success('操作成功');
		} else {
			//回滚事务
			Db::rollback();
			return $this->error('操作失败');
		}
	}

}