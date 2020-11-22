<?php

/**
 * Created by PhpStorm.
 * User: raid
 * Date: 2017/1/13
 * Time: 17:16
 */

class Wechatapp extends OauthBase{
    	//实例
    private $instance = null;
    
    private $AppID  = '';
	private $AppSecret = '';

    //默认配置
    protected $config = [
        'url' => "https://api.weixin.qq.com/sns/jscode2session", //微信获取session_key接口url
        'appid' => 'your appId', // APPId
        'secret' => 'your secret', // 秘钥
        'grant_type' => 'authorization_code', // grant_type，一般情况下固定的
    ];

	public function __construct($config)
	{
        require_once(dirname(__FILE__)."/ErrorCode2.php");

        if(isset($config['AppID']) && $config['AppID']){
            $this->config['appid'] = $config['AppID'];
        }
        if(isset($config['url']) && $config['url']){
            $this->config['url'] = $config['url'];
        }
        if(isset($config['grant_type']) && $config['grant_type']){
            $this->config['grant_type'] = $config['grant_type'];
        }
        if(isset($config['AppSecret']) && $config['AppSecret']){
            $this->config['secret'] = $config['AppSecret'];
        }
        $this->initWechatApi();
    }
    
    private function initWechatApi(){
        if($this->instance == null)
		{
			$classFile = IWeb::$app->getBasePath().'plugins/wechat/wechat.php';
			$classFile2 = IWeb::$app->getBasePath().'plugins/wechat/EasyWechat.class.php';
			if(is_file($classFile))
			{
				include_once($classFile);
				include_once($classFile2);
                $options = array(
                'debug'=>true,
                'appid'=>$this->config['appid'], //填写高级调用功能的app id
                'appsecret'=>$this->config['secret'] //填写高级调用功能的密钥 		
                );
				$this->instance = new EasyWechat($options);
			}
			else
			{
				die('您的版本不支持微商城');
			}
		}
		return $this->instance;
    }

