<?php
/**
 * @file exam.php
 */
class examclass
{
	public static function getMonthWeek($month,$date=false)
    {    
        $arr = [] ;
        foreach (examclass::get_weekinfo($month) as $k => $v)
        {
             //连接上个月的数据去掉
             if( date("m",strtotime($v[0])) == date("m",strtotime($month))  )
             {
             //    $arr[] = $v;
             }
             if(date("d",strtotime($v[1]))>date("d",strtotime($v[0]))){
                $arr[] = $v;
             }elseif($k==0 && date("d",strtotime($v[1]))>4){
                $arr[] = $v;
            }elseif($k>0 && date("d",strtotime($v[1]))<5){
                $arr[] = $v;
            }
        }

		$papers = array();
		//数据插入
		foreach ($arr as $ke => $va)
		{    
            $data = array();
			$data['week'] = $ke+1;                        //第几周
			$data['year'] = date('Y',strtotime($month));    //年
			$data['month'] = date('m',strtotime($month));//月
			$data['title'] = date('Y年-m月',strtotime($month)).'第'.$data['week'].'周';//
			$data['date_s'] = $va[0];
            $data['date_e'] = $va[1];
            if(!$date || ITime::getDiffSec($va[0],$date)<=0){
                $papers[] = $data;
            }
        }
        //月份的最后一天
      //  $lastday = date('Y-m-d', mktime(23, 59, 59, date('m', strtotime($month))+1, 00));
        $lastday = date('Y-m-d', strtotime($month.' +1 month -1 day'));
        $lastweek = end($arr);
        //不够下个月的数据补上
        $data = array();
        if( strtotime($lastday) > strtotime($lastweek[1]))
        {
            $newendarr = array( date( 'Y-m-d',strtotime($lastweek[1])+86400 ),date( 'Y-m-d',strtotime($lastweek[1])+(86400*7) ) );
            if(date('d',strtotime($newendarr[1]))<5){
                $data['week'] = count($papers)+1;                        //第几周
                $data['year'] = date('Y',strtotime($newendarr[0]));    //年
                $data['month'] = date('m',strtotime($newendarr[0]));//月
                $data['title'] = date('Y年-m月',strtotime($newendarr[0])).'第'.$data['week'].'周';//
            }else if($date && strtotime($date)<=strtotime($lastday)){
                $data['week'] = 1;                        //第几周
                $data['year'] = date('Y',strtotime($newendarr[1]));    //年
                $data['month'] = date('m',strtotime($newendarr[1]));//月
                $data['title'] = date('Y年-m月',strtotime($newendarr[1])).'第'.$data['week'].'周';//
            }
			$data['date_s'] = $newendarr[0];
            $data['date_e'] = $newendarr[1];
            if(isset($data['week'])){
                if(!$date || ITime::getDiffSec($newendarr[0],$date)<=0){
                    $papers[] = $data;
                }
            }
        }

        //倒序
        $papers = array_reverse($papers);
		return $papers;
    }
    //获取星期参数
    public static function getTimeWeek($time) 
    {
        $day = date("w",$time);
        return ($day == 0) ? '7' : $day ;
	}
	
