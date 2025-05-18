<?php

declare(strict_types=1);

namespace Origin\Utilities\Types;

abstract class Singleton {
    protected function __construct() {
    }
    
    final public static function Get($version = NULL) {
        static $instances = [];
        $calledClass = get_called_class();
        if ($version !== NULL) {
            if (!isset($instances[$calledClass.$version])) {
                $instances[$calledClass.$version] = new $calledClass($version);
            }
            
            return $instances[$calledClass.$version];
        } 
        
        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass();
        }
        return $instances[$calledClass];
    }
    private function __clone() {
    }
}
