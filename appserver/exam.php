<?php
/**
 * @brief 考试接口
 * @class Exam
 */
class Exam extends IApiController 
{
	public function init()
	{
		plugin::reg("onBeforeCreateAction@exam@index",function(){
            plugin::trigger('checkUserRights','index');
		});
		plugin::reg("onBeforeCreateAction@exam@examRecodList",function(){
            plugin::trigger('checkUserRights','examRecodList');
		});
		plugin::reg("onBeforeCreateAction@exam@examRecodDatas",function(){
            plugin::trigger('checkUserRights','examRecodDatas');
		});
		plugin::reg("onBeforeCreateAction@exam@submitPaper",function(){
            plugin::trigger('checkUserRights','submitPaper');
        });
		plugin::reg("onBeforeCreateAction@exam@myerror",function(){
            plugin::trigger('checkUserRights','myerror');
		});
		plugin::reg("onBeforeCreateAction@exam@question_report",function(){
            //plugin::trigger('checkUserRights','question_report');
		});
		plugin::reg("onBeforeCreateAction@tools@suggestion",function(){
            plugin::trigger('getUser','suggestion');
		});
		plugin::reg("onBeforeCreateAction@exam@myExam",function(){
            plugin::trigger('checkUserRights','myExam');
		});
		plugin::reg("onBeforeCreateAction@exam@addExam",function(){
            plugin::trigger('checkUserRights','addExam');
		});
		
		$this->versionActions = array(
			'questionPool'=>array('1.2.1','1.0','1.1'),
		);

		$this->cacheAble = true;
		$this->cacheActions = array(
			'questionPool'=>600,
			'questionPool_1_0'=>600,
			'questionPool_1_2_1'=>600,
			'questionPool_1_1'=>600,
			'genPapaer'=>600,
        );
	}

	public function index(){
		$user_id   = $this->user['user_id'];

		$exam_recodObj       = new IModel('exam_paper_member_record');
		$ex = $exam_recodObj->getObj('user_id='.$user_id,'count(id) as exam_count');
		$exam_count = $ex['exam_count'];

		$point_logObj       = new IModel('point_log');
		$ex = $point_logObj->getObj('user_id='.$user_id.' and event like "exam%"','sum(value) as exam_point');
		$exam_point = $ex['exam_point']?$ex['exam_point']:0;

        $this->setRenderData(array('exam_count' => $exam_count,'exam_point'=>$exam_point));
	}

	public function myExam(){
		$user_id   = IFilter::act($this->user['user_id'],'int');

		$paperTypeObj       = new IModel('exam_paper_type t ,exam_pool p');
		$paperTemps = $paperTypeObj->query('t.poolid=p.id and t.visibility=1 and t.user_id='.$user_id,' t.*,p.poolformat','t.id desc');
		$exam_recodObj       = new IModel('exam_paper_member_record as r,exam_paper as p');
		foreach($paperTemps as &$paperTemp){
			$types = JSON::decode($paperTemp['types']);
			$paperTemp['types'] = $types;
			$recod = $exam_recodObj->getObj('r.paperid=p.id and p.tid='.$paperTemp['id'],'max(rightnum) max_rightnum');
			$paperTemp['max_rightnum'] = '0';
			if($recod){
				$paperTemp['max_rightnum'] = $recod['max_rightnum'];
			}
		}
		return $paperTemps;
	}

	public function addExam(){
		$user_id   = $this->user['user_id'];
		$pid = IFilter::act( IReq::get('poolid'),'int');
		$title = IFilter::act( IReq::get('title'));
		$paperTypeObj       = new IModel('exam_paper_type');
		$paperType = $paperTypeObj->getObj("poolid=".$pid.' and user_id='.$user_id);
		if($paperType){
			return $paperType;
		}

		$paperType = $paperTypeObj->getObj("id=1");
		unset($paperType['id']);
		$paperType['user_id']=$user_id;
		$paperType['poolid']=$pid;
		$paperType['title']='自测：'.$title;
		$paperType['description']='';
		$paperTypeObj->setData($paperType);
		$paperTypeObj->add();
		return $paperType;
	}

	public function questionPool_1_0(){
		return $this->questionPool_1_2_1();
	}


	public function questionPool_1_2_1(){
		$pid = IFilter::act( IReq::get('pid'),'int');

		$questionPoolDB = new IModel('exam_pool');
		$where = 'visibility=1';
		$where .= ' and parent_id='.$pid.' and (bdcid is null or bdcid="")';
		
		$poolList = $questionPoolDB->query($where,'*','sort asc');
		$paperObj       = new IQuery('exam_question');
		$paperObj->group = 'poolid';
		$paperObj->fields = 'count(poolid) as num,poolid';
		$poolcount = $paperObj->find();
		$poolc = array();
		foreach($poolcount as $val){
			$poolc[$val['poolid']]=$val['num'];
		}

		$dbpaperObj       = new IQuery('bdtk2');
		$dbpaperObj->group = 'cate_id';
		$dbpaperObj->fields = 'count(cate_id) as num,cate_id';
		$dbpoolcount = $dbpaperObj->find();
		$bdpoolc = array();
		foreach($dbpoolcount as $val){
			$bdpoolc[$val['cate_id']]=$val['num'];
		}

		$erpaperObj       = new IQuery('chujikuaiji');
		$erpaperObj->group = 'sub_class';
		$erpaperObj->fields = 'count(sub_class) as num,sub_class';
		$erpoolcount = $erpaperObj->find();
		$erpoolc = array();
		foreach($erpoolcount as $val){
			$erpoolc[$val['sub_class']]=$val['num'];
		}

		foreach($poolList as &$val){
			if($val['poolformat']==1){
				$val['count']=isset($erpoolc[$val['title']])?$erpoolc[$val['title']]:0;
			}else{
				if($val['bdcid']){
					$val['count']=isset($bdpoolc[$val['bdcid']])?$bdpoolc[$val['bdcid']]:0;
				}else{
					$val['count']=isset($poolc[$val['id']])?$poolc[$val['id']]:0;
				}
			}
			$child = $questionPoolDB->query('parent_id='.$val['id']);
			if($child){
				$val['child'] = count($child);
			}else{
				$val['child'] = 0;
			}
		}
		return $poolList;
	}

