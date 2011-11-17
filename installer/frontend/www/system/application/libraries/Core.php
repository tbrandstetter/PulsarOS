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
 * @file		Core.php
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
  
class Core {

	function Core($params) {
		// initialize parameters from controller
		$this->backup = $params['backup'];
		$this->www_root = $params['www_root'];
		$this->pulsarroot = $params['pulsarroot'];
		$this->version = $params['version'];
	}
	
	function pulsarVersion()
	{
		return file_get_contents($this->version);
	}
	
	function apiError() 
	{
		print "API Error! Please send the problem to a PulsarOS developer!";
	}
	
	function getSysinfo()
	{
		$cpuinfo = exec('cat /proc/cpuinfo |grep "model name"| awk \'{ print $4" "$5" " $6" "$7" "$8" "$9 }\'');
		$kernelversion = exec('uname -r');
		$memory = exec('cat /proc/meminfo |grep "MemTotal"|awk \'{ print $2 }\'');
		$memorysize = floor($memory / 1000);
		$output = "<div>
				<p><span>Processor: $cpuinfo</span>
				<span>Kernel Version: $kernelversion</span>
				<span>Memory: $memorysize Mbyte</span></p>
			   </div>"
			   ;
		return $output;
	}

	function getUptime()
	{
		$output = exec('uptime|awk \'{print $2" "$3" "$4" "$5" "$6" "$7" "$8" "$9}\'');
		return $output;
	}

	function getStorage()
	{
		$storageid = exec('btrfs-show |grep "Label"|awk \'{ print $1" "$2 }\'', $pools);
		$i = 0;
		foreach ($pools as $pool) {
				$pooldesc = explode(" ", $pool);
				$poolname[$i]['name'] = $pooldesc[1];
				$i++;
		}
		if ($i == 0) {
			return "<p>No storage configured yet!</p>";	
		}
		return $poolname;
	}
	
	// Calculate MB,GB,TB based on numbersize
	function calcByte($number) {
		if ($number > 1000000) {
			$capacity = round($number/1000000,2);
			$number = ''. $capacity .'TB';
		}
		elseif ($number > 1000) {
			$capacity = round($number/1000,2);
			$number = ''. $capacity .'GB';
		}
		else  {
			$capacity = round($number,2);
			$number = ''. $capacity .'MB';
		}
		return $number;
	}
	
	function convByte($disk) {
		$disk['capacity'] = (int) $disk['capacity']; 
		if ($disk['type'] == "GB" && strlen($disk['capacity']) == 4) {
			$disksize = ''. round($disk['capacity']/1024) .'TB';
		}
		elseif ($disk['type'] == "GB" && strlen($disk['capacity']) < 4) {
			$disksize = ''. $disk['capacity'].'GB';
		}
		elseif ($disk['type'] == "MB" && strlen($disk['capacity']) == 4) {
			$disksize = ''. round($disk['capacity']/1024) .'GB';
		}
		elseif ($disk['type'] == "MB" && strlen($disk['capacity']) < 4) {
			$disksize = ''. $disk['capacity'].'MB';
		}
		return $disksize;
	}
	
	function array_search($needle,$haystack,$arraykey=FALSE)
	{
		// thx to Frank A Cefalu frankcefalu at gmail dot com who wrote this function on the php.net site
		foreach($haystack as $key=>$value) {
			$current_key=$key;
			if($arraykey) {
				if($needle == $value[$arraykey]) {
					return $value['id'];
            	}
          		if(array_search($needle,$value[$arraykey]) == true) {
            		return $current_key;
            	}
            }
            else {
            	if($needle == $value) {
            		return $value;
            	}
            	if(array_search($needle,$value) == true) {
                	return $current_key;
            	} 
        	}
        }
    	return false;
	}
	
	function chgConfig($data) 
	{
		$configfile = exec('cat '. $data['path'] .'', $output);
		exec ('rm '. $data['path'] .'');
		if (!empty($output)) {
		foreach ($output as $configline ) {
			$line = explode("=", $configline);
			$key = trim($line[0]);
			$item = trim($line[1]);
			if (!empty($data['action']) && $key == $data['key']) {
				switch($data['action']) {
					case "add":
						$item .= " $data[value]";
						exec('echo "'. $key .' = '. $item .'" >> '. $data['path'] .'');
						break;
					case "delete":
						$values = explode(" ", $item);
						$item = "";
						foreach ($values as $value) {
							if (strpos($value, $data['value']) === false) {
								$item .= "$value";
							}
						}
						if (empty($item)) {
							exec('echo "'. $key .' = " >> '. $data['path'] .'');
						}
						else {
							exec('echo "'. $key .' = '. $item .'" >> '. $data['path'] .'');
						}
						break;
					case "change":
						$item = $data['value'];
						exec('echo "'. $key .' = '. $item .'" >> '. $data['path'] .'');
						break;
					case "deleteall":
						if (strpos($configline, "$data[value]") === false) {
							exec('echo "'. $key .' = '. $item .'" >> '. $data['path'] .'');
						}
						break;
				}
			}
			else {
				if (!isset($item)) {
					exec('echo "'. $key .'" >> '. $data['path'] .'');
				}
				else {
					exec('echo "'. $key .' = '. $item .'" >> '. $data['path'] .'');
				}
			}
		}
		}
	}
	
	function createBackup() 
	{
		if (file_exists(''. $this->pulsarroot .'/pulsaros_backup.tar.bz2')) {
			exec('rm '. $this->pulsarroot .'/pulsarroot_backup.tar.bz2');
		}
		exec('cd '. $this->pulsarroot .' && tar -cf pulsaros_backup.tar configs && bzip2 -9 pulsaros_backup.tar');
		exec('mv '. $this->pulsarroot .'/pulsaros_backup.tar.bz2 '. $this->www_root .'');
	}
	
	function loginStatus($data)
	{
		if (!isset($data) || $data != TRUE)
		{	
			header('Location: index.php?login');
		}		
	}
	
	
	
/*	function restartService($service) {
		exec('monit -c /pulsarroot/configs/monitrc restart '. $service .'');
	} */
}
?>