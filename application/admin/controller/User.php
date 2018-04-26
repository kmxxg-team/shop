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
use app\common\logic\UserLogic;

/**
 * 会员控制器
 */
class User extends Base
{
    // 用户模型
    protected $modelUser;
    protected $logicUser;
    // 用户等级模型
    protected $userLevel;

    /**
     * 初始化
     */
    public function _initAdmin()
    {
        $this->modelUser = model('User');
        $this->logicUser = new UserLogic();
        $this->modelLevel = model('UserLevel');
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
    * 新增会员
    */
    public function add()
    {
        // 接收到ajax请求
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $result = $this->logicUser->addUser($data);
            if ($result['status'] == 1) {
                $this->success('添加成功', 'index');
            } else {
                $this->error($result['msg']);
            }
        }

        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $id = input('user_id');
        $info = $this->logicUser->detail($id);

        if ($this->request->isAjax()) {
            // 接收数据
            $data = $this->request->param();

            // 若密码字段未设置 则不修改密码
            if ('' == $data['password']) {
                unset($data['password']);
            }

            $result = $this->logicUser->updateUser($id, $data);
            if ($result['status'] == 1) {
                $this->success('修改成功', 'index');
            } else {
                $this->error($result['msg']);
            }
        }

        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 会员等级列表
     */
    public function level()
    {
        // 接收表单传值
        $data = input('param.');

        // 处理搜索条件
        $map = [];

        // 关键词：按照用户名和邮箱进行搜索
        if (!empty($data['keyword'])) {
            $map['level_name | describe'] = array('like', '%'.$data['keyword'].'%');
        }

        // 若为AJAX
        if ($this->request->isAjax()) {
            $list = array();
            $count = $this->modelLevel->where($map)->count();

            //查询管理员表
            $list = $this->modelLevel
                ->where($map)
                ->order('level_id asc')
                ->page($this->modelLevel->getPageNow(), $this->modelLevel->getPageLimit())
                ->select()
            ;

            if (!$list) {
                return $this->error('信息不存在');
            }

            $this->assign('list', $list);
            $html = $this->fetch('level_ajax');

            $data = [
                'list'  => $html,
                'count' => $count,
                'limit' => $this->modelLevel->getPageLimit()
            ];

            $this->success('获取成功', '', $data);
        }

        return $this->fetch();
    }

    /**
     * 会员等级信息页
     */
    public function levelInfo()
    {
        $id = input('level_id');

        // 根据ID查询等级信息
        $info = array();
        if ($id) {
            $info = $this->modelLevel->get($id);
            $this->assign('info', $info);
        }

        return $this->fetch('level_info');
    }

    /**
     * 会员等级新增
     */
    public function addLevel()
    {
        // 接收表单传值
        $data = $this->request->param();

        unset($data['level_id']);

        // 检测重复
        $require_name = $this->modelLevel->where("level_name", $data['level_name'])->count();
        if ($require_name) {
            return $this->error('此等级名已被使用，请更换');
        } else {
            $result = $this->modelLevel->insert($data);
        }

        // 结果反馈
        if ($result) {
            return $this->success('新增成功', url('level'));
        } else {
            return $this->error('新增失败');
        }
    }

    /**
     * 会员等级编辑
     */
    public function editLevel()
    {
        // 接收表单传值
        $data = $this->request->param();

        // 检测重复
        $result = $this->modelLevel
            ->where('level_id', $data['level_id'])
            ->update($data)
        ;

        // 结果反馈
        if ($result >= 0) {
            return $this->success('修改成功', url('level'));
        } else {
            return $this->error('修改失败');
        }
    }
}
