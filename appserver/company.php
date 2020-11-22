<?php
/**
 * @brief 学习接口
 * @class Company
 * 接口 访问路径
 *  http://iweb.goat.msns.cn:5088/api/study/studyList
 */
class Company extends IApiController implements userAuthorization
{
	public function init()
	{

    }

    public function img_upload()
	{
		 //调用文件上传类
		$uploadDir = IWeb::$app->config['upload'].'/member';
		$photoObj = new PhotoUpload($uploadDir);
		$result   = current($photoObj->run());
		return $result;
	}

    public function changeWorkInfo(){
        $user_id   = $this->user['user_id'];
        $work_id  = IFilter::act(IReq::get('work_id'),'int');
        $is_check  = IFilter::act(IReq::get('is_check'),'int');

        $tzzy_code  = IFilter::act(IReq::get('tzzy_code'));
		$aqzg_code  = IFilter::act(IReq::get('aqzg_code'));
        $aqzg_c_time = IFilter::act(IReq::get('aqzg_c_time'),'date');
        $aqzg_h_time = IFilter::act(IReq::get('aqzg_h_time'),'date');
        $aqzg_f_time = IFilter::act(IReq::get('aqzg_f_time'));

        $tzzy_c_time = IFilter::act(IReq::get('tzzy_c_time'),'date');
        $tzzy_h_time = IFilter::act(IReq::get('tzzy_h_time'),'date');
        $tzzy_f_time = IFilter::act(IReq::get('tzzy_f_time'));

        $aqzg_img = IFilter::act(IReq::get('aqzg_img'));
        $aqzg_img2 = IFilter::act(IReq::get('aqzg_img2'));
        $tzzy_img = IFilter::act(IReq::get('tzzy_img'));
        $tzzy_img2 = IFilter::act(IReq::get('tzzy_img2'));

        $companyWork = array();
        if($aqzg_code){$companyWork['aqzg_code'] = $aqzg_code;}
        if($aqzg_c_time){$companyWork['aqzg_c_time'] = $aqzg_c_time;}
        if($aqzg_h_time){$companyWork['aqzg_h_time'] = $aqzg_h_time;}
        if($aqzg_f_time){$companyWork['aqzg_f_time'] = $aqzg_f_time;}
        if($tzzy_code){$companyWork['tzzy_code'] = $tzzy_code;}
        if($tzzy_c_time){$companyWork['tzzy_c_time'] = $tzzy_c_time;}
        if($tzzy_h_time){$companyWork['tzzy_h_time'] = $tzzy_h_time;}
        if($tzzy_f_time){$companyWork['tzzy_f_time'] = $tzzy_f_time;}
        if($aqzg_img){$companyWork['aqzg_img'] = $aqzg_img;}
        if($aqzg_img2){$companyWork['aqzg_img2'] = $aqzg_img2;}
        if($tzzy_img){$companyWork['tzzy_img'] = $tzzy_img;}
        if($tzzy_img2){$companyWork['tzzy_img2'] = $tzzy_img2;}

        $user_companyDB        = new IModel('user_company');
        $work = $user_companyDB->getObj("id=".$work_id.' and user_id='.$user_id);
        if(!$work){
            IError::show(400009,"工作关系不存在");
        }else{
            if($is_check==2 || (isset($companyWork['tzzy_f_time']) && $work['tzzy_f_time']!=$companyWork['tzzy_f_time'])){
                $companyWork['is_check'] = $is_check;
            }
        }
        if($_FILES)
		{
			$uploadDir = IWeb::$app->config['upload'].'/member';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

			if(isset($photoInfo['aqzg_img']['img']) && file_exists($photoInfo['aqzg_img']['img']))
			{
				$companyWork['aqzg_img'] = $photoInfo['aqzg_img']['img'];
			}
			if(isset($photoInfo['tzzy_img']['img']) && file_exists($photoInfo['tzzy_img']['img']))
			{
				$companyWork['tzzy_img'] = $photoInfo['tzzy_img']['img'];
			}
			if(isset($photoInfo['aqzg_img2']['img']) && file_exists($photoInfo['aqzg_img2']['img']))
			{
				$companyWork['aqzg_img2'] = $photoInfo['aqzg_img2']['img'];
			}
			if(isset($photoInfo['tzzy_img2']['img']) && file_exists($photoInfo['tzzy_img2']['img']))
			{
				$companyWork['tzzy_img2'] = $photoInfo['tzzy_img2']['img'];
			}
        }
   
        $user_companyDB->setData($companyWork);
        $work_id = $user_companyDB->update("id=".$work_id);
    }

