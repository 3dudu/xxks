<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file OauthCore.php
 * @brief oauth协议登录接口
 * @author chendeshan
 * @date 2017/1/18 18:29:40
 * @version 4.7
 *
 * @update
 * @info 增加OauthBase基类，所有oauth类都需要经过此类库
 * @date 2018/6/21 12:08:15
 * @version 5.2
 */

/**
 * @class OauthCore
 * @brief oauth协议接口
 */
class OauthCore
{
	//oauth对象实例化
	private $oauthObj = null;
	private $config = array();
	public $oauthTable='oauth_user';

	/**
	 * @brief 构造函数
	 * @param int $id oauthID参数
	 */
	public function __construct($id)
	{
		$oauthRow = $this->getOauthRow($id);
		if(!$oauthRow)
		{
			throw new IException("【ID:{$id}】的第三方登录方式不存在");
		}

		if($this->requireFile($oauthRow['file']))
		{
			$config   = unserialize($oauthRow['config']);
			$fileName = $oauthRow['file'];
			$this->oauthObj = new $fileName($config);
			$this->oauthObj->setOid($id);
			$this->oauthObj->initCache($id);
			if($config && is_array($config)){
				$this->config = array_merge($this->config,$config);
			}
		}
		else
		{
			throw new IException("【ID:{$id}】的第三方登录方式初始化失败");

			return false;
		}
	}

	public function getConfig(){
		return $this->config;
	}

	//获取字段数据
	public function getFields()
	{
		return $this->oauthObj->getFields();
	}

	//回调函数
	public function checkStatus($parms)
	{
		return $this->oauthObj->checkStatus($parms);
	}

	//获取平台的用户信息
	public function getUserInfo($openid='')
	{
		return $this->oauthObj->getUserInfo($openid);
	}

	//获取登录url地址
	public function getLoginUrl($url = '')
	{
		return $this->oauthObj->getLoginUrl($url);
	}

	//获取令牌数据
	public function getAccessToken($parms)
	{
		return $this->oauthObj->getAccessToken($parms);
	}

	//根据id值获取数据库中的数据
	private function getOauthRow($id)
	{
		$oauthObj = new IModel('oauth');
		return $oauthObj->getObj('id = '.$id);
	}

	//引入平台接口文件
	private function requireFile($fileName)
	{
		$classFile = IWeb::$app->getBasePath().'plugins/oauth/'.$fileName.'/'.$fileName.'.php';
		if(file_exists($classFile))
		{
			include_once($classFile);
			return true;
		}
		else
		{
			return false;
		}
	}


	public function addOauthUser($user_id,$openid,$union_id=false){
		// 插入关系表
		$oauthUserObj = new IModel($this->oauthTable);
		$oauthUserData = array(
			'oauth_user_id' => $openid,
			'oauth_id' => $this->oauthObj->getOid(),
			'datetime' => ITime::getDateTime(),
			'user_id' => $user_id,
		);
		
		if(isset($union_id) && $union_id){
			$oauthUserData['union_id'] = $union_id;
		}

		$oauthUserObj->setData($oauthUserData);
		$reult = $oauthUserObj->add();
		return $reult;
	}

	public function checkUserOauth($user_id){
		$oauthUserObj = new IModel($this->oauthTable);
		return $oauthUserObj->getObj('user_id='.$user_id.' and oauth_id='.$this->oauthObj->getOid());
	}

		
	public function checkUser($openid='',$union_id=false){
		$oauth_userObj = new IModel($this->oauthTable);
        $ouserRow = $oauth_userObj->getObj("oauth_user_id='".$openid."'");
        if($ouserRow)
        {
			if($union_id && $ouserRow['union_id']!=$union_id){
				$oauth_userObj->setData(array('union_id'=>$union_id));
				$oauth_userObj->update("oauth_user_id='".$openid."'");
			}
            return $ouserRow;
		}
		if($union_id){
			$ouserRow = $oauth_userObj->getObj("union_id='".$union_id."'");
			if($ouserRow)
			{
				$oauth_userObj->setData(array('union_id'=>$union_id,'oauth_id'=>$this->oauthObj->oid,'oauth_user_id'=>$openid,'user_id'=>$ouserRow['user_id']));
				$id=$oauth_userObj->add();
				$ouserRow['oauth_id'] = $this->oauthObj->oid;
				$ouserRow['oauth_user_id'] = $openid;
				$ouserRow['id'] = $id;
				return $ouserRow;
			}
		}
		return false;
	}

