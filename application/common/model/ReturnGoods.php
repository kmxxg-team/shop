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

    // 定义关联方法
    public function goods()
    {
        return $this->hasOne('goods', 'goods_id', 'goods_id');
    }

    public function order()
    {
        return $this->belongsTo('order', 'order_id', 'order_id');
    }

    public function users()
    {
        return $this->hasOne('users', 'user_id', 'user_id');
    }

    // ------------------------ 读取器 ----------------------------

    /**
     * 状态
     */
    public function getStatusAttr($value)
    {
        $status = [
            -2 => '用户取消',
            -1 => '不同意',
            0  => '待审核',
            1  => '通过',
            2  => '已发货',
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

