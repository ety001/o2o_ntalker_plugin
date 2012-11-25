<?php
/* 
 * Plug Name: Ntalker for Wordpress
 * Plug Author: ETY001 
 * Blog: www.domyself.me 
 */
$dm_ntalker_version = '3.0.0';
$dm_ntalker_build = '20110722';
require_once('./ntalker_init.php');

header ( "Content-Type: text/xml; charset=utf-8" );
echo ("<?xml version='1.0' encoding='utf-8'?>");
echo ("<imxml encoding='utf-8'>");

$querytype = isset ( $_GET ['query'] ) ? $_GET ['query'] : null;
$querysid = isset ( $_GET ['sid'] ) ? $_GET ['sid'] : null;
$isuserprofile = isset ( $_GET ['isuserprofile'] ) ? $_GET ['isuserprofile'] : null;
$isdetail = isset ( $_GET ['isdetail'] ) ? $_GET ['isdetail'] : null;
$queryusername = isset ( $_GET ['username'] ) ? $_GET ['username'] : null;
$queryuserid = isset ( $_GET ['uid'] ) ? $_GET ['uid'] : null;
$querysrcuid = isset ( $_GET ['srcuid'] ) ? $_GET ['srcuid'] : null;
$newbuddyname = isset ( $_GET ['newbuddyname'] ) ? $_GET ['newbuddyname'] : null;
$newbuddyid = isset ( $_GET ['newbuddyid'] ) ? $_GET ['newbuddyid'] : null;
$delbuddyid = isset ( $_GET ['delbuddyid'] ) ? $_GET ['delbuddyid'] : null;
$destid = isset ( $_GET ['destid'] ) ? $_GET ['destid'] : null;
$userkey = isset($_GET['userkey']) ? $_GET['userkey'] : null; 
$pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : null; 
$pageindex = isset($_GET['pageindex']) ? $_GET['pageindex'] : null;
$fuids = isset($_GET['fuids']) ? $_GET['fuids'] : null;
$isfuidlist = isset($_GET['isfuidlist']) ? $_GET['isfuidlist'] : null;
//$istempuser = false;
//$tempusername = '';

if (!$querytype)
{
	  echo ("<error>no valid query param</error>");
	  echo ("</imxml>");
	  return;
}

//缓存
/*
$catchid = 'tempusers';
if ($usersdata = $cache->get( $catchid )) { //找到缓存对象，则从其中查找访问者数据
	if (isset($usersdata [$queryuserid])) { //如果找到访问者ID对应的缓存数据，则更新访问时间参数
		$usersdata [$queryuserid] ["updatetime"] = $now;
		$tempusername = $usersdata [$queryuserid] ["username"];
		$istempuser = $usersdata [$queryuserid] ["tempuser"];
	}
}
*/

switch ($querytype)
{
	case "imxmlversion":
		  getImxmlVersion();
		  break;
	case "login" :
		  getLogin ($querysid, $queryuserid, $isuserprofile, $isdetail);
	 	  break;
	case "userprofile" :
		  getUserProfile($querysid, $queryuserid, $userkey, $destid, $isdetail);
		  break;
	case "siteprofile" :
		  getSiteProfile();
		  break;
	case "buddylist" :
		  getBuddyList($querysid, $queryuserid, $userkey, $pagesize, $pageindex, $isfuidlist, $isdetail, $fuids);
		  break;
	case "addbuddy":
		  getAddBuddy($querysid, $queryuserid, $userkey, $newbuddyid);
		  break;
	case "delbuddy":
		  getDelBuddy ($querysid, $queryuserid, $userkey, $delbuddyid);
		  break;
	case "confirmbuddy":
		  getAddBuddy($querysid, $queryuserid, $userkey, $newbuddyid);
	default :
		  echo ("<error>query param is not valide</error>");
}

echo ("</imxml>");

