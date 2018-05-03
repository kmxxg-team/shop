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
class OrderAction extends Base
{
	// 设置数据表（不含前缀)
    protected $name = 'order_action';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk 	= 'action_id';


    // ----------------------- 关联模型 ---------------------------

    /**
     * 关联用户
     */ 
    public function admin()
    {
        return $this->hasOne('Admmin', 'admin_id', 'action_user')->field('user_name');
    }



    // ------------------------ 读取器 ----------------------------

    /**
     * 下单时间
     */
    public function getLogTimeAttr($value)
    { 
        if ($value) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }

    /**
     * 支付时间
     */
    public function getPayTimeAttr($value)
    { 
        if ($value) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }

    /**
     * 支付状态
     */
    public function getOrderStatusAttr($value)
    {
        $status = [
            0 => '待确认',
	        1 => '已确认',
	        2 => '已收货',
	        3 => '已取消',                
	        4 => '已完成',//评价完
	        5 => '已作废',
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }

    /**
     * 配送状态
     */
    public function getShippingStatusAttr($value)
    {
        $status = [
           	0 => '未发货',
	        1 => '已发货',
	        2 => '部分发货',
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }

    /**
     * 支付状态
     */
    public function getPayStatusAttr($value)
    {
        $status = [
            0 => '未支付',
	        1 => '已支付',
	        2 => '部分支付',
	        3 => '已退款',
	        4 => '拒绝退款',
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }

}