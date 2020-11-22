<?php
/**
 * @brief 用户接口
 * @class Ucenter
 * 接口 访问路径
 */
class Ucenter extends IApiController implements userAuthorization
{
	public function init()
	{
		$this->cacheAble = true;
		$this->cacheActions = array(
		//	'pointBand'=>60,
		);
		$this->versionActions = array(
		//	'pointBand'=>array('1.0.12','1.0.9'),
		);
		//$this->disableAction = array("pointBand");
    }

    public function pointTaskList(){
        $user_id   = $this->user['user_id'];
        $memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('user_id = '.$user_id,'group_id,point');
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
        $eventQuery->where = "curdate()>=start_date and curdate()<=end_date and status=1".$where;
        $eventQuery->order = 'sort asc';
        $events = $eventQuery->find();

        $eventLog = new IModel('event_log');
        $where = 'user_id='.$user_id;
        $normal_task = array();
        foreach($events as $event){
            $event_type = $event['event_type'];
			$event_id = $event['id'];
            if($event['task_num']>0 || $event['event_type']==1){
                //查询已获得积分数量
                if($event_type==1){
                    //单次任务
                    $eventLogs = $eventLog->getObj($where . ' and event_id ='. $event_id,'count(id) as log_count');
                    $event['log_count'] = $eventLogs['log_count'];
                    $event['task_num'] = '1';
                }elseif($event_type==2) {
                    # 多次任务...
                    $eventLogs = $eventLog->getObj($where . ' and event_id ='. $event_id,'count(id) as log_count');
                    $event['log_count'] = $eventLogs['log_count'];
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
                    $eventLogs = $eventLog->getObj($where  . ' and event_id =' . $event_id . $priedwhere,'count(id) as log_count');
                    $event['log_count'] = $eventLogs['log_count'];
                }
                $normal_task[] = $event;
            }
        }

        //获取考试
        $exam_task = array();

        $paperTypeObj       = new IModel('exam_paper_type');
        $paperTemps = $paperTypeObj->query('visibility=1 and (user_id=0 || user_id='.$user_id.')');
        
        $pointLog = new IModel('point_log');
        $where = 'user_id='.$user_id;
		foreach($paperTemps as $paperTemp){
            $priedtype = $paperTemp['score_priod'];
            $_where = $where . ' and event="exam_'.$paperTemp['id'].'"';
			if($priedtype==1){
				$_where .= " and DATE_FORMAT(datetime,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d')";
			}elseif ($priedtype==2) {
				$_where .= " and DATE_FORMAT(datetime,'%x%v')=DATE_FORMAT(NOW(),'%x%v')";
			}elseif ($priedtype==3) {
				$_where .= " and DATE_FORMAT(datetime,'%Y%m')=DATE_FORMAT(NOW(),'%Y%m')";
            }
            $pointLogs = $pointLog->getObj($_where,'sum(value) as got_score');
            $paperTemp['got_score'] = $pointLogs['got_score']?$pointLogs['got_score']:0;
            $exam_task[] = $paperTemp;
        }

        $pointLogs = $pointLog->getObj($where." and DATE_FORMAT(datetime,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d')",'sum(value) as got_score');
        $pointSummer = array(
            'today'=>$pointLogs['got_score']?$pointLogs['got_score']:0,
            'total'=>$memberRow['point']?$memberRow['point']:0,
        );
        $this->setRenderData(array('pointSummer'=>$pointSummer,'normal_task' => $normal_task,'exam_task'=>$exam_task));
    }

