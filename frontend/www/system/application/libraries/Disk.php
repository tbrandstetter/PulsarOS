<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

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
 
class Disk {

	function Disk($params) {
		// initialize parameters from controller
		$this->storagedir = $params['storagedir'];
		$this->pool = $params['pool'];
		$this->volume = $params['volume'];
		$this->mdadm = $params['mdadm'];
	}

	function getDisks() 
	{
		// Returns a list of all available disks on the system.
		$rootdisk = $this->getRootdisk();
		exec('fdisk -l|grep Disk|grep -v partition|grep -v '. $rootdisk .'|grep -v "md"|grep -v "dm"|awk \'{print $2 $3" "$4}\'|awk -F: \'{print $1" "$2" "$3}\'', $list['disks']);
		if (empty($list['disks'])) {
			$output = 0;
		}
		else {
			$i=0;
			foreach($list['disks'] as $disk) {
				$data['disk'] = preg_split("/[\s,]+/", $disk);
				$output[$i]['device'] = $data['disk'][0];
				$output[$i]['capacity'] = $data['disk'][1];
				$output[$i]['type'] = $data['disk'][2];
				$output[$i]['id'] = exec('smartctl -a '. $data['disk'][0] .'|grep \'Serial Number\' | awk \'{print $3}\'');
				$i++;
			}
		}	 
		return $output;
	}
	
	function addDisk($disk)
	{
		// Adds a disk to the existing storage pool.
	}
	
	function delDisk($disk)
	{
		// Deletes a disk from the existing storage pool.
	}
	
	function addPool($pool)
	{
		// disk count
		$disks = explode(" ", $pool['disks']);
		$diskcount = count($disks)-1;
		// Search for an available md device
		if (exec('cat /proc/mdstat | grep -c md') == 0) {
			$mdev = "/dev/md0";
		}
		else {
			$i = 0;
			$mdev ="";
			do {
				if (exec('cat /proc/mdstat | grep -c md'. $i .'') == 0) {
					$mdev = "/dev/md$i";
					break;
				}
				$i++;
			} while (empty($mdev));
		}
		// Clean out old md devices first
		if (exec ('cat '. $this->pool .' | grep -c name') == "0") {
			exec ('cat /proc/mdstat | grep md | awk \'{print $1}\'', $md);
			foreach ($md as $device) {
				exec ('mdadm --stop /dev/'. $device .'');
			}
		}
		// Creates a pool with a bunch of $disks and mount it.
		exec ('mkdir '. $this->pooldir .'/'. $pool['name'] .'');
		switch($pool['raidlevel'])
		{
			case "raid0":
				if ( $diskcount == 1 ) {
					exec ('mdadm --create '. $mdev .' --run --level=stripe --force --raid-devices='. $diskcount .' '. $pool['disks'] .'');
					break;
				}
				else {
					exec ('mdadm --create '. $mdev .' --run --level=stripe --raid-devices='. $diskcount .' '. $pool['disks'] .'');
					break;
				}
				break;
			case "raid1":
				exec ('mdadm --create '. $mdev .' --run --level=mirror --raid-devices='. $diskcount .' '. $pool['disks'] .'');
				break;
			case "raid5":
				exec ('mdadm --create '. $mdev .' --run --level=5 --raid-devices='. $diskcount .' '. $pool['disks'] .'');
				break;
		}
		// Add the pool to the mdadm config file
		exec ('mdadm --detail --scan > '. $this->mdadm .'');
		exec ('sync');
		// Clean out old LVM2 configurations
		exec ('pvremove -ff -y '. $mdev .'');
		// Create volume group
		exec ('pvcreate '. $mdev .'');
		exec ('vgcreate '. $pool['name'] .' '. $mdev .'');
		exec ('vgchange -a y '. $pool['name'] .'');
		return $mdev;
	}
	
	function growPool($mdname) {
		exec('mdadm --grow /dev/'. $mdname .' --size=max');
		exec('pvresize /dev/'. $mdname .'');
		// change device states back to ready
		$xml = simplexml_load_file("/pulsarroot/configs/storage/pool.xml");
		foreach ($xml->xpath('//disk') as $disks) {
			$device = explode("/", $disks->device);
			if (exec('cat /proc/mdstat|grep '. $device[2] .'|awk \'{print $1}\'')  == $mdname) {
				$disks->status[0] = "ready";
				$disks->id[0] = exec('smartctl -a '. $disks->device .' | grep "Serial Number" | awk \'{ print $3}\'');
			}
		}
		$xml->asXML('/pulsarroot/configs/storage/pool.xml');
	}
	
