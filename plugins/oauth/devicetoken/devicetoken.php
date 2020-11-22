<?php
/**
 * 设备登录
 * @author nswe
 */
class Devicetoken extends OauthBase
{
	private $aesKey = '';
    private $encrypt = '1';
    private $aesiv = "1234567812345678";

	public function __construct($config)
	{
		$this->aesKey = isset($config['aesKey']) ? $config['aesKey'] : "";
        $this->encrypt = isset($config['encrypt']) ? $config['encrypt'] : "";
        $this->aesiv = isset($config['aesiv']) ? $config['aesiv'] : "1234567812345678";
	}

	public function getFields()
	{
		return array(
			'aesKey'=> array(
				'label' => 'aesKey',
				'type'  => 'string',
            ),
            'encrypt'=> array(
				'label' => 'encrypt',
				'type'  => 'string',
            ),
            'aesiv'=> array(
				'label' => 'aesiv',
				'type'  => 'string',
			),
		);
	}
	
	function getLoginUrl($scop=true)
	{
		return "";
	}

	function checkStatus($parms)
	{
		if(isset($parms['error']) || !isset($parms['code']))
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
		if(!$parms['code']){
			$parms['code'] = $_GET['code'];
        }
        ISession::clearAll();

        if($this->encrypt=='1'){
            /*
            if(isset($parms['sign'])){
                $sign = $parms['sign'];
            }else{
                IError::show('授权失败',505);
                return "";
            }
            $val_sign = IHash::md5($parms['code'].$this->aesKey);
            if($sign==$val_sign){
                $deviceCode = $parms['code'];
            }else{
                IError::show('授权失败',505);
                return "";

            }*/
            $deviceCode = $this->decrypt($parms['code'],$this->aesKey,$this->aesiv);
        }else{
            $deviceCode = $parms['code'];
        }

        if($deviceCode){
            ISession::set('deviceCode',$deviceCode);
            return array('openid'=>$deviceCode,'unionid'=>$deviceCode);
        }else{
            IError::show('授权失败',505);
            return "";

        }
	}

	function getUserInfo($openid='')
	{
        if($openid){
            $deviceCode = $openid;
        }else{
            $deviceCode = ISession::get('deviceCode');
        }
		if($deviceCode){
			$userInfo['id']   = $deviceCode;
			$userInfo['name'] = '游客'.IHash::random(8);
			$userInfo['sex'] = 1;
			$userInfo['city'] = "";
			$userInfo['province'] = "";
			$userInfo['country'] = "";
			$userInfo['headimgurl'] = "";
			$userInfo['unionid'] = "";
			$userInfo['uuid'] = $deviceCode;
		}else{
            IError::show('授权失败',505);
        }
		return $userInfo;
    }
    
    function encrypt($str,$screct_key,$iv){
        //AES, 128 模式加密数据 CBC
       // $screct_key = base64_decode($screct_key);
        $str = trim($str);
       // $str = $this->addPKCS7Padding($str);
        //$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC),1);

        $result=openssl_encrypt( $str, "AES-128-CBC", $screct_key, 1,$iv);


       // $encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
        return base64_encode($result);
    }
    
    /**
     * 解密方法
     * @param string $str
     * @return string
     */
     function decrypt($str,$screct_key,$iv){
        //AES, 128 模式加密数据 CBC
        $str = base64_decode($str);
      //  $screct_key = base64_decode($screct_key);
       // $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC),1);
      //  $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
    //    $encrypt_str = trim($encrypt_str);
    
        $encrypt_str=openssl_decrypt( $str, "AES-128-CBC", $screct_key, 1,$iv);


       // $encrypt_str = $this->stripPKSC7Padding($encrypt_str);
        return $encrypt_str;
    
    }
    



}
