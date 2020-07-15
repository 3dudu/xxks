<?php
/**
 * @copyright Copyright(c) 2016 aircheng.com
 * @file menu.php
 * @brief 后台系统菜单管理
 * @author nswe
 * @date 2016/3/4 23:59:33
 * @version 4.4
 */
class Menu
{
    //菜单的配制数据
	public static $menu = array(
		'用户'=>array(
			'企业管理'=>array(
			    '/company/company_list' => '企业列表',
				'/company/company_edit' => '添加企业',
				'/company/category_list'	=>	'行业列表',
				'/company/category_edit'	=>	'添加行业',
				'/company/jyfw_list'	=>	'经营范围列表',
				'/company/jyfw_edit'	=>	'添加经营范围'
			),
			'从业人员'=>array(
				'/company/company_work_list' => '人员列表',
				'/company/company_work_edit' => '从业人员添加',
				'/member/category_list'	=>	'岗位列表',
				'/member/category_edit'	=>	'添加岗位'
			),
			'账号管理'=>array(
				'/member/member_list' 	=> '账号列表',
	    	//	'/member/member_work_list' 	=> '用户工作记录',
			),

			'信息处理' => array(
				'/comment/suggestion_list'  => '建议管理',
			//	'/comment/discussion_list'	=> '讨论管理',
			),
		),

	   '学习'=>array(
			'内容管理'=>array(
				'/study/article_list'=> '内容列表',
				'/study/article_edit'=> '新增内容',
				'/study/article_part_list'=> '章节列表',
				'/study/article_cat_list'=> '学习栏目',
			),
			'学习配置'=>array(
				'/system/conf_banner' => '首页幻灯图',
			),
			'学习记录'=>array(
				'/study/study_log'=> '学习记录',
			),
		),

		'考试'=>array(
			'题库管理'=>array(
				'/exam/question_list'=> '题目管理',
				'/exam/question_edit'=> '题目添加',
				'/exam/question_pool_list'=> '题库分类',
				'/exam/question_pool_edit'=> '添加题库分类',
			),
			'试卷管理'=>array(
				'/exam/paper_type_list'=> '试卷类型',
				'/exam/paper_type_edit'=> '编辑试卷类型',
				'/exam/paper_list'=> '试卷记录',
			),
			'答题记录'=>array(
				'/exam/paper_log'=> '考试记录',
				'/exam/question_log'=> '答题历史',
			),
		),
		'统计'=>array(
			'排行'=>array(
				  '/market/companyrank' 	   => '企业排行',
				  '/market/userrank'       => '用户排行'
			),
			'统计图表'=>array(
				  '/market/user_reg' 	   => '用户注册统计',
				  '/market/exam_point'       => '考试积分统计',
				  '/market/studypoint'       => '学习积分统计',
				  '/market/companypoint'       => '企业积分统计',
				  '/market/company_point_detail'       => '企业积分分布'
			),
			'日志操作记录'=>array(
				'/market/point_list'   => '积分记录',
				'/market/operation_list' => '后台操作记录',
			),
		),


        '系统'=>array(
    		'后台首页'=>array(
    			'/system/default' => '首页',
    		),
        	'系统管理'=>array(
        		'/system/conf_base' => '系统设置',
        	//	'/system/conf_guide' => '网站导航',
        		'/system/conf_ui/type/site'   => '网站前台主题',
        		'/system/conf_ui/type/system'   => '后台管理主题',
        		'/system/conf_ui/type/comadmin'   => '企业管理主题',
        	),
        	'支付管理'=>array(
            	'/system/payment_list' => '支付方式'
			),
			'积分规则'=>array(
				'/pointconf/event_list'	=>	'规则列表',
				'/pointconf/event_edit'	=>	'规则添加'
			),
        	'第三方平台'=>array(
            	'/system/oauth_list' => 'oauth登录列表',
            	'/system/hsms' => '手机短信平台',
        	),
        	'地域管理'=>array(
        		'/system/area_list' => '地区列表',
        	),
        	'权限管理'=>array(
        		'/system/admin_list' => '管理员',
        		'/system/role_list'  => '角色',
        		'/system/right_list' => '权限资源'
        	),
		),

       '工具'=>array(
			'数据库管理'=>array(
				'/tools/db_bak' => '数据库备份',
				'/tools/db_res' => '数据库还原',
			),
   			'公告管理'=>array(
   				'/tools/notice_list'=> '公告列表',
   				'/tools/notice_edit'=> '公告发布'
			   ),
			   '帮助管理'=>array(
				'/tools/help_cat_list'=> '帮助分类',
				'/tools/help_list'=> '帮助列表'
			),
		),
		/*
		'插件' => array(
       		'插件管理' => array(
       			'/plugins/plugin_list' => '插件列表',
       		),
		),
		*/
	);

