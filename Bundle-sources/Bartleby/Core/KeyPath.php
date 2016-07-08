<?php

namespace Bartleby\Core;

/**
 * Class KeyPath
 * Inspired by Cocoa KVC
 * @package Bartleby\Core
 */
class KeyPath {


    /**
     * Returns a value or null
     *
     * @param $instance
     * @param $keyPath
     * @return null
     */
    public static function valueForKeyPath($instance,$keyPath){
        $keyComponents=explode('.',$keyPath);
        if (count($keyComponents)>0){
            $key=array_shift($keyComponents);
            $c=count($keyComponents);
            if($c>1){
                $nextKeyPath=implode('.',$keyComponents);
            }else{
                $nextKeyPath=implode('',$keyComponents);
            }
            if (is_array($instance)){
                if(array_key_exists($key,$instance)) {
                    $member = $instance[$key];
                    if ($c==0){
                        return $member;
                    }
                    return KeyPath::valueForKeyPath($member, $nextKeyPath);
                }
            } else {
                if (property_exists($instance, $key)) {
                    $member = $instance->{$key};
                    if ($c == 0) {
                        return $member;
                    }
                    return KeyPath::valueForKeyPath($member, $nextKeyPath);
                }
            }
        }
        return NULL;
    }

    /**
     * Sets the value for a given keyPath
     *
     * @param $instance the instance is passed by reference.
     * @param $keyPath
     * @param $value
     */
    public static function setValueByReferenceForKeyPath(&$instance,$keyPath,$value){
        $keyComponents=explode('.',$keyPath);
        if (count($keyComponents)>0){
            $key=array_shift($keyComponents);
            $c=count($keyComponents);
            if($c>1){
                $nextKeyPath=implode('.',$keyComponents);
            }else{
                $nextKeyPath=implode('',$keyComponents);
            }
            if (is_array($instance)){
                if(array_key_exists($key,$instance)) {
                    if ($c==0){
                        $instance[$key]=$value;
                    }else{
                        KeyPath::setValueByReferenceForKeyPath($instance[$key], $nextKeyPath,$value);
                    }

                }
            } else {
                if (property_exists($instance, $key)) {
                    if ($c == 0) {
                        $instance->{$key} = $value;
                    } else {
                        KeyPath::setValueByReferenceForKeyPath($instance->{$key}, $nextKeyPath, $value);
                    }

                }
            }
        }
    }

    /**
     * Removes the entity at a given keypath
     * @param $instance the instance is passed by reference
     * @param $keyPath
     * @param $value
     */
    public static function removeKeyPathByReference(&$instance,$keyPath){
        $keyComponents=explode('.',$keyPath);
        if (count($keyComponents)>0){
            $key=array_shift($keyComponents);
            $c=count($keyComponents);
            if($c>1){
                $nextKeyPath=implode('.',$keyComponents);
            }else{
                $nextKeyPath=implode('',$keyComponents);
            }
            if (is_array($instance)){
                if(array_key_exists($key,$instance)) {
                    if ($c==0){
                        unset($instance[$key]);
                    }else{
                        KeyPath::removeKeyPathByReference($instance[$key], $nextKeyPath);
                    }
                }
            } else {
                if (property_exists($instance, $key)) {
                    if ($c == 0) {
                        unset($instance->{$key});
                    } else {
                        KeyPath::removeKeyPathByReference($instance->{$key}, $nextKeyPath);
                    }
                }
            }
        }
    }



}