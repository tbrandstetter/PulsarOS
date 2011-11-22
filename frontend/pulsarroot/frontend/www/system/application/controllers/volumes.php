<?php

/**
 * 

 * 
 * Description
 * 
 * @license		GNU General Public License
 * @author		Thomas Brandstetter
 * @link		http://www.pulsaros.com
 * @email		admin@pulsaros.com
 * 
 * Copyright (c) 2009-2011
 */
 
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT
 * HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR
 * FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE
 * OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,
 * COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.COPYRIGHT HOLDERS WILL NOT
 * BE LIABLE FOR ANY DIRECT, INDIRECT, SPECIAL OR CONSEQUENTIAL
 * DAMAGES ARISING OUT OF ANY USE OF THE SOFTWARE OR DOCUMENTATION.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://gnu.org/licenses/>.
 */
 
class Volumes extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('storagedir' => $this->config->item('storagedir'),
						'pool' => $this->config->item('pool'),
						'volume' => $this->config->item('volume'),
						'mdadm' => $this->config->item('mdadm'),
						'filesystems' => $this->config->item('filesystems'),
						'pooldir' => $this->config->item('pooldir'),
						'confdir' => $this->config->item('confdir'),
						'smbdir' => $this->config->item('smbdir'),
						'smbconf' => $this->config->item('smbconf'),
						'iscsiconfig' => $this->config->item('iscsiconfig'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('configs', $params);
		$this->load->library('volume', $params);
		$this->load->library('share', $params);
		$this->load->library('disk', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		// check if storage exist already
		if ($this->configs->chkSettings('pool', 'name') == 0) {
			header("Location: index.php?storage");
		}
		// get volume list
		$xml = $this->configs->getSettings('volume');
		$xml_pool = $this->configs->getSettings('pool');
		$html['pools'] = array();
		$i = 0;
		foreach ($xml_pool->pool as $pool) {
			$html['pools'][$i]['name'] = $pool->name;
			$html['pools'][$i]['capacity'] = $this->core->calcByte($pool->capacity);
			// get remaining pool size
			$html['pools'][$i]['remaining'] = round($this->disk->getPoolsize($pool->name, "yes"));
			// Show more accurate Raid Level descriptions
			switch($pool->raidlevel)
			{
			case "raid0":
				$html['pools'][$i]['raidlevel'] = "Raid0";
				break;
			case "raid1":
				$html['pools'][$i]['raidlevel'] = "Raid1";
				break;
			case "raid5":
				$html['pools'][$i]['raidlevel'] = "Raid5";
				break;
			}
			$html['pools'][$i]['volumes'] = array();
			$html['pools'][$i]['iscsi_volumes'] = array();
			$html['pools'][$i]['state'] = "";
			$x = 0;
			foreach ($xml->volume as $volume) {
				if ("$volume->pool" == "$pool->name") {
					if ($volume->homedir == "y") {
						$html['pools'][$i]['homedir_volumes'][$x]['volume'] = $volume->name;
						$html['pools'][$i]['homedir_volumes'][$x]['description'] = $volume->description;
						$html['pools'][$i]['homedir_volumes'][$x]['share'] = $volume->share;
						$html['pools'][$i]['homedir_volumes'][$x]['status'] = $volume->status;
						$html['pools'][$i]['homedir_volumes'][$x]['usedsize'] = $this->volume->getVolsize($pool->name, $volume->name);
						$html['pools'][$i]['homedir_volumes'][$x]['maxsize'] = $volume->size + $html['pools'][$i]['remaining'];
						$html['pools'][$i]['homedir_volumes'][$x]['volsize'] = $volume->size;
						$html['pools'][$i]['homedir_volumes'][$x]['size'] = $this->core->calcByte($volume->size);
					}
					elseif (exec('cat /proc/mounts | grep "/storage/'. $pool->name .'" | grep -c "'. $volume->name .' "') == 1) {
						$html['pools'][$i]['volumes'][$x]['volume'] = $volume->name;
						$html['pools'][$i]['volumes'][$x]['description'] = $volume->description;
						$html['pools'][$i]['volumes'][$x]['share'] = $volume->share;
						$html['pools'][$i]['volumes'][$x]['status'] = $volume->status;
						$html['pools'][$i]['volumes'][$x]['usedsize'] = $this->volume->getVolsize($pool->name, $volume->name);
						$html['pools'][$i]['volumes'][$x]['maxsize'] = $volume->size + $html['pools'][$i]['remaining'];
						$html['pools'][$i]['volumes'][$x]['volsize'] = $volume->size;
						$html['pools'][$i]['volumes'][$x]['size'] = $this->core->calcByte($volume->size);
						$x++;
					}
					elseif ($volume->iscsi == "y") {
						$html['pools'][$i]['iscsi_volumes'][$x]['volume'] = $volume->name;
						$html['pools'][$i]['iscsi_volumes'][$x]['description'] = $volume->description;
						$html['pools'][$i]['iscsi_volumes'][$x]['share'] = $volume->share;
						$html['pools'][$i]['iscsi_volumes'][$x]['status'] = $volume->status;
						$html['pools'][$i]['iscsi_volumes'][$x]['iscsi'] = $volume->iscsi;
						$html['pools'][$i]['iscsi_volumes'][$x]['size'] = $this->core->calcByte($volume->size);
					}
					else {
						$html['pools'][$i]['state'] = "<p>Volume error - please check pool state!</p>";
						break;
					}
				}
			}
			$i++;
		}
		//Show Site
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('volumes/index', $html);
		$this->load->view('footer');
	}
	
	function add()
	{
		if (!empty($_POST)) {
			$date = date(Y-m);
			$description = htmlspecialchars($_POST['desc']);
			$volume['iscsi'] = htmlspecialchars($_POST['iscsi']);
			$volume['name'] = htmlspecialchars($_POST['name']);
			$volume['pool'] = htmlspecialchars($_POST['pool']);
			$volume['size'] = htmlspecialchars($_POST['size']);
			$volume['iqn'] = 'iqn.'. $date .'.'. $volume['pool'] .':'. $volume['name'] .'_target';
			if ($volume['iscsi'] == "y") {
				$xml = array('name' => $volume['name'], 'description' => $description, 'size' => $volume['size'],
						 	 'share' => 'online', 'status' => 'on', 'pool' => $volume['pool'], 
						 	 'iscsi' => $volume['iscsi'], 'iqn' => $volume['iqn']);
			}
			else {
				$xml = array('name' => $volume['name'], 'description' => $description, 'size' => $volume['size'],
						 	 'share' => 'offline', 'status' => 'off', 'pool' => $volume['pool'], 
						 	 'iscsi' => "n", 'homedir' => "n");
			}
			$this->configs->addSettings('volume', $xml);
			$this->volume->addVolume($volume);
		}
	}
	
	function del($volume)
	{
		// need to change on iscsi delete action --> $volume['iscsi'] = htmlspecialchars($_POST['iscsi']);
		$this->configs->delSettings('volume', 'name', $volume['name']);
		if ($volume['iscsi'] != "y") {
			$data = $this->share->delShare('samba', $volume['name'], $config=true);
			$this->core->chgConfig($data);
			$this->share->delShare('nfs', $volume['name']);
			$this->share->delShare('afp', $volume['name']);
			//copy changed config to pulsarroot
			$this->configs->chgConfig("/etc/exports", $this->config->item('nfsdir'));
		}
		$this->volume->delVolume($volume);
	}
	
	function chg()
	{
		if (!empty($_POST)) {
			$volume['name'] = htmlspecialchars($_POST['name']);
			$volume['pool'] = htmlspecialchars($_POST['pool']);
			$volume['iscsi'] = htmlspecialchars($_POST['iscsi']);
			if ($_POST['delete'] == "y" ) {
				$this->del($volume);
			}
			else {
				$volume['newsize'] = htmlspecialchars($_POST['newsize']);
				$volume['currentsize'] = htmlspecialchars($_POST['volsize']);
				$volume['minsize'] = htmlspecialchars($_POST['minsize']);
				$element['node'] = 'name';
				$element['nodevalue'] = $volume['name'];
				$element['subnode'] = 'size';
				$volume['size'] = $this->volume->chgVolume($volume);
				$this->configs->chgSettings('volume', $element, $volume['size']);
			}
		}
	}
	
	function chk()
	{
		$status = array('code' => '1', 'message' => '');
		if (!empty($_POST)) {
			$volume['name'] = htmlspecialchars($_POST['volume']);
			// Check if clients are connected
			$iscsi = exec('/usr/local/sbin/tgt-admin -s|grep -A15 '. $volume['name'] .'_target|grep -c Connection', $output);
			foreach ($output as $line) {
				exec ('echo '. $line .' >> /tmp/output');
			}
			$samba = exec('/usr/local/bin/smbstatus|grep -c "/'. $volume['name'] .' "');
			$nfs = exec('netstat -an|grep 2049|grep -v 0.0.0.0|wc -l');
			if ($iscsi != "0" || $samba != "0" || $nfs != "0") {
				$status = array('code' => '0', 'message' => "<span>Connection on volume exists!</span>");
			}
		}
		print json_encode($status);
	}
}
?>