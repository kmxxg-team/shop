<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: alwayswhl <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-15
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Controller;
use think\Config;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;
use think\response\Redirect;
use think\Url;

/**
 * 后台公共控制器
 */
class Base extends Controller 
{
    /**
     * 系统初始化
     */
    protected function _initialize()
    {
        $this->_initAdmin();
    }

    /**
     * 后台初始化
     */
    protected function _initAdmin(){}
}
