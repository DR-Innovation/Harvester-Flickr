<?php
/**
 * This is a very minimalistic client for the open Flickr API.
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author     Kræn Hansen (Open Source Shift) for the danish broadcasting corporation, innovations.
 * @license    http://opensource.org/licenses/LGPL-3.0	GNU Lesser General Public License
 * @version    $Id:$
 * @link       https://github.com/CHAOS-Community/Harvester-DFI
 * @since      File available since Release 0.1
 */

namespace CHAOS\Harvester\Flickr;

require("flickr/phpFlickr.php");
class LoadableFlickrClient extends \phpFlickr implements \CHAOS\Harvester\IExternalClient {
	
	/**
	 * A reference to the harvester.
	 * @var \CHAOS\Harvester\ChaosHarvester
	 */
	protected $harvester;
	
	/**
	 * Constructs a new DFIClient for communication with the Danish Film Institute open API.
	 * @param string $baseURL 
	 */
	public function __construct($harvester, $name, $parameters = array()) {
		parent::__construct($parameters['key'], $parameters['secret'], $harvester->hasOption('debug'));
		$this->harvester = $harvester;
	}
	
	/**
	 * Checks if the DFI service is advailable, by sending a single row request for the movie.service.
	 * @return boolean True if the service call goes through, false if not.
	 */
	public function sanityCheck() {
		$echo = $this->test_echo();
		return $echo['stat'] == 'ok';
	}
	/*
	public function __doRequest ($request, $location, $action, $version, $one_way = null) {
		//timed();
		//$result = parent::__doRequest($request, $location, $action, $version, $one_way);
		//timed('bonanza');
		//return $result;
	}
	*/
}
