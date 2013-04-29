<?php

namespace CHAOS\Harvester\Flickr\Processors;

class PhotoObjectProcessor extends \CHAOS\Harvester\Processors\ObjectProcessor {
	
	protected function generateQuery($externalObject) {
		$old = sprintf('(FolderID:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, $externalObject['id']);
		$new = sprintf('(FolderID:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, $externalObject['url']);
		return sprintf('(%s OR %s)', $new, $old);
	}
	
	public function process(&$externalObject, &$shadow = null) {
		$shadow = new \CHAOS\Harvester\Shadows\ObjectShadow();
		$this->initializeShadow($externalObject, $shadow);
		$this->_harvester->process('photo_metadata', $externalObject, $shadow);
		$this->_harvester->process('photo_file_large', $externalObject, $shadow);
		$this->_harvester->process('photo_file_thumb', $externalObject, $shadow);
		$shadow->commit($this->_harvester);
	}
}