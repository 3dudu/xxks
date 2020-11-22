<?php
/**
 * @brief 企业内部管理模块
 * @class Comadmin
 * @author chendeshan
 * @datetime 2014/7/19 15:18:56
 */
class Comadmin extends IController implements comAuthorization
{
	public $layout = 'com';

	private $data = array();
	
	/**
	 * @brief 初始化检查
	 */
	public function init()
	{

    }

	public function index()
	{
		$com_id = $this->comadmin['company']['id'];
		$user_companyDB        = new IModel('user_company');
		$user_companyDB->setData(array(
			"is_check"=>1
		));
		$user_companyDB->update("com_id= ".$com_id. " and is_del=0 and is_check=0 and tzzy_code<>'' and DATE_FORMAT(tzzy_f_time,'%Y%m')<=DATE_FORMAT(NOW(),'%Y%m')");


		$search = IFilter::act(IReq::get('search'),'strict');
		$keywords = IFilter::act(IReq::get('keywords'));
		$where = ' 1 ';
		if($search && $keywords)
		{
			$where .= " and $search like '%{$keywords}%' ";
		}

		$czz = IFilter::act(IReq::get('czz'));
		$aqz = IFilter::act(IReq::get('aqz'));
		if($czz){
			if($czz==1){
				$where = $where . ' and uc.tzzy_code<>"" and curdate()<=uc.tzzy_h_time and uc.is_check=0 ';
			}elseif($czz==2){
				$where = $where . ' and uc.tzzy_code<>"" and curdate()>uc.tzzy_h_time ';
			}elseif($czz==3){
				$where = $where . ' and uc.tzzy_code<>"" and uc.is_check=1';
			}
		}
		if($aqz){
			if($aqz==1){
				$where = $where . ' and uc.aqzg_code<>"" and curdate()<=uc.aqzg_h_time ';
			}elseif($aqz==2){
				$where = $where . ' and uc.aqzg_code<>"" and curdate()>uc.aqzg_h_time ';
			}
		}
		
		$this->data['search'] = $search;
		$this->data['keywords'] = $keywords;
		
		$this->setRenderData($this->data);

        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("user_company as uc");
        $query->join   = ' left join member as m on m.user_id = uc.user_id';
        $query->where  = ' uc.is_del=0 and uc.com_id = '.$com_id.' and '.$where;
        $query->fields = ' m.*,uc.id as uc_id ';
        $query->order  = ' m.user_id desc ';
		$query->page   = $page;
		$this->query   = $query;
		
		$this->redirect('index');

	}


	public function recycling()
	{
		$com_id = $this->comadmin['company']['id'];

		$search = IFilter::act(IReq::get('search'),'strict');
		$keywords = IFilter::act(IReq::get('keywords'));
		$where = ' 1 ';
		if($search && $keywords)
		{
			$where .= " and $search like '%{$keywords}%' ";
		}
		
		$this->data['search'] = $search;
		$this->data['keywords'] = $keywords;
		
		$this->setRenderData($this->data);

        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("user_company as uc");
        $query->join   = ' left join member as m on m.user_id = uc.user_id';
        $query->where  = ' uc.is_del = 1 and uc.com_id = '.$com_id.' and '.$where;
        $query->fields = ' m.*,uc.id as uc_id';
        $query->order  = ' m.user_id desc ';
		$query->page   = $page;
		$this->query   = $query;
		
		$this->redirect('recycling');
		
	}

	public function restore()
	{
		$com_id = $this->comadmin['company']['id'];

		$id = IFilter::act(IReq::get('id'));
		$userCompanyObj = new IModel("user_company");
		$userCompany =  $userCompanyObj->getObj('id = '.$id.' and com_id='.$com_id);
		if($userCompany){
			$memberObj = new IModel("member");
			$memberObj->setData(array("status"=>1));
			$memberObj->update("user_id=".$userCompany["user_id"]);
		}
		$this->recycling();
	}

	public function del()
	{
		$com_id = $this->comadmin['company']['id'];

		$id = IFilter::act(IReq::get('id'));
		$userCompanyObj = new IModel("user_company");
		$userCompany =  $userCompanyObj->getObj('id = '.$id.' and com_id='.$com_id);
		if($userCompany){
			$userCompanyObj->setData(array("is_del"=>1));
			$userCompanyObj->update("id=".$id);
		}
		$this->index();
	}


