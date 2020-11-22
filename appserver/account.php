<?php
/**
 * @brief 公共模块
 * @class Block
 */
class Account extends IApiController
{
	public function init()
	{

	}

	//发送注册验证码
	public function sendBindMobileCode()
	{
		$mobile   = IReq::get('mobile');
		
		if(IValidate::mobi($mobile) == false)
		{
			IError::show(900004,'请填写正确的手机号码');
		}
		$cacheObj = new ICache('file');

		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		//最小提现金额
		$needCaptcha =  isset($site_config['needCaptcha'])?$site_config['needCaptcha']:0;

		if($needCaptcha){
			$captcha  = IReq::get('captcha');
			$t = IFilter::act(IReq::get('t'));
			$_captcha = $cacheObj->get('captcha'.$t);
			if(!$captcha || !$_captcha || $captcha != $_captcha)
			{
				$this->setError('请填写正确的图形验证码',900005);
				return;
			}
		}

		$code = $cacheObj->get('mobile_code_t'.$mobile);
		if($code){
			IError::show('请60秒后再试',900006);
			return;
		}

		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('mobile = "'.$mobile.'"');
		if($memberRow)
		{
			IError::show('手机号已经被注册',900007);
			return;
		}

		$mobile_code = rand(1000,9999);
		$cacheObj->set('mobile_code_t'.$mobile,$mobile_code,60);
		$content = smsTemplate::mobileBind(array('code' => $mobile_code));
		$result = Hsms::send($mobile,$content,0);
		if($result == 'success')
		{
			$cacheObj->set('mobile_code'.$mobile,$mobile_code,300);
		}
		else
		{
			IError::show($result,900008);
		}
	}
	public function regUser2(){
		//生成随机账号
		$mobile   = IReq::get('mobile');
		$captcha= IFilter::act(IReq::get('captcha'));
		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$true_name  = IFilter::act(IReq::get('true_name'));
		$uuid  = IFilter::act(IReq::get('uuid'));
		$sex        = IFilter::act(IReq::get('sex'),'int');
		$cacheObj = new ICache('file');
		$t = IFilter::act(IReq::get('t'));
		$_captcha = $cacheObj->get('captcha'.$t);
		
		$captcha = strtolower($captcha);
		$_captcha = strtolower($_captcha);
			if(!$captcha || !$_captcha || $captcha != $_captcha)
			{
				$this->setError('请填写正确的图形验证码',900005);
				return;
			}
			$cacheObj->del('captcha'.$t);

		if(IValidate::mobi($mobile) == false)
		{
			IError::show('手机号格式不正确',900009);
		}

		if(!preg_match('|\S{6,32}|',$password))
    	{
    		IError::show("密码是字母，数字，下划线组成的6-32个字符");
    	}

    	if($password != $repassword)
    	{
    		IError::show("2次密码输入不一致");
    	}


		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('mobile = "'.$mobile.'"');
		if($memberRow)
		{
			IError::show('该手机号已经被注册，请直接登录',900011);
		}

		//插入user表
		$userObj = new IModel('user');

		$userArray = array(
			'username' => $mobile,
			'uuid' => $uuid,
			'password' => md5($password),
		);
		$userObj->setData($userArray);
		$user_id = $userObj->add();
		if(!$user_id)
		{
			$userObj->rollback();
			IError::show("用户创建失败");
		}
		$userArray['id'] = $user_id;
		$userArray['head_ico'] = "";

		//插入member表
		$memberArray = array(
			'user_id' => $user_id,
			'time'    => ITime::getDateTime(),
			'status'  => 1,
			'mobile'  => $mobile,
			'true_name'  => $true_name,
			'nickname'  => $true_name,
			'sex'          => $sex,
		);
		$memberObj = new IModel('member');
		$memberObj->setData($memberArray);
		$memberObj->add();
		$userArray['user_id'] = $user_id;
		$this->loginSuccess($userArray);
	}
	public function regUser(){
		//生成随机账号
		$mobile   = IReq::get('mobile');
		$mobile_code= IFilter::act(IReq::get('mobile_code'));
		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$true_name  = IFilter::act(IReq::get('true_name'));
		$idcode  = IFilter::act(IReq::get('idcode'));
		$sex        = IFilter::act(IReq::get('sex'),'int');


		if(IValidate::mobi($mobile) == false)
		{
			IError::show('手机号格式不正确',900009);
		}

		if(!preg_match('|\S{6,32}|',$password))
    	{
    		IError::show("密码是字母，数字，下划线组成的6-32个字符");
    	}

    	if($password != $repassword)
    	{
    		IError::show("2次密码输入不一致");
    	}

		$cacheObj = new ICache('file');
		$_mobileCode = $cacheObj->get('mobile_code'.$mobile);
		if(!$mobile_code || !$_mobileCode || $mobile_code != $_mobileCode)
		{
			IError::show('手机号验证码不正确',900010);
		}

		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('mobile = "'.$mobile.'"');
		if($memberRow)
		{
			IError::show('该手机号已经被注册，请直接登录',900011);
		}

		//插入user表
		$userObj = new IModel('user');

		$userArray = array(
			'username' => $mobile,
			'password' => md5($password),
		);
		$userObj->setData($userArray);
		$user_id = $userObj->add();
		if(!$user_id)
		{
			$userObj->rollback();
			IError::show("用户创建失败");
		}
		$userArray['id'] = $user_id;
		$userArray['head_ico'] = "";

		//插入member表
		$memberArray = array(
			'user_id' => $user_id,
			'time'    => ITime::getDateTime(),
			'status'  => 1,
			'mobile'  => $mobile,
			'true_name'  => $mobile,
			'sex'          => $sex,
			'idcode'          => $idcode,
		);
		$memberObj = new IModel('member');
		$memberObj->setData($memberArray);
		$memberObj->add();
		$userArray['user_id'] = $user_id;
		$this->loginSuccess($userArray);
	}


