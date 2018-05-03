<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-11
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\model\Base;

/**
 * 订单备注模型
 */
class OrderGoods extends Base
{
	// 设置数据表（不含前缀)
    protected $name = 'order_goods';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk 	= 'rec_id';

    /**
     * 发货状态
     */
    public function getIsSendAttr($value)
    {
        $status = [
           	0 => '未发货',
	        1 => '已发货',
	        2 => '已换货',
	        3 => '已退货',
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }

}