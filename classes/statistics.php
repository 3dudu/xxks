<?php
/**
 * @copyright (c) 2014 aircheng
 * @file statistics.php
 * @brief 统计分析类库
 * @author nswe
 * @date 2014/7/27 11:09:43
 * @version 1.0.0
 */
class statistics
{
	//日期单位
	public static $dateUnit = '';

	//日期格式
	public static $format = 'Y-m-d';

	/**
	 * @brief 日期的智能处理
	 * @param string $start 开始日期 Y-m-d
	 * @param string $end   结束日期 Y-m-d
	 */
	public static function dateParse($start = '',$end = '')
	{
		//默认没有时间条件,查询之前7天的数据
		if(!$start && !$end)
		{
			$diffSec = 86400 * 30;
			$beforeDate = ITime::pass(-$diffSec,self::$format);
			return array($beforeDate,ITime::getNow(self::$format));
		}

		//有时间条件
		if($start && $end)
		{
			return array($start,$end);
		}
		else if($start)
		{
			return array($start,$start);
		}
		else if($end)
		{
			return array($end,$end);
		}
	}

	/**
	 * @brief 根据条件分组
	 * @param int 相差的秒数
	 * @return string y年,m月,d日
	 */
	private static function groupByCondition($diffSec)
	{
		$diffSec = abs($diffSec);
		//按天分组，小于30个天
		if($diffSec <= 86400 * 30)
		{
			return 'd';
		}
		//按月分组，小于24个月
		else if($diffSec <= 86400 * 30 * 24)
		{
			return 'm';
		}
		//按年分组
		else
		{
			return 'y';
		}
	}

	/**
	 * @brief 处理条件
	 * @param IQuery $db 数据库IQuery对象
	 * @param string $timeCols 时间字段名称
	 * @param string $start 开始日期 Y-m-d
	 * @param string $end   结束日期 Y-m-d
	 */
	private static function ParseCondition($db,$timeCols = 'time',$start = '',$end = '',$group='')
	{
		$result     = array();

		//获取时间段
		$date       = self::dateParse($start,$end);
		$startArray = explode('-',$date[0]);
		$endArray   = explode('-',$date[1]);
		$diffSec    = ITime::getDiffSec($date[0],$date[1]);
		$groups = '';
		switch(self::groupByCondition($diffSec))
		{
			//按照年
			case "y":
			{
				$startCondition = $startArray[0];
				$endCondition   = $endArray[0]+1;
				$db->fields .= ',DATE_FORMAT('.$timeCols.',"%Y") as xValue';
				$groups   = "DATE_FORMAT(".$timeCols.",'%Y') having xValue >= '{$startCondition}' and xValue < '{$endCondition}'";
			}
			break;

			//按照月
			case "m":
			{
				$startCondition = $startArray[0].'-'.$startArray[1];
				$endCondition   = $endArray[1] == 12 ? ($endArray[0]+1) : $endArray[0].'-'.($endArray[1]+1);
				$db->fields .= ',DATE_FORMAT('.$timeCols.',"%Y-%m") as xValue';
				$groups   = "DATE_FORMAT(".$timeCols.",'%Y-%m') having xValue >= '{$startCondition}' and xValue < '{$endCondition}'";
			}
			break;

			//按照日
			case "d":
			{
				$startCondition = $startArray[0].'-'.$startArray[1].'-'.$startArray[2];
				$endCondition   = $endArray[0].'-'.$endArray[1].'-'.$endArray[2].' 23:59:59';
				$db->fields .= ',DATE_FORMAT('.$timeCols.',"%Y-%m-%d") as xValue';
				$groups   = "DATE_FORMAT(".$timeCols. ",'%Y-%m-%d') having xValue >= '{$startCondition}' and xValue < '{$endCondition}'";
			}
			break;
		}
		if($group){
			$db->fields .= ','.$group;
			$groups = $group.','.$groups;
		}else{
		}
		$db->group = $groups;
		$data = $db->find();
		foreach($data as $key => $val)
		{
			if($group){
				$cur_group = $val['xValue'];
				if(!isset($result[$cur_group])){
					$result[$cur_group] = array();
				}
				$result[$cur_group][$val['topGroup']] = intval($val['yValue']);
			}else{
				$result[$val['xValue']] = intval($val['yValue']);
			}
		}
		return $result;
	}

	private static function getAreas($area=''){
		$areas  = array(
			"全市"=>0,
			"市辖区"=>321301,
			"宿城区"=>321302,
			"宿豫区"=>321311,
			"沭阳县"=>321322,
			"泗阳县"=>321323,
			"泗洪县"=>321324,
		);
		foreach($areas as $key=>$val){
			if($area && $val!=$area){
				unset($areas[$key]);
			}
		}
		return $areas;
	}