//1-返回IMXML版本号
function getImxmlVersion()//OK!!!
{
	 global $dm_ntalker_build;
	 global $dm_ntalker_version;
 	 echo "<version>".$dm_ntalker_version."</version>";
     echo "<for>custom</for>";
     echo "<build>".$dm_ntalker_build."</build>";
     return;
}
//2-验证用户是否在网站登录成功
function  getLogin($querysid, $queryuserid, $isuserprofile=false, $isdetail=false)
{
	global $dm_ntalker_version,$queryusername,$db,$_table;
	$sitekey = app_conf('sitekey');
	echo '<version>'.$dm_ntalker_version.'</version>';
	if(!$queryuserid || !$querysid || md5($queryuserid.$sitekey) != $querysid) {
		echo '<sessionvalide>false</sessionvalide>';
		return false;
	}
	$uid = $queryuserid;
	$sql = 'select * from '. $_table . 'user where id = ' . $uid;
	$user_info = $db->getRow($sql);
	//判断提交的$queryuserid和$querysid中存储着已登录用户的uid是否相同，相同则说明已等陆
	if(md5($queryuserid.$sitekey) == $querysid) {
		echo '<sessionvalide>true</sessionvalide>';
		if($isuserprofile) {
			echo '<userprofile>';
			echo '<uid>'.$uid.'</uid>';
			echo '<name>'.im_xmlsafestr(htmlspecialchars($user_info['user_name'])).'</name>';
			echo '<icon><![CDATA['.'http://'.$_SERVER['HTTP_HOST'].'/'.get_user_avatar($uid , 'middle').']]></icon>';
			echo '<isdefaulticon>-1</isdefaulticon>';
			$profileurl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?ctl=space&id='.$uid;
			echo '<profileurl><![CDATA['.$profileurl.']]></profileurl>';
			echo '<sex>'.$user_info['sex'].'</sex>';
			if($user_info['byear'] && $user_info['bmonth'] && $user_info['bday']){
				echo '<bday>'.$user_info['byear'].'-'.$user_info['bmonth'].'-'.$user_info['bday'].'</bday>';
			}
			if($isdetail) {
				echo "<nick>".im_xmlsafestr(htmlspecialchars($user_info['user_name']))."</nick>";
				echo "<email>".$user_info['email']."</email>";
				echo "<credit>".$user_info['point']."</credit>";
				echo "<money>".$user_info['score']."</money>";
				echo "<profileinfo>".$user_info['my_intro']."</profileinfo>";
			}
			echo "</userprofile>";
		}
		return true;
	} else {
		echo "<sessionvalide>false</sessionvalide>";
		return false;
	}
}
//3-获取网站相关WDK配置
function getSiteProfile(){
    global $enablesitekey,$dm_ntalker_version,$dm_ntalker_build;
    $enablesitekey = $enablesitekey ? "true" : "false";
    $systimestamp = time();
    
    echo "<software>custom</software>";
    echo "<softwareversion>2.2.1116</softwareversion>";
    echo "<language>utf-8</language>";
	echo "<isusesitekey>".$enablesitekey."</isusesitekey>";
	echo "<systimestamp>".$systimestamp."</systimestamp>";
	echo "<sitenanme>".app_conf('SHOP_TITLE')."</sitenanme>";
	echo "<version>".$dm_ntalker_version."</version>";
	echo "<build>".$dm_ntalker_build."</build>";
	echo "<groupchatmsgtothread>false</groupchatmsgtothread>";//群聊内容发帖的接口
    echo "<mycenter>false</mycenter>";//我的中心
    echo "<sitefocus>false</sitefocus>";//焦点
    echo "<sitehavegroup>false</sitehavegroup>";//站点的族群
}

