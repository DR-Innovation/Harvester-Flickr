<?php

namespace CHAOS\Harvester\Flickr\Modes;

class SetByPhotosetIDMode extends \CHAOS\Harvester\Modes\SetByReferenceMode {
	
	protected $_flickrUserID; 
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		if(key_exists('flickrUsername', $parameters)) {
			$flickr = $this->_harvester->getExternalClient('flickr');
			$response = $flickr->people_findByUsername($parameters['flickrUsername']);
			$this->_flickrUserID = $response['nsid'];
		}
	}
	
	function execute($reference) {
		$flickr = $this->_harvester->getExternalClient('flickr');
		assert($flickr instanceof \CHAOS\Harvester\Flickr\LoadableFlickrClient);
		
		$photoset_ids = explode(',', $reference);
		foreach($photoset_ids as $photoset_id) {
			$this->_harvester->info("Harvesting from photoset #%u", $photoset_id);
			
			$page = 1;
			$photoIndex = 1;
			
			do {
				$result = $flickr->photosets_getPhotos(
					$photoset_id, /* photoset_id */
					'description,license,owner_name,date_taken,date_upload,tags,url_s,url_l,geo', /* extras */
					null, /* privacy_filter */
					50, /* per_page */
					$page++, /* page */
					'photos' /* media */
				);
				
				assert(array_key_exists('photoset', $result));
				$photoset = $result['photoset'];
				
				assert(array_key_exists('photo', $photoset));
				assert(is_array($photoset['photo']));
				
				foreach($photoset['photo'] as $photo) {
					$this->_harvester->info("[%u/%u] Processing '%s' #%u", $photoIndex++, $photoset['total'], $photo['title'], $photo['id']);
					$photo['ownerid'] = $this->_flickrUserID;
					$photo['url'] = sprintf("http://www.flickr.com/photos/%s/%u/", $photo['ownerid'], $photo['id']);
					$photoShadow = $this->_harvester->process('photo', $photo);
					echo "\n";
				}
			} while($photoset['page'] < $photoset['pages']);
			
		}
	}
}