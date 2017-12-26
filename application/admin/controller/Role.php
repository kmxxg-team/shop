<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-14
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;

/**
 * 权限控制器
 */
class Role extends Base 
{   
	// 权限模型
	protected $modelRole;

	/**
	 * 初始化
	 */
	public function _initAdmin()
	{
		$this->modelRole = model('Role');
	}

    /**
     * 列表展示
     */
    public function index()
    {
        // 接收表单传值
        $data = input('param.');

        // 处理搜索条件
        $map = [];

        // 关键词：按照用户名和邮箱进行搜索
        if (!empty($data['keyword'])) {
            $map['role_name | role_desc'] = array('like', '%'.$data['keyword'].'%');
        }

        // 若为AJAX
        if ($this->request->isAjax()) {
            // list用于存放：角色信息
            $list = array();
            $count = $this->modelRole->where($map)->count();

            //查询管理员表
            $list = $this->modelRole
                ->where($map)
                ->order('role_id asc')
                ->page($this->modelRole->getPageNow(), $this->modelRole->getPageLimit())
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
                'limit' => $this->modelRole->getPageLimit()
            ];

            $this->success('获取成功', '', $data);
        }

        return $this->fetch();
    }

    /**
     * 角色信息页面
     */
    public function roleInfo()
    {
        $id = input('role_id');

        // 根据ID查询信息 给模板展示
        $info = array();
        if ($id) {
            $info = $this->modelRole->get($id);
            $this->assign('info', $info);
        }
        
        return $this->fetch('role_info');
    }

    /**
     * 添加角色
     */
    public function add()
    {
        // 接收到ajax请求
        if (Request::instance()->isAjax()) {
            $data = Request::instance()->param();
            
            // 更新查找到的记录
            $result = $this->modelRole->allowField(true)->save($data);

            // 结果反馈
            if ($result) {
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }
    }

    /**
     * 编辑角色
     */
    public function edit()
    {
        // 接收到ajax请求
        if (Request::instance()->isAjax()) {
            $data = Request::instance()->param();
            
            // 更新查找到的记录
            $result = $this->modelRole->allowField(true)->update($data);

            // 结果反馈
            if ($result) {
                $this->success('更新成功', 'index');
            } else {
                $this->error('更新失败');
            }
        }
    }

    /**
     * 删除角色
     */
    public function del()
    {
        $id = input('role_id');
        $result = $this->modelRole->destroy($id);
        if ($result) {
            $this->success('删除成功', 'index');
        } else {
            $this->error('删除失败');
        }
    }
}
