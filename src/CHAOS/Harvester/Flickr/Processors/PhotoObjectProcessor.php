<?php

namespace CHAOS\Harvester\Flickr\Processors;

class PhotoObjectProcessor extends \CHAOS\Harvester\Processors\ObjectProcessor {
	
	public function process(&$externalObject, &$shadow = null) {
		$shadow = new \CHAOS\Harvester\Shadows\ObjectShadow();
		$this->initializeShadow($shadow);
		$this->_harvester->process('photo_metadata', $externalObject, $shadow);
	}
}