    public function joinCompany(){
        $user_id   = $this->user['user_id'];

        $user_companyDB        = new IModel('user_company');
        $com_id        = IFilter::act(IReq::get('com_id'),'int');
        $gw_ids        = IFilter::act(IReq::get('gw_id'),'int');
        $tzzy_code  = IFilter::act(IReq::get('tzzy_code'));
		$aqzg_code  = IFilter::act(IReq::get('aqzg_code'));
        if($gw_ids && !is_array($gw_ids)){
            $gw_ids = array($gw_ids);
        }


        $companyDB = new IModel('company');
        $company = $companyDB->getObj('is_del=0 and id='.$com_id);
        if(!$company){
            IError::show(400002,"选择的公司不存在");
        }
        
        $memberDB = new IModel('member');
        if($memberDB->getObj("user_id=".$user_id.' and is_work=1')){
            IError::show(400000,"用户已入职其他公司,请直接登录");
        }

        if($work = $user_companyDB->getObj("user_id=".$user_id.' and is_del=0')){
            if($work['com_id']==$com_id){
                IError::show(400001,"用户已入职该公司,请直接登录");
            }else{
                IError::show(400000,"用户已入职其他公司,请直接登录");
            }
        }
        $memberDB->setData(array('is_work'=>1));
        $rid = $memberDB->update('user_id='.$user_id.' and is_work=0');
        if(!$rid){
            IError::show(400000,"用户已入职其他公司,请直接登录");
        }

        $companyWork = array(
            'com_id'=>$com_id,
            'aqzg_code'=>$aqzg_code,
            'tzzy_code'=>$tzzy_code,
            'aqzg_img'=>IFilter::act(IReq::get('aqzg_img')),
			'aqzg_img2'=>IFilter::act(IReq::get('aqzg_img2')),
			'tzzy_img'=>IFilter::act(IReq::get('tzzy_img')),
			'tzzy_img2'=>IFilter::act(IReq::get('tzzy_img2')),
        );

        if($_FILES)
		{
			$uploadDir = IWeb::$app->config['upload'].'/member';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

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
		if($gw_ids){
			sort($gw_ids);
			$gw_md5 = implode(',',$gw_ids);
		}
		$companyWork['gw_id'] = $gw_md5;
        $companyWork['create_time']=ITime::getDateTime();
		$companyWork['user_id']=$user_id;
		$user_companyDB->setData($companyWork);
        $work_id = $user_companyDB->add();
        
        //处理关系
		$categoryDB = new IModel('user_category_extend');
		$categoryDB->del('work_id = '.$work_id);
		if($gw_ids)
		{
			foreach($gw_ids as $item)
			{
				$item = IFilter::act($item,'int');
				$categoryDB->setData(array('work_id' => $work_id,'category_id' => $item));
				$categoryDB->add();
			}
        }
        
        return "申请成功，等待管理员审核";
    }
    public function findCompanyList(){
        $keywords        = IFilter::act(IReq::get('keywords'));

        $companyDB = new IModel('company');
        $companyList = $companyDB->query('is_del=0 and (name like "%'.$keywords.'%" or com_faren  like "%'.$keywords.'%")','*','',20);
        return $companyList;
    }

    public function getGWCateList(){
        $tb_category = new IModel('gw_category');
        $pid        = IFilter::act(IReq::get('pid'));
        $where = '';
        if($pid!==''){
            $where = 'parent_id='.$pid;
        }
        $data = $tb_category->query($where,'*','sort asc');
        if($pid===''){
            $data = company_class::sortdata($data);
        }
        return $data;
    }
}