<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
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
    protected $modelImages;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelGoods = model('Goods');
        $this->modelImages = model('GoodsImages');
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
    public function info()
    {
    	$id = input('goods_id');

        // 根据ID查询信息 给模板展示
        $info = array();
        if ($id) {
            $info = $this->modelGoods->get($id);
        }

        // 获取商品分类信息树状数组
        $cat_list = controller('GoodsCategory')->getTreeArray();

        $this->assign('info', $info);
        $this->assign('cat_list', $cat_list);
    	return $this->fetch();
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

            $validate = validate('Goods');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

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
            
            $validate = validate('Goods');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

    		$result = $this->modelGoods->allowField(true)->update($data);

    		// 结果反馈
    		if ($result !== false) {
    			$this->success('修改成功', 'index');
    		} else {
    			$this->error('修改失败');
    		}
    	}
    }

    /**
     * 编辑商品详情页
     */
    public function imgInfo()
    {
        $id = input('goods_id');
        
        if (empty($id)) {
            $this->error('未获取到商品ID');
        }

        $images = $this->modelImages->where('goods_id', $id)->select();

        $this->assign('images', $images);
        return $this->fetch('img_info');
    }

    /**
     * 商品相册图片上传
     */
    public function updateImg()
    {
        $id = input('goods_id', '');
        if (empty($id)) {
        // 未获取到商品ID
            $response = [
                'code' => 1,
                'msg'  => '未获取到商品ID',
            ];
            return $response;
        }

        // 上传图片
        $file = $this->request->file();
        $file = $file['file'];
        $info = $file->move(ROOT_PATH . 'public\uploads\goods_images');

        if ($info) {
            $response = [
                'code' => 0,
                'msg'  => '上传成功',
                'data' => ['src' => ROOT_PATH . 'public\uploads\goods_images\\' . $info->getSaveName()],
            ];
        } else {
            $response = [
                'code' => 1,
                'msg'  => $info->getError(),
            ];
        }
        
        // 添加图片表数据
        if ($response['code'] === 0) {
            $data['goods_id'] = $id;
            $data['image_url'] = '\public\uploads\goods_images\\' . $info->getSaveName();
            $result = $this->modelImages->save($data);
            if ($result === false) {
                $response = [
                    'code' => 1,
                    'msg'  => '保存失败',
                ];
            }
        }

        return $response;
    }

    /**
     * 商品图片删除
     *
     * @param intger $id 商品图片ID
     */
    public function deleteImg($id = '')
    {
        if (empty($id)) {
            return $this->error('未获取到图片ID');
        }

        $path = $this->modelImages->where('img_id', $id)->value('image_url');
        $path = ROOT_PATH . $path;
        $result = $this->modelImages->where('img_id', $id)->delete();
        
        if ($result === false) {
            return $this->error('删除失败');
        }

        // 删除对应文件
        if (file_exists($path)) unlink($path);

        return $this->success('删除成功');
    }

}
