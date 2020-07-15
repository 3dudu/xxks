<?php
$iweb = dirname(__FILE__)."/lib/iweb.php";
$config = dirname(__FILE__)."/config/config.php";
defined('VENDOR_PATH') or define('VENDOR_PATH',dirname(__file__).DIRECTORY_SEPARATOR);
require($iweb);
IWeb::createWebApp($config)->run();
?>