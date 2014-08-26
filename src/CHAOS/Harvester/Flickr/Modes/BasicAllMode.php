<?php

namespace CHAOS\Harvester\Flickr\Modes;

class BasicAllMode extends \CHAOS\Harvester\Modes\AllMode {
	
	protected $_flickrUserID; 
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		if(key_exists('flickrUsername', $parameters)) {
			$flickr = $this->_harvester->getExternalClient('flickr');
			$response = $flickr->people_findByUsername($parameters['flickrUsername']);
			$this->_flickrUserID = $response['nsid'];
		}
	}
	
	function execute() {
		/* @var $flickr CHAOS\Harvester\Flickr\LoadableFlickrClient */
		$flickr = $this->_harvester->getExternalClient('flickr');
		
		$page = 1;
		$photoIndex = 1;
		
		do {
			// TODO: Consider adding machine_tags in the extras field to see what can come out of this.
			$result = $flickr->photos_search(array(
				'user_id' => $this->_flickrUserID,
				'per_page' => 50,
				'page' => $page++,
				'extras' => 'description,license,owner_name,date_taken,date_upload,tags,url_s,url_l,geo',
				'content_type' => 7, // 7 for photos, screenshots, and 'other' (all).
				'media' => 'photos'
			));
			if ($result['photo']) {
				foreach($result['photo'] as $photo) {
					$this->_harvester->info("[%u/%u] Processing '%s' #%u", $photoIndex++, $result['total'], $photo['title'], $photo['id']);
					$photo['ownerid'] = $this->_flickrUserID;
					$photo['url'] = sprintf("http://www.flickr.com/photos/%s/%u/", $photo['ownerid'], $photo['id']);
					$photoShadow = $this->_harvester->process('photo', $photo);
				}
			}
		} while($result['page'] < $result['pages']);
		
	}
}