<?php
class NtalkerAction extends CommonAction{
	public function index()
	{
		$filename = get_real_path()."public/ntalker_config.php";
		if(file_exists($filename))require $filename;
		$authkey = md5($im_siteid.'_'.$sitekey);
		$this->assign("main_title",'Ntalker配置');
		$this->assign("site_id",$im_siteid);
		$this->assign("site_key",$sitekey);
		$this->assign("site_url",$siteurl);
		$this->assign("auth_key",$authkey);
		$this->assign("enable",$im_enable);
		$this->display();
	}

	public function update()
	{
		$siteEnable = $_POST['site_enable']?'true':'false';
		$siteID = $_POST['site_id'];
		$siteKey = $_POST['site_key'];
		$siteURL = $_POST['site_url'];

		if($siteEnable && $siteID && $siteKey && $siteURL){
			//开始写入配置文件
			$config_str = "<?php\n";
			$config_str .= '$im_siteid = "'.$siteID.'";'."\n";
			$config_str .= '$sitekey = "'.$siteKey.'";'."\n";
			$config_str .= '$siteurl = "'.$siteURL.'";'."\n";
			$config_str .= '$im_enable = '.$siteEnable.';'."\n";
			$filename = get_real_path()."public/ntalker_config.php";
			if (!$handle = fopen($filename, 'w')) {
				$this->error(l("OPEN_FILE_ERROR").$filename);
			}
			if (fwrite($handle, $config_str) === FALSE) {
				$this->error(l("WRITE_FILE_ERROR").$filename);
			}
			fclose($handle);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			$this->error(L("UPDATE_ERROR"));
		}
	}
}
?>