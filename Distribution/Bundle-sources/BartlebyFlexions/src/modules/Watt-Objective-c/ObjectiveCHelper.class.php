<?php

/*
 * Created by Benoit Pereira da Silva on 20/04/2013. Copyright (c) 2013
 * http://www.chaosmos.fr This file is part of Flexions Flexions is free software: you can
 * redistribute it and/or modify it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as
 * published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version. Flexions is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU LESSER GENERAL PUBLIC LICENSE for more details. You should have received a
 * copy of the GNU LESSER GENERAL PUBLIC LICENSE along with Flexions If not, see
 * <http://www.gnu.org/Licenses/>
 */

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';

class ObjectiveCHelper {
	const UNDEFINED_TYPE = "UNDEFINED_TYPE";
	
	/**
	 */
	public $useARC = true; // Non ARC will be soon deprecated (it is not usefull anymore)
	
	/*
	 * we do expose primitive types for documentation purposes to perform metaprogramming more
	 * easily
	 */
	
	/**
	 *
	 * @return array of string enumerates the supported types as en entries in the hypotypose
	 */
	public function getPrimitiveTypes() {
		return array (
				"string",
				"mutable_string",
				"object",
				"array",
				"mutable_array",
				"mutable_dictionary",
				"dictionary",
				"null",
				"any",
				"uint",
				"int",
				"int 16",
				"int 32",
				"int 64",
				"unsigned int",
				"long long",
				"unsigned long",
				"unsigned long long",
				"integer",
				"float",
				"double",
				"bool",
				"number",
				"numeric",
				"rect",
				"size",
				"point" 
		);
	}
	
	/**
	 *
	 * @return array of string the list of the supported native types.
	 */
	public function getPrimitiveNativeTypes() {
		return array (
				"NSString",
				"NSMutableString",
				"NSObject",
				"NSArray",
				"NSMutableArray",
				"NSDictionary",
				"NSMutableDictionary",
				"NSNull",
				"id",
				"NSInteger",
				"float",
				"double",
				"BOOL",
				"NSNumber",
				"GGRect",
				"CGSize",
				"CGPoint" 
		);
	}
	
	/**
	 * Returns the property d�claration
	 *
	 * @param PropertyRepresentation $property        	
	 * @param bool $allowScalars
	 *        	used for example when generating for Core data
	 * @return string
	 */
	public function getPropertyDeclaration(PropertyRepresentation $property, $allowScalars = true) {
		$t = $this->nativeTypeForProperty ( $property, $allowScalars );
		$options = $this->propertyDeclarationOptionForNativeType ( $t );
		$p = $this->isScalar ( $t ) == true ? "" : "*";
		return "@property (" . $options . ") " . $t . " " . $p . " " . $property->name . ";";
	}
	
	/**
	 * Return a string containing the property declaration options
	 *
	 * @param string $nativeTypeName        	
	 * @return string
	 */
	public function propertyDeclarationOptionForNativeType($nativeTypeName) {
		if ($nativeTypeName == 'NSInteger' or $nativeTypeName == 'NSUInteger' or $nativeTypeName == 'CGRect' or $nativeTypeName == 'CGSize' or $nativeTypeName == 'CGPoint' or $nativeTypeName == 'id' or $nativeTypeName == 'float' or $nativeTypeName == 'double' or $nativeTypeName == 'BOOL') {
			return "nonatomic,assign";
		}
		if ($nativeTypeName == "NSString" or $nativeTypeName == "NSMutableString")
			return "nonatomic,copy";
		return "nonatomic,strong";
	}
	
	/**
	 *
	 * @param PropertyRepresentation $property        	
	 * @param bool $allowScalars        	
	 * @return string
	 */
	public function nativeTypeForProperty(PropertyRepresentation $property, $allowScalars = true) {
		return $this->determineNativeTypeFromString ( $property->type, $property->instanceOf, $allowScalars );
	}
	
	/**
	 * From the allowable types, determine the type that the variable matches
	 *
	 * @param string $type
	 *        	Parameter type
	 * @return string Returns the matching type on
	 */
	public function determineNativeTypeFromString($type, $instanceOf = null, $allowScalars = true) {
		// We return directly any UNDEFINED_TYPE
		if ($type == ObjectiveCHelper::UNDEFINED_TYPE) {
			return $type;
		}
		$rType = $type;
		$t = trim ( strtolower ( $type ) );
		
		if ($t == 'string') {
			$rType = 'NSString';
		} elseif ($t == 'mutable_string') {
			$rType = 'NSMutableString';
		} elseif ($t == 'object' && $instanceOf == null) {
			$rType = 'NSObject';
		} elseif ($t == 'array') {
			$rType = 'NSArray';
		} elseif ($t == 'mutable_array') {
			$rType = 'NSMutableArray';
		} elseif ($t == 'dictionary') {
			$rType = 'NSDictionary';
		} elseif ($t == 'mutable_dictionary') {
			$rType = 'NSMutableDictionary';
		} elseif ($t == 'null') {
			$rType = 'NSNull';
		} elseif ($t == 'any') {
			$rType = 'id';
		} elseif (	strpos ( $t, 'int' ) === 0 or
						strpos ( $t, 'int32_t' === 0 )	or
						strpos ( $t, 'int64_t' === 0 )or
						strpos ( $t, 'long' === 0 )
				) {
			if ( 	strpos ( $t, 'uint' ) !== false  or 
					strpos ( $t, 'u_' !== false ) or
					strpos ( $t, 'unsigned' !== false )
					) {
					$rType = 'NSUInteger';
			} else {
				$rType = 'NSInteger';
			}
		} elseif ($t == 'float') {
			$rType = 'float';
		} elseif ($t == 'double') {
			$rType = 'double';
		} elseif ($t == 'boolean') {
			$rType = 'BOOL';
		} elseif ($t == 'number') {
			$rType = 'NSNumber';
		} elseif ($t == 'numeric') {
			$rType = 'NSNumber';
		} elseif ($t == 'rect') {
			$rType = 'CGRect';
		} elseif ($t == 'size') {
			$rType = 'CGSize';
		} elseif ($t == 'point') {
			$rType = 'CGPoint';
		}
		
		// We cast any scalar to NSNumber
		if ($allowScalars == false && $this->isScalar ( $rType ) == true && $rType != 'id') {
			$rType = 'NSNumber';
		}
		// The $rType can still be equal to the initial type.
		if ($t == 'object' && $instanceOf != null) {
			// We consider that it is not a primitive and return the entry
			$rType = $instanceOf;
		}
		return $rType;
	}
	
