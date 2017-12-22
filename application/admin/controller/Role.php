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
    	$role_id = input('role_id');

    	// 根据ID查询角色信息
    	$info = array();
    	if ($role_id) {
    		$info = $this->modelRole->get($role_id);
    		$this->assign('info', $info);
    	}

    	// 根据是否有id 判断操作是新增或修改
        $act = empty($role_id) ? 'add' : 'edit';
        $this->assign('act', $act);
     
        return $this->fetch('role_info');
    }
}
