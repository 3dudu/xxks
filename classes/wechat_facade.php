<?php
/**
 * @copyright (c) 2015 easier.cn
 * @file wechat_facade.php
 * @brief 微信封装类库
 * @author nswe
 * @date 2015/3/29 15:15:14
 * @version 3.1
 */
class wechat_facade
{
	//实例
	public static $instance = null;

	/**
	 * @brief 创建分词类库实例
	 */
	private static function createInstance($oid)
	{
		if(self::$instance == null)
		{
			$classFile = IWeb::$app->getBasePath().'plugins/wechat/wechat.php';
			$classFile2 = IWeb::$app->getBasePath().'plugins/wechat/EasyWechat.class.php';
			if(is_file($classFile))
			{
				include_once($classFile);
				include_once($classFile2);

				$oauthObj = new OauthCore($oid);
				$wx_config = $oauthObj->getConfig();
				if($wx_config){
					$options = array(
						'token'=>isset($wx_config['Token'])?$wx_config['Token']:'', 
						'encodingaeskey'=>isset($wx_config['EncodingAESKey'])?$wx_config['EncodingAESKey']:'',
						'appid'=>isset($wx_config['AppID'])?$wx_config['AppID']:'',
						'appsecret'=>isset($wx_config['AppSecret'])?$wx_config['AppSecret']:'',		
						'debug'=>isset($wx_config['debug'])?$wx_config['debug']:0	
					);
				}else{
					$options = array();
				}
				self::$instance = new EasyWechat($options);
			}
			else
			{
				die('初始化微信api失败');
			}
		}
		return self::$instance;
	}

	//微信推送的相应
	public static function response($oid)
	{
		$instance = self::createInstance($oid);

		$instance->valid();
		$type = $instance->getRev()->getRevType();
	    switch($type) {
	    		case Wechat::MSGTYPE_TEXT:
					$revContent = $instance->getRevContent();
					if($revContent=="绑定") {
						self::bind($instance->getRevFrom());
						return true;
                    }
					return false;
	    			break;
	    		case Wechat::MSGTYPE_EVENT:
					$recEvent = $instance->getRevEvent();
					if($recEvent['event']==Wechat::EVENT_SUBSCRIBE){
					    $fromopenid = $instance->getRevFrom();
						self::subscribe($fromopenid);
						return true;
					}else if($recEvent['event']==Wechat::EVENT_UNSUBSCRIBE){
						//
					    $fromopenid = $instance->getRevFrom();
						self::unSubscribe($fromopenid);
						return true;
					}else if($recEvent['event']==Wechat::EVENT_MENU_CLICK){
					    $recEventMsg = $instance->getRevEvent();
					    if($recEventMsg['key']=='bind'){
					        //解绑消息
							self::bind($instance->getRevFrom());
							return true;
						}
						if($recEventMsg['key']=='task'){
							self::taskLog($instance->getRevFrom());
							return true;
						}
						return false;
					}
					return false;
					break;
				default:
					return false;
					break;
	    }
	}



	//微信获取菜单
	public static function getMenu($oid)
	{
	   $instance = self::createInstance($oid);
	   $json = $instance->getMenu();
	   return $json['menu'];
	}

	/**
	 * @brief 微信设置菜单
	 * @param json $menuData
	 */
	public static function setMenu($oid,$menuData)
	{
		$instance = self::createInstance($oid);
		//设置菜单
        $result = $instance->createMenu(json_decode($menuData));		
        return $result;
	}
	
	

	/**
	 * @brief 微信jssdk初始参数
	 * @param json $menuData
	 */
	public static function getJsSign($oid,$url)
	{
	    $instance = self::createInstance($oid);
	    //设置菜单
	    $result = $instance->getJsSign($url);
	    return $result;
	}
	
	/**
	 * @brief 微信下载上传的图片
	 * @param json $menuData
	 */
	public static function getMedia($oid,$media_id)
	{
	    $instance = self::createInstance($oid);
	    //设置菜单
	    $result = $instance->getMedia($media_id,true);
	    return $result;
	}
    /**
     * @brief 微信下载上传的图片到服务器上
     * @param json $menuData
     */
    public static function downloadMedia($oid,$media_id,$is_video=false,$file_name=false,$savedir=false)
    {
        $instance = self::createInstance($oid);
        //设置菜单
        $result = $instance->downloadMedia($media_id,$is_video,$file_name,$savedir);
        return $result;
    }
	/**
	 * 发送评价提醒通知
	 * @param unknown $notifyData
	 * @return raw
	 */
	public static function sendCommentNotify($oid,$notifyData)
	{
	    $instance = self::createInstance($oid);
	    //设置菜单
	    $result = $instance->sendTemplateMessage($notifyData);
	    return $result;
	}
    /**
     * 发送新建立投递提醒通知
     * @param unknown $notifyData
     * @return raw
     */
    public static function sendNewResumeNotify($oid,$notifyData)
    {
        $instance = self::createInstance($oid);
        //设置菜单
        $result = $instance->sendTemplateMessage($notifyData);
        return $result;
	}

