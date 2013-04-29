<?php
namespace CHAOS\Harvester\Flickr\Processors;

class PhotoThumbFileProcessor extends \CHAOS\Harvester\Flickr\Processors\PhotoFileProcessor {

	public function process(&$externalObject, &$shadow = null) {
		assert(is_array($externalObject) && array_key_exists('url_s', $externalObject));
		return $this->processHelper($externalObject, $shadow, $externalObject['url_s']);
	}
}