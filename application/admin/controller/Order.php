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
use think\Db;

/**
 * 后台订单控制器
 */
class Order extends Base
{
	// 订单模型
	protected $modelOrder;
	protected $modelOrderAction;
	protected $modelOrderGoods;
	protected $modelShipping;
	protected $modelDeliveryDoc;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelOrder = model('Order');
		$this->modelOrderAction = model('OrderAction');
		$this->modelOrderGoods = model('OrderGoods');
		$this->modelShipping = model('Shipping');
		$this->modelDeliveryDoc = model('DeliveryDoc');
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

	/**
	 * 订单信息页面
	 */
	public function orderInfo()
	{
		$order_id = $this->request->param('order_id');

		// 判断是否有id传入
		if (!$order_id) {
			return $this->error('数据错误');
		}

		$info = $this->modelOrder->get($order_id);
		$this->assign('info',$info);
		
		return $this->fetch('order_info');
	}


	/**
	 * 导出报表
	 */
	public function derive($file_name = '订单数据报表', $map = [])
	{
		$excel = new \app\common\org\Excel;
		
		// // 到处筛选条件
		// if (input('post.category')) {
		// 	$children_ids = get_allchild_ids('goods_category', input('post.category'), 'id,pid', 'id', 'pid');
		// 	$map['a.category_id'] = ['in', $children_ids];;
		// }

		// if (input('post.supplier')) {
		// 	$map['a.supplier_id'] = input('post.supplier');
		// }
		
		// $start_time = strtotime(input('post.start_time', ''));
		// $end_time   = strtotime(input('post.end_time', ''));
		
		// if ($start_time && $end_time) {
		// 	$map['a.buy_time'] = ['between', [$start_time, $end_time]];
		// } else if ($start_time) {
		// 	$map['a.buy_time'] = ['egt', $start_time];
		// } else if ($end_time) {
		// 	$map['a.buy_time'] = ['elt', $end_time];
		// }

		// if (input('post.name')) {
		// 	$map['a.name']    = ['like', input('post.name')];
		// }

		// if (input('post.number')) {
		// 	$map['a.number']  = ['like', input('post.number')];
		// }

		// if (input('post.ids/a')) {
		// 	$map['a.id']      = ['in', input('post.ids/a')];
		// }

		// 组装数据 导出报表
		$header = $this->modelOrder->getOrderFields();


		$list = $this->modelOrder
			->where($map)
			->select()
		;
		
		if (empty($list)) {
			return $this->error('暂无数据');
		}

		$file_name = $file_name.date('YmdHis');
		$excel->export($file_name, $header, $list);
	}

	/**
	 * 订单详情页
	 */
	public function orderDetail()
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
		return $this->fetch('order_detail'); 
	}

	/**
	 * 操作订单
	 */
	public function orderOption()
	{

		if (!$this->request->param()) {
			return $this->error('数据错误');
		}

		$data = $this->request->param();

		$data_order = $data;
		unset($data_order['action_note']);

		// 开启事务新增数据
		Db::startTrans(); 

		$res_order = $this->modelOrder->update($data_order);
		$map['order_id'] = $data_order['order_id'];
		$res = db('order')
			->where($map)
			->field('order_status, shipping_status, pay_status, user_id as action_user')
			->find()
		;
		unset($data_order['order_id']);

		$data = array_merge($data,$res);
		$data['log_time'] = time();

		switch ($data_order) {
			case ['order_status' => 1]:
				$data['status_desc'] = '提交确认';

				// 当支付方式为货到付款
				$pay_code = $this->modelOrder->where($map)->field('pay_code')->find();
				$pay_code['pay_code'] == 'cod' ? $this->orderToDelivery($map) : '';
				break;
			// case ['order_status' => 0]:
			// 	$data['status_desc'] = '取消提交确认';
			// 	break;
			case ['pay_status' => 1]:
				$data['status_desc'] = '付款成功';
				// 记录支付时间
				$this->modelOrder->where($map)->update(['pay_time' => time()]);
				// 订单支付生成发货单
				$this->orderToDelivery($map);
				break;
			// case ['pay_status' => 0]:
			// 	$data['status_desc'] = '付款取消';
			// 	break;
			default:
				// 将订单状态置为无效信息
				$data['status_desc'] = '作废订单';
				$data['order_status'] = 5;

				$res_status = $this->modelOrder->where($map)->update(['order_status' => 5]);
				break;
		}

		$res_action = $this->modelOrderAction->save($data);

		if ($res_order && $res_action) {
			//提交事务
			Db::commit();
			return $this->success('操作成功');
		} else {
			//回滚事务
			Db::rollback();
			return $this->error('操作失败');
		}
	}

	/**
	 * 订单确认生成发货单
	 */
	protected function orderToDelivery($map)
	{
		// 查询补全发货单字段
		$column = 'order_id, order_sn, user_id, consignee, zipcode, mobile, country, province, city, district, twon, address, shipping_code, shipping_name, shipping_price';
		$data = db('order')
			->where($map)
			->field($column)
			->find();

		$data['create_time'] = time();
		$data['admin_id'] = isLogin();

		// 生成发货单
		$res = $this->modelDeliveryDoc->save($data);
		// halt($res);
	}

	/**
	 * 删除方法
	 */
	public function delete($order_id = 0)
	{
		if (!$order_id) {
			return $this->error('数据错误');
		}

		// 开启事务新增数据
		Db::startTrans(); 

		$map['order_id'] = $order_id; 

		$res_order 	= $this->modelOrder->where($map)->delete();
		$res_doc 	= $this->modelDeliveryDoc->where($map)->delete();
		$res_action = $this->modelOrderAction->where($map)->delete();
		$res_goods 	= $this->modelOrderGoods->where($map)->delete();

		if ($res_order && $res_doc && $res_action && $res_action) {
			//提交事务
			Db::commit();
			return $this->success('操作成功');
		} else {
			//回滚事务
			Db::rollback();
			return $this->error('操作失败');
		}
	}
}