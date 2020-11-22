<?php
/**
 * @copyright (c) 2015 aircheng.com
 * @file wechatOauth.php
 * @brief wechat 的oauth协议登录接口
 * @author nswe
 * @date 2016/4/27 7:41:04
 * @version 4.3

 * @update
 * @date 2018/6/21 0:48:47
 * @info 增加APP登录功能
 * @version 5.2
 */

/**
 * @class wechatOauth
 * @brief wechat的oauth协议接口
 */
class wechatuniapp extends OauthBase
{
	private $AppID     = '';
	private $AppSecret = '';

	public function __construct($config)
	{
        $this->AppID     = isset($config['AppID'])     ? $config['AppID']     : "";
        $this->AppSecret = isset($config['AppSecret']) ? $config['AppSecret'] : "";
	}

	//后台可配置的参数
	public function getFields()
	{
		return array(
			'AppID' => array(
				'label' => 'AppID网站应用',
				'type'  => 'string',
			),
			'AppSecret'=>array(
				'label' => 'AppSecret网站应用',
				'type'  => 'string',
			),
		);
	}

	//获取登录url地址
	public function getLoginUrl()
	{
    		$urlparam = array(
    			"appid=".$this->AppID,
    			"redirect_uri=".urlencode(parent::getReturnUrl()),
    			"response_type=code",
    			"scope=snsapi_login",
    			"state=".rand(100,999),
    		);
    		$url = "https://open.weixin.qq.com/connect/qrconnect?".join("&",$urlparam)."#wechat_redirect";
    		return $url;
	}

	//获取进入令牌
	public function getAccessToken($parms)
	{
        $access_token = $parms['access_token'];
        $openid = $parms['openid'];
        $expires_in = $parms['expires_in'];
		if($access_token && $openid)
		{
				//保存令牌
				ISession::set('access_token',$access_token);
				ISession::set('oauth_openid',$openid);
				$this->setAccessToken($openid,$access_token,$expires_in);
				return $openid;
		}
		else
		{
			return null;
		}
	}

	//获取用户数据
	public function getUserInfo($openid='')
	{
		$openid      = $openid?$openid:ISession::get('oauth_openid');
		$accessToken = $this->hasAccessToken($openid);
		if(!$accessToken){
			$accessToken = ISession::get('access_token');
		}
		$urlparam    = array(
			'access_token='.$accessToken,
			'openid='.$openid,
		);
		//获取用户信息
		$apiUrl = "https://api.weixin.qq.com/sns/userinfo?";
		$apiUrl .= join("&",$urlparam);
		$json    = file_get_contents($apiUrl);
		if(stripos($json,"errcode") !== false)
		{
			return $json;
		}
		$reData = JSON::decode($json);

		//处理用户信息
		$unid = $reData['openid'];

		$name = substr($unid,-8);

		//获取微信用户信息
		if(isset($reData['nickname']))
		{
			$wechatName = trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u',"",$reData['nickname']));
			$name = $wechatName ? $wechatName : $name;
		}
		$userInfo['id']   = $reData['openid'];
		$userInfo['name'] = $reData['nickname'];
		$userInfo['union_id'] = isset($reData['unionid'])?$reData['unionid']:"";
		$userInfo['unionid'] = $userInfo['union_id'];
		$userInfo['sex'] = isset($reData['sex'])?$reData['sex']:1;
		$userInfo['city'] = isset($reData['city'])?$reData['city']:"";
		$userInfo['province'] = isset($reData['province'])?$reData['province']:"";
		$userInfo['country'] = isset($reData['country'])?$reData['country']:"";
		$userInfo['headimgurl'] = isset($reData['headimgurl'])?$reData['headimgurl']:"";
		return $userInfo;
	}

	public function checkStatus($parms)
	{
		if(isset($parms['access_token']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}