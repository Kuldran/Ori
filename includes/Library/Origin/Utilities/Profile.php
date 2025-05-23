<?php

declare(strict_types=1);

namespace Origin\Utilities;

use \Origin\Utilities\Bucket\Bucket;
use \Origin\Utilities\Bucket\Common;

class ProfileStorage {
    use Bucket, Common {
        String as Name;
        Number as Start;
        Number as Stop;
    }
    
    public function __construct($name) {
        $this->Name($name);
        $this->Start(microtime(TRUE));
    }
    
    public function Difference() {
        return round((($this->Stop() - $this->Start()) * 1000), 4);
    }
}

class Profile extends \Origin\Utilities\Types\Singleton {
    private $log;
    private $storage = [];
    public function __construct() {
        $this->log = \Origin\Log\Log::Get('profile');
    }
    
    public function __destruct() {
        $this->End();
    }
    
    public function Start($name) {
        if (!isset($this->storage[$name])) {
            $this->storage[$name] = [];
        }
        
        array_unshift($this->storage[$name], (new ProfileStorage($name)));
    }
    
    public function Stop($name) {
        if (!isset($this->storage[$name])) {
            return;
        }
        
        foreach ($this->storage[$name] as $profile) {
            if ($profile->Stop() === NULL) {
                $profile->Stop(microtime(TRUE));
                break;
            }
        }
    }
    
    public function End($name = NULL) {
        if ($name === NULL) {
            foreach ($this->storage as $name => $object) {
                $this->End($name);
            }
            
            return;
        }
        
        foreach ($this->storage[$name] as  $id => $object) {
            if ($object->Stop() === NULL) {
                $this->Stop($name);
            }
        }
        
        if (count($this->storage[$name]) === 1) {
            foreach ($this->storage[$name] as $id => $object) {
                $this->log->Warning($name, sprintf('Total time for %s: %s', $object->Name(), $object->Difference() . ' ms'));
            }
        } else {
            $total = 0;
            $highest = 0;
            $lowest = NULL;
            foreach ($this->storage[$name] as $id => $object) {
                $total += $object->Difference();
                if ($object->Difference() > $highest) {
                    $highest = $object->Difference();
                }
                
                if ($object->Difference() < $lowest || $lowest === NULL) {
                    $lowest = $object->Difference();
                }
            }
            
            $this->log->Warning($name, sprintf(
                '%s had %s iterations over %s ms, with an average of %s ms, a high of %s ms and a low of %s ms.',
                $name,
                count($this->storage[$name]),
                $total,
                ($total / count($this->storage[$name])),
                $highest,
                $lowest
            ));
        }
        
        unset($this->storage[$name]);
    }
}