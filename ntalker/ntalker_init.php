<?php
@set_magic_quotes_runtime (0);
define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if(!defined('APP_ROOT')) {
        // 网站URL根目录
        $_root = dirname(_PHP_FILE_);
        $_root = (($_root=='/' || $_root=='\\')?'':$_root);
        $_root = str_replace("/system","",$_root);
        define('APP_ROOT', $_root  );
}

if(!defined('APP_ROOT_PATH')) 
define('APP_ROOT_PATH', str_replace('ntalker/ntalker_init.php', '', str_replace('\\', '/', __FILE__)));

define('KEY','domyself');//定义ntalker的私有KEY
$filename = APP_ROOT_PATH."public/ntalker_config.php";
$cache_tag = false;
require $filename;
//好友列表入口浮动开关（logo模式）
$im_float = true;     //true:启用浮动方式；false：启用固定方式
//是否使用ntalker sitekey验证
$enablesitekey = true;     //true:使用；false：不使用
//是否显示ntalker自定义的用户信息,true:显示；false：不显示
$isshowprofile = false;
//是否将用户使用ntalker获得的积分转换为网站积分,true:开启转换；false：关闭转换
$issyncmoney = false;
//用户使用ntalker获得的积分与网站积分的兑换率，也就是多少ntalker积分兑换网站的1个积分
$moneyrate = 1;
//是否向ntalker服务器推送网站最新动态，ture：推送；false：不推送
$im_enablefeedactivity = true;
//ntalker接收推送动态的服务器地址
$im_feedurl = "http://active.ntalker.com/reportactivity.php";
//是否装载自定义配置脚本，ture：装载；false：不装载
$im_enableLoadConfig = false;
//－－－－－－－－－－－－－－以下配置无需修改－－－－－－－－－－－－－－－－－
$im_wdkresource_server = "download.ntalker.com/res";
$im_imfunction_js = "http://".$im_wdkresource_server."/imfunction_utf8.js";
//－－－－－－－－－－－－－－以上配置无需修改－－－－－－－－－－－－－－－－－
//接口文件优化系数 high：性能优先；low：扩展性优先
$imxmlperf = "high";

//引入数据库的系统配置及定义配置函数
$sys_config = require APP_ROOT_PATH.'system/config.php';
function app_conf($name)
{
	return stripslashes($GLOBALS['sys_config'][$name]);
}
$_table = app_conf('DB_PREFIX');
//end 引入数据库的系统配置及定义配置函数

//引入时区配置及定义时间函数
if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(app_conf('DEFAULT_TIMEZONE'));
//end 引入时区配置及定义时间函数

//定义缓存
require APP_ROOT_PATH.'system/cache/Cache.php';
$cache = CacheService::getInstance();
//end 定义缓存

//定义DB
require APP_ROOT_PATH.'system/db/db.php';
define('DB_PREFIX', app_conf('DB_PREFIX')); 
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/db_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/app/db_caches/',0777);
$pconnect = false;
$db = new mysql_db(app_conf('DB_HOST').":".app_conf('DB_PORT'), app_conf('DB_USER'),app_conf('DB_PWD'),app_conf('DB_NAME'),'utf8',$pconnect);
//end 定义DB

//获取客户端IP
function get_client_ip() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return ($ip);
}

function im_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
	 $ckey_length = 6;
 	 $key = md5($key != '' ? $key : KEY);
	 $keya = md5(substr($key, 0, 16));
	 $keyb = md5(substr($key, 16, 16));
	 $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	 $cryptkey = $keya.md5($keya.$keyc);
	 $key_length = strlen($cryptkey);
	 $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	 $string_length = strlen($string);
     $result = '';
	 $box = range(0, 255);
	 $rndkey = array();
	 for($i = 0; $i <= 255; $i++) {
	 	 $rndkey[$i] = ord($cryptkey[$i % $key_length]);
	 }
	 for($j = $i = 0; $i < 256; $i++) {
		 $j = ($j + $box[$i] + $rndkey[$i]) % 256;
		 $tmp = $box[$i];
		 $box[$i] = $box[$j];
		 $box[$j] = $tmp;
	 }
	 for($a = $j = $i = 0; $i < $string_length; $i++) {
		 $a = ($a + 1) % 256;
		 $j = ($j + $box[$a]) % 256;
		 $tmp = $box[$a];
		 $box[$a] = $box[$j];
		 $box[$j] = $tmp;
		 $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	 }
	 if($operation == 'DECODE') {
		 if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			 return substr($result, 26);
		 } else {
			 return '';
		 }
	 } else {
		 return $keyc.str_replace('=', '', base64_encode($result));
	 }
}
//获取用户头像的文件名
function get_user_avatar($id,$type)
{
	$uid = sprintf("%09d", $id);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$path = $dir1.'/'.$dir2.'/'.$dir3;
				
	$id = str_pad($id, 2, "0", STR_PAD_LEFT); 
	$id = substr($id,-2);
	$avatar_file = "public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";
	$avatar_check_file = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";
	if(file_exists($avatar_check_file))	
	return $avatar_file;
	else
	return "public/avatar/noavatar_".$type.".gif";
	//@file_put_contents($avatar_check_file,@file_get_contents(APP_ROOT_PATH."public/avatar/noavatar_".$type.".gif"));
}
?>