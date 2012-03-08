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

class Plugin {
	
	function Install($params) {
		// initialize parameters from controller
		$this->update = $params['update'];
		$this->setup = $params['setup'];
	}

	function getUpdates() {
		// check pacman PulsarOS mirrors for updates
		$output = exec('pacman -Suy --print|grep -c "there is nothing to do"');
		return $output;
	}
	
	function chkPlugin($pluginname) {
		// check Pluginstatus
		$status = exec('pacman -Q '. $pluginname .'|wc -l');
		return $status;
	}
	
	function getPlugin($pluginname) {
		// install Plugin
		exec('pacman -S '. $pluginname .' --noconfirm --noprogressbar', $output);
		foreach ($output as $line) {
			exec('echo '. $line .' >> /tmp/plugininstall');	
		}
		exec('sync');
	}
	
	function remPlugin($pluginname) {
		// remove Plugin
		exec('pacman -R '. $pluginname .' --noconfirm --noprogressbar', $output);
		foreach ($output as $line) {
			exec('echo '. $line .' >> /tmp/plugininstall');	
		}
		exec('sync');
	}
}
?>