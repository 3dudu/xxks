<?php
/**
 * @copyright Copyright(c) 2016 aircheng.com
 * @file menuCompany.php
 * @author nswe
 * @date 2016/3/8 9:30:34
 * @version 4.4
 */
class menuCompany
{
    //菜单的配制数据
	public static $menu = array
	(
		"员工管理" => array(
			"/comadmin/index" => "员工列表",
			"/comadmin/user_request" => "员工审核",
			"/comadmin/userrank" => "员工排行",
		),
		"考试学习" => array(
			"/comadmin/study_log" => "学习记录",
			"/comadmin/paper_log" => "考试记录",
			"/comadmin/question_log" => "答题记录",
		),
		"统计分析" => array(
			"/comadmin/company_point_line" => "积分统计",
			"/comadmin/user_point_detail" => "员工情况",
		),
		"配置模块" => array(
			"/comadmin/changepassword" => "修改登录密码",
			//"/comadmin/company_edit" => "资料修改",
		),
	);

    /**
     * @brief 根据权限初始化菜单
     * @param int $roleId 角色ID
     * @return array 菜单数组
     */
    public static function init($roleId = "")
    {
		//菜单创建事件触发
		plugin::trigger("onCompanyMenuCreate");
		return self::$menu;
    }
}