	private function loginSuccess($userRow){
		plugin::trigger("userLoginCallback",$userRow);
		$token = Api::userToken($userRow,$userRow['user_id']);
		$cookie = Api::showCookie($userRow);
				//获取用户各项统计数据
		//$statistics = Api::run('getMemberTongJi',$userRow['user_id']);

		$this->setRenderData(array(
			"userinfo"=>$userRow,
		//	"statistics"=>$statistics,
			"x-token"=>$token,
			"cookie"=>$cookie
		));
	}

	//退出登录
    function logout()
    {
		plugin::trigger('clearUser');
    }

    //用户登录
    function login_act()
    {
		//调用_userInfo登录插件
		$result = plugin::trigger('userLoginAct');
		
		if(is_array($result))
		{
			$this->loginSuccess($result);
		}
		else
		{
			$this->setError($result);
		}
	}


	public function oauth_callback(){
    	$oid = IFilter::act(IReq::get('oid'),'int');
		$reg = IFilter::act(IReq::get('reg'),'int');
		$updateuser = IFilter::act(IReq::get('updateuser'),'int');

    	$oauthObj = new OauthCore($oid);
    	$result   = $oauthObj->checkStatus($_GET);

    	if($result === true)
    	{
			$accInfo = $oauthObj->getAccessToken($_GET);
			if(is_array($accInfo)){
				$openid = $accInfo['openid'];
				$unionid = $accInfo['unionid'];
			}else{
				$openid = $accInfo;
				$unionid = false;
			}
			
			if($openid){
				$userRow = $oauthObj->oauthLogin($openid,$unionid);
				if($userRow){
					if(isset($userRow['error'])){
						IError::show(500005,$userRow['error']);
					}else{
						if($updateuser){
							//更新
							$newuserRow = $oauthObj->getUserInfo($openid);
							if(isset($newuserRow['unionid']) && $newuserRow['unionid']){
								$unionid = $newuserRow['unionid'];
							}
							$newuserRow['user_id'] =  $userRow['user_id'];
							$oauthObj->updateUser($newuserRow,$openid,$unionid);
						}
						$this->loginSuccess($userRow);
					}
				}elseif($reg){
					//注册
					$userRow = $oauthObj->getUserInfo($openid);
					if($userRow){
						if(isset($userRow['unionid']) && $userRow['unionid']){
							$unionid = $userRow['unionid'];
						}
						$oauthObj->regUser($userRow,$openid,$unionid,$userRow['uuid']);
						$userRow = $oauthObj->oauthLogin($openid,$unionid);
						if(isset($userRow['error'])){
							IError::show(500005,$userRow['error']);
						}else{
							$this->loginSuccess($userRow);
						}
					}else{
						IError::show(500003,"获取微信个人信息失败");
					}
				}else{
					ISafe::id();
					IError::show(500004,"您的微信未绑定账号");
				}
			}else{
                IError::show(500002,"获取微信信息失败");
			}
    	}
    	else
    	{
            IError::show(500001,"微信授权失败");
    	}
	}