    public function userInfo(){
        $user_id   = $this->user['user_id'];


        $orderObj       = new IModel('order');
        $order = $orderObj->getObj('user_id='.$user_id.' and order_type=3 and curdate()>=start_date and curdate()<=end_date ');
        if(!$order){
            $order = null;
        }

        $user_companyDB        = new IModel('user_company');
		$user_companyDB->setData(array(
			"is_check"=>1
		));
		$user_companyDB->update("user_id=".$user_id." and is_del=0 and is_check=0 and tzzy_code<>'' and DATE_FORMAT(tzzy_f_time,'%Y%m')<=DATE_FORMAT(NOW(),'%Y%m')");

        $memberDB = new IModel('member');
        $member = $memberDB->getObj('user_id='.$user_id);
        $userBand = array(
            'point'=>0,
            'allcount'=>0,
            'hycount'=>0,
            'comcount'=>0
        );
        if($member){
            $memberPoint = $member['point']?$member['point']:0;
            $memberCount = $memberDB->getObj('point>'.$memberPoint,'count(DISTINCT point) as count');
            $userBand['point']= $member['point'];
            $userBand['allcount']= $memberCount['count']+1;


            $user_companyDB = new IModel('user_company');
            $work = $user_companyDB->getObj('user_id='.$user_id.' and is_del=0');
            if($work){

                if(!$work['hasAct'] && $work['is_accept']==1){
                    $user_companyDB->setData(array('hasAct'=>1));
                    $user_companyDB->update('id='.$work['id']);
                }
                
                $categoryExtend = new IQuery('gw_category');
                $categoryExtend->where = 'id in ('.$work['gw_id'].')';
                $categoryExtend->fields = 'name';
                $cateData = $categoryExtend->find();
                $gwName = array();
                if($cateData)
                {
                    foreach($cateData as $item)
                    {
                        $gwName[] = $item['name'];
                    }
                }
                $member['gwName']=$gwName;
    
                $companyDB = new IModel('company');
                $company = $companyDB->getObj('id='.$work['com_id']);
                $company['is_accept'] = $work['is_accept'];
                //行业排名
                $memberHYDB = new IModel('member as m,user_company as uc,company as c');
                $memberHYCount = $memberHYDB->getObj('m.user_id=uc.user_id and uc.is_del=0 and uc.com_id=c.id and c.hy_id="'.$company['hy_id'].'" and m.point>'.$memberPoint,'count(DISTINCT m.point) as count');
                $userBand['hycount']= $memberHYCount['count']+1;
    
                //用户企业排名
                $memberComDB = new IModel('member as m,user_company as uc');
                $memberComCount = $memberComDB->getObj('m.user_id=uc.user_id and uc.is_del=0 and uc.com_id='.$company['id'].' and m.point>'.$memberPoint,'count(DISTINCT m.point) as count');
                $userBand['comcount']= $memberComCount['count']+1;

                //企业分类
                $categoryExtend = new IQuery('hy_category');
                $categoryExtend->where = 'id in ('.$company['hy_id'].')';
                $categoryExtend->fields = 'name';
                $cateData = $categoryExtend->find();
                $hyName = array();
                if($cateData)
                {
                    foreach($cateData as $item)
                    {
                        $hyName[] = $item['name'];
                    }
                }
                $member['hyName']=$hyName;
            }

            $event = plugin::trigger('event',array('event'=>'login','user_id'=>$user_id));

            if(isset($company)){
                $this->setRenderData(array('order'=>$order,'member' => $member,'userBand'=>$userBand,'company'=>$company,'work'=>$work));
            }else{
                $this->setRenderData(array('order'=>$order,'member' => $member,'userBand'=>$userBand));
            }
        }
    }

    public function pointList(){
		$user_id   = $this->user['user_id'];
        $page       = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query      = new IQuery('point_log');
        $query->where  = "user_id = ".$user_id;
        $query->page   = $page;
        $query->order  = "id desc";
		$pointList = $query->find();
		$paging = $query->paging;
        $this->setRenderData(array('pointList' => $pointList,'paging'=>$paging));
    }

    public function pointSumByDay(){
		$user_id   = $this->user['user_id'];
        $enddate = IFilter::act(IReq::get('enddate'),'date');
        if(!$enddate){
            $enddate = ITime::getDateTime('Y-m-d');
        }

        $startdate = ITime::getDateTime('Y-m-d',strtotime('-10 day',ITime::getTime($enddate.' 23:59:59')));


        $eventQuery = new IQuery('event');
        $eventQuery->where = "status=1";
        $eventQuery->order = 'sort asc';
        $events = $eventQuery->find();
        $event_conf = array();
        foreach($events as $event){
            $event_conf['event.'.$event['event']] = $event;
        }

        $paperTypeObj       = new IModel('exam_paper_type');
        $paperTemps = $paperTypeObj->query();
        $paper_conf = array();
        foreach($paperTemps as $paperTemp){
            $paper_conf['exam_'.$paperTemp['id']] = $paperTemp;
        }

        $query      = new IQuery('point_log');
        $query->where  = "user_id = ".$user_id." and  DATE_FORMAT(datetime,'%Y-%m-%d') > '{$startdate}' and DATE_FORMAT(datetime,'%Y-%m-%d') <= '{$enddate}' and value>0";
        $query->fields = 'event,DATE_FORMAT(datetime,"%Y-%m-%d") as xValue,sum(value) as point';
        $query->group   = "DATE_FORMAT(datetime,'%Y-%m-%d'),event";
        $query->order  = "id desc";
        $pointList = $query->find();
        $dayPointList = array();
        foreach($pointList as $point){
            $date = $point['xValue'];
            if(!isset($dayPointList[$date])){
                $dayPointList[$date] = array();
            }
            if(strpos($point['event'],'event')!==false){
                $title = $event_conf[$point['event']]['name'];
            }elseif(strpos($point['event'],'exam')!==false){
                $title = $paper_conf[$point['event']]['title'];
            }else{
                $title = '其他';
            }
            $dayPointList[$date][] = array(
                'point'=>$point['point'],
                'title'=>$title,
            );
        }
        $this->setRenderData(array('dayPointList' => $dayPointList,'startdate'=>$startdate,'enddate'=>$enddate));
    }


