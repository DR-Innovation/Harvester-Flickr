<?php
/**
 * This harvester connects to a OAI-PMH compliant webservice and
 * copies information on items into a Chaos service.
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU Lesser General Public License as published by  
 * the Free Software Foundation, either version 3 of the License, or  
 * (at your option) any later version.  
 *
 * @author     Kræn Hansen (Open Source Shift) for the danish broadcasting corporation, innovations.
 * @license    http://opensource.org/licenses/LGPL-3.0	GNU Lesser General Public License
 * @version    $Id:$
 * @link       https://github.com/CHAOS-Community/Harvester-OAI-PMH
 * @since      File available since Release 0.1
 */

use flickr\dka\DKAMetadataGenerator;

require "bootstrap.php";

class FlickrException extends RuntimeException {
	function __construct($flickrClient) {
		parent::__construct($flickrClient->getErrorCode(). ": " .$flickrClient->getErrorMsg());
	}
}

/**
 * Main class of the OAI-PMH Harvester.
 *
 * @author     Kræn Hansen (Open Source Shift) for the danish broadcasting corporation, innovations.
 * @license    http://opensource.org/licenses/LGPL-3.0	GNU Lesser General Public License
 * @version    Release: @package_version@
 * @link       https://github.com/CHAOS-Community/Harvester-OAI-PMH
 * @since      Class available since Release 0.1
 */
class FlickrIntoDKAHarvester extends AChaosImporter {
	
	/**
	 * The client to use when communicating with the Flickr service.
	 * @var phpFlickr
	 */
	protected $_flickr;
	
	/**
	 * The base url of the external Flickr webservice.
	 * @var string
	 */
	protected $_FlickrBaseUrl;
	
	/**
	 * The key to authenticate towards the external Flickr webservice.
	 * @var string
	 */
	protected $_FlickrKey;
	
	/**
	 * The secret to authenticate towards the external Flickr webservice.
	 * @var string
	 */
	protected $_FlickrSecret;
	
	/**
	 * The token to authenticate towards the external Flickr webservice.
	 * @var string
	 */
	/*
	protected $_FlickrToken;
	*/
	const PER_PAGE = 500;
	
	/**
	 * The object type of a chaos object, to be used later.
	 * Populated when AChaosImporter::loadConfiguration is called.
	 * @var string
	 */
	protected $_objectTypeID;
	
	/**
	 * The ID of the format to be used when linking images to a DKA Program.
	 * Populated when AChaosImporter::loadConfiguration is called.
	 * @var string
	 */
	protected $_imageFormatID;
	
	/**
	 * The ID of the format to be used when linking lowres-images to a DKA Program.
	 * Populated when AChaosImporter::loadConfiguration is called.
	 * @var string
	 */
	protected $_lowResImageFormatID;
	
	/**
	 * The ID of the format to be used when linking thumbnail.images to a DKA Program.
	 * Populated when AChaosImporter::loadConfiguration is called.
	 * @var string
	 */
	protected $_thumbnailImageFormatID;
	
	/**
	 * The ID of the format to be used when linking images to a DKA Program.
	 * Populated when AChaosImporter::loadConfiguration is called.
	 * @var string
	 */
	protected $_imageDestinationID;
	
	/**
	 * Constructor for the DFI Harvester
	 * @throws RuntimeException if the Chaos services are unreachable or
	 * if the Chaos credentials provided fails to authenticate the session.
	 */
	public function __construct($args) {
		// Adding configuration parameters
		$this->_CONFIGURATION_PARAMETERS["FLICKR_KEY"] = "_FlickrKey";
		$this->_CONFIGURATION_PARAMETERS["FLICKR_SECRET"] = "_FlickrSecret";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_DKA_OBJECT_TYPE_ID"] = "_objectTypeID";
		
		/*
		$this->_CONFIGURATION_PARAMETERS["CHAOS_TOEF_IMAGE_FORMAT_ID"] = "_imageFormatID";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_TOEF_LOWRES_IMAGE_FORMAT_ID"] = "_lowResImageFormatID";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_TOEF_THUMBNAIL_IMAGE_FORMAT_ID"] = "_thumbnailImageFormatID";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_TOEF_IMAGE_DESTINATION_ID"] = "_imageDestinationID";
		*/
		
		// Adding xml generators.
		$this->_metadataGenerators[] = new flickr\dka\DKAMetadataGenerator();
		$this->_metadataGenerators[] = new flickr\dka\DKA2MetadataGenerator();
		//$this->_metadataGenerators[] = new XSLTMetadataGenerator('../stylesheets/DKA.xsl', '00000000-0000-0000-0000-000063c30000');
		// Adding file extractors.
		$this->_fileExtractors['photo'] = new flickr\FlickrPhotoExtractor();
		
		parent::__construct($args);
		$this->Flickr_initialize();
		//$this->testXMLGenerator();
	}
	
