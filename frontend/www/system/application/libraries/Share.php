<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

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
 * @file		User.php
 * @version		0.5alpha
 * @date		12/02/2009
 * 
 * Copyright (c) 2009-2010
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
 
class Share {

	function Share($params) {
		// initialize parameters from controller
		$this->smbconf = $params['smbconf'];
		$this->afpconf = $params['afpconf'];
		$this->smbdir = $params['smbdir'];
		$this->pooldir = $params['pooldir'];
		$this->confdir = $params['confdir'];
	}

	function addShare($data, $type)
	{
		//adds options to the $type of share
		switch($type)
		{
			case "samba":
				$path = ''. $this->smbdir .'/'. $data['name'] .'.conf';
				exec('echo "['. $data['name'] .']" > '. $path .'');
				exec('echo "path = '. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .'" >> '. $path .'');
				exec('echo "valid users =" >> '. $path .'');
				exec('echo "valid groups =" >> '. $path .'');
				exec('echo "inherit permissions = yes" >> '. $path .'');
				exec('echo "inherit acls = yes" >> '. $path .'');
				exec('echo "map acl inherit = yes" >> '. $path .'');
				if ( $data['readonly'] == "y" ) {
					exec('echo "read only = yes" >> '. $path .'');
				}
				else {
					exec('echo "read only = no" >> '. $path .'');
				}
				if ( $data['browseable'] == "y" ) {
					exec('echo "browseable = yes" >> '. $path .'');
				}
				else {
					exec('echo "browseable = no" >> '. $path .'');
				}
				if ( $data['activate'] == "y" ) {
					exec('echo "include = '. $path .'" >> '. $this->smbconf .'');
				}
				exec('/etc/init.d/samba reload');
				break;
			case "nfs":
				if ( $data['activate'] == "y" ) {
					$path = '/etc/exports';
					if ($data['readonly'] == "") {
						$data['readonly'] = "rw";
					}
					if ($data['osx'] == "") {
						exec('echo "'. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .' *('. $data['readonly'] .',sync,no_subtree_check)" >> '. $path .'');
					}
					else {
						exec('echo "'. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .' *('. $data['readonly'] .',sync,'. $data['osx'] .',no_subtree_check)" >> '. $path .'');
					}
					exec('/etc/init.d/nfs restart');
				}
				break;
			case "afp":
				if ( $data['activate'] == "y" ) {
					exec('echo "'. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .' '. $data['name'] .' options:noadouble allow:" >> '. $this->afpconf .'');
					exec('monit restart afpd');
				}
				break;
		}
	}
	
	function delShare($type, $volume, $config=false)
	{
		//deactivates $type of share
		switch($type)
		{
			case "samba":
				$cfg['path'] = $this->smbconf;
				$cfg['key'] = 'include';
				$cfg['value'] = $volume;
				$cfg['action'] = 'deleteall';
				if ($config === true) {
					exec('rm '. $this->smbdir .'/'. $volume .'.conf');
				}
				exec ('/etc/init.d/samba/reload');
				return $cfg;
				break;
			case "nfs":
				exec ('cat /etc/exports', $exports);
				exec ('rm /etc/exports');
				foreach ($exports as $export) {
					if (! strpos($export, $volume)) {
						exec ('echo "'. $export .'" >> /etc/exports');
					}
				}
				exec('/etc/init.d/nfs reload');
				break;
			case "afp":
				exec ('cat '. $this->afpconf .'', $shares);
				exec ('rm '. $this->afpconf .'');
				foreach ($shares as $share) {
					if (! strpos($share, '/'. $volume .' ')) {
						exec ('echo "'. $share .'" >> '. $this->afpconf .'');
					}
				}
				exec('monit restart afpd');
				break;
		}
	}
	
