<?php

namespace CHAOS\Harvester\Flickr\Filters;

class FlickrTagFilter extends \CHAOS\Harvester\Filters\Filter {
	
	const TAG_SEPERATOR = ' ';
	protected $_requiredTags;
	protected $_bannedTags;
	
	public function __construct($harvester, $name, $parameters = array()) {
		parent::__construct($harvester, $name, $parameters);
		
		if(array_key_exists('requiredTags', $parameters)) {
			$this->_requiredTags = explode(self::TAG_SEPERATOR, $parameters['requiredTags']);
		} else {
			$this->_requiredTags = array();
		}
		
		if(array_key_exists('bannedTags', $parameters)) {
			$this->_bannedTags = explode(self::TAG_SEPERATOR, $parameters['bannedTags']);
		} else {
			$this->_bannedTags = array();
		}
	}
		
	public function passes($externalObject, $objectShadow) {
		if(!array_key_exists('tags', $externalObject)) {
			throw new \RuntimeException("Expected a tags parameter on the photo returned from the Flickr service."); 
		}
		$tags = explode(self::TAG_SEPERATOR, $externalObject['tags']);
		foreach($this->_requiredTags as $tag) {
			if(!in_array($tag, $tags)) {
				return "Required tag '$tag' was not found in the photo's tags.";
			}
		}
		foreach($this->_bannedTags as $tag) {
			if(in_array($tag, $tags)) {
				return "Banned tag '$tag' was found in the photo's tags.";
			}
		}
		return true;
	}
}