	function Flickr_initialize() {
		$this->_flickr = new phpFlickr($this->_FlickrKey, $this->_FlickrSecret, true);
		/*$this->_flickr->setToken($this->_FlickrToken);
		
		$response = $this->_flickr->test_login();
		if($response === false) {
			throw new FlickrException($this->_flickr);
		} else {
			printf("Flickr client initialized, harvesting photos from '%s'.\n", $response['username']);
		}
		
		/*
		$this->_fileExtractors['image']->_imageFormatID = $this->_imageFormatID;
		$this->_fileExtractors['image']->_lowResImageFormatID = $this->_lowResImageFormatID;
		$this->_fileExtractors['image']->_thumbnailImageFormatID = $this->_thumbnailImageFormatID;
		$this->_fileExtractors['image']->_imageDestinationID = $this->_imageDestinationID;
		*/
	}
	
	protected function fetchRange($start, $count = null) {
		if(array_key_exists('flickr-set', $this->runtimeOptions)) {
			$flickrSet = $this->runtimeOptions['flickr-set'];
			$response = $this->_flickr->photosets_getInfo($flickrSet);
			printf("Harvesting images from the '%s' photoset.\n", $response['title']);
			
			$result = array();
			$page = floor($start / self::PER_PAGE);
			$offset = $page * self::PER_PAGE;
			// The webservice is one-indexed.
			$page += 1;
			
			do {
				$response = $this->_flickr->photosets_getPhotos($flickrSet, null, null, self::PER_PAGE, $page);
				foreach($response['photoset']['photo'] as $photo) {
					if($offset < $start) {
						$offset++;
						continue;
					} elseif ($count == null || $offset < $count) {
						$result[] = strval($photo['id']);
						$offset++;
					} else {
						// All done
						break 2;
					}
				}
				$page++;
			} while(count($response['photoset']['photo']) > 0);
			
			return $result;
		} else {
			throw new RuntimeException("The runtime parameter --flickr-set={...} must be sat.");
		}
	}
	
	protected function fetchSingle($reference) {
		$response = $this->_flickr->photos_getInfo($reference);
		if($response === false || !is_array($response['photo'])) {
			throw new RuntimeException("Errors occurred when harvesting.");
		} else {
			return $response['photo'];
		}
	}
	
	protected function externalObjectToString($externalObject) {
		return sprintf("%s [%u]", $externalObject['title'], $externalObject['id']);
	}
	
	protected function initializeExtras($sight, &$extras) {
		$extras = array();
	}
	
	protected function shouldBeSkipped($externalObject) {
		return false;
	}
	
	protected function generateChaosQuery($externalObject) {
		if($externalObject == null) {
			throw new RuntimeException("Cannot get or create a Chaos object from a null external object.");
		}
		$id = $externalObject["id"]; 
		$folderId = $this->_ChaosFolderID;
		$objectTypeId = $this->_objectTypeID;
		// Extract the nummeric ID.
		return sprintf('(FolderTree:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $folderId, $objectTypeId, $id);
	}
	
	protected function getChaosObjectTypeID() {
		return $this->_objectTypeID;
	}
	
	public function getExternalClient() {
		return $this->_flickr;
	}
}

// Call the main method of the class.
FlickrIntoDKAHarvester::main($_SERVER['argv']);
