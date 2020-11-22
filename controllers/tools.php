<?php
/**
 * @brief 工具模块
 * @class Tools
 * @note  后台
 */
class Tools extends IController implements adminAuthorization
{
	public $layout='admin';
	public $checkRight = array('check' => 'all','uncheck' => array('seo_sitemaps'));

	function init()
	{

	}


	//上传sql附件进行还原
	function upload_sql()
	{
		$this->layout = '';
		$this->redirect('upload_sql');
	}

	//[备份还原]数据备份动作(ajax操作)
	function db_act_bak()
	{
		//要备份的数据表
		$tableName = IReq::get('name','post');
		$tableName = IFilter::act($tableName,"string");

		//分卷大小限制(KB)
		$partSize = 4000;

		if(is_array($tableName) && isset($tableName[0]) && $tableName[0]!='')
		{
			$backupObj = new DBBackup($tableName);
			$backupObj->setPartSize($partSize);   //设置分卷大小
			$backupObj->runBak();                 //开始执行
			$result = array(
				'isError' => false,
				'redirect'=> IUrl::creatUrl('/tools/db_res'),
			);
		}
		else
		{
			$result = array(
				'isError' => true,
				'message' => '请选择要备份的数据表',
			);
		}
		echo JSON::encode($result);
	}

	//[备份还原]下载数据库
	function download()
	{
		$file = IFilter::act(IReq::get('file'),'filename');
		$backupObj = new DBBackup;
		$backupObj->download($file);
	}

	//[备份还原]删除备份
	function backup_del()
	{
		$name = IFilter::act(IReq::get('name'),'filename');
		$name = is_array($name) ? $name : array($name);

		if($name)
		{
			$backupObj = new DBBackup($name);
			$backupObj->del();
			$this->redirect('db_res');
		}
		else
		{
			IError::show(403,'请选择要删除的备份文件');
		}
	}

	//数据库备份列表
	function db_bak()
	{
		$dbObj = IDBFactory::getDB();
		$tableInfo = $dbObj->query('show table status');
		$this->setRenderData(array('tableInfo' => $tableInfo));
		$this->redirect('db_bak');
	}

	//数据库还原
	function db_res()
	{
		$backupObj = new DBBackup;
		$resList = $backupObj->getList();
		$this->setRenderData(array('resList' => $resList));
		$this->redirect('db_res');
	}

	//[备份还原]导入数据(ajax)
	function res_act()
	{
		$name = IFilter::act(IReq::get('name'));
		if(is_array($name) && $name)
		{
			$backupObj = new DBBackup($name);
			$backupObj->runRes();
			$result = array(
				'isError' => false,
				'redirect'=> IUrl::creatUrl('/tools/db_bak'),
			);
		}
		else
		{
			$result = array(
				'isError' => true,
				'message' => '请选择要导入的SQL文件',
			);
		}
		echo JSON::encode($result);
	}

	//本地上传sql文件导入
	public function localUpload()
	{
		if(isset($_FILES['attach']['tmp_name']) && file_exists($_FILES['attach']['tmp_name']))
		{
			$fileName  = $_FILES['attach']['tmp_name'];
			$backupObj = new DBBackup();
			$backupObj->parseSQL($fileName);
			die('<script type="text/javascript">parent.uploadSuccess();</script>');
		}
		else
		{
		    $error = IUpload::errorMessage($_FILES['attach']['error']);
			die('<script type="text/javascript">parent.uploadFail("'.$error.'");</script>');
		}
	}

	//[备份还原]打包下载
	function download_pack()
	{
		$name = IFilter::act(IReq::get('name'));

		if($name)
		{
			$backupObj = new DBBackup($name);
			$fileName  = $backupObj->packDownload();
			if($fileName === false)
			{
				IError::show(403,'环境不支持zip扩展');
			}

			$db_fileName = $backupObj->download($fileName);
			if(is_file($db_fileName))
			{
				@unlink($db_fileName);
			}
		}
		else
		{
			IError::show(403,'请选择要打包的文件');
		}
	}

