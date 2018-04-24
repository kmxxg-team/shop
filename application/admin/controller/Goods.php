<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: dorisnzy <dorisnzy@163.com>
// +----------------------------------------------------------------------
// | Date: 2017-1-1
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 商品控制器
 */
class Goods extends Base 
{   
	// 商品模型
	protected $modelGoods;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelGoods = model('Goods');
	}

    /**
     * 首页
     */
    public function index()
    {
    	// 接收表单传值
        $data = input('param.');

        // 处理搜索条件
        $map = [];

        // 关键词：按照用户名和邮箱进行搜索
        if (!empty($data['keyword'])) {
            $map['goods_name | goods_sn'] = array('like', '%'.$data['keyword'].'%');
        }

        // 若为AJAX
        if ($this->request->isAjax()) {
            $list = array();
            $count = $this->modelGoods->where($map)->count();

            //查询管理员表
            $list = $this->modelGoods
                ->where($map)
                ->order('goods_id desc')
                ->page($this->modelGoods->getPageNow(), $this->modelGoods->getPageLimit())
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
                'limit' => $this->modelGoods->getPageLimit()
            ];

            $this->success('获取成功', '', $data);
        }
        return $this->fetch();
    }

    /**
     * 商品信息页面
     */
    public function goodsInfo()
    {
    	$id = input('goods_id');

        // 根据ID查询信息 给模板展示
        $info = array();
        if ($id) {
            $info = $this->modelGoods->get($id);
            $this->assign('info', $info);
        }

    	return $this->fetch('goods_info');
    }

    /**
     * 新增商品
     */
    public function add()
    {
    	// 接收到ajax请求
    	if ($this->request->isAjax()) {
    		$data = $this->request->param();

    		// 转换 “是否是虚拟商品”
    		$data['is_virtual'] = isset($data['is_virtual']) ? 1 : 0;

    		// 转换 ”是否包邮“
    		$data['is_free_shipping'] = isset($data['is_free_shipping']) ? 1 : 0;

    		$result = $this->modelGoods->allowField(true)->save($data);

    		// 结果反馈
    		if ($result) {
    			$this->success('商品新增成功', 'index');
    		} else {
    			$this->error('商品新增失败');
    		}
    	}
    }

    /**
     * 编辑商品
     */
    public function edit()
    {
    	// 接收到ajax请求
    	if ($this->request->isAjax()) {
    		$data = $this->request->param();

    		// 转换 “是否是虚拟商品”
    		$data['is_virtual'] = isset($data['is_virtual']) ? 1 : 0;

    		// 转换 ”是否包邮“
    		$data['is_free_shipping'] = isset($data['is_free_shipping']) ? 1 : 0;

    		$result = $this->modelGoods->allowField(true)->update($data);

    		// 结果反馈
    		if ($result) {
    			$this->success('修改成功', 'index');
    		} else {
    			$this->error('修改失败');
    		}
    	}
    }
}
