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
  
class shares extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('storagedir' => $this->config->item('storagedir'),
						'pool' => $this->config->item('pool'),
						'volume' => $this->config->item('volume'),
						'userdir' => $this->config->item('userdir'),
						'confdir' => $this->config->item('confdir'),
						'pooldir' => $this->config->item('pooldir'),
						'smbdir' => $this->config->item('smbdir'),
						'smbconf' => $this->config->item('smbconf'),
						'afpconf' => $this->config->item('afpconf'));
						
		// Initialize needed libraries				
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('configs', $params);
		$this->load->library('share', $params);
		$this->load->library('user', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index($volumename)
	{
		// show configuration settings for shares. $volumename comes from codeigniters routing (route.php)
		// get poolname depending on volumename
		$page = "index";
		$xml = $this->configs->getSettings('volume');
		foreach ($xml as $elements) {
			if ($elements->name == $volumename) {
				$data['pool'] = $elements->pool;
				// check if volume is iscsi volume
				$iscsi = $elements->iscsi;
			}
		}
		if ($iscsi == "y") {
			$page = "iscsi";
			$html['iqn'] = $elements->iqn;
		}
		else {
			// get share settings
			$html = $this->share->getShare($volumename);
			$html['name'] = $volumename;
			// user list who are already added to share
			$data['volume'] = $volumename; 
			$html['userperm'] = array();
			$html['groupperm'] = array();
			$html['usershare'] = $this->share->getUser($data);
			$html['groupshare'] = $this->share->getGroup($data);
			// user list for shares
			$html['users'] = array();
			$html['groups'] = array();
			$i = 0;
			// only show users and groups in dropdown list who are not already added to the share
			foreach ($this->user->getUser('shadow') as $users) {
				if ($this->core->array_search($users['name'], $html['usershare']) === false) {
					$html['users'][$i]['user'] = $users['name'];
					$data['user'] = $users['name'];
					$i++;
				}
			}
			$x = 0;
			foreach ($this->user->getGroup('shadow') as $groups) {
				if ($this->core->array_search($groups['name'], $html['groupshare']) === false) {
					$html['groups'][$x]['group'] = $groups['name'];
					$data['group'] = $groups['name'];
					$x++;
				}
			}
			// if this is the first configuration - hide permission settings
			if (empty($html['samba']) && empty($html['nfs']) && empty($html['afp'])) {
				$page = "index_first.php";
			}
		}
		//Show Site
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse("shares/$page", $html);
		$this->load->view('footer');
	}
	
	function cfg()
	{
		// adds options and permissions to activated shares
		$xml = $this->configs->getSettings('volume');
		foreach ($xml as $elements) {
			if ($elements->name == htmlspecialchars($_POST['name'])) {
				// get poolname of volume
				$data['pool'] = $elements->pool;
			}
		}
		$samba['activate'] = htmlspecialchars($_POST['samba']);
		$samba['readonly'] = htmlspecialchars($_POST['samba_readonly']);
		$samba['browseable'] = htmlspecialchars($_POST['browseable']);
		$samba['name'] = htmlspecialchars($_POST['name']);
		$samba['poolname'] = $data['pool'];
		$nfs['activate'] = htmlspecialchars($_POST['nfs']);
		$nfs['readonly'] = htmlspecialchars($_POST['nfs_readonly']);
		$nfs['osx'] = htmlspecialchars($_POST['nfs_osx']);
		$nfs['name'] = htmlspecialchars($_POST['name']);
		$nfs['poolname'] = $data['pool'];
		$afp['name'] = htmlspecialchars($_POST['name']);
		$afp['poolname'] = $data['pool'];
		$afp['activate'] = htmlspecialchars($_POST['afp']);
		// SAMBA
		if (!file_exists(''. $this->config->item('smbdir') .'/'. $samba['name'] .'.conf')) {
			$this->share->addShare($samba, 'samba');
		}
		else {
			$data = $this->share->chgShare($samba, 'samba');
			foreach ($data as $cfg) {
				$this->core->chgConfig($cfg);
			}
		}
		// NFS
		if (exec('grep -c "/'. $nfs['name'] .' " /etc/exports') == 1) {
			$this->share->chgShare($nfs, 'nfs');
			if (!file_exists('/etc/exports')) {
				exec('rm '. $this->config->item('nfsdir') .'/exports');
			}
			else {
				//copy changed config to pulsarroot
			$this->configs->chgConfig("/etc/exports", $this->config->item('nfsdir'));
			}
		}
		else {
			$this->share->addShare($nfs, 'nfs');
			//copy changed config to pulsarroot
			$this->configs->chgConfig("/etc/exports", $this->config->item('nfsdir'));
		}
		// AFP
		if (exec('grep -c "/'. $afp['name'] .' " '. $this->config->item('afpconf') .'') == 1) {
			$this->share->chgShare($afp, 'afp');
		}
		else {
			$this->share->addShare($afp, 'afp');			
		}
		if (!empty($samba['activate']) || !empty($nfs['activate']) || !empty($afp['activate'])) {
			$element['node'] = "name";
			$element['nodevalue'] = htmlspecialchars($_POST['name']);
			$element['subnode'] = 'share';
			$this->configs->chgSettings('volume', $element, 'online');
			$element['subnode'] = 'status';
			$this->configs->chgSettings('volume', $element, 'on');
		}
		else {
			// if the share isn't activated, we don't need the configuration
			$element['node'] = "name";
			$element['nodevalue'] = htmlspecialchars($_POST['name']);
			$element['subnode'] = 'share';
			$this->configs->chgSettings('volume', $element, 'offline');
			$element['subnode'] = 'status';
			$this->configs->chgSettings('volume', $element, 'off');
		}
	}
	
	function perm()
	{
		// adds or deletes permissions of users and groups
		$volumename = htmlspecialchars($_POST['name']);
		$xml = $this->configs->getSettings('volume');
		foreach ($xml as $elements) {
			if ($elements->name == $volumename) {
				$poolname =  $elements->pool;
			}
		}
		
		if (!empty($_POST['removeuser'])) {
			$user['name'] = htmlspecialchars($_POST['removeuser']);
			$user['pool'] = $poolname;
			$user['volume'] = $volumename;
			$data = $this->share->deleteUser($user);
			$this->core->chgConfig($data);
		}
		elseif (!empty($_POST['removegroup'])) {
			$group['name'] = htmlspecialchars($_POST['removegroup']);
			$group['pool'] = $poolname;
			$group['volume'] = $volumename;
			$data = $this->share->deleteGroup($group);
			$this->core->chgConfig($data);
		}
		else {
			if (isset($_POST['permission_user'])) {
			$user['name'] = htmlspecialchars($_POST['user']);
			$user['pool'] = $poolname;
			$user['volume'] = $volumename;
			$user['read'] = htmlspecialchars($_POST["$user[name]_read"]);
			$user['write'] = htmlspecialchars($_POST["$user[name]_write"]);
			$user['execute'] = htmlspecialchars($_POST["$user[name]_execute"]);
			$this->share->setUserPermissions($user);
		}
		elseif (isset($_POST['permission_group'])) {
			$group['name'] = htmlspecialchars($_POST['user']);
			$group['pool'] = $poolname;
			$group['volume'] = $volumename;
			$group['read'] = htmlspecialchars($_POST["$group[name]_read"]);
			$group['write'] = htmlspecialchars($_POST["$group[name]_write"]);
			$group['execute'] = htmlspecialchars($_POST["$group[name]_execute"]);
			$this->share->setGroupPermissions($group);
		}
		else {
			if (!empty($_POST['user'])) {
				$user['name'] = htmlspecialchars($_POST['user']);
				$user['pool'] = $poolname;
				$user['volume'] = $volumename;
				$data = $this->share->addUser($user);
				$this->core->chgConfig($data);
			}
			elseif (!empty($_POST['group'])) {
				$group['name'] = htmlspecialchars($_POST['group']);
				$group['pool'] = $poolname;
				$group['volume'] = $volumename;
				$data = $this->share->addGroup($group);
				$this->core->chgConfig($data);
			}
		}
		}
		header("Location: index.php?share/$volumename");
	}
}
?>