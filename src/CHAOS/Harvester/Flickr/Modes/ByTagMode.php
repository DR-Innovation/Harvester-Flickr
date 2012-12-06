<?php

namespace CHAOS\Harvester\Flickr\Modes;

class ByTagMode extends \CHAOS\Harvester\Modes\SetByReferenceMode {
	
	protected $flickrUserID; 
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		$flickrUserID = $parameters['flickrUserID'];
	}
	
	function execute($reference) {
		var_dump($reference);
		exit;
	}
}