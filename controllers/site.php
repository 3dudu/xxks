<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file site.php
 * @brief
 * @author webning
 * @date 2011-03-22
 * @version 0.6
 * @note
 */
/**
 * @brief Site
 * @class Site
 * @note
 */
class Site extends IController
{
    public $layout='h5';

	function init()
	{

	}




	//文章详情页面
	function articleView()
	{
        //记录学习情况
        $user_id   = $this->user['user_id'];

        $aid = IFilter::act( IReq::get('id'),'int');
        $apid = IFilter::act( IReq::get('part_id'),'int');

        $articleObj    = new IModel('article as a,article_category as ac');
        $this->articleRow = $articleObj->getObj(' a.id='.$aid.' and a.category_id=ac.id','a.*,ac.name');
        if($this->articleRow){
            $articlePartObj       = new IModel('article_part');
            $articlePartRows = $articlePartObj->query(' aid = '.$aid,'*','sort asc');
            if($articlePartRows){
                foreach($articlePartRows as $part){
                    if($apid==$part['id']){
                        $part['content'] = IUrl::imgFullUrl($part['content']);
                        $this->articlePartRow = $part;
                    }
                }
                if(!$this->articlePartRow){
                    $articlePartRows[0]['content'] = IUrl::imgFullUrl($articlePartRows[0]['content']);
                    $this->articlePartRow = $articlePartRows[0];
                    $apid = $articlePartRows[0]['id'];
                }
            }
            Article::readLog($user_id,$aid,$apid);
            $this->redirect('articleView');
        }else{
			IError::show(403,'文章不存在');
        }
    }
    
    function help()
	{
		$id       = IFilter::act(IReq::get("id"),'int');
		$tb_help  = new IModel("help");
		$help_row = $tb_help->getObj($id);
		if($help_row)
		{

            $this->articlePartRow = array("title"=>$help_row['name']);
		    $this->help_row = $help_row;
		}

		if(!isset($help_row))
		{
			IError::show(403,"帮助信息不存在");
		}

		$this->redirect("help");
	}




}