	public function questionPool_1_1(){
		return $this->questionPool_1_4_2();
	}
	public function questionPool_1_4_2(){
		$pid = IFilter::act( IReq::get('pid'),'int');
	//	$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

		$questionPoolDB = new IModel('exam_pool');
		$questionPoolQuery = new IQuery('exam_pool');
		$where = 'visibility=1';
		//$where .= ' and parent_id='.$pid;
	//	$questionPoolQuery->page = $page;
		$questionPoolQuery->where = $where;
		$questionPoolQuery->order = "sort asc";
		$poolList = $questionPoolQuery->find();
	//	$paging = $questionPoolQuery->paging;

		
		$paperObj       = new IQuery('exam_question');
		$paperObj->group = 'poolid';
		$paperObj->fields = 'count(poolid) as num,poolid';
		$poolcount = $paperObj->find();
		$poolc = array();
		foreach($poolcount as $val){
			$poolc[$val['poolid']]=$val['num'];
		}

		$dbpaperObj       = new IQuery('bdtk2');
		$dbpaperObj->group = 'cate_id';
		$dbpaperObj->fields = 'count(cate_id) as num,cate_id';
		$dbpoolcount = $dbpaperObj->find();
		$bdpoolc = array();
		foreach($dbpoolcount as $val){
			$bdpoolc[$val['cate_id']]=$val['num'];
		}

		$erpaperObj       = new IQuery('chujikuaiji');
		$erpaperObj->group = 'sub_class';
		$erpaperObj->fields = 'count(sub_class) as num,sub_class';
		$erpoolcount = $erpaperObj->find();
		$erpoolc = array();
		foreach($erpoolcount as $val){
			$erpoolc[$val['sub_class']]=$val['num'];
		}

		$newlist = array();
		foreach($poolList as &$val){
			if($val['poolformat']==1){
				$val['count']=isset($erpoolc[$val['sub_class']])?$erpoolc[$val['sub_class']]:0;
			}else{
				if($val['bdcid']){
					$val['count']=isset($bdpoolc[$val['bdcid']])?$bdpoolc[$val['bdcid']]:0;
				}else{
					$val['count']=isset($poolc[$val['id']])?$poolc[$val['id']]:0;
				}
			}
			$child = $questionPoolDB->query('parent_id='.$val['id']);
			$val['child'] = 0;
			if(!$child){
				$newlist[] = $val;
 			}
		}
		$this->setRenderData(array('questionPool' => $newlist));
	}

	public function questionPool(){
		$pid = IFilter::act( IReq::get('pid'),'int');
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

		$questionPoolDB = new IModel('exam_pool');
		$questionPoolQuery = new IQuery('exam_pool');
		$where = 'visibility=1';
		$where .= ' and parent_id='.$pid;
		$questionPoolQuery->page = $page;
		$questionPoolQuery->where = $where;
		$questionPoolQuery->order = "sort asc";
		$poolList = $questionPoolQuery->find();
		$paging = $questionPoolQuery->paging;

		
		$paperObj       = new IQuery('exam_question');
		$paperObj->group = 'poolid';
		$paperObj->fields = 'count(poolid) as num,poolid';
		$poolcount = $paperObj->find();
		$poolc = array();
		foreach($poolcount as $val){
			$poolc[$val['poolid']]=$val['num'];
		}

		$dbpaperObj       = new IQuery('bdtk2');
		$dbpaperObj->group = 'cate_id';
		$dbpaperObj->fields = 'count(cate_id) as num,cate_id';
		$dbpoolcount = $dbpaperObj->find();
		$bdpoolc = array();
		foreach($dbpoolcount as $val){
			$bdpoolc[$val['cate_id']]=$val['num'];
		}

		$erpaperObj       = new IQuery('chujikuaiji');
		$erpaperObj->group = 'sub_class';
		$erpaperObj->fields = 'count(sub_class) as num,sub_class';
		$erpoolcount = $erpaperObj->find();
		$erpoolc = array();
		foreach($erpoolcount as $val){
			$erpoolc[$val['sub_class']]=$val['num'];
		}

		foreach($poolList as &$val){
			if($val['poolformat']==1){
				$val['count']=isset($erpoolc[$val['sub_class']])?$erpoolc[$val['sub_class']]:0;
			}else{
				if($val['bdcid']){
					$val['count']=isset($bdpoolc[$val['bdcid']])?$bdpoolc[$val['bdcid']]:0;
				}else{
					$val['count']=isset($poolc[$val['id']])?$poolc[$val['id']]:0;
				}
			}
			$child = $questionPoolDB->query('parent_id='.$val['id']);
			if($child){
				$val['child'] = count($child);
			}else{
				$val['child'] = 0;
			}
		}
		$this->setRenderData(array('questionPool' => $poolList,'paging'=>$paging));
	}

