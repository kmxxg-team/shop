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
            $map['nickname'] = ['like', '%'. $this->request->param('nickname') . '%'];
        }

        // 按手机号搜索
        if ($this->request->param('mobile')) {
            $map['mobile'] = ['like', '%'. $this->request->param('mobile') . '%'];
        }

        if ($this->request->isAjax()) {
            $count = $this->modelUser->where($map)->count();

            $list  = $this->modelUser
                ->where($map)
                ->page($this->modelUser->getPageNow(), $this->modelUser->getPageLimit())
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
                'limit' => $this->modelUser->getPageLimit()
            ];

            return $this->success('获取成功', '', $data);
        }

        return $this->fetch();                     
    }
    
    /**
     * 删除会员
     */
    public function delete()
    {
        // return $this->error('删除失败');
        return $this->success('删除成功');
    }

    /**
     * 编辑
     */
    public function edit() {
        if ($this->request->isPost()) {
            sleep(2);
            // return $this->error('格式不正确');
            return $this->success('编辑成功');
        }
        return $this->fetch();
    }
}