	//非菜单连接映射关系,array(视图名称 => menu数组中已存在的菜单连接)
	public static $innerPathUrl = array(
		"/system/navigation" => "/system/default",
		"/system/navigation_edit" => "/system/default",
	);

    /**
     * @brief 根据权限初始化菜单
     * @param int $roleId 角色ID
     * @return array 菜单数组
     */
    public static function init($roleId)
    {
		//菜单创建事件触发
		plugin::trigger("onSystemMenuCreate");

		//根据角色分配权限
		if($roleId == 0)
		{
			$adminRights = 'administrator';
		}
		else
		{
			$roleObj = new IModel('admin_role');
			$where   = 'id = '.$roleId.' and is_del = 0';
			$roleRow = $roleObj->getObj($where);
			$adminRights = isset($roleRow['rights']) ? $roleRow['rights'] : '';
		}

		//1,超管返回全部菜单
		if($adminRights == "administrator")
		{
			return self::$menu;
		}

		//2,根据权限码显示菜单
		$result      = array();
		$defaultShow = array('/system/default');
		foreach(self::$menu as $key1 => $val1)
		{
			foreach($val1 as $key2 => $val2)
			{
				foreach($val2 as $key3 => $val3)
				{
					//把菜单数据里面的路径转化成@符号做权限码比对
					$tempKey3 = str_replace("/","@",trim($key3,"/"));
					if(in_array($key3,$defaultShow) || strpos($adminRights,$tempKey3) !== false)
					{
						$result[$key1][$key2][$key3] = $val3;
					}
				}
			}
		}
		return $result;
    }

    /**
     * @brief 根据当前URL动态生成菜单分组
     * @param array  $menu 菜单数据
     * @param string $info 连接信息
     * @return array 菜单数组
     */
    public static function get($menu,$info)
    {
    	$result = self::menuInfo($menu,$info);
    	if($result)
    	{
    		return $result;
    	}

		//历史URL信息
		$lastInfo = IUrl::getRefRoute();
		if($lastInfo && strpos($lastInfo,$info) === false && $result = self::menuInfo($menu,$lastInfo))
		{
			ICookie::set('lastInfo',$lastInfo);
			return $result;
		}

		//从COOKIE读取URL信息
		$lastInfo = ICookie::get('lastInfo');
		if($lastInfo)
		{
			return self::menuInfo($menu,$lastInfo);
		}
		return array('系统' => self::$menu['系统']);
    }

	/**
	 * @brief 判断url路径获取定义的菜单项
	 * @param array  $menu 当前管理员权限合法的菜单
	 * @param string $info 访问的URL
	 * @return array(地址=>名称) or null
	 */
    public static function menuInfo($menu,$info)
    {
    	//已有菜单查找
		foreach($menu as $key1 => $val1)
		{
			foreach($val1 as $key2 => $val2)
			{
				foreach($val2 as $key3 => $val3)
				{
					if(strpos($key3,$info) !== false || strpos($info,$key3) !== false)
					{
						return array($key1 => $menu[$key1]);
					}
				}
			}
		}

		//配置菜单映射
		if(self::$innerPathUrl)
		{
			foreach(self::$innerPathUrl as $key => $val)
			{
				if(strpos($key,$info) !== false)
				{
					return self::menuInfo($menu,$val);
				}
			}
		}
		return null;
    }

    /**
     * @brief 获取顶级分类关系数据
     * @param array $menu 菜单数据
     * @return array 顶级菜单数组
     */
    public static function getTopMenu($menu)
    {
    	$result = array();
		foreach($menu as $key1 => $val1)
		{
			foreach($val1 as $key2 => $val2)
			{
				foreach($val2 as $key3 => $val3)
				{
					$result[$key1] = $key3;
					break 2;
				}
			}
		}
		return $result;
    }
}