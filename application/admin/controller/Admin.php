<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-15
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Controller;
use think\Db;

/**
 * 后台管理员控制器
 */
class Admin extends Controller
{   
    /**
     * 首页
     */
    public function index()
    {
    	$list = array();
    	$keyword = input('keyword');

    	/*查询管理员表*/
		if (empty($keyword)) {
			$res = Db::name('admin')->select();
		} else {
			$res = Db::name('admin')
				->where('user_name', 'like', '%'.$keyword.'%')
				->order('admin_id')
				->select()
			;
		}

		/*查询权限表*/
		$role = Db::name('admin_role')->column('role_id, role_name');

		/*将管理员所属角色名 存入数组*/
		if ($res && $role) {
			foreach ($res as $val) {
    			$val['role'] =  $role[$val['role_id']];
    			$list[] = $val;
			}
		}

		$this->assign('list', $list);
        return $this->fetch();
    }
}
