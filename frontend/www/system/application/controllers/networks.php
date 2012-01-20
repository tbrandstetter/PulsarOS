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
 * @file		networks.php
 * @version		0.7alpha
 * @date		22/07/2011
 * 
 * Copyright (c) 2009-2011
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
 
class Networks extends Controller
{

	
	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('interfaces' => $this->config->item('interfaces'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('configs', $params);
		$this->load->library('network', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		$html['nwcards'] = $this->network->getNetwork();
		$html['defaultgw'] = $html['nwcards'][0]['defaultgw'];
		$html['nameserver'] = $html['nwcards'][0]['nameserver'];
		$html['mtu'] = $html['nwcards'][0]['mtu'];
		$html['cards'] = $html['nwcards'][0]['cards'];
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('admin/network', $html);
		$this->load->view('footer');
	}
	
	function chg()
	{
		if (!empty($_POST)) {
			$nwcards = trim(htmlspecialchars($_POST['cards']));
			$cards = explode(" ", $nwcards);
			foreach ($cards as $card) {
				$network['card'] = $card;
				$network['activate'] = htmlspecialchars($_POST[''. $card .'_activate']);
				$network['dhcp'] = htmlspecialchars($_POST[''. $card .'_dhcp']);
				$network['address'] = htmlspecialchars($_POST[''. $card .'_ipaddr']);
				$network['netmask'] = htmlspecialchars($_POST[''. $card .'_netmask']);
				$network['gateway'] = htmlspecialchars($_POST[''. $card .'_gateway']);
				$network['mtu'] = htmlspecialchars($_POST[''. $card .'_mtu']);
				$network['defaultgw'] = htmlspecialchars($_POST['defaultgw']);
				$network['nameserver'] = htmlspecialchars($_POST['nameserver']);
				$this->network->chgNetwork($network);
			}
			//copy changed config to pulsarroot
			$this->configs->chgConfig("/etc/defaultrouter", $this->config->item('nwdir'));
			$this->configs->chgConfig("/etc/resolv.conf", $this->config->item('nwdir'));
			$status = array('code' => '0', 'message' => "Your network settings have changed. Please reboot.");
			print json_encode($status);
		}
	}
}
?>