	public static function sendTemplateMessageNotify($oid,$user_id,$notifyData)
    {
		if($notifyData){
			$mpUserObj = new IModel('wxmp_user');
			$mpUser = $mpUserObj->getObj("user_id=".$user_id);
			if($mpUser){
				$instance = self::createInstance($oid);
				$notifyData['touser']=$mpUser['open_id'];
				$instance->log($notifyData);
				$result = $instance->sendTemplateMessage($notifyData);
				return $result;
			}
		}
		return false;
    }
  
    /**
     * 获取用户分组列表
     * @return boolean|array
     */
    public static function getGroup($oid)
    {
        $instance = self::createInstance($oid);
        $result = $instance->getGroup();
        return $result;
    }
    /**
     * 获取用户所在分组
     * @param string $openid
     * @return boolean|int 成功则返回用户分组id
     */
    public function getUserGroup($oid,$openid){
        $instance = self::createInstance($oid);
        $result = $instance->getUserGroup($openid);
        return $result;
    }
    /**
     * 移动用户分组
     * @param int $groupid 分组id
     * @param string $openid 用户openid
     * @return boolean|array
     */
    public static function updateGroupMembers($oid,$groupid,$openid){
        $instance = self::createInstance($oid);
        $result = $instance->updateGroupMembers($groupid,$openid);
        return $result;
    }
    /**
     * 批量移动用户分组
     * @param int $groupid 分组id
     * @param string $openid_list 用户openid数组,一次不能超过50个
     * @return boolean|array
     */
    public static function batchUpdateGroupMembers($oid,$groupid,$openid_list){
        $instance = self::createInstance($oid);
        $result = $instance->batchUpdateGroupMembers($groupid,$openid_list);
        return $result;
	}
	
