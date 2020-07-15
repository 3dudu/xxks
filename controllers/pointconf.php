<?php
/**
 * @brief 任务管理模块
 * @class Pointconf
 * @note  后台
 */
class Pointconf extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
	public $layout = 'admin';
	public $data = array();

	function init()
	{
	}

	
	//禁用启用event
	function event_status()
	{
		$status    = IFilter::act(IReq::get('status'));
		$id        = IFilter::act(IReq::get('id'),'int');

		if(!empty($id) && $status != null)
		{
			$eventObj = new IModel('event');
			if(is_array($id))
			{
				foreach($id as $val)
				{
					$where = 'id = '.$val;
					$eventRow = $eventObj->getObj($where);
					if($status=='is_close')
					{
						$eventObj->setData(array('status' => 0));
					}
					else
					{
						$eventObj->setData(array('status' => 1));
					}
					$eventObj->update($where);
				}
			}
			else
			{
				$where = 'id = '.$id;
				$eventRow = $eventObj->getObj($where);
				if($status=='is_close')
				{
					$eventObj->setData(array('status' => 0));
				}
				else
				{
					$eventObj->setData(array('status' => 1));
				}
				$eventObj->update($where);
			}
			$this->redirect('event_list');
		}
		else
		{
			$this->redirect('event_list',false);
			Util::showMessage('请选择要修改的id值');
		}
	} 



	//任务添加,修改[单页]
	function event_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$eventObj = new IModel('event');
			$where = 'id = '.$id;
			$eventRow = $eventObj->getObj($where);
			if(empty($eventRow))
			{
				$this->redirect('event_list');
			}

			$this->eventRow = $eventRow;
		}
		$this->redirect('event_edit');
	}

		//删除添加,修改[单页]
		function event_del()
		{
			$id = IFilter::act(IReq::get('id'),'int');

			if($id)
			{
				$eventDB = new IModel('event');
				$eventDB->del('id = '.$id);
			}
	
			$this->redirect('event_list');
		}

	//任务添加,修改[动作]
	function event_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$user_group  = IFilter::act(IReq::get('user_group','post'));

		if(is_string($user_group))
		{
			$user_group_str = $user_group;
		}
		else
		{
			$user_group_str = ",".join(',',$user_group).",";
		}

		$dataArray = array(
			'id'         => $id,
			'name'       => IFilter::act(IReq::get('name','post')),
			'status'   => IFilter::act(IReq::get('status','post')),
			'start_date' => IFilter::act(IReq::get('start_date','post')),
			'end_date'  => IFilter::act(IReq::get('end_date','post')),
			'mark'      => IFilter::act(IReq::get('mark','post')),
			'event_type' => IFilter::act(IReq::get('event_type','post')),
			'priedtype' => IFilter::act(IReq::get('priedtype','post')),
			'event'      => IFilter::act(IReq::get('event','post')),
			'point'      => IFilter::act(IReq::get('point','post')),
			'point_type'      => IFilter::act(IReq::get('point_type','post')),
			'task_num'      => IFilter::act(IReq::get('task_num','post')),
			'sort'      => IFilter::act(IReq::get('sort','post')),
			'user_group' => $user_group_str,
		);


		$eventObj = new IModel('event');
		$eventObj->setData($dataArray);
		if($id)
		{
			$where = 'id = '.$id;
			$eventObj->update($where);
		}
		else
		{
			$eventObj->add();
		}
		$this->redirect('event_list');
	}



	function event_sort()
	{
		$event_id = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$flag = 0;
		if($event_id)
		{
			$tb_event = new IModel('event');
			$event_info = $tb_event->getObj('id='.$event_id);
			if(count($event_info)>0)
			{
				if($event_info['sort']!=$sort)
				{
					$tb_event->setData(array('sort'=>$sort));
					if($tb_event->update('id='.$event_id))
					{
						$flag = 1;
					}
				}
			}
		}
		echo $flag;
	}
}