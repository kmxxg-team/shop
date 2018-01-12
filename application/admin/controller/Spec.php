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
		$map = [];

		// 按昵称搜索
        if ($this->request->param('keyword')) {
            $map['name'] = ['like', '%'. $this->request->param('keyword') . '%'];
        }

		if ($this->request->isAjax()) {
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
		
		return $this->fetch(); 
	}

	/**
	 * 商品属性信息页面
	 */
	public function menuInfo()
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
		return $this->fetch('spec_info');
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
	 * 删除商品属性
	 */
	public function delete()
	{
		if ($this->request->param('id')) {
			// 开启事务删除数据
			Db::startTrans(); 
			
			$spec = $this->modelSpec->get($this->request->param('id'));
			
			$result  = $spec->delete();
			$resItem = $spec->specItem()->delete();  

			if ((false !== $result) && $resItem) {
				//提交事务
				Db::commit();
				$this->success('删除成功');
			} else {
				//回滚事务
				Db::rollback();
				$this->error('删除失败');
			}
		}
		$this->error('删除失败');
	}


	/**
    *更新规格表和规格项表数据
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