	public function user_request()
	{
		$com_id = $this->comadmin['company']['id'];
		
		$search = IFilter::act(IReq::get('search'),'strict');
		$keywords = IFilter::act(IReq::get('keywords'));
		$where = ' 1 ';
		if($search && $keywords)
		{
			$where .= " and $search like '%{$keywords}%' ";
		}
		
		$this->data['search'] = $search;
		$this->data['keywords'] = $keywords;
		
		$this->setRenderData($this->data);

        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("user_company as uc");
        $query->join   = ' left join member as m on uc.user_id=m.user_id ';
        $query->where  = ' uc.is_accept != 1  and uc.is_del=0 and uc.com_id = '.$com_id.' and '.$where;
        $query->fields = ' m.*,uc.id as uc_id,uc.create_time';
        $query->order  = ' m.user_id desc ';
		$query->page   = $page;
		$this->query   = $query;
		
		$this->redirect('user_request');
	}


	public function handle_request(){
		$com_id = $this->comadmin['company']['id'];
		$result = IFilter::act(IReq::get('result'));
		$id = IFilter::act(IReq::get('id'));
		$userCompanyObj = new IModel("user_company");
		$handleData = array("is_accept"=>$result);
		$userCompanyObj->setData($handleData);
		$where = 'id = '.$id.' and com_id='.$com_id;
		$userCompanyObj->update($where);
		$this->user_request();

	}

	public function user_edit()
	{
		$com_id = $this->comadmin['company']['id'];

		$work_id = IFilter::act(IReq::get('id'),'int');

		//修改页面
		if($work_id)
		{
			$user_companyDB        = new IModel('user_company');
			$companyWorkRow = $user_companyDB->getObj('id = '.$work_id.' and com_id='.$com_id);
			$userData = Api::run('getMemberInfo',$companyWorkRow['user_id']);
			if(!$userData)
			{
				$this->member_list();
				Util::showMessage("没有找到相关用户记录！");
				exit;
			}
			
			//岗位分类
			$categoryExtend = new IQuery('user_category_extend');
			$categoryExtend->where = 'work_id = '.$work_id;
			$categoryExtend->fields = 'category_id';
			$cateData = $categoryExtend->find();
			if($cateData)
			{
				foreach($cateData as $item)
				{
					$companyWorkRow['member_category'][] = $item['category_id'];
				}
			}
			$this->setRenderData(array('companyWork'=>$companyWorkRow,'userData' => $userData));
		}else{
			$userData = array(
				"area"=>',320000,321300,321301,',
			);
			$this->setRenderData(array('userData' => $userData));

		}
		$this->redirect('user_edit');
	}

	public function user_save()
	{
		$com_id = $this->comadmin['company']['id'];

		$user_id = IFilter::act(IReq::get('user_id'),'int');
		$true_name = IFilter::act(IReq::get('true_name'));
		$mobile      = IFilter::act(IReq::get('mobile'));
		$username      = IFilter::act(IReq::get('mobile'));
		$idcode      = IFilter::act(IReq::get('idcode'));
		$sex        = IFilter::act(IReq::get('sex'),'int');
		$work_id        = IFilter::act(IReq::get('work_id'),'int');

		$email       = IFilter::act(IReq::get('email'));
		$telephone  = IFilter::act(IReq::get('telephone'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$status     = IFilter::act(IReq::get('status'),'int');
		$point        = IFilter::act(IReq::get('point'),'int');
		$contact_addr = IFilter::act(IReq::get('contact_addr'));
		$zip        = IFilter::act(IReq::get('zip'));
		$qq         = IFilter::act(IReq::get('qq'));

		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));


		$tzzy_code  = IFilter::act(IReq::get('tzzy_code'));
		$aqzg_code  = IFilter::act(IReq::get('aqzg_code'));
		$is_check        = IFilter::act(IReq::get('is_check'),'int');

		// if(is_array($com_id)){
		// 	$com_id = $com_id[0];
		// }

		if(!$work_id && $password == '')
		{
			$this->setError('请输入密码！');
		}

		if($password != $repassword)
		{
			$this->setError('两次输入的密码不一致！');
		}

		//创建会员操作类
		$userDB   = new IModel("user");
		$memberDB = new IModel("member");
		$user_companyDB        = new IModel('user_company');
		$user = array(
			'username' => $username,
		);
		if($password)
		{
			$user['password'] = md5($password);
		}
		$member = array(
			'email'        => $email,
			'true_name'    => $true_name,
			'telephone'    => $telephone,
			'mobile'       => $mobile,
			'contact_addr' => $contact_addr,
			'qq'           => $qq,
			'sex'          => $sex,
			'zip'          => $zip,
			'point'        => $point,
			'status'       => $status,
			'idcode'       => $idcode,
		);
		$member['area'] = ",320000,321300,321301,";
		if($province && $city && $area)
		{
			$member['area'] = ",{$province},{$city},{$area},";
		}
		
		$companyWork = array(
			'com_id'=>$com_id,
			'is_accept'=>1,
			'aqzg_code'=>$aqzg_code,
			'tzzy_code'=>$tzzy_code,
			'is_check'=>$is_check,
		);

		$companyWork['tzzy_img'] = IFilter::act(IReq::get('_tzzy_img'));
		$companyWork['tzzy_img2'] = IFilter::act(IReq::get('_tzzy_img2'));
		$companyWork['aqzg_img'] = IFilter::act(IReq::get('_aqzg_img'));
		$companyWork['aqzg_img2'] = IFilter::act(IReq::get('_aqzg_img2'));
		$member['logo'] = IFilter::act(IReq::get('_logo'));

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
				$member['logo'] = $photoInfo['logo']['img'];
				$user['head_ico'] = $photoInfo['logo']['img'];
			}

