<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

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
 
class Configs {

	function Configs($params) {
		// initialize parameters from controller
		$this->storagedir = $params['storagedir'];
		$this->pool = $params['pool'];
		$this->volume = $params['volume'];
	}

	function getSettings($function) {
		// Creates a list or just a single value of the given $elements from the given $function.
		$xml = $this->getXML($function);
		return $xml;
	}
	
	function addSettings($function, $elements, $startelements="") {
		// Creates an $element from the given $function XML file.
		$xml = $this->getXML($function);	
		$root_node = $xml->addChild($function);
		foreach ($elements as $element => $value) {
			$root_node->addChild($element, $value);
			//print "Adding Child Element: $element Value: $value <br />";
		}
		if (!empty($startelements)) {
			//print "<br />";
			//print "Adding Subchild Element: $startelements[0] <br />";
			foreach ($startelements[1] as $array) {
				$xml_node = $root_node->addChild($startelements[0]);
				foreach ($array as $element => $value) {
					//print "Adding Subchild Elements: $element Value: $value <br />";
					$xml_node->addChild($element, $value);
				}
			}
		}
		$xml->asXML(''. $this->storagedir .'/'. $function .'.xml');
	}
	
	function delSettings($function, $element, $value) {
		// Deletes the given $element from the given $function XML file.
		$xml = $this->getXML($function);
		$count = 0;
		foreach ($xml as $elements) {
			if ($elements->$element == "$value") {
				// workaround for the variable/array --> needs to be fixed
				switch($function)
				{
					case "volume":
						unset($xml->volume[$count]);
						break;
					case "pool":
						unset($xml->pool[$count]);
						break;
				}
				break;
			}
			$count++;
		}
		$xml->asXML(''. $this->storagedir .'/'. $function .'.xml');
	}
	
	function chkSettings($function, $element, $value="") {
		if ($function == "user") {
			if (exec('grep -c '. $value .' /etc/passwd') != 0) {
				return 1;
			}
			else {
				return 0;
			}
		}
		elseif ($function == "group") {
			if (exec('grep -c '. $value .' /etc/group') != 0) {
				return 1;
			}
			else {
				return 0;
			}
		}
		else {
			// Check if there is a given $element in the given $function XML file. If a $value is there,
			// the value will also be checked against the XML file. If not a value from that given $element will be delivered.
			$xml = $this->getXML($function);
			if ($value != "") {
				foreach ($xml->xpath("//$element") as $content) {
					if ($content == $value) {
						return 1;
					}		
				}
			}
			else {
				$value = $xml->xpath("//$element");
				if (!empty($value)) {
					return 1;
				} 
			}
			return 0;
		}
	}
	
	function chgSettings($function, $element, $value) {
		// Changes the given $element in the given $function XML file with the value.
		// Deletes the given $element from the given $function XML file.
		$xml = $this->getXML($function);
		if (is_array($element)) {
			foreach ($xml->$function as $node) {
				if ($node->$element['node'] == $element['nodevalue']) {
					$node->$element['subnode'] = $value;
				}
			}
		}
		else {
			$xml->$function->$element = $value;
		}
		$xml->asXML(''. $this->storagedir .'/'. $function .'.xml');
	}
	
	function getXML($function)
	{
		// Load the desired XML File depending on the $function
		// Allowed functions: 	volume
		//						pool
		switch($function)
		{
			case "volume":
				if (file_exists(''. $this->volume .'') && file_get_contents(''. $this->volume .'') !='') {
					$xml = simplexml_load_file(''. $this->volume .'');
				}
				else {
					$xml = new SimpleXMLElement('<volumes><!-- empty --></volumes>');
					$xml->asXML(''. $this->volume .'');
					$xml = simplexml_load_file(''. $this->volume .'');
				}
				break;
			case "pool":
				if (file_exists(''. $this->pool .'') && file_get_contents(''. $this->pool .'') != '') {
					$xml = simplexml_load_file(''. $this->pool .'');
				}
				else {
					$xml = new SimpleXMLElement('<pools><!-- empty --></pools>');
					$xml->asXML(''. $this->pool .'');
					$xml = simplexml_load_file(''. $this->pool .'');
				}
				break;
		}
		return $xml;
	}
	
	function chgConfig($config, $path) {
		exec('cp -p '. $config .' '. $path .'');
		exec('sync');
	}
}
?>