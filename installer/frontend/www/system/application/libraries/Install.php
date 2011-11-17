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
 * @file		Install.php
 * @version		0.7alpha
 * @date		17/06/2011
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

class Install {
	
	function Install($params) {
		// initialize parameters from controller
		$this->update = $params['update'];
		$this->setup = $params['setup'];
	}

	function getDisks() {
		exec('fdisk -l|grep Disk|grep -v partition|awk \'{print $2 $3" "$4}\'|awk -F: \'{print $1" "$2" "$3}\'', $list['disks']);
		if (empty($list['disks'])) {
			$output = 0;
		}
		else {
			// mount installer image
			exec(''. $this->setup .' get_disks');
			$i=0;
			foreach($list['disks'] as $disk) {
				$data['disk'] = preg_split("/[\s,]+/", $disk);
				$output[$i]['device'] = $data['disk'][0];
				$output[$i]['capacity'] = $data['disk'][1];
				$output[$i]['type'] = $data['disk'][2];
				$i++;
			}
		}
		return $output;
	}
	
	function getNet() {
		// html format of the network cards
		exec(''. $this->setup .' get_net', $output);
		return $output;
	}
	
	function cfgOS($data) {
		// install PulsarOS to disk
		if (isset($data['dhcp'])) {
			exec(''. $this->setup .' install_os '. $data['disk'] .' '. $data['nwcard'] .' '. $data['dhcp'] .' '. $data['hostname'] .'');
		}
		else {
			exec(''. $this->setup .' install_os '. $data['disk'] .' '. $data['nwcard'] .' "n" '. $data['hostname'] .' '. $data['ipaddr'] .' '. $data['netmask'] .' '. $data['gateway'] .' '. $data['nameserver'] .'');
		}
	}
	
	function getUpdates() {
		// check pacman PulsarOS mirrors for updates
		$output = exec('pacman -Suy --print|grep -c "there is nothing to do"');
		return $output;
	}
	
	function toUpdate() {
		// update PulsarOS system
		exec('mount /boot');
		exec('pacman -Suyq --noconfirm --noprogressbar');
		exec('sync');
		// fix permissions
		exec('chown -R root:root /boot');
		exec('chown -R root:root /pulsarroot/bin /pulsarroot/configs /pulsarroot/frontend /pulsarroot/plugins');
		// run postupgrade script to reflect changes which are not covered by the pacman updater
		if (file_exists('/pulsarroot/bin/postupgrade.sh')) {
			exec('/pulsarroot/bin/postupgrade.sh');
			exec('mv /pulsarroot/bin/postupgrade.sh /pulsarroot/bin/postupgrade.sh_old');
		}
		exec('umount /boot');
	}
}
?>