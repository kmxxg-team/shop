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
    /*----------- 后台自定义分页 -----------------*/
    // 每页显示记录数目
    protected $listRows;
    // 分页类型
    protected $pageType;

    protected function initialize()
    {
        // 加载分页配置
        $this->loadPageConfig();
    }

    /**
     * 加载分页配置
     *
     * @return voild
     */
    protected function loadPageConfig()
    {
        $page = Config::get('page');
        $rows = Request::instance()->param('rows', 0, 'intval');
        $this->listRows = $rows ? : $page['list_rows'];
        $this->pageType = $page['type'];
    }

    /**
     * 分页获取数据
     *
     * @param  array  $map 查询条件
     *
     * @return object      数据集
     */
    public function getList()
    {
        return $this->$pageType($map);
    }

    /**
     * layui数据分页
     *
     * @return object 数据集
     */
    protected function layui()
    {
        $page_now = Request::instance()->param('page', 1, 'intval');
        $list = $this->page($page_now, $this->listRows)->select();     
    }
}