<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file controller_class.php
 * @brief 控制器类,控制action动作,渲染页面
 * @author chendeshan
 * @date 2010-12-16
 * @update 2016/4/13 18:29:42
 * @version 4.4
 */

/**
 * @class IController
 * @brief 控制器
 */
class IApiController extends IControllerBase
{
	public $defaultActions = array();                  //默认action对应关系,array(ID => 类名或对象引用)
	public $error          = array();                  //错误信息内容
	public $versionActions          = array();                  //错误信息内容

	protected $app;                                    //隶属于APP的对象
	protected $ctrlId;                                 //控制器ID标识符

	private $action;                                   //当前action对象
	private $defaultAction       = 'index';            //默认执行的action动作
	private $renderData          = array();            //渲染的数据
	public $cacheAble = false;
	public $cacheActions = array();
	private $defaultCache;
	public $dataFormat = 'json';
	public $disableAction          = array();                  //错误信息内容
	public $actionDisable = false;


	/**
	 * @brief 构造函数
	 * @param string $app    上一级APP对象
	 * @param string $ctrlId 控制器ID标识符
	 */
	public function __construct($app,$controllerId)
	{
		$this->app    = $app;
		$this->ctrlId = $controllerId;
		$this->defaultCache = $this->spicalCache();
		if(!$this->defaultCache){
			$this->defaultCache = new ICache('redis','API');
		}
	}

		
	public function getDataFormat()
	{
		return $this->dataFormat;
	}

	public function spicalCache(){
		return false;
	}

	public function redirect($nextUrl, $location = true, $data = null){
		$this->showError("session timeout!",99999);
	}
	
	public function getActionVersion($actId){
		if(isset($this->versionActions[$actId]))
		{
			$version = $this->app->clientVersion;
			$versions = $this->versionActions[$actId];
			if(is_array($versions) &&  in_array($version,$versions)){
				return str_replace('.','_',$version);
			}
		}
		return '';
	}

	/**
	 * @brief 获取当前控制器的id标识符
	 * @return 控制器的id标识符
	 */
	public function getId()
	{
		return $this->ctrlId;
	}

	/**
	 * @brief 初始化controller对象
	 */
	public function init()
	{
	}

	/**
	 * @brief 获取当前action对象
	 * @return object 返回当前action对象
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @brief 执行action方法
	 * @param string $actionId 动作actionID
	 */
	public function run($_actionId = '')
	{
		//开启缓冲区
		ob_start();

		header("content-type:application/json;charset=".$this->app->charset);

		//初始化控制器
		$this->init();

		//创建action对象
		IInterceptor::trigger("onBeforeCreateAction",$this,$_actionId);
		//获取action的标识符
		$actionId = $_actionId ? $_actionId : $this->defaultAction;
		$version = $this->getActionVersion($actionId);
		if($version){
			$actionId = $actionId.'_'.$version;
		}

		if($this->actionDisable && in_array($actionId,$this->disableAction)){
			$this->showError("action disabled!",403);
			return;
		}

		$actionObj = $this->createAction($actionId);
		if($actionObj){
			IInterceptor::trigger("onCreateAction",$this,$actionObj);
			$ifCache = false;
			if($this->cacheAble && isset($this->cacheActions[$actionId])){
				//读取缓存
				$param = $_GET;
				ksort($param);
				reset($param);
				$user_id   = IFilter::act($this->user['user_id'],'int');

				$cacheKey = $user_id.'_'.$actionId.'_'.md5(http_build_query($param).$actionId);
				$renderData = $this->defaultCache->get($cacheKey);
				if($renderData){
					$ifCache = true;
					echo JSON::encode(array("ret_code"=>0,"ret_msg"=>'SUCCESS',"ret_data"=>$renderData,"cacheKey"=>$cacheKey));
				}
			}
			if(!$ifCache){
				$rdata = $actionObj->run();
				//输出。写缓存
				if($rdata!==null){
					if(isset($rdata['errorNo'])){
						$this->setError($rdata['desc'],$rdata['errorNo']);
					}else{
						$this->setRenderData(array($_actionId=>$rdata));
					}
				} 

				$error = $this->getAllError();
				$renderData = $this->getRenderData();
				if($error){
					//有错
					//获取配置的错误描述
					$errorConfigObj = new Config("error_config");
					$errorConfig = $errorConfigObj->getInfo();
					foreach($this->error as $key=>$val){
						if(isset($errorConfig[$val['errorNo']])){
							$configError = $errorConfig[$val['errorNo']];
							$configError['errorNo'] = $val['errorNo'];
							$this->error[$key] = $configError;
						}
					}
					$configError = current($this->error);
					if($renderData){
						echo JSON::encode(array("ret_code"=>$configError['errorNo'],"ret_msg"=>$configError['desc'],"ret_error"=>$this->error,"ret_data"=>$renderData));
					}else{
						echo JSON::encode(array("ret_code"=>$configError['errorNo'],"ret_msg"=>$configError['desc'],"ret_error"=>$this->error));
					}
				}else{
					if($renderData){
						if($this->cacheAble && isset($this->cacheActions[$actionId])){
							//读取缓存
							$param = $_GET;
							ksort($param);
							reset($param);
							$user_id   = IFilter::act($this->user['user_id'],'int');

							$cacheKey = $user_id.'_'. $actionId.'_'.md5(http_build_query($param).$actionId);
							$this->defaultCache->set($cacheKey,$renderData,$this->cacheActions[$actionId]);
						}
						echo JSON::encode(array("ret_code"=>0,"ret_msg"=>'SUCCESS',"ret_data"=>$renderData));
					}else{
						echo JSON::encode(array("ret_code"=>0,"ret_msg"=>'SUCCESS'));
					}
				}
			}

			IInterceptor::trigger("onFinishAction",$this,$actionObj);
		}else{
			$this->showError("action not found!",404);
		}
		//处理缓冲区
		ob_end_flush();
	}

