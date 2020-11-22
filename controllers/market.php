<?php
/**
 * @brief 营销模块
 * @class Market
 * @note  后台
 */
class Market extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
	public $layout = 'admin';

	function init()
	{

	}

	//后台操作记录
	function operation_list()
	{
		$page  = IReq::get('page') ? intval(IReq::get('page')) : 1;
		$where = Util::search(IReq::get('search'));

		$operationObj = new IQuery('log_operation');
		$operationObj->where = $where;
		$operationObj->order = 'id desc';
		$operationObj->page  = $page;

		$this->operationObj  = $operationObj;
		$this->operationList = $operationObj->find();

		$this->redirect('operation_list');
	}

	//清理后台管理员操作日志
	function clear_log()
	{
		$type  = IReq::get('type');
		$month = intval(IReq::get('month'));
		if(!$month)
		{
			die('请填写要清理日志的月份');
		}

		switch($type)
		{
			case "account":
			{
				$logObj = new IModel('account_log');
				$logObj->del("event = 1 and TIMESTAMPDIFF(MONTH,time,NOW()) >= '{$month}'");
				$this->redirect('account_list');
				break;
			}
			case "operation":
			{
				$logObj = new IModel('log_operation');
				$logObj->del("TIMESTAMPDIFF(MONTH,datetime,NOW()) >= '{$month}'");
				$this->redirect('operation_list');
				break;
			}
			default:
				die('缺少类别参数');
		}
	}


	//导出用户统计数据
	public function user_report()
	{
		$start = IFilter::act(IReq::get('start'));
		$end   = IFilter::act(IReq::get('end'));

		$memberQuery = new IQuery('member as m');
		$memberQuery->join   = "left join user as u on m.user_id=u.id";
		$memberQuery->fields = "u.username,m.time,m.email,m.mobile";
		$memberQuery->where  = "m.time between '".$start."' and '".$end." 23:59:59'";
		$memberList          = $memberQuery->find();

		$reportObj = new report('user');
		$reportObj->setTitle(array("日期","用户名","邮箱","手机号"));
		foreach($memberList as $k => $val)
		{
			$insertData = array($val['time'],$val['username'],$val['email'],$val['mobile']);
			$reportObj->setData($insertData);
		}
		$reportObj->toDownload();
	}

	public function userrank_report(){
		$where       = Util::search(IReq::get('search'));

		$where = 'm.status = 1 and uc.is_del=0 and '.$where;

		$gw_id = IFilter::act(IReq::get('gw_id'));
		if($gw_id){
			$where = $where . ' and FIND_IN_SET('.$gw_id.',uc.gw_id)';
		}

		$hy_id = IFilter::act(IReq::get('hy_id'));
		if($hy_id){
			$where = $where . ' and FIND_IN_SET('.$hy_id.',c.hy_id)';
		}
		if($this->admin['level']==0){
			$where = $where . ' and c.area='.$this->admin['area'];
			$this->companyRow = array(
				"province"=>$this->admin['province'],
				"city"=>$this->admin['city'],
				"area"=>$this->admin['area'],
			);
		}else{
			$area = IFilter::act(IReq::get('area'));
			if($area){
				$where = $where . ' and c.area='.$area;
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>$area,
				);
			}else{
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>'0',
				);
			}
		}

		$query = new IQuery("member as m");
		$query->where  = $where;
		$query->order = 'm.point desc';
		$query->join = 'left join user_company as uc on uc.user_id=m.user_id left join company as c on c.id=uc.com_id';
		$query->fields = 'm.mobile,m.user_id,m.true_name,m.point,uc.point as w_point,uc.create_time,c.name as c_name,uc.com_id,c.point as c_point,uc.id as work_id';
		//$query->group = 'point';
		$this->query    = $query;
		$queryList    = $query->find();
		$reportObj = new report('用户排行');
		$reportObj->setTitle(array("查询名次","总名次","姓名","手机","积分","岗位","入职日期","企业名","行业"));
		if($queryList){

			$first = current($queryList);
			$first_point = $first['point'];
			$memberDB = new IModel('member as m,user_company as uc,company as c');
	
			$companyCount = $memberDB->getObj('m.point>'.$first_point.' and m.status = 1 and m.user_id=uc.user_id and uc.com_id=c.id and uc.is_del=0 and '.$where,'count( DISTINCT m.point) as count');
			$cure_rank = $companyCount['count']+1;
	
			foreach($queryList as $key => &$val){
				if($first_point !=  $val['point'] ){
					$first_point = $val['point'];
					$cure_rank ++;
				}
				$val['rank'] = $cure_rank;
	
				$companyCount = $memberDB->getObj('m.point>'.$first_point.' and m.status = 1 and m.user_id=uc.user_id and uc.com_id=c.id and uc.is_del=0','count( DISTINCT m.point) as count');
				$all_rank = $companyCount['count']+1;
				$val['all_rank'] = $all_rank;

				$gwquery = Api::run('getGWCategoryExtendNameByCategoryid',array('id'=>$val["work_id"]));
				$gwcat = '';
				foreach($gwquery as $catName){
					$gwcat .= '['.$catName['name'].']';
				}

				$hyquery = Api::run('getHYCategoryExtendNameByCategoryid',array('id'=>$val["com_id"]));
				$hycat = '';
				foreach($hyquery as $catName){
					$hycat .= '['.$catName['name'].']';
				}
				$insertData = array($val['rank'],$val['all_rank'],$val['true_name'],
				$val['mobile'],$val['point'],$gwcat,$val['create_time'],$val['c_name'],$hycat);
				$reportObj->setData($insertData);
			}
		}
		$reportObj->export(array(10,10,20,15,5,30,20,40,40));
	}

	//导出用户统计数据
	public function companyrank_report()
	{
		$where       = Util::search(IReq::get('search'));
		$where = 'is_del = 0 and '.$where;

		$hy_id = IFilter::act(IReq::get('hy_id'));
		if($hy_id){
			$where = $where . ' and FIND_IN_SET('.$hy_id.',hy_id)';
		}

		if($this->admin['level']==0){
			$where = $where . ' and area='.$this->admin['area'];
			$this->companyRow = array(
				"province"=>$this->admin['province'],
				"city"=>$this->admin['city'],
				"area"=>$this->admin['area'],
			);
		}else{
			$area = IFilter::act(IReq::get('area'));
			if($area){
				$where = $where . ' and area='.$area;
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>$area,
				);
			}else{
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>'0',
				);
			}
		}
		$query = new IQuery("company");
		$query->where  = $where;
		$query->order = 'point desc';
		//$query->group = 'point';
		$this->query    = $query;
		$queryList    = $query->find();

		$reportObj = new report('企业排行');
		$reportObj->setTitle(array("查询名次","总名次","企业名","属地","积分","执照号","登记日期","行业"));
		if($queryList){

			$first = current($queryList);
			$first_point = $first['point'];
			$companyDB = new IModel('company');
	
			$companyCount = $companyDB->getObj('point>'.$first_point.' and is_del=0 and '.$where,'count( DISTINCT point) as count');
			$cure_rank = $companyCount['count']+1;
	

			foreach($queryList as $key => &$val){
				if($first_point !=  $val['point'] ){
					$first_point = $val['point'];
					$cure_rank ++;
				}
				$val['rank'] = $cure_rank;
	
				$companyCount = $companyDB->getObj('point>'.$first_point.' and is_del=0','count( DISTINCT point) as count');
				$all_rank = $companyCount['count']+1;
				$val['all_rank'] = $all_rank;

				$area = implode(' ',area::name($val['city'],$val['area']));
				$hyquery = Api::run('getHYCategoryExtendNameByCategoryid',array('id'=>$val["id"]));
				$cat = '';
				foreach($hyquery as $catName){
					$cat .= '['.$catName['name'].']';
				}
				$insertData = array($val['rank'],$val['all_rank'],$val['name'],$area,$val['point'],$val['com_code'],$val['create_time'],$cat);
				$reportObj->setData($insertData);
			}
		}
		$reportObj->export(array(10,10,40,30,5,40,40,40));
	}

	


	//账户余额记录
	function point_list()
	{
		$page       = intval(IReq::get('page')) ? IReq::get('page') : 1;
		$event      = IFilter::act(IReq::get('event'));
		$startDate  = IFilter::act(IReq::get('startDate'));
		$endDate    = IFilter::act(IReq::get('endDate'));
		$true_name    = IFilter::act(IReq::get('true_name'));
		$user_id    = IFilter::act(IReq::get('user_id'));

		$where      = "1";
		if($startDate)
		{
			$where .= " and a.datetime >= '{$startDate}' ";
		}

		if($endDate)
		{
			$temp   = $endDate.' 23:59:59';
			$where .= " and a.datetime <= '{$temp}' ";
		}

		if($event)
		{
			$where .= " and a.event = '{$event}' ";
		}

		if($true_name)
		{
			$where .= " and u.true_name like '%{$true_name}%' ";
		}

		if($user_id)
		{
			$where .= " and a.user_id = {$user_id} ";
		}

		$pointObj = new IQuery('point_log as a');
		$pointObj->join = 'left join member as u on a.user_id=u.user_id left join company as c on a.com_id=c.id';
		$pointObj->fields = "a.*,u.true_name,u.logo,u.mobile,c.name as c_name,u.nickname";
		$pointObj->where = $where;
		$pointObj->order = 'a.id desc';
		$pointObj->page  = $page;

		$this->pointObj  = $pointObj;
		$this->event       = $event;
		$this->startDate   = $startDate;
		$this->endDate     = $endDate;
		$this->true_name     = $true_name;
		$this->pointList = $pointObj->find();

		$this->redirect('point_list');
	}


	public function companyrank(){
		$where       = Util::search(IReq::get('search'));
		$where = 'is_del = 0 and '.$where;

		$hy_id = IFilter::act(IReq::get('hy_id'));
		if($hy_id){
			$where = $where . ' and FIND_IN_SET('.$hy_id.',hy_id)';
		}

		if($this->admin['level']==0){
			$where = $where . ' and area='.$this->admin['area'];
			$this->companyRow = array(
				"province"=>$this->admin['province'],
				"city"=>$this->admin['city'],
				"area"=>$this->admin['area'],
			);
		}else{
			$area = IFilter::act(IReq::get('area'));
			if($area){
				$where = $where . ' and area='.$area;
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>$area,
				);
			}else{
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>'0',
				);
			}
		}
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("company");
		$query->where  = $where;
		$query->order = 'point desc';
		$query->page   = $page;
		//$query->group = 'point';
		$this->query    = $query;
		$queryList    = $query->find();

		if($queryList){

			$first = current($queryList);
			$first_point = $first['point'];
			$companyDB = new IModel('company');
	
			$companyCount = $companyDB->getObj('point>'.$first_point.' and is_del=0 and '.$where,'count( DISTINCT point) as count');
			$cure_rank = $companyCount['count']+1;
	
	
			foreach($queryList as $key => &$val){
				if($first_point !=  $val['point'] ){
					$first_point = $val['point'];
					$cure_rank ++;
				}
				$val['rank'] = $cure_rank;
	
				$companyCount = $companyDB->getObj('point>'.$first_point.' and is_del=0','count( DISTINCT point) as count');
				$all_rank = $companyCount['count']+1;
				$val['all_rank'] = $all_rank;
			}
		}

		$this->search_category    = $hy_id;
		$this->queryList = $queryList;
		$this->redirect('companyrank');

	}

	//根据管理员级别过滤
	public function userrank(){
		$where       = Util::search(IReq::get('search'));

		$where = 'm.status = 1 and uc.is_del=0 and '.$where;

		$gw_id = IFilter::act(IReq::get('gw_id'));
		if($gw_id){
			$where = $where . ' and FIND_IN_SET('.$gw_id.',uc.gw_id)';
		}

		$hy_id = IFilter::act(IReq::get('hy_id'));
		if($hy_id){
			$where = $where . ' and FIND_IN_SET('.$hy_id.',c.hy_id)';
		}
		if($this->admin['level']==0){
			$where = $where . ' and c.area='.$this->admin['area'];
			$this->companyRow = array(
				"province"=>$this->admin['province'],
				"city"=>$this->admin['city'],
				"area"=>$this->admin['area'],
			);
		}else{
			$area = IFilter::act(IReq::get('area'));
			if($area){
				$where = $where . ' and c.area='.$area;
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>$area,
				);
			}else{
				$this->companyRow = array(
					"province"=>$this->admin['province'],
					"city"=>$this->admin['city'],
					"area"=>'0',
				);
			}
		}

		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("member as m");
		$query->where  = $where;
		$query->order = 'm.point desc';
		$query->join = 'left join user_company as uc on uc.user_id=m.user_id left join company as c on c.id=uc.com_id';
		$query->fields = 'm.mobile,m.user_id,m.true_name,m.point,uc.point as w_point,uc.create_time,c.name as c_name,uc.com_id,c.point as c_point,uc.id as work_id';
		$query->page   = $page;
		//$query->group = 'point';
		$this->query    = $query;
		$queryList    = $query->find();

		if($queryList){

			$first = current($queryList);
			$first_point = $first['point'];
			$memberDB = new IModel('member as m,user_company as uc,company as c');
	
			$companyCount = $memberDB->getObj('m.point>'.$first_point.' and m.status = 1 and m.user_id=uc.user_id and uc.com_id=c.id and uc.is_del=0 and '.$where,'count( DISTINCT m.point) as count');
			$cure_rank = $companyCount['count']+1;
	
			foreach($queryList as $key => &$val){
				if($first_point !=  $val['point'] ){
					$first_point = $val['point'];
					$cure_rank ++;
				}
				$val['rank'] = $cure_rank;
	
				$companyCount = $memberDB->getObj('m.point>'.$first_point.' and m.status = 1 and m.user_id=uc.user_id and uc.com_id=c.id and uc.is_del=0','count( DISTINCT m.point) as count');
				$all_rank = $companyCount['count']+1;
				$val['all_rank'] = $all_rank;
			}
		}

		$this->member_category    = $gw_id;
		$this->search_category    = $hy_id;

		$this->queryList = $queryList;
		$this->redirect('userrank');

	}
}