	public function questionList(){
		$poolid = IFilter::act( IReq::get('poolid'),'int');
		$v = IFilter::act( IReq::get('visibility'),'int');
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		//检查是否购买
		$poolObj       = new IModel('exam_pool');
		if($v){
			$pool = $poolObj->getObj('id='.$poolid);
		}else{
			$pool = $poolObj->getObj('id='.$poolid.' and visibility=1');
		}
		if(!$pool){
			IError::show(403,"题库不存在");
		}
		$needorder = false;
		$hasorder = false;
		$order = array();
		if($pool['price']>0){
			$needorder = true;
			//检查购买
			$user_id   = IFilter::act($this->user['user_id'],'int');
			if($user_id){
				$orderObj       = new IModel('order');
				$order = $orderObj->getObj('user_id='.$user_id.' and status=1 and ((order_type=1 and product_id='.$poolid.') or (order_type=3 and curdate()>=start_date and curdate()<=end_date ))');
				if($order){
					$hasorder = true;
				}
			}
		}
		if($pool['poolformat']==1){
			$return = $this->questionList2($pool['sub_class']);
			$return['needorder'] = $needorder;
			$return['hasorder'] = $hasorder;
			$return['order'] = $order;
			$this->setRenderData($return);
		}else{
			$paperObj       = new IQuery('exam_question as q');
			$paperObj->where = 'q.poolid='.$poolid;
			$paperObj->fields = 'q.*';
			$paperObj->order = 'q.id desc';
			$paperObj->page = $page;
			$questionList = $paperObj->find();
			foreach($questionList as &$val){
				$val['question'] = $this->htmlTransform($val['question']);
				$val['answer'] = $this->htmlTransform($val['answer']);
				$val['explain'] = $this->htmlTransform($val['explain']);
				$val['items'] = JSON::decode($val['items']);
				if($val['items']){
					foreach($val['items'] as &$item){
						$item['item_content'] = $this->htmlTransform($item['item_content']);
					}
				}
			}
			$paging = $paperObj->paging;
			$this->setRenderData(array('questionList' => $questionList,'paging'=>$paging,'needorder'=>$needorder,'hasorder'=>$hasorder,'order'=>$order));
		}
	}

	private function questionList2($sub_class){
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$typemap = array(
			"判断题"=>1,
			"单选题"=>2,
			"多选题"=>3,
			"填空题"=>4,
		);
		$paperObj       = new IQuery('chujikuaiji as q');
		$paperObj->where = 'q.sub_class="'.$sub_class.'"';
		$paperObj->fields = 'q.*';
		$paperObj->order = 'q.id desc';
		$paperObj->page = $page;
		$questionList = $paperObj->find();
		foreach($questionList as &$val){
			$val['question'] = $this->htmlTransform($val['question']);
			$val['answer'] = $this->htmlTransform($val['answer']);
			$val['explain'] = $this->htmlTransform($val['infos']);
			$val['thumb'] = $val['q_pic'];
			if(isset($typemap[$val['q_type']])){
				$val['type']=$typemap[$val['q_type']];
			}else{
				$val['type']=4;
			}
			if($val['type']==1){
				$val['answer'] = $val['answer']=='A'?'1':'0';
			}

			$val['items'] = array();
			if($val['type']==2||$val['type']==3){
				if($val['opta']){
					$val['items'][] = array(
						"item_content"=>$val['opta'],
						"item_value"=>'A',
						"item_file"=>'',
					);
				}
				if($val['optb']){
					$val['items'][] = array(
						"item_content"=>$val['optb'],
						"item_value"=>'B',
						"item_file"=>'',
					);
				}
				if($val['optc']){
					$val['items'][] = array(
						"item_content"=>$val['optc'],
						"item_value"=>'C',
						"item_file"=>'',
					);
				}
				if($val['optd']){
					$val['items'][] = array(
						"item_content"=>$val['optd'],
						"item_value"=>'D',
						"item_file"=>'',
					);
				}
				if($val['opte']){
					$val['items'][] = array(
						"item_content"=>$val['opte'],
						"item_value"=>'E',
						"item_file"=>'',
					);
				}
			}
			unset($val['opta']);
			unset($val['optb']);
			unset($val['optc']);
			unset($val['optd']);
			unset($val['opte']);
			unset($val['infos']);
			unset($val['q_type']);
		}
		$paging = $paperObj->paging;
		return array('questionList' => $questionList,'paging'=>$paging);
	}

	public function htmlTransform($string)
	{
		  $string = str_replace('&quot;','"',$string);
		  $string = str_replace('&amp;','&',$string);
		  $string = str_replace('amp;','',$string);
		  $string = str_replace('&lt;','<',$string);
		  $string = str_replace('&gt;','>',$string);
		  $string = str_replace('&nbsp;',' ',$string);
		  //$string = str_replace("\\", '',$string);
		  return $string;
	}

