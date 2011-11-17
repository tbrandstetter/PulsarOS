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
 * @file		storage.php
 * @version		0.7alpha
 * @date		26/07/2011
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
 
class storage extends Controller
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
						'confdir' => $this->config->item('confdir'),
						'pooldir' => $this->config->item('pooldir'),
						'smbdir' => $this->config->item('smbdir'),
						'smbconf' => $this->config->item('smbconf'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('configs', $params);
		$this->load->library('disk', $params);
		$this->load->library('volume', $params);
		$this->load->library('share', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		// shows, deletes or adds storage pools
		$xml = $this->configs->getSettings('pool');
		$i = 0;
		$html['pools'] = array();
		foreach ($xml as $pool) {
			$html['pools'][$i]['name'] = $pool->name;
			$html['pools'][$i]['size'] = $this->core->calcByte($pool->capacity);
			//mdname is needed for the sync status
			$mdname = explode("/", $pool->mdname);
			$html['pools'][$i]['mdname'] = $mdname[2];
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
			$x = 0;
			foreach ($pool->disk as $disk) {
				$html['pools'][$i]['devices'][$x]['device'] = $disk->device;
				// show accurate disksize
				$html['pools'][$i]['devices'][$x]['capacity'] = $disk->capacity;
				$html['pools'][$i]['devices'][$x]['id'] = $disk->id;
				// gives back the state of the disks (ready, failure or new --> used for the images)
				$disks = array('device' => $disk->device, 'id' => $disk->id, 'status' => $disk->status);
				$html['pools'][$i]['devices'][$x]['availability'] = $this->disk->getState($disks);
				$x++;
				// if all disks are "new" the raid device is ready to grow :-)
				if ($disk->status == "new") {
					$y++;
				}
			}
			// only if all disks are "new" and synchronization is completed
			if ($x == $y && $this->disk->getMDState($mdname[2]) === TRUE) {
				$html['pools'][$i]['size'] = "<a href='index.php?storage/grow/$mdname[2]' alt='grow'>Expand pool</a>";
			}
			$i++;
		}
		//Show Site
		$this->load->view('header');
		$this->load->view('menu');
		// check available disks against used disks
		$disklist = $this->disk->getDisks();
		$i = 0;
		if (!empty($disklist)) {
			foreach ($disklist as $disks) {
				if ($this->configs->chkSettings('pool', 'device', $disks['device']) == 0) {
					$html['disklist'][$i]['device'] = $disks['device'];
					$html['disklist'][$i]['capacity'] = $this->core->convByte($disks);
					$html['disklist'][$i]['id'] = $disks['id'];
					// gives back the state of the disks (ready, failure or new --> used for the images)
					$html['disklist'][$i]['availability'] = $this->disk->getState($disks);
					if ($i == 0) {
						$html['validate'][$i] = "validate['group[1,1]']";
					}
					else {
						$html['validate'][$i] = "validate['group[1]']";
					}
					$i++;
				}
			}
		}
		if (!isset($html['disklist'])) {
			$this->parser->parse('storage/index_nodisk', $html);
		}
		else {
			$this->parser->parse('storage/index', $html);
		}
		$this->load->view('footer');
	}
	
	function add()
	{
		// finally creates the pool with the selected options
		$disks = ($_POST);
		$name = htmlspecialchars($_POST['name']);
		$raidlevel = htmlspecialchars($_POST['raidlevel']);
		$devices = "";
		$i = 0;
		foreach (preg_grep("/dev/", $disks) as $disk) {
			$devdesc = explode(" ", $disk);
			$device[$i]['device'] = $devdesc[0];
			$device[$i]['capacity'] = $devdesc[1];
			$device[$i]['id'] = $devdesc[2];
			// replace with statuscheck !!
			$device[$i]['status'] = "ok";
			$devices .= "$devdesc[0] ";
			$i++;
		}
		$pool = array('disks' => $devices, 'name' => $name, 'raidlevel' => $raidlevel);
		$mdname = $this->disk->addPool($pool);
		// add pool information to xml config
		$poolsize = $this->disk->getPoolsize($name);
		$startelements = array('disk', $device);
		$elements = array('name' => $name, 'mdname' => $mdname, 'raidlevel' => $raidlevel, 'capacity' => $poolsize, 'iscsi' => $iscsi);
		$this->configs->addSettings('pool', $elements, $startelements);
		$status = array('code' => '1', 'message' => '');
	}
	
	function del() 
	{
		// delete the given pool and all assosiated volumes
		$poolname = htmlspecialchars($_POST['name']);
		$device['name'] = $poolname;
		$xml = $this->configs->getSettings('pool');
		$i = 0;
		foreach ($xml->pool as $pool) {
			if ($pool->name == $poolname) {
				foreach ($pool->disk as $disk) {
					$device['disks'][$i] = $disk->device;
					$i++;
				}
			}
		}
		// delete associate volumes
		$xml = $this->configs->getSettings('volume');
		$i = 0;
		$volumes['pool'] = $poolname;
		foreach ($xml->volume as $volumes) {
			if ($volumes->pool == $poolname) {
				foreach ($volumes->name as $volume) {
					$this->configs->delSettings('volume', 'name', $volume);
					$volumes['name'] = $volume;
					$volumes['pool'] = $poolname;
					$data = $this->share->delShare('samba', $volume, $config=true);
					$this->core->chgConfig($data);
					$this->share->delShare('nfs', $volume['name']);
					$this->share->delShare('afp', $volume['name']);
					//copy changed config to pulsarroot
					$this->configs->chgConfig("/etc/exports", $this->config->item('nfsdir'));
					$this->volume->delVolume($volumes);
				}
			}
		}
		// delete the pool
		$this->configs->delSettings('pool', 'name', $poolname);
		$this->disk->delPool($device);
	}
	
	function cfg()
	{
		// displays the storage options for the selected devices
		$disks = ($_POST);
		$html['name'] = htmlspecialchars($_POST['name']);
		// get the information from the input "device" field
		$i = 0;
		foreach (preg_grep("/dev/", $disks) as $disk) {
			$devdesc = explode(" ", $disk);
			$html['disks'][$i]['device'] = $devdesc[0];
			$html['disks'][$i]['capacity'] = $devdesc[1];
			$html['disks'][$i]['id'] = $devdesc[2];
			$i++;
		}
		$diskcount = $i;
		if ($diskcount == 1) {
			$html['options'][0]['raidlevel'] = "raid0";
		}		   
		elseif ($diskcount < 3) {
			$html['options'][0]['raidlevel'] = "raid0";
			$html['options'][1]['raidlevel'] = "raid1";
		}
		elseif ($diskcount >= 3) {
			$html['options'][0]['raidlevel'] = "raid0";
			$html['options'][1]['raidlevel'] = "raid5";
		}
		// show site
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('storage/options', $html);
		$this->load->view('footer');
	}
	
	
	function grow($mdname) {
		// grow the md device to it's new size
		$this->disk->growPool($mdname);
		// change poolsize in config
		$xml = $this->configs->getSettings('pool');
		foreach ($xml as $pool) {
			$md = explode("/", $pool->mdname);
			if ($mdname == $md[2]) {
				$poolsize = $this->disk->getPoolsize($pool->name);
				$element['node'] = 'name';
				$element['nodevalue'] = (string) $pool->name;
				$element['subnode'] = 'capacity';
				$this->configs->chgSettings('pool', $element, $poolsize);
			}
		}
		header('Location: index.php?storage');
	}
	
	function syncstate($device)
	{
		$this->disk->getSyncstate($device);
	}
}
?>