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
 * @file		Network.php
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
 
class Network {

	function Network($params) {
		// initialize parameters from controller
		$this->interfaces = $params['interfaces'];
		$this->nwdir = $params['nwdir'];
	}

	function getNetwork()
	{
		exec('ifconfig -a|grep -v lo|grep Link|awk \'{ print $1}\'', $output);
		$i = 0;
		// initialize array of all network cards
		$network[0]['cards'] = "";
		foreach ($output as $nwcard) {
			$network[0]['cards'] .= " $nwcard";
			$network[$i]['card'] = $nwcard;
			if (file_exists(''. $this->interfaces .'_'. $nwcard .'')) {
				$network[$i]['activate'] = "checked=checked";
			}
			$networktype = exec('grep "iface '. $nwcard .'" '. $this->interfaces .'_'. $nwcard .'|grep -c dhcp');
			if ( $networktype == 1 ) {
				$network[$i]['dhcp'] = "checked=checked";
				$network[$i]['ip'] = "";
				$network[$i]['netmask'] = "";
				$network[$i]['gateway'] = "";
			}
			else {
				$network[$i]['dhcp'] = "";
				$network[$i]['ip'] = exec('sed -n \'/iface '. $nwcard .'/ {p; n; p;}\' '. $this->interfaces .'_'. $nwcard .'|grep address|awk \'{print $2}\'');
				$network[$i]['netmask'] = exec('sed -n \'/iface '. $nwcard .'/ {p; n; p; n; p;}\' '. $this->interfaces .'_'. $nwcard .'|grep netmask|awk \'{print $2}\'');
				$network[$i]['gateway'] = exec('sed -n \'/iface '. $nwcard .'/ {p; n; p; n; p; n; p;}\' '. $this->interfaces .'_'. $nwcard .'|grep gateway|awk \'{print $2}\'');
			}
			$i++;
		}
		$network[0]['defaultgw'] = exec('cat /etc/defaultrouter');
		$network[0]['nameserver'] = exec('cat /etc/resolv.conf|awk \'{print $2}\'');
		return $network;
	}
	
	function chgNetwork($network) {
		exec('rm '. $this->interfaces .'_'. $network['card'] .'');
		if ($network['activate'] == "y" ) {
			if ($network['dhcp'] == "y" ) {
				exec('echo "auto '. $network['card'] .'" >> '. $this->interfaces .'_'. $network['card'] .'');
				exec('echo "iface '. $network['card'] .' inet dhcp" >> '. $this->interfaces .'_'. $network['card'] .'');
			}
			else {
				exec('echo "auto '. $network['card'] .'" > '. $this->interfaces .'_'. $network['card'] .'');
				exec('echo "iface '. $network['card'] .' inet static" >> '. $this->interfaces .'_'. $network['card'] .'');
				if (!empty($network['address'])) {
					exec('echo "address '. $network['address'] .'" >> '. $this->interfaces .'_'. $network['card'] .'');
				}
				if (!empty($network['netmask'])) {
					exec('echo "netmask '. $network['netmask'] .'" >> '. $this->interfaces .'_'. $network['card'] .'');
				}
				if (!empty($network['gateway'])) {
					exec('echo "gateway '. $network['gateway'] .'" >> '. $this->interfaces .'_'. $network['card'] .'');
				}
				if (!empty($network['mtu'])) {
					exec('echo "mtu '. $network['mtu'] .'" >> '. $this->interfaces .'_'. $network['card'] .'');
				}
			}
		}
		exec('echo '. $network['defaultgw'] .' > /etc/defaultrouter');
		if (!empty($network['nameserver'])) {
			exec('echo "nameserver '. $network['nameserver'] .'" > /etc/resolv.conf');
		}
		else {
			exec('echo "" > /etc/resolv.conf');
		}
	}
}
?>