    public function pointListByDay(){
		$user_id   = $this->user['user_id'];
        $enddate = IFilter::act(IReq::get('enddate'),'date');
        if(!$enddate){
            $enddate = ITime::getDateTime('Y-m-d');
        }

        $startdate = ITime::getDateTime('Y-m-d',strtotime('-1 month',ITime::getTime($enddate.' 23:59:59')));


        $eventQuery = new IQuery('event');
        $eventQuery->where = "status=1";
        $eventQuery->order = 'sort asc';
        $events = $eventQuery->find();
        $event_conf = array();
        foreach($events as $event){
            $event_conf['event.'.$event['event']] = $event;
        }

        $paperTypeObj       = new IModel('exam_paper_type');
        $paperTemps = $paperTypeObj->query();
        $paper_conf = array();
        foreach($paperTemps as $paperTemp){
            $paper_conf['exam_'.$paperTemp['id']] = $paperTemp;
        }

        $query      = new IQuery('point_log');
        $query->where  = "user_id = ".$user_id." and  DATE_FORMAT(datetime,'%Y-%m-%d') > '{$startdate}' and DATE_FORMAT(datetime,'%Y-%m-%d') <= '{$enddate}' and value>0";
        $query->fields = 'datetime,event,DATE_FORMAT(datetime,"%Y-%m-%d") as xValue,value as point';
       // $query->group   = "DATE_FORMAT(datetime,'%Y-%m-%d')";
        $query->order  = "id desc";
        $pointList = $query->find();
        $dayPointList = array();
        foreach($pointList as $point){
            $date = $point['xValue'];
            if(!isset($dayPointList[$date])){
                $dayPointList[$date] = array();
            }
            if(strpos($point['event'],'event')!==false){
                $title = $event_conf[$point['event']]['name'];
            }elseif(strpos($point['event'],'exam')!==false){
                $title = $paper_conf[$point['event']]['title'];
            }else{
                $title = '其他';
            }
            $dayPointList[$date][] = array(
                'point'=>$point['point'],
                'event'=>$point['event'],
                'datetime'=>$point['datetime'],
                'title'=>$title,
            );
        }
        $this->setRenderData(array('dayPointList' => $dayPointList,'startdate'=>$startdate,'enddate'=>$enddate));
    }


    public function pointBand(){
        $user_id   = $this->user['user_id'];
        $memberDB = new IModel('member');
        $member = $memberDB->getObj('user_id='.$user_id);
        $userBand = array(
            'point'=>0,
            'allcount'=>0,
            'hycount'=>0,
            'comcount'=>0
        );
        $companyBand = array(
            'point'=>0,
            'allcount'=>0,
            'hycount'=>0
        );
        if($member){
            $memberPoint = $member['point']?$member['point']:0;
            $memberCount = $memberDB->getObj('point>'.$memberPoint,'count(DISTINCT point) as count');
            $userBand['point']= $memberPoint;
            $userBand['allcount']= $memberCount['count']+1;
        }
        $companyTop = array();
        $user_companyDB = new IModel('user_company');
        $work = $user_companyDB->getObj('user_id='.$user_id.' and is_del=0');
        if($work){
            $memberDB = new IModel('member as m,user_company as c');
            $companyTop = $memberDB->query('m.user_id=c.user_id and c.is_del=0 and c.com_id='.$work['com_id'],'m.true_name,m.logo,m.point','m.point desc',10);

            $companyDB = new IModel('company');
            $company = $companyDB->getObj('id='.$work['com_id'],'hy_id,point,id');
            $companyPoint = $company['point']?$company['point']:0;

            $companyCount = $companyDB->getObj('point>'.$companyPoint,'count(DISTINCT point) as count');
            $companyBand['point']= $companyPoint;
            $companyBand['allcount']= $companyCount['count']+1;

            //行业排名
            $memberHYDB = new IModel('member as m,user_company as uc,company as c');
            $memberHYCount = $memberHYDB->getObj('m.user_id=uc.user_id and uc.is_del=0 and uc.com_id=c.id and c.hy_id="'.$company['hy_id'].'" and m.point>'.$memberPoint,'count(DISTINCT m.point) as count');
            $userBand['hycount']= $memberHYCount['count']+1;

            //用户企业排名
            $memberComDB = new IModel('member as m,user_company as uc');
            $memberComCount = $memberComDB->getObj('m.user_id=uc.user_id and uc.is_del=0 and uc.com_id='.$company['id'].' and m.point>'.$memberPoint,'count(DISTINCT m.point) as count');
            $userBand['comcount']= $memberComCount['count']+1;


            //行业排名
            $companyHYCount = $companyDB->getObj('point>'.$companyPoint.' and hy_id="'.$company['hy_id'].'"','count(DISTINCT point) as count');
            $companyBand['hycount']= $companyHYCount['count']+1;

        }
        $this->setRenderData(array('companyTop' => $companyTop,'userBand'=>$userBand,'companyBand'=>$companyBand));
    }

