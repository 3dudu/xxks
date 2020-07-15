<?php
/**
 * @copyright Copyright(c) 2016 aircheng.com
 * @file _event.php
 * @brief 任务触发
 * @author nswe
 * @date 2016/3/3 18:02:21
 * @version 4.4
 */
class _event extends pluginBase
{
	//注册事件
	public function reg()
	{
		plugin::reg('event',$this,'doEvent');
	}

	//授权信息校验
	public function doEvent($data)
	{
		$eventApi = new Event();
		$result = $eventApi->doEvent($data['event'],$data['user_id']);
		return $result;
	}


}