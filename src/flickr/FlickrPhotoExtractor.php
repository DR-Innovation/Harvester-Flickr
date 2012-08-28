<?php
namespace flickr;
use RuntimeException, Exception;
class FlickrPhotoExtractor extends \AChaosFileExtractor {
	
	// See: http://www.flickr.com/services/api/misc.urls.html
	
	public $_imageFormatID;
	public $_lowResImageFormatID;
	public $_thumbnailImageFormatID;
	public $_imageDestinationID;
	/**
	 * Process the DFI movieitem.
	 * @param \DFIIntoDKAHarvester $harvester The Chaos client to use for the importing.
	 * @param dfi\DFIClient $dfiClient The DFI client to use for importing.
	 * @param array $photo The Flickr photo item.
	 * @param stdClass $object Representing the DKA program in the Chaos service, of which the images should be added to.
	 * @return array An array of processed files.
	 */
	function process($harvester, $object, $photo, &$extras) {
		if($object == null) {
			throw new Exception("Cannot extract files from an empty object.");
		}
		
		$imagesProcessed = array();
		
		printf("\tUpdating the file for the thumbnail image: ");
		// Update the thumbnail.
		$folderPath = sprintf("%s", $photo['server']);
		$filename = sprintf("%s_%s_m.jpg", $photo['id'], $photo['secret']);
		
		var_dump($folderPath);
		var_dump($filename);
		exit;
		
		$response = $this->getOrCreateFile($harvester, $object, null, $this->_thumbnailImageFormatID, $this->_imageDestinationID, $filename, $filename, $folderPath);
		if($response == null) {
			throw new RuntimeException("Failed to create the main image file.");
		} else {
			$imagesProcessed[] = $response;
		}
		printf(" Done.\n");
		
		return $imagesProcessed;
	}
}