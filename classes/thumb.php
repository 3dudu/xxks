<?php
/**
 * @brief 动态生成缩略图类
 */
class Thumb
{
	//缩略图路径
	public static $thumbDir = "runtime/_thumb/";

	/**
	 * @brief 获取缩略图物理路径
	 */
	public static function getThumbDir()
	{
		return IWeb::$app->getBasePath().self::$thumbDir;
	}

	/**
	 * @brief 生成缩略图
	 * @param string $imgSrc 图片路径
	 * @param int $width 图片宽度
	 * @param int $height 图片高度
	 * @return string WEB图片路径名称
	 */
    public static function get($imgSrc,$width=0,$height=0)
    {
		//远程图片
		$imgSrc = trim($imgSrc);
		$imgSrc = trim($imgSrc,'');
		$imgSrc = trim($imgSrc,'�');
		if(stripos($imgSrc,"http") !== false)
		{
			// 第三方缩略图地址
			$thumb_url = plugin::trigger("get_thumb", $imgSrc, $width, $height);
			if (false !== $thumb_url)
			{
				return $thumb_url;
			}
			
			//根据URL生成要保存的唯一路径

			$file_md5 = IHash::md5($imgSrc);
			$downFile = self::getThumbDir()."remote/".$file_md5;
			$dirname = 'remote';
			//如果系统不存在此路径则直接下载
			if(!is_file($downFile))
			{
				$subdir = substr($file_md5,0,2);
				$downFile = self::getThumbDir()."remote/".$subdir.'/'.$file_md5;
				$dirname = 'remote/'.$subdir;

				if(!is_file($downFile)){
					$ch = curl_init($imgSrc);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
					$fileRes = new IFile($downFile,"w+");
					$result  = $fileRes->write(curl_exec($ch));
					if(!$result)
					{
						throw new IException($downFile." download fail");
					}
				}

			}
			$sourcePath = $downFile;
		}
		//本地图片
		else
		{
			$sourcePath = IWeb::$app->getBasePath().$imgSrc;
			if(is_file($sourcePath) == false)
			{
				return;
			}
			$dirname = dirname($imgSrc);
		}

		//缩略图文件名
		$preThumb      = "{$width}_{$height}_";
		$thumbFileName = $preThumb.basename($sourcePath);

		//缩略图目录
		$thumbDir    = self::getThumbDir().trim($dirname,"/")."/";
		$webThumbDir = self::$thumbDir.trim($dirname,"/")."/";
		if(is_file($thumbDir.$thumbFileName) == false)
		{
			IImage::thumb($sourcePath,$width,$height,$preThumb,$thumbDir);
		}
		return $webThumbDir.$thumbFileName;
    }
}