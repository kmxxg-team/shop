<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-3
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 后台商品模型控制器
 */
class GoodsType extends Base
{
	// 商品类模型
	protected $modelGoodsType;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelGoodsType = model('GoodsType');
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
			$count = $this->modelGoodsType->where($map)->count();

			$list  = $this->modelGoodsType
				->where($map)
				->page($this->modelGoodsType->getPageNow(), $this->modelGoodsType->getPageLimit())
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
				'limit' => $this->modelGoodsType->getPageLimit(),
			];

			return $this->success('获取成功', '', $data);
		}
		
		return $this->fetch(); 
	}

	/**
	 * 商品模型信息页面
	 */
	public function menuInfo()
	{
		$id = $this->request->param('id');
		$info = array();

		// 判断是否有id传入
		if ($id) {
			$info = $this->modelGoodsType->get($id);
			$this->assign('info',$info);
		}
		return $this->fetch('goods_type_info');
	}

	/**
	 * 新增商品模型
	 */
	public function add()
	{	
		// 判断请求
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			$result = $this->modelGoodsType->save($data);

			if ($result !== false) {
				$this->success('新增成功','index');
			} else {
				$this->error('新增失败');
			}
		}
	}

	/**
	 * 编辑商品模型
	 */
	public function edit()
	{
		if ($this->request->isPost()) {
			// 接受数据
			$data = $this->request->param();
			$result = $this->modelGoodsType->update($data);

			if ($result !== false) {
				$this->success('编辑成功','index');
			} else {
				$this->error('编辑失败');
			}
		}
	}
}