//4-获取用户个人信息，用于显示在聊天窗口中
function getUserProfile($querysid, $queryuserid, $userkey, $destid, $isdetail)
{
	global $dm_ntalker_version,$db,$_table;
	if(!sitekey_login($queryuserid, $userkey))
	{
		return;
	}
	if(!$destid)
	{
		echo "<error>query destid param not valid</error>";
		return;
	}
	$queryuserid = intval($queryuserid);
	$destid = intval($destid);
	$uid = $destid;
	$isdetail = $isdetail ? ($isdetail=="true" ? 1 : 0): 0;
	
	$sql = 'select * from '. $_table . 'user where id = ' . $uid;
	$user_info = $db->getRow($sql);
	
	echo '<userprofile>';
	echo '<uid>'.$uid.'</uid>';
	echo '<name>'.im_xmlsafestr(htmlspecialchars($user_info['user_name'])).'</name>';
	echo '<icon><![CDATA['.'http://'.$_SERVER['HTTP_HOST'].'/'.get_user_avatar($uid , 'middle').']]></icon>';
	echo '<isdefaulticon>-1</isdefaulticon>';
	$profileurl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?ctl=space&id='.$uid;
	echo '<profileurl><![CDATA['.$profileurl.']]></profileurl>';
	echo '<sex>'.$user_info['sex'].'</sex>';
	if($user_info['byear'] && $user_info['bmonth'] && $user_info['bday']){
		echo '<bday>'.$user_info['byear'].'-'.$user_info['bmonth'].'-'.$user_info['bday'].'</bday>';
	}
	if($isdetail) {
		echo "<nick>".im_xmlsafestr(htmlspecialchars($user_info['user_name']))."</nick>";
		echo "<email>".$user_info['email']."</email>";
		echo "<credit>".$user_info['point']."</credit>";
		echo "<money>".$user_info['score']."</money>";
		echo "<profileinfo>".$user_info['my_intro']."</profileinfo>";
	}
	echo "</userprofile>";
}
//5-获取用户好友列表
function getBuddyList($querysid, $queryuserid, $userkey, $pagesize=255, $pageindex=0, $isfuidlist, $isdetail, $fuids)
{
	global $dm_ntalker_version,$db,$_table,$cache,$cache_tag;
	$focus_val_name = md5('focus_ety001_'.$queryuserid);
	$focused_val_name = md5('focused_ety001_'.$queryuserid);

	$_fuid='';
	if(!sitekey_login($queryuserid, $userkey))
	{
		return;
	}
	$queryuserid = intval($queryuserid);
	$pagesize =  intval($pagesize) > 0 ? intval($pagesize) : 255;
	$pageindex = $pageindex ? intval($pageindex) : 0;     
	$isdetail = $isdetail ? ($isdetail=="true" ? 1 : 0): 0;
	$isfuidlist = $isfuidlist ? (strtolower($isfuidlist)=='true' ? 1 : 0): 0;
	//查找好友的sql语句
	if($cache_tag)$focus = $cache->get($focus_val_name);
	if($cache_tag)$focused = $cache->get($focused_val_name);
	if(!$focus || !$focused){
		//我关注的人
		$sql1 = 'select * from ' . $_table .'user_focus where focus_user_id = '. $queryuserid;
		$focus = $db->getAll($sql1);
		if($cache_tag)$cache->set($focus_val_name , $focus , 60*5);
		//关注我的人
		$sql2 = 'select * from ' . $_table .'user_focus where focused_user_id = '. $queryuserid;
		$focused = $db->getAll($sql2);
		if($cache_tag)$cache->set($focused_val_name , $focused , 60*5);
	}

	$focused_uid = array();
	foreach($focus as $v){
		array_push($focused_uid , $v['focused_user_id']);
	}

	$focus_uid = array();
	foreach($focused as $v){
		array_push($focus_uid , $v['focus_user_id']);
	}

	$fuid_arr = array_intersect($focused_uid , $focus_uid);//好友id数组
	$fuid_str = implode($fuid_arr);//好友id字符串
	$buddynum = count($fuid_arr);//好友总数
	echo "<pageindex>".$pageindex."</pageindex>";
	echo "<pagesize>".$pagesize."</pagesize>";
	if($fuids && $buddynum)
	{
		$fuids = uid_safecheck($fuids,$buddynum);//fuids安全检查
	}
	if($isfuidlist || !$fuids)
	{
		echo '<allbuddynum>'.$buddynum.'</allbuddynum>';
	}
	if($isfuidlist)
	{
		echo '<buddyuids>'.uid_safecheck($fuid_str , $buddynum) . '</buddyuids>';
	}
	else
	{
		$start =  page_start($pageindex, $pagesize, $buddynum);
		echo '<buddylist>';
		if($fuids){
			$idList = ' u.id in (' . $fuids . ') ';
		} else {
			$idList = ' u.id in (' . $fuid_str . ') ';
		}
		$sql = 'select u.*,ug.name from ' . $_table . 'user u left join ' . $_table . 'user_group ug on u.group_id = ug.id where ' . $idList . ' order by u.id asc limit ' . $start . ',' . $pagesize; 
		$friend_info = $db->getAll($sql);
		foreach($friend_info as $user_info){
			echo '<buddy>';
			echo '<uid>'.$user_info['id'].'</uid>';
			echo '<name>'.im_xmlsafestr(htmlspecialchars($user_info['user_name'])).'</name>';
			echo '<icon><![CDATA['.'http://'.$_SERVER['HTTP_HOST'].'/'.get_user_avatar($user_info['id'] , 'middle').']]></icon>';
			echo '<isdefaulticon>-1</isdefaulticon>';
			$profileurl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?ctl=space&id='.$user_info['id'];
			echo '<profileurl><![CDATA['.$profileurl.']]></profileurl>';
			if($imxmlperf!='high'){
				echo "<buddygroup id=\"".$user_info['group_id']."\">".$user_info['name']."</buddygroup>";
				echo '<sex>'.$user_info['sex'].'</sex>';
				if($user_info['byear'] && $user_info['bmonth'] && $user_info['bday']){
					echo '<bday>'.$user_info['byear'].'-'.$user_info['bmonth'].'-'.$user_info['bday'].'</bday>';
				}
			}
			if($isdetail) {
				echo "<nick>".im_xmlsafestr(htmlspecialchars($user_info['user_name']))."</nick>";
				echo "<email>".$user_info['email']."</email>";
				echo "<credit>".$user_info['point']."</credit>";
				echo "<money>".$user_info['score']."</money>";
				echo "<profileinfo>".$user_info['my_intro']."</profileinfo>";
			}
			echo "</buddy>";
		}
		echo '</buddylist>';
	}
}
//6-添加好友
function getAddBuddy($querysid, $queryuserid, $userkey, $newbuddyid) {
	global $dm_ntalker_version,$db,$_table;
	if(!sitekey_login($queryuserid, $userkey))
	{
		return;
	}
	$sql1 = 'select * from ' . $_table . 'user where id = ' . $queryuserid;
	$sql2 = 'select * from ' . $_table . 'user where id = ' . $newbuddyid;
	$userInfo1 = $db->getRow($sql1);
	$username1 = $userInfo1['user_name'];
	$userInfo2 = $db->getRow($sql2);
	$username2 = $userInfo2['user_name'];
	$sql3 = 'select * from ' . $_table . 'user_focus where focus_user_id = ' . $queryuserid . ' and focused_user_id = ' . $newbuddyid;
	$r = $db->getRow($sql3);
	if($r){
		echo '<error>该用户你已经关注了，如果没有在你的ntalker好友列表中出现，是因为对方没有关注你或者是没有同意你的加好友申请</error>';
		echo "<addbuddyresult>false</addbuddyresult>";
	} else {
		$sql4 = 'insert into ' . $_table . "user_focus (focus_user_id , focused_user_id , focus_user_name , focused_user_name) values ('{$queryuserid}' , '{$newbuddyid}' , '{$username1}' , '{$username2}')";
		$db->query($sql4);
		echo '<addbuddyresult>accepted</addbuddyresult>';
	}
}
//7-删除好友
function getDelBuddy ($querysid, $queryuserid, $userkey, $delbuddyid){
	global $dm_ntalker_version,$db,$_table;
	if(!sitekey_login($queryuserid, $userkey))
	{
		return;
	}
	$sql1 = ' from ' . $_table . 'user_focus where focus_user_id = ' . $delbuddyid . ' and focused_user_id = ' . $queryuserid;
	$sql2 = ' from ' . $_table . 'user_focus where focus_user_id = ' . $queryuserid . ' and focused_user_id = ' . $delbuddyid;
	$r1 = $db->getRow('select * '.$sql1);
	$r2 = $db->getRow('select * '.$sql2);
	if($r1 && $r2){
		$db->query('delete '.$sql1);
		$db->query('delete '.$sql2);
		echo '<delbuddyresult>bothsuccess</delbuddyresult>';
	} else {
		echo '<error>该用户不是你的好友</error>';
		echo '<delbuddyresult>failed</delbuddyresult>';
	}
}
//8-添加好友确认
function getConfirmAddBuddy ($querysid, $queryuserid, $userkey, $newbuddyid ,$confirm){
	global $dm_ntalker_version,$db,$_table;
	if(!sitekey_login($queryuserid, $userkey))
	{
		return;
	}
	$sql1 = 'select * from ' . $_table . 'user where id = ' . $queryuserid;
	$sql2 = 'select * from ' . $_table . 'user where id = ' . $newbuddyid;
	$userInfo1 = $db->getRow($sql1);
	$username1 = $userInfo1['user_name'];
	$userInfo2 = $db->getRow($sql2);
	$username2 = $userInfo2['user_name'];
	$sql3 = 'select * from ' . $_table . 'user_focus where focus_user_id = ' . $queryuserid . ' and focused_user_id = ' . $newbuddyid;
	$r = $db->getRow($sql3);
	if($r){
		echo '<error>该用户你已经关注了，如果没有在你的ntalker好友列表中出现，是因为对方没有关注你或者是没有同意你的加好友申请</error>';
		echo "<addbuddyresult>false</addbuddyresult>";
	} else {
		$sql4 = 'insert into ' . $_table . "user_focus (focus_user_id , focused_user_id , focus_user_name , focused_user_name) values ('{$queryuserid}' , '{$newbuddyid}' , '{$username1}' , '{$username2}')";
		$db->query($sql4);
		echo '<addbuddyresult>accepted</addbuddyresult>';
	}
}

