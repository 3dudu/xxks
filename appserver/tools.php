<?php
/**
 * @copyright Copyright(c) 2018 gaot.com
 * @brief Tools
 * @class Tools
 * @note
 */
class Tools extends IApiController
{
	public function init()
	{
		plugin::reg("onBeforeCreateAction@tools@suggestion",function(){
            plugin::trigger('getUser','suggestion');
        });
	}
    /**
	 * @brief 生成验证码
	 * @return image图像
	 */
	public function captcha()
	{
		//清空布局
		$this->layout = '';

		//配置参数
		$width      = IReq::get('w') ? IReq::get('w') : 130;
		$height     = IReq::get('h') ? IReq::get('h') : 45;
		$wordLength = IReq::get('l') ? IReq::get('l') : 5;
		$fontSize   = IReq::get('s') ? IReq::get('s') : 25;
		$t = IFilter::act( IReq::get('t') );
		$cacheObj = new ICache('file');
		$data = $cacheObj->del('captcha'.$t);
		//创建验证码
		$ValidateObj = new Captcha();
		$ValidateObj->width  = $width;
		$ValidateObj->height = $height;
		$ValidateObj->maxWordLength = $wordLength;
		$ValidateObj->minWordLength = $wordLength;
		$ValidateObj->fontSize      = $fontSize;
		
		//设置验证码
		$ValidateObj->CreateImage($text);
		$cacheObj->set('captcha'.$t,$text,600);
	}

	public function update(){
		$code = IFilter::act(IReq::get('c'),'int');
		$p = IFilter::act(IReq::get('p'),'int');
		$v = IFilter::act(IReq::get('v'));
		if(!$code)
		{
			IError::show(403,"传递的参数不正确");
			exit;
		}
		$tb_version = new IModel('version');
		$version_info = $tb_version->getObj('code='.$code.' and platform = '.$p);
		$status = '0';
		$curVersionOff = 1;//强制升级
		if($version_info){
			$status = $version_info['status'];
			$versionConf = JSON::decode($version_info['conf']);
			if(!$versionConf){
				$versionConf = array('testEnv'=>0);
			}
			$version_info = $tb_version->getObj('code='.$code.' and platform = '.$p.' and (status=1 or status=2) and curdate()>=start_date and curdate()<=end_date');
			if($version_info){
				//版本有效
				$curVersionOff=0;
			}
		}else{
			//未配置版本，只提示升级
			$curVersionOff=0;
			$versionConf = array('testEnv'=>0);
		}
		$versionConf['status']=$status;
		if($status=='2'){
			return array("curVersionOff"=>0,"versionConf"=>$versionConf);
		}
		//获取最新版本
		$newVersion = $tb_version->getObj("(baseVersion='' or baseVersion like '%".$v."%' ) and code>".$code." and platform = ".$p." and status=1 and curdate()>=start_date and curdate()<=end_date order by code desc");
		if($newVersion){
			unset($newVersion['conf']);
			if(rand(1,10)<=$newVersion['rate']){
				return array("curVersionOff"=>$curVersionOff,"versionConf"=>$versionConf,"newVersion"=>$newVersion);
			}
			return array("curVersionOff"=>$curVersionOff,"versionConf"=>$versionConf);
		}else{
			return array("curVersionOff"=>$curVersionOff,"versionConf"=>$versionConf);
		}
	}

	
    private function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
	}

	public function ossConf(){
		//_authorization::checkUserRights("");

		$subDir       = IFilter::act(IReq::get("subDir"),'filename','10');

		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();

		$id= $site_config['oss_id'];
		$key=  $site_config['oss_key'];
		$host =  $site_config['oss_host'];

		$now = time();
		$expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
		$end = $now + $expire;
		$expiration = $this->gmt_iso8601($end);
		$rootdir =  isset($site_config['oss_rootdir'])?$site_config['oss_rootdir']:'jfq';

		$dir = $rootdir.'/'.date("y-m-d");
		if($subDir){
			$dir = $dir.'/'.$subDir;
		}

		//最大文件大小.用户可以自己设置
		$condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
		$conditions[] = $condition; 

		//表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
		$start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
		$conditions[] = $start; 


		$arr = array('expiration'=>$expiration,'conditions'=>$conditions);
		//echo json_encode($arr);
		//return;
		$policy = json_encode($arr);
		$base64_policy = base64_encode($policy);
		$string_to_sign = $base64_policy;
		$signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

		$response = array();
		$response['OSSAccessKeyId'] = $id;
		$response['host'] = $host;
		$response['policy'] = $base64_policy;
		$response['signature'] = $signature;
		$response['expiration'] = $expiration;
		$response['expire'] = $end;
		$response['success_action_redirect'] = IUrl::getHost().IUrl::creatUrl("/pic/ossSuccess");
		//这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        $response['key'] = $dir.'/'.ITime::getDateTime('YmdHis').IHash::random(6).time();
		$this->setRenderData($response);
		return;
	}

	function helpinfo(){
		$id       = IFilter::act(IReq::get("id"),'int');
		$tb_help  = new IModel("help");
		$help_row = $tb_help->getObj($id);
		return $help_row;
	}

	public function ajax_auto(){
		$data = IFilter::act(IReq::get('data'));
		$keywords = IFilter::act(IReq::get('query'));
		$true_name = IFilter::act(IReq::get('true_name'));
		$idcode = IFilter::act(IReq::get('idcode'));
		$mobile = IFilter::act(IReq::get('mobile'));
		$page  = 1;
		$query = new IQuery("member");
		$where =  $data . ' like "'.$keywords.'%"';
		$where .= ($idcode && $data!='idcode')? ' and idcode like "'.$idcode.'%"' : '';
		$where .= ($true_name && $data!='true_name')? ' and true_name like "'.$true_name.'%"' : '';
		$where .= ($mobile && $data!='mobile')? ' and mobile like "'.$mobile.'%"' : '';
        $query->fields = 'true_name,idcode,mobile,user_id';
        $query->where  = $where ;
		$query->page   = $page;
		$queryList = $query->find();
		foreach($queryList as &$val){
			$companyDB        = new IModel('user_company as u,company as c');
			$work = $companyDB->getObj('u.com_id=c.id and u.is_del=0 and u.user_id='.$val['user_id'],'c.name');
			if($work){
				$val['label'] = $val['mobile'].' - '.$val['true_name'].' - '.$val['idcode'].' -- 在职 '.$work['name'];
			}else{
				$val['label'] = $val['mobile'].' - '.$val['true_name'].' - '.$val['idcode'].' -- 待业 ';
			}
		}
		return $queryList;
	}

	public function initWXJSConfig(){
		$oid = IFilter::act(IReq::get('oid'));
		$url       = IFilter::act(IReq::get("url"),'url');
		$JsSign = wechat_facade::getJsSign($oid,$url);
		return $JsSign;

	}

	public function wxOauthUrl(){
		$oid = IFilter::act(IReq::get('oid'));
		$url       = IFilter::act(IReq::get("url"),'url');
		$oauthObj = new OauthCore($oid);
		$url = $oauthObj->getLoginUrl($url);
		return $url;
	}

	public function suggestion(){
		$user_id   = IFilter::act($this->user['user_id'],'int');
		$c_type = IFilter::act( IReq::get('c_type') ,'int');
		$content = IFilter::act( IReq::get('content') ,'text');
		$tb = new IModel("suggestion");
		$data = array(
			'user_id'=>$user_id,
			'c_type'=>$c_type,
			'content'=>$content,
			'time'=>ITime::getDateTime(),
		);
		$tb->setData($data);
		$tb->add();
		return array('success'=>0);
    }
}