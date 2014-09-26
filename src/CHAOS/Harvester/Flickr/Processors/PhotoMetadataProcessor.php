<?php
namespace CHAOS\Harvester\Flickr\Processors;
class PhotoMetadataProcessor extends \CHAOS\Harvester\Processors\MetadataProcessor {
	
	protected function resolveLocationName($place_id) {
		$flickr = $this->_harvester->getExternalClient('flickr');
		/* @var $flickr \CHAOS\Harvester\Flickr\LoadableFlickrClient */
		
		$place = $flickr->places_getInfo($place_id);
		
		if($place && array_key_exists('name', $place)) {
			return $place['name'];
		} else {
			return null;
		}
	}
	
	protected function resolveLicenseName($license) {
		assert($license != null);
		
		$flickr = $this->_harvester->getExternalClient('flickr');
		$licenseInfo = $flickr->photos_licenses_getInfo();
		if(array_key_exists($license, $licenseInfo)) {
			$licenseInfo = $licenseInfo[$license];
			return sprintf("%s (%s)", $licenseInfo['name'], $licenseInfo['url']);
		} else {
			return "Unknown";
		}
	}
	
	public function generateMetadata($externalObject, &$shadow = null) {
		$photo = $externalObject;
		
		$result = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><DKA xmlns="http://www.danskkulturarv.dk/DKA2.xsd"></DKA>');
		
		$result->addChild("Title", trim(htmlspecialchars($photo['title'])));
		
		$result->addChild("Abstract", '');
		
		$decription = htmlspecialchars($photo['description']);
		$result->addChild("Description", $decription);
		
		$result->addChild("Organization", $photo['ownername']);

		$result->addChild("ExternalURL", $photo['url']);

		$result->addChild("ExternalIdentifier", $photo['url']);
		
		$result->addChild("Type", "Photo");
		
		$result->addChild("CreatedDate", date('c', strtotime($photo['dateupload'])));

		$result->addChild("FirstPublishedDate", date('c', intval($photo['datetaken'])));
		
		// Contributors and creators is left out.
		$result->addChild('Contributors');
		$result->addChild('Creators');

		// $result->addChild("TechnicalComment", "Format: ". $format);
		
		if(array_key_exists('place_id', $photo)) {
			$location = $this->resolveLocationName($photo['place_id']);
			if($location !== null) {
				$result->addChild("Location", htmlspecialchars($location));
			}
		}
		
		$result->addChild("RightsDescription", $this->resolveLicenseName($photo['license']));
		
		if(array_key_exists('latitude', $photo) && array_key_exists('longitude', $photo)) {
			if($photo['latitude'] != 0 && $photo['longitude'] != 0) { // Very unlikely.
				$geoData = $result->addChild("GeoData");
				$geoData->addChild("Latitude", $photo['latitude']);
				$geoData->addChild("Longitude", $photo['longitude']);
			}
		}

		$result->addChild("Categories");
		
		$Tags = $result->addChild("Tags");
		$Tags->addChild("Tag", "Flickr");
		foreach(explode(\CHAOS\Harvester\Flickr\Filters\FlickrTagFilter::TAG_SEPERATOR, $photo['tags']) as $tag) {
			$Tags->addChild("Tag", $tag);
		}
		
		return $result;
	}
}