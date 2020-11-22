<?php
/**
 * @brief 学习接口
 * @class Study
 * 接口 访问路径
 *  http://iweb.goat.msns.cn:5088/api/study/studyList
 */
class Study extends IApiController
{
	public function init()
	{
		$this->cacheAble = true;
		$this->cacheActions = array(
			'home'=>600,
			'studyDetail'=>60,
			//'cateList'=>600,
        );
        plugin::reg("onBeforeCreateAction@study@likeArticle",function(){
            plugin::trigger('checkUserRights','likeArticle');
        });
        plugin::reg("onBeforeCreateAction@study@addFav",function(){
            plugin::trigger('checkUserRights','addFav');
        });
        plugin::reg("onBeforeCreateAction@study@submitStudy",function(){
            plugin::trigger('checkUserRights','submitStudy');
        });
        plugin::reg("onBeforeCreateAction@study@delFav",function(){
            plugin::trigger('checkUserRights','delFav');
        });
        plugin::reg("onBeforeCreateAction@study@favList",function(){
            plugin::trigger('checkUserRights','favList');
        });

    }

    public function home(){
        //banner
        $bannerObj = new IModel('banner');
	    $bannerLsit =  $bannerObj->query("type = 'mobile'");
        $user_id   = $this->user['user_id'];

        //一级学习分类
        $catObj    = new IModel('article_category');
        $cateList = $catObj->query('cat_type=1 and parent_id=0 and issys=0','*','sort asc');
        $page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

        //文章
        $query = new IQuery('article as a ');
        $where = 'a.visibility=1';
        $where .= ' and c.cat_type= 1';
        $query->where = $where;
        $query->join = "left join article_category AS c ON c.id = a.category_id";
        $query->order = 'a.sort asc,a.create_time_v desc,a.id desc';
        $query->fields = 'a.*,c.name';
        $query->page = $page;
        $studyList = $query->find();

        $articlelogObj    = new IModel('article_log');

        foreach($studyList as &$val){
            if($user_id){
                $log = $articlelogObj->query('aid='.$val['id'].' and user_id='.$user_id,'*','study desc',1);
                if($log){
                    $val['log'] = current($log);
                }
            }
            if($val['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$val['id'];
                $tb_article_photo->order = 'ghr.id asc';
                $val['photo'] = $tb_article_photo->find();
            }else{
                $val['photo'] = array();
            }
        }

        $paging = $query->paging;

        $this->setRenderData(array('bannerLsit'=>$bannerLsit,'topCat'=>$cateList,'studyList'=>$studyList,'paging'=>$paging));
    }

    public function loadMoreStudy(){
        $cateid = IFilter::act(IReq::get('cid'),'int');
        $user_id   = $this->user['user_id'];

        $page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery('article as a ');
        $where = 'a.visibility=1';
        $where .= ' and c.cat_type= 1';
        if($cateid){
            $where .= ' and a.category_id= '.$cateid;
        }
        $query->where = $where;
        $query->join = "left join article_category AS c ON c.id = a.category_id";
        $query->order = 'a.sort asc,a.create_time_v desc,a.id desc';
        $query->fields = 'a.*,c.name';
        $query->page = $page;
        $studyList = $query->find();
        $paging = $query->paging;
        $articlelogObj    = new IModel('article_log');
        foreach($studyList as &$val){
            if($user_id){
                $log = $articlelogObj->query('aid='.$val['id'].' and user_id='.$user_id,'*','study desc',1);
                if($log){
                    $val['log'] = current($log);
                }
            }
            if($val['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$val['id'];
                $tb_article_photo->order = 'ghr.id asc';
                $val['photo'] = $tb_article_photo->find();
            }else{
                $val['photo'] = array();
            }
        }

        $this->setRenderData(array('studyList'=>$studyList,'paging'=>$paging));
    }

    public function searchStudy(){
        $cateid = IFilter::act(IReq::get('cid'),'int');
        $keywords = IFilter::act(IReq::get('keywords'));
        $user_id   = $this->user['user_id'];

        $page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery('article_part as ap ');
        $where = 'ap.visibility=1 and c.cat_type= 1 and a.visibility=1';
        if($cateid){
            $where .= ' and a.category_id= '.$cateid;
        }
        $where .= ' and (';
        $where .= 'a.title like "%'.$keywords.'%" ';
        $where .= 'or a.keywords like "%'.$keywords.'%" ';
        $where .= 'or a.description like "%'.$keywords.'%" ';
        $where .= 'or ap.title like "%'.$keywords.'%" ';
        $where .= 'or ap.description like "%'.$keywords.'%" ';
        $where .= ' ) ';

        $query->where = $where;
        $query->join = "left join article as a on ap.aid=a.id left join article_category AS c ON c.id = a.category_id";
        $query->order = 'a.sort asc,a.create_time_v desc,a.id desc';
        $query->fields = 'a.wechatname,a.author,a.id,a.title,a.description,a.img,a.article_readnum,a.article_likenum,a.create_time_v,c.name as cat_name,ap.title as ap_title,ap.id as ap_id,ap.description as ap_description';
        $query->page = $page;
        $studyList = $query->find();
        $paging = $query->paging;
        $articlelogObj    = new IModel('article_log');

        foreach($studyList as &$val){
            if($user_id){
                $log = $articlelogObj->query('aid='.$val['id'].' and apid='.$val['ap_id'] .' and user_id='.$user_id,'*','study desc',1);
                if($log){
                    $val['log'] = current($log);
                }
            }

            if($val['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$val['id'];
                $tb_article_photo->order = 'ghr.id asc';
                $val['photo'] = $tb_article_photo->find();
            }
        }

        $this->setRenderData(array('studyList'=>$studyList,'paging'=>$paging));
    }

    //无需分页
    public function cateList(){
        $pid = IFilter::act( IReq::get('pid'),'int');
        $where = '1';
            //拼接分类条件
        $where .= ' and cat_type=1 and issys=0 and parent_id='.$pid;
        $catObj    = new IModel('article_category');
        $cateList = $catObj->query($where,'*','sort asc');
        return $cateList;
    }

    //学习内容详情，分页
    public function studyDetail(){
        //记录学习情况
        $user_id   = $this->user['user_id'];

        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');

        $articleObj    = new IModel('article as a,article_category as ac');
        $article = $articleObj->getObj('a.visibility=1 and a.id='.$aid.' and a.category_id=ac.id','a.*,ac.name');
        if($article){
            $articlePartObj       = new IModel('article_part');
            if($apid){
                $articlePart = $articlePartObj->getObj('visibility=1 and aid = '.$aid.' and id='.$apid);
                if($articlePart){
                    $article['cur_part'] = $articlePart;
                }else{
                    $articlePartRow = $articlePartObj->query('visibility=1 and aid = '.$aid,'*','sort asc',1);
                    if($articlePartRow){
                        $article['cur_part'] = $articlePartRow[0];
                        $apid = $articlePartRow[0]['id'];
                    }
                }
            }else{
                $articlePartRow = $articlePartObj->query('visibility=1 and aid = '.$aid,'*','sort asc',1);
                if($articlePartRow){
                    $article['cur_part'] = $articlePartRow[0];
                    $apid = $articlePartRow[0]['id'];
                }
            }

            $articlePartRow = $articlePartObj->query('visibility=1 and aid = '.$aid,'id,aid,title,create_time_v,description,article_readnum_v,article_likenum_v','sort asc');
            if($articlePartRow){
                $article['partList'] = $articlePartRow;
                $log = Article::readLog($user_id,$aid,$apid);
                $article['log'] = $log;
                $article['cur_part']['content'] = $this->htmlTransform($article['cur_part']['content']);
            }
            if($article['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$article['id'];
                $tb_article_photo->order = 'ghr.id asc';
                $article['photo'] = $tb_article_photo->find();
            }else{
                $article['photo'] = array();
            }
            return $article;
        }else{
            $this->setError('文章不存在');
        }
    }

    public function htmlTransform($string)
    {
        //  $string = str_replace('&quot;','"',$string);
          $string = str_replace('&amp;','&',$string);
        //  $string = str_replace('amp;','',$string);
        //  $string = str_replace('&lt;','<',$string);
        //  $string = str_replace('&gt;','>',$string);
        //  $string = str_replace('&nbsp;',' ',$string);
          $string = str_replace("\\", '',$string);
          $string = IUrl::imgFullUrl($string);
          return $string;
    }

    public function likeArticle(){
        $user_id   = $this->user['user_id'];

        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');

        Article::like($user_id,$aid,$apid);
        return;
    }

    public function submitStudy(){
        $user_id   = $this->user['user_id'];
        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');
        $articleDB    = new IModel('article as a,article_log as al');
        $article = $articleDB->getObj('a.id='.$aid.' and a.id=al.aid and ( al.apid='.$apid.' or '.$apid.'=0)','a.title,a.mintime,al.*');
        if($article){
			//if(ITime::getDiffSec(ITime::getDateTime(),$article['last_study_time'])>60*$article['study']){
                $event = plugin::trigger('event',array('event'=>'study','user_id'=>$user_id));
                if(!empty($event)){
                    $articlelogObj    = new IModel('article_log');
                    $articlelogObj->setData(array(
                        'last_study_time'=>ITime::getDateTime(),
                        'study'=>'`study` + 1'
                    ));
                    $articlelogObj->update('id='.$article['id'],'study');
                }
                return $event;
            //}else{
            //    $this->setError('相同文章学习间隔太短');
            //}
        }else{
            $this->setError('文章不存在');
        }
    }

    public function addFav(){
        $user_id   = $this->user['user_id'];
        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');
        $articlelogObj    = new IModel('article_log');
        $articlelogObj->setData(array(
            'fav'=>1,
            'fav_time'=>ITime::getDateTime(),
        ));
        $ret = $articlelogObj->update('user_id='.$user_id.' and aid='.$aid.' and apid='.$apid);
        if(!$ret){
            Article::readLog($user_id,$aid,$apid);
            $articlelogObj->update('user_id='.$user_id.' and aid='.$aid.' and apid='.$apid);
        }
    }
    public function delFav(){
        $user_id   = $this->user['user_id'];
        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');
        $articlelogObj    = new IModel('article_log');
        $articlelogObj->setData(array(
            'fav'=>0,
        ));
        $articlelogObj->update('user_id='.$user_id.' and aid='.$aid.' and apid='.$apid);
    }
    public function favList(){
        $user_id   = $this->user['user_id'];
        $page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery('article_log as l');
        $query->join = 'left join article as a on l.aid=a.id left join article_part as ap on ap.id=l.apid';
        $query->fields = 'a.wechatname,a.author,l.id,a.img,l.aid,l.apid,l.fav,l.fav_time,a.title,a.visibility,a.article_readnum,a.article_likenum,a.create_time_v,ap.title as p_title,ap.visibility as p_visibility,a.description,ap.description as p_description';
        $query->page = $page;
        $query->where = 'l.user_id='.$user_id.' and l.fav=1';
        $query->order = 'l.fav_time desc';
        $favList = $query->find();
        foreach($favList as &$val){
            if($val['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$val['aid'];
                $tb_article_photo->order = 'ghr.id asc';
                $val['photo'] = $tb_article_photo->find();
            }
        }
        $paging = $query->paging;
        $this->setRenderData(array('favList'=>$favList,'paging'=>$paging));
    }

    
	public function importArticle(){
		$url  = IFilter::act(IReq::get('url'),'url');
        $cid  = IFilter::act(IReq::get('cid'),'int');
        $articleObj = new IModel('article_part');
        if($articleObj->getObj('url="'.$url.'"')){
            return;
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

        $catObj    = new IModel('article_category');
        $cateList = $catObj->query('cat_type=1 and parent_id=0 and issys=0','*','sort asc');
        $title = $wxar['title'];
        $title = strtolower($title);
        foreach($cateList as $cat){
            $keywords = $cat['keywords'];
            if($keywords){
                $keywords = strtolower($keywords);
                $keywords = explode(",",$keywords);
                if($keywords){
                    foreach($keywords as $key){
                        if(strpos($title,$key)!==false){
                            $cid = $cat['id'];
                            break;
                        }
                    }
                }
            }
        }
            
		$articleObj = new IModel('article');
		$dataArray  = array(
            'title'       => $wxar['title'],
			'content'     => '',
			'category_id' => $cid,
			'update_time' => ITime::getDateTime(),
			'keywords'    => $wxar['digest'],
			'description' => $wxar['digest'],
			'visibility'   => 1,
			'top'         => 1,
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
                    $val = IFilter::act($val);
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
			return;
		}else{
			return;
		}
	}

}