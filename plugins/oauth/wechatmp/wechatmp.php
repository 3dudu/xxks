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
class Wechatmp extends OauthBase
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
				'label' => 'AppID',
				'type'  => 'string',
			),
			'AppSecret'=> array(
				'label' => 'AppSecret',
				'type'  => 'string',
            ),
            "EncodingAESKey"=>array(
				'label' => 'EncodingAESKey',
				'type'  => 'string',
            ),
            "Token"=>array(
				'label' => 'Token',
				'type'  => 'string',
            ),
            "debug"=>array(
				'label' => 'debug',
				'type'  => 'string',
            ),
		);
	}

	//获取登录url地址
	public function getLoginUrl($url='')
	{
		if(!$url){
			$url = parent::getReturnUrl();
		}
    		$urlparam = array(
    			"appid=".$this->AppID,
    			"redirect_uri=".urlencode($url),
    			"response_type=code",
    			"scope=snsapi_userinfo",
    			"state=".rand(100,999),
    		);
    		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?".join("&",$urlparam)."#wechat_redirect";
    		return $url;
	    
	}

	//获取进入令牌
	public function getAccessToken($parms)
	{
		$urlparam = array(
			"appid=".$this->AppID,
			"secret=".$this->AppSecret,
			"code=".$parms['code'],
			"grant_type=authorization_code",
		);
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?".join("&",$urlparam);

		//模拟post提交
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($ch);
		if($result)
		{
			$tokenInfo = JSON::decode($result);
			if(isset($tokenInfo['access_token']) && isset($tokenInfo['openid']))
			{
				//保存令牌
				ISession::set('access_token',$tokenInfo['access_token']);
				ISession::set('oauth_openid',$tokenInfo['openid']);
				$openid = $tokenInfo['openid'];
				$accessToken = $tokenInfo['access_token'];
				$this->setAccessToken($openid,$accessToken,$tokenInfo['expires_in']);
				return $openid;
			}
			else
			{
				return null;
			}
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

		//当公众号和开发平台有多个应用会存在此 unionid,此时需要开放这里,可以让微信公众账号和微信OAuth平台同步用户信息
		$oauthUserDB = new IModel('oauth_user');
		$oldOauthUser= $oauthUserDB->getObj('oauth_user_id = "'.$unid.'" and oauth_id = 5');
		if($oldOauthUser && isset($reData['unionid']))
		{
			$oauthUserDB->setData(array('oauth_user_id' => $reData['unionid']));
			$oauthUserDB->update('id = '.$oldOauthUser['id']);
		}
		$unid = isset($reData['unionid']) ? $reData['unionid'] : $reData['openid'];
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
		$userInfo['sex'] = isset($reData['sex'])?$reData['sex']:1;
		$userInfo['city'] = isset($reData['city'])?$reData['city']:"";
		$userInfo['province'] = isset($reData['province'])?$reData['province']:"";
		$userInfo['country'] = isset($reData['country'])?$reData['country']:"";
		$userInfo['headimgurl'] = isset($reData['headimgurl'])?$reData['headimgurl']:"";
		return $userInfo;
	}

	public function checkStatus($parms)
	{
		if(isset($parms['code']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}