	public function myerror(){
		$user_id   = $this->user['user_id'];
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$ptype = IFilter::act( IReq::get('ptype'),'int');

		$paperObj       = new IQuery('exam_paper_member_data as r');
		$paperObj->where = 'r.user_id='.$user_id .' and r.isright=0';
	//	$paperObj->join = 'right join exam_question as q on q.id = r.questionid';
		$paperObj->fields = 'count(r.id) as count,r.*';
		$paperObj->order = 'count desc,r.id asc';
		$paperObj->group = 'r.questionid';
		$paperObj->page = $page;
		$examRecodDatasList = $paperObj->find();
		$que = new IModel("exam_question as q");
		$chuji = new IModel("chujikuaiji");
		foreach($examRecodDatasList as &$val){
			if($val['poolformat']==1){
				$queObj = $chuji->getObj('id='.$val['questionid']);
				$new = examclass::transOneQ221($queObj);
				$val['question'] = $new['question'];
				$val['thumb'] = $new['thumb'];
				$val['q_answer'] = $new['answer'];
				$val['explain'] = $new['explain'];
				$val['fansnum'] = 0;
				$val['correctnum'] = 0;
				$val['items'] = $new['items'];
			}elseif($val['poolformat']==2){

			}else{
				$queObj = $que->getObj('id='.$val['questionid'],'q.question,q.thumb,q.answer as q_answer,q.explain,q.fansnum,q.correctnum,q.items');
				$val['items'] = JSON::decode($queObj['items']);
				$val['question'] = $queObj['question'];
				$val['thumb'] = $queObj['thumb'];
				$val['q_answer'] = $queObj['answer'];
				$val['explain'] = $queObj['explain'];
				$val['fansnum'] = $queObj['fansnum'];
				$val['correctnum'] = $queObj['correctnum'];
			}		}
		$paging = $paperObj->paging;
		$this->setRenderData(array('myerror' => $examRecodDatasList,'paging'=>$paging));
	}
	public function getExamType(){
		$paperTypeObj       = new IModel('exam_paper_type');
		$paperTemps = $paperTypeObj->query('visibility=1 and user_id=0');
		$exam_recodObj       = new IModel('exam_paper_member_record as r,exam_paper as p');
		foreach($paperTemps as &$paperTemp){
			$types = JSON::decode($paperTemp['types']);
			$paperTemp['types'] = $types;
			$recod = $exam_recodObj->getObj('r.paperid=p.id and p.tid='.$paperTemp['id'],'max(rightnum) max_rightnum');
			$paperTemp['max_rightnum'] = '0';
			if($recod){
				$paperTemp['max_rightnum'] = $recod['max_rightnum'];
			}
		}
		return $paperTemps;
	}

	public function getPaperByType(){
		$paper_type = IFilter::act( IReq::get('paper_type'),'int');
		$paper_date = IFilter::act( IReq::get('paper_date'),'date');
		$paper_month = IFilter::act( IReq::get('paper_month'));
		$user_id   = IFilter::act($this->user['user_id'],'int');
		$user_company = $this->user['user_company'];

		if(!$paper_date)
		{
			$paper_date = ITime::getDateTime('Y-m-d');
		}
		$paper_time = ITime::getTime($paper_date.' 00:00:00');
		if(!$paper_month){
			$paper_month = ITime::getDateTime('Y-m',$paper_time);
		}

		if($paper_type)
		{
			//获取文章信息
			$paperTypeObj       = new IModel('exam_paper_type');
			$paperType = $paperTypeObj->getObj('id = '.$paper_type);
			if(!$paperType)
			{
				IError::show(403,"试卷类型不存在");
			}
			$papers = array();
			$types = JSON::decode($paperType['types']);
			$paperType['types'] = $types;

			//不出卷
			if($paperType['c_paper']==0){
				//查询试卷
				$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

				$paperObj       = new IQuery('exam_paper');
				$paperObj->where = 'tid='.$paper_type;
				$paperObj->page = $page;
				$papers = $paperObj->find();
        		$paging = $paperObj->paging;
				$this->setRenderData(array('papers' => $papers,'paperType'=>$paperType,'paging'=>$paging));
			}else{
				//前面十天
				if($paperType['c_priod']==1){
					for($i=0;$i<10;$i++){
						$paper_time = $paper_time - $i*24*3600;
						$paperName = ITime::getDateTime('Y年-m月-d日',$paper_time);
						$paper_date = ITime::getDateTime('Y-m-d',$paper_time);
						$pid = ITime::getDateTime('Y_m_d',$paper_time);

						$papers[] = array(
							'paper_code' => $pid.'_'.$paper_type,
							"title"=>$paperName,
							"date"=>$paper_date,
						);
					}
				}elseif($paperType['c_priod']==2){

					//前面5月,以星期三计算
					for($i=0;$i<6;$i++){
						$month_papers = examclass::getMonthWeek($paper_month,$paper_date);
						$paper_month = ITime::getDateTime('Y-m',strtotime($paper_month.' -1 month'));
						foreach($month_papers as &$paper){
							$paper['paper_code'] = $paper['year'].'_'.$paper['month'].'_'.$paper['week'].'_'.$paper_type;
							$papers[] = $paper;
						}
					}
				}elseif($paperType['c_priod']==3){
					//确定是第几周
					for($i=0;$i<10;$i++){
						$paper_time = strtotime('-'.$i.' month',$paper_time);
						$paperName = ITime::getDateTime('Y年-m月',$paper_time);
						$paper_date = ITime::getDateTime('Y-m-d',$paper_time);
						$pid = ITime::getDateTime('Y_m',$paper_time);

						$papers[] = array(
							'paper_code' => $pid.'_'.$paper_type,
							"title"=>$paperName,
							"date"=>$paper_date,
							"types"=>$types,
						);
					}
				}
			}
			
			//检查是否答题
			foreach($papers as &$paper){
				
				$paper_code = $paper['paper_code'];
				$where = 'r.paperid=p.id and r.user_id='.$user_id.' and p.paper_code="'.$paper_code.'"';
				if($paperType['fix_paper']==1){
					if($user_company && ($paperType['q_scope']==1 || $paperType['q_scope']==3)){
						$where .= ' and p.hy_md5="'.$user_company['hy_id'].'"';
					}
					if($paperType['q_scope']==2 || $paperType['q_scope']==3){
						$where .= ' and p.gw_md5="'.$this->user['gw_id'].'"';
					}
				}
				$exam_recodObj       = new IModel('exam_paper_member_record as r,exam_paper as p');
				$recod = $exam_recodObj->query($where,'r.*','r.id desc',1);
				if($recod){
					$paper['recod'] = $recod[0];
				}
			}
            $this->setRenderData(array('papers' => $papers,'paperType'=>$paperType));
		}else{
			IError::show(100000,"请传递类型");
		}
	}
	