	private static function fillTimeData($areas,$dates){
		foreach($areas as $key=>$val){
			$newDatas = array();
			foreach($dates as $date){
				if(!array_key_exists($date,$val)){
					$newDatas[$date] = 0;
				}else{
					$newDatas[$date] = $val[$date];
				}
			}
			$areas[$key] = $newDatas;
		}
		return $areas;
	}

	public static function examPointByArea($start = '',$end = '',$area='')
	{
		$areas  = self::getAreas($area);

		$dates = array();
		foreach($areas as &$val){
			$db = new IQuery('point_log as r');
			$db->fields = 'sum(r.value) as yValue,r.datetime ';
			$db->where  = 'r.event like "exam%" and (c.area='.$val.' or 0='.$val.')';
			$db->join  = 'left join company as c on c.id=r.com_id';
			$val = self::ParseCondition($db,'r.datetime',$start,$end);
			$dates = array_merge($dates,$val);
			//规整时间
		}
		$dates = array_keys($dates);

		return self::fillTimeData($areas,$dates);
	}

	public static function studypointByArea($start = '',$end = '',$area='')
	{
		$areas  = self::getAreas($area);

		$dates = array();
		foreach($areas as &$val){
			$db = new IQuery('point_log as l');
			$db->fields = 'sum(l.value) as yValue,`datetime`';
			$db->where  = 'l.event_id = 1 and (c.area='.$val.' or 0='.$val.')';
			$db->join  = 'left join company as c on c.id=l.com_id';
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$dates = array_merge($dates,$val);
			//规整时间
		}
		$dates = array_keys($dates);

		return self::fillTimeData($areas,$dates);
	}


	public static function allpointByArea($start = '',$end = '',$area='')
	{
		$areas  = self::getAreas($area);

		$dates = array();
		foreach($areas as &$val){
			$db = new IQuery('point_log as l');
			$db->fields = 'sum(l.value) as yValue,`datetime`';
			$db->where  = '(c.area='.$val.' or 0='.$val.')';
			$db->join  = 'left join company as c on c.id=l.com_id';
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$dates = array_merge($dates,$val);
			//规整时间
		}
		$dates = array_keys($dates);

		return self::fillTimeData($areas,$dates);
	}

	public static function userByArea($start = '',$end = '',$area='')
	{
		$areas  = self::getAreas($area);

		$dates = array();
		foreach($areas as &$val){
			$db = new IQuery('user_company as l');
			$db->fields = 'count(l.id) as yValue,l.create_time';
			$db->where  = 'l.is_del=0 and (c.area='.$val.' or 0='.$val.')';
			$db->join  = 'left join company as c on c.id=l.com_id';
			$val = self::ParseCondition($db,'l.create_time',$start,$end);
			$dates = array_merge($dates,$val);
			//规整时间
		}
		$dates = array_keys($dates);

		return self::fillTimeData($areas,$dates);
	}

	public static function pointByCompany($com_id,$start = '',$end = '')
	{
		$areas  = array();
		$dates = array();
			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'com_id='.$com_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['汇总'] = $val;
			$dates = array_merge($dates,$val);

			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'event_id=1 and com_id='.$com_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['学习'] = $val;
			$dates = array_merge($dates,$val);

			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'event like "exam%" and com_id='.$com_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['考试'] = $val;
			$dates = array_merge($dates,$val);

			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'event_id!=1 and event like "event%" and com_id='.$com_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['其他'] = $val;
			$dates = array_merge($dates,$val);

			$dates = array_keys($dates);

			return self::fillTimeData($areas,$dates);
	}

	public static function pointByUser($user_id,$start = '',$end = '')
	{
		$areas  = array();
		$dates = array();
			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'user_id='.$user_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['汇总'] = $val;
			$dates = array_merge($dates,$val);

			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'event_id=1 and user_id='.$user_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['学习'] = $val;
			$dates = array_merge($dates,$val);

			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'event like "exam%" and user_id='.$user_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['考试'] = $val;
			$dates = array_merge($dates,$val);

			$db = new IQuery('point_log');
			$db->fields = 'sum(value) as yValue,`datetime`';
			$db->where  = 'event_id!=1 and event like "event%" and user_id='.$user_id;
			$val = self::ParseCondition($db,'datetime',$start,$end);
			$areas['其他'] = $val;
			$dates = array_merge($dates,$val);

			$dates = array_keys($dates);

			return self::fillTimeData($areas,$dates);
	}

	public static function companyPointPie($start = '',$end = '',$area='')
	{
		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(c.name,"未知公司") as topGroup';
		$db->join  = 'left join company as c on c.id=l.com_id';
		$db->order  = 'l.datetime asc';
		if($area){
			$db->where = 'c.area="'.$area.'"';
		}
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.com_id');
		return $val;
	}


