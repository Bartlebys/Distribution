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

/**
 * THIS TEMPLATE IS A SUB-TEMPLATE
 * IT RELIES ON  $sf
 * 	When using this template  you must define :  $collectionClassName;
 */
require_once FLEXIONS_MODULES_DIR . 'Watt-Objective-c/ObjCGeneric.functions.php';

/* @var $sf Flexed */
/* @var string $collectionClassName */


if(!isset($collectionClassName)){
    $collectionClassName='UNDEFINED_COLLECTION_CLASS_NAME';
}

$sf->fileName =$collectionClassName.".h";
$className=getClassNameFromCollectionClassName($collectionClassName);

?><?php ////////////   GENERATION STARTS HERE   ////////// ?>
<?php if($f->license!=null) include $f->license;?>
<?php echo getCommentHeader($sf);?>
<?php echo "#import \"$className.h\"";?> 
<?php echo "#import \"$collectionParentClass.h\"";?> 

@interface <?php echo"$collectionClassName:$collectionParentClass"?> <?php echo(isset($protocols))?"<$protocols>":"";?>{
}
- (void)enumerateObjectsUsingBlock:(void (^)(<?php echo $className;?> *obj, NSUInteger idx, BOOL *stop))block reverse:(BOOL)useReverseEnumeration;
- ( <?php echo"$collectionClassName"?>*)filteredCollectionUsingBlock:(BOOL (^)(<?php echo $className;?>  *obj))block withRegistry:(WattRegistry *)registry;
- (<?php echo $collectionClassName;?>*)filteredCollectionUsingPredicate:(NSPredicate *)predicate withRegistry:(WattRegistry *)registry;
- (NSUInteger)count;
- (<?php echo $className;?> *)objectAtIndex:(NSUInteger)index;
- (<?php echo $className;?> *)firstObject;
- (<?php echo $className;?> *)lastObject;
- (<?php echo $className;?> *)firstObjectCommonWithArray:(NSArray*)array;
- (void)addObject:(<?php echo $className;?>*)anObject;
- (void)insertObject:(<?php echo $className;?>*)anObject atIndex:(NSUInteger)index;
- (void)removeLastObject;
- (void)removeObjectAtIndex:(NSUInteger)index;
- (void)replaceObjectAtIndex:(NSUInteger)index withObject:(<?php echo $className;?>*)anObject;

@end
<?php ////////////   GENERATION ENDS HERE   ////////// ?>