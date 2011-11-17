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
 * @file		setup.php
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
 
class Setup extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('setup' => $this->config->item('setup'),
						'interfaces' => $this->config->item('interfaces'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('network', $params);
		$this->load->library('install', $params);
	}
	
	function index()
	{
		$disks = $this->install->getDisks();
		if ($disks != 0) {
			$i = 0;
			foreach ($disks as $disk) {
				$html['disks'][$i]['name'] = $disk['device'];
				$html['disks'][$i]['capacity'] = $this->core->convByte($disk);
				$i++;
			}
		}
		$nwcards = $this->install->getNet();
		$i = 0;
		foreach ($nwcards as $nwcard) {
			$html['nwcards'][$i]['nwname'] = $nwcard;
			$i++;
		}
		//Show Site
		$this->load->view('setup/header');
		$this->load->view('setup/menu');
		$this->parser->parse('setup/index', $html);
		$this->load->view('footer');
	}
	
	function install()
	{
		if (!empty($_POST)) {
			$this->install->cfgOS($_POST);
			$status = array('code' => '1', 'message' => "<img src='images/tick.png' alt='Settings applied' />Your system is installed now. Please reboot it and remove any setup dvd's/cd's or sticks.");
			print json_encode($status);
		}
		else {
			$this->core->apiError();
		}
	}
}
?>