//下面是辅助函数

//userkey验证用户登录
function sitekey_login($queryuserid, $userkey)
{
	global $sitekey,$dm_ntalker_version;
	echo '<version>'.$dm_ntalker_version.'</version>';
	if(!$queryuserid)
	{
		echo "<error>no uid param valid</error>";
		echo "<userkeyvalide>false</userkeyvalide>";
		return false;
	}
	if(!$userkey)
	{
		echo "<error>no userkey param valid</error>";
		echo "<userkeyvalide>false</userkeyvalide>";
		return false;
	}
	$tempkey = md5($queryuserid.$sitekey);
	if($tempkey == $userkey)
	{
		echo "<userkeyvalide>true</userkeyvalide>";
		return true;
	}
	else
	{
		echo "<userkeyvalide>false</userkeyvalide>";
		return false;
	}
}

//好友列表的页数
function page_start($page, $ppp, $totalnum)
{
	$totalpage = ceil($totalnum / $ppp);
	$page =  max(0, min($totalpage,intval($page)));
	return $page * $ppp;
}

//好友uid安全性检查
function uid_safecheck($fuids,$fnum)
{
	if(!$fuids)
	{
		return '';
	}
    $_fuid = explode(',',$fuids);
    if(count($_fuid)>$fnum)
    {
		return '';
	}
	$_fuids = '';
	$i = 0;
    foreach($_fuid as $_tmpuid)
    {
		//验证是否为数字uid
		if(is_numeric($_tmpuid))
		{
			if($i>=100)
			{
				break;
			}
			$_fuids =  $_fuids ? $_fuids.','.$_tmpuid : $_tmpuid;
			$i++;
		}
		else
		{
			break;
		}
	}
	if($_fuids)
	{
		return $_fuids;
	}
	else
	{
		return '';
	}
}
//处理特殊用户名       
function im_xmlsafestr($s)
{
	return preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$s);
}


?>