	public function showError($httpNum = 403,$errorData = "")
	{
		//参数的次序颠倒
		if(is_numeric($errorData))
		{
			list($httpNum,$errorData) = array($errorData,$httpNum);
		}
		else if(is_string($httpNum) && !$errorData)
		{
			list($httpNum,$errorData) = array(403,$httpNum);
		}

		$errorConfigObj = new Config("error_config");
		$errorConfig = $errorConfigObj->getInfo();
		$defaultError = array('desc'=>$errorData,'cause'=>'未知错误原因');
		if(isset($errorConfig[$httpNum])){
			$defaultError = $errorConfig[$httpNum];
			$errorData = $defaultError['desc'];
		}

		header("content-type:application/json;charset=".IWeb::$app->charset);
		echo JSON::encode(array("ret_code"=>$httpNum,"ret_msg"=>$errorData,"ret_error"=>$defaultError));
		exit;
	}

	/**
	 * @brief 创建action动作
	 * @param string $actionId 动作actionID
	 * @return object 返回action动作对象
	 */
	public function createAction($actionId = '')
	{
		/*创建action对象流程
		 *1,配置动作
		 *2,控制器内部动作
		 *3,视图动作*/

		//1,配置动作
		if(isset($this->defaultActions[$actionId]))
		{
			//自定义类名
			$class = $this->defaultActions[$actionId];
			$this->action = is_object($class) ? $class : new $class($this,$actionId);
		}
		//2,控制器内部动作
		else if(method_exists($this,$actionId) || is_callable($this->$actionId))
		{
			$this->action = new IInlineAction($this,$actionId);
		}
		return $this->action;
	}


	/**
	 * @brief 渲染出静态文字
	 * @param string $text 要渲染的静态数据
	 * @param bool $return 输出方式 值: true:返回; false:直接输出;
	 * @return string 静态数据
	 */
	public function renderText($text,$return=false)
	{
		$text = $this->tagResolve($text);
		if($return)
		{
			return $text;
		}
		echo $text;
		exit;
	}

	/**
	 * @brief 获取要渲染的数据
	 * @return array 渲染的数据
	 */
	public function getRenderData()
	{
		return $this->renderData;
	}

	function object_to_array($obj) {
		$obj = (array)$obj;
		foreach ($obj as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array') {
				$obj[$k] = (array)$this->object_to_array($v);
			}
		}
	 
		return $obj;
	}

	/**
	 * @brief 设置要渲染的数据
	 * @param array $data 渲染的数据数组
	 */
	public function setRenderData($data)
	{
		if(!is_array($data)){
			$data = $this->object_to_array($data);
		}
		$this->renderData = array_merge($this->renderData,$data);
	}

	/**
	 * @brief 设置错误信息
	 * @param string $errorMsg 错误信息内容
	 * @param string $errorNo  错误信息编号
	 */
	public function setError($errorMsg,$errorNo = 500)
	{
		$this->error[] = array('errorNo'=>$errorNo,'desc'=>$errorMsg);
	}

	/**
	 * @brief 获取单条错误信息
	 * @return string 错误信息内容
	 */
	public function getError()
	{
		return $this->error ? current($this->error) : "";
	}

	/**
	 * @brief 获取全部错误信息
	 * @return array 全部错误信息内容
	 */
	public function getAllError()
	{
		return $this->error;
	}
}
