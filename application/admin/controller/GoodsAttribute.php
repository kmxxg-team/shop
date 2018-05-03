<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-4
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 后台商品属性控制器
 */
class GoodsAttribute extends Base
{
	// 商品类模型
	protected $modelGoodsAttribute;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelGoodsAttribute = model('GoodsAttribute');
	}

	/**
	 * 列表展示
	 */
	public function index()
	{
		if ($this->request->isAjax()) {
			$map = [];
			$keyword = input('keyword/s', '');
			$type_id = input('type_id/s', '');
			// 按昵称搜索
	        if (empty($keyword) != true) {
	            $map['attr_name'] = ['like', '%'. $keyword . '%'];
	        }

	        //按照所属模型搜索
	        if (empty($type_id) != true) {
	        	$map['type_id'] = ['eq', $type_id];
	        }
	        
			$count = $this->modelGoodsAttribute->where($map)->count();

			$list  = $this->modelGoodsAttribute
				->where($map)
				->page($this->modelGoodsAttribute->getPageNow(), $this->modelGoodsAttribute->getPageLimit())
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
				'limit' => $this->modelGoodsAttribute->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}

		// 获取商品模型
		$types = model('goods_type')->select();
		$this->assign('types', $types);
		return $this->fetch(); 
	}

	/**
	 * 商品属性信息页面
	 */
	public function info()
	{
		$id = $this->request->param('id');

		$info = array();
		// 判断是否有id传入
		if ($id) {
			$info = $this->modelGoodsAttribute->get($id);
		}
		
		// 查询模型信息
		$type = db('goods_type')->select();

		$this->assign('info',$info);
		$this->assign('type', $type);
		return $this->fetch();
	}

	/**
	 * 新增商品属性
	 */
	public function add()
	{	
		// 判断请求
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			$result = $this->modelGoodsAttribute->save($data);

			if ($result !== false) {
				$this->success('新增成功','index');
			} else {
				$this->error('新增失败');
			}
		}
	}

	/**
	 * 编辑商品属性
	 */
	public function edit()
	{
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			$result = $this->modelGoodsAttribute->update($data);

			if ($result !== false) {
				$this->success('编辑成功','index');
			} else {
				$this->error('编辑失败');
			}
		}
	}
}