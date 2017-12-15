<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 检测管理员是否登录
 * @return 存在 返回管理员id 不存在 返回false
 */
function isLogin(){
	if (session('admin_id') && session('admin_id')>0) {
		return session('admin_id');
	} else {
		return  false;
	}
}


/**
 * md5加密密码处理
 * @param str 需要加密数据
 * @return 加密密码串
 */
function encrypt($str){
	return md5('encrypt'.$str);
}

/**
 * 更新session值
 * @param info 需要更新的数组
 * @return bool
 */
function updateSession($info){
	if ($info) {
		session('admin_id', $info['admin_id']);
	    session('act_list', $info['act_list']);
	    session('last_login_time', $info['last_login']);
	    session('last_login_ip', $info['last_ip']);
	    return true;
	} else {
		return false;
	}	
}