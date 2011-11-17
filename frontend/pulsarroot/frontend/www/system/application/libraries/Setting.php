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
 * @file		Setting.php
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
 
class Setting {

	function Setting($params) {
		// initialize parameters from controller
		$this->power = $params['power'];
		$this->hostname = $params['hostname'];
		$this->ntpd = $params['ntpd'];
		$this->smtp = $params['smtp'];
		$this->smtp_log = $params['smtp_log'];
		$this->spindown = $params['spindown'];
		$this->timezone = $params['timezone'];
	}
	
	function getHostname() {
		return exec('cat '. $this->hostname .'');
	}

	function chgHostname($hostname) {
		exec('echo '. $hostname .' > '. $this->hostname .'');
		exec('hostname -F '. $this->hostname .'');
		exec('sync');
	}
	
	function getTimezone() {
		return exec('cat /etc/TZ');
	}
	
	function chgTimezone($timezone) {
		exec('echo '. $timezone .' > /etc/TZ');
	}
	
	function getTimeserver() {
		return exec('cat '. $this->ntpd .'');
	}
	
	function chgTimeserver($timeserver) {
		if (empty($timeserver)) {
			$timeserver = "127.127.1.1";
		}
		exec('echo '. $timeserver .' > '. $this->ntpd .'');
		$this->restartService('ntpd');
	}
	
	function chgRoot($password) {
		if (!empty($password)) {
			exec('(echo '. $password .'; sleep 1; echo '. $password .') | passwd root');
		}
	}
	
	function getMailsettings() {
		$mail['email'] = exec('cat '. $this->smtp .' |grep "user "|awk \'{print $2}\'');
		$mail['smtp'] = exec('cat '. $this->smtp .' |grep "host "|awk \'{print $2}\'');
		$mail['pass'] = exec('cat '. $this->smtp .' |grep "password "|awk \'{print $2}\'');
		if (exec('cat '. $this->smtp .' |grep "tls_starttls"|awk \'{print $2}\'') == "on") {
			$mail['tls'] = "checked=checked";
		}
		return $mail;
	}
	
	function chgMailsettings($settings) {
		exec('echo "defaults" > '. $this->smtp .'');
		if ($settings['tls'] == "y") {
			exec('echo "tls on" >> '. $this->smtp .'');
			exec('echo "tls_starttls on" >> '. $this->smtp .'');
			exec('echo "tls_certcheck off" >> '. $this->smtp .'');
			$port = "587";
		}
		else {
			$port = "25";
		}
		exec('echo "account default" >> '. $this->smtp .'');
		exec('echo "host '. $settings['smtp'] .'" >> '. $this->smtp .'');
		exec('echo "port '. $port .'" >> '. $this->smtp .'');
		exec('echo "auth on" >> '. $this->smtp .'');
		exec('echo "user '. $settings['email'] .'" >> '. $this->smtp .'');
		exec('echo "password '. $settings['password'] .'" >> '. $this->smtp .'');
		exec('echo "from root@'. $settings['hostname'] .'.local" >> '. $this->smtp .'');
		exec('echo "logfile '. $this->smtp_log .'" >> '. $this->smtp .'');
	}
	
	function getSpindown() {
		if (file_exists($this->spindown)) {
			return round(exec('cat '. $this->spindown .'')*5);
		}
		return NULL;
	}
	
	function chgSpindown($spindown) {
		if (empty($spindown)) {
			exec('rm '. $this->spindown .'');
		}
		else {
			$spindown = round($spindown / 5);
			exec('echo '. $spindown .' > '. $this->spindown .'');
			exec('/etc/init.d/spindown');
		}
	}
	
	function getPowermode() {
		if (file_exists($this->power)) {
			$powermode = explode(" ", exec('cat '. $this->power .''));
		}
		return $powermode;
	}
	
	function chgPowermode($powermode, $timeout) {
		if ($timeout != "0") {
			exec('echo '. $powermode .' '. $timeout .' > '. $this->power .'');
		}
		else {
			exec('echo none '. $timeout .' > '. $this->power .'');
		}
	}
	
	function chgPwd($password) {
		// changes the admin password
		if (!empty($password)) {
			exec('(echo '. $password .'; sleep 1; echo '. $password .') | passwd root');
		}
	}

	
	function restartService($service) {
		exec('monit restart '. $service .'');
	} 
}