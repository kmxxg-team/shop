<?php
// +----------------------------------------------------------------------
// | B2C商城系统
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈果 <yayuneko@163.com>
// +----------------------------------------------------------------------
// | Date: 2017-1-1
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\model\Base;

/**
 * 商品模型
 */
class Goods extends Base
{
    //设置数据表（不含前缀)
    protected $name = 'goods';
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'goods_id';

/*---------------------------------- 关联模型 -----------------------------------*/
	/**
	 * 商品分类关联模型
	 */
	public function goodsCategory()
	{
		return $this->belongsTo('goodsCategory', 'cat_id', 'id');
	}

/*----------------------------------- 获取器 ------------------------------------*/



/*----------------------------------- 修改器 ------------------------------------*/
	/**
	 * 商品货号修改器
	 */
	public function setGoodsSnAttr($value)
	{
		if (!empty($value)) {
			return $value;
		}
		return uniqid();
	}

	/**
	 * 商品关键词修改器
	 */
	public function setKeywordsAttr($value)
	{
		return stringUnique(' ', $value);
	}
}