	function chgShare($data, $type)
	{
		// changes share options
		switch($type)
		{
			case "samba":
				if ( $data['readonly'] == "y" ) {
					$cfg['readonly']['path'] = ''. $this->smbdir .'/'. $data['name'] .'.conf';
					$cfg['readonly']['section'] = '['. $data['name'] .']';
					$cfg['readonly']['key'] = 'read only';
					$cfg['readonly']['value'] = 'yes';
					$cfg['readonly']['action'] = 'change';
				}
				elseif (empty($data['readonly'])) {
					$cfg['readonly']['path'] = ''. $this->smbdir .'/'. $data['name'] .'.conf';
					$cfg['readonly']['section'] = '['. $data['name'] .']';
					$cfg['readonly']['key'] = "read only";
					$cfg['readonly']['value'] = "no";
					$cfg['readonly']['action'] = "change";
				}
				if ( $data['browseable'] == "y" ) {
					$cfg['browseable']['path'] = ''. $this->smbdir .'/'. $data['name'] .'.conf';
					$cfg['browseable']['section'] = '['. $data['name'] .']';
					$cfg['browseable']['key'] = "browseable";
					$cfg['browseable']['value'] = "yes";
					$cfg['browseable']['action'] = "change";
				}
				elseif (empty($data['browseable'])) {
					$cfg['browseable']['path'] = ''. $this->smbdir .'/'. $data['name'] .'.conf';
					$cfg['browseable']['section'] = '['. $data['name'] .']';
					$cfg['browseable']['key'] = "browseable";
					$cfg['browseable']['value'] = "no";
					$cfg['browseable']['action'] = "change";
				}
				if ( $data['activate'] == "y" && exec('grep -c '. $data['name'] .'.conf '. $this->smbconf .'') == 0) {
					exec('echo "include = '. $this->smbdir .'/'. $data['name'] .'.conf" >> '. $this->smbconf .'');
				}
				elseif (empty($data['activate'])) {
					$data = $this->delShare('samba', $data['name']);
					$cfg['share']['path'] = $data['path'];
					$cfg['share']['key'] = $data['key'];
					$cfg['share']['value'] = $data['value'];
					$cfg['share']['action'] = $data['action'];
				}
				exec ('/etc/init.d/samba/reload');
				return $cfg;
				break;
			case "nfs":
				$path = "/etc/exports";
				exec ('cat /etc/exports', $exports);
				exec ('rm /etc/exports');
				foreach ($exports as $export) {
					if (! strpos($export, $data['name'])) {
						exec ('echo "'. $export .'" >> /etc/exports');
					}
				}
				if ($data['activate'] == "y") {
					if (empty($data['readonly'])) {
						$data['readonly'] = "rw";
					}
					if (empty($data['osx'])) {
						exec('echo "'. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .' ('. $data['readonly'] .',sync,no_subtree_check)" >> '. $path .'');
					}
					else {
						exec('echo "'. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .' ('. $data['readonly'] .',sync,'. $data['osx'] .',no_subtree_check)" >> '. $path .'');
					}	
				}
				exec('/etc/init.d/nfs reload');
				break;
			case "afp":
				if ( $data['activate'] == "y" ) {
					if (exec('grep -c "#'. $this->pooldir .'/'. $data['poolname'] .'/'. $data['name'] .' " '. $this->afpconf .'') == 1) {
						exec ('cat '. $this->afpconf .'', $shares);
						exec ('rm '. $this->afpconf .'');
						foreach ($shares as $share) {
							if (! strpos($share, '/'. $data['name'] .' ')) {
								exec ('echo "'. $share .'" >> '. $this->afpconf .'');
							}
							else {
								$share = explode('#', $share);
								
								exec('echo '. $share[1] .' >> '. $this->afpconf .'');
							}
						}
					}
				}
				else {
					exec ('cat '. $this->afpconf .'', $shares);
					exec ('rm '. $this->afpconf .'');
					foreach ($shares as $share) {
						if (! strpos($share, '/'. $data['name'] .' ')) {
							exec ('echo "'. $share .'" >> '. $this->afpconf .'');
						}
						else {
							if (strpos($share, "#") !== false ) {
								exec('echo "'. $share .'" >> '. $this->afpconf .'');
							}
							else {
								exec('echo "#'. $share .'" >> '. $this->afpconf .'');
							}
						}
					}
				}
				exec('monit restart afpd');
				break;
		}
		
	}
	
