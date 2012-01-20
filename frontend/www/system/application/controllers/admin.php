<?php

/**
 * 

 * 
 * Description
 * 
 * @license		GNU General Public License
 * @author		Thomas Brandstetter
 * @link		http://www.pulsaros.com
 * @email		tb@digitalplayground.at
 * 
 * @file		admin.php
 * @version		0.5alpha
 * @date		04/08/2010
 * 
 * Copyright (c) 2009-2010
 */
 
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any latewgr version.
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

class Admin extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('storagedir' => $this->config->item('storagedir'),
						'pool' => $this->config->item('pool'),
						'volume' => $this->config->item('volume'),
						'update' => $this->config->item('update'),
						'setup' => $this->config->item('setup'),
						'interfaces' => $this->config->item('interfaces'),
						'backup' => $this->config->item('backup'),
						'www_root' => $this->config->item('www_root'),
						'pulsarroot' => $this->config->item('pulsarroot'),
						'version' => $this->config->item('version'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('configs', $params);
		$this->load->library('install', $params);
		$this->load->library('network', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		$html['sysinfo'] = $this->core->getSysinfo();
		$version = $this->core->pulsarVersion();
		$html['version'] = "PulsarOS $version";
		$html['uptime'] = $this->core->getUptime();
		$html['storage'] = $this->core->getStorage();
		$xml_pool = $this->configs->getSettings('pool');
		$html['pools'] = array();
		$i = 0;
		foreach ($xml_pool->pool as $pool) {
			$html['pools'][$i]['name'] = $pool->name;
			$i++;
		}	
		// Show Site
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('admin/index', $html);
		$this->load->view('footer');
	}
	
	function backup()
	{
		$html['status'] = "";
		if ($_POST['backup'] == "y") {
			$this->core->createBackup();
			$backup = '/'. $this->config->item('backup') .'.tar.bz2';
			$status = array('code' => '1', 'message' => "<a href=$backup alt='backupfile'>Download backup</a>");
			print json_encode($status);
		}
		else {
			$this->load->view('header');
			$this->load->view('menu');
			$this->parser->parse('admin/backup', $html);
			$this->load->view('footer');
		}
	}
	
	function restore()
	{
		$html['status'] = "";
		if ($_FILES['file']['error'] > 0 || $_FILES['file']['size'] == 0 ) {
			$html['status'] = "<p>Upload error!</p>";
		}
		elseif ($_FILES['file']['type'] != "application/x-bzip2" ) {
			$html['status'] = "<p>Wrong file type!</p>";
		}
		else {
			move_uploaded_file($_FILES['file']['tmp_name'], ''. $this->config->item('pulsarroot') .'/'. $_FILES['file']['name'] .'');
			exec('bzip2 -d '. $this->config->item('pulsarroot') .'/'. $_FILES['file']['name'] .'');
			$output = exec('tar -tf '. $this->config->item('pulsarroot') .'/'. $this->config->item('backup') .'.tar|grep -c configs');
			if ($output >= 30) {
				exec('cd '. $this->config->item('pulsarroot') .' && tar -xf '. $this->config->item('backup') .'.tar');
				exec('cd '. $this->config->item('pulsarroot') .' && rm '. $this->config->item('backup') .'.tar');
				$html['status'] = "<p>Restored config - Please reboot your system!</p>";
			}
			else {
				$html['status'] = "<p>Wrong restore file - Please check content!</p>";
			}
			
		}
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('admin/backup', $html);
		$this->load->view('footer');
	}
	
	function validate()
	{
		$status = "";
		if (!empty($_POST)) {
			$data = htmlspecialchars($_POST['data']);
			$type = htmlspecialchars($_POST['id']);
			if ($this->configs->chkSettings($type, 'name', $data) != 0) {
				$status = array('code' => '0', 'message' => "<span>$type exists!</span>");
			}
			print json_encode($status);
		}
	}
	
	function update()
	{
		if ($_POST['update'] == "y") {
			$this->install->toUpdate();
			$status = array('code' => '0', 'message' => "Your system has been updated now. Please reboot it.");
			print json_encode($status);
		}
		else {
			exec('ping -c 4 '. $this->config->item('mirror') .'', $output, $status);
			if ($status != 0) {
				$html['update'] = "Network error - Please check your network connection!";
				$update = "admin/no_update";
			}
			else {
				$update = $this->install->getUpdates();
				if ($update != "1") {
					$html['update'] = "New updates are available!";
					$update = "admin/update";
				}
				else {
					$html['update'] = "There is no update available!";
					$update = "admin/no_update";
				}
			}
			$this->load->view('header');
			$this->load->view('menu');
			$this->parser->parse($update, $html);
			$this->load->view('footer');
		}
	}
}
?>