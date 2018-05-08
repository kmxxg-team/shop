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
    protected $modelSpecItem;
    protected $modelSpecPrice;
    protected $modelGoodsAttr;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelGoods = model('Goods');
        $this->modelImages = model('GoodsImages');
        $this->modelSpecItem = model('SpecItem');
        $this->modelSpecPrice = model('SpecGoodsPrice');
        $this->modelGoodsAttr = model('GoodsAttr');

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

    /**
     * 商品模型信息页
     */
    public function typeInfo()
    {
        $id = input('goods_id');
        if (empty($id)) {
            return $this->error('未获取到商品ID');
        }

        // 获取商品模型
        $types = db('goods_type')->select();

        // 获取商品信息
        $info = $this->modelGoods->find($id);

        // dump($info->spec_item_ids);

        // 接受商品模型 获取所属规格和属性
        if ($this->request->isAjax()) {
            $type_id = input('type_id');
            if (empty($type_id)) {
                return $this->error('模型id未获取');
            }

            $map['type_id'] = ['eq', $type_id];

            // 获取规格
            $data['spec'] = model('spec')->where($map)->select();
            // 获取属性
            $data['attr'] = model('goods_attribute')->where($map)->select();
            
            if ($data['spec'] === false || $data['attr'] === false) {
                return $this->error('获取失败');
            }

            // 将attribute表中的待选值 拆分为数组
            foreach ($data['attr'] as $key=>$attr) {
                if (empty($attr['attr_values'])) {
                    $data['attr'][$key]['attr_values'] = '';
                    continue;
                }
                $data['attr'][$key]['attr_values']  = explode('/', $attr['attr_values']);
            }

            $this->assign('data', $data);
            $html['spec'] = $this->fetch('spec_ajax');
            $html['attr'] = $this->fetch('attr_ajax');

            return $this->success('获取成功', null, $html);
        }

        $this->assign('types', $types);
        $this->assign('info', $info);
        return $this->fetch('type_info');
    }

    /**
     * 商品模型信息页 根据选取规格项生成表单
     */
    public function ajaxGetSpecInput()
    {
        if ($this->request->isAjax()) {
            // 获取规格项数组
            $spec_arr = input('spec_arr/a');
            if (empty($spec_arr)) {
                $this->error('未选取规格项');
            }

            // 去除数组中的空值
            $spec_arr = array_filter($spec_arr);

            // 按照键值进行升序排序（按规格项数量）
            foreach ($spec_arr as $key => $value) {
                $spec_arr_sort[$key] = count($value);
            }
            asort($spec_arr_sort);
            foreach ($spec_arr_sort as $key => $value) {
                $new_arr[$key] = $spec_arr[$key];
            }
            $spec_arr = $new_arr;

            // 获取规格名称数组
            $spec_id = array_keys($spec_arr);
            foreach ($spec_id as $id) {
                $spec_name[] = db('spec')->where('id', $id)->value('spec_name');
            }

            // 获取各个规格规格项的笛卡尔积
            $dika_arr = combineDika($spec_arr);

            // 获取笛卡尔积数组键值 与 所代表规格项id字符（id_id_id） 关联数组
            // (用于给Input表单赋name值)
            foreach ($dika_arr as $id => $item) {
                $dika_id_item[$id] = implode('_', $item);
            }

            // 获取规格项id对应名称的关联数组
            $item_name = db('spec_item')->column('item', 'id');

            $this->assign('spec_name', $spec_name);
            $this->assign('dika_arr', $dika_arr);
            $this->assign('dika_id_item', $dika_id_item);
            $this->assign('item_name', $item_name);

            return $this->fetch('spec_input_ajax');

        }
    }

    /**
     * 商品属性及规格信息保存
     */
    public function updateSpecAttr()
    {
        if ($this->request->isAjax()) {
            $goods_id = input('goods_id');
            if (empty($goods_id)) {
                return $this->error('未获取到商品ID');
            }

            $data = input();

            $type = isset($data['type']) ? $data['type'] : [];
            if (empty($type)) {
                return $this->error('未选取商品模型');
            }

            // 启动事务
            $this->modelGoods->startTrans();

            // 删除原先的数据
            $delete_result = $this->deleteSpecAttr($goods_id);

            // 修改商品所属模型
            $type_result = $this->modelGoods
                ->where('goods_id', $goods_id)
                ->update(['type_id'=>$type])
            ;

            // 修改商品规格项数据
            $spec = isset($data['spec']) ? $data['spec'] : [];
            $spec_all_data = [];
            foreach ($spec as $key => $value) {
                // 若不启用该条规格项 跳过
                if (!isset($value['open'])) continue;
                $spec_data = [];
                // 商品ID
                $spec_data['goods_id'] = $goods_id;
                // 规格项 id字符（id_id_...）
                $spec_data['key'] = $key;
                // 规格项 中文名字符（规格项名：规格项值 规格项名2：规格项值2 ...）
                $key_name = explode('_', $key);
                foreach ($key_name as $k => $v) {
                    $item_obj = $this->modelSpecItem->find($v);
                    $key_name[$k] = $item_obj->spec->spec_name .':'. $item_obj['item'];
                }
                $spec_data['key_name'] = implode(' ', $key_name);
                // 价格
                $spec_data['price'] = $value['price'];
                // 库存数量
                $spec_data['store_count'] = $value['store_count'];
                // SKU
                $spec_data['sku'] = $value['sku'];
                $spec_all_data[] = $spec_data;
            }
            $spec_result = $this->modelSpecPrice->saveAll($spec_all_data);

            // 修改商品属性数据
            $attr = isset($data['attr']) ? $data['attr'] : [];
            $attr_all_data = [];
            foreach ($attr as $key => $value) {
                $attr_data = [];
                // 商品ID
                $attr_data['goods_id'] = $goods_id;
                // 属性ID
                $attr_data['attr_id'] = $key;
                // 属性值
                $attr_data['attr_value'] = $value;
                $attr_all_data[] = $attr_data;
            }
            $attr_result = $this->modelGoodsAttr->saveAll($attr_all_data);

            // 最终结果 皆不出错
            $result = $type_result !== false;
            $result &= $spec_result !== false;
            $result &= $attr_result !== false;
            $result &= $delete_result !== false;

            if ($result) {
                // 提交事务
                $this->modelGoods->commit();
                return $this->success('提交成功');
            } else {
                // 回滚事务
                $this->modelGoods->rollback();
                return $this->error('提交失败');
            }
        }
    }

    /**
     * 删除物品所有规格及属性信息
     *     * 
     * @param intger $goods_id 商品id
     * @return boolen 删除结果
     */
    private function deleteSpecAttr($goods_id = 0)
    {
        if (empty($goods_id)) {
            return false;
        }

        // 将商品所属模型id设为0
        $type_result = $this->modelGoods
            ->where('goods_id', $goods_id)
            ->update(['goods_id'=>0])
        ;

        // 删除规格项关联表中关联数据
        $spec_result = $this->modelSpecPrice
            ->where('goods_id', $goods_id)
            ->delete()
        ;

        // 删除属性关联表中关联数据
        $attr_result = $this->modelGoodsAttr
            ->where('goods_id', $goods_id)
            ->delete()
        ;

        $result = $type_result !== false;
        $result &= $spec_result !== false;
        $result &= $attr_result !== false;

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}
