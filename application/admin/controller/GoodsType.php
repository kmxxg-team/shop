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
	public function info()
	{
		$id = $this->request->param('id');
		$info = array();

		// 判断是否有id传入
		if ($id) {
			$info = $this->modelGoodsType->get($id);
			$this->assign('info',$info);
		}
		return $this->fetch();
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

	/**
     * 设置一条或者多条数据的状态
     * @param $strict 严格模式要求处理的纪录的uid等于当前登陆用户UID
     */
    public function setStatus($model = '', $strict = false){
        if ($model =='') {
            $model = request()->controller();
        }
        $ids    = array_unique((array) input('ids/a', 0));
        $status = input('status');
        $setfield = input('setfield','status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        // 获取主键
        $status_model      = model($model);
        $model_primary_key = $status_model->getPk();

        // 获取id
        $ids                     = is_array($ids) ? implode(',', $ids) : $ids;
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map[$model_primary_key] = array('in', $ids);
        // 严格模式
        if ($strict) {
            $map['id'] = array('eq', is_login());
        }
        switch ($status) {
            case 'delete': 
                // 删除记录
                // 查询当前删除的项目是否有子代
                if (in_array('pid', $status_model->getTableFields())) {
                    $son_count = $status_model->where(array('pid' => array('in', $ids)))->count();
                    if ($son_count > 0) {
                        $this->error('无法删除，存在子项目！');
                    }
                }

                // 查询当前删除模型 是否有所属属性
                $attr_count = model('GoodsAttribute')
                	->where(array('type_id' => array('in', $ids)))
                	->count()
                ;
                if ($attr_count > 0) {
                	$this->error('无法删除，存在子属性！请先删除所属属性');
                }

                // 查询当前删除模型 是否有所属规格
                $spec_count = model('Spec')
                	->where(array('type_id' => array('in', $ids)))
                	->count()
                ;
                if ($spec_count > 0) {
                	$this->error('无法删除，存在子规格！请先删除所属规格');
                }

                // 查询当前删除商品 是否存在所属商品
                $goods_count = model('Goods')
                	->where(array('type_id' => array('in', $ids)))
                	->count()
                ;
                if ($goods_count > 0) {
                	$this->error('无法删除，存在属于该模型的商品！请先删除所属商品');
                }

                // 删除数据
                $result = $status_model->where($map)->delete();

                if ($result) {
                	$status_model->commit();
                    $this->success('删除成功，不可恢复！');
                } else {
                	$status_model->rollback();
                    $this->error('删除失败');
                }
                break;
            default:
                $this->error('参数错误');
                break;
        }
    }
}