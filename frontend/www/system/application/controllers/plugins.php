<?php

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
 
class Plugins extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array();
						
		// Initialize needed libraries
		$this->load->library('plugin');
		$this->load->library('parser');
		$this->load->library('core', $params);
		
		// check if user is authenticated
		$this->core->loginStatus($this->session->userdata('is_logged_in'));
	}
	
	function index()
	{
		$this->load->view('header');
		$this->load->view('menu');
		
		exec('ping -c 4 '. $this->config->item('mirror') .'', $output, $status);
		if ($status != 0) {
			$html['plugins'] = "Network error - Please check your network connection!";
			$this->parser->parse('plugins/error', $html);
		}
		else {
			// goes to library function Plugin.php
			$plugins = json_decode(file_get_contents('http://plugins.pulsaros.com/index.php/api/plugins/format/json'));
			
			foreach ($plugins->plugin as $plugin) {
				$html['plugin'][$i]['name'] = $plugin->name;
				// $html['plugin'][$i]['author'] = $plugin->author;
				$html['plugin'][$i]['version'] = $plugin->version;
				$html['plugin'][$i]['logo'] = "$plugin->name.png";
				
				// goes to library function Plugin.php
				if ($this->plugin->chkPlugin(strtolower($plugin->name)) == "1") {
					$html['plugin'][$i]['status'] = "Uninstall";
				}
				// tbd --> Plugin new version
				else {
					$html['plugin'][$i]['status'] = 'Install';
				}
				$i++;
			}
			$this->parser->parse('plugins/index', $html);
		}
		$this->load->view('footer');
	}

	function install()
	{
		if ($_POST['install'] == "y") {
			$pluginname = strtolower($_POST['pluginname']);
			if ($_POST['status'] == "Install") {
				$this->plugin->getPlugin($pluginname);		
			}
			elseif ($_POST['status'] == "Uninstall") {
				$this->plugin->remPlugin($pluginname);		
			}
		}
		else {
			exec('ping -c 4 '. $this->config->item('mirror') .'', $output, $status);
			if ($status != 0) {
				$html['update'] = "Network error - Please check your network connection!";
			}
			$this->load->view('header');
			$this->load->view('menu');
			$this->parser->parse(plugins/error, $html);
			$this->load->view('footer');
		}
	}

	function config($pluginname)
	{
		// show configuration settings for plugins. $pluginname comes from codeigniters routing (routes.php)
		// if no plugin is installed redirect to plugin index
		$plugin = strtolower($pluginname);
		if ($this->plugin->chkPlugin($plugin) == "1") {
			$this->load->view('header');
			$this->load->view('menu');
			$this->load->view("plugins/$plugin");
			$this->load->view('footer');
		}
		else {
			header('Location: index.php?plugins');
		}
	}
}
?>