<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-3
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\model\Base;

/**
 * 商品类模型
 */
class Order extends Base
{
	// 设置数据表（不含前缀)
    protected $name = 'order';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk   = 'order_id';


    // ----------------------- 关联模型 ---------------------------

    /**
     * 关联用户
     */ 
    public function user()
    {
        return $this->hasOne('User', 'user_id', 'user_id')->field('nickname');
    }

    /**
     * 关联用户
     */ 
    public function admin()
    {
        return $this->hasOne('Admin', 'admin_id', 'user_id')->field('user_name');
    }

    /**
     * 发货单
     */ 
    public function deliveryDoc()
    {
        return $this->hasOne('DeliveryDoc', 'order_id', 'order_id');
    }

    /**
     * 订单商品
     */ 
    public function orderGoods()
    {
        return $this->hasMany('OrderGoods', 'order_id', 'order_id');
    }

    /**
     * 订单操作记录
     */ 
    public function orderAction()
    {
        return $this->hasMany('OrderAction', 'order_id', 'order_id');
    }

    // ------------------------ 读取器 ----------------------------

    /**
     * 下单时间
     */
    public function getAddTimeAttr($value)
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
     * 发货状态
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
	        4 => '拒绝退款'
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }


    /**
     * 获取导出数据字段
     */
    public function getOrderFields()
    {
        return [
            'order_sn'                  => '订单编号',
            'add_time'                  => '日期',
            'consignee'                 => '收货人',
            'order_id'                 	=> '收货地址',
            'mobile'                    => '电话',
            'total_amount'              => '订单金额',
            'order_amount'              => '实际支付',
            'pay_name'                  => '支付方式',
            'pay_status'                => '支付状态',
            'shipping_status'           => '发货状态',
            'order_id'		            => '商品数量',
            'order_id'           		=> '商品信息',
        ];
    }
}