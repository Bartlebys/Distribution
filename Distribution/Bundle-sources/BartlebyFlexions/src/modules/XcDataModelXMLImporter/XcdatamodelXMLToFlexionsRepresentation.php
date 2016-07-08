<?php

/*
Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.chaosmos.fr

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/Licenses/>
*/


require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';

require_once 'XcdataModelDelegate.Interface.php';


/**
 * Class XCDDataXMLToFlexionsRepresentation
 */
class XCDDataXMLToFlexionsRepresentation {

	function projectRepresentationFromXcodeModel($descriptorFilePath, $nativePrefix = "", XcdataModelDelegateInterface $delegate) {

		if(!isset($delegate)){
			fLog ( "XCDDataXMLToFlexionsRepresentation.projectRepresentationFromXcodeModel() module requires a XcdataModelDelegate" , true );
			return;
		}

		fLog ( "Invoking XCDDataXMLToFlexionsRepresentation.projectRepresentationFromXcodeModel()" . cr () . cr (), true );
		
		$r = new ProjectRepresentation ();
		$r->classPrefix = $nativePrefix;
		$r->entities = array ();
		
		$dom = new DomDocument ();
		$pth = realpath ( $descriptorFilePath );
		$dom->load ( $pth );
		$entities = $dom->getElementsByTagName ( 'entity' );
		
		// /////////////////////////
		// ENTITIES
		// /////////////////////////
		
		fLog ( 	 "********************".cr(),true );
		fLog ( 	 "Parsing Entities".cr(),true );
		fLog ( 	 "********************".cr().cr(),true );
		
		foreach ( $entities as $entity ) {
			
			/* @var DOMNode $entity */
			$entityR = new EntityRepresentation ();
			$entityR->metadata=array();
			
			// ////////////////////////////////////////////////////////////////
			//
			// -> ENTITY
			// Stored in <model ><entity>...
			//
			// sample :
			// <entity name="Activity" representedClassName="Activity" syncable="YES">
			// ...
			//
			// ////////////////////////////////////////////////////////////////
			
			// We parse the attribute of the <entity> element
			
			if ($entity->hasAttribute ( "representedClassName" )) {
				$entityR->name = $entity->getAttribute ( "representedClassName" );
				fLog ( cr ().'Parsing : ' . $entityR->name . cr (), true );
				fLog ( '------------------------'. cr (), true );
			}
			if ($entity->hasAttribute ( "name" ) && strlen ( $entityR->name ) <= 1) {
				$entityR->name = $entity->getAttribute ( "name" );
			}
			if (strlen ( $entityR->name ) <= 1) {
				throw new Exception ( 'entity with no representedClassName and no name' );
			}
			
			$entityR->type = "object"; // Entities are objects
			if ($entity->hasAttribute ( "parentEntity" )) {
				$entityR->instanceOf = $nativePrefix . $entity->getAttribute ( "parentEntity" );
			} else {
				// We donnot qualifiy the instance
				// a requalification can be done according to the situation in
				// the template
			}
			
			// ////////////////////////////////////////////////////////////////
			//
			// -> ENTITY->metadata
			// Stored in <userInfo><entry> elements
			//
			// Sample :
			// <entity name="Activity" representedClassName="Activity" syncable="YES">
			// ...
			// <userInfo>
			// <entry key="parent" value="WattModel"/>
			// </userInfo>
			// </entity>
			//
			// ////////////////////////////////////////////////////////////////
			
			$entityUserInfos = $entity->getElementsByTagName ( "userInfo" );
			foreach ( $entityUserInfos as $entityUserInfo ) {
				$userInfoEntries = $entityUserInfo->getElementsByTagName ( "entry" );
				foreach ( $userInfoEntries as $userInfoEntry ) {
					fLog ( 	 $entityR->name .'.metadata : '.$this->elementToString($userInfoEntry).cr(),true );
					
					if ($userInfoEntry->hasAttribute ( "key" ) && $userInfoEntry->hasAttribute ( "value" )){
						$entityR->metadata[ rtrim ( $userInfoEntry->getAttribute ( "key" ))]=rtrim( $userInfoEntry->getAttribute ( "value" ));
					}
					
					if ($userInfoEntry->hasAttribute ( "key" ) && rtrim ( $userInfoEntry->getAttribute ( "key" ) ) == "generate" && ($userInfoEntry->hasAttribute ( "value" ) && strtolower ( rtrim ( $userInfoEntry->getAttribute ( "value" ) ) ) == "collection")) {
						$entityR->generateCollectionClass = true;
					}
					if ($userInfoEntry->hasAttribute ( "key" ) && rtrim ( $userInfoEntry->getAttribute ( "key" ) ) == "parent" && $userInfoEntry->hasAttribute ( "value" )) {
						$entityR->instanceOf = $userInfoEntry->getAttribute ( "value" );
					}
				}
			}
			
			// ////////////////////////////////////////////////////////////////
			//
			// -> ENTITY->properties
			// Stored in <attribute> elements
			//
			// Sample :
			//
			// <entity name="Activity" representedClassName="Activity" syncable="YES">
			// <attribute name="level" optional="YES" attributeType="Integer 16"
			// defaultValueString="0" syncable="YES"/>
			// <attribute name="rating" optional="YES" attributeType="Integer 16"
			// defaultValueString="0" syncable="YES"/>
			// ...
			//
			// ////////////////////////////////////////////////////////////////
			
			$attributes = $entity->getElementsByTagName ( 'attribute' );
			foreach ( $attributes as $attribute ) {
				
				// For each attribute : here attribute == property
				$property = new PropertyRepresentation ();
				
				// We parse the attribute of the <attribute> element
				if ($attribute->hasAttribute ( "name" )) {
					$property->name = $attribute->getAttribute ( "name" );
				} else {
					throw new Exception ( 'property with no name' );
				}
				
				fLog ( $entityR->name.'.'.$property->name.' '.cr(),true );
				
				if ($attribute->hasAttribute ( "attributeType" )) {
					$property->type = $attribute->getAttribute ( "attributeType" );
				} else {
					$property->type = ObjectiveCHelper::UNDEFINED_TYPE;
				}
				
				if ($attribute->hasAttribute ( "defaultValueString" )) {
					$property->default = $attribute->getAttribute ( "defaultValueString" );
				}
				
				// We parse the property metadata

				$propertyUserInfos = $attribute->getElementsByTagName ( "userInfo" );
				foreach ( $propertyUserInfos as $propertyUserInfo ) {
					$userInfos = $propertyUserInfo->getElementsByTagName ( "entry" );
					// We parse the entries
					
					$property->metadata=array();
					
					foreach ( $userInfos as $propertyInfoEntry ) {
			
						fLog ( 	 $entityR->name.'.'.$property->name .'.metadata : '.$this->elementToString($propertyInfoEntry).cr(),true );
						
						if ($propertyInfoEntry->hasAttribute ( "key" ) && $propertyInfoEntry->hasAttribute ( "value" )){
							$property->metadata[ rtrim ( $propertyInfoEntry->getAttribute ( "key" ))]=rtrim( $propertyInfoEntry->getAttribute ( "value" ));
						}
						
						if ($propertyInfoEntry->hasAttribute ( "key" ) && rtrim ( $propertyInfoEntry->getAttribute ( "key" ) ) == "type" && rtrim ( $propertyInfoEntry->hasAttribute ( "value" ) )) {
							$propertyType = $propertyInfoEntry->getAttribute ( "value" );
							$property->type = $propertyType;
						}
						if ($propertyInfoEntry->hasAttribute ( "key" ) && rtrim ( $propertyInfoEntry->getAttribute ( "key" ) ) == "relationship" && rtrim ( $propertyInfoEntry->hasAttribute ( "value" ) )) {
							/* Support of external relationship */
							$propertyType = $propertyInfoEntry->getAttribute ( "value" );
							$property->type = "object";
							$property->instanceOf = $propertyType;
							$property->isGeneratedType = true;
							$property->isExternal = true; // Used to prevent from generation
						}
						if ($propertyInfoEntry->hasAttribute ( "key" ) && rtrim ( $propertyInfoEntry->getAttribute ( "key" ) ) == "extractible" && rtrim ( $propertyInfoEntry->hasAttribute ( "value" ) )) {
							/* Support of extractibility */
							$property->isExtractible = (strtolower ( $propertyInfoEntry->getAttribute ( "value" ) ) != "no");
							var_dump ( $property->extractible );
						}
					}
				}
				$entityR->properties [$property->name] = $property;
				
				
			}
			
			
			// ////////////////////////////////////////////////////////////////
			//
			// -> ENTITY->relationships
			// Stored in <relationship> elements
			//
			// Sample :
			//
			// <entity name="Cell" syncable="YES">
			// ...
			// <relationship name="column" optional="YES" minCount="1" maxCount="1"
			// deletionRule="Nullify" destinationEntity="Column" inverseName="cells"
			// inverseEntity="Column" syncable="YES"/>
			// <relationship name="element" optional="YES" minCount="1" maxCount="1"
			// deletionRule="Nullify" destinationEntity="Element" inverseName="cells"
			// inverseEntity="Element" syncable="YES"/>
			//
			// ////////////////////////////////////////////////////////////////
			
			$relationships = $entity->getElementsByTagName ( 'relationship' );
			foreach ( $relationships as $relationship ) {
				
				// We create a property to hold the relationship
				$property = new PropertyRepresentation ();
				$property->metadata=array();
				
				if ($relationship->hasAttribute ( "name" )) {
					$property->name = $relationship->getAttribute ( "name" );
				} else {
					throw new Exception ( 'property with no name' );
				}
				fLog ( $entityR->name.'.'.$property->name.' '.cr(),true );
				$tooMany = false;
				if ($relationship->hasAttribute ( "toMany" )) {
					$tooMany = ($relationship->getAttribute ( "toMany" ) == "YES");
				}
				if ($relationship->hasAttribute ( "destinationEntity" )) {
					$destinationEntity = $relationship->getAttribute ( "destinationEntity" );
					if ($tooMany == true) {
						$property->type = "object";
						$property->instanceOf = $delegate->getCollectionClassName( $nativePrefix, $destinationEntity );
						$property->isGeneratedType = true;
					} else {
						$property->type = "object";
						$property->instanceOf = $nativePrefix . ucfirst ( $destinationEntity );
						$property->isGeneratedType = true;
					}
				} else {
					$property->type = ObjectiveCHelper::UNDEFINED_TYPE;
				}
				
				// Relationship metadata user infos
				
				$relationshipUserInfos = $relationship->getElementsByTagName ( 'userInfo' );
				foreach ( $relationshipUserInfos as $relationshipUserInfo ) {
					$userInfoEntries = $relationshipUserInfo->getElementsByTagName ( "entry" );
					foreach ( $userInfoEntries as $propertyInfoEntry ) {
						
						fLog ( 	 $entityR->name.'.'.$property->name .'.metadata : '.$this->elementToString($propertyInfoEntry).cr(),true );
						
						if ($propertyInfoEntry->hasAttribute ( "key" ) && $propertyInfoEntry->hasAttribute ( "value" )){
							$property->metadata[ rtrim ( $propertyInfoEntry->getAttribute ( "key" ))]=rtrim( $propertyInfoEntry->getAttribute ( "value" ));
						}
						
						if ($propertyInfoEntry->hasAttribute ( "key" ) && rtrim ( $propertyInfoEntry->getAttribute ( "key" ) ) == "extractible" && rtrim ( $propertyInfoEntry->hasAttribute ( "value" ) )) {
							/* Support of extractibility */
							$property->isExtractible = (strtolower ( $propertyInfoEntry->getAttribute ( "value" ) ) != "no");
						}
					}
				}
				
				// Add the property to the entity
				$entityR->properties [$property->name] = $property;
			}
			
			// We add the entity representations to the entities.
			$r->entities [$entityR->name] = $entityR;
		}
		
		fLog ( "" . cr (), true );
		
		fLog ( 	 "********************".cr(),true );
		fLog ( 	 "End of  Entities".cr(),true );
		fLog ( 	 "********************".cr().cr(),true );
		
		
		return $r;
	}
	
	public function elementToString($domElement){
		return $domElement->ownerDocument->saveXML($domElement);
	}
}

?>