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
 * @file		Volume.php
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
  
class Volume {

	function Volume($params) {
		// initialize parameters from controller
		$this->pooldir = $params['pooldir'];
		$this->filesystems = $params['filesystems'];
		$this->iscsiconfig = $params['iscsiconfig'];
	}

	function addVolume($volume)
	{
		// Adds a new volume to an existing pool.
		exec ('lvcreate -L'. $volume['size'] .' -n'. $volume['name'] .' '. $volume['pool'] .'');
		// Create iscsi device
		if ( $volume['iscsi'] == "y" ) {
			exec ('echo "<target '. $volume['iqn'] .'>" > '. $this->iscsiconfig .'/config/'. $volume['name'] .'.conf');
			exec ('echo backing-store /dev/'. $volume['pool'] .'/'. $volume['name'] .' >> '. $this->iscsiconfig .'/config/'. $volume['name'] .'.conf');
			exec ('echo "</target>" >> '. $this->iscsiconfig .'/config/'. $volume['name'] .'.conf');
			exec ('sync');
			exec ('/etc/init.d/iscsi reload', $output);
			foreach ($output as $line) {
				exec('echo '. $line .' >> /tmp/iscsi');
			}
		}
		else {
			// Create filesystem
			exec ('mkdir -p '. $this->pooldir .'/'. $volume['pool'] .'/'. $volume['name'] .'');
			exec ('chmod 777 '. $this->pooldir .'/'. $volume['pool'] .'/'. $volume['name'] .'');
			// Format and mount the volume
			exec ('mkfs.ext4 /dev/'. $volume['pool'] .'/'. $volume['name'] .'');
			exec ('mount -t ext4 -o acl /dev/'. $volume['pool'] .'/'. $volume['name'] .' '. $this->pooldir .'/'. $volume['pool'] .'/'. $volume['name'] .'');
			// Add the volume to filesystems file
			exec ('echo /dev/'. $volume['pool'] .'/'. $volume['name'] .'\|'. $this->pooldir .'/'. $volume['pool'] .'/'. $volume['name'] .' >> '. $this->filesystems .'');
			exec ('sync');
		}
	}

	function delVolume($volume)
	{	
		if ($volume['iscsi'] == "y") {
			exec ('rm '. $this->iscsiconfig .'/config/'. $volume['name'] .'.conf');
			exec ('/etc/init.d/iscsi reload');
		}
		else {
			// Unmount the volume
			exec ('umount -l '. $this->pooldir .'/'. $volume['pool'] .'/'. $volume['name'] .'');
			// Remove the volume from the filesystems file
			exec ('cat '. $this->filesystems .'', $filesystems);
			exec ('rm '. $this->filesystems .'');
			foreach ($filesystems as $filesystem) {
				if (! strpos($filesystem, $volume['name'])) {
					// doesn't work with it, don't know why - used fopen instead
					//exec ('echo '. $filesystem .' >> '. $this->filesystems .'');
					$fd = fopen($this->filesystems, 'a');
					$out = print_r($filesystem,true);
					fwrite($fd, $out);
					exec('sync');
					$fd = fopen($this->filesystems, 'a');
					fwrite($fd, chr(10));
				}
			}
			// Remove directory structure
			exec ('rm -r '. $this->pooldir .'/'. $volume['pool'] .'/'. $volume['name'] .'');
		}
		// Delete an existing volume.
		exec ('lvremove -f  /dev/'. $volume['pool'] .'/'. $volume['name'] .'');
	}
	
	function chgVolume($volume)
	{
		// Change the configuration of a volume.
		$currentsize = $volume['currentsize'];
		$newsize = $volume['newsize'];
		$minsize = $volume['minsize'];
		if ($currentsize > $newsize) {
			//shrink filesystem
			exec ('umount '. $this->pooldir .'/'. $volume['pool'].'/'. $volume['name'] .'');
			exec ('e2fsck -y -f /dev/'. $volume['pool'].'/'. $volume['name'] .'');
			if ($newsize == $minsize) {
				exec ('resize2fs /dev/'. $volume['pool'].'/'. $volume['name'] .' -M');
				exec ('echo "works" >> /tmp/output');
			}
			else {
				exec ('resize2fs /dev/'. $volume['pool'].'/'. $volume['name'] .' '. $newsize .'M');
			}
			exec ('lvchange -an '. $volume['pool'].'/'. $volume['name'] .'');
			exec ('lvresize -L '. $newsize .'M /dev/'. $volume['pool'].'/'. $volume['name'] .'');
			exec ('lvchange -ay '. $volume['pool'].'/'. $volume['name'] .'');
			exec ('e2fsck -y -f /dev/'. $volume['pool'].'/'. $volume['name'] .'');
			exec ('mount -t ext4 -o acl /dev/'. $volume['pool'].'/'. $volume['name'] .' '. $this->pooldir .'/'. $volume['pool'].'/'. $volume['name'] .'');
		}
		elseif ($newsize > $currentsize) {
			//grow filesystem
			exec ('lvresize -L '. $newsize .'M /dev/'. $volume['pool'].'/'. $volume['name'] .'');
			exec ('resize2fs /dev/'. $volume['pool'].'/'. $volume['name'] .'');
		}
		$size = exec('lvdisplay '. $volume['pool'].'/'. $volume['name'] .'|grep "Current LE"|awk \'{print $3}\'')*4;
		return $size;
	}
	
	function getVolsize($poolname, $volname) {
		$freespace = exec('tune2fs -l /dev/'. $poolname .'/'. $volname .'|grep "Free blocks"| awk \'{print $3}\'');
		$allspace = exec('tune2fs -l /dev/'. $poolname .'/'. $volname .'|grep "Block count"| awk \'{print $3}\'');
		$blocksize = exec('tune2fs -l /dev/'. $poolname .'/'. $volname .'|grep "Block size"| awk \'{print $3}\'');
		$usedspace = ceil((((($allspace - $freespace) * $blocksize ) / 1024 ) / 1024 ));
		return $usedspace;
	}
}
?>