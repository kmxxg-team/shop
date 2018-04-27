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
 * 后台商品规格控制器
 */
class Spec extends Base
{
	// 商品类模型
	protected $modelSpec;
	protected $modelSpecItem;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelSpec = model('Spec');
		$this->modelSpecItem = model('SpecItem');
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
	            $map['spec_name'] = ['like', '%'. $keyword . '%'];
	        }

	        //按照所属模型搜索
	        if (empty($type_id) != true) {
	        	$map['type_id'] = ['eq', $type_id];
	        }

			$count = $this->modelSpec->where($map)->count();

			$list  = $this->modelSpec
				->where($map)
				->page($this->modelSpec->getPageNow(), $this->modelSpec->getPageLimit())
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
				'limit' => $this->modelSpec->getPageLimit(),
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

		// 查询模型信息
		$type = db('goods_type')->select();
		$this->assign('type', $type);

		$info = array();

		// 判断是否有id传入
		if ($id) {
			$info = $this->modelSpec->get($id);
			$this->assign('info',$info);
		}
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
			
			// 开启事务新增数据
			Db::startTrans(); 
			
			// 抽取规格项数据
	        $apart = $data['spec_item'];
	        $apart = substr($apart,  0, -1);
	        $apart = explode('/', $apart);
			unset($data['spec_item']);

			//添加规格表数据获取id
			$resSpec = $this->modelSpec->save($data);
			$spec_id = $this->modelSpec->id;

			
	        // 组装数据
	        for ($i=0; $i < count($apart); $i++) { 
	            $item[$i] = ['spec_id'=>$spec_id, 'item'=>$apart[$i]];
	        }

	        //添加规格项表数据
			$spec = $this->modelSpec->get($spec_id);
			$resItem = $spec->specItem()->saveAll($item);
			if ($resSpec && $resItem) {
				//提交事务
				Db::commit();
				$this->success('新增成功','index');
			} else {
				//回滚事务
				Db::rollback();
				$this->error('新增失败');
			}
		}
		$this->error('新增失败');
	}

	/**
	 * 编辑商品属性
	 */
	public function edit()
	{		
		if ($this->request->isPost()) {
		
			// 接受数据
			$data = $this->request->param();

			// 开启事务修改数据
			Db::startTrans(); 

			// 抽取规格项数据和id
			$spec_id = $data['id'];
	        $apart = $data['spec_item'];
	        $apart = substr($apart,  0, -1);
	        $apart = explode('/', $apart);
			unset($data['spec_item']);

			$item_old = db('spec_item')
			->where(array('spec_id'=>$spec_id))
			->column('item')
			;
			//更新规格项表数据
			$resItem = $this->updateData($spec_id,$apart,$item_old);

			//添加规格表数据
			$result = $this->modelSpec->update($data);
			
			if ((false !== $result) && $resItem) {
				//提交事务
				Db::commit();
				$this->success('编辑成功','index');
			} else {
				//回滚事务
				Db::rollback();
				$this->error('编辑失败');
			}
		}
		$this->error('编辑失败');
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

                $status_model->startTrans();
                // 删除记录
                $spec_result = $status_model->where($map)->delete();
                // 删除规格项
                $item_result = $this->modelSpecItem->where(array('spec_id' => array('in', $ids)))->delete();
                if ($spec_result && $item_result) {
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

	/**
     * 更新规格表和规格项表数据
     */
    private function updateData($id,$new_data,$old_data=array())
    {
        // 求新标签和旧的之间公共部分
        $com_data = array_intersect($new_data,$old_data);
        // 求需要增加的部分
        $insert_data = array_diff($new_data, $com_data);
        // 求需要删除的部分
        $delete_data = array_diff($old_data, $com_data);

		$add_result = 0;
        $drop_result = 0;
 
        // 关联表增加操作
        if (!empty($insert_data) && is_array($insert_data)) {
            foreach ($insert_data as $item) {
                $add_data[] = array(
                            'spec_id' => $id,
                            'item' => $item,
                        	);   
            }
            $add_result = $this->modelSpecItem->saveAll($add_data);
        }

        // 关联表删除操作
        if (!empty($delete_data) && is_array($delete_data)) {
            foreach ($delete_data as $item) {
                $drop_data = array(
                            'spec_id' => $id,
                            'item' => $item,
                        	);
                $drop_result = $this->modelSpecItem->where($drop_data)->delete();
            }
        }

        if ($add_result || $drop_result) {
            return true;
        }else{
            return false;
        }
    }
}