	public static function get_weekinfo($month)
    {
        $weekinfo = array();//创建一个空数组
        $end_date = date('d',strtotime($month.' +1 month -1 day'));//计算当前月有多少天
        for ($i=1; $i <$end_date ; $i=$i+7) {   //循环本月有多少周
            $w = date('N',strtotime($month.'-'.$i));  //计算第一天是周几
            $weekinfo[] = array(date('Y-m-d',strtotime($month.'-'.$i.' -'.($w-1).' days')),date('Y-m-d',strtotime($month.'-'.$i.' +'.(7-$w).' days')));
        } 
        return $weekinfo;
    }
    public static function transQ221($questionList){
        $typemap = array(
			"判断题"=>"1",
			"单选题"=>"2",
			"多选题"=>"3",
			"填空题"=>"4",
		);
        foreach($questionList as &$val){
			$val['question'] = self::htmlTransform($val['question']);
			$val['answer'] = self::htmlTransform($val['answer']);
            $val['explain'] = self::htmlTransform($val['infos']);
            $val['thumb'] = $val['q_pic'];

			if(isset($typemap[$val['q_type']])){
				$val['type']=$typemap[$val['q_type']];
			}else{
				$val['type']="4";
			}
			if($val['type']=="1"){
				$val['answer'] = $val['answer']=='A'?'1':'0';
			}

			$val['items'] = array();
			if($val['type']=="2"||$val['type']=="3"){
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
        return $questionList;
    }
    public static function transBD21($questionList){
        foreach($questionList as &$val){
			$val['bdjson'] = self::htmlTransform($val['bdjson']);

			$val['bdjson'] = JSON::decode($val['bdjson']);
		}

        return $questionList;

    }

    public static function genQuestion2($num,$title){
        $where = '';
		$strlength = strlen($title);
        if($strlength>0){
            $where .= ' q.sub_class="'.$title.'" ';
        }
        $questionObj = new IQuery('chujikuaiji as q');
        $questionObj->where = $where;
        $questionObj->fields = 'q.*';
        $questionObj->order = 'RAND()';
        $questionObj->limit = $num;
        $questionList =  $questionObj->find();
        return self::transQ221($questionList);
    }

    public static function genQuestionBD($num,$bdcid){
        $where = '';
		$strlength = strlen($bdcid);
        if($strlength>0){
            $where .= ' q.cate_id="'.$bdcid.'" ';
        }
        $questionObj = new IQuery('bdtk2 as q');
        $questionObj->where = $where;
        $questionObj->fields = 'q.*';
        $questionObj->order = 'RAND()';
        $questionObj->limit = $num;
        $questionList =  $questionObj->find();
        return self::transBD21($questionList);
    }

    //返回随机题目
    public static function genQuestion($q_type,$num,$user_id=0,$level=10,$scope=3,$poolid='',&$poolformat=0){
        if($poolid){
            $pid = IFilter::act($poolid,'int');
            if($pid){
                $poolObj       = new IModel('exam_pool');
                $pool = $poolObj->getObj('id='.$poolid);
                $poolformat = $pool['poolformat'];
                if($pool['poolformat']==1){
                    return self::genQuestion2($num,$pool['sub_class']);
                }elseif($pool['poolformat']==2){
                    return self::genQuestionBD($num,$pool['bdcid']);
                }
            }
        }

        $hy_ids = '0';
        $gw_ids = '0';
        if($user_id){
            $user_companyDB        = new IModel('user_company');
            $companyWorkRow = $user_companyDB->getObj('user_id = '.$user_id.' and is_del=0');
            if($companyWorkRow){
                $categoryExtend = new IQuery('user_category_extend');
                $categoryExtend->where = 'work_id = '.$companyWorkRow['id'];
                $categoryExtend->fields = 'category_id';
                $cateData = $categoryExtend->find();
                if($cateData)
                {
                    $gw_category_id = array();
                    foreach($cateData as $item)
                    {
                        $gw_category_id[] = $item['category_id'];
                    }
                    $gw_ids = implode(',',$gw_category_id);
                }

                $categoryExtend = new IQuery('company_category_extend');
                $categoryExtend->where = 'com_id = '.$companyWorkRow['com_id'];
                $categoryExtend->fields = 'category_id';
                $cateData = $categoryExtend->find();
                if($cateData)
                {
                    $hy_category_id = array();
                    foreach($cateData as $item)
                    {
                        $hy_category_id[] = $item['category_id'];
                    }
                    $hy_ids = implode(',',$hy_category_id);
                }
            }
        }
        $where = '';
        if($scope==1 || $scope==3 ){
            $where .= ' and (hy.hy_id is null or hy.hy_id in ('.$hy_ids.'))';
        }
        if($scope==2 || $scope==3 ){
            $where .= ' and (gw.gw_id is null or gw.gw_id in ('.$gw_ids.'))';
        }
        $poolid    = trim($poolid);
		//获取字符串总字节数
		$strlength = strlen($poolid);
        if($strlength>0){
            $where .= ' and FIND_IN_SET(q.poolid,"'.$poolid.'")';
        }
        $questionObj = new IQuery('exam_question as q');
        $questionObj->join = 'left join exam_question_gw as gw on q.id=gw.q_id left join exam_question_hy as hy on q.id=hy.q_id';
        if($q_type){
            $where .= ' and type='.$q_type;
        }
        $questionObj->where = 'is_del=0 and level<='.$level.$where;
        $questionObj->fields = 'DISTINCT q.*';
        $questionObj->order = 'RAND()';
        $questionObj->limit = $num;
        $questionList =  $questionObj->find();
        foreach($questionList as &$val){
            $val['question'] = self::htmlTransform($val['question']);
			$val['answer'] = self::htmlTransform($val['answer']);
			$val['explain'] =self::htmlTransform($val['explain']);
			$val['items'] = JSON::decode($val['items']);
			if($val['items']){
				foreach($val['items'] as &$item){
					$item['item_content'] = self::htmlTransform($item['item_content']);
				}
			}
        }
        return $questionList;
    }

    public static function htmlTransform($string)
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

    public static function getImportPlugins(){
        $classFile = IWeb::$app->getBasePath().'plugins/examimport/';

        $dirRes = opendir($classFile);
        $importList = array();
		//遍历目录读取配置文件
		while( false !== ($dir = readdir($dirRes)) ){
            if($dir[0] == "." || $dir[0] == "_")
			{
				continue;
            }
            $pluginIndex = $classFile.$dir."/".$dir.".php";
			if(is_file($pluginIndex))
			{
                include_once($pluginIndex);
                $import = new $dir();
                $importList[] = array(
                    "name"=>$import->tempConf()['name'],
                    "file"=>$dir,
                    "ext"=>$import->tempConf()['ext'],
                );
            }
        }
        return $importList;
    }

    public static function downTemp($dir){
        $classFile = IWeb::$app->getBasePath().'plugins/examimport/';
        $pluginIndex = $classFile.$dir."/".$dir.".php";
        if(is_file($pluginIndex))
        {
            include_once($pluginIndex);
            $import = new $dir();
            header('Cache-Control: max-age=0');
            header('Content-Disposition: attachment;filename='.$import->tempConf()['filename']);
            header('Content-type: '.$import->tempConf()['ctype']);
            readfile(IWeb::$app->getBasePath().'plugins/examimport/'.$dir.'/'.$import->tempConf()['file']);
        }else{
            die('模板不存在');
        }
    }


    public static function transOneQ221($val){
        $typemap = array(
			"判断题"=>"1",
			"单选题"=>"2",
			"多选题"=>"3",
			"填空题"=>"4",
		);
			$val['question'] = self::htmlTransform($val['question']);
			$val['answer'] = self::htmlTransform($val['answer']);
			$val['explain'] = self::htmlTransform($val['infos']);
			$val['thumb'] = $val['q_pic'];
			if(isset($typemap[$val['q_type']])){
				$val['type']=$typemap[$val['q_type']];
			}else{
				$val['type']="4";
			}
			if($val['type']=="1"){
				$val['answer'] = $val['answer']=='A'?'1':'0';
			}

			$val['items'] = array();
			if($val['type']=="2"||$val['type']=="3"){
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
            return $val;
    }

    public static function sortdata($catArray, $id = 0 , $prefix = '')
	{
		static $formatCat = array();
		static $floor     = 0;

		foreach($catArray as $key => $val)
		{
			if($val['parent_id'] == $id)
			{
				$str         = self::nstr($prefix,$floor);
				$val['title'] = $str.$val['title'];

				$val['floor'] = $floor;
				$formatCat[]  = $val;

				unset($catArray[$key]);

				$floor++;
				self::sortdata($catArray, $val['id'] ,$prefix);
				$floor--;
			}
		}
		return $formatCat;
    }
    
    	//处理商品列表显示缩进
	public static function nstr($str,$num=0)
	{
		$return = '';
		for($i=0;$i<$num;$i++)
		{
			$return .= $str;
		}
		return $return;
	}
}


