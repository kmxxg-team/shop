<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王怀礼 <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-1-8
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\model\Base;

/**
 * 商品规格模型
 */
class spec extends Base
{
	// 设置数据表（不含前缀)
    protected $name = 'spec';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 定义关联方法
    public function goodsType()
    {
        return $this->belongsTo('goodsType','type_id' ,'id')->field('name');
    }

    // 定义关联方法
    public function specItem()
    {
        return $this->hasMany('specItem','spec_id' ,'id')->field('item');
    }
}