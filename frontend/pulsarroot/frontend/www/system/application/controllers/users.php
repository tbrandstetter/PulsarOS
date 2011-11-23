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

class users extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('storagedir' => $this->config->item('storagedir'),
						'pool' => $this->config->item('pool'),
						'pooldir' => $this->config->item('pooldir'),
						'volume' => $this->config->item('volume'),
						'userdir' => $this->config->item('userdir'),
						'confdir' => $this->config->item('confdir'),
						'smbdir' => $this->config->item('smbdir'),
						'smbconf' => $this->config->item('smbconf'),
						'nfsdir' => $this->config->item('nfsdir'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('disk', $params);
		$this->load->library('volume', $params);
		$this->load->library('configs', $params);
		$this->load->library('user', $params);
		$this->load->library('share', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		// check if storage exist already - 
		// necessary to create a standard home directory
		if ($this->configs->chkSettings('pool', 'name') == 0) {
			header("Location: index.php?storage");
		}
		
		// get list of pools where the user can choose from - 
		// for the standard home directory
		$xml_pool = $this->configs->getSettings('pool');
		$html['pools'] = array();
		$i = 0;
		foreach ($xml_pool->pool as $pool) {
			$html['pools'][$i]['name'] = $pool->name;
			$html['pools'][$i]['capacity'] = $this->core->calcByte($pool->capacity);
			$i++;
		}
		
		//show user and group list
		$i = 0;
		$html['users'] = array();
		$html['groups'] = array();
		foreach ($this->user->getUser('shadow') as $users) {
			$html['users'][$i] = $users;
			$i++;
		}
		$x = 0;
		foreach ($this->user->getGroup('shadow') as $groups) {
			$html['groups'][$x] = $groups;
			$x++;
		}
		
		//Show Site
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('users/index', $html);
		$this->load->view('footer');
	}
	
	function add()
	{
		// add user
		$user['name'] = htmlspecialchars($_POST['name']);
		$user['password'] = htmlspecialchars($_POST['password']);
		$user['pool'] = htmlspecialchars($_POST['pool']);
		$user['scponly'] = htmlspecialchars($_POST['scponly']);
		$this->user->addUser('shadow', $user);
		$this->configs->chgConfig('/etc/passwd', $this->config->item('confdir'));
		$this->configs->chgConfig('/etc/shadow', $this->config->item('confdir'));
		
		// add default home volume
		$volume['name'] = $user['name'];
		$volume['pool'] = $user['pool'];
		
		// Change this to a default variable -
		// set in the configuration options
		$volume['size'] = "100";
		
		// get remaining pool size
		$volume['remaining'] = round($this->disk->getPoolsize($volume['pool'], "yes"));
		
		// only create a home volume if enough space is available
		if ($volume['size'] < $volume['remaining']) {
			$xml = array('name' => $volume['name'], 'description' => 'Home directory', 'size' => $volume['size'],
					 'share' => 'offline', 'status' => 'off', 'pool' => $volume['pool'], 
				     'iscsi' => "n", 'homedir' => "y");
			$this->configs->addSettings('volume', $xml);
			$this->volume->addVolume($volume);
		}
	}
	
	function del($user)
	{
		// deletes user
		// delete the user also from each share he is configured to
		$this->delUserShare($user['name']);
		
		// deletes the user from all databases
		$this->user->delUser('shadow', $user);
		$this->configs->chgConfig('/etc/passwd', $this->config->item('confdir'));
		$this->configs->chgConfig('/etc/shadow', $this->config->item('confdir'));
		
		// delete the home directory volume too
		$this->volume->delVolume($volume);
	}
	
	function chg()
	{
		// change user (password)
		$user['name'] = htmlspecialchars($_POST['name']);
		$user['description'] = htmlspecialchars($_POST['description']);
		$user['password'] = htmlspecialchars($_POST['password']);
		$removeuser = htmlspecialchars($_POST['removeuser']);
		if ($removeuser == "y" ) {
			$this->del($user);
		}
		else if (!empty($user['description']) || !empty($user['password'])) {
			$this->user->chgUser('shadow', $user);
		}
		$this->configs->chgConfig('/etc/passwd', $this->config->item('confdir'));
		$this->configs->chgConfig('/etc/shadow', $this->config->item('confdir'));
		header("Location: index.php?users");
	}
	
	function delUserShare($name)
	{
		$data['name'] = $name;
		// get volume list
		$xml = $this->configs->getSettings('volume');
		$xml_pool = $this->configs->getSettings('pool');
		$x = 0;
		foreach ($xml_pool->pool as $pool) {
			$data['pools'][$x]['name'] = $pool->name;
			$i = 0;
			$data['pools'][$x]['volumes'] = array();
			foreach ($xml->volume as $volume) {
				// show only non iscsi volumes
				if ($volume->iscsi == "n") {
					if ("$volume->pool" == "$pool->name") {
						$data['pool'] = $pool->name;
						$data['volume'] = $volume->name;
						$output = $this->share->getUser($data);
						if ( $output[0]['username'] == $name) {
							$this->share->deleteUser($data);
						}
						$i++;
					}
				}
			}
			$x++;
		}
		//$this->configs->chgConfig('/etc/passwd', $this->config->item('confdir'));
		//$this->configs->chgConfig('/etc/shadow', $this->config->item('confdir'));
	}
}
?>