	public function oauthLogin($openid,$unionid=false){
		$ouserRow = $this->checkUser($openid,$unionid);
		if($ouserRow){
			$userObj = new IModel('user');
			$userRow = $userObj->getObj('id='.$ouserRow['user_id']);
			if($userRow){
				$userRow = plugin::trigger("isValidUser",array($userRow['username'],$userRow['password']));
				if($userRow){
					return $userRow;
				}else{
					return array('error'=>"用户被锁");
				}
			}else{
				return array('error'=>"用户无效");
			}
		}
	}

	public function regUser($userRow,$openid,$unionid='',$uuid=''){
		$userObj = new IModel('user');
		$username = 'uid_'.IHash::random(10);
		for(;;){
			$userCount = $userObj->getObj("username = '{$username}'", 'count(*) as num');
			// 没有重复的用户名
			if ($userCount['num'] == 0){
				break;
			}else {
				$username = 'uid_'.IHash::random(10);
			}
		}
		$userArray = array(
			'username' => $username,
			'password' => '',
			'uuid'=>$uuid,
		);
		if($userRow['headimgurl']){
			$userArray['head_ico']=$userRow['headimgurl'];
		}
		$userObj->setData($userArray);
		$user_id = $userObj->add();
		// 插入关系表，绑定
		$this->addOauthUser($user_id,$openid,$unionid);
		//更新用户信息
		$area = $userRow['country'].',' . $userRow['province'] . ',' . $userRow['city'];
		$memberArray = array(
			'user_id' => $user_id,
			'sex' => $userRow['sex'],
			'area' => trim($area,','),
			'time'    => ITime::getDateTime(),
			'nickname' => str_replace("'","",$userRow['name']),
		);
			// 保存头像，
		if($userRow['headimgurl']){
			$memberArray['logo']=$userRow['headimgurl'];
		}
		$memberObj = new IModel('member');
		$memberObj->setData($memberArray);
		$memberObj->add();
	}

	public function updateUser($userRow,$openid,$unionid=''){
		$userObj = new IModel('user');
	
	
		if($userRow['headimgurl']){
			$userArray = array(
				'head_ico' => $userRow['headimgurl']
			);
			$userObj->setData($userArray);
			$userObj->update('id='.$userRow['user_id']);
		}
		//更新用户信息
		$area = $userRow['country'].',' . $userRow['province'] . ',' . $userRow['city'];
		$memberArray = array(
			'user_id' => $userRow['user_id'],
			'sex' => $userRow['sex'],
			'area' => trim($area,','),
			'nickname' => str_replace("'","",$userRow['name']),
		);
			// 保存头像，
		if($userRow['headimgurl']){
			$memberArray['logo']=$userRow['headimgurl'];
		}
		$memberObj = new IModel('member');
		$memberObj->setData($memberArray);
		$memberObj->update('user_id='.$userRow['user_id']);
	}
}

/**
 * @class Oauth
 * @brief oauth协议登录基础类
 */
abstract class OauthBase
{
	public $oid = 0;

	//获取回调URL地址
	protected function getReturnUrl()
	{
		$className = strtolower( get_class($this) );
		return IUrl::getHost().IUrl::creatUrl('/account/oauth_callback/oid/'.$this->oid);
	}
	public static $token_cache = null;
	public function initCache($id)
	{
		if(self::$token_cache==null){
			self::$token_cache = new ICache('redis','access_token_'.$id);
		}
	}
	public function setAccessToken($openid,$access_token,$expris){
		self::$token_cache->set($openid,$access_token,$expris);
	}

	public function hasAccessToken($openid){
		return self::$token_cache->get($openid);
	}

	public function setOid($oid){
		$this->oid = $oid;
	}

	public function getOid(){
		return $this->oid;
	}

	abstract public function getLoginUrl($url='');
	abstract public function checkStatus($parms);
	abstract public function getAccessToken($parms);
	abstract public function getUserInfo($openid='');
	abstract public function getFields();
}