    public function getcreateWXAppQR($scene='',$page=''){
        return $this->instance->createWXAppQR($scene,$page);
    }


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
            'url'=> array(
				'label' => 'url',
				'type'  => 'string',
            ),
            'grant_type'=> array(
				'label' => 'grant_type',
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
    
    function getLoginUrl($scop=true)
	{
		return parent::getReturnUrl();
	}

    function checkStatus($parms)
	{
		if(isset($parms['error']) || (!isset($parms['code']) && !isset($parms['signature'])))
		{
			return false;
		}
		else
		{
			return true;
		}
    }
    

	function getAccessToken($parms)
	{
        $code =  IReq::get('code');
        $signature =  IReq::get('signature');
        if(!$code){
            if(!$signature){
                return ['code'=>ErrorCode2::$RequestTokenFailed, 'message'=>'请求Token失败,code参数错误'];
            }else{
                return false;
            }
        }
        $params = [
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret'],
            'js_code' => $code,
            'grant_type' => $this->config['grant_type']
        ];

        $res = $this->makeRequest($this->config['url'], $params);

        if ($res['code'] !== 200 || !isset($res['result']) || !isset($res['result'])) {
            return ['code'=>ErrorCode2::$RequestTokenFailed, 'message'=>'请求Token失败','wxres'=>$res];
        }
        $reqData = json_decode($res['result'], true);
        if (!isset($reqData['session_key'])) {
            return ['code'=>ErrorCode2::$RequestTokenFailed, 'message'=>'请求Token失败','wxres'=>$res];
        }

   

        $sessionKey = $reqData['session_key'];

        ISession::set('sessionKey',$sessionKey);
        $openid = $reqData['openid'];
        $this->setAccessToken($openid,$sessionKey,1200);
        if(isset($reqData['unionid'])){
            $unionid = $reqData['unionid'];
            return array('openid'=>$openid,'unionid'=>$unionid);
        }else{
            return $openid;
        }
	}

	function getUserInfo($openid='')
	{
        $sessionKey = ISession::get('sessionKey');
        if(!$sessionKey){
            $sessionKey = $this->hasAccessToken($openid);
        }
		$uuid = IFilter::act(IReq::get('uuid'));

        $rawData =  IReq::get('rawData');
        $signature =  IReq::get('signature');
        $encryptedData =  IReq::get('encryptedData');
        $iv =  IReq::get('iv');
        $signature2 = sha1($rawData . $sessionKey);

        if ($signature2 !== $signature) return ['code'=>ErrorCode2::$SignNotMatch, 'message'=>'签名不匹配'];

        require_once(dirname(__FILE__)."/WXBizDataCrypt.php");

        $pc = new WXBizDataCrypt($this->config['appid'], $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        if ($errCode !== 0) {
            return ['code'=>$errCode, 'message'=>'解密信息错误'];
        }

        $data = json_decode($data, true);
        $userInfo = array();
		if($data && isset($data['nickName'])){
			$userInfo['id']   = $data['openId'];
			$userInfo['name'] = $data['nickName'];
			$userInfo['union_id'] = isset($data['unionId'])?$data['unionId']:"";
			$userInfo['sex'] = isset($data['gender'])?$data['gender']:1;
			$userInfo['city'] = isset($data['city'])?$data['city']:"";
			$userInfo['province'] = isset($data['province'])?$data['province']:"";
			$userInfo['country'] = isset($data['country'])?$data['country']:"";
            $userInfo['headimgurl'] = isset($data['avatarUrl'])?$data['avatarUrl']:"";
            $userInfo['uuid'] = $uuid;
        }
        
		return $userInfo;
	}



    /**
     * 发起http请求
     * @param string $url 访问路径
     * @param array $params 参数，该数组多于1个，表示为POST
     * @param int $expire 请求超时时间
     * @param array $extend 请求伪造包头参数
     * @param string $hostIp HOST的地址
     * @return array    返回的为一个请求状态，一个内容
     */
    public function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
    {
        if (empty($url)) {
            return array('code' => '100');
        }

        $_curl = curl_init();
        $_header = array(
            'Accept-Language: zh-CN',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache'
        );
        // 方便直接访问要设置host的地址
        if (!empty($hostIp)) {
            $urlInfo = parse_url($url);
            if (empty($urlInfo['host'])) {
                $urlInfo['host'] = substr(DOMAIN, 7, -1);
                $url = "http://{$hostIp}{$url}";
            } else {
                $url = str_replace($urlInfo['host'], $hostIp, $url);
            }
            $_header[] = "Host: {$urlInfo['host']}";
        }

        // 只要第二个参数传了值之后，就是POST的
        if (!empty($params)) {
            curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($_curl, CURLOPT_POST, true);
        }

        if (substr($url, 0, 8) == 'https://') {
            curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($_curl, CURLOPT_URL, $url);
        curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
        curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

        if ($expire > 0) {
            curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
            curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
        }

        // 额外的配置
        if (!empty($extend)) {
            curl_setopt_array($_curl, $extend);
        }

        $result['result'] = curl_exec($_curl);
        $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
        $result['info'] = curl_getinfo($_curl);
        if ($result['result'] === false) {
            $result['result'] = curl_error($_curl);
            $result['code'] = -curl_errno($_curl);
        }

        curl_close($_curl);
        return $result;
    }

    /**
     * 读取/dev/urandom获取随机数
     * @param $len
     * @return mixed|string
     */
    public function randomFromDev($len) {
        $fp = @fopen('/dev/urandom','rb');
        $result = '';
        if ($fp !== FALSE) {
            $result .= @fread($fp, $len);
            @fclose($fp);
        }
        else
        {
            trigger_error('Can not open /dev/urandom.');
        }
        // convert from binary to string
        $result = base64_encode($result);
        // remove none url chars
        $result = strtr($result, '+/', '-_');

        return substr($result, 0, $len);
    }



}