	//手机短信找回密码
	function reset_password_mobile()
	{
		$mobile = IReq::get("mobile");
		if($mobile === null || !IValidate::mobi($mobile))
		{
			IError::show(403,"请输入正确的电话号码");
		}

		$mobile_code = IFilter::act(IReq::get('find_mobile_code'));
		if($mobile_code === null)
		{
			IError::show(403,"请输入短信校验码");
		}

		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));

		if(!preg_match('|\S{6,32}|',$password))
    	{
    		IError::show("密码是字母，数字，下划线组成的6-32个字符");
    	}

    	if($password != $repassword)
    	{
    		IError::show("2次密码输入不一致");
    	}

		$cacheObj = new ICache('file');
		$_mobileCode = $cacheObj->get('find_mobile_code_t'.$mobile);

		if(!$mobile_code || !$_mobileCode || $mobile_code != $_mobileCode)
		{
			IError::show('手机号验证码不正确',900010);
			return;
		}


		$userDB = new IModel('user as u , member as m');
		$userRow = $userDB->getObj('m.mobile = "'.$mobile.'" and u.id = m.user_id');
		if($userRow)
		{
			if($userRow['status']!='1')
			{
				IError::show('账号被锁或不存在',900011);
				return;
			}
			else
			{
				$userObj = new IModel('user');
				$userArray = array(
					'password' => md5($password),
				);
				$userObj->setData($userArray);
				$userObj->update('id='.$userRow['id']);
			}
		}
		else
		{
			IError::show('手机号码不存在',900010);
		}
	}

	//发送注册验证码
	public function resetMobileCode()
	{
		$mobile   = IReq::get('mobile');
		
		if(IValidate::mobi($mobile) == false)
		{
			IError::show(900004,'请填写正确的手机号码');
			return;
		}
		$cacheObj = new ICache('file');

		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		//最小提现金额
		$needCaptcha =  isset($site_config['needCaptcha'])?$site_config['needCaptcha']:0;

		if($needCaptcha){
			$captcha  = IReq::get('captcha');
			$t = IFilter::act(IReq::get('t'));
			$_captcha = $cacheObj->get('captcha'.$t);
			if(!$captcha || !$_captcha || $captcha != $_captcha)
			{
				$this->setError('请填写正确的图形验证码',900005);
				return;
			}
		}

		$code = $cacheObj->get('find_mobile_code_t'.$mobile);
		if($code){
			IError::show('请60秒后再试',900006);
			return;
		}

		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('mobile = "'.$mobile.'"');
		if(!$memberRow)
		{
			IError::show('手机号没有注册',900007);
			return;
		}

		if($memberRow['status']!='1')
		{
			IError::show('账号被锁或不存在',900011);
			return;
		}

		$mobile_code = rand(1000,9999);
		$cacheObj->set('find_mobile_code_t'.$mobile,$mobile_code,60);
		$content = smsTemplate::mobileFind(array('code' => $mobile_code));
		$result = Hsms::send($mobile,$content,0);
		if($result == 'success')
		{
			$cacheObj->set('find_mobile_code_t'.$mobile,$mobile_code,300);
		}
		else
		{
			IError::show($result,900008);
		}
	}

}