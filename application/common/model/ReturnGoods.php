<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-15
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\model\Base;

/**
 * 退换货模型
 */
class ReturnGoods extends Base
{
	// 设置数据表（不含前缀)
    protected $name = 'return_goods';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk 	= 'rec_id';

    // ----------------------- 关联模型 --------------------------
    
    /**
     * 订单关联
     */
    public function order()
    {
        return $this->belongsTo('Order', 'order_id', 'order_id');
    }

    /**
     * 用户关联
     */
    public function user()
    {
        return $this->hasOne('User', 'user_id', 'user_id');
    }

    /**
     * 订单货物关联
     */
    public function orderGoods()
    {
        return $this->belongsTo('OrderGoods', 'rec_id', 'rec_id');
    }

    /**
     * 申请时间
     */
    public function getAddTimeAttr($value)
    { 
        if ($value) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }

    // ------------------------ 读取器 ----------------------------

    /**
     * 状态
     */
    public function getStatusAttr($value)
    {
        $status = [
            -2 => '服务单取消',
            -1 => '审核失败',
            0  => '待审核',
            1  => '审核通过',
            2  => '买家发货',
            3  => '已收货',
            4  => '换货完成',
            5  => '退款完成',
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }

    /**
     * 类型
     */
    public function getTypeAttr($value)
    {
        $status = [
            0 => '仅退款',
            1 => '退货退款',
            2 => '换货',
        ];

        if (isset($status[$value])) {
            return $status[$value];
        }
        return '未知';
    }

}

