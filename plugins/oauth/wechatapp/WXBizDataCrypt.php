<?php

/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

class WXBizDataCrypt
{
    private $appid;
	private $sessionKey;

	/**
	 * 构造函数
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 * @param $appid string 小程序的appid
	 */
	public function __construct( $appid, $sessionKey)
	{
		$this->sessionKey = $sessionKey;
		$this->appid = $appid;
	}


	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
     *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData( $encryptedData, $iv, &$data )
	{
		require_once(dirname(__FILE__)."/ErrorCode2.php");

		if (strlen($this->sessionKey) != 24) {
			return ErrorCode2::$IllegalAesKey;
		}
		$aesKey=base64_decode($this->sessionKey);

        
		if (strlen($iv) != 24) {
			return ErrorCode2::$IllegalIv;
		}
		$iv = str_replace(array(' '),array('+'),$iv);
		$aesIV=base64_decode($iv);

		$encryptedData = str_replace(array(' '),array('+'),$encryptedData);
		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		//$result = $this->decrypt($aesCipher,$aesKey,$aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL ) {
			return ErrorCode2::$IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->appid ) {
			return ErrorCode2::$IllegalBuffer;
		}
		$data = $result;
		return ErrorCode2::$OK;
	}

	function decrypt($aesCipher,$aesKey,$aesIV){
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $aesIV);
        mcrypt_generic_init($module, $aesKey, $aesIV);
        $decrypt_data = mdecrypt_generic($module, $aesCipher);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
	
		$decrypt_data = $this->stripPKSC7Padding($decrypt_data);
		return $decrypt_data;
	
	}

	/**
	 * 移去填充算法
	 * @param string $source
	 * @return string
	 */
	function stripPKSC7Padding($source){
		$source = trim($source);
		$char = substr($source, -1);
		$num = ord($char);
		if($num==62)return $source;
		$source = substr($source,0,-$num);
		return $source;
	}

}
