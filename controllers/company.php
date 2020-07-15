<?php
/**
 * @brief 企业管理模块
 * @class Company
 * @note  后台
 */
class Company extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
    public $layout = 'admin';
    public $data = array();

	public function init()
	{

    }

    
	/**
	 * @brief 行业分类添加、修改
	 */
	function category_edit()
	{
		$category_id = IFilter::act(IReq::get('cid'),'int');
		if($category_id)
		{
			$categoryObj = new IModel('hy_category');
			$this->categoryRow = $categoryObj->getObj('id = '.$category_id);
		}
		$this->redirect('category_edit');
	}

	/**
	 * @brief 保存行业分类
	 */
	function category_save()
	{
		//获得post值
		$category_id = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$parent_id = IFilter::act(IReq::get('parent_id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$childString = company_class::hycatChild($category_id);//父类ID不能死循环设置成其子分类
		if($parent_id > 0 && stripos(",".$childString.",",",".$parent_id.",") !== false)
		{
			$this->redirect('/company/category_list/_msg/父分类设置错误');
			return;
		}

		$tb_category = new IModel('hy_category');
		$category_info = array(
			'name'      => $name,
			'parent_id' => $parent_id,
			'sort'      => $sort,
		);
		$tb_category->setData($category_info);
		if($category_id)									//保存修改分类信息
		{
			$where = "id=".$category_id;
			$tb_category->update($where);
		}
		else												//添加新行业分类
		{
			$tb_category->add();
		}
		$this->redirect('category_list');
	}

	/**
	 * @brief 删除行业分类
	 */
	function category_del()
	{
		$category_id = IFilter::act(IReq::get('cid'),'int');
		if($category_id)
		{
			$tb_category = new IModel('hy_category');
			$catRow      = $tb_category->getObj('parent_id = '.$category_id);

			//要删除的分类下还有子节点
			if($catRow)
			{
				$this->category_list();
				Util::showMessage('无法删除此分类，此分类下还有子分类，或者回收站内还留有子分类');
				exit;
			}

			if($tb_category->del('id = '.$category_id))
			{
				$tb_category_extend  = new IModel('company_category_extend');
				$tb_category_extend->del('category_id = '.$category_id);

				$tb_category_extend  = new IModel('exam_question_hy');
				$tb_category_extend->del('hy_id = '.$category_id);

				Api::removeCat('company','hy_id',$category_id);
				Api::removeCat('exam_paper','hy_md5',$category_id);

				$this->redirect('category_list');
			}
			else
			{
				$this->category_list();
				$msg = "没有找到相关分类记录！";
				Util::showMessage($msg);
			}
		}
		else
		{
			$this->category_list();
			$msg = "没有找到相关分类记录！";
			Util::showMessage($msg);
		}
	}

	/**
	 * @brief 行业分类列表
	 */
	function category_list()
	{
		$isCache = false;
		$tb_category = new IModel('hy_category');
		$cacheObj = new ICache('file');
		$data = $cacheObj->get('hy_category');
		if(!$data)
		{
			$data = company_class::sortdata($tb_category->query(false,'*','sort asc'));
			$isCache ? $cacheObj->set('hy_category',$data) : "";
		}
		$this->data['category'] = $data;
		$this->setRenderData($this->data);
		$this->redirect('category_list',false);
	}

	public function company_edit()
	{
		$company_id = IFilter::act(IReq::get('id'),'int');

		$level = $this->admin['level'];
		$this->fixLevel = false;
		if($level==0){
			$this->fixLevel = true;
		}
		//修改页面
		if($company_id)
		{
			$companyDB        = new IModel('company');
			$this->companyRow = $companyDB->getObj('id = '.$company_id);
			$companyAdminDB = new IModel("company_admin");
			$this->companyAdminRow = $companyAdminDB->getObj('is_default=1 and com_id = '.$company_id);


			//企业分类
			$categoryExtend = new IQuery('company_category_extend');
			$categoryExtend->where = 'com_id = '.$company_id;
			$categoryExtend->fields = 'category_id';
			$cateData = $categoryExtend->find();
			if($cateData)
			{
				foreach($cateData as $item)
				{
					$company_category[] = $item['category_id'];
				}
				$this->company_category = $company_category;
			}

			//企业经营范围
			$categoryExtend = new IQuery('company_jyfw_extend');
			$categoryExtend->where = 'com_id = '.$company_id;
			$categoryExtend->fields = 'jyfw_id';
			$cateData = $categoryExtend->find();
			if($cateData)
			{
				foreach($cateData as $item)
				{
					$jyfw_category[] = $item['jyfw_id'];
				}
				$this->jyfw_category = $jyfw_category;
			}
		}else{
			$this->companyRow = array(
				"province"=>$this->admin['province'],
				"city"=>$this->admin['city'],
				"area"=>$this->admin['area'],
				"street"=>321301,
			);
		}
		$this->redirect('company_edit');
	}

	//[企业]单页
	function company_add()
	{
		$company_id   = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$email       = IFilter::act(IReq::get('email'));
		$phone       = IFilter::act(IReq::get('phone'));
		$mobile      = IFilter::act(IReq::get('mobile'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$street        = IFilter::act(IReq::get('street'),'int');
		$level        = IFilter::act(IReq::get('level'),'int');
		$is_lock     = IFilter::act(IReq::get('is_lock'),'int');
		$address     = IFilter::act(IReq::get('address'));
		$server_num  = IFilter::act(IReq::get('server_num'));
		$home_url    = IFilter::act(IReq::get('home_url'));
		$sort        = IFilter::act(IReq::get('sort'),'int');
		$com_faren        = IFilter::act(IReq::get('com_faren'));
		$com_code        = IFilter::act(IReq::get('com_code'));
		$note        = IFilter::act(IReq::get('note'));
		$com_worknum        = IFilter::act(IReq::get('com_worknum'),'int');
		$com_managernum        = IFilter::act(IReq::get('com_managernum'),'int');
		$com_securitynum        = IFilter::act(IReq::get('com_securitynum'),'int');


		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$admin_name    = IFilter::act(IReq::get('admin_name'));

		if(!$company_id && $password == '')
		{
			$errorMsg = '请输入密码！';
		}

		if($password != $repassword)
		{
			$errorMsg = '两次输入的密码不一致！';
		}

		//创建商家操作类
		$companyAdminDB = new IModel("company_admin");
		$companyDB = new IModel("company");

		if($com_code){
			$comsql = "(name = '{$name}' or com_code='{$com_code}') and id != {$company_id}";
		}else{
			$comsql = "name = '{$name}' and id != {$company_id}";
		}

		if($companyAdminDB->getObj("name = '{$admin_name}' and com_id != {$company_id}"))
		{
			$errorMsg = "登录用户名重复";
		}
		else if($companyDB->getObj($comsql))
		{
			$errorMsg = "企业真实全称或营业执照号重复";
		}

		//操作失败表单回填
		if(isset($errorMsg))
		{
			$this->companyRow = $_POST;
			if(isset($_POST['_company_category']) && $_POST['_company_category']){
				$this->company_category = $_POST['_company_category'];
			}
			if(isset($_POST['_jyfw_category']) && $_POST['_jyfw_category']){
				$this->jyfw_category = $_POST['_jyfw_category'];
			}
			$this->redirect('company_edit',false);
			Util::showMessage($errorMsg);
		}
		$hy_md5 = '0';
		if(isset($_POST['_company_category']) && $_POST['_company_category']){
			sort($_POST['_company_category']);
			$hy_md5 = implode(',',$_POST['_company_category']);
		}

		//待更新的数据
		$companyRow = array(
			'name' => $name,
			'phone'     => $phone,
			'hy_id' => $hy_md5,
			'mobile'    => $mobile,
			'email'     => $email,
			'address'   => $address,
			'is_lock'   => $is_lock,
			'province'  => $province,
			'city'      => $city,
			'area'      => $area,
			'street'      => $street,
			'level'      => $level,
			'server_num'=> $server_num,
			'home_url'  => $home_url,
			'sort'      => $sort,
			'com_faren'      => $com_faren,
			'com_code'      => $com_code,
			'com_worknum'      => $com_worknum,
			'com_managernum'      => $com_managernum,
			'com_securitynum'      => $com_securitynum,
			'note'      => $note,
		);

		//附件上传$_FILE
		if($_FILES)
		{
		    $uploadDir = IWeb::$app->config['upload'].'/company';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

			//企业资质上传
			if(isset($photoInfo['paper_img']['img']) && file_exists($photoInfo['paper_img']['img']))
			{
				$companyRow['paper_img'] = $photoInfo['paper_img']['img'];
			}

			//logo图片处理
			if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
			{
				$companyRow['logo'] = $photoInfo['logo']['img'];
			}
		}

		//添加新会员
		if(!$company_id)
		{
			$companyRow['create_time'] = ITime::getDateTime();

			$companyDB->setData($companyRow);
			$company_id = $companyDB->add();
			
			$companyAdminDB->setData(array(
				"name"=>$admin_name,
				"password"=>md5($password),
				"com_id"=>$company_id,
				"is_default"=>1,
				"create_time"=>ITime::getDateTime()
			));
			$companyAdminDB->add();
		}
		//编辑会员
		else
		{
			$companyDB->setData($companyRow);
			$companyDB->update("id = ".$company_id);

			$companyAdminRow = array(
				"name"=>$admin_name,
				"com_id"=>$company_id,
			);
			//修改密码
			if($password)
			{
				$companyAdminRow['password'] = md5($password);
			}

			$companyAdminDB->setData($companyAdminRow);
			$companyAdminDB->update("com_id=".$company_id.' and is_default=1');
		}

		//处理企业分类
		$categoryDB = new IModel('company_category_extend');
		$categoryDB->del('com_id = '.$company_id);
		if(isset($_POST['_company_category']) && $_POST['_company_category'])
		{
			foreach($_POST['_company_category'] as $item)
			{
				$item = IFilter::act($item,'int');
				$categoryDB->setData(array('com_id' => $company_id,'category_id' => $item));
				$categoryDB->add();
			}
		}
		//处理企业经营范围
		$categoryDB = new IModel('company_jyfw_extend');
		$categoryDB->del('com_id = '.$company_id);
		if(isset($_POST['_jyfw_category']) && $_POST['_jyfw_category'])
		{
			foreach($_POST['_jyfw_category'] as $item)
			{
				$item = IFilter::act($item,'int');
				$categoryDB->setData(array('com_id' => $company_id,'jyfw_id' => $item));
				$categoryDB->add();
			}
		}

		$this->redirect('company_list');
	}

	//获取企业列表
	public function company_list()
	{
		
		$where       = Util::search(IReq::get('search'));
		
		$hy_id = IFilter::act(IReq::get('hy_id'));
		$where = 'c.is_del = 0 and '.$where;
		if($hy_id){
			$where = $where . ' and FIND_IN_SET('.$hy_id.',c.hy_id)';
		}
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and c.level=0 and c.area='.$this->admin['area'];
		}


		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("company as c");
		$query->where  = $where;
		$query->order = 'c.id desc';
		$query->join   = 'left join company_admin as a on c.id=a.com_id';
		$query->fields   = 'c.*,a.name as a_name';
		$query->page   = $page;
		$this->query    = $query;
		$this->search_category    = $hy_id;
		$this->redirect('company_list');
	}


	public function company_del(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$exam_paper_type = new IModel('company');
				$where = "id in (".$ids.")";
				$exam_paper_type->setData(array('is_del'=>1));
				$exam_paper_type->update($where);
			}
		}
		$this->company_list();
	}

	public function company_restore(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$exam_paper_type = new IModel('company');
				$where = "id in (".$ids.")";
				$exam_paper_type->setData(array('is_del'=>0));
				$exam_paper_type->update($where);
			}
		}
		$this->company_recycle_list();
	}

	//获取企业列表
	public function company_recycle_list()
	{
		$where       = Util::search(IReq::get('search'));

		$hy_id = IFilter::act(IReq::get('hy_id'));
		$where = 'is_del = 1 and '.$where;
		if($hy_id){
			$where = $where . ' and FIND_IN_SET('.$hy_id.',hy_id)';
		}
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and level=0 and area='.$this->admin['area'];
		}

		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("company");
		$query->where  = $where;
		$query->order = 'id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->search_category    = $hy_id;
		$this->redirect('company_recycle_list');
	}

	//企业状态ajax
	public function ajax_company_lock()
	{
		$id   = IFilter::act(IReq::get('id'));
		$lock = IFilter::act(IReq::get('lock'));
		$sellerObj = new IModel('company');
		$sellerObj->setData(array('is_lock' => $lock));
		$sellerObj->update("id = ".$id);
	}

		/**
	 * @brief 会员列表
	 */
	function company_work_list()
	{
		//更新过期状态
		$user_companyDB        = new IModel('user_company');
		$user_companyDB->setData(array(
			"is_check"=>1
		));
		$user_companyDB->update("is_del=0 and is_check=0 and tzzy_code<>'' and DATE_FORMAT(tzzy_f_time,'%Y%m')<=DATE_FORMAT(NOW(),'%Y%m')");

		$where = Util::search(IReq::get('search'));

		$gw_id = IFilter::act(IReq::get('gw_id'));
		if($gw_id){
			$where = $where . ' and FIND_IN_SET('.$gw_id.',u.gw_id)';
		}
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and c.level=0 and c.area='.$this->admin['area'];
		}
		$czz = IFilter::act(IReq::get('czz'));
		$aqz = IFilter::act(IReq::get('aqz'));
		if($czz){
			if($czz==1){
				$where = $where . ' and u.tzzy_code<>"" and curdate()<=u.tzzy_h_time and u.is_check=0 ';
			}elseif($czz==2){
				$where = $where . ' and u.tzzy_code<>"" and curdate()>u.tzzy_h_time ';
			}elseif($czz==3){
				$where = $where . ' and u.tzzy_code<>"" and u.is_check=1';
			}
		}
		if($aqz){
			if($aqz==1){
				$where = $where . ' and u.aqzg_code<>"" and curdate()<=u.aqzg_h_time ';
			}elseif($aqz==2){
				$where = $where . ' and u.aqzg_code<>"" and curdate()>u.aqzg_h_time ';
			}
		}


        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("user_company as u");
        $query->join   = 'left join member as m on m.user_id = u.user_id left join company as c on u.com_id=c.id';
        $query->where  = $where;
        $query->fields = "m.mobile,m.status,m.true_name,m.sex,m.point as m_point,u.*,c.name as cname,m.last_login,m.idcode,if(DATE_FORMAT(u.last_study_time,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d'),1,0) as today_studyed";
        $query->order  = 'u.is_del asc,u.last_study_time asc,u.id desc';
        $query->page   = $page;
		$this->query   = $query;
		$this->member_category    = $gw_id;
		$this->redirect('company_work_list');
	}

	//企业员工审核ajax
	public function ajax_user_company_lock()
	{
		$this->dataFormat = 'json';
		$id   = IFilter::act(IReq::get('id'));
		$accept = IFilter::act(IReq::get('accept'));
		$user_company = new IModel('user_company');
		$user_company->setData(array('is_accept' => $accept));
		$user_company->update("id = ".$id);
	}
	//企业员工注销ajax
	public function ajax_user_company_del()
	{
		$this->dataFormat = 'json';

		$id = IFilter::act(IReq::get('id'));
		$accept = IFilter::act(IReq::get('accept'));
		$user_company = new IModel('user_company');
		$workinfo = $user_company->getObj('id='.$id);
		if($accept){
			$user_company->setData(array('is_del' => $accept,'leve_time'=>ITime::getDateTime()));
			$user_company->update("id = ".$id);

			$memberDB = new IModel('member');
			$memberDB->setData(array('is_work'=>0));
			$memberDB->update('user_id='.$workinfo['user_id'] .' and is_work=1');
		}else{
			if($user_company->getObj("user_id=".$workinfo['user_id'].' and is_del=0')){
				IError::show(403,'用户已入职其他公司');
			}else{
				$user_company->setData(array('is_del' => $accept));
				$user_company->update("id = ".$id);


				$memberDB = new IModel('member');
				$memberDB->setData(array('is_work'=>1));
				$memberDB->update('user_id='.$workinfo['user_id'] .' and is_work=0');
			}
		}
	}

	//修改，新增准备数据
	public function company_work_edit()
	{
		$work_id = IFilter::act(IReq::get('id'),'int');

		//修改页面
		if($work_id)
		{
			$user_companyDB        = new IModel('user_company as u,company as c');
			$companyWorkRow = $user_companyDB->getObj('u.id = '.$work_id.' and u.com_id=c.id','u.*,c.name,c.logo');
			if(!$companyWorkRow)
			{
				$this->member_list();
				Util::showMessage("员工信息不完整！");
				exit;
			}
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
			$ra = array('userData' => $userData);
			$com_id = IFilter::act(IReq::get('com_id'));
			if($com_id){
				$user_companyDB        = new IModel('company as c');
				$companyWorkRow = $user_companyDB->getObj('id '.$com_id);
				$ra['companyWork'] = $companyWorkRow;
			}
			$this->setRenderData($ra);

		}
		$this->redirect('company_work_edit');
	}
	
	//提交数据
	public function company_work_save()
	{
		$user_id = IFilter::act(IReq::get('user_id'),'int');
		$true_name = IFilter::act(IReq::get('true_name'));
		$mobile      = IFilter::act(IReq::get('mobile'));
		$username      = IFilter::act(IReq::get('mobile'));
		$idcode      = IFilter::act(IReq::get('idcode'));
		$sex        = IFilter::act(IReq::get('sex'),'int');
		$com_id        = IFilter::act(IReq::get('com_id'),'int');
		$work_id        = IFilter::act(IReq::get('work_id'),'int');

		$email       = IFilter::act(IReq::get('email'));
		$telephone  = IFilter::act(IReq::get('telephone'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$status     = IFilter::act(IReq::get('status'),'int');
		//$point        = IFilter::act(IReq::get('point'),'int');
		$is_check        = IFilter::act(IReq::get('is_check'),'int');
		$contact_addr = IFilter::act(IReq::get('contact_addr'));
		$zip        = IFilter::act(IReq::get('zip'));
		$qq         = IFilter::act(IReq::get('qq'));

		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));

		$tzzy_code  = IFilter::act(IReq::get('tzzy_code'));
		$aqzg_code  = IFilter::act(IReq::get('aqzg_code'));

		if(is_array($com_id)){
			$com_id = $com_id[0];
		}

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
		//	'status'       => $status,
			'idcode'       => $idcode,
		);
		$member['area'] = ",320000,321300,321301,";
		if($province && $city && $area)
		{
			$member['area'] = ",{$province},{$city},{$area},";
		}

		//附件上传$_FILE

		$companyWork = array(
			'com_id'=>$com_id,
			'aqzg_code'=>$aqzg_code,
			'tzzy_code'=>$tzzy_code,
			'is_accept'=>1,
			'is_check'=>$is_check,
		);

		$companyWork['tzzy_img'] = IFilter::act(IReq::get('_tzzy_img'));
		$companyWork['tzzy_img2'] = IFilter::act(IReq::get('_tzzy_img2'));
		$companyWork['aqzg_img'] = IFilter::act(IReq::get('_aqzg_img'));
		$companyWork['aqzg_img2'] = IFilter::act(IReq::get('_aqzg_img2'));
		$member['logo'] = IFilter::act(IReq::get('_logo'));

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

		$gw_md5 = '0';
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
			//修改账号信息
			if($work_id){
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
			$member['point']=0;

			$memberDB->setData($member);
			$memberDB->add();
		}
		//处理企业关系
		if($work_id){
			$work_user = $user_companyDB->getObj("id=".$work_id);
			if($work_user['com_id']==$com_id){
				//没有换公司
				$user_companyDB->setData($companyWork);
				$user_companyDB->update('id='.$work_user['id']);
				$work_id = $work_user['id'];
			}else{
				if($work_user['is_del']==0){
					$user_companyDB->setData(array("is_del"=>1,'leve_time'=> ITime::getDateTime()));
					$user_companyDB->update('id='.$work_user['id']);
				}
				$companyWork['create_time']=ITime::getDateTime();
				$companyWork['user_id']=$work_user['user_id'];
				$user_companyDB->setData($companyWork);
				$work_id = $user_companyDB->add();
			}
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
		
		
		$this->redirect('company_work_list');
	}

	public function company_work_out(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$tb_member = new IModel('user_company');
				$where = "id in (".$ids.") and is_del=0";
				$user_ids = $tb_member->query($where,'user_id');
				if($user_ids)
				{
					foreach($user_ids as $item)
					{
						$user_id[] = $item['user_id'];
					}
				}
				$user_ids = implode(',',$user_id);

				$tb_member->setData(array('is_del'=>'1'));
				$tb_member->update($where);

				if($user_ids){
					$memberDB = new IModel('member');
					$memberDB->setData(array('is_work'=>0));
					$memberDB->update('user_id in ('.$user_ids.') and is_work=1');
				}
			}
		}
		$this->company_work_list();
	}


	public function company_work_in(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$tb_member = new IModel('user_company');
				$where = "id in (".$ids.") and is_del=1";
				$user_ids = $tb_member->query($where,'user_id');
				if($user_ids)
				{
					foreach($user_ids as $item)
					{
						$user_id[] = $item['user_id'];
					}
				}
				$user_ids = implode(',',$user_id);
				
				$tb_member->setData(array('is_del'=>'0'));
				$tb_member->update($where);

				if($user_ids){
					$memberDB = new IModel('member');
					$memberDB->setData(array('is_work'=>1));
					$memberDB->update('user_id in ('.$user_ids.') and is_work=0');
				}
			}
		}
		$this->company_work_list();
	}


	public function company_work_accept_in(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$tb_member = new IModel('user_company');
				$tb_member->setData(array('is_accept'=>'1'));
				$where = "id in (".$ids.") and is_accept=0";
				$tb_member->update($where);
			}
		}
		$this->company_work_list();
	}

	public function company_work_accept_out(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$tb_member = new IModel('user_company');
				$tb_member->setData(array('is_accept'=>'0'));
				$where = "id in (".$ids.") and is_accept=1";
				$tb_member->update($where);
			}
		}
		$this->company_work_list();
	}

	public function company_work_del(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$tb_member = new IModel('user_company');
				$tb_member->setData(array('is_del'=>'1'));
				$where = "id in (".$ids.") and is_del=1";
				$tb_member->del($where);
			}
		}
		$this->company_work_list();
	}

	public function select_company(){
		$this->setRenderData($_GET);
		$this->redirect('select_company');
	}

	public function select_company_list(){
		$this->layout='';
		$cat_id      = IFilter::act( IReq::get('_company_category'),'int');
		$search      = IFilter::act(IReq::get('search'));
		$show_num    = IFilter::act( IReq::get('show_num'),'int');

		$table_name = 'company as a';
		$where = ' 1 ';
		if(isset($search['content']))
		{
			$where .= " and ".$search['type']." like '%{$search['content']}%' ";
		}
		if($cat_id)
		{
			$table_name .= ' ,company_category_extend as ca ';
			$where      .= " and ca.category_id = {$cat_id} and a.id = ca.com_id ";
		}
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and a.level=0 and a.area='.$this->admin['area'];
		}
		$fields     = 'a.*,a.id as com_id';
		$appDB = new IModel($table_name);
		$data    = $appDB->query($where,$fields,'a.id desc',$show_num);

		$this->data = $data;
		$this->type = IFilter::act(IReq::get('type'));//页面input的type类型，比如radio，checkbox
		$this->redirect('select_company_list');
	}


		/**
	 */
	public function categoryAjax()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$parent_id = IFilter::act(IReq::get('parent_id'),'int');
		if($id && is_array($id))
		{
			foreach($id as $category_id)
			{
				$childString = company_class::hycatChild($category_id);//父类ID不能死循环设置成其子分类
				if($parent_id > 0 && stripos(",".$childString.",",",".$parent_id.",") !== false)
				{
					die(JSON::encode(array('result' => 'fail')));
				}
			}

			$catDB     = new IModel('hy_category');
			$catDB->setData(array('parent_id' => $parent_id));
			$result = $catDB->update('id in ('.join(",",$id).')');
			if($result)
			{
				die(JSON::encode(array('result' => 'success')));
			}
		}
		die(JSON::encode(array('result' => 'fail')));
	}

	function category_sort()
	{
		$category_id = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$flag = 0;
		if($category_id)
		{
			$tb_category = new IModel('hy_category');
			$category_info = $tb_category->getObj('id='.$category_id);
			if(count($category_info)>0)
			{
				if($category_info['sort']!=$sort)
				{
					$tb_category->setData(array('sort'=>$sort));
					if($tb_category->update('id='.$category_id))
					{
						$flag = 1;
					}
				}
			}
		}
		echo $flag;
	}


	/**
	 * @brief 经营范围添加、修改
	 */
	function jyfw_edit()
	{
		$jyfw_id = IFilter::act(IReq::get('cid'),'int');
		if($jyfw_id)
		{
			$categoryObj = new IModel('jyfw_category');
			$this->categoryRow = $categoryObj->getObj('id = '.$jyfw_id);
		}
		$this->redirect('jyfw_edit');
	}

	/**
	 * @brief 保存经营范围
	 */
	function jyfw_save()
	{
		//获得post值
		$jyfw_id = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$parent_id = IFilter::act(IReq::get('parent_id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$childString = company_class::jyfwcatChild($jyfw_id);//父类ID不能死循环设置成其子分类
		if($parent_id > 0 && stripos(",".$childString.",",",".$parent_id.",") !== false)
		{
			$this->redirect('/company/jyfw_list/_msg/父分类设置错误');
			return;
		}

		$tb_category = new IModel('jyfw_category');
		$jyfw_info = array(
			'name'      => $name,
			'parent_id' => $parent_id,
			'sort'      => $sort,
		);
		$tb_category->setData($jyfw_info);
		if($jyfw_id)									//保存修改分类信息
		{
			$where = "id=".$jyfw_id;
			$tb_category->update($where);
		}
		else												//添加新经营范围
		{
			$tb_category->add();
		}
		$this->redirect('jyfw_list');
	}

	/**
	 * @brief 删除行业分类
	 */
	function jyfw_del()
	{
		$jyfw_id = IFilter::act(IReq::get('cid'),'int');
		if($jyfw_id)
		{
			$tb_category = new IModel('jyfw_category');
			$catRow      = $tb_category->getObj('parent_id = '.$jyfw_id);

			//要删除的分类下还有子节点
			if($catRow)
			{
				$this->jyfw_list();
				Util::showMessage('无法删除此分类，此分类下还有子分类，或者回收站内还留有子分类');
				exit;
			}

			if($tb_category->del('id = '.$jyfw_id))
			{
				$tb_jyfw_extend  = new IModel('company_jyfw_extend');
				$tb_jyfw_extend->del('jyfw_id = '.$jyfw_id);
				$this->redirect('jyfw_list');
			}
			else
			{
				$this->jyfw_list();
				$msg = "没有找到相关分类记录！";
				Util::showMessage($msg);
			}
		}
		else
		{
			$this->jyfw_list();
			$msg = "没有找到相关分类记录！";
			Util::showMessage($msg);
		}
	}

	/**
	 * @brief 行业分类列表
	 */
	function jyfw_list()
	{
		$isCache = false;
		$tb_category = new IModel('jyfw_category');
		$cacheObj = new ICache('file');
		$data = $cacheObj->get('jyfw_category');
		if(!$data)
		{
			$data = company_class::sortdata($tb_category->query(false,'*','sort asc'));
			$isCache ? $cacheObj->set('jyfw_category',$data) : "";
		}
		$this->data['category'] = $data;
		$this->setRenderData($this->data);
		$this->redirect('jyfw_list',false);
	}


		/**
	 */
	public function jyfwAjax()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$parent_id = IFilter::act(IReq::get('parent_id'),'int');
		if($id && is_array($id))
		{
			foreach($id as $jyfw_id)
			{
				$childString = company_class::hycatChild($jyfw_id);//父类ID不能死循环设置成其子分类
				if($parent_id > 0 && stripos(",".$childString.",",",".$parent_id.",") !== false)
				{
					die(JSON::encode(array('result' => 'fail')));
				}
			}

			$catDB     = new IModel('jyfw_category');
			$catDB->setData(array('parent_id' => $parent_id));
			$result = $catDB->update('id in ('.join(",",$id).')');
			if($result)
			{
				die(JSON::encode(array('result' => 'success')));
			}
		}
		die(JSON::encode(array('result' => 'fail')));
	}

	function jyfw_sort()
	{
		$jyfw_id = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$flag = 0;
		if($jyfw_id)
		{
			$tb_category = new IModel('jyfw_category');
			$jyfw_info = $tb_category->getObj('id='.$jyfw_id);
			if(count($jyfw_info)>0)
			{
				if($jyfw_info['sort']!=$sort)
				{
					$tb_category->setData(array('sort'=>$sort));
					if($tb_category->update('id='.$jyfw_id))
					{
						$flag = 1;
					}
				}
			}
		}
		echo $flag;
	}
}