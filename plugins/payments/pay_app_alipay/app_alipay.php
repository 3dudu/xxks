<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file direct_alipay.php
 * @brief 支付宝插件类[即时到帐]
 * @author nswe
 * @date 2011-01-27
 * @version 0.6
 * @note
 */

 /**
 * @class direct_alipay
 * @brief 支付宝插件类
 */

require_once 'aop/AopClient.php';
require_once 'aop/request/AlipayTradeAppPayRequest.php';
require_once 'aop/request/AlipayTradeQueryRequest.php';
require_once 'aop/request/AlipayFundTransToaccountTransferRequest.php';
class app_alipay extends paymentPlugin
{
	//支付插件名称
    public $name = 'app_alipay';
	public $message = 'fail';
	/**
	 * @see paymentplugin::getSubmitUrl()
	 */
	public function getSubmitUrl()
	{
		return '#';
	}

	/**
	 * @see paymentplugin::notifyStop()
	 */
	public function notifyStop()
	{
		echo $this->message;
	}

	/**
	 * @see paymentplugin::callback()
	 */
	public function callback($callbackData,&$paymentId,&$money,&$message,&$orderNo)
	{
		return serverCallback($callbackData,$paymentId,$money,$message,$orderNo);
	}

	/**
	 * @see paymentplugin::serverCallback()
	 */
	public function serverCallback($callbackData,&$paymentId,&$money,&$message,&$orderNo)
	{
		$aop = new AopClient;
		$aop->alipayrsaPublicKey = Payment::getConfigParam($paymentId,'m_PublicKey');
		unset($callbackData['WX_DATA']);
		$flag = $aop->rsaCheckV1($callbackData, null, $callbackData['sign_type']);
		$app_id = Payment::getConfigParam($paymentId,'m_appId');
		if($flag && $callbackData['app_id']== $app_id){
			//该分支在成功回调到NotifyCallBack方法，处理完成之后流程
			$orderNo = $callbackData['out_trade_no'];
			$trade_no = $callbackData['trade_no'];
			$money = $callbackData['total_amount'];
			$updateNum = $this->recordTradeNo($orderNo,$trade_no);
			if($callbackData['trade_status']=='TRADE_SUCCESS'||$callbackData['trade_status']=='TRADE_FINISHED'){
				$this->message = 'success';
				return 1;
			}else{
				$this->message = 'fail';
				return 1;			
			}

		} else{
			$this->message = 'check fail';
			return 0;
		}
	}

	/**
	 * @see paymentplugin::getSendData()
	 */
	public function getSendData($payment)
	{
		$aop = new AopClient;
		$aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
		$aop->appId = $payment['m_appId'];
		$aop->rsaPrivateKey = $payment['m_PrivateKey'];
		$aop->format = "json";
		$aop->charset = "UTF-8";
		$aop->signType = "RSA2";
		$aop->alipayrsaPublicKey = $payment['m_PublicKey'];
		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		$request = new AlipayTradeAppPayRequest();
		//SDK已经封装掉了公共参数，这里只需要传入业务参数
		$bizcontent = "{\"body\":\"".$payment['M_Remark']."\","
		                . "\"subject\":\"".$payment['R_Name']."\","
		                . "\"out_trade_no\":\"".$payment['M_OrderNO']."\","
		                . "\"timeout_express\":\"30m\","
		                . "\"total_amount\":\"".$payment['M_Amount']."\","
		                . "\"goods_type\":\"1\","
		                . "\"product_code\":\"QUICK_MSECURITY_PAY\""
		                . "}";
		$request->setNotifyUrl(urlencode($this->serverCallbackUrl));
		$request->setBizContent($bizcontent);
		//这里和普通的接口调用不同，使用的是sdkExecute
		$response = $aop->sdkExecute($request);
		//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
		if($payment['client']=='app'){
			$data['app'] = $payment['m_appId'];
			$data['url_r'] = $this->serverCallbackUrl;
			$data['alipay_str'] = $response;
			$data['data'] = $response;
			return $data;
		}else{
			$data['app'] = $payment['m_appId'];
			$data['url_r'] = $this->serverCallbackUrl;
			$data['alipay_str'] = htmlspecialchars($response);
			$data['data'] = htmlspecialchars($response);
			return $data;
		}
	}

	public function checkPayPay($paymentId,$orderNo){
		$data['result']='未支付';

		$aop = new AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$aop->appId = Payment::getConfigParam($paymentId,'m_appId');
		$aop->rsaPrivateKey = Payment::getConfigParam($paymentId,'m_PrivateKey');
		$aop->alipayrsaPublicKey=Payment::getConfigParam($paymentId,'m_PublicKey');
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset='UTF-8';
		$aop->format='json';
		$request = new AlipayTradeQueryRequest();
		$trade_no=$this->queryTradeNo($orderNo);
		if($trade_no!=''){
			$request->setBizContent("{" .
			"\"out_trade_no\":\"".$orderNo."\"," .
			"\"trade_no\":\"".$trade_no."\"" .
			"}");
		}else{
			$request->setBizContent("{" .
			"\"out_trade_no\":\"".$orderNo."\"".
			"}");
		}
		$result = $aop->execute($request); 
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			$data['result']=$result->$responseNode->msg;
			$data['money']=$result->$responseNode->total_amount;
			$data['tid']=$result->$responseNode->trade_no;
			if($trade_no==''){
				$this->recordTradeNo($orderNo,$data['tid']);
			}

		} else {
			$data['result']=$result->$responseNode->sub_msg;
		}
		
	    return $data;

	}


	/**
	 * 支付宝付款接口
	 */
	public function mmpaymkttransfers($payment)
	{
		$aop = new AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		$aop->appId = $payment['m_appId'];
		$aop->rsaPrivateKey = $payment['m_PrivateKey'];
		$aop->alipayrsaPublicKey=$payment['m_PublicKey'];
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset='UTF-8';
		$aop->format='json';
		$request = new AlipayFundTransToaccountTransferRequest();
		$request->setBizContent("{".
		"\"out_biz_no\":\"".$payment['pay_tid']."\",".
		"\"payee_type\":\"ALIPAY_LOGONID\"," .
		"\"payee_account\":\"".$payment['payname']."\"," .
		"\"amount\":\"".floatval($payment['money']/100)."\"," .
		"\"payee_real_name\":\"".$payment['truename']."\"," .
		"\"remark\":\"".$payment['message']."\"," .
		"}");

		$result = $aop->execute($request);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			$order_id=$result->$responseNode->order_id;
			$pay_date=$result->$responseNode->pay_date;
			$this->updateWithdrawLog($payment['pay_tid'],$order_id,$pay_date);
			return 'SUCCESS';
		} else {
			$msg = $result->$responseNode->order_id;
			$sub_code = $result->$responseNode->sub_code;
			$sub_msg = $result->$responseNode->sub_msg;
			$re_note='付款失败：'.$sub_msg;
			return $re_note;
		}
		
	    return $data;
	}


	/**
	 * @param 获取配置参数
	 */
	public function configParam()
	{

		$result = array(
			'm_PrivateKey'  => '开发者私钥',
			'm_PublicKey' => '支付宝公钥（Key）',
			'm_appId'      => 'appid',
		);
		return $result;
	}
}