	//获取试卷
	public function genPapaer(){
		$user_id   = IFilter::act($this->user['user_id'],'int');
		$user_company = $this->user['user_company'];
		$paper_type = IFilter::act( IReq::get('paper_type'),'int');  //试卷类型
		$paper_code = IFilter::act( IReq::get('paper_code'));  //试卷代号
		//考试记录id
		$exam_recod_id = IFilter::act( IReq::get('exam_recod_id'));   //考试编号
		$paper_name = IFilter::act( IReq::get('paper_name'));   //试卷名称

		if(!$paper_code){
			$paper_code = ITime::getDateTime('Y');
		}
		if($paper_type)
		{
			$paperTypeObj       = new IModel('exam_paper_type');
			$paperType = $paperTypeObj->getObj('id = '.$paper_type);
			if(!$paperType)
			{
				IError::show(403,"试卷类型不存在");
			}

			//$exam_recodMaxObj       = new IModel('exam_paper_member_record as r,exam_paper as p');
		//	$recod = $exam_recodMaxObj->getObj('r.paperid=p.id and p.tid='.$paper_type,'max(rightnum) max_rightnum');
			$paperType['max_rightnum'] = '0';
		//	if($recod){
		//		$paperType['max_rightnum'] = $recod['max_rightnum'];
		//	}
			
			$types = JSON::decode($paperType['types']);
			$questionList = array();
			
			//如果要保存试卷，直接查已存在的
			$where = 'tid='.$paper_type.' and paper_code="'.$paper_code.'"';
			if($paperType['fix_paper']==1){
				if($user_company && ($paperType['q_scope']==1 || $paperType['q_scope']==3)){
					$where .= ' and hy_md5="'.$user_company['hy_id'].'"';
				}
				if($paperType['q_scope']==2 || $paperType['q_scope']==3){
					$where .= ' and gw_md5="'.$this->user['gw_id'].'"';
				}
			}
			$exam_paperObj       = new IModel('exam_paper');
			$paper = $exam_paperObj->getObj($where);
			$needGenQu = true;
			if($paper){
				//固定题目
				if($paperType['fix_paper']==1){
					$exam_paper_questionObj       = new IModel('exam_paper_question as eq,exam_question as q');
					$questionList = $exam_paper_questionObj->query("eq.paperid=".$paper['id'].' and eq.questionid=q.id','q.*','eq.displayorder asc');
					foreach($questionList as &$val){
						$val['items'] = JSON::decode($val['items']);
					}
					$paperType['questionList'] = $questionList;
					$paperType['paper'] = $paper;
					if($questionList){
						$needGenQu = false;
					}
				}
				//获取试卷记录，继续考试。限时试卷
				if($exam_recod_id){
					$exam_recodObj       = new IModel('exam_paper_member_record');
					$recod = $exam_recodObj->getObj('id='.$exam_recod_id);
					if($recod){
						$paperType['recod'] = $recod;
					}
				}
				$exam_paperObj->setData(array(
					'viewnum'=>'viewnum+1',
				));
				$exam_paperObj->update('id='.$paper['id'],'viewnum');
			}
			$level = $paperType['level'];
			$q_scope = $paperType['q_scope'];
			$poolformat=0;
			if($needGenQu){
				//生成题目
				foreach($types as $key=>$val){
					if($val['type_num']>0){
						//获取试题
						$questions = examclass::genQuestion($val['type_value'],$val['type_num'],$user_id,$level,$q_scope,$paperType['poolid'],$poolformat);
						foreach($questions as $question){
							$questionList[] = $question;
						}
					}
				}
			}
			if(!$paper){
				//记录试卷
				$paper = array(
					'title'=>$paper_name,
					'level'=>$level,
					'score'=>$paperType['score'],
					'times'=>$paperType['times'],
					'paper_code'=>$paper_code,
					'tid'=>$paper_type,
					'viewnum'=>1,
					'year'=>ITime::getDateTime('Y'),
					'update_time'=>ITime::getDateTime(),
					'create_time'=>ITime::getDateTime(),
				);
				if($paperType['fix_paper']==1){
					if($user_company && ($paperType['q_scope']==1 || $paperType['q_scope']==3)){
						$paper['hy_md5'] = $user_company['hy_id'];
					}
					if($paperType['q_scope']==2 || $paperType['q_scope']==3){
						$paper['gw_md5'] = $this->user['gw_id'];
					}
				}
				$exam_paperObj->setData($paper);
				$paperid = $exam_paperObj->add();
				$paper['id'] = $paperid;
			}

			//记录题目
			if($paperType['fix_paper']==1 && $needGenQu){
				$exam_paper_questionObj       = new IModel('exam_paper_question');
				foreach($questionList as $index=>$val){
					$exam_paper_questionObj->setData(array(
						'questionid'=>$val['id'],
						'displayorder'=>$index,
						'paperid'=>$paper['id'],
					));
					$exam_paper_questionObj->add();
				}
			}

			$paperType['paper'] = $paper;
			$paperType['questionList'] = $questionList;
			$paperType['types'] = $types;
			$paperType['poolformat'] = $poolformat;

			$cacheObj = new ICache('redis','exam');
			$cacheObj->set($user_id.'_'.$paper['id'],$paper_type.'_'.$paper_code.'_'.$paper_name,600);

            $this->setRenderData(array('paperType' => $paperType));
		}else{
			IError::show(100000,"请传递类型");
		}
	}

