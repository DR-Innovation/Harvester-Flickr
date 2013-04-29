<?php
namespace CHAOS\Harvester\Flickr\Processors;

class PhotoFileProcessor extends \CHAOS\Harvester\Processors\FileProcessor {
	
	public function process(&$externalObject, &$shadow = null) {
		assert(is_array($externalObject) && array_key_exists('url_l', $externalObject));
		return $this->processHelper($externalObject, $shadow, $externalObject['url_l']);
	}
	
	protected function processHelper(&$externalObject, &$shadow, $url) {
		assert($shadow instanceof \CHAOS\Harvester\Shadows\ObjectShadow);
		
		$fileShadow = $this->createFileShadowFromURL($url);
		
		// Adding the file to the list of file shadows.
		if($fileShadow instanceof \CHAOS\Harvester\Shadows\FileShadow) {
			$shadow->fileShadows[] = $fileShadow;
		} else {
			throw new \RuntimeException("Couldn't create a file for the flickr image at $url.");
		}
	}
}