<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: dorisnzy <dorisnzy@163.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-19
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\controller\Base;

/**
 * 会员控制器
 */
class User extends Base
{
    // 用户模型
    protected $modelUser;

    /**
     * 初始化
     */
    public function _initAdmin()
    {
        $this->modelUser = model('user');
        $this->setMeta('用户管理');
    }

    /**
     * 列表展示
     */
    public function index()
    {
        return $this->fetch();                     
    }
    
}
