<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if(!defined('APP_ROOT_PATH'))define('APP_ROOT_PATH', str_replace('ntalker/install.php', '', str_replace('\\', '/', __FILE__)));

//引入数据库的系统配置及定义配置函数
$sys_config = require APP_ROOT_PATH.'system/config.php';

function app_conf($name)
{
	return stripslashes($GLOBALS['sys_config'][$name]);
}
$_table = app_conf('DB_PREFIX');
//end 引入数据库的系统配置及定义配置函数
//定义DB
require APP_ROOT_PATH.'system/db/db.php';
define('DB_PREFIX', app_conf('DB_PREFIX')); 
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/db_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/app/db_caches/',0777);
$pconnect = false;

$db = new mysql_db(app_conf('DB_HOST').":".app_conf('DB_PORT'), app_conf('DB_USER'),app_conf('DB_PWD'),app_conf('DB_NAME'),'utf8',$pconnect);
//end 定义DB

$sql = "select * from {$_table}role_node where name = 'Ntalker配置'";
$r = $db->getRow($sql);
if($r)die('后台数据已安装');
//插入后台管理数据
$sql1 = "INSERT INTO `{$_table}role_group` (`id`, `name`, `nav_id`, `is_delete`, `is_effect`, `sort`) VALUES (NULL, 'Ntalker管理', '7', '0', '1', '40')";
$db->query($sql1);
$group_id = $db->insert_id();

$sql2 = "INSERT INTO `{$_table}role_module` (`id`, `module`, `name`, `is_effect`, `is_delete`) VALUES (NULL, 'Ntalker', 'Ntalker配置', '1', '0')";
$db->query($sql2);
$module_id = $db->insert_id();

$sql3 = "INSERT INTO `{$_table}role_node` (`id`, `action`, `name`, `is_effect`, `is_delete`, `group_id`, `module_id`) VALUES (NULL, 'index', 'Ntalker配置', '1', '0', '{$group_id}', '{$module_id}')";
$db->query($sql3);
$node_id = $db->insert_id();
//插入后台管理数据结束

echo '后台安装完毕';

?>