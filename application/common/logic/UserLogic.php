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

namespace app\common\logic;

use think\Model;
use think\Db;

/**
 * 会员模型
 */
class UserLogic extends Model
{
	/**
     * 获取指定用户信息
     * @param $uid int 用户UID
     *
     * @return mixed 找到返回数组
     */
    public function detail($uid)
    {
        $user = Db::name('users')->where('user_id', $uid)->find();
        return $user;
    }

	/**
     * 添加用户
     * @param $user
     * @return array
     */
	public function addUser($user)
	{
		$user_count = Db::name('users')
			->where(function($query) use ($user){
				if ($user['email']) {
					$query->where('email',$user['email']);
				}
				if ($user['mobile']) {
					$query->whereOr('mobile',$user['mobile']);
				}
			})
			->count()
		;

		if ($user_count > 0) {
			return array('status' => -1, 'msg' => '邮箱或手机号已被占用');
		}

		// 密码加盐
    	$user['password'] = encrypt($user['password']);

    	// 创建时间
    	$user['reg_time'] = time();

    	// 新增
    	$user_id = Db::name('users')->insert($user);

    	// 结果反馈
    	if (!$user_id) {
    		return array('status' => -1, 'msg' => '添加失败');
    	} else {
    		return array('status' => 1, 'msg' => '添加成功');
    	}
	}

	/**
     * 改变会员信息
     * @param int $uid
     * @param array $data
     * @return array
     */
    public function updateUser($uid = 0, $data = array())
    {
    	$user = Db::name('users')->where('user_id', $uid)->find();
    	if (!$user) {
    		return array('status' => -1, '会员不存在');
    	}

		if ($user_count > 0) {
			return array('status' => -1, 'msg' => '邮箱或手机号已被占用');
		}

		// 密码加盐
    	$user['password'] = encrypt($user['password']);

    	$result = Db::name('users')
    		->where('user_id', $uid)
    		->strict(false)
    		->update($data)
    	;

        if ($result >= 0) {
            return array('status' => 1, 'msg' => '会员信息修改成功');
        } else {
            return array('status' => -1, 'msg' => '会员信息修改失败');
        }
    }
}