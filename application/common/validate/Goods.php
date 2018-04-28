<?php
namespace app\common\validate;

use think\Validate;

class Goods extends Validate
{
	// 验证规则
    protected $rule = [
        'goods_name'            => ['require', 'min:3', 'max:150', 'unique:goods'],
        'cat_id'                => ['number', 'gt:0'],
        'goods_sn'              => ['unique:goods', 'max:20'],
        'shop_price'            => ['require', 'regex'=>'([1-9]\d*(\.\d*[1-9])?)|(0\.\d*[1-9])'],
        'market_price'          => ['require', 'regex'=>'\d{1,10}(\.\d{1,2})?$', 'checkMarketPrice'],
        'weight'                => ['regex'=>'\d{1,10}(\.\d{1,2})?$'],
        'give_integral'         => ['regex'=>'^\d+$'],
        // 'exchange_integral'     => ['checkExchangeIntegral'],
        // 'is_virtual'            => ['checkVirtualIndate'],
    ];
    
    //错误信息
    protected $message  = [
        'goods_name.require'                            => '商品名称必填',
        'goods_name.min'                                => '名称长度至少3个字符',
        'goods_name.max'                                => '名称长度至多50个汉字',
        'goods_name.unique'                             => '商品名称重复',
        'cat_id.number'                                 => '商品分类必须填写',
        'cat_id.gt'                                     => '商品分类必须选择',
        'goods_sn.unique'                               => '商品货号重复',
        'goods_sn.max'                                  => '商品货号超过长度限制',
        // 'goods_num.checkGoodsNum'                       => '抢购数量不能大于库存数量',
        'shop_price.require'                            => '本店售价必须',
        'shop_price.regex'                              => '本店售价格式不对',
        'market_price.require'                          => '市场价格必填',
        'market_price.regex'                            => '市场价格式不对',
        'market_price.checkMarketPrice'                 => '市场价不得小于本店价',
        'weight.regex'                                  => '重量格式不对',
        'give_integral.regex'                           => '赠送积分必须是正整数',
        // 'exchange_integral.checkExchangeIntegral'       => '积分抵扣金额不能超过商品总额',
        // 'is_virtual.checkVirtualIndate'                 => '虚拟商品有效期不得小于当前时间',
    ];

    /**
     * 检查市场价
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function  checkMarketPrice($value,$rule,$data){
        if($value < $data['shop_price']){
            return false;
        }else{
            return true;
        }
    }
}