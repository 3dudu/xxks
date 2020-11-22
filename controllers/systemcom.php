<?php
/**
 * @brief 企业登录控制器
 * @class Systemcom
 */
class Systemcom extends IController
{
	public $layout = '';

	/**
	 * @brief 企业登录动作
	 */
	public function login()
	{
		$name = IFilter::act(IReq::get('username'));
		$password    = IReq::get('password');
		$message     = '';
		$captcha    = IReq::get('captcha','post');
		$_captcha   = ISafe::get('captcha');

		if($name == '')
		{
			$message = '登录名不能为空';
		}
		else if($password == '')
		{
			$message = '密码不能为空';
		}else if(!$captcha || !$_captcha || $captcha != $_captcha)
		{
            $message = '验证码输入不正确';
        }
        else
		{
			$company_adminObj = new IModel('company_admin as ca,company as c');
			$comAdminRow = $company_adminObj->getObj('ca.com_id=c.id and ca.name = "'.$name.'" and ca.is_del = 0 and c.is_del=0','ca.*,c.is_lock');
			if($comAdminRow && ($comAdminRow['password'] == md5($password)))
			{
				if($comAdminRow['is_lock']==1){
					$message = '企业信息正在审核中，请耐心等待';
				}else{
					$dataArray = array(
						'login_time' => ITime::getDateTime(),
					);
					$companyObj = new IModel('company_admin');
					$companyObj->setData($dataArray);
					$where = 'id = '.$comAdminRow["id"];
					$companyObj->update($where);
	
					//存入私密数据
					ISafe::set('com_admin_id',$comAdminRow['id']);
					ISafe::set('com_admin_name',$comAdminRow['name']);
					ISafe::set('com_admin_pwd',$comAdminRow['password']);
	
					ISafe::set('com_id',$comAdminRow['com_id']);
	
					//通知事件
					plugin::trigger("sellerLoginCallback",$comAdminRow);
					$this->redirect('/comadmin/index');
				}
			}
			else
			{
				$message = '用户名与密码不匹配';
			}
		}

		if($message != '')
		{
			$this->redirect('index',false);
			Util::showMessage($message);
		}
	}

	//后台登出
	function logout()
	{
		plugin::trigger('clearComadmin');
    	$this->redirect('/systemcom');
	}

	function com_reg(){
		$this->layout = "opencom";
		$this->companyRow = array(
			"province"=>320000,
			"city"=>321300,
			"area"=>321301,
			"street"=>321301,
		);
		$this->redirect('com_reg');
	}

	function success(){
		$this->layout = "opencom";
		$this->redirect('success');
	}

	function com_reg_act(){
		$this->layout = "opencom";

		$name = IFilter::act(IReq::get('name'));
		$email       = IFilter::act(IReq::get('email'));
		$phone       = IFilter::act(IReq::get('phone'));
		$mobile      = IFilter::act(IReq::get('mobile'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$street        = IFilter::act(IReq::get('street'),'int');
		$level        = '0';
		$is_lock     = '1';
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

		if($password == '')
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

		if($companyAdminDB->getObj("name = '{$admin_name}'"))
		{
			$errorMsg = "登录用户名重复";
		}
		else if($companyDB->getObj("(name = '{$name}' or com_code='{$com_code}')"))
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
			$this->redirect('com_reg',false);
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

		$this->redirect('success');
	}
}