    public function suggestion(){
		$user_id   = IFilter::act($this->user['user_id'],'int');
		$c_type = IFilter::act( IReq::get('c_type') ,'int');
		$content = IFilter::act( IReq::get('content') ,'string');
		$tb = new IModel("suggestion");
		$data = array(
			'user_id'=>$user_id,
			'c_type'=>$c_type,
			'content'=>$content,
			'time'=>ITime::getDateTime(),
		);
		$tb->setData($data);
		$tb->add();
    }

    public function mySuggest()
    {
		$user_id   = $this->user['user_id'];
        $page   = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query  = new IQuery('suggestion');
        $query->where="user_id = ".$user_id;
        $query->page  = $page;
        $query->order = 'id desc';
		$mySuggest = $query->find();
		$paging = $query->paging;
        $this->setRenderData(array('mySuggest' => $mySuggest,'paging'=>$paging));
    }

    		/**
	 * @brief 添加中图片上传的方法
	 */
	public function changeLogo()
	{
        $user_id   = $this->user['user_id'];

        //附件上传$_FILE
        if($_FILES)
        {
            $uploadDir = IWeb::$app->config['upload'].'/member';
            $uploadObj = new PhotoUpload($uploadDir);
            $uploadObj->setIterance(false);
            $photoInfo = $uploadObj->run();

            //logo图片处理
            if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
            {
                $userArray = array(
                    'head_ico' => $photoInfo['logo']['img'],
                );
                $userObj = new IModel('user');
                $userObj->setData($userArray);
                $userObj->update('id = '.$user_id);
                
                $memberArray = array(
                    'logo' => $photoInfo['logo']['img'],
                );
                $memberObj = new IModel('member');
                $memberObj->setData($memberArray);
                $memberObj->update('user_id = '.$user_id);
                return $userArray;
            }else{
                IError::show(300000,"更新头像失败");
            }
        }else{
            IError::show(300000,"更新头像失败");
        }
	}
	//退出登录
    function logout()
    {
        $user_id   = $this->user['user_id'];
		Api::clearUserToken($user_id);
		plugin::trigger('clearUser');
    }


	//执行绑定已存在用户
	public function bindUser()
	{
		$oid = IFilter::act(IReq::get('oid'));
		$oauthObj = new OauthCore($oid);
		$user_id   = $this->user['user_id'];
		$reBind = IReq::get('reBind') ? IFilter::act(IReq::get('reBind'),'int') : 1;

		$myOauth = $oauthObj->checkUserOauth($user_id);
		if($myOauth){
			IError::show(500000,"你已绑定微信");
		}
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
				$userRow = $oauthObj->getUserInfo($openid);
				if($userRow){
					// 插入关系表，绑定
					$oauthObj->addOauthUser($user_id,$openid,$unionid);
					if($reBind){
						//更新用户信息
						$area = $userRow['country'].',' . $userRow['province'] . ',' . $userRow['city'];
						$memberArray = array(
							'sex' => $userRow['sex'],
							'area' => trim($area,','),
							'nickname' => str_replace("'","",$userRow['name']),
						);
							// 保存头像，
						if($userRow['headimgurl']){
                            $memberArray['logo']=$userRow['headimgurl'];
                            $userObj = new IModel('user');
                            $userObj->setData(array("head_ico"=>$userRow['headimgurl']));
                            $userObj->update('id='.$user_id);
						}
						$memberObj = new IModel('member');
						$memberObj->setData($memberArray);
                        $memberObj->update('user_id='.$user_id);
					}
				}else{
                    IError::show(500003,"获取微信个人信息失败");
				}
			}else{
                IError::show(500002,"获取微信信息失败");
            }
		}else{
            IError::show(500001,"微信授权失败");
        }
	}
}