	public function submitPaper(){
		$user_id   = IFilter::act($this->user['user_id'],'int');
		$paper_id = IFilter::act( IReq::get('paper_id'),'int');
		$record_id = IFilter::act( IReq::get('record_id'),'int');
		$times = IFilter::act( IReq::get('times'),'int');
		$totalnum = IFilter::act( IReq::get('totalnum'),'int');
		$rightnum = IFilter::act( IReq::get('rightnum'),'int');
		$totaltimes = IFilter::act( IReq::get('totaltimes'),'int');
		$finish = IFilter::act( IReq::get('finish'),'int');
		$donum = IFilter::act( IReq::get('donum'),'int');
		
		$q_ids = IFilter::act( IReq::get('q_id'),'int');
		$q_answers = IFilter::act( IReq::get('q_answer'));
		$q_times = IFilter::act( IReq::get('q_times'));
		$q_isrights = IFilter::act( IReq::get('q_isright'));
		$q_types = IFilter::act( IReq::get('q_type'));
		$poolformat = IFilter::act( IReq::get('poolformat'));
		
		$cacheObj = new ICache('redis','exam');
		if(!$cacheObj->get($user_id.'_'.$paper_id)){
			IError::show(100008,"考试已提交或考试超时");
		}

		$user_company = $this->user['user_company'];
		if($user_company){
			$com_id = $user_company['com_id'];
			$work_id = $user_company['id'];
		}else{
			$com_id = 0;
			$work_id = 0;
		}

		$exam_paperObj       = new IModel('exam_paper');
		$paper = $exam_paperObj->getObj('id='.$paper_id);
		if($paper ){
			$exam_paperObj->setData(array(
				'fansnum'=>'fansnum+1'
			));
			$exam_paperObj->update('id='.$paper_id,'fansnum');

			$r_time = ITime::getDateTime();
			$examRecodData = array(
				'user_id'=>$user_id,
				'paperid'=>$paper_id,
				'times'=>$times,
				'totalnum'=>$totalnum,
				'rightnum'=>$rightnum,
				'donum'=>$donum,
				'totaltimes'=>$totaltimes,
				'did'=>$finish,
				'com_id'=>$com_id,
				'work_id'=>$work_id,
				'end_time'=>ITime::getDateTime(),
			);

			//获取数据类型,获取分值
			$paperTypeObj       = new IModel('exam_paper_type');
			$paperType = $paperTypeObj->getObj('id = '.$paper['tid']);
			if(!$paperType){
				IError::show(100000,"考试类型不存在");
			}
			//不限时长考试不能提交一半，
			if($paperType['times']==0 || $times>=$totaltimes || $donum>=$totalnum){
				$finish=1;
				$examRecodData['did'] = 1;
			}
			$types = JSON::decode($paperType['types']);
			$key_types = array();
			foreach($types as $val){
				$key_types[$val['type_value']] = $val;
			}
			if($key_types[0]['type_num']>0){
				$key_types[1] = $key_types[0];
				$key_types[2] = $key_types[0];
				$key_types[3] = $key_types[0];
				$key_types[4] = $key_types[0];
			}

			//计算分数
			$exam_recodScoreObj       = new IModel('exam_paper_member_data as d,exam_paper as p');
			$where = 'd.user_id='.$user_id.' and d.paperid=p.id and p.tid='.$paperType['id'];
			$priedtype = $paperType['score_priod'];
			
			if($priedtype==1){
				$where .= " and DATE_FORMAT(d.create_time,'%Y%m%d')=DATE_FORMAT(NOW(),'%Y%m%d')";
			}elseif ($priedtype==2) {
				$where .= " and DATE_FORMAT(d.create_time,'%x%v')=DATE_FORMAT(NOW(),'%x%v')";
			}elseif ($priedtype==3) {
				$where .= " and DATE_FORMAT(d.create_time,'%Y%m')=DATE_FORMAT(NOW(),'%Y%m')";
			}
			//本次得分
			$total_score = 0;
			$exam_recod_item = array();
			$recodNum = 0;
			if($record_id){
				$exam_recodDataObj       = new IModel('exam_paper_member_data');
				$recodNumRow = $exam_recodDataObj->getObj('recordid='.$record_id,'count(id) as recodNum');
				$recodNum = intval($recodNumRow['recodNum']);
			}

			//周期内积分上限,已得分
			$allScore = 0;
			$allScoreRow = $exam_recodScoreObj->getObj($where,'sum(d.score) as allScore');
			if($allScoreRow){
				$allScore = floatval($allScoreRow['allScore']);
			}
			$remainScore = floatval($paperType['maxScore'])-$allScore;
			foreach($q_ids as $index=>$val){
				$_where = $where .' and questionid='.$val .' and poolformat='.$poolformat;
				$hasAnswer = $exam_recodScoreObj->getObj($_where,'d.*');
				$score = 0;
				if($q_isrights[$index] && !$hasAnswer && ($total_score<$remainScore || $paperType['maxScore']==0) ){
					$total_score += $key_types[$q_types[$index]]['type_point'];
					$score = $key_types[$q_types[$index]]['type_point'];
				}
				$exam_recod_item[] = array(
					'user_id'=>$user_id,
					'paperid'=>$paper_id,
					'recordid'=>$record_id,
					'questionid'=>$val,
					'answer'=>$q_answers[$index],
					'times'=>$q_times[$index],
					'isright'=>$q_isrights[$index],
					'type'=>$q_types[$index],
					'create_time'=>$r_time,
					'pageid'=>$recodNum + $index,
					'score'=>$score,
					'com_id'=>$com_id,
					'work_id'=>$work_id,
					'poolformat'=>$poolformat,
				);
				//触发外部积分规则
			}

			//记录考试
			$exam_recodObj       = new IModel('exam_paper_member_record');
			if($record_id){
				$examRecodData['score'] = 'score+'.$total_score;
				$exam_recodObj->setData($examRecodData);
				$exam_recodObj->update('id='.$record_id,array('score'));
			}else{
				$examRecodData['create_time'] = $r_time;
				$examRecodData['score'] = $total_score;
				$exam_recodObj->setData($examRecodData);
				$record_id = $exam_recodObj->add();
			}
			$examRecodData['id'] = $record_id;
			$exam_questionObj       = new IModel('exam_question');

			$exam_recodDataObj       = new IModel('exam_paper_member_data');
			foreach($exam_recod_item as $item){
				$item['recordid'] = $record_id;
				$exam_recodDataObj->setData($item);
				$exam_recodDataObj->add();
				if(!$poolformat){
					$exam_questionObj->setData(
						array('fansnum'=>'fansnum+1','correctnum'=>'correctnum+'.$item['isright'])
					);
					$exam_questionObj->update('id='.$item['questionid'],array('fansnum','correctnum'));
				}
			}
			if($finish){
				//记录系统积分
				$pointConfig = array(
					'user_id' => $user_id,
					'point'   => $examRecodData['score'],
					'event'   => 'exam_'.$paperType['id'],
					'event_id'=> $record_id,
					'log'     => $paper['title'].'积分：'.$examRecodData['score'],
				);
				$pointObj = new Point();
				$pointObj->update($pointConfig);
			}
			$this->setRenderData(array('paper' => $paper,'examRecodResult'=>$examRecodData));
		}else{
			IError::show(100000,"考试不存在");
		}
	}

