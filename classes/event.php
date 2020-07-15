<?php
/**
 * @copyright (c) 2011 [group]
 * @file pointlog.php
 * @brief 奖励日志记录处理类
 * @author chendeshan
 * @date 2011-6-15 14:58:39
 * @version 0.6
 */
class Event
{
	public function doEvent($event,$user_id){
		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('user_id = '.$user_id,'group_id');
		$user_group = $memberRow['group_id'];
		
		//用户组
		if($user_group != null)
		{
			$where=' and (user_group = "all" or user_group = "" or FIND_IN_SET('.$user_group.',user_group))';
		}
		else
		{
			$where=' and (user_group = "all" or user_group = "" )';
		}
		
		$eventQuery = new IQuery('event');
        $eventQuery->where = "event= '".$event."' and curdate()>=start_date and curdate()<=end_date and status=1".$where;
        $events = $eventQuery->find();
		
        $eventLog = new IModel('event_log');
        $where = 'user_id='.$user_id;
        $result = array();
        foreach($events as $event)
        {
			$event_type = $event['event_type'];
			$event_id = $event['id'];
			$event_num = $event['task_num'];
            if($event_type==1){
				//单次任务
                $eventLogs = $eventLog->getObj($where . ' and event_id ='. $event_id);
                if(!$eventLogs){
					//增加奖励，记录
                    $eventLogResult = $this->update($user_id,$event);
                    $result[] = $eventLogResult;
                }
            }elseif($event_type==2) {
				# 多次任务...
				$eventLogs = $eventLog->getObj($where . ' and event_id ='. $event_id,'count(id) as eventNum');
				if($event_num>$eventLogs['eventNum'] || $event_num==0){
					$eventLogResult = $this->update($user_id,$event);
					$result[] = $eventLogResult;
				}
            }elseif($event_type==3) {
                # 周期...
                $priedtype = $event['priedtype'];
                if($priedtype==1){
                    $priedwhere = " and DATE_FORMAT(log_time,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d')";
                }elseif ($priedtype==2) {
                    $priedwhere = " and DATE_FORMAT(log_time,'%x%v')=DATE_FORMAT(NOW(),'%x%v')";
                }elseif ($priedtype==3) {
                    $priedwhere = " and DATE_FORMAT(log_time,'%Y%m')=DATE_FORMAT(NOW(),'%Y%m')";
                }
                $eventLogs = $eventLog->getObj($where  . ' and event_id =' . $event_id . $priedwhere,'count(id) as eventNum');
                if( $event_num>$eventLogs['eventNum'] ){
                    //增加奖励，记录
                    $eventLogResult = $this->update($user_id,$event);
                    $result[] = $eventLogResult;
                }
            }
        }
        return $result;
	}
	/**
	 * @brief 操作的构造函数
	 * @param array $config => array('user_id' => 用户ID , 'point' => 奖励增减(正，负区分) , 'log' => 日志记录内容)
	 */
	public function update($user_id,$eventObj)
	{
		
		if(!isset($user_id) || intval($user_id) <= 0)
		{
			$this->error = '用户ID不能为空';
		}
		else if(!isset($eventObj['point']))
		{
			$this->error = '奖励数值格式不正确';
		}
		else
		{
			$siteConfigObj = new Config("site_config");
			$site_config   = $siteConfigObj->getInfo();
			$wawabiName = isset($site_config['wawabiName']) ?  $site_config['wawabiName']:'元';
			$goldName = isset($site_config['goldName']) ?  $site_config['goldName']:'积分';

			$action = $eventObj['point']>0?'增加':'减少';
			if($eventObj['point_type']==0){
				//(4)增加奖励
				$pointConfig = array(
					'user_id' => $user_id,
					'point'   => $eventObj['point'],
					'log'     => $eventObj['name'].','.$action.':'.$eventObj['point'].$goldName,
					'event_id'=> $eventObj['id'],
					'event'   => 'event.'. $eventObj['event']
				);
				$pointObj = new Point();
				$is_success = $pointObj->update($pointConfig);
			}else{
				//扣除余额并且记录日志
				$logObj = new AccountLog();
				$config = array(
					'user_id'  => $user_id,
					'event'    => $eventObj['point']>0?'achieve':'fine',
					'num'      => $eventObj['point'],
					'order_no' => $eventObj['id'],
					'note' => $eventObj['name'].','.$action.':'.$eventObj['point'].$wawabiName,
				);
				$is_success = $logObj->write($config);
			}
		
			if($is_success)
			{
				return $this->writeLog($user_id,$eventObj);
			}
		}
	}

	/**
	 * @brief 日志记录
	 * @param array $config => array('user_id' => 用户ID , 'point' => 奖励增减(正，负区分) , 'log' => 日志记录内容)
	 */
	private function writeLog($user_id,$eventObj)
	{
		//修改pointLog表
		$action = $eventObj['point']>0?'增加':'减少';
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();

		$poinLogObj    = new IModel('event_log');
		if($eventObj['point_type']==0){
			$goldName = isset($site_config['goldName']) ?  $site_config['goldName']:'积分';
			$note = $eventObj['name'].','.$action.':'.$eventObj['point'].$goldName;
		}else{
			$wawabiName = isset($site_config['wawabiName']) ?  $site_config['wawabiName']:'元';
			$note = $eventObj['name'].','.$action.':'.$eventObj['point'].$wawabiName;
		}
		$pointLogArray = array(
			'user_id' => $user_id,
			'event_id' => $eventObj['id'],
			'log_time'=> ITime::getDateTime(),
			'point'   => $eventObj['point'],
			'note'   => $note,
		);
		$poinLogObj->setData($pointLogArray);
		$poinLogObj->add();
		$pointLogArray['name']=$eventObj['name'];
		$pointLogArray['event']=$eventObj['event'];
		$pointLogArray['mark']=$eventObj['mark'];
		return $pointLogArray;
	}

}