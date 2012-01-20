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
 * @file		login.php
 * @version		0.6alpha
 * @date		17/02/2011
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

class Login extends Controller
{

	function __construct()
	{
		parent::Controller();
		// set config parameters
		$params = array();
						
		// Initialize needed libraries
		$this->load->library('parser');
		$this->load->library('core', $params);
		
		// check if user is authenticated
		if ($this->session->userdata('is_logged_in') == TRUE ) {
			header('Location: index.php?admin');
		}
	}
	
	function index()
	{
		$html['status'] = "";
		if (!empty($_POST)) {
			$username = $_POST['user'];
			$password = $_POST['password'];
			exec('sshpass -p '. $password .' ssh -y -l root localhost touch /tmp/correct');
			if ($username != 'admin' ||  !file_exists('/tmp/correct')) {
				$html['status'] = "<span>Wrong user or password!</span>";
			}
			else {
				$data = array( 'username' => $username, 'is_logged_in' => TRUE );
				$this->session->set_userdata($data);
				exec('rm /tmp/correct');
				header('Location: index.php?login');
			}
		}
		// Show Site
		$this->parser->parse('login', $html);
	}
	
	function logout()
	{
		$this->session->sess_destroy();
		$this->index();
	}
}
?>