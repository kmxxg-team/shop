<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
// +----------------------------------------------------------------------
// | Date: 2018-04-27
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 商品分类控制器
 */
class GoodsCategory extends Base 
{   
    // 商品分类模型
    protected $modelCategory;

    /**
     * 初始化
     */
    public function _initAdmin()
    {
        $this->modelCategory = model('GoodsCategory');
    }

    /**
     * 首页
     */
    public function index()
    {
        // 若为AJAX
        if ($this->request->isAjax()) {
            $map = [];
            // 接收表单传值
            $data = input('param.');

            // 关键词：按照用户名和邮箱进行搜索
            if (!empty($data['keyword'])) {
                $map['name | mobile_name'] = array('like', '%'.$data['keyword'].'%');
            }

            $count = $this->modelCategory->where($map)->count();

            //查询管理员表
            $list = $this->modelCategory
                ->where($map)
                ->order('id desc')
                ->page($this->modelCategory->getPageNow(), $this->modelCategory->getPageLimit())
                ->column('*')
            ;

            if (!$list) {
                return $this->error('信息不存在');
            }

            foreach ($list as $key => $val) {
                $list[$key]['parentid_node'] = ($val['pid']) ? 'class = "child-of-node-' . $val['pid'] . '"' : '';
            }

            if (!empty($list)) {
                $tree = new \app\common\org\TreeList();
                $list = $tree->toFormatTree($list, 'name');
            }

            $list = $this->getTreeLevel($list, 0, $deep = 0);

            $this->assign('list', $list);
            $html = $this->fetch('index_ajax');

            $data = [
                'list'  => $html,
                'count' => $count,
                'limit' => $this->modelCategory->getPageLimit()
            ];
            
            $this->success('获取成功', '', $data);
        }

        return $this->fetch();
    }

    /**
     * 商品分类信息页面
     */
    public function info()
    {
        $id = input('id');

        // 根据ID查询信息 给模板展示
        $info = array();
        if ($id) {
            $info = $this->modelCategory->get($id);
        }

        // 树状结构 供下拉菜单
        $list = $this->getTreeArray();

        $this->assign('info', $info);
        $this->assign('list', $list);

        return $this->fetch();
    }


    /**
     * 新增商品分类
     */
    public function add()
    {
        // 接收到ajax请求
        if ($this->request->isAjax()) {
            $data = $this->request->param();

            // 转换 “是否是虚拟商品”
            $data['is_show'] = isset($data['is_show']) ? 1 : 0;

            // 转换 ”是否包邮“
            $data['is_hot'] = isset($data['is_hot']) ? 1 : 0;

            // 数据验证
            $validate = validate('GoodsCategory');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $result = $this->modelCategory->allowField(true)->save($data);

            // 结果反馈
            if ($result) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
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
            $data['is_show'] = isset($data['is_show']) ? 1 : 0;

            // 转换 ”是否包邮“
            $data['is_hot'] = isset($data['is_hot']) ? 1 : 0;

            // 数据验证
            $validate = validate('GoodsCategory');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $result = $this->modelCategory->allowField(true)->update($data);

            // 结果反馈
            if ($result !== false) {
                $this->success('修改成功', 'index');
            } else {
                $this->error('修改失败');
            }
        }
    }

    /**
     * 获取分类树状数组
     *
     * @param array $map 查询条件
     *
     * @return array 树状数组
     */
    public function getTreeArray($map = [])
    {
        //查询管理员表
        $list = $this->modelCategory->where($map)->column('*');

        if (!$list) {
            return $this->error('信息不存在');
        }

        foreach ($list as $key => $val) {
            $list[$key]['parentid_node'] = ($val['pid']) ? 'class = "child-of-node-' . $val['pid'] . '"' : '';
        }

        if (!empty($list)) {
            $tree = new \app\common\org\TreeList();
            $list = $tree->toFormatTree($list, 'name');
        }

        return $list;
    }

    /**
     * 获取当前树深度
     */
    public function getTreeLevel($data, $pid, $deep = 0)
    {
        static $tree = array();
        foreach ($data as $row) {
            if ($row['pid'] == $pid) {
                $row['lever'] = $deep;
                $tree[] = $row;
                $this->getTreeLevel($data, $row ['id'], $deep + 1);
            }
        }
        return $tree;
    }

}