	// @todo
	public function formatSpecifierFromNativeType($type) {
		$type = strtolower ( $type );
		$type = trim ( $type );
		if ($type == 'int')
			return "%i";
		if ($type == 'float' or $type == 'double')
			return "%f";
		return "%@";
	}
	
	/**
	 *
	 * @param string $expr
	 *        	for example :"self.propertyName"
	 * @param string $type
	 *        	the native type
	 * @return string
	 */
	public function objectFromExpression($expr, $type) {
		
		/*
		 * NSNumber support : Supported : + (NSNumber *)numberWithChar:(char)value; + (NSNumber
		 * *)numberWithShort:(short)value; + (NSNumber *)numberWithLong:(long)value; + (NSNumber
		 * *)numberWithFloat:(float)value; + (NSNumber *)numberWithDouble:(double)value; + (NSNumber
		 * *)numberWithBool:(BOOL)value; + (NSNumber *)numberWithInteger:(NSInteger)value
		 * NS_AVAILABLE(10_5, 2_0); + (NSNumber *)numberWithUnsignedInteger:(NSUInteger)value
		 * NS_AVAILABLE(10_5, 2_0); Currently not supported : + (NSNumber
		 * *)numberWithInt:(int)value; // We use NSInteger and NSUInteger + (NSNumber
		 * *)numberWithUnsignedChar:(unsigned char)value; + (NSNumber
		 * *)numberWithUnsignedShort:(unsigned short)value; + (NSNumber
		 * *)numberWithUnsignedInt:(unsigned int)value; + (NSNumber
		 * *)numberWithUnsignedLong:(unsigned long)value; + (NSNumber *)numberWithLongLong:(long
		 * long)value; + (NSNumber *)numberWithUnsignedLongLong:(unsigned long long)value;
		 */
		if ($type == 'char')
			return "@(" . $expr . ")";
		
		if ($type == 'short')
			return "@(" . $expr . ")";
		
		if ($type == 'NSUInteger')
			return "@(" . $expr . ")";
		
		if ($type == 'NSInteger')
			return "@(" . $expr . ")";
		
		if ($type == 'long')
			return "@(" . $expr . ")";
		
		if ($type == 'float')
			return "@(" . $expr . ")";
		
		if ($type == 'double')
			return "@(" . $expr . ")";
		
		if ($type == 'BOOL')
			return "@(" . $expr . ")";
		
		if ($type == "CGRect")
			return "[NSValue valueWithCGRect:$expr]";
		
		if ($type == 'CGSize')
			return "[NSValue valueWithCGSize:$expr]";
		
		if ($type == "CGPoint")
			return "[NSValue valueWithCGPoint:$expr]";
			
			// Any object
		return ( string ) $expr;
	}
	
	/**
	 * We define a symetric KVC implementation for extended supported types.
	 *
	 * Example of usage :
	 *
	 * $valueString=$languageHelper->valueStringForProperty("value",$property);
	 * echoIndent("[self setValue:$valueString forKey:@\"$name\"];\n",2);
	 *
	 * @param string $valueString        	
	 * @param PropertyRepresentation $p        	
	 * @return string
	 */
	function valueStringForProperty($valueString, PropertyRepresentation $p) {
		if ($p->isGeneratedType == true) {
			return "[" . $p->instanceOf . " instanceFromDictionary:$valueString inRegistry:_registry includeChildren:NO]";
		}
		$nativeType = $this->nativeTypeForProperty ( $p );
		/*
		 * NOT USEFULL if($nativeType=='CGRect') return "[$valueString CGRectValue]";
		 * if($nativeType=='CGSize') return "[$valueString CGSizeValue]"; if($nativeType=='CGPoint')
		 * return "[$valueString CGPointValue]";
		 */
		return $valueString;
	}
	
	/**
	 *
	 * @param string $nativeTypeName        	
	 * @return boolean
	 */
	public function isScalar($nativeTypeName) {
		if (
				$nativeTypeName == 'id' or 		// Not really scalar.
				$nativeTypeName =='char' or 
				$nativeTypeName =='short' or
				$nativeTypeName =='long' or
				$nativeTypeName == 'NSInteger' or 
				$nativeTypeName == 'NSUInteger' or
				$nativeTypeName == 'float' or 
				$nativeTypeName == 'double' or 
				$nativeTypeName == 'BOOL' or 
				$nativeTypeName == 'CGRect' or
				$nativeTypeName == 'CGPoint' or 
				$nativeTypeName == 'CGSize'
			)
			return true;
		return false;
	}
	
	/**
	 *
	 * @param string $source        	
	 * @param string $style        	
	 * @return string
	 */
	public function indent($source, $style) {
		return $source;
	}

}

?>