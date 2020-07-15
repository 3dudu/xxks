<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file companys_class.php
 * @brief 企业管理类库
 * @author nswe
 * @date 2014/8/18 11:53:43
 * @version 2.6
 */
class company_class
{
	//算账类库
	private static $countsumInstance = null;

	//商户ID
	public $com_id = '';

	//构造函数
	public function __construct($com_id = '')
	{
		$this->com_id = $com_id;
	}
/**
	 * @brief 获取子分类可以无限递归获取子分类
	 * @param int $catId 分类ID
	 * @param int $level 层级数
	 * @return string 所有分类的ID拼接字符串
	 */
	public static function hycatChild($catId,$level = 1)
	{
		if($level == 0)
		{
			return $catId;
		}

		$temp   = array();
		$result = array($catId);
		$catDB  = new IModel('hy_category');

		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$temp = $catDB->query('parent_id = '.$id);
			foreach($temp as $key => $val)
			{
				if(!in_array($val['id'],$result))
				{
					$result[] = $val['id'];
				}
			}
			next($result);
		}
		return join(',',$result);
	}
	
	/**
	 * @brief 获取子分类可以无限递归获取子分类
	 * @param int $catId 分类ID
	 * @param int $level 层级数
	 * @return string 所有分类的ID拼接字符串
	 */
	public static function jyfwcatChild($catId,$level = 1)
	{
		if($level == 0)
		{
			return $catId;
		}

		$temp   = array();
		$result = array($catId);
		$catDB  = new IModel('jyfw_category');

		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$temp = $catDB->query('parent_id = '.$id);
			foreach($temp as $key => $val)
			{
				if(!in_array($val['id'],$result))
				{
					$result[] = $val['id'];
				}
			}
			next($result);
		}
		return join(',',$result);
    }

    	/**
	 * @param
	 * @return array
	 * @brief 无限极分类递归函数
	 */
	public static function sortdata($catArray, $id = 0 , $prefix = '')
	{
		static $formatCat = array();
		static $floor     = 0;

		foreach($catArray as $key => $val)
		{
			if($val['parent_id'] == $id)
			{
				$str         = self::nstr($prefix,$floor);
				$val['name'] = $str.$val['name'];

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

	/**
	 * @brief 根据商品分类的父类ID进行数据归类
	 * @param array $categoryData 商品category表的结构数组
	 * @return array
	 */
	public static function categoryParentStruct($categoryData)
	{
		$result = array();
		foreach($categoryData as $key => $val)
		{
			if(isset($result[$val['parent_id']]) && is_array($result[$val['parent_id']]))
			{
				$result[$val['parent_id']][] = $val;
			}
			else
			{
				$result[$val['parent_id']] = array($val);
			}
		}
		return $result;
	}

	public static function gwcatChild($catId,$level = 1)
	{
		if($level == 0)
		{
			return $catId;
		}

		$temp   = array();
		$result = array($catId);
		$catDB  = new IModel('gw_category');

		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$temp = $catDB->query('parent_id = '.$id);
			foreach($temp as $key => $val)
			{
				if(!in_array($val['id'],$result))
				{
					$result[] = $val['id'];
				}
			}
			next($result);
		}
		return join(',',$result);
	}

}