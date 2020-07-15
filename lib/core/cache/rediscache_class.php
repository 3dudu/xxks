<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class IRedisCache implements ICacheInte {
    private $handler;
    private $options;
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        $options = array_merge(array (
            'host'          => isset(IWeb::$app->config['cache']['redis_server']) ? IWeb::$app->config['cache']['redis_server'] : '127.0.0.1',
            'port'          => isset(IWeb::$app->config['cache']['redis_port']) ? IWeb::$app->config['cache']['redis_port'] : 6379,
            'timeout'       => isset(IWeb::$app->config['cache']['redis_timeout']) ? IWeb::$app->config['cache']['redis_timeout'] : false,
            'persistent'    => false,
        ),$options);

        $this->options =  $options;
        $_expire    = isset(IWeb::$app->config['cache']['expire'])  ? IWeb::$app->config['cache']['expire']  : 0;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   $_expire;
        $pre = IWeb::$app->config['cache']['redis_prefix'].'_'.IWeb::$app->name.'_';
        $this->options['prefix'] =  isset($options['prefix']) && $options['prefix'] ?  $pre.$options['prefix'].'_'  :   $pre;        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;  
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        if($this->handler === null){
            $this->handler  = new \Redis;
            $options['timeout'] === false ?
                $this->handler->$func($options['host'], $options['port']) :
                $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name,$expire = 0) {
        if(is_int($expire) && $expire) {
            $this->handler->setTimeout($this->options['prefix'].$name,$expire);
        }
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = JSON::decode( $value );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? JSON::encode($value) : $value;
        if(is_int($expire) && $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function del($name,$timeout = '') {
        return $this->handler->del($this->options['prefix'].$name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function flush($prefix='') {
        $keys = $this->handler->keys($this->options['prefix'].$prefix.'*');
        return $this->handler->del($keys);
    }

}
