<?php
/**
 * @copyright (c) 2011 [group]
 * @file pointlog.php
 * @brief 奖励日志记录处理类
 * @author chendeshan
 * @date 2011-6-15 14:58:39
 * @version 0.6
 */
class Point
{
	//错误信息
	private $error  = '';

	private $goldName  = '金币';
	//构造函数
	function __construct()
	{
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$this->goldName = isset($site_config['goldName']) ?  $site_config['goldName']:'金币';
	}

	/**
	 * @brief 奖励操作的构造函数
	 * @param array $config => array('user_id' => 用户ID , 'point' => 奖励增减(正，负区分) , 'log' => 日志记录内容)
	 */
	public function update($config)
	{
		
		if(!isset($config['event']))
		{
			$config['event'] = 0;
		}
		if(!isset($config['event_id']))
		{
			$config['event_id'] = 0;
		}
		if(!isset($config['com_id']))
		{
			$config['com_id'] = 0;
		}
		if(!isset($config['work_id']))
		{
			$config['work_id'] = 0;
		}


		if(!isset($config['user_id']) || intval($config['user_id']) <= 0)
		{
			$this->error = '用户ID不能为空';
		}
		else if(!isset($config['point']) || intval($config['point']) == 0)
		{
			$this->error = $this->goldName.'格式不正确';
		}
		else if(!isset($config['log']))
		{
			$this->error = $this->goldName.'日志内容不正确';
		}
		else
		{
			$is_success = $this->editPoint($config['user_id'],$config['point'],$config['event'],$config['event_id']);
			//记录单位，记录工作
			if($is_success!==false)
			{
				$config['com_id'] = $is_success['com_id'];
				$config['work_id'] = $is_success['work_id'];
				if($logId = $this->writeLog($config))
				{
					return $logId;
					$this->error = '记录日志失败';
				}
			}
			else
			{
				$this->error = $this->goldName.'更新失败';
			}
		}

		return $this->error == '' ? true:false;
	}

	//返回错误信息
	public function getError()
	{
		return $this->error;
	}

	/**
	 * @brief 日志记录
	 * @param array $config => array('user_id' => 用户ID , 'point' => 奖励增减(正，负区分) , 'log' => 日志记录内容)
	 */
	private function writeLog($config)
	{
		//修改pointLog表
		$poinLogObj    = new IModel('point_log');
		$pointLogArray = array(
			'user_id' => $config['user_id'],
			'datetime'=> ITime::getDateTime(),
			'value'   => $config['point'],
			'intro'   => $config['log'],
			'type'    => $config['point']<0?1:0,
			'event'   => $config['event'],
			'com_id'   => $config['com_id'],
			'work_id'   => $config['work_id'],
			'event_id'   => $config['event_id'],
		);
		$poinLogObj->setData($pointLogArray);
		return $poinLogObj->add();
	}

	/**
	 * @brief 奖励更新
	 * @param int $user_id 用户ID
	 * @param int $point   奖励数(正，负)
	 */
	private function editPoint($user_id,$point)
	{
		$memberObj   = new IModel('member');
		$member = $memberObj->getObj('user_id='.$user_id);
		
		$finnalpoint = $member['point'] + $point;
		if($finnalpoint < 0)
		{
			return false;
		}
		$com_id = 0;
		$work_id = 0;
		if($point>0){
			//记录公司积分
			$user_company = new IModel('user_company');
			$workinfo = $user_company->getObj('user_id='.$user_id.' and is_del=0 and is_accept=1');
			if($workinfo){
				$com_id = $workinfo['com_id'];
				$work_id = $workinfo['id'];
				
				$finnalpoint = $workinfo['point'] + $point;
				if($finnalpoint >= 0)
				{
					$user_company->setData(array('point' => 'point + '.$point));
					$user_company->update('id = '.$workinfo['id'],'point');
				}
				$companyDB = new IModel('company');
				$company = $companyDB->getObj('id='.$workinfo['com_id'].' and is_lock=0 and is_del=0');
				if($company){
					$finnalpoint = $company['point'] + $point;
					if($finnalpoint >= 0)
					{
						$companyDB->setData(array('point' => 'point + '.$point));
						$companyDB->update('id = '.$company['id'],'point');
					}
				}
			}
		}

		$memberArray = array('point' => 'point + '.$point);
		$memberObj->setData($memberArray);
		$memberObj->update('user_id = '.$user_id,'point');
		return array('com_id'=>$com_id,'work_id'=>$work_id);
	}

}