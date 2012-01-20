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
 * @file		settings.php
 * @version		0.5alpha
 * @date		04/08/2010
 * 
 * Copyright (c) 2009-2010
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
 
class Settings extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array('power' => $this->config->item('power'),
						'hostname' => $this->config->item('hostname'),
						'ntpd' => $this->config->item('ntpd'),
						'smtp' => $this->config->item('smtp'),
						'smtp_log' => $this->config->item('smtp_log'),
						'spindown' => $this->config->item('spindown'));
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		$this->load->library('configs', $params);
		$this->load->library('setting', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		$powermode = $this->setting->getPowermode();
		$timezone = $this->setting->getTimezone();
		$html[$timezone] = "selected";
		$html['hostname'] = $this->setting->getHostname();
		$html['timeserver'] = $this->setting->getTimeserver();
		$html['spindown'] = $this->setting->getSpindown();
		$html['mailsettings'][0] = $this->setting->getMailsettings();
		switch ($powermode[0]) {
    	case 'none':
        	$html['none'] = "selected";
        	break;
    	case 'poweroff':
        	$html['poweroff'] = "selected";
        	break;
    	case 'hibernate':
        	$html['hibernate'] = "selected";
        	break;
		case 'standby':
			$html['standby'] = "selected";
			break;
		}
		$html['timeout'] = $powermode[1];
		$this->load->view('header');
		$this->load->view('menu');
		$this->parser->parse('admin/settings', $html);
		$this->load->view('footer');
	}
	
	function chg()
	{
		if (!empty($_POST)) {
			$hostname = htmlspecialchars($_POST['hostname']);
			$timeserver = htmlspecialchars($_POST['timeserver']);
			$timezone = htmlspecialchars($_POST['timezone']);
			$spindown = htmlspecialchars($_POST['spindown']);
			$powermode = htmlspecialchars($_POST['powermode']);
			$timeout = htmlspecialchars($_POST['timeout']);
			$password = htmlspecialchars($_POST['password']);
			$settings['email'] = htmlspecialchars($_POST['email']);
			$settings['smtp'] = htmlspecialchars($_POST['smtp']);
			$settings['user'] = htmlspecialchars($_POST['user']);
			$settings['password'] = htmlspecialchars($_POST['pass']);
			$settings['tls'] = htmlspecialchars($_POST['tls']);
			$settings['hostname'] = $hostname;
			$this->setting->chgHostname($hostname);
			$this->setting->chgTimeserver($timeserver);
			$this->setting->chgSpindown($spindown);
			$this->setting->chgPowermode($powermode, $timeout);
			$this->setting->chgPwd($password);
			$this->setting->chgMailsettings($settings);
			$this->setting->chgTimezone($timezone);
			$this->setting->chgRoot($password);
			$this->configs->chgConfig("/etc/passwd", $this->config->item('confdir'));
			$this->configs->chgConfig("/etc/TZ", $this->config->item('confdir'));
		}
	}
}
?>