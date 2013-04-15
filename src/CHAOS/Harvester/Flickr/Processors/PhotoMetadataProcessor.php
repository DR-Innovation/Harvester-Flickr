<?php
namespace CHAOS\Harvester\Flickr\Processors;
class PhotoMetadataProcessor extends \CHAOS\Harvester\Processors\MetadataProcessor {
	
	public function generateMetadata($externalObject, &$shadow = null) {
		$photo = $externalObject;
		$result = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><DKA xmlns="http://www.danskkulturarv.dk/DKA2.xsd"></DKA>');
		
		$photo['url'] = sprintf("http://www.flickr.com/photos/%s/%u/", $photo['ownerid'], $photo['id']);
		
		var_dump($photo);
		exit;
		$result->addChild("Title", trim(htmlspecialchars($photo->title)));
		
		$result->addChild("Abstract", '');
		
		$decription = htmlspecialchars($photo->description);
		$result->addChild("Description", $decription);
		
		$result->addChild("Organization", self::DFI_ORGANIZATION_NAME);

		$result->addChild("ExternalURL", $photo['url']);
		
		$result->addChild("Type", "Photo");
		
		// TODO Make the conversion work.
		/*
		$result->addChild("CreatedDate", self::yearToXMLDateTime((string)$externalObject->ProductionYear));
		
		$result->addChild("FirstPublishedDate", self::yearToXMLDateTime((string)$externalObject->ReleaseYear));
		
		$contributors = $result->addChild("Contributors");
		foreach($externalObject->Credits->children() as $creditListItem) {
			if($this->isContributor($creditListItem->Type)) {
				$contributor = $contributors->addChild("Contributor");
				$contributor->addAttribute("Name", trim(htmlspecialchars($creditListItem->Name)));
				$contributor->addAttribute("Role", self::translateCreditTypeToRole(htmlspecialchars($creditListItem->Type)));
			}
		}
		
		$creators = $result->addChild("Creators");
		foreach($externalObject->Credits->children() as $creditListItem) {
			if($this->isCreator($creditListItem->Type)) {
				$creator = $creators->addChild("Creator");
				$creator->addAttribute("Name", trim(htmlspecialchars($creditListItem->Name)));
				$creator->addAttribute("Role", self::translateCreditTypeToRole(htmlspecialchars($creditListItem->Type)));
			}
		}
		// This goes for the new DKA Metadata.
		foreach($externalObject->xpath('/dfi:MovieItem/dfi:ProductionCompanies/dfi:CompanyListItem') as $company) {
			$creator = $creators->addChild("Creator");
			$creator->addAttribute("Name", trim(htmlspecialchars($company->Name)));
			$creator->addAttribute("Role", 'Production');
		}
		foreach($externalObject->xpath('/dfi:MovieItem/dfi:DistributionCompanies/dfi:CompanyListItem') as $company) {
			$creator = $creators->addChild("Creator");
			$creator->addAttribute("Name", trim(htmlspecialchars($company->Name)));
			$creator->addAttribute("Role", 'Distribution');
		}
		
		$format = trim(htmlspecialchars($externalObject->Format));
		if($format !== '') {
			$result->addChild("TechnicalComment", "Format: ". $format);
		}
		
		// TODO: Consider if the location is the shooting location or the production location.
		$result->addChild("Location", htmlspecialchars($externalObject->CountryOfOrigin));
		
		$result->addChild("RightsDescription", self::RIGHTS_DESCIPTION);
		
		$Categories = $result->addChild("Categories");
		$Categories->addChild("Category", htmlspecialchars($externalObject->Category));
		
		foreach($externalObject->xpath('/dfi:MovieItem/dfi:SubCategories/a:string') as $subCategory) {
			$Categories->addChild("Category", $subCategory);
		}
		
		$Tags = $result->addChild("Tags");
		$Tags->addChild("Tag", "DFI");
		*/
		return $result;
	}
}