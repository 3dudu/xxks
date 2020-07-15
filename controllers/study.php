<?php
/**
 * @brief 学习管理模块
 * @class Study
 * @note  后台
 */
class Study extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
    public $layout = 'admin';
    public $data = array();

	public function init()
	{

    }

    /**
	 * @brief 添加中图片上传的方法
	 */
	public function study_img_upload()
	{
		 //调用文件上传类
		 $uploadDir = IWeb::$app->config['upload'].'/study';
		 $photoObj = new PhotoUpload($uploadDir);
		$result   = current($photoObj->run());
		echo JSON::encode($result);
	}
    
	//[文章]删除
	function article_del()
	{
		$id = IFilter::act( IReq::get('id') ,'int' );
		if($id)
		{
			$obj = new IModel('article');

			if(is_array($id) && isset($id[0]) && $id[0]!='')
			{
				$id_str = join(',',$id);
				$where1 = ' id in ('.$id_str.')';
			}
			else
			{
				$where1 = 'id = '.$id;
			}
			$obj->del($where1);               //删除商品
			$this->redirect('article_list');
		}
		else
		{
			$this->redirect('article_list',false);
			Util::showMessage('请选择要删除的文章');
		}
	}

		//[文章章节]删除
	function article_part_del()
	{
		$id = IFilter::act( IReq::get('id') ,'int' );
		$aid = IFilter::act( IReq::get('aid') ,'int' );
		if($id)
		{
			$obj = new IModel('article_part');

			if(is_array($id) && isset($id[0]) && $id[0]!='')
			{
				$id_str = join(',',$id);
				$where1 = ' id in ('.$id_str.')';
			}
			else
			{
				$where1 = 'id = '.$id;
			}
			$obj->del($where1);               //删除商品
			$this->redirect('/study/article_part_list/aid/'.$aid);
		}
		else
		{
			$this->redirect('/study/article_part_list/aid/'.$aid,false);
			Util::showMessage('请选择要删除的文章');
		}
	}

	//[文章]单页
	function article_edit()
	{
		$id   = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			//获取文章信息
			$articleObj       = new IModel('article');
			$articleRow = $articleObj->getObj('id = '.$id);
			if(!$articleRow)
			{
				IError::show(403,"文章信息不存在");
            }
            
            //相册
            $tb_article_photo = new IQuery('article_photo_relation as ghr');
            $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
            $tb_article_photo->fields = 'gh.img';
            $tb_article_photo->where = 'ghr.aid='.$articleRow['id'];
            $tb_article_photo->order = 'ghr.id asc';
			$articleRow['photo'] = $tb_article_photo->find();
			
            $this->setRenderData(array('articleRow' => $articleRow));
		}else{
			$siteConfigObj = new Config("site_config");
			$site_config   = $siteConfigObj->getInfo();
			//最小学习时间
			$mintime =  isset($site_config['mintime'])?$site_config['mintime']:2;
			$this->setRenderData(array('articleRow' => array('mintime'=>$mintime)));
		}
		$this->redirect('article_edit');
	}

	function article_part_change(){
		$id = IFilter::act( IReq::get('id') ,'int' );
		$aid = IFilter::act( IReq::get('aid') ,'int' );
		if($id)
		{
			$obj = new IModel('article_part');
			$aobj = new IModel('article');
			$ar = $aobj->getObj('id='.$aid);
			if(is_array($id) && isset($id[0]) && $id[0]!='')
			{
			}
			else
			{
				$id[] = $id;
			}

			foreach($id as $cid){
				$po = $obj->getObj('id='.$cid);
				$dataArray  = array(
					'title'       => $po['title'],
					'category_id' => $ar['category_id'],
					'update_time' => ITime::getDateTime(),
					'keywords'    => $po['keywords'],
					'description'    => $po['description'],
					'visibility'    => $po['visibility'],
					'top'         => $ar['top'],
					'sort'        => $ar['sort'],
					'mintime'        => $ar['mintime'],
					'style'        => $ar['style'],
					'color'        => $ar['color'],
					'img'        => $ar['img'],
					'wechatname'       => $po['wechatname'],
					'author'       => $po['author'],
					'create_time_v'        => $ar['create_time_v'],
				);
				$dataArray['create_time'] = $dataArray['update_time'];
				$aobj->setData($dataArray);
				$naid = $aobj->add();
				$obj->setData(array('aid'=>$naid));
				$obj->update('id='.$cid);
			}

			$this->redirect('/study/article_part_list/aid/'.$aid);
		}
		else
		{
			$this->redirect('/study/article_part_list/aid/'.$aid,false);
			Util::showMessage('请选择要删除的文章');
		}
	}

	//[文章]增加修改
	function article_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		$articleObj = new IModel('article');
		$dataArray  = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'content'     => IFilter::act(IReq::get('content','post'),'text'),
			'category_id' => IFilter::act(IReq::get('category_id','post'),'int'),
			'update_time' => ITime::getDateTime(),
			'keywords'    => IFilter::act(IReq::get('keywords','post')),
			'description' => IFilter::act(IReq::get('description','post'),'text'),
			'visibility'   => IFilter::act(IReq::get('visibility','post'),'int'),
			'top'         => IFilter::act(IReq::get('top','post'),'int'),
			'sort'        => IFilter::act(IReq::get('sort','post'),'int'),
			'mintime'        => IFilter::act(IReq::get('mintime','post'),'int'),
			'style'       => IFilter::act(IReq::get('style','post')),
			'color'       => IFilter::act(IReq::get('color','post')),
			'img'       => IFilter::act(IReq::get('img','post')),
			'create_time_v'       => IFilter::act(IReq::get('create_time_v','post')),
			'wechatname'       =>  IFilter::act(IReq::get('wechatname','post')),
			'author'       =>  IFilter::act(IReq::get('author','post')),
		);
		if($dataArray['mintime']<1){
			$siteConfigObj = new Config("site_config");
			$site_config   = $siteConfigObj->getInfo();
			//最小学习时间
			$dataArray['mintime'] =  isset($site_config['mintime'])?$site_config['mintime']:2;
		}
        $img = IFilter::act(IReq::get('img'),'string');
        $_imgList = IFilter::act(IReq::get('_imgList'));
		if($_imgList)
		{
			$_imgList   = trim($_imgList,',');
			$_imgList   = explode(",",$_imgList);
			$_imgList   = array_filter($_imgList);
			$img = $img ? $img : current($_imgList);
		}
        $dataArray['img'] = $img;
		//检查catid是否为空
		if($dataArray['category_id'] == 0)
		{
			$this->articleRow = $dataArray;
			$this->redirect('article_edit',false);
			Util::showMessage('请选择分类');
		}
		
		if($id)
		{
			$articleObj->setData($dataArray);
			//开始更新操作
			$where = 'id = '.$id;
			$is_success = $articleObj->update($where);
		}
		else
		{
			$dataArray['create_time'] = $dataArray['update_time'];
			$articleObj->setData($dataArray);
			$id = $articleObj->add();
			$is_success = $id ? true : false;
		}

		if($is_success)
		{
					//处理图片
            $photoRelationDB = new IModel('article_photo_relation');
            $photoRelationDB->del('aid = '.$id);
            if(isset($_imgList) && $_imgList)
            {
                $photoDB = new IModel('photo');
                foreach($_imgList as $key => $val)
                {
                    $val = IFilter::act($val);
                    $photoPic = $photoDB->getObj('img = "'.$val.'"','id');
                    if($photoPic)
                    {
                        $photoRelationDB->setData(array('aid' => $id,'photo_id' => $photoPic['id']));
                        $photoRelationDB->add();
                    }
                }
            }
		}
		else
		{
			$dataArray['id'] = $id;
			$this->articleRow = $dataArray;
			$this->redirect('article_edit',false);
			Util::showMessage('插入数据时发生错误');
		}

		$this->redirect('article_list');
    }
    

	//[文章]增加修改
	function article_part_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		$articleObj = new IModel('article_part');
		$dataArray  = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'update_time' => ITime::getDateTime(),
			'keywords'    => IFilter::act(IReq::get('keywords','post')),
			'description' => IFilter::act(IReq::get('description','post'),'text'),
			'visibility'   => IFilter::act(IReq::get('visibility','post'),'int'),
			'aid'   => IFilter::act(IReq::get('aid','post'),'int'),
			'top'         => IFilter::act(IReq::get('top','post'),'int'),
			'sort'        => IFilter::act(IReq::get('sort','post'),'int'),
			'style'       => IFilter::act(IReq::get('style','post')),
			'color'       => IFilter::act(IReq::get('color','post')),
			'url'       => IFilter::act(IReq::get('url','post')),
			'create_time_v'       => IFilter::act(IReq::get('create_time_v','post')),
			'wechatname'       =>  IFilter::act(IReq::get('wechatname','post')),
			'author'       =>  IFilter::act(IReq::get('author','post')),
        );
		$content     = IReq::get('content','post');

		$v = '';
		preg_match('/<video.*><\/video>/', $content, $matches, PREG_OFFSET_CAPTURE);
		if($matches && !empty($matches) && isset($matches[0])){
			$v = $matches[0][0];
			$content = str_replace($v,'[vvvvv]',$content);
		}
		preg_match('/<video[^>]*\/>/', $content, $matches, PREG_OFFSET_CAPTURE);
		if($matches && !empty($matches) && isset($matches[0])){
			$v = $matches[0][0];
			$content = str_replace($v,'[vvvvv]',$content);
		}

	   //替换video标签
		$content = IFilter::act($content,'text2');
		if($v){
			$content = str_replace('[vvvvv]',$v,$content);
		}
	    $dataArray['content'] = $content;
		//检查catid是否为空
		if($id)
		{
			$articleObj->setData($dataArray);
			//开始更新操作
			$where = 'id = '.$id;
			$is_success = $articleObj->update($where,array(),true);
		}
		else
		{
			$dataArray['create_time'] = $dataArray['update_time'];
			$articleObj->setData($dataArray);
			$id = $articleObj->add(true);
			$is_success = $id ? true : false;
		}

		if($is_success)
		{
		}
		else
		{
			$dataArray['id'] = $id;
			$this->articleRow = $dataArray;
			$this->redirect('article_edit',false);
			Util::showMessage('插入数据时发生错误');
		}

		$this->redirect('article_part_list?aid='.$dataArray['aid']);
    }
    
	//[文章分类] 增加和修改动作
	function cat_edit_act()
	{
		$id        = IFilter::act( IReq::get('id','post') );
		$parent_id = IFilter::act( IReq::get('parent_id','post') ) ;

		$catObj    = new IModel('article_category');
		$DataArray = array(
			'parent_id' => $parent_id,
			'name'      => IFilter::act( IReq::get('name','post'),'string'),
			'issys'     => IFilter::act( IReq::get('issys','post') ),
			'sort'      => IFilter::act( IReq::get('sort','post') ),
			'title'     => IFilter::act( IReq::get('title','post') ),
			'keywords'  => IFilter::act( IReq::get('keywords','post') ),
			'description'=>IFilter::act( IReq::get('description','post') ),
			'cat_type'=>IFilter::act( IReq::get('cat_type','post') ),
		);

		//附件上传$_FILE
		if($_FILES)
		{
			$uploadDir = IWeb::$app->config['upload'].'/study';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

			//logo图片处理
			if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
			{
				$DataArray['logo'] = $photoInfo['logo']['img'];
			}
		}
		

		/*开始--获取path信息*/
		//1,修改操作
		if($id)
		{
			$where  = 'id = '.$id;
			$catRow = $catObj->getObj($where);
			if($catRow['parent_id']==$parent_id)
			{
				$isMoveNode = false;
				$DataArray['path'] = $catRow['path'];
			}
			else
				$isMoveNode = true;

			$localId = $id;
		}
		//2,新增操作
		else
		{
			$max_id  = $catObj->getObj('','max(id) as max_id');
			$localId = $max_id['max_id'] ? $max_id['max_id']+1 : 1;
		}

		//如果不存在path数据时,计算path数据
		if(!isset($DataArray['path']))
		{
			//获取父节点的path路径
			if($parent_id==0)
				$DataArray['path'] = ','.$localId.',';
			else
			{
				$where     = 'id = '.$parent_id;
				$parentRow = $catObj->getObj($where);
				$DataArray['path'] = $parentRow['path'].$localId.',';
			}
		}
		/*结束--获取path信息*/
		//设置数据值
		$catObj->setData($DataArray);

		//1,修改操作
		if($id)
		{
			//节点移动
			if($isMoveNode == true)
			{
				if(isset($parentRow) && $parentRow['path']!=null && strpos($parentRow['path'],','.$id.',')!==false)
				{
					$this->catRow = array(
						'parent_id' => $DataArray['parent_id'],
						'name'      => $DataArray['name'],
						'issys'     => $DataArray['issys'],
						'sort'      => $DataArray['sort'],
						'id'        => $id,
						'title'     => $DataArray['title'],
 						'keywords'   =>$DataArray['keywords'],
						'description'=>$DataArray['description'],
					);
					$this->redirect('article_cat_edit',false);
					Util::showMessage('不能该节点移动到其子节点的位置上');
				}
				else
				{
					//其子节点批量移动
					$childObj = new IModel('article_category');
					$oldPath  = $catRow['path'];
					$newPath  = $DataArray['path'];

					$where = 'path like "'.$oldPath.'%"';
					$updateData = array(
						'path' => "replace(path,'".$oldPath."','".$newPath."')",
					);
					$childObj->setData($updateData);
					$childObj->update($where,array('path'));
				}
			}
			$where = 'id = '.$id;
			$catObj->update($where);
		}
		//2,新增操作
		else
			$catObj->add();

		$this->redirect('article_cat_list');
	}

	//[文章分类] 增加修改单页
	function cat_edit()
	{
		$data = array();
		$id = IFilter::act( IReq::get('id'),'int' );

		if($id)
		{
			$catObj = new IModel('article_category');
			$where  = 'id = '.$id;
			$data = $catObj->getObj($where);
			if(count($data)>0)
			{
				$this->catRow = $data;
				$this->redirect('article_cat_edit',false);
			}
		}
		if(count($data)==0)
		{
			$this->redirect('article_cat_list');
		}
	}

	//[文章分类] 删除
	function cat_del()
	{
		$id = IFilter::act( IReq::get('id'),'int' );
		$catObj = new IModel('article_category');

		//是否执行删除检测值
		$isCheck=true;

		//检测是否有parent_id 为 $id
		$where   = 'parent_id = '.$id;
		$catData = $catObj->getObj($where);
		if($catData)
		{
			$isCheck=false;
			$message='此分类下还有子分类';
		}

		//检测是否有article的category_id 为 $id
		else
		{
			$articleObj = new IModel('article');
			$where = 'category_id = '.$id;
			$catData = $articleObj->getObj($where);

			if($catData)
			{
				$isCheck=false;
				$message='此分类下还有文章';
			}
		}

		if($isCheck == false)
		{
			$message = isset($message) ? $message : '删除失败';
			$this->redirect('article_cat_list',false);
			Util::showMessage($message);
		}
		else
		{
			$where  = 'id = '.$id;
			$result = $catObj->del($where);
			$this->redirect('article_cat_list');
		}
	}


	//[文章]单页
	function article_part_edit()
	{
		$data = array();
		$id   = IFilter::act(IReq::get('id'),'int');
		$aid   = IFilter::act(IReq::get('aid'),'int');
		if($id)
		{
			//获取文章信息
			$articlePartObj       = new IModel('article_part');
			$this->articlePartRow = $articlePartObj->getObj('id = '.$id);
			if(!$this->articlePartRow)
			{
				IError::show(403,"章节信息不存在");
            }
            //获取文章信息
			$articleObj       = new IModel('article');
			$this->articleRow = $articleObj->getObj('id = '.$this->articlePartRow['aid']);
			if(!$this->articleRow)
			{
				IError::show(403,"内容大纲信息不存在");
            }
		}else{
            //获取文章信息
			$articleObj       = new IModel('article');
			$this->articleRow = $articleObj->getObj('id = '.$aid);
			if(!$this->articleRow)
			{
				IError::show(403,"内容大纲信息不存在");
            }
        }
		$this->redirect('article_part_edit');
    }
    
	function article_list()
	{
		$search = IFilter::act(IReq::get('search'),'strict');
		$where = ' 1 ';
		$condition = Util::search($search);
		$where .= $condition ? " and ".$condition : "";

        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("article as ar");
        $query->join = 'left join article_category as ac on ac.id = ar.category_id';
        $query->where  = $where ;//.' and c.status = 1';
        $query->fields = 'ar.*,ac.name';
        $query->page   = $page;
		$query->order   = 'ar.sort asc,ar.id desc';
		$this->query   = $query;
		$this->redirect('article_list');
	}

	function article_part_list()
	{
		$search = IFilter::act(IReq::get('search'),'strict');
		$where = ' 1 ';
		$condition = Util::search($search);
		$where .= $condition ? " and ".$condition : "";
        $aid = IFilter::act(IReq::get('aid'),'int');
        if($aid){
            $where.= " and r.aid=".$aid;
            $this->aid   = $aid;
        }
        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("article_part as r");
        $query->join = 'left join article as a on r.aid=a.id';
        $query->where  = $where ;//.' and c.status = 1';
        $query->fields = 'r.*,a.title as a_name';
        $query->page   = $page;
		$query->order   = 'r.aid desc,r.sort asc';
		$this->query   = $query;
		$this->redirect('article_part_list');
	}

	public function study_log(){
		$where       = Util::search(IReq::get('search'));
		$page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery("article_log as l");
		$join = 'left join member as m on m.user_id=l.user_id left join article as a on a.id=l.aid left join article_part as p on p.id=l.apid';
		$level = $this->admin['level'];
		if($level==0){
			$where = $where . ' and c.level=0 and c.area='.$this->admin['area'];
		}
		$join = $join . ' left join user_company as uc on uc.user_id = l.user_id left join company as c on c.id=uc.com_id ';
		$query->join = $join;
		$query->fields = 'DISTINCT a.title,p.title as p_title,m.true_name, l.*,c.name as c_name,m.nickname';
		$query->where  = '1 and '.$where;
		$query->order = 'l.last_read_time desc,l.id desc';
		$query->page   = $page;
		$this->query    = $query;
		$this->redirect('study_log');
	}

	public function importArticle(){
		$url  = IFilter::act(IReq::get('url'),'url');
		$aid  = IFilter::act(IReq::get('aid'),'int');
		$wxCrawler = new  WxCrawler();
		$wxar = $wxCrawler->crawByUrl($url);
		if(isset($wxar) && isset($wxar['title']) && $wxar['title']!=''){
			$content = $wxar['content_html'];
			$v = '';
			preg_match('/<video.*><\/video>/', $content, $matches, PREG_OFFSET_CAPTURE);
			if($matches && !empty($matches) && isset($matches[0])){
				$v = $matches[0][0];
				$content = str_replace($v,'[vvvvv]',$content);
			}
			preg_match('/<video[^>]*\/>/', $content, $matches, PREG_OFFSET_CAPTURE);
			if($matches && !empty($matches) && isset($matches[0])){
				$v = $matches[0][0];
				$content = str_replace($v,'[vvvvv]',$content);
			}
	
		   //替换video标签
		    $content = IFilter::act($content,'text2');
			if($v){
				$content = str_replace('[vvvvv]',$v,$content);
			}


			$articleObj = new IModel('article_part');
			$dataArray  = array(
				'title'       => $wxar['title'],
				'content'     => $content,
				'update_time' => ITime::getDateTime(),
				'keywords'    => $wxar['digest'],
				'description' => $wxar['digest'],
				'visibility'   => 1,
				'aid'   => $aid,
				'top'         => 0,
				'url'         => $url,
				'sort'        => 0,
				'wechatname'       => $wxar['wechatname'],
                'author'       => $wxar['author'],
				'create_time_v'   => ITime::getDateTime('Y-m-d',$wxar['date']),
			);
			$dataArray['create_time'] = $dataArray['update_time'];
			$articleObj->setData($dataArray);
			$id = $articleObj->add(true);
			die('<script type="text/javascript">parent.uploadSuccess(1);</script>');
		}else{
			die('<script type="text/javascript">parent.uploadFail("导入微信公众号文章失败");</script>');
		}
	}

	public function importArticle2(){
		$url  = IFilter::act(IReq::get('url'),'url');
        $cid  = IFilter::act(IReq::get('cid'),'int');
        $articleObj = new IModel('article_part');
        if($articleObj->getObj('url="'.$url.'"')){
			die('<script type="text/javascript">parent.uploadFail("导入微信公众号文章失败");</script>');
        }
		$wxCrawler = new  WxCrawler();
		$wxar = $wxCrawler->crawByUrl($url);
		if(isset($wxar) && isset($wxar['title']) && $wxar['title']!=''){
			$content = $wxar['content_html'];
			$v = '';
			preg_match('/<video.*><\/video>/', $content, $matches, PREG_OFFSET_CAPTURE);
			if($matches && !empty($matches) && isset($matches[0])){
				$v = $matches[0][0];
				$content = str_replace($v,'[vvvvv]',$content);
			}
			preg_match('/<video[^>]*\/>/', $content, $matches, PREG_OFFSET_CAPTURE);
			if($matches && !empty($matches) && isset($matches[0])){
				$v = $matches[0][0];
				$content = str_replace($v,'[vvvvv]',$content);
			}
	
		   //替换video标签
		    $content = IFilter::act($content,'text2');
			if($v){
				$content = str_replace('[vvvvv]',$v,$content);
			}
			$images = $wxar['images'];
		$articleObj = new IModel('article');
		$dataArray  = array(
            'title'       => $wxar['title'],
			'content'     => '',
			'category_id' => $cid,
			'update_time' => ITime::getDateTime(),
			'keywords'    => $wxar['digest'],
			'description' => $wxar['digest'],
			'visibility'   => 1,
			'top'         => 0,
			'sort'        => 0,
			'mintime'        => 3,
			'style'       => '',
			'color'       => '',
			'wechatname'       => $wxar['wechatname'],
            'author'       => $wxar['author'],
			'create_time_v'       => ITime::getDateTime(),
        );
        if(count($images)>0){
            $dataArray['img'] = $images[0];
        }
        $dataArray['create_time'] = $dataArray['update_time'];
			$articleObj->setData($dataArray);
			$aid = $articleObj->add();
            $is_success = $aid ? true : false;
            if($is_success)
		{
					//处理图片
            $photoRelationDB = new IModel('article_photo_relation');
            $photoRelationDB->del('aid = '.$aid);
            if(isset($images) && $images)
            {
                $photoDB = new IModel('photo');
                foreach($images as $key => $val)
                {
                    $photoPic = $photoDB->getObj('img = "'.$val.'"','id');
                    if($photoPic)
                    {
                        $photoRelationDB->setData(array('aid' => $aid,'photo_id' => $photoPic['id']));
                        $photoRelationDB->add();
                    }else{
						$photoPic = $photoDB->getObj('id = "'.IHash::md5($val).'"','id');
						if($photoPic)
						{
							$photoRelationDB->setData(array('aid' => $aid,'photo_id' => $photoPic['id']));
							$photoRelationDB->add();
						}else{
                        $photoDB->setData(array(
                            'id'=>IHash::md5($val),
                            'img'=>$val,
                            'create_time'=>ITime::getDateTime(),
                        ));
                        $photoDB->add();
                        $photoRelationDB->setData(array('aid' => $aid,'photo_id' => IHash::md5($val)));
						$photoRelationDB->add();
						}
                    }
                }
            }
		}
			$articleObj = new IModel('article_part');
			$dataArray  = array(
				'title'       => $wxar['title'],
				'content'     => $content,
				'update_time' => ITime::getDateTime(),
				'keywords'    => $wxar['digest'],
				'description' => $wxar['digest'],
				'visibility'   => 1,
				'aid'   => $aid,
				'top'         => 0,
				'url'         => $url,
				'sort'        => 0,
				'wechatname'       => $wxar['wechatname'],
                'author'       => $wxar['author'],
				'create_time_v'   => ITime::getDateTime('Y-m-d',$wxar['date']),
			);
			$dataArray['create_time'] = $dataArray['update_time'];
			$articleObj->setData($dataArray);
			$id = $articleObj->add(true);
			die('<script type="text/javascript">parent.uploadSuccess(1);</script>');
		}else{
			die('<script type="text/javascript">parent.uploadFail("导入微信公众号文章失败");</script>');
		}
	}

	public function import_word(){
		$path = IWeb::$app->getBasePath()."runtime/_study_import/";
		$upObj = new IUpload(10000,array('docx'));
		$upObj->setDir($path.date("Ymd"));
		$tempRes = $upObj->execute();
		if($tempRes && is_array($tempRes))
		{
    		$result = current(current($tempRes));
    		if($result['flag'] == 1)
    		{
    			$result['qFile'] = stripos($result['fileSrc'],"http") === false ? $result['fileSrc'] : IUrl::creatUrl('').$result['fileSrc'];
				$reportObj = new report('q');
				$ext = $result['ext'];
				if($ext == 'docx'){
					$values = $reportObj->importDoc($result);
					echo "<html><body>";
					echo "<div id='_word_html' style='display:none'>".$values."</idv>";
					echo '<script type="text/javascript">parent.uploadContent(document.getElementById("_word_html").innerHTML);</script>';
					die("</body></html>");
				}else{
					die('<script type="text/javascript">parent.uploadFail("模板错误");</script>');
				}
			}else{
				$error = $result['error'];
				die('<script type="text/javascript">parent.uploadFail("'.$error.'");</script>');
			}
		}else{
			die('<script type="text/javascript">parent.uploadFail("上传失败");</script>');
		}
	}
}