<?php

namespace CHAOS\Harvester\Flickr\Modes;

class ByTagMode extends \CHAOS\Harvester\Modes\SetByReferenceMode {
	
	protected $_flickrUserID; 
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		/*
		if(key_exists('flickrUsername', $parameters)) {
			$flickr = $this->_harvester->getExternalClient('flickr');
			$result = $flickr->people_findByUsername($parameters['flickrUsername']);
			if($result !== false) {
				$this->_flickrUserID = $result['nsid'];
			}
		}
		*/
		// TODO: Implement this ...
	}
	
	function execute($reference) {
		var_dump($this->_flickrUserID);
		$flickr = $this->_harvester->getExternalClient('flickr');
		$result = $flickr->photos_search(array(
			'user_id' => $this->_flickrUserID,
			'tags' => $reference
		));
		var_dump($result);
	}
}