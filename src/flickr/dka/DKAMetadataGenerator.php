<?php
namespace flickr\dka;
use SimpleXMLElement;
use InvalidArgumentException;
class DKAMetadataGenerator extends \AChaosMetadataGenerator {
	const SCHEMA_NAME = 'DKA';
	const SCHEMA_GUID = '00000000-0000-0000-0000-000063c30000';
	const VALIDATE = false;
	
	/**
	 * Sets the schema source fetching it from a chaos system.
	 * @param CHAOS\Portal\Client\PortalClient $chaosClient
	 */
	public function fetchSchema($chaosClient) {
		return $this->fetchSchemaFromGUID($chaosClient, self::SCHEMA_GUID);
	}
	
	/**
	 * Generate XML from some import-specific object.
	 * @param unknown_type $photo
	 * @param string[string] $extras
	 * @return DOMDocument Representing the imported item as XML in a specific schema.
	 */
	public function generateXML($photo, &$extras) {
		$result = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><DKA></DKA>");
		
		$result->addChild("Title", $photo['title']);
		
		// TODO: Consider if this is the correct mapping.
		$result->addChild("Abstract", '');
		
		$result->addChild("Description", $photo['description']);
		
		$result->addChild("Organization", $photo['owner']['username']);
		
		$result->addChild("Type", 'Picture');
		
		if(!empty($photo['dates']['taken'])) {
			$result->addChild("CreatedDate", $photo['dates']['taken']);
		}
		
		// This can infact be the DFI ID, but it is not used on the DKA frontend.
		$result->addChild("Identifier", $photo["id"]);
		
		/*
		$contributors = $result->addChild("Contributor");
		foreach($movieItem->Credits->children() as $creditListItem) {
			if($this->isContributor($creditListItem->Type)) {
				$person = $contributors->addChild("Person");
				$person->addAttribute("Name", $creditListItem->Name);
				$person->addAttribute("Role", self::translateCreditTypeToRole(htmlspecialchars($creditListItem->Type)));
			}
		}
		
		$creators = $result->addChild("Creator");
		foreach($movieItem->Credits->children() as $creditListItem) {
			if($this->isCreator($creditListItem->Type)) {
				$person = $creators->addChild("Person");
				$person->addAttribute("Name", $creditListItem->Name);
				$person->addAttribute("Role", self::translateCreditTypeToRole(htmlspecialchars($creditListItem->Type)));
			}
		}
		*/
		$location = self::extractLocation($photo['location']);
		if($location != null) {
			$result->addChild("Location", $location);
		}
		
		$result->addChild("RightsDescription", 'Copyright © ' . $photo['owner']['username']);
		
		// Needs to be here if the validation should succeed.
		if(!empty($photo['location'])) {
			$GeoData = $result->addChild("GeoData");
			$GeoData->addChild("Latitude", $photo['location']['latitude']);
			$GeoData->addChild("Longitude", $photo['location']['longitude']);
		}
		
		//$Categories = $result->addChild("Categories");
		
		$Tags = $result->addChild("Tags");
		foreach($photo['tags']['tag'] as $tag) {
			$Tags->addChild("Tag", $tag['_content']);
		}
		
		// Generate the DOMDocument.
		$dom = dom_import_simplexml($result)->ownerDocument;
		$dom->formatOutput = true;
		if(self::VALIDATE) {
			$this->validate($dom);
		}
		return $dom;
	}
	
	public function getSchemaGUID() {
		return self::SCHEMA_GUID;
	}
	
	/**
	 * Applies translation of different types of persons.
	 * @param string $type The type to be translated.
	 */
	public static function translateCreditTypeToRole($type) {
		$ROLE_TRANSLATIONS = array(); // Right now no translation is provided.
		if(key_exists($type, $ROLE_TRANSLATIONS)) {
			return $this->_roleTranslations[$type];
		} else {
			return $type;
		}
	}
	
	const CONTRIBUTOR = 0x01;
	const CREATOR = 0x02;
	/**
	 * Devides the types known by DFI into Creator or Contributor known by a DKA Program.
	 * @param string $type The type to be translated.
	 * @return int Either the value of the CONTRIBUTOR or the CREATOR class constants.
	 */
	public static function translateCreditTypeToContributorOrCreator($type) {
		switch ($type) {
			case 'Actor':
				return self::CONTRIBUTOR;
			default:
				return self::CREATOR;
		}
	}
	
	/**
	 * Checks if a type known by DFI is a Creator in the DKA Program notion.
	 * @param string $type
	 * @return boolean True if it should be treated as a creator, false otherwise.
	 */
	public static function isCreator($type) {
		return self::translateCreditTypeToContributorOrCreator($type) == self::CREATOR;
	}
	
	/**
	 * Checks if a type known by DFI is a Contributor in the DKA Program notion.
	 * @param string $type
	 * @return boolean True if it should be treated as a contributor, false otherwise.
	 */
	public static function isContributor($type) {
		return self::translateCreditTypeToContributorOrCreator($type) == self::CONTRIBUTOR;
	}
	
	/**
	 * Transforms a 4-digit year into an XML data YYYY-01-01 format.
	 * @param string $year The 4-digit representation of a year.
	 * @throws InvalidArgumentException If this is not null, an empty string or a 4-digit string.
	 * @return NULL|unknown|string The expected result, null or the empty string if this was the input argument.
	 */
	public static function yearToXMLDate($year) {
		if($year === null) {
			return null;
		} elseif($year === '') {
			return $year;
		} elseif(strlen($year) === 4) {
			return $year . '-01-01';
		} else {
			throw new InvalidArgumentException('The \$year argument must be null, empty or of length 4, got "'.strval($year).'"');
		}
	}
	
	/**
	 * Transforms a 4-digit year into an XML data YYYY-01-01T00:00:00 format.
	 * @param string $year The 4-digit representation of a year.
	 * @throws InvalidArgumentException If this is not null, an empty string or a 4-digit string.
	 * @return NULL|unknown|string The expected result, null or the empty string if this was the input argument.
	 */
	public static function yearToXMLDateTime($year) {
		if($year === null) {
			return null;
		} elseif($year === '') {
			return $year;
		} elseif(strlen($year) === 4) {
			return $year . '-01-01T00:00:00';
		} else {
			throw new InvalidArgumentException('The \$year argument must be null, empty or of length 4, got "'.strval($year).'"');
		}
	}
	
	public static function extractFileTypes($extractedFiles) {
		$result = array();
		if(!empty($extractedFiles['dfi\DFIImageExtractor'])) {
			$result[] = 'Picture';
		}
		if(!empty($extractedFiles['dfi\DFIVideoExtractor'])) {
			$result[] = 'Video';
		}
		return $result;
	}
	
	public static function extractLocation($location) {
		$prioritizedFields = array("neighbourhood", "locality", "county", "region", "country");
		if(!empty($location)) {
			foreach($prioritizedFields as $field) {
				if(!empty($location[$field]) && !empty($location[$field]['_content'])) {
					return $location[$field]['_content'];
				}
			}
		}
		return null;
	}
}