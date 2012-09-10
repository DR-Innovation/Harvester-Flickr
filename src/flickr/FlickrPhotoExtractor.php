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
		
		// Update the thumbnail.
		$folderPath = sprintf("%s.staticflickr.com/%s", $photo['farm'], $photo['server']);
		$filenames = array();
		// b = large, 1024 on longest side
		$filenames['normal'] = self::generateFilename($photo['id'], $photo['secret'], 'b');
		// m = small, 240 on longest side
		$filenames['lowres'] = self::generateFilename($photo['id'], $photo['secret'], 'm');
		// m = small, 240 on longest side
		$filenames['thumbnail'] = self::generateFilename($photo['id'], $photo['secret'], 'm');
		
		printf("\tUpdating the file for the thumbnail image: ");
		$response = $this->getOrCreateFile($harvester, $object, null, $this->_thumbnailImageFormatID, $this->_imageDestinationID, $filenames['thumbnail'], $filenames['thumbnail'], $folderPath);
		if($response == null) {
			throw new RuntimeException("Failed to create the main image file.");
		} else {
			$imagesProcessed[] = $response;
		}
		printf(" Done.\n");
		
		printf("\tUpdating the file for the main image: ");
		$response = $this->getOrCreateFile($harvester, $object, null, $this->_imageFormatID, $this->_imageDestinationID, $filenames['normal'], $filenames['normal'], $folderPath);
		if($response == null) {
			throw new RuntimeException("Failed to create the main image file.");
		} else {
			$imagesProcessed[] = $response;
		}
		printf(" Done.\n");
		
		printf("\tUpdating the file for the main (lowres) image: ");
		$response = $this->getOrCreateFile($harvester, $object, null, $this->_lowResImageFormatID, $this->_imageDestinationID, $filenames['lowres'], $filenames['lowres'], $folderPath);
		if($response == null) {
			throw new RuntimeException("Failed to create the main image file.");
		} else {
			$imagesProcessed[] = $response;
		}
		printf(" Done.\n");
		
		return $imagesProcessed;
	}
	
	static function generateFilename($id, $secret, $size) {
		return sprintf("%s_%s_%s.jpg", $id, $secret, $size);
	}
}