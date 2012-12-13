<?php
namespace flickr\dka;
use SimpleXMLElement;
class DKA2MetadataGenerator extends DKAMetadataGenerator {
	const SCHEMA_NAME = 'DKA2';
	const SCHEMA_GUID = '5906a41b-feae-48db-bfb7-714b3e105396';
	const VALIDATE = true;
	
	/**
	 * Sets the schema source fetching it from a chaos system.
	 * @param CHAOS\Portal\Client\PortalClient $chaosClient
	 */
	public function fetchSchema($chaosClient) {
		return $this->fetchSchemaFromGUID($chaosClient, self::SCHEMA_GUID);
	}
	
	/**
	 * Generate XML from some import-specific object.
	 * @param unknown_type $externalObject
	 * @param boolean $validate Validate the generated XML agains a schema.
	 * @return DOMDocument Representing the imported item as XML in a specific schema.
	 */
	public function generateXML($photo, &$extras) {
		$result = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><dka:DKA xmlns:dka='http://www.danskkulturarv.dk/DKA2.xsd' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'></dka:DKA>");
		
		$result->addChild("Title", $photo['title']);
		
		// TODO: Consider if this is the correct mapping.
		$result->addChild("Abstract", '');
		
		$result->addChild("Description", $photo['description']);
		
		$result->addChild("Organization", $photo['owner']['username']);
		
		$url = $photo['urls']['url'];
		$url = $url[0]['_content'];
		$result->addChild("ExternalURL", $url);
		
		$result->addChild("ExternalIdentifier", $photo['id']);
		
		$result->addChild("Type", 'Picture');
		
		if(!empty($photo['dates']['taken'])) {
			$data = new \DateTime($photo['dates']['taken']);
			$result->addChild("CreatedDate", date_format($data, 'c'));
		}
		
		$contributors = $result->addChild("Contributors");
		/*
		foreach($movieItem->Credits->children() as $creditListItem) {
			if($this->isContributor($creditListItem->Type)) {
				$person = $contributors->addChild("Person");
				$person->addAttribute("Name", $creditListItem->Name);
				$person->addAttribute("Role", self::translateCreditTypeToRole(htmlspecialchars($creditListItem->Type)));
			}
		}
		*/
		
		$creators = $result->addChild("Creators");
		/*
		foreach($movieItem->Credits->children() as $creditListItem) {
			if($this->isCreator($creditListItem->Type)) {
				$person = $creators->addChild("Person");
				$person->addAttribute("Name", $creditListItem->Name);
				$person->addAttribute("Role", self::translateCreditTypeToRole(htmlspecialchars($creditListItem->Type)));
			}
		}
		*/
		
		if(array_key_exists('location', $photo)) {
			$location = self::extractLocation($photo['location']);
			if($location != null) {
				$result->addChild("Location", $location);
			}
		}
		
		$result->addChild("RightsDescription", 'Copyright © ' . $photo['owner']['username']);
		
		// Needs to be here if the validation should succeed.
		if(!empty($photo['location'])) {
			$GeoData = $result->addChild("GeoData");
			$GeoData->addChild("Latitude", $photo['location']['latitude']);
			$GeoData->addChild("Longitude", $photo['location']['longitude']);
		}
		
		$Categories = $result->addChild("Categories");
		
		$Tags = $result->addChild("Tags");
		foreach($photo['tags']['tag'] as $tag) {
			$Tags->addChild("Tag", $tag['_content']);
		}
		
		// Generate the DOMDocument.
		$dom = dom_import_simplexml($result)->ownerDocument;
		$dom->formatOutput = true;
		libxml_use_internal_errors(false);
		if(self::VALIDATE) {
			$this->validate($dom);
		}
		return $dom;
	}
	
	public function getSchemaGUID() {
		return self::SCHEMA_GUID;
	}
}