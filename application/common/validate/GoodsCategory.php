<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
// +----------------------------------------------------------------------
// | Date: 2018-04-27
// +----------------------------------------------------------------------

namespace app\common\validate;

use think\Validate;

/**
 * 商品分类验证器
 */
class GoodsCategory extends Validate
{
	// 验证规则
    protected $rule = [
        'name'            => 'require|max:45|unique:goods_category',
        'pid'             => 'require|number|different:id',
    ];
    
    //错误信息
    protected $message  = [
        'name.require'    => '分类名称必填',
        'name.max'        => '名称长度至多15个汉字',
        'name.unique'     => '商品分类名称重复',
        'pid.require'     => '父类id不能为空',
        'pid.number'      => '父类ID不合法',
        'pid.different'   => '父分类不能为自己',
    ];
}