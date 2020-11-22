<?php
/**
 * @brief 考试管理模块
 * @class Exam
 * @note  后台
 */
class Exam extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
    public $layout = 'admin';
    public $data = array();

	public function init()
	{

	}
	
	function paper_edit()
	{
		$id = IFilter::act(IReq::get("id"),'int');
		if($id)
		{
			$exam_paperDB = new IModel('exam_paper');
			$paper = $exam_paperDB->getObj('id='.$id);
			if(!$paper)
			{
				IError::show(403,'信息不存在');
			}
			$this->paper_row = $paper;
			$this->redirect("paper_edit");
		}else{
			IError::show(403,'信息不存在');
		}
	}

	function paper_edit_act()
	{
		$id   = IFilter::act(IReq::get("id","post"),'int');
		$data = array(
			"title"          => IFilter::act(IReq::get("title")),
			"description"          => IFilter::act(IReq::get("description")),
		);

		$exam_paperDB = new IModel('exam_paper');
		$exam_paperDB->setData($data);

		if($id)
		{
			$exam_paperDB->update('id = '.$id);
		}
		$this->redirect('paper_list');
	}

	function paper_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$id = is_array($id) ? join(',',$id) : $id;
		if(!$id)
		{
			$this->redirect('question_pool_list',false);
			util::showMessage("请选择要删除的信息");
			return;
		}
		$questionPoolDB = new IModel('exam_paper');
		$questionPoolDB->setData(array("is_del"=>1));
		$questionPoolDB->update('id in ('.$id.')');

		$this->redirect('paper_list');
	}

	function question_pool_edit()
	{
		$id = IFilter::act(IReq::get("id"),'int');
		if($id)
		{
			$catRow = Api::run('getQuestionPoolInfo',array('id' => $id));
			if(!$catRow)
			{
				IError::show(403,'信息不存在');
			}
			$this->pool_row = $catRow;
		}
		$this->redirect("question_pool_edit");
	}

	function poolinit(){
		$mainc = "";
		$questionPoolDB = new IModel('exam_pool');
		$max_id  = $questionPoolDB->getObj('','max(id) as max_id');
		$localId = $max_id['max_id'] ? $max_id['max_id']+1 : 1;

		$chuji = new IQuery('chujikuaiji');
		$chuji->fields="main_class,sub_class";
		$chuji->group = "main_class,sub_class";
		$chuji->order = "main_class desc";
		$li = $chuji->find();
		$sort = 0;
		foreach($li as $val){
			if($mainc!=$val['main_class']){
				$sort = 0;
				$mainc = $val['main_class'];
				$maindata = array(
					"id"=>$localId,
					"parent_id"=>0,
					"path"=>",".$localId.",",
					"title"=>$mainc,
					"description"=>$mainc,
					"visibility"=>1,
					"sort"=>0,
					"price"=>0,
					"poolformat"=>1,
					"sub_class"=>""
				);
				$questionPoolDB->setData($maindata);
				$questionPoolDB->add();
				$localId++;
			}
			$subdata = array(
				"id"=>$localId,
				"parent_id"=>$maindata['id'],
				"path"=>",".$maindata['id'].",".$localId.",",
				"title"=>$mainc.":".$val['sub_class'],
				"description"=>$val['sub_class'],
				"visibility"=>1,
				"sort"=>$sort++,
				"price"=>0,
				"poolformat"=>1,
				"sub_class"=>$val['sub_class']
			);
			$questionPoolDB->setData($subdata);
			$questionPoolDB->add();
			$localId++;
		}
		$this->redirect("question_pool_list");


	}

	function question_pool_edit_act()
	{
		$id   = IFilter::act(IReq::get("id","post"),'int');
		$parent_id = IFilter::act( IReq::get('parent_id','post') ) ;

		$data = array(
			'parent_id' => $parent_id,
			"title"          => IFilter::act(IReq::get("title")),
			"description"          => IFilter::act(IReq::get("description")),
			"visibility"          => IFilter::act(IReq::get("visibility")),
			"price"          => IFilter::act(IReq::get("price")),
			"price_level"          => IFilter::act(IReq::get("price_level")),
			"sort"          => IFilter::act(IReq::get("sort"),'int'),
			"bdcid"          => IFilter::act(IReq::get("bdcid")),
			"poolformat"          => IFilter::act(IReq::get("poolformat")),
			"sub_class"          => IFilter::act(IReq::get("sub_class")),
		);

		$questionPoolDB = new IModel('exam_pool');

		/*开始--获取path信息*/
		//1,修改操作
		if($id)
		{
			$where  = 'id = '.$id;
			$catRow = $questionPoolDB->getObj($where);
			if($catRow['parent_id']==$parent_id)
			{
				$isMoveNode = false;
				$data['path'] = $catRow['path'];
			}
			else
				$isMoveNode = true;

			$localId = $id;
		}
		//2,新增操作
		else
		{
			$max_id  = $questionPoolDB->getObj('','max(id) as max_id');
			$localId = $max_id['max_id'] ? $max_id['max_id']+1 : 1;
		}

		//如果不存在path数据时,计算path数据
		if(!isset($data['path']))
		{
			//获取父节点的path路径
			if($parent_id==0)
				$data['path'] = ','.$localId.',';
			else
			{
				$where     = 'id = '.$parent_id;
				$parentRow = $questionPoolDB->getObj($where);
				$data['path'] = $parentRow['path'].$localId.',';
			}
		}
		$questionPoolDB->setData($data);


		//1,修改操作
		if($id)
		{
			//节点移动
			if($isMoveNode == true)
			{
				if(isset($parentRow) && $parentRow['path']!=null && strpos($parentRow['path'],','.$id.',')!==false)
				{
					$this->pool_row = array(
						'parent_id' => $data['parent_id'],
						'title'      => $data['title'],
						'sort'      => $data['sort'],
						'price'      => $data['price'],
						'price_level'      => $data['price_level'],
						'id'        => $id,
						'visibility'     => $data['visibility'],
						'description'=>$data['description'],
					);
					$this->redirect('question_pool_edit',false);
					Util::showMessage('不能该节点移动到其子节点的位置上');
				}
				else
				{
					//其子节点批量移动
					$childObj = new IModel('exam_pool');
					$oldPath  = $catRow['path'];
					$newPath  = $data['path'];

					$where = 'path like "'.$oldPath.'%"';
					$updateData = array(
						'path' => "replace(path,'".$oldPath."','".$newPath."')",
					);
					$childObj->setData($updateData);
					$childObj->update($where,array('path'));
				}
			}
			$where = 'id = '.$id;
			$questionPoolDB->update($where);
		}
		//2,新增操作
		else
			$questionPoolDB->add();
		$this->redirect('question_pool_list');
	}

	function question_pool_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$id = is_array($id) ? join(',',$id) : $id;
		if(!$id)
		{
			$this->redirect('question_pool_list',false);
			util::showMessage("请选择要删除的信息");
			return;
		}
		$questionPoolDB = new IModel('exam_pool');
		$questionPoolDB->del('id in ('.$id.')');
		$questionObj       = new IModel('exam_question');
		$questionObj->setData(array("poolid"=>0));
		$questionObj->update('poolid in ('.$id.')');

		$this->redirect('question_pool_list');
	}

	function question_edit()
	{
		$data = array();
		$id   = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			//获取文章信息
			$questionObj       = new IModel('exam_question');
			$questionRow = $questionObj->getObj('id = '.$id);
			if(!$questionRow)
			{
				IError::show(403,"试题信息不存在");
			}
			
			//行业分类
			$categoryExtend = new IQuery('exam_question_gw');
			$categoryExtend->where = 'q_id = '.$id;
			$categoryExtend->fields = 'gw_id';
			$cateData = $categoryExtend->find();
			if($cateData)
			{
				foreach($cateData as $item)
				{
					$member_category[] = $item['gw_id'];
				}
				$questionRow['member_category'] = $member_category;
			}
			$categoryExtend = new IQuery('exam_question_hy');
			$categoryExtend->where = 'q_id = '.$id;
			$categoryExtend->fields = 'hy_id';
			$cateData = $categoryExtend->find();
			if($cateData)
			{
				foreach($cateData as $item)
				{
					$company_category[] = $item['hy_id'];
				}
				$questionRow['company_category'] = $company_category;
			}
			if($questionRow['type']=='1' || $questionRow['type']=='4'){
				$questionRow['items'] = array(
					array("item_value"=>"A"),
					array("item_value"=>"B"),
					array("item_value"=>"C"),
					array("item_value"=>"D"),
					array("item_value"=>"E"),
					array("item_value"=>"F"),
				);
			}else{
				$questionRow['items'] = JSON::decode($questionRow['items']);
			}

            $this->setRenderData(array('questionRow' => $questionRow));
		}else{
            $questionRow = array(
				"answer"=>'',
                "items"=>array(
					array("item_value"=>"A"),
					array("item_value"=>"B"),
					array("item_value"=>"C"),
					array("item_value"=>"D"),
					array("item_value"=>"E"),
					array("item_value"=>"F"),
				)
            );
            $this->setRenderData(array('questionRow' => $questionRow));
        }
		$this->redirect('question_edit');
	}

	function question_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		$questionObj = new IModel('exam_question');
		$level = IFilter::act(IReq::get('level','post'),'int');
		$type = IFilter::act(IReq::get('type','post'),'int');
		$poolid = IFilter::act(IReq::get('poolid','post'),'int');
		$question = IFilter::act(IReq::get('question','post'));
		$explain = IFilter::act(IReq::get('explain','post'));
		//$thumb = "";
		$dataArray  = array(
			'level'       => $level,
			'type'       => $type,
			'question'       => $question,
			'explain'       => $explain,
			//'thumb'       => $thumb,
			'poolid'       => $poolid,
			'update_time' => ITime::getDateTime(),
        );

		//附件上传$_FILE
		if($_FILES)
		{
			$uploadDir = IWeb::$app->config['upload'].'/exam';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

			//logo图片处理
			if(isset($photoInfo['thumb']['img']) && file_exists($photoInfo['thumb']['img']))
			{
				$dataArray['thumb'] = $photoInfo['thumb']['img'];
			}
		}

		//题项
		if($dataArray['type']=='1'){
			$dataArray['answer'] = IFilter::act(IReq::get('item_value','post'),'int');
			$dataArray['items']='';
		}elseif($dataArray['type']=='2' || $dataArray['type']=='3'){
			$item_content = IFilter::act(IReq::get('item_content','post'));
			$item_value = IFilter::act(IReq::get('item_value','post'));
			$item_file = IFilter::act(IReq::get('item_file','post'));
			if(!$item_value){
				$item_value=array('A');
			}
			$dataArray['answer']=implode('',$item_value);
			$items = array();
			$allItem = array('A','B','C','D','E','F');
			foreach($allItem as $key=>$item){
				if(isset($item_content[$key]) || isset($item_content[$key])){
					$items[] = array(
						"item_content"=>isset($item_content[$key])?$item_content[$key]:'',
						"item_value"=>$item,
						"item_file"=>isset($item_file[$key])?$item_file[$key]:'',
					);
				}
			}
			$dataArray['items']=JSON::encode($items);
		}elseif($dataArray['type']=='4'){
			$dataArray['answer'] = IFilter::act(IReq::get('answer','post'),'string');
			$dataArray['items']='';
		}


		if($id)
		{
			$questionObj->setData($dataArray);
			//开始更新操作
			$where = 'id = '.$id;
			$is_success = $questionObj->update($where);
		}
		else
		{
			$dataArray['create_time'] = $dataArray['update_time'];
			$questionObj->setData($dataArray);
			$id = $questionObj->add();
			$is_success = $id ? true : false;
		}

		if($is_success)
		{
			//处理关系
			$gw_categoryDB = new IModel('exam_question_gw');
			$gw_categoryDB->del('q_id = '.$id);
			if(isset($_POST['_member_category']) && $_POST['_member_category'])
			{
				foreach($_POST['_member_category'] as $item)
				{
					$item = IFilter::act($item,'int');
					$gw_categoryDB->setData(array('q_id' => $id,'gw_id' => $item));
					$gw_categoryDB->add();
				}
			}

			//处理关系
			$hy_categoryDB = new IModel('exam_question_hy');
			$hy_categoryDB->del('q_id = '.$id);
			if(isset($_POST['_company_category']) && $_POST['_company_category'])
			{
				foreach($_POST['_company_category'] as $item)
				{
					$item = IFilter::act($item,'int');
					$hy_categoryDB->setData(array('q_id' => $id,'hy_id' => $item));
					$hy_categoryDB->add();
				}
			}
		}
		else
		{
			$dataArray['id'] = $id;
			$dataArray['items']=$items;
			if(isset($_POST['_member_category']) && $_POST['_member_category'])
			{
				$dataArray['member_category']=$_POST['_member_category'];
			}
			if(isset($_POST['_company_category']) && $_POST['_company_category'])
			{
				$dataArray['company_category']=$_POST['_company_category'];
			}
			$this->setRenderData(array('questionRow' => $dataArray));
			$this->redirect('question_edit',false);
			Util::showMessage('插入数据时发生错误');
		}

		$this->redirect('question_list');
    }
	
	//获取题目列表
	public function question_list()
	{
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("exam_question as q");
		$query->where  = 'q.is_del = 0 and '.$where;
		$query->join = 'left join exam_pool as p on q.poolid=p.id';
		$query->fields = 'q.*,p.title as p_title';
		$query->order = 'q.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('question_list');
	}

	public function question_del(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$exam_paper_type = new IModel('exam_question');
				$where = "id in (".$ids.")";
				$exam_paper_type->setData(array('is_del'=>1));
				$exam_paper_type->update($where);
			}
		}
		$this->question_list();
	}

	public function question_real_del(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$exam_paper_type = new IModel('exam_question');
				$where = "id in (".$ids.")";
				$exam_paper_type->del($where);

				$gw_categoryDB = new IModel('exam_question_gw');
				$gw_categoryDB->del("q_id in (".$ids.")");

				$gw_categoryDB = new IModel('exam_question_hy');
				$gw_categoryDB->del("q_id in (".$ids.")");
			}
		}
		$this->question_recycle_list();
	}

	public function question_recycle_empty(){
		$exam_paper_type = new IModel('exam_question');
		$where = "is_del=1";
		$exam_paper_type->del($where);
		$this->question_recycle_list();
	}

	public function question_recycle_list(){
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("exam_question as q");
		$query->where  = 'q.is_del = 1 and '.$where;
		$query->join = 'left join exam_pool as p on q.poolid=p.id';
		$query->fields = 'q.*,p.title as p_title';
		$query->order = 'q.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('question_recycle_list');
	}

	public function question_restore(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$exam_paper_type = new IModel('exam_question');
				$where = "id in (".$ids.")";
				$exam_paper_type->setData(array('is_del'=>0));
				$exam_paper_type->update($where);
			}
		}
		$this->question_recycle_list();
	}

	function paper_type_edit()
	{
		$data = array();
		$id   = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			//获取文章信息
			$paperTypeObj       = new IModel('exam_paper_type');
			$paperType = $paperTypeObj->getObj('id = '.$id);
			if(!$paperType)
			{
				IError::show(403,"试卷类型不存在");
            }
            
			$types = JSON::decode($paperType['types']);
			$paperType['types'] = $types;
			if($paperType['q_scope']==3){
				$paperType['q_scope'] = array(1,2);
			}
            $this->setRenderData(array('paperType' => $paperType));
		}
		$this->redirect('paper_type_edit');
	}

	function paper_type_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$poolid  = IFilter::act(IReq::get('poolid','post'));

		if(is_string($poolid))
		{
			$poolid_str = $poolid;
		}
		else
		{
			$poolid_str = join(',',$poolid);
		}
		$paperTypeObj = new IModel('exam_paper_type');
		$dataArray  = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'score' => IFilter::act(IReq::get('score','post'),'int'),
			'description' => IFilter::act(IReq::get('description','post'),'text'),
			'visibility'   => IFilter::act(IReq::get('visibility','post'),'int'),
			'times'         => IFilter::act(IReq::get('times','post'),'int'),
			'sort'        => IFilter::act(IReq::get('sort','post'),'float'),
			'maxScore'        => IFilter::act(IReq::get('maxScore','post'),'float'),
			'c_paper'        => IFilter::act(IReq::get('c_paper','post'),'int'),
			'c_priod'        => IFilter::act(IReq::get('c_priod','post'),'int'),
			'c_times'        => IFilter::act(IReq::get('c_times','post'),'int'),
			'fix_paper'        => IFilter::act(IReq::get('fix_paper','post'),'int'),
			'num'        => IFilter::act(IReq::get('num','post'),'int'),
			'mode'        => IFilter::act(IReq::get('mode','post'),'int'),
			'level'        => IFilter::act(IReq::get('level','post'),'int'),
			'poolid' => $poolid_str,
			'q_scope'        => 0,
			'score_priod'        => IFilter::act(IReq::get('score_priod','post'),'int'),
		);
				//附件上传$_FILE
				if($_FILES)
				{
					$uploadDir = IWeb::$app->config['upload'].'/exam';
					$uploadObj = new PhotoUpload($uploadDir);
					$uploadObj->setIterance(false);
					$photoInfo = $uploadObj->run();
		
					//logo图片处理
					if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
					{
						$dataArray['logo'] = $photoInfo['logo']['img'];
					}
				}

		$q_scope = IFilter::act(IReq::get('q_scope','post'),'int');
		$scope = 0;
		if($q_scope){
			foreach($q_scope as $val){
				$scope += intval($val);
			} 
		}
		$dataArray['q_scope'] = $scope;
		$allTypes = array('0','2','3','1','4');
		$type_value = IFilter::act(IReq::get('type_value','post'));
		$allType_num = IFilter::act(IReq::get('type_num','post'));
		$allType_point = IFilter::act(IReq::get('type_point','post'));

		$types = array();
		foreach($allTypes as $key=>$type ){
			if(in_array($type,$type_value)){
				$types[] = array(
					'type_value'=>$type,
					'type_num'=>$allType_num[$key],
					'type_point'=>$allType_point[$key],
				);
			}else{
				$types[] = array(
					'type_value'=>$type,
					'type_num'=>0,
					'type_point'=>0,
				);
			}
		}

		$dataArray['types'] = JSON::encode($types);
		$is_success = true;
		if($id)
		{
			$paperTypeObj->setData($dataArray);
			//开始更新操作
			$where = 'id = '.$id;
			$paperTypeObj->update($where);
		}
		else
		{
			$paperTypeObj->setData($dataArray);
			$id = $paperTypeObj->add();
			$is_success = $id ? true : false;
		}

		if($is_success)
		{
	
		}
		else
		{
			$dataArray['types'] = $types;
			$dataArray['id'] = $id;
			$this->setRenderData(array('paperType' => $dataArray));
			$this->redirect('paper_type_edit',false);
			Util::showMessage('插入数据时发生错误');
		}

		$this->redirect('paper_type_list');
    }
    
	public function paper_type_list()
	{
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("exam_paper_type");
		$query->where  = ' 1  and '.$where;
		$query->order = 'id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('paper_type_list');
	}

	public function paper_type_del(){
		$ids = IReq::get('id');
		$ids = is_array($ids) ? $ids : array($ids);
		$ids = IFilter::act($ids,'int');
		if($ids)
		{
			$ids = implode(',',$ids);
			if($ids)
			{
				$exam_paper_type = new IModel('exam_paper_type');
				$where = "id in (".$ids.")";
				$exam_paper_type->del($where);
			}
		}
		$this->paper_type_list();
	}

	//获取题目列表
	public function paper_log()
	{
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("exam_paper_member_record as r");
		
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and c.level=0 and c.area='.$this->admin['area'];
		}
		
		$query->join = 'left join exam_paper as p on p.id=r.paperid left join member as m on m.user_id=r.user_id left join company as c on c.id=r.com_id';
		$query->fields = 'r.*,m.nickname,m.mobile,c.name,p.title,m.true_name';
		$query->where  = ' 1 and '.$where;
		$query->order = 'r.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('paper_log');
	}

	public function question_log(){
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		
		$join = 'left join exam_paper as p on p.id=r.paperid left join member as m on m.user_id=r.user_id left join exam_question as q on q.id=r.questionid';
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and c.level=0 and c.area='.$this->admin['area'];
			$join = $join . ' left join company as c on c.id=r.com_id';
		}
		
		$query = new IQuery("exam_paper_member_data as r");
		$query->join = $join;
		$query->fields = 'r.*,m.true_name,m.mobile,p.title,q.question,m.nickname';
		$query->where  = '1 and '.$where;
		$query->order = 'r.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('question_log');
	}

	public function paper_list(){
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("exam_paper as p");
		$query->join = 'left join exam_paper_type as t on t.id=p.tid';
		$query->fields = 'p.*,t.title as t_title';
		$query->where  = '1 and p.is_del=0 and '.$where;
		$query->order = 'p.paper_code desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('paper_list');
	}

	public function categoryGWAjax()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$cat_id = IFilter::act(IReq::get('cat_id'),'int');
		if($id && is_array($id))
		{
			foreach($id as $q_id)
			{
				//处理关系
				$hy_categoryDB = new IModel('exam_question_gw');
				$hy_categoryDB->del('q_id = '.$q_id);
				if($cat_id)
				{
					foreach($cat_id as $item)
					{
						$item = IFilter::act($item,'int');
						$hy_categoryDB->setData(array('q_id' => $q_id,'gw_id' => $item));
						$hy_categoryDB->add();
					}
				}
			}

			die(JSON::encode(array('result' => 'success')));
		}
		die(JSON::encode(array('result' => 'fail')));
	}

	public function categoryHYAjax()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$cat_id = IFilter::act(IReq::get('cat_id'),'int');
		if($id && is_array($id))
		{
			foreach($id as $q_id)
			{
				//处理关系
				$hy_categoryDB = new IModel('exam_question_hy');
				$hy_categoryDB->del('q_id = '.$q_id);
				if($cat_id)
				{
					foreach($cat_id as $item)
					{
						$item = IFilter::act($item,'int');
						$hy_categoryDB->setData(array('q_id' => $q_id,'hy_id' => $item));
						$hy_categoryDB->add();
					}
				}
			}

			die(JSON::encode(array('result' => 'success')));
		}
		die(JSON::encode(array('result' => 'fail')));
	}
	
	//导出用户统计数据
	public function question_report()
	{
		$where       = Util::search(IReq::get('search'));
		$query = new IQuery("exam_question");
		$query->where  = 'is_del = 0 and '.$where;
		$query->order = 'type asc,id desc';
		$queryList    = $query->find();

		$q_type = array(
			1=>"判断",
			2=>"单选",
			3=>"多选",
			4=>"填空",
		);
		$reportObj = new report('题目');
		$reportObj->setTitle(array("题目","题型","难度","配图","讲解","答案","题项A","题项B","题项C","题项D","题项E","题项F"));
		if($queryList){
			foreach($queryList as $key => $val){
				$type = $q_type[$val['type']];
				$insertData = array($val['question'],$type,$val['level'],$val['thumb'],$val['explain'],$val['answer']);
				if($val['type']==2||$val['type']==3){
					$items = JSON::decode($val['items']);
					if($items){
						foreach($items as $item){
							if($item['item_content']){
								if($item['item_file']){
									$insertData[] = $item['item_content'].';'.$item['item_file'];
								}else{
									$insertData[] = $item['item_content'];
								}
							}
						}
					}
				}
				$reportObj->setData($insertData);
			}
		}
		$reportObj->export(array(60,5,5,10,30,20,40,40,40,40,40));
	}

	public function importQuestion(){
		$poolid        = IFilter::act(IReq::get('poolid'),'int');
		$temp        = IFilter::act(IReq::get('temp'));
		$classFile = IWeb::$app->getBasePath().'plugins/examimport/';
        $pluginIndex = $classFile.$temp."/".$temp.".php";
        if(is_file($pluginIndex))
        {
			include_once($pluginIndex);
            $import = new $temp();
            $path = IWeb::$app->getBasePath()."runtime/_exam_import/";
            $upObj = new IUpload(10000,array($import->tempConf()['ext']));
            $upObj->setDir($path.date("Ymd"));
			$tempRes = $upObj->execute();
			$q_type = array(
				"判断"=>1,
				"单选"=>2,
				"多选"=>3,
				"填空"=>4,
			);
			if($tempRes && is_array($tempRes))
			{
				$result = current(current($tempRes));
				if($result['flag'] == 1)
				{
					$result['qFile'] = stripos($result['fileSrc'],"http") === false ? $result['fileSrc'] : IUrl::creatUrl('').$result['fileSrc'];
					$values = $import->import($result);
					$questionObj = new IModel('exam_question');
					$import_time = ITime::getDateTime();
					$row = 0;
					$num = 0;
					$errorNum = 0;
					foreach($values as $val){
						if($row>0 && isset($q_type[$val[1]])){
							$dataArray  = array(
								'question'       => $val[0],
								'type'       => $q_type[$val[1]],
								'level'       => $val[2],
								'thumb'       =>  $val[3],
								'explain'       => $val[4],
								'answer' => $val[5],
								'update_time' => $import_time,
								'create_time' => $import_time,
								'is_del' => 1,
								'poolid' => $poolid,
								'items' => '',
							);
							if($dataArray['type']==2 || $dataArray['type']==3){
								$items = array();
								$allItem = array('A','B','C','D','E','F');
								for($index=6;$index<count($val) && $index<12;$index++){
									$itemval = $val[$index];
									$itemval = strpos($itemval,';')===false?$itemval.';':$itemval;
									$itemval = explode(';',$itemval);
									$items[] = array(
										"item_content"=>$itemval[0],
										"item_value"=>$allItem[$index-6],
										"item_file"=>$itemval[1],
									);
								}
								$dataArray['items']=JSON::encode($items);
							}elseif($dataArray['type']==1){
								$dataArray['answer']=$dataArray['answer']=='正确'?'1':'0';
							}
							$questionObj->setData($dataArray);
							$id = $questionObj->add();
							if($id){
								$num++;
							}
						}elseif($row==0){
							if($val[0]!='题目'||$val[1]!='题型'||$val[2]!='难度'||$val[3]!='配图'||$val[4]!='讲解'||$val[5]!='答案'){
								die('<script type="text/javascript">parent.uploadFail("模板错误");</script>');
							}
						}else{
							$errorNum++;
						}
						$row++;
					}
					die('<script type="text/javascript">parent.uploadSuccess('.$num.','.$errorNum.');</script>');
				}
				else
				{
					$error = $result['error'];
					die('<script type="text/javascript">parent.uploadFail("'.$error.'");</script>');
				}
			}else{
				die('<script type="text/javascript">parent.uploadFail("上传失败");</script>');
			}
		}else{
			die('<script type="text/javascript">parent.uploadFail("模板不存在");</script>');
		}
	}

	public function question_temp_down(){
		$file        = IFilter::act(IReq::get('file'));
		examclass::downTemp($file);
	}


}