	//[公告]增加修改
	function notice_edit_act()
	{
		$id = intval(IReq::get('id','post'));

		$noticeObj = new IModel('announcement');
		$dataArray  = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'content'     => IFilter::act(IReq::get('content','post'),'text'),
			'keywords'    => IFilter::act(IReq::get('keywords','post'),'text'),
			'description' => IFilter::act(IReq::get('description','post'),'text'),
		);
		$dataArray['time'] = date("Y-m-d H:i:s");
		$noticeObj->setData($dataArray);

		if($id)
		{
			$is_success = $noticeObj->update( "id={$id}" );
		}
		else
		{
			$noticeObj->add();
		}
		$this->redirect('notice_list');
	}

	//[公告]删除
	function notice_del()
	{
		$id = IFilter::act( IReq::get('id') , 'int'  );
		if(!is_array($id))
		{
			$id = array($id);
		}
		$id = implode(",",$id);

		$noticeObj = new IModel('announcement');
		$noticeObj->del( "id IN ({$id})" );
		$this->redirect('notice_list');
	}

	//[公告]单页
	function notice_edit()
	{
		$id = IFilter::act( IReq::get('id') , 'int'  );
		if($id)
		{
			//获取文章信息
			$noticeObj       = new IModel('announcement');
			$this->noticeRow = $noticeObj->getObj('id = '.$id);
			if(!$this->noticeRow)
			{
				IError::show(403,"信息不存在");
			}
		}

		$this->redirect('notice_edit',false);
	}
	

	//[广告位] 删除
	function ad_position_del()
	{
		$id = IFilter::act( IReq::get('id') , 'int' );
		if($id)
		{
			$obj = new IModel('ad_position');
			if(is_array($id) && isset($id[0]) && $id[0]!='')
			{
				$id_str = join(',',$id);
				$where = ' id in ('.$id_str.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$obj->del($where);
			$this->redirect('ad_position_list');
		}
		else
		{
			$this->redirect('ad_position_list',false);
			Util::showMessage('请选择要删除的广告位');
		}
	}

	//[广告位] 添加修改 (单页)
	function ad_position_edit()
	{
		$id   = IFilter::act( IReq::get('id'),'int' );
		$name = IFilter::act( IReq::get('name'),'text' );

		$obj = new IModel('ad_position');
		if($name)
		{
			$positionRow = $obj->getObj('name="'.$name.'"');
		}
		else if($id)
		{
			$positionRow = $obj->getObj('id = '.$id);
		}
		$this->positionRow = isset($positionRow) && $positionRow ? $positionRow : array('name' => $name);
		$this->redirect('ad_position_edit',false);
	}

	//[广告位] 添加和修改动作
	function ad_position_edit_act()
	{
		$id = IFilter::act( IReq::get('id') );

		$obj = new IModel('ad_position');

		$dataArray = array(
			'name'         => IFilter::act( IReq::get('name','post') ,'string' ),
			'width'        => IFilter::act( IReq::get('width','post') ),
			'height'       => IFilter::act( IReq::get('height','post') ),
			'fashion'      => IFilter::act( IReq::get('fashion','post'),'int' ),
			'status'       => IFilter::act( IReq::get('status','post'),'int' )
		);
		$obj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$result = $obj->update($where);
		}
		else
			$result = $obj->add();

		$this->redirect('ad_position_list');
	}

	//[广告] 删除
	function ad_del()
	{
		$id = IFilter::act( IReq::get('id') , 'int' );
		if($id)
		{
			$obj = new IModel('ad_manage');
			if(is_array($id) && isset($id[0]) && $id[0]!='')
			{
				$id_str = join(',',$id);
				$where = ' id in ('.$id_str.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$obj->del($where);
			$this->redirect('ad_list');
		}
		else
		{
			$this->redirect('ad_list',false);
			Util::showMessage('请选择要删除的广告');
		}
	}

	//[广告] 添加修改 (单页)
	function ad_edit()
	{
		$id          = IFilter::act(IReq::get('id'),'int');
		$positionId  = IFilter::act(IReq::get('pid'),'int');
		$this->adRow = array('position_id' => $positionId);
		if($id)
		{
			$obj = new IModel('ad_manage');
			$this->adRow = $obj->getObj('id = '.$id);
		}
		$this->redirect('ad_edit');
	}

	//[广告] 添加和修改动作
	function ad_edit_act()
	{
		$id      = IFilter::act( IReq::get('id'),'int');
		$type    = IFilter::act(IReq::get('type'),'int');
		$content = IReq::get('content');
		$files   = $_FILES ? current($_FILES)  : "";

		//附件上传
		if($files)
		{
			$upType    = $type == 1 ? array("gif","png","jpg") : array('flv','swf');
			$uploadDir = IWeb::$app->config['upload'].'/ad';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setType($upType);
			$photoInfo = $uploadObj->run();
			$result = $photoInfo ? current($photoInfo) : "";

			if($result && isset($result['flag']) && $result['flag'] == 1)
			{
				//最终附件路径
				$content = $result['img'];
			}
			else if(!$content)
			{
				IError::show(403,"上传失败,错误信息：".$result['error']);
			}
		}

		$adObj = new IModel('ad_manage');
		$dataArray = array(
			'content'     => IFilter::addSlash($content),
			'name'        => IFilter::act(IReq::get('name')),
			'position_id' => IFilter::act(IReq::get('position_id')),
			'type'        => $type,
			'link'        => IFilter::addSlash(IReq::get('link')),
			'start_time'  => IFilter::act(IReq::get('start_time')),
			'end_time'    => IFilter::act(IReq::get('end_time')),
			'description' => IFilter::act(IReq::get('description'),'text'),
			'order'       => IFilter::act(IReq::get('order'),'int'),
			'goods_cat_id'=> IFilter::act(IReq::get('goods_cat_id'),'int'),
		);

		$adObj->setData($dataArray);
		if($id)
		{
			$where = 'id = '.$id;
			$adObj->update($where);
		}
		else
		{
			$adObj->add();
		}
		$this->redirect("ad_list");
	}

	//帮助信息列表
	function help_list()
	{
		$this->query = Api::run('getHelpList');
		$this->redirect("help_list");
	}

	function help_edit()
	{
		$id = IFilter::act(IReq::get("id"),'int');
		if($id)
		{
			$helpRow = Api::run('getHelpInfo',array('id' => $id));
			if(!$helpRow)
			{
				IError::show(403,'没有这条记录');
			}
			$this->help_row = $helpRow;
		}
		$this->redirect("help_edit");
	}

	//帮助信息保存和更新
	function help_edit_act()
	{
		$id   = IFilter::act(IReq::get("id"),'int');
		$data = array(
			'cat_id'  => IFilter::act(IReq::get('cat_id'),'int'),
			'name'    => IFilter::act(IReq::get('name')),
			'sort'    => IFilter::act(IReq::get("sort"),'int'),
			'content' => IFilter::act(IReq::get("content"),'text'),
			'dateline'=> time(),
		);

		$helpDB = new IModel('help');
		$helpDB->setData($data);

		if($id)
		{
			$helpDB->update('id = '.$id);
		}
		else
		{
			$helpDB->add();
		}
		$this->redirect("help_list");
	}

	//帮助信息删除
	function help_del()
	{
		$id = IFilter::act(IReq::get("id"));
		$id = is_array($id) ? join(',',$id) : $id;
		if(!$id)
		{
			$this->redirect('help_list',false);
			util::showMessage("请选择要删除的信息");
			return;
		}
		$helpDB = new IModel('help');
		$helpDB->del('id in ('.$id.')');
		$this->redirect('help_list');
	}

	//帮助信息编辑
	function help_cat_edit()
	{
		$id = IFilter::act(IReq::get("id"),'int');
		if($id)
		{
			$catRow = Api::run('getHelpCategoryInfo',array('id' => $id));
			if(!$catRow)
			{
				IError::show(403,'信息不存在');
			}
			$this->cat_row = $catRow;
		}
		$this->redirect("help_cat_edit");
	}

	//帮助分类保存和更新
	function help_cat_edit_act()
	{
		$id   = IFilter::act(IReq::get("id","post"),'int');
		$data = array(
			"name"          => IFilter::act(IReq::get("name")),
			"position_left" => IFilter::act(IReq::get("position_left"),'int'),
			"position_foot" => IFilter::act(IReq::get("position_foot"),'int'),
			"sort"          => IFilter::act(IReq::get("sort"),'int'),
		);

		$helpCatDB = new IModel('help_category');
		$helpCatDB->setData($data);

		if($id)
		{
			$helpCatDB->update('id = '.$id);
		}
		else
		{
			$helpCatDB->add();
		}
		$this->redirect('help_cat_list');
	}

	//帮助分类删除
	function help_cat_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$id = is_array($id) ? join(',',$id) : $id;
		if(!$id)
		{
			$this->redirect('help_cat_list',false);
			util::showMessage("请选择要删除的信息");
			return;
		}
		$helpCatDB = new IModel('help_category');
		$helpCatDB->del('id in ('.$id.')');
		$this->redirect('help_cat_list');
	}

	//[关键词管理]添加
	function keyword_add()
	{
		$word  = IFilter::act(IReq::get('word'));
		$hot   = intval(IReq::get('hot'));
		$order = IReq::get('order') ? intval(IReq::get('order')) : 99;

		$re = keywords::add($word ,$hot,$order);

		if($re['flag']==true)
		{
			$this->redirect('keyword_list');
		}
		else
		{
			$this->redirect('keyword_edit');
			Util::showMessage($re['data']);
		}
	}

	//[关键词管理]删除
	function keyword_del()
	{
		$id = IFilter::act(IReq::get('id'));
		if($id)
		{
			$keywordObj = new IModel('keyword');
			if(is_array($id))
			{
				$ids = '"'.join('","',$id).'"';
				$keywordObj->del('word in ('.$ids.')');
			}
			else
			{
				$keywordObj->del('word = "'.$id.'"');
			}
		}
		else
		{
			$message = '请选择要删除的关键词';
		}

		if(isset($message))
		{
			$this->redirect('keyword_list',false);
			Util::showMessage($message);
		}
		else
		{
			$this->redirect('keyword_list');
		}
	}

	//[关键词管理]设置hot
	function keyword_hot()
	{
		$id  = IFilter::act(IReq::get('id'));

		$keywordObj = new IModel('keyword');
		$dataArray  = array('hot' => 'abs(hot - 1)');
		$keywordObj->setData($dataArray);
		$is_result  = $keywordObj->update('word = "'.$id.'"','hot');

		$keywordRow = $keywordObj->getObj('word = "'.$id.'"');
		if($is_result!==false)
		{
			echo JSON::encode(array('isError' => false,'hot' => $keywordRow['hot']));
		}
		else
		{
			echo JSON::encode(array('isError'=>true,'message'=>'设置失败'));
		}
	}

	//[关键词管理]统计商品数量
	function keyword_account()
	{
		$word = IFilter::act(IReq::get('id'));
		if(!$word)
		{
			$this->redirect('keyword_list',false);
			Util::showMessage('请选择要同步的关键词');
		}

		$keywordObj = new IModel('keyword');
		foreach($word as $key => $val)
		{
			//获取各个关键词的管理商品数量
			$resultCount = keywords::count($val);
			$dataArray = array(
				'goods_nums' => $resultCount,
			);
			$keywordObj->setData($dataArray);
			$keywordObj->update('word = "'.$val.'"');
		}
		$this->redirect('keyword_list');
	}
	//关键词排序
	function keyword_order()
	{
		$word  = IFilter::act(IReq::get('id'));
		$order = IReq::get('order') ? intval(IReq::get('order')) : 99;

		$keywordObj = new IModel('keyword');
		$dataArray = array('order' => $order);
		$keywordObj->setData($dataArray);
		$is_success = $keywordObj->update('word = "'.$word.'"');

		if($is_success === false)
		{
			$result = array(
				'isError' => true,
				'message' => '更新排序失败',
			);
		}
		else
		{
			$result = array(
				'isError' => false,
			);
		}
		echo JSON::encode($result);
	}

	/**
	 * 查询删除
	 */
	function search_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$id = is_array($id) ? join(",",$id) : $id;

		//生成search对象
	    $tb_search = new IModel('search');
	    if($id)
		{
			$tb_search->del("id in (".$id.")");
		}
		else
		{
			$this->redirect("search_list",false);
			Util::showMessage('请选择要删除的数据');
		}
		$this->redirect("search_list");
	}
}