	function delPool($pool)
	{
		//Remove the LVM pool
		$mdev = exec ('pvdisplay -c | grep '. $pool['name'] .' | awk -F: \'{print $1}\'');
		exec('vgremove -f '. $pool['name'] .'');
		exec('pvremove -f '. $mdev .'');
		exec('mdadm --stop '. $mdev .'');
		foreach($pool['disks'] as $disk):
			exec ('dd if=/dev/zero of='. $disk .' bs=8192 count=10');
		endforeach;
		// Remove the pool from filesystems file
		exec ('mdadm --detail --scan > '. $this->mdadm .'');
		exec ('sync');
	}
	
	function getPoolsize($name, $remaining="") 
	{
		if (!empty($remaining)) {
			return exec ('vgdisplay '. $name .' | grep "Free  PE" | awk \'{print $5}\'') * 4;
		}
		else {
			return exec ('vgdisplay '. $name .' | grep "Total PE" | awk \'{print $3}\'') * 4;
		}
	}
	
	function getRootdisk() {
		return substr(exec('blkid -t LABEL=PULSARROOT|awk \'{ print $1}\'| cut -d : -f 1,1'), 0, -1);
	}
	
	function getState($disks)
	{
		$device = $disks['device'];
		$id = $disks['id'];
		$state = $disks['status'];
		$diskid = exec('smartctl -a '. $device .'|grep \'Serial Number\' | awk \'{print $3}\'');
		$smartstatus = $this->getSmartstate($device);
		if ( $diskid == $id ) {
			if ($state == "new" ) {
				return "new";
			}
			else {
				return $smartstatus;
			}
		}
		elseif ( !empty($diskid) && exec ('grep -c '. $diskid .' '. $this->pool .'') == 0 && $this->getRootdisk() != $device) {
			return "new";
		}
		else {
			exec('fdisk -l |grep Disk|awk \'{print $2}\'|awk -F: \'{print $1}\'', $disks);
			foreach ( $disks as $disk) {
				if ( $disk != $device ) {
					$diskid = exec('smartctl -a '. $disk .'|grep \'Serial Number\' | awk \'{print $3}\'');
					if ( $diskid == $id ) {
						return "on";
					}
				}
				else {
					return "off";
				}
			}
		}
	}
	
	function getSyncstate($device) {
		if (exec('cat /proc/mdstat |grep -c recovery') != 0 || exec('cat /proc/mdstat |grep -c resync') != 0) {
			exec('cat /proc/mdstat',$output);
			$i=0;
			foreach ($output as $line) {
				if (strpos($line, $device) !== false ) {
					if (!empty($output[$i+2])) {
						$syncstate=explode(" ",$output[$i+2]);
						// Fix for /proc/mdstat spaces
						$syncpercent1 = $syncstate[10];
						if (!strpos($syncstate[11], "/")) {
							$syncpercent2 = $syncstate[11];
						}
						$syncfinish = round(substr($syncstate[13],7, -3)/60,0);
						print "<p><b>Pool syncing!</b></p><p>Complete: $syncpercent1 $syncpercent2</p><p>Finish in: $syncfinish hours</p>";
						break;
					}
				}
				$i++;
			}
		}
		else {
			print "<p>Pool ready!</p>";
		}
	}
	
	function getMDState($device) {
		if (exec('cat /proc/mdstat |grep -c recovery') != 0 || exec('cat /proc/mdstat |grep -c resync') != 0) {
			return FALSE;
		}
		else {
			return TRUE;
		}
	}
	
	function getSmartstate($device) {
		$smartstate = exec('smartctl -H '. $device .'|grep -c PASSED');
		$smartavail = exec('smartctl -H '. $device .'|grep -c Unavailable');
		if ($smartavail == "0") {
			if ($smartstate == "1") {
				$output = "ready";
			}
			else {
				$output = "failure";
			}
		}
		else {
			$output = "ready";
		}
		return $output;
	}
}
?>