	public static function syncMembers($oid,$next_openid=''){
        $instance = self::createInstance($oid);
		$result = $instance->getUserList($next_openid);
		$mpUserObj = new IModel('wxmp_user');
		$memberObj = new IModel('member');

		$lastOpenid = '';
		if($result){
			foreach($result['data']['openid'] as $openid){
				//获取openid,记录
				$mpUser = $mpUserObj->getObj("open_id='".$openid."'");
				$data = $instance->getUserInfo($openid);
				$userInfo = array();
				if($data){
					$userInfo['nickname'] = $data['nickname'];
					$userInfo['sex'] = $data['sex'];
					$userInfo['city'] = $data['city'];
					$userInfo['province'] = $data['province'];
					$userInfo['country'] = $data['country'];
					$userInfo['headimgurl'] = $data['headimgurl'];
					$userInfo['subscribe_scene'] = $data['subscribe_scene'];
					$userInfo['union_id'] = isset($data['union_id'])?$data['union_id']:'';

					if ($mpUser) {
						$userInfo['is_look'] = $data['subscribe']?1:0;
						$userInfo['hadlook'] = 1;
						$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
						$mpUserObj->setData($userInfo);
						$mpUserObj->update("open_id='".$openid."'");
						$memberObj->setData(array("nickname"=>$userInfo['nickname']));
						$memberObj->update('user_id='.$mpUser['user_id']);
					} else {
						if($data['subscribe']){
							$userInfo['is_look'] = 1;
							$userInfo['hadlook'] = 1;
							$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
							$userInfo['datetime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
							$userInfo['open_id'] = $openid;
							$userInfo['oauth_id'] = 9;
							$mpUserObj->setData($userInfo);
							$mpUserObj->add();
						}
					}
				}
				$lastOpenid = $openid;
			}
			if($lastOpenid){
				self::syncMembers($lastOpenid);
			}
		}
        return true;
	}

	public static function syncOneMembers($oid,$openid=''){
        $instance = self::createInstance($oid);
		$mpUserObj = new IModel('wxmp_user');
		$memberObj = new IModel('member');

				//获取openid,记录
				$mpUser = $mpUserObj->getObj("open_id='".$openid."'");
				$data = $instance->getUserInfo($openid);
				$userInfo = array();
				if($data){
					$userInfo['nickname'] = $data['nickname'];
					$userInfo['sex'] = $data['sex'];
					$userInfo['city'] = $data['city'];
					$userInfo['province'] = $data['province'];
					$userInfo['country'] = $data['country'];
					$userInfo['headimgurl'] = $data['headimgurl'];
					$userInfo['subscribe_scene'] = $data['subscribe_scene'];
					$userInfo['union_id'] = isset($data['union_id'])?$data['union_id']:'';

					if ($mpUser) {
						$userInfo['is_look'] = $data['subscribe']?1:0;
						$userInfo['hadlook'] = 1;
						$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
						$mpUserObj->setData($userInfo);
						$mpUserObj->update("open_id='".$openid."'");
						$memberObj->setData(array("nickname"=>$userInfo['nickname']));
						$memberObj->update('user_id='.$mpUser['user_id']);
					} else {
						if($data['subscribe']){
							$userInfo['is_look'] = 1;
							$userInfo['hadlook'] = 1;
							$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
							$userInfo['datetime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
							$userInfo['open_id'] = $openid;
							$userInfo['oauth_id'] = 9;
							$mpUserObj->setData($userInfo);
							$mpUserObj->add();
						}
					}
				}else{
					$userInfo['is_look'] = 0;
					$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
					$mpUserObj->setData($userInfo);
					$mpUserObj->update("open_id='".$openid."'");
				}
        return true;
	}
	

	private static function unSubscribe($oid,$wxUserId){
	    $instance = self::createInstance($oid);
	    //关注
	    //获取openid,记录
	
		$mpUserObj = new IModel('wxmp_user');
		$mpUser = $mpUserObj->getObj("open_id='".$wxUserId."'");
		if($mpUser && $mpUser['user_id']){
			//解绑微信支付
			$user_payDB  = new IModel('user_pay');
			$nums = $user_payDB->del("user_id=".$mpUser['user_id']." and payname='".$wxUserId."'");
		}
        $oauthUserData = array(
			'is_look' => 0,
			'user_id' =>0,
        );
        $mpUserObj->setData($oauthUserData);
		$mpUserObj->update("open_id='".$wxUserId."'");
	}
	
	private static function subscribe($oid,$wxUserId){ 
	    $instance = self::createInstance($oid);
	    //关注
		//获取openid,记录
		$mpUserObj = new IModel('wxmp_user');
		$mpUser = $mpUserObj->getObj("open_id='".$wxUserId."'");


		$data = $instance->getUserInfo($wxUserId);
		$userInfo = array();
		if($data){
			$userInfo['nickname'] = $data['nickname'];
			$userInfo['sex'] = $data['sex'];
			$userInfo['city'] = $data['city'];
			$userInfo['province'] = $data['province'];
			$userInfo['country'] = $data['country'];
			$userInfo['headimgurl'] = $data['headimgurl'];
			$userInfo['union_id'] = isset($data['union_id'])?$data['union_id']:'';
			$userInfo['subscribe_scene'] = $data['subscribe_scene'];
		}
	    // 没有绑定账号
	    if ($mpUser) {
			$userInfo['is_look'] = 1;
			$userInfo['hadlook'] = 1;
			$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
	        $mpUserObj->setData($userInfo);
	        $mpUserObj->update("open_id='".$wxUserId."'");
            $instance->text("笑赚欢迎您回来，关注公众号获取最新赚钱信息！")->reply();
	    } else {
			$userInfo['is_look'] = 1;
			$userInfo['hadlook'] = 1;
			$userInfo['updateTime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
			$userInfo['datetime'] = ITime::getDateTime('Y-m-d H:i:s',$data['subscribe_time']);
			$userInfo['open_id'] = $wxUserId;
			$userInfo['oauth_id'] = 9;
	        $mpUserObj->setData($userInfo);
	        $mpUserObj->add();
			$instance->text("笑赚欢迎您，关注公众号获取最新赚钱信息！\n第一步：下载笑赚客户端\n第二步：点击菜单获取验证码绑定APP")->reply();
		}
	}

	private static function taskLog($oid,$wxUserId){
		$instance = self::createInstance($oid);
		$mpUserObj = new IModel('wxmp_user');
		$mpUser = $mpUserObj->getObj("open_id='".$wxUserId."'");
		if($mpUser && isset($mpUser['user_id']) && $mpUser['user_id']!='0'){
			$where = ' and event in (6,7,8,9,11)';
			$totalDB = new IModel('account_log');
			$total = $totalDB->getObj("user_id = ".$mpUser['user_id'] . $where,'sum(amount) as totalAmount');
			$totalAmount = $total['totalAmount'];
			if($totalAmount){
				$accountLog = $totalDB->query("user_id = ".$mpUser['user_id'] . $where,"*",'id desc',5);
				$re_msg = "总收益：".$totalAmount ;
				foreach($accountLog as $key=>$val){
					$re_msg .= ".\n".$val['time']." ".$val['note']." ".$val['amount']."元。";
				}
				$instance->text($re_msg)->reply();
			}else{
				$instance->text("还没有收益，快钱赚钱吧！")->reply();
			}
		}else{
			$instance->text("还没有绑定APP，请到笑赚APP中输入绑定")->reply();
		}


	
	}
	private static function bind($oid,$wxUserId){ 
		$instance = self::createInstance($oid);

		$mpUserObj = new IModel('wxmp_user');
		$mpUser = $mpUserObj->getObj("open_id='".$wxUserId."'");
		if($mpUser){
			if(isset($mpUser['user_id']) && $mpUser['user_id']!='0'){
				$instance->text("已绑定笑赚APP")->reply();
			}else{
				if(ITime::getDiffSec($mpUser['sendtime'])>0){
					$instance->text("不要重复获取验证码，绑定验证码是：".$mpUser['bindcode']."，请到笑赚APP中输入绑定")->reply();
				}else{
					$bindcode=IHash::random(6,'int');
					$userInfo = array('bindcode'=>$bindcode,'sendtime'=>ITime::pass(60*5));
					$mpUserObj->setData($userInfo);
					$mpUserObj->update("open_id='".$wxUserId."'");
					$instance->text("绑定验证码是：".$bindcode."，请到笑赚APP中输入绑定")->reply();
				}
			}
		}else{
			self::subscribe($oid,$wxUserId);
		}
	}
}
	