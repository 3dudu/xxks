<?php
/**
 * @copyright Copyright(c) 2018 gaot.com
 * @brief Tools
 * @class Tools
 * @note
 */
class Wechatapi extends IApiController
{
    //微信公众平台对接
	public function notify()
	{
        $oid = IFilter::act(IReq::get('oid'));
		$wechat_facade = new wechat_facade($oid);
		$result = $wechat_facade->response();
		return;
	}
}