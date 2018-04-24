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

namespace app\common\model;

use think\Model;
use think\Config;
use think\Request;

/**
 * 业务层基础模型
 */
class Base extends Model
{
    // 当前页
    protected $pageNow;
    // 每页显示记录数
    protected $pageLimit;

    protected function initialize()
    {
        $this->loadPage();
    }

    /**
     * 加载数据分页
     *
     * @return void
     */
    protected function loadPage()
    {
        $page = Config::get('page');
        $rows = Request::instance()->param('limit', 0, 'intval');
        $this->pageLimit = $rows ? : $page['list_rows'];
        $this->pageNow = Request::instance()->param('page', 1, 'intval');
    }

    /**
     * 获取每页显示条数
     *
     * @return integer 每页显示条数
     */
    public function getPageLimit()
    {
        return $this->pageLimit;        
    }

    /**
     * 获取当前页
     *
     * @return integer 当前页码
     */
    public function getPageNow()
    {
        return $this->pageNow;
    }
}