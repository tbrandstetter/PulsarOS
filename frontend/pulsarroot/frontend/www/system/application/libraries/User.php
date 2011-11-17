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
 
class User {

	function User($params) {
		// initialize parameters from controller
		$this->userdir = $params['userdir'];
		$this->confdir = $params['confdir'];
	}

	function getUser($repository)
	{
		// gets a list of all users depending of the activated repository
		switch($repository)
		{
			case "shadow":
				$output = array();
				exec('cat /etc/passwd| grep pulsarroot', $users);
				$i = 0;
				foreach ($users as $user) {
					$userdesc = explode(":", $user);
					$output[$i]['name'] = $userdesc[0];
					$output[$i]['desc'] = $userdesc[4];
					$i++;
				}
				return $output;
				break;
			case "ldap":
				break;
		}
	}

	function addUser($repository, $user)
	{
		// adds a user to the defined $repository
		switch($repository)
		{
			case "shadow":
				if ($user['scponly'] == "y" ) {
					exec('adduser -s /usr/libexec/sftp-server -G users -D -h '. $this->userdir .'/'. $user['name'] .' '. $user['name'] .'');
				}
				else {
					exec('adduser -s /bin/sh -G users -D -h '. $this->userdir .'/'. $user['name'] .' '. $user['name'] .'');
				}
				exec('(echo '. $user['password'] .'; sleep 1; echo '. $user['password'] .') | passwd '. $user['name'] .'');
				// nasty problem with busybox adduser - creates a group too
				exec('delgroup '. $user['name'] .'');
				exec('(echo '. $user['password'] .'; echo '. $user['password'] .' ) | /usr/local/bin/smbpasswd -s -a '. $user['name'] .'');	
				break;
		}
	}
	
	function delUser($repository, $user)
	{
		// deletes a user from the defined $repository
		switch($repository)
		{
			case "shadow":
				exec('deluser '. $user['name'] .'');
				exec('smbpasswd -x '. $user['name'] .'');
				exec('rm -r '. $this->userdir .'/'. $user['name'] .'');
				break;
		}
	}
	
	function chgUser($repository, $user)
	{
		// changes a user password from the defined $repository
		switch($repository)
		{
			case "shadow":
				if (isset($user['password'])) {
					exec('(echo '. $user['password'] .'; echo '. $user['password'] .' ) | /usr/local/bin/smbpasswd -s '. $user['name'] .'');
				}
				if (isset($user['description'])) {
					$olddescription = exec('cat /etc/passwd|grep tbrandstetter|awk -F: \'{print $5}\'');
					exec('sed "s/'. $olddescription .'/'. $user['description'] .'/" /etc/passwd > /tmp/passwd.tmp');
					exec('mv /tmp/passwd.tmp /etc/passwd');
				}
				break;
		}
	}
	
	function getGroup($repository)
	{
		// gets a list of all groups depending of the activated repository
		switch($repository)
		{
			case "shadow":
				$output = array();
				$hiddengroups=" root daemon bin sys adm tty disk wheel utmp staff haldaemon dbus netdev nobody nogroup users default audio ftp";
				exec('cat /etc/group', $groups);
				$i = 0;
				foreach ($groups as $group) {
					$groupdesc = explode(":", $group);
					if (!strpos($hiddengroups, $groupdesc[0])) {
						$output[$i]['name'] = $groupdesc[0];
						$i++;
					}
				}
				return $output;
				break;
			case "ldap":
				break;
		}
	}
	
	function addGroup($repository, $group)
	{
		// adds a group to the defined $repository
		switch($repository)
		{
			case "shadow":
				exec('addgroup '. $group['name'] .'');
				break;
		}
	}
	
	function delGroup($repository, $group)
	{
		// deletes a user from the defined $repository
		switch($repository)
		{
			case "shadow":
				exec('delgroup '. $group['name'] .'');
				break;
		}
	}
}