	public static function company_point_detail($start = '',$end = '',$area='')
	{
		$dataGroup  = array();
		$dates = array();
		$companys = array();

		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(c.name,"未知公司") as topGroup';
		$db->where = 'l.event_id=1';
		$db->join  = 'left join company as c on c.id=l.com_id';
		$db->order  = 'l.datetime asc';
		if($area){
			$db->where = 'c.area="'.$area.'"';
		}
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.com_id');
		foreach($val as $date=>$com){
			foreach($com as $com_name=>$com_value){
				$dataGroup[$date][$com_name]['学习']=$com_value;
			}
			$companys = array_merge($companys,$com);
		}

		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(c.name,"未知公司") as topGroup';
		$db->where = 'l.event like "exam%"';
		$db->join  = 'left join company as c on c.id=l.com_id';
		$db->order  = 'l.datetime asc';
		if($area){
			$db->where = 'c.area="'.$area.'"';
		}
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.com_id');
		foreach($val as $date=>&$com){
			foreach($com as $com_name=>$com_value){
				$dataGroup[$date][$com_name]['考试']=$com_value;
			}
			$companys = array_merge($companys,$com);
		}

		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(c.name,"未知公司") as topGroup';
		$db->where = 'l.event_id!=1 and l.event like "event%" ';
		$db->join  = 'left join company as c on c.id=l.com_id';
		$db->order  = 'l.datetime asc';
		if($area){
			$db->where = 'c.area="'.$area.'"';
		}
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.com_id');
		foreach($val as $date=>&$com){
			foreach($com as $com_name=>$com_value){
				$dataGroup[$date][$com_name]['其他']=$com_value;
			}
			$companys = array_merge($companys,$com);
		}

		$dates = array_keys($dataGroup);
		sort($dates);
		$companys = array_keys($companys);
		sort($companys);

		$resultData = array();
		foreach($dates as $date){
			$dateDatas = $dataGroup[$date];
			$new_com_data = array();
			foreach($companys as $com_name){
				if(!array_key_exists($com_name,$dateDatas)){
					$new_com_data[$com_name]=array(
						'学习'=>0,
						'考试'=>0,
						'其他'=>0,
					);
				}else{
					$new_com_data[$com_name] = array_merge(array(
						'学习'=>0,
						'考试'=>0,
						'其他'=>0,
						),$dateDatas[$com_name]
					);
				}
			}
			$resultData[$date] = $new_com_data;
		}
		return $resultData;
	}

	public static function user_point_detail($start = '',$end = '',$com_id='')
	{
		$dataGroup  = array();
		$dates = array();
		$companys = array();

		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(m.true_name,"未知员工") as topGroup';
		$db->where = 'l.event_id=1 and l.com_id='.$com_id;
		$db->join  = 'left join member as m on l.user_id=m.user_id';
		$db->order  = 'l.datetime asc';
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.user_id');
		foreach($val as $date=>$com){
			foreach($com as $com_name=>$com_value){
				$dataGroup[$date][$com_name]['学习']=$com_value;
			}
			$companys = array_merge($companys,$com);
		}

		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(m.true_name,"未知员工") as topGroup';
		$db->where = 'l.event like "exam%" and l.com_id='.$com_id;
		$db->join  = 'left join member as m on l.user_id=m.user_id ';
		$db->order  = 'l.datetime asc';
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.user_id');
		foreach($val as $date=>&$com){
			foreach($com as $com_name=>$com_value){
				$dataGroup[$date][$com_name]['考试']=$com_value;
			}
			$companys = array_merge($companys,$com);
		}

		$db = new IQuery('point_log as l');
		$db->fields = 'sum(l.value) as yValue,l.datetime,IFNULL(m.true_name,"未知员工") as topGroup';
		$db->where = 'l.event_id!=1 and l.event like "event%" and l.com_id='.$com_id;
		$db->join  = 'left join member as m on l.user_id=m.user_id';
		$db->order  = 'l.datetime asc';
		$val = self::ParseCondition($db,'l.datetime',$start,$end,'l.user_id');
		foreach($val as $date=>&$com){
			foreach($com as $com_name=>$com_value){
				$dataGroup[$date][$com_name]['其他']=$com_value;
			}
			$companys = array_merge($companys,$com);
		}

		$dates = array_keys($dataGroup);
		sort($dates);
		$companys = array_keys($companys);
		sort($companys);

		$resultData = array();
		foreach($dates as $date){
			$dateDatas = $dataGroup[$date];
			$new_com_data = array();
			foreach($companys as $com_name){
				if(!array_key_exists($com_name,$dateDatas)){
					$new_com_data[$com_name]=array(
						'学习'=>0,
						'考试'=>0,
						'其他'=>0,
					);
				}else{
					$new_com_data[$com_name] = array_merge(array(
						'学习'=>0,
						'考试'=>0,
						'其他'=>0,
						),$dateDatas[$com_name]
					);
				}
			}
			$resultData[$date] = $new_com_data;
		}
		return $resultData;
	}
}