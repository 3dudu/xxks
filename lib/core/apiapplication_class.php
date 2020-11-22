<?php
/**
 * @copyright Copyright(c) 2015 aircheng.com
 * @file webapplication_class.php
 * @brief web应用类
 * @author nswe
 * @date 2015/10/26 18:35:19
 * @version 4.2
 */

/**
 * @brief IWebApplication 应用类
 * @class IWebApplication
 * @note
 */
class IApiApplication extends IApplication
{
	public $controller;               //当前控制器对象
	public $name = 'tbk';
	public $isAppCLient;
	public $clientVersion = '';
	public $isDevCLient = false;
	public $clientType;               //客户端类型, pc电脑, mobile手机
	public $app_dir = 'appserver';

    /**
     * @brief 构造函数
     * @param array or string $config 配置数组或者配置文件名称
     */
	public function __construct($config,$app_dir='')
	{
		parent::__construct($config);

		if(!$this->basePath)
		{
			if(isset($_SERVER['SCRIPT_FILENAME']))
			{
				$this->basePath = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
			}
			else
			{
				//document_root 不存在
				if(!isset($_SERVER['DOCUMENT_ROOT']))
				{
					if(isset($_SERVER['PATH_TRANSLATED']))
					{
						$_SERVER['DOCUMENT_ROOT'] = dirname($_SERVER['PATH_TRANSLATED']);
					}
				}
				$this->basePath = rtrim(rtrim($_SERVER['DOCUMENT_ROOT'],'\\/').dirname($_SERVER['SCRIPT_NAME']),'\\/').DIRECTORY_SEPARATOR;
			}
		}

		if(!$this->basePath)
		{
			throw new IException("the APP basePath illegal");
		}
		if($app_dir){
			$this->app_dir = $app_dir;
		}
		$this->clientType = IClient::getDevice();
		$this->isAppCLient = IClient::isAppCLient();
		$this->clientVersion = IClient::clientVersion();

		ini_set('default_charset','UTF-8');
		ini_set('upload_tmp_dir',$this->getRuntimePath());
		libxml_disable_entity_loader(true);
	}

    /**
     * @brief 请求执行方法，是application执行的入口方法
     */
    public function execRequest()
    {
        IUrl::beginUrl();
        $ctrlId   = IUrl::getInfo("controller");
		$actionId = IUrl::getInfo('action');
				
		$ver = IUrl::getInfo("ver");
		if($ver){
			$this->clientVersion = $ver;
		}

		IInterceptor::trigger("onBeforeCreateController",$ctrlId);
        $this->controller = $this->createController($ctrlId);
        IInterceptor::trigger("onCreateController",$this->controller);
        $this->controller->run($actionId);
		IInterceptor::trigger("onFinishController",$this->controller);
    }
    /**
     * @brief 创建当前的Controller对象
     * @param string $ctrlId 控制器ID
     * @return object Controller对象
     */
    public function createController($ctrlId)
    {
    	$ctrlId     = $ctrlId ? $ctrlId : $this->defaultController;
    	$ctrlObject = null;
    	$ctrlFile   = $this->basePath.$this->app_dir."/".$ctrlId.".php";
    	if(is_file($ctrlFile) && (class_exists($ctrlId) || include($ctrlFile)))
    	{
    		$ctrlObject = new $ctrlId($this,$ctrlId);
		}
        if($ctrlObject){
			return $ctrlObject;
		}else{
			header("content-type:application/json;charset=".IWeb::$app->charset);
			echo JSON::encode(array("ret_code"=>500,"ret_msg"=>'Controller not found'));
			exit;	
		} 
    }
    /**
     * @brief 取得当前的Controller
     * @return object Controller对象
     */
    public function getController()
    {
        return $this->controller;
	}
	
	    /**
     * @brief 获取当前WEB的运行URL路径
     * @return String 路径地址
     */
	public function getWebRunPath()
	{
		header("content-type:application/json;charset=".IWeb::$app->charset);
		echo JSON::encode(array("ret_code"=>500,"ret_msg"=>'This is APi Controller!'));
		exit;
	}

    /**
     * @brief 获取视图实际路径
     * @return String 实际路径
     */
	public function getViewPath()
	{
		header("content-type:application/json;charset=".IWeb::$app->charset);
		echo JSON::encode(array("ret_code"=>500,"ret_msg"=>'This is APi Controller!'));
		exit;
	}

    /**
     * @brief 获取当前WEB的模板URL路径
     * @return String 路径地址
     */
	public function getWebViewPath()
	{
		header("content-type:application/json;charset=".IWeb::$app->charset);
		echo JSON::encode(array("ret_code"=>500,"ret_msg"=>'This is APi Controller!'));
		exit;
	}

}