	//开始考试
	//暂停考试

	public function examRecodList(){
		$user_id   = $this->user['user_id'];

		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

		$paperObj       = new IQuery('exam_paper_member_record as r');
		$paperObj->where = 'did=1 and user_id='.$user_id;
		$paperObj->fields = 'r.*,p.title';
		$paperObj->order = 'r.id desc';
		$paperObj->join = 'left join exam_paper as p on p.id = r.paperid';
		$paperObj->page = $page;
		$examRecodList = $paperObj->find();
		$paging = $paperObj->paging;
		$this->setRenderData(array('examRecodList' => $examRecodList,'paging'=>$paging));
	} 

	public function examRecodDatas(){
		$user_id   = $this->user['user_id'];
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$record_id = IFilter::act(IReq::get('record_id'),'int');

		$paperObj       = new IQuery('exam_paper_member_data as r');
		$paperObj->where = 'r.user_id='.$user_id .' and r.recordid='.$record_id ;
		//$paperObj->join = 'left join exam_question as q on q.id = r.questionid';
		//$paperObj->fields = 'r.*,q.question,q.thumb,q.answer as q_answer,q.explain,q.fansnum,q.correctnum,q.items';
		$paperObj->order = 'r.pageid asc,r.id asc';
		$paperObj->page = $page;
		$examRecodDatasList = $paperObj->find();
		$que = new IModel("exam_question as q");
		$chuji = new IModel("chujikuaiji");
		foreach($examRecodDatasList as &$val){
			if($val['poolformat']==1){
				$queObj = $chuji->getObj('id='.$val['questionid']);
				$new = examclass::transOneQ221($queObj);
				$val['question'] = $new['question'];
				$val['thumb'] = $new['thumb'];
				$val['q_answer'] = $new['answer'];
				$val['explain'] = $new['explain'];
				$val['fansnum'] = 0;
				$val['correctnum'] = 0;
				$val['items'] = $new['items'];
			}elseif($val['poolformat']==2){

			}else{
				$queObj = $que->getObj('id='.$val['questionid'],'q.question,q.thumb,q.answer as q_answer,q.explain,q.fansnum,q.correctnum,q.items');
				$val['items'] = JSON::decode($queObj['items']);
				$val['question'] = $queObj['question'];
				$val['thumb'] = $queObj['thumb'];
				$val['q_answer'] = $queObj['answer'];
				$val['explain'] = $queObj['explain'];
				$val['fansnum'] = $queObj['fansnum'];
				$val['correctnum'] = $queObj['correctnum'];
			}
		}
		$paging = $paperObj->paging;
		$this->setRenderData(array('examRecodDatasList' => $examRecodDatasList,'paging'=>$paging));
	} 


