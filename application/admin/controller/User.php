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
        $this->modelUser = model('User');
    }

    /**
     * 列表展示
     */
    public function index()
    {
        $map = [];
        
        // 按昵称搜索
        if ($this->request->param('nickname')) {
            $map['nickname'] = $this->request->param('nickname');
        }
        
        $count = $this->modelUser->count();

        if ($this->request->isAjax()) {
            $list  = $this->modelUser
                ->where($map)
                ->page($this->modelUser->getPageNow(), $this->modelUser->getPageLimit())
                ->select()
            ;

            $this->assign('list', $list);
            $html = $this->fetch('index_ajax');

            $data = [
                'list'  => $html,
                'count' => $count,
            ];

            return $this->success('获取成功', '', $data);
        }

        $this->assign('count', $count);
        return $this->fetch();                     
    }
    
}
