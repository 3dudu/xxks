<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file article.php
 * @brief 关于文章管理
 * @author chendeshan
 * @date 2011-02-14
 * @version 0.6
 */

 /**
 * @class article
 * @brief 文章管理模块
 */
class Article
{
	//显示标题
	public static function showTitle($title,$color=null,$fontStyle=null)
	{
		$str='<span style="';
		if($color!=null) $str.='color:'.$color.';';
		if($fontStyle!=null)
		{
			switch($fontStyle)
			{
				case "1":
				$str.='font-weight:bold;';
				break;

				case "2":
				$str.='font-style:oblique;';
				break;
			}
		}
		$str.='">'.$title.'</span>';
		return $str;
	}

	//显示标题
	public static function subTitle($str,$maxlen=40)
	{
		$len = IString::getStrLen($str);
		if($len>$maxlen){
			return IString::substr($str,40).'...';
		}
		return $str;
	}

	public static function readLog($user_id,$aid,$apid)
	{
		if(!$user_id || !$aid ){
			return;
		}
		$articlelogObj    = new IModel('article_log');
		$articleObj    = new IModel('article');
		$articlePartObj       = new IModel('article_part');
		$log = $articlelogObj->getObj('aid='.$aid.' and apid='.$apid.' and user_id='.$user_id);
		$logid = 0;
		if($log){
			$logid = $log['id'];
			//阅读次数越多，下次计次越久
			if(ITime::getDiffSec(ITime::getDateTime(),$log['last_read_time'])>0*$log['read']){
				//更新
				$articlelogObj->setData(array(
					'last_read_time'=>ITime::getDateTime(),
					'read'=>'`read` + 1'
				));
				$articlelogObj->update('id='.$log['id'],'read');

				$articleObj->setData(array(
					'article_readnum'=>'article_readnum + 1'
				));
				$articleObj->update('id='.$aid,array('article_readnum'));

				$articlePartObj->setData(array(
					'article_readnum'=>'article_readnum + 1'
				));
				$articlePartObj->update('id='.$apid,array('article_readnum'));
			}
		}else{
			$articlelogObj->setData(array(
				'read_time'=>ITime::getDateTime(),
				'last_read_time'=>ITime::getDateTime(),
				'aid'=>$aid,
				'user_id'=>$user_id,
				'apid'=>$apid,
				'read'=>1
			));
			$logid = $articlelogObj->add();

			$articleObj->setData(array(
				'article_readnum'=>'article_readnum + 1'
			));
			$articleObj->update('id='.$aid,array('article_readnum'));

			$articlePartObj->setData(array(
				'article_readnum'=>'article_readnum + 1'
			));
			$articlePartObj->update('id='.$apid,array('article_readnum'));
		}
		return $articlelogObj->getObj('id='.$logid);
	}

	public static function like($user_id,$aid,$apid){
		if(!$user_id || !$aid ){
			return;
		}
		$articlelogObj    = new IModel('article_log');
		$log = $articlelogObj->getObj('aid='.$aid.' and apid='.$apid.' and user_id='.$user_id);
		if($log){
			$articleObj    = new IModel('article');
			$articlePartObj       = new IModel('article_part');
			if($log['like']==1){
				$articlelogObj->setData(array(
					'like'=>'`like`-1'
				));
				$articlelogObj->update('id='.$log['id'],array('like'));
				$articleObj->setData(array(
					'article_likenum'=>'article_likenum-1'
				));
				$articleObj->update('id='.$aid,array('article_likenum'));
	
				$articlePartObj->setData(array(
					'article_likenum'=>'article_likenum-1'
				));
				$articlePartObj->update('id='.$apid,array('article_likenum'));
			}else{
				$articlelogObj->setData(array(
					'like'=>'`like`+1'
				));
				$articlelogObj->update('id='.$log['id'],array('like'));
				$articleObj->setData(array(
					'article_likenum'=>'article_likenum+1'
				));
				$articleObj->update('id='.$aid,array('article_likenum'));
	
				$articlePartObj->setData(array(
					'article_likenum'=>'article_likenum+1'
				));
				$articlePartObj->update('id='.$apid,array('article_likenum'));
			}
		}
	}
}