	function getShare($volume)
	{
		// get the options of each share type
		$data['samba'] = "";
		$data['samba_readonly'] = "";
		$data['browseable'] = "";
		$data['nfs'] = "";
		$data['nfs_readonly'] = "";
		$data['nfs_osx'] = "";
		$data['afp'] = "";
		if (file_exists(''. $this->smbdir .'/'. $volume .'.conf')) {
			if (exec('grep -c "read only = yes" '. $this->smbdir .'/'. $volume .'.conf') == 1 ) {
				$data['samba_readonly'] = "checked=checked";
			}
			if (exec('grep -c "browseable = yes" '. $this->smbdir .'/'. $volume .'.conf') == 1 ) {
				$data['browseable'] = "checked=checked";
			}
			if (exec('grep -c '. $volume .'.conf '. $this->smbconf .'') == 1 ) {
				$data['samba'] = "checked=checked";
			}
		}
		if (file_exists("/etc/exports")) {
			if (exec('grep -c "'. $volume .' " /etc/exports') == 1) {
				$data['nfs'] = "checked=checked";
				if (exec('grep "'. $volume .' " /etc/exports | grep -c ro,') == 1) {
					$data['nfs_readonly'] = "checked=checked";
				}
				if (exec('grep "'. $volume .' " /etc/exports | grep -c insecure') == 1) {
					$data['nfs_osx'] = "checked=checked";
				}
			}
		}
		if (file_exists(''. $this->afpconf .'')) {
			if (exec('grep -c "/'. $volume .' " '. $this->afpconf .'') == 1 && exec('grep "/'. $volume .' " '. $this->afpconf .'| grep -c "#" ') == 0  ) {
				$data['afp'] = "checked=checked";
			}
		}
		return $data;
	}
	
