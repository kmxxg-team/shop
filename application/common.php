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

/**
 * 递归删除文件
 * @param path 文件路径
 * @param delDir 指定删除路径
 * @return 失败 false 有指定时成功返回删除路径结果
 */
function delFile($path, $delDir = false)
{	
	if (!is_dir($path)) {
		return false;
	}
	$handle = opendir($path);
	//是否读取路径
	if ($handle) {
		// 打开目录然后读取其内容 删除子文件
		while (false !== ($item = readdir($handle))) {
			// 读取到的文件名不为返回上一级.. 或 .
			if ($item != '.' && $item != '..') {
				// 判断当前路径下是否有子文件夹 有则递归 无则删除其中文件
				is_dir("$path/$item") ? delFile("$path/$item", $delDir) : unlink("$path/$item");
			}
		}
		closedir($handle);
		//删除目录
		if ($delDir) return rmdir($path);
	} else {
		if (file_exists($path)) {
			return unlink($path);
		} else {
			return false;
		}
	}
}