			if($aqzg_code && isset($photoInfo['aqzg_img']['img']) && file_exists($photoInfo['aqzg_img']['img']))
			{
				$companyWork['aqzg_img'] = $photoInfo['aqzg_img']['img'];
			}
			if($tzzy_code && isset($photoInfo['tzzy_img']['img']) && file_exists($photoInfo['tzzy_img']['img']))
			{
				$companyWork['tzzy_img'] = $photoInfo['tzzy_img']['img'];
			}
			if($aqzg_code && isset($photoInfo['aqzg_img2']['img']) && file_exists($photoInfo['aqzg_img2']['img']))
			{
				$companyWork['aqzg_img2'] = $photoInfo['aqzg_img2']['img'];
			}
			if($tzzy_code && isset($photoInfo['tzzy_img2']['img']) && file_exists($photoInfo['tzzy_img2']['img']))
			{
				$companyWork['tzzy_img2'] = $photoInfo['tzzy_img2']['img'];
			}
		}

		if($aqzg_code)
        {
            $companyWork['aqzg_c_time'] = IFilter::act(IReq::get('aqzg_c_time'));
            $companyWork['aqzg_h_time'] = IFilter::act(IReq::get('aqzg_h_time'));
            $companyWork['aqzg_f_time'] = IFilter::act(IReq::get('aqzg_f_time'));
        }
        if($tzzy_code)
        {
            $companyWork['tzzy_c_time'] = IFilter::act(IReq::get('tzzy_c_time'));
            $companyWork['tzzy_h_time'] = IFilter::act(IReq::get('tzzy_h_time'));
            $companyWork['tzzy_f_time'] = IFilter::act(IReq::get('tzzy_f_time'));
        }

		$gw_md5 = '';
		if(isset($_POST['_member_category']) && $_POST['_member_category']){
			sort($_POST['_member_category']);
			$gw_md5 = implode(',',$_POST['_member_category']);
		}
		$companyWork['gw_id'] = $gw_md5;
	
		if($work_id){
			//更新
			if($userDB->getObj("username='".$username."' and id != ".$user_id))
			{
				$this->setError('用户名重复');
			}
			if($email && $memberDB->getObj("email='".$email."' and user_id != ".$user_id))
			{
				$this->setError('邮箱重复');
			}
			if($mobile && $memberDB->getObj("mobile='".$mobile."' and user_id != ".$user_id))
			{
				$this->setError('手机号码重复');
			}
		}else{
			//新增
			$userInfo = $userDB->getObj("username='".$username."'");
			if($userInfo){
				$user_id = $userInfo['id'];
				if($user_companyDB->getObj("user_id=".$userInfo['id'].' and is_del=0')){
					$this->setError('用户已入职其他公司');
				}
			}
		}

		//操作失败表单回填
		if($errorMsg = $this->getError())
		{
			$companyDB        = new IModel('company');
			$companyRow = $companyDB->getObj('id='.$com_id);
			$companyWork['name'] = $companyRow['name'];

			$companyWork['member_category'] = $_POST['_member_category'];
			if($work_id){
				$companyWork['id'] = $work_id;
			}
			if($user_id){
				$companyWork['user_id'] = $user_id;
			}
			$this->setRenderData(array('userData' => $member,'companyWork'=>$companyWork));
			$this->redirect('company_work_edit',false);
			Util::showMessage($errorMsg);
			exit;
		}

		//修改页面
		if($user_id)
		{
			if($work_id){
				//修改账号信息
				$userDB->setData($user);
				$userDB->update('id = '.$user_id);
				//修改用户信息
				$memberDB->setData($member);
				$memberDB->update("user_id = ".$user_id);
			}
		}else{
			$userDB->setData($user);
			$user_id = $userDB->add();
			$member['user_id'] = $user_id;
			$memberDB->setData($member);
			$memberDB->add();
		}
		//处理企业关系
		if($work_id){
			// $work_user = $user_companyDB->getObj("id=".$work_id);
			// if($work_user['com_id']==$com_id){
			// 	//没有换公司
			// 	$user_companyDB->setData($companyWork);
			// 	$user_companyDB->update('id='.$work_user['id']);
			// 	$work_id = $work_user['id'];
			// }else{
			// 	if($work_user['is_del']==0){
			// 		$user_companyDB->setData(array("is_del"=>1,'leve_time'=> ITime::getDateTime()));
			// 		$user_companyDB->update('id='.$work_user['id']);
			// 	}
			// 	$companyWork['create_time']=ITime::getDateTime();
			// 	$companyWork['user_id']=$work_id;
			// 	$user_companyDB->setData($companyWork);
			// 	$work_id = $user_companyDB->add();
			// }
		}else{
			//新增
			$companyWork['create_time']=ITime::getDateTime();
			$companyWork['user_id']=$user_id;
			$user_companyDB->setData($companyWork);
			$work_id = $user_companyDB->add();
		}
		//处理关系
		$categoryDB = new IModel('user_category_extend');
		$categoryDB->del('work_id = '.$work_id);
		if(isset($_POST['_member_category']) && $_POST['_member_category'])
		{
			foreach($_POST['_member_category'] as $item)
			{
				$item = IFilter::act($item,'int');
				$categoryDB->setData(array('work_id' => $work_id,'category_id' => $item));
				$categoryDB->add();
			}
		}
		$this->redirect('index');
	}


	//根据管理员级别过滤
	public function userrank(){
		$where       = Util::search(IReq::get('search'));
		$com_id = $this->comadmin['company']['id'];
		$where = 'c.id='.$com_id.' and m.status = 1 and uc.is_del=0 and '.$where;

		$gw_id = IFilter::act(IReq::get('gw_id'));
		if($gw_id){
			$where = $where . ' and FIND_IN_SET('.$gw_id.',uc.gw_id)';
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

		$this->queryList = $queryList;
		$this->redirect('userrank');

	}


	public function admin_repwd_act(){
		$admin_id = $this->comadmin['id'];

		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));

		if($password == '')
		{
			$errorMsg = '请输入密码！';
		}

		if($password != $repassword)
		{
			$errorMsg = '两次输入的密码不一致！';
		}
		//操作失败表单回填
		if(isset($errorMsg))
		{
			$this->redirect('changepassword',false);
			Util::showMessage($errorMsg);
		}
		//创建商家操作类
		$companyAdminDB = new IModel("company_admin");
		$companyAdminRow = array(
			"password"=>md5($password),
		);
	
		$companyAdminDB->setData($companyAdminRow);
		$companyAdminDB->update("id=".$admin_id);
		ISafe::set('com_admin_pwd',$companyAdminRow['password']);

		$this->redirect('index');
	}


	public function study_log(){
		$com_id = $this->comadmin['company']['id'];

		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("article_log as l");
		$join = 'left join member as m on m.user_id=l.user_id left join article as a on a.id=l.aid left join article_part as p on p.id=l.apid';
			
		$where = $where . ' and c.id='.$com_id;
		$join = $join . ' left join user_company as uc on uc.user_id = l.user_id left join company as c on c.id=uc.com_id ';
		$query->join = $join;
		$query->fields = 'DISTINCT a.title,p.title as p_title,m.true_name, l.*,c.name as c_name';
		$query->where  = '1 and '.$where;
		$query->order = 'l.last_read_time desc,l.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('study_log');
	}


	public function question_log(){
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		
		$join = 'left join exam_paper as p on p.id=r.paperid left join member as m on m.user_id=r.user_id left join exam_question as q on q.id=r.questionid';
		
		$com_id = $this->comadmin['company']['id'];
		$join = $join . ' left join company as c on c.id=r.com_id';
		$where = $where . ' and c.id='.$com_id;
		
		$query = new IQuery("exam_paper_member_data as r");
		$query->join = $join;
		$query->fields = 'r.*,m.true_name,m.mobile,p.title,q.question';
		$query->where  = '1 and '.$where;
		$query->order = 'r.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('question_log');
	}

	//获取题目列表
	public function paper_log()
	{
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("exam_paper_member_record as r");
		
		$com_id = $this->comadmin['company']['id'];
		$where = $where . ' and c.id='.$com_id;

		$query->join = 'left join exam_paper as p on p.id=r.paperid left join member as m on m.user_id=r.user_id left join company as c on c.id=r.com_id';
		$query->fields = 'r.*,m.true_name,m.mobile,c.name,p.title';
		$query->where  = ' 1 and '.$where;
		$query->order = 'r.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('paper_log');
	}

}