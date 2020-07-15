<?php
/**
 * @brief 学习接口
 * @class Study
 * 接口 访问路径
 *  http://iweb.goat.msns.cn:5088/api/study/studyList
 */
class Shop extends IApiController implements userAuthorization
{
	public function init()
	{
		$this->cacheAble = true;
		$this->cacheActions = array(
			'home'=>600,
			//'studyDetail'=>60,
			//'cateList'=>600,
		);
    }

    public function goodsList(){
        
    }

    public function goodsDetail(){
        
    }

    public function changeGoods(){
        //获取兑换商品详情
        //检查商品数量
        //检查积分余额

        //扣分，记录扣分记录，产生兑换订单
        //调用三方接口
        //更新订单状态
    }

}