	function addUser($data)
	{
		// add users to shares
		// SAMBA
		$cfg['path'] = ''. $this->smbdir .'/'. $data['volume'] .'.conf';
		$cfg['key'] = "valid users";
		$cfg['value'] = $data['name'];
		$cfg['action'] = "add";
		// AFP
		if (!file_exists(''. $this->afpconf .'') || exec('grep -c "/'. $data['volume'] .' " '. $this->afpconf .'') == 0) {
			// comment the share out, if it isn't activated yet
			exec('echo "#'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble allow:'. $data['name'] .'" >> '. $this->afpconf .'');
		}
		else {
			$share = exec('grep "/'. $data['volume'] .' " '. $this->afpconf .'');
			$content = explode(" ", $share);
			$permissions = $content[3];
			if ($permissions == "allow:") {
				$permissions = 'allow:'. $data['name'] .'';
			}
			else {
				$permissions .= ','. $data['name'] .'';
			}
			exec ('cat '. $this->afpconf .'', $shares);
			exec ('rm '. $this->afpconf .'');
			foreach ($shares as $share) {
				if (! strpos($share, '/'. $data['volume'] .' ')) {
					exec ('echo "'. $share .'" >> '. $this->afpconf .'');
				}
				else {
					if (strpos($share, '#') !== false) {
						exec('echo "#'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble '. $permissions .'" >> '. $this->afpconf .'');
					}
					else {
						exec('echo "'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble '. $permissions .'" >> '. $this->afpconf .'');
					}
				}
			}
		}
		exec('monit restart afpd');
		exec ('setfacl -R -m user:'. $data['name'] .':000 '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'', $output);
		return $cfg;
	}
	
	function deleteUser($data)
	{
		// remove users from shares
		$cfg['path'] = ''. $this->smbdir .'/'. $data['volume'] .'.conf';
		$cfg['key'] = "valid users";
		$cfg['value'] = $data['name'];
		$cfg['action'] = "delete";
		// AFP
		$share = exec('grep "/'. $data['volume'] .' " '. $this->afpconf .'');
		$content = explode(" ", $share);
		$permissions = $content[3];
		$allow = explode(':', $permissions);
		$users = explode(',', $allow[1]);
		$i=0;
		$x=count($users);
		foreach ($users as $user) {
			if ($user != $data['name']) {
				$i++;
				if ($i > 1 && $i < $x) {
					$userperm .= ','. $user .'';
				}
				else {
					$userperm .= $user;
				}
			}
		}
		exec ('cat '. $this->afpconf .'', $shares);
		exec ('rm '. $this->afpconf .'');
		foreach ($shares as $share) {
			if (! strpos($share, '/'. $data['volume'] .' ')) {
				exec ('echo "'. $share .'" >> '. $this->afpconf .'');	
			}
			else {
				if (strpos($share, '#') !== false) {
					exec('echo "#'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble allow:'. $userperm .'" >> '. $this->afpconf .'');
				}
				else {
					exec('echo "'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble allow:'. $userperm .'" >> '. $this->afpconf .'');
				}
			}
		}
		exec('monit restart afpd');
		exec ('setfacl -R -x user:'. $data['name'] .' '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'');
		return $cfg;
	}
	
	function getUser($data)
	{
		// list users on shares
		exec ('/usr/local/bin/getfacl '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'|grep -v ^user::| grep ^user| awk -F: \'{ print $2" "$3 }\'', $output);
		$i = 0;
		$userlist = array();
		foreach ($output as $users) {
			$user = explode(" ", $users);
			$userlist[$i]['username'] = $user[0];
			$permissions = str_split($user[1]);
			$x = 0;
			foreach ($permissions as $permission) {
				switch ($x)
				{
					case 0:
						if ($permission == "r") {
							$userlist[$i]['read'] = "checked=checked";
						}
						break;
					case 1:
						if ($permission == "w") {
							$userlist[$i]['write'] = "checked=checked";
						}
						break;
					case 2:
						if ($permission == "x") {
							$userlist[$i]['execute'] = "checked=checked";
						}
						break;
				}
				$x++;
			}
			$i++;
		}
		return $userlist;
	}
	
	function addGroup($data)
	{
		// add groups to shares
		$cfg['path'] = ''. $this->smbdir .'/'. $data['volume'] .'.conf';
		$cfg['key'] = "valid groups";
		$cfg['value'] = $data['name'];
		$cfg['action'] = "add";
		// AFP
		if (!file_exists(''. $this->afpconf .'') || exec('grep -c "/'. $data['volume'] .' " '. $this->afpconf .'') == 0) {
			// comment the share out, if it isn't activated yet
			exec('echo "#'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble allow:'. $data['name'] .'" >> '. $this->afpconf .'');
		}
		else {
			$share = exec('grep "/'. $data['volume'] .' " '. $this->afpconf .'');
			$content = explode(" ", $share);
			$permissions = $content[3];
			if ($permissions == "allow:") {
				$permissions = 'allow:@'. $data['name'] .'';
			}
			else {
				$permissions .= ',@'. $data['name'] .'';
			}
			exec ('cat '. $this->afpconf .'', $shares);
			exec ('rm '. $this->afpconf .'');
			foreach ($shares as $share) {
				if (! strpos($share, '/'. $data['volume'] .' ')) {
					exec ('echo "'. $share .'" >> '. $this->afpconf .'');
				}
				else {
					if (strpos($share, '#') !== false) {
						exec('echo "#'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble '. $permissions .'" >> '. $this->afpconf .'');
					}
					else {
						exec('echo "'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble '. $permissions .'" >> '. $this->afpconf .'');
					}
				}
			}
		}
		exec('monit restart afpd');
		exec ('setfacl -R -m group:'. $data['name'] .':000 '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'');
		return $cfg;
	}
	
	function deleteGroup($data)
	{
		// remove groups from shares
		$cfg['path'] = ''. $this->smbdir .'/'. $data['volume'] .'.conf';
		$cfg['key'] = "valid groups";
		$cfg['value'] = $data['name'];
		$cfg['action'] = "delete";
		// AFP
		$share = exec('grep "/'. $data['volume'] .' " '. $this->afpconf .'');
		$content = explode(" ", $share);
		$permissions = $content[3];
		$allow = explode(':', $permissions);
		$groups = explode(',', $allow[1]);
		$i=0;
		$x=count($groups);
		foreach ($groups as $group) {
			if ($group != '@'. $data['name'] .'') {
				$i++;
				if ($i > 1 && $i < $x) {
					$groupperm .= ','. $group .'';
				}
				else {
					$groupperm .= $group;
				}
			}
		}
		exec ('cat '. $this->afpconf .'', $shares);
		exec ('rm '. $this->afpconf .'');
		foreach ($shares as $share) {
			if (! strpos($share, '/'. $data['volume'] .' ')) {
				exec ('echo "'. $share .'" >> '. $this->afpconf .'');	
			}
			else {
				if (strpos($share, '#') !== false) {
					exec('echo "#'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble allow:'. $groupperm .'" >> '. $this->afpconf .'');
				}
				else {
					exec('echo "'. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .' '. $data['volume'] .' options:noadouble allow:'. $groupperm .'" >> '. $this->afpconf .'');
				}
			}
		}
		exec('monit restart afpd');
		exec ('setfacl -R -x group:'. $data['name'] .' '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'');
		return $cfg;
	}
	
	function getGroup($data)
	{
		// list groups on shares
		exec ('/usr/local/bin/getfacl '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'|grep -v ^group::| grep ^group| awk -F: \'{ print $2" "$3 }\'', $output);
		$i = 0;
		$grouplist = array();
		foreach ($output as $groups) {
			$group = explode(" ", $groups);
			$grouplist[$i]['groupname'] = $group[0];
			$permissions = str_split($group[1]);
			$x = 0;
			foreach ($permissions as $permission) {
				switch ($x)
				{
					case 0:
						if ($permission == "r") {
							$grouplist[$i]['read'] = "checked=checked";
						}
						break;
					case 1:
						if ($permission == "w") {
							$grouplist[$i]['write'] = "checked=checked";
						}
						break;
					case 2:
						if ($permission == "x") {
							$grouplist[$i]['execute'] = "checked=checked";
						}
						break;
				}
				$x++;
			}
			$i++;
		}
		return $grouplist;
	}
	
	function setUserPermissions($data)
	{
		// sets user permissions
		if ($data['read'] == "y") {
			$permission = "r";
		}
		else { 
			$permission = "-"; 
		}
		if ($data['write'] == "y") {
			$permission .= "w";
		}
		else { 
			$permission .= "-"; 
		}
		if ($data['execute'] == "y") {
			$permission .= "x";
		}
		else { 
			$permission .= "-"; 
		}
		exec('/usr/local/bin/setfacl -R -m user:'. $data['name'] .':'. $permission .' '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'');
	}
	
	function setGroupPermissions($data)
	{
		// sets group permissions
		if ($data['read'] == "y") {
			$permission = "r";
		}
		else { 
			$permission = "-"; 
		}
		if ($data['write'] == "y") {
			$permission .= "w";
		}
		else { 
			$permission .= "-"; 
		}
		if ($data['execute'] == "y") {
			$permission .= "x";
		}
		else { 
			$permission .= "-"; 
		}
		exec('/usr/local/bin/setfacl -R -m group:'. $data['name'] .':'. $permission .' '. $this->pooldir .'/'. $data['pool'] .'/'. $data['volume'] .'');
	}
}