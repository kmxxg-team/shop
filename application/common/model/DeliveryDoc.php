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

    // ----------------------- 关联模型 ---------------------------

    /**
     * 订单
     */ 
    public function order()
    {
        return $this->hasOne('Order', 'order_id', 'order_id');
    }

    /**
     * 订单商品
     */ 
    public function orderGoods()
    {
        return $this->hasMany('OrderGoods', 'order_id', 'order_id');
    }

    /**
     * 订单备注
     */ 
    public function orderAction()
    {
        return $this->hasMany('OrderAction', 'order_id', 'order_id');
    }

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


    // ------------------------ 读取器 ----------------------------

    /**
     * 创建时间
     */
    public function getCreateTimeAttr($value)
    { 
        if ($value) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }

    /**
     * 发货方式
     */
    public function getSendTypeAttr($value)
    {
        $status = [
            0 => '自填快递',
	        1 => '在线预约',
	        2 => '电子面单',
	        3 => '无需物流',                
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }
}