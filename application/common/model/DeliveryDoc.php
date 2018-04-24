<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-10
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\model\Base;

/**
 * 商品属性模型
 */
class DeliveryDoc extends Base
{
	// 设置数据表（不含前缀)
    protected $name = 'delivery_doc';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk 	= 'id';

    // 定义关联方法
    public function order()
    {
        return $this->belongsTo('order', 'order_id', 'order_id');
    }
}