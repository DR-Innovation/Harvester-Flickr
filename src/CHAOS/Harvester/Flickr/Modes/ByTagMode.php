<?php

namespace CHAOS\Harvester\Flickr\Modes;

class ByTagMode extends \CHAOS\Harvester\Modes\SetByReferenceMode {
	
	protected $_flickrUserID; 
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		if(key_exists('flickrUsername', $parameters)) {
			$flickr = $this->_harvester->getExternalClient('flickr');
			$response = $flickr->people_findByUsername($parameters['flickrUsername']);
			var_dump($response);
			$this->_flickrUserID = $response['nsid'];
		}
	}
	
	function execute($reference) {
		/* @var $flickr CHAOS\Harvester\Flickr\LoadableFlickrClient */
		$flickr = $this->_harvester->getExternalClient('flickr');
		
		$page = 1;
		$photoIndex = 1;
		
		$this->_harvester->debug("Executing %s on tag = '%s'.", $this->_name, $reference);
		
		do {
			// TODO: Consider searching for any picture on the user and do a client-side filtering on tags.
			$result = $flickr->photos_search(array(
				'user_id' => $this->_flickrUserID,
				'per_page' => 50,
				'page' => $page++,
				'tags' => $reference,
				'extras' => 'description,license,owner_name,date_taken,tags,url_s,url_l',
				'content_type' => 7, // 7 for photos, screenshots, and 'other' (all).
				'media' => 'photos'
			));
			
			foreach($result['photo'] as $photo) {
				$this->_harvester->info("[%u/%u] Processing '%s' #%u", $photoIndex++, $result['total'], $photo['title'], $photo['id']);
				$photo['ownerid'] = $this->_flickrUserID;
				$photoShadow = $this->_harvester->process('photo', $photo);
			}
		} while($result['page'] < $result['pages']);
		
	}
}