<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file util.php
 * @brief 公共函数类
 * @author kane
 * @date 2011-01-13
 * @version 0.6
 * @note
 */

 /**
 * @class Util
 * @brief 公共函数类
 */
class Util
{
	/**
	 * @brief 显示错误信息（dialog框）
	 * @param string $message	错误提示字符串
	 */
	public static function showMessage($message)
	{
		echo '<script type="text/javascript">typeof(toastr) == "object" ? toastr.warning("'.$message.'") : alert("'.$message.'");</script>';
		exit;
	}

	//字符串拼接
	public static function joinStr($id)
	{
		if(is_array($id))
		{
			$where = "id in (".join(',',$id).")";
		}
		else
		{
			$where = 'id = '.$id;
		}
		return $where;
	}

	/**
	 * 商品价格格式化
	 * @param $price float 商品价
	 * @return float 格式化后的价格
	 */
	public static function priceFormat($price)
	{
		return round($price,2);
	}

	/**
	 * 检索自动执行
	 * @param array $search 查询拼接规则， key(字段) => like,likeValue(数据)
	 */
	public static function search($search)
	{
		$where = array(1);
		if($search && is_array($search))
		{
			//like子句处理
			if(isset($search['like']) && $search['likeValue'])
			{
				$search['like']      = IFilter::act($search['like'],"strict");
				$search['likeValue'] = IFilter::act($search['likeValue']);

				$where[] = $search['like']." like '%".$search['likeValue']."%' ";
			}
			unset($search['like']);
			unset($search['likeValue']);
			//自定义子句处理
			foreach($search as $key => $val)
			{
				$key = IFilter::act($key,'strict');
				$val = IFilter::act($val,'strict');
				
				if($val === '' || $key === '' || $val == 'favicon.ico')
				{
					continue;
				}

				if( strpos($key,'num') !== false || in_array($val[0],array("<",">","=")) )
				{
					$where[] = $key." ".$val;
				}
				else
				{
					$where[] = $key."'".$val."'";
				}
			}
		}
		return join(" and ",$where);
	}

	/**
	 * @brief 计算折扣率
	 * @param $originalPrice float 原价
	 * @param $nowPrice float 现价
	 * @return float 折扣数
	 */
	public static function discount($originalPrice,$nowPrice)
	{
		if($originalPrice >= $nowPrice)
		{
			return round($nowPrice/$originalPrice,2)*10;
		}
		return "";
	}

    //检索用户信息
	public static function searchUser($info)
	{
	    $userIds = "";
        $revInfo = array();
        $where   = array();

        //用户名
        if(isset($info['username']) && $info['username'])
        {
            $where[] = "u.username = '".$info['username']."'";
            $revInfo[] = "【用户名：".$info['username']."】";
        }

        //用户组
        if(isset($info['group_id']) && $info['group_id'])
        {
            $where[] = "m.group_id = ".$info['group_id'];

            $groupDB = new IModel('user_group');
            $groupRow= $groupDB->getObj($info['group_id']);

            $revInfo[] = "【会员组：".$groupRow['group_name']."】";
        }

        //手机号
        if(isset($info['mobile']) && $info['mobile'])
        {
            $where[] = "m.mobile = '".$info['mobile']."'";
            $revInfo[] = "【用户手机号：".$info['mobile']."】";
        }

        //积分区间
        if(isset($info['point_min']) && isset($info['point_max']) && $info['point_min'] && $info['point_max'])
        {
            $where[] = "m.point BETWEEN ".$info['point_min']." and ".$info['point_max'];
            $revInfo[] = "【积分区间：".$info['point_min']."——".$info['point_max']."】";
        }

        //余额区间
        if(isset($info['balance_min']) && isset($info['balance_max']) && $info['balance_min'] && $info['balance_max'])
        {
            $where[] = "m.balance BETWEEN ".$info['balance_min']." and ".$info['balance_max'];
            $revInfo[] = "【余额区间：".$info['balance_min']."——".$info['balance_max']."】";
        }

        if($where)
        {
        	$userDB = new IQuery('user as u');
        	$userDB->join  = 'left join member as m on u.id = m.user_id';
        	$userDB->fields= 'u.id';
        	$userDB->where = join(" and ",$where);
        	$userData      = $userDB->find();
        	$tempArray     = array();
        	foreach($userData as $key => $item)
        	{
        		$tempArray[] = $item['id'];
        	}
        	$userIds = join(',',$tempArray);
        }

    	return $userIds ? array('id' => $userIds,'note' => join(" ",$revInfo)) : null;
	}
}