<?php
/**
 * @brief 新闻专题接口
 * @class News
 * 接口 访问路径
 */
class News extends IApiController
{
	public function init()
	{

    }

    public function home(){
        //banner
        $bannerObj = new IModel('banner');
	    $bannerLsit =  $bannerObj->query("type = 'pc'");

        //一级学习分类
        $catObj    = new IModel('article_category');
        $cateList = $catObj->query('cat_type=2 and parent_id=0 and issys=0 ','*','sort asc');
        $cateList;

        //文章
        $query = new IQuery('article as a ');
        $where = 'a.visibility=1 and a.top=1';
        $where .= ' and c.cat_type= 2';
        $query->where = $where;
        $query->join = "left join article_category AS c ON c.id = a.category_id";
        $query->order = 'a.sort asc';
        $query->fields = 'a.*,c.name';
        $query->page = 1;
        $studyList = $query->find();

        foreach($studyList as &$val){
            if($val['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$val['id'];
                $tb_article_photo->order = 'ghr.id asc';
                $val['photo'] = $tb_article_photo->find();
            }
        }

        $paging = $query->paging;

        $this->setRenderData(array('bannerLsit'=>$bannerLsit,'topCat'=>$cateList,'news'=>$studyList,'paging'=>$paging));
    }


    public function loadMoreNews(){
        $cateid = IFilter::act(IReq::get('cid'),'int');

        $page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery('article as a ');
        $where = 'a.visibility=1';
        $where .= ' and c.cat_type= 2';
        if($cateid){
            $where .= ' and a.category_id= '.$cateid;
        }
        $query->where = $where;
        $query->join = "left join article_category AS c ON c.id = a.category_id";
        $query->order = 'a.sort asc';
        $query->fields = 'a.*,c.name';
        $query->page = $page;
        $studyList = $query->find();
        $paging = $query->paging;

        foreach($studyList as &$val){
            if($val['img']){
                $tb_article_photo = new IQuery('article_photo_relation as ghr');
                $tb_article_photo->join = 'left join photo as gh on ghr.photo_id=gh.id';
                $tb_article_photo->fields = 'gh.img';
                $tb_article_photo->where = 'ghr.aid='.$val['id'];
                $tb_article_photo->order = 'ghr.id asc';
                $val['photo'] = $tb_article_photo->find();
            }
        }

        $this->setRenderData(array('news'=>$studyList,'paging'=>$paging));
    }



    //无需分页
    public function cateList(){
        $pid = IFilter::act( IReq::get('pid'),'int');
        $where = '1';
            //拼接分类条件
        $where .= ' and cat_type=2 and issys=0 and parent_id='.$pid;
        $catObj    = new IModel('article_category');
        $cateList = $catObj->query($where);
        return $cateList;
    }

    //学习内容详情，分页
    public function newDetail(){
        //记录学习情况
        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');

        $articleObj    = new IModel('article as a,article_category as ac');
        $article = $articleObj->getObj('a.visibility=1 and a.id='.$aid.' and a.category_id=ac.id','a.*,ac.name');
        if($article){
            $articlePartObj       = new IModel('article_part');
            $articlePartRow = $articlePartObj->query('aid = '.$aid,'*','sort asc');
            if($articlePartRow){
                foreach($articlePartRow as $part){
                    if($apid==$part['id']){
                        $article['cur_part'] = $part;
                    }
                }
                if(!isset($article['cur_part'])){
                    $article['cur_part'] = $articlePartRow[0];
                }
                $article['partList'] = $articlePartRow;
            }
            return $article;
        }else{
            $this->setError('文章不存在');
        }

    }


}