		//导出用户统计数据
		public function question_report()
		{
			$poolid = IFilter::act( IReq::get('poolid'),'int');
			$v = IFilter::act( IReq::get('visibility'),'int');
			//检查是否购买
			$poolObj       = new IModel('exam_pool');
			if($v){
				$pool = $poolObj->getObj('id='.$poolid);
			}else{
				$pool = $poolObj->getObj('id='.$poolid.' and visibility=1');
			}
			if(!$pool){
				IError::show(403,"题库不存在");
			}

			if($pool['bdcid']){
				$paperObj       = new IQuery('bdtk2 as q');
				$paperObj->where = 'q.cate_id="'.$pool['bdcid'].'"';
				$paperObj->order = 'id desc';
				$paperObj->limit = 40;
				$queryList    = $paperObj->find();
				$reportObj = new report($pool['title']);
				$reportObj->exportBDWord($queryList);
				exit;
			}
			$rtype = IFilter::act(IReq::get('type'));

			if($pool['poolformat']==1){
				$this->report2($pool['sub_class'],$rtype);
				exit;
			}

			$user_id   = IFilter::act($this->user['user_id'],'int');
			$query = new IQuery("exam_question");
			$query->where  = 'is_del = 0 and poolid='.$poolid;
			$query->order = 'id desc';
			$queryList    = $query->find();
	
	
			$q_type = array(
				1=>"判断",
				2=>"单选",
				3=>"多选",
				4=>"填空",
			);
			$reportObj = new report($pool['title']);
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
			if($rtype=='word'){
				$reportObj->exportWord();
			}elseif($rtype=='excle'){
				$reportObj->export(array(60,5,5,10,30,20,40,40,40,40,40));
			}elseif($rtype=='html'){
				$reportObj->toDownload();
			}else{
				$reportObj->exportText();
			}
			exit;
	}

	private function report2($sub_class,$rtype){
		$paperObj       = new IQuery('chujikuaiji as q');
		$paperObj->where = 'q.sub_class="'.$sub_class.'"';
		$paperObj->fields = 'q.*';
		$paperObj->order = 'q.id desc';
		$questionList = $paperObj->find();
		$reportObj = new report($sub_class);
		$reportObj->setTitle(array("题目","题型","难度","配图","讲解","答案","题项A","题项B","题项C","题项D","题项E","题项F"));
		foreach($questionList as &$val){
			$val['question'] = $this->htmlTransform($val['question']);
			$val['answer'] = $this->htmlTransform($val['answer']);
			$val['explain'] = $this->htmlTransform($val['infos']);
			$insertData = array($val['question'],$val['q_type'],0,$val['q_pic'],$val['explain'],$val['answer']);

			$insertData[] = $val['opta'];
			$insertData[] = $val['optb'];
			$insertData[] = $val['optc'];
			$insertData[] = $val['optd'];
			$insertData[] = $val['opte'];
			$reportObj->setData($insertData);
		}
		if($rtype=='word'){
			$reportObj->exportWord();
		}elseif($rtype=='excle'){
			$reportObj->export(array(60,5,5,10,30,20,40,40,40,40,40));
		}elseif($rtype=='html'){
			$reportObj->toDownload();
		}else{
			$reportObj->exportText();
		}
		exit;
	}

	public function bdquestionList(){
		$poolid = IFilter::act( IReq::get('poolid'),'int');
		$bdcid = IFilter::act( IReq::get('bdcid'));
		$v = IFilter::act( IReq::get('visibility'),'int');
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		//检查是否购买
		$poolObj       = new IModel('exam_pool');
		if($v){
			$pool = $poolObj->getObj('id='.$poolid);
		}else{
			$pool = $poolObj->getObj('id='.$poolid.' and visibility=1');
		}
		if(!$pool){
			IError::show(403,"题库不存在");
		}
		$needorder = false;
		$hasorder = false;
		$order = array();
		if($pool['price']>0){
			$needorder = true;
			//检查购买
			$user_id   = IFilter::act($this->user['user_id'],'int');
			if($user_id){
				$orderObj       = new IModel('order');
				$order = $orderObj->getObj('user_id='.$user_id.' and status=1 and ((order_type=1 and product_id='.$poolid.') or (order_type=3 and curdate()>=start_date and curdate()<=end_date ))');
				if($order){
					$hasorder = true;
				}
			}
		}

		$paperObj       = new IQuery('bdtk2 as q');
		$paperObj->where = 'q.cate_id="'.$bdcid.'"';
		$paperObj->fields = 'q.*';
		$paperObj->order = 'q.id desc';
		$paperObj->page = $page;
		$paperObj->pagesize = 10;
		$questionList = $paperObj->find();
		foreach($questionList as &$val){
			$val['bdjson'] = $this->htmlTransform($val['bdjson']);

			$val['bdjson'] = JSON::decode($val['bdjson']);
		}
		$paging = $paperObj->paging;
		$this->setRenderData(array('bdquestionList' => $questionList,'paging'=>$paging,'needorder'=>$needorder,'hasorder'=>$hasorder,'order'=>$order));
	}
}