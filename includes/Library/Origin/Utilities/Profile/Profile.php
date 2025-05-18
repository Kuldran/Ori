<?php

declare(strict_types=1);

namespace Origin\Utilities\Profile;

use \Origin\Utilities\Profile\Interfaces\iStorage;
use \Origin\Utilities\Types\CustomStorage;

class Profile extends \Origin\Utilities\Types\Singleton {
    private $timings;
    private $handler;
    private $storage;
    private $start;
    public function __construct() {
        $this->start = microtime(TRUE);
        $this->timings = new CustomStorage(); // Holds timings.
        $this->storage = new GenericStorage(); // Writes timings to disk or DB or whatever.
        $this->handler = get_class((new GenericTiming()));
        
        register_shutdown_function(function () {
            $this->Shutdown();
        });
    }
    
    public function __destruct() {
        $this->Shutdown();
    }
    
    private $shutdown = FALSE;
    public function Shutdown() {
        if ($this->shutdown === FALSE) {
            $this->shutdown = TRUE;
            $this->storage->Shutdown($this->timings, $this->start, microtime(TRUE));
        }
    }
    
    /*
    * Allows for custom writing of timings.
    */
    public function SetStorage(iStorage $storage) {
        $this->storage = $storage;
    }
    
    /*
    * Allows for a custom handler.
    */
    public function SetHandler(iTimingHandler $handler) {
        $this->handler = get_class($handler);
    }
    
    /*
    * Start with function name prepended to name.
    */
    public function FStart($name = NULL) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $name = $backtrace['file']."\\".$backtrace['function']."() - ".$name;
        $this->Start($name, $backtrace);
    }
    
    /*
    * Start a timing. No label required, but encouraged.
    */
    public function Start($name = NULL, $backtrace = NULL) {
        if ($name === NULL) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $name = $backtrace['file']."\\".$backtrace['function']."()";
        }
        
        if (!$this->timings->offsetExists($name)) {
            if ($backtrace === NULL) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            }

            $cn = $this->handler;
            $temp = (new $cn);
            $temp->Name($name);
            $temp->File($backtrace['file']);
            $temp->Line($backtrace['line']);
            $this->timings->offsetSet($name, $temp);
        }
        
        $this->timings->offsetGet($name)->Start();
    }
    
    /*
    * Stop with function name prepended to name.
    */
    public function FStop($name = NULL) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $name = $backtrace['file']."\\".$backtrace['function']."() - ".$name;
        $this->Stop($name, $backtrace);
    }
    
    public function Stop($name = NULL, $backtrace = NULL) {
        if ($name === NULL) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $name = $backtrace['file']."\\".$backtrace['function']."()";
        }
        
        if ($this->timings->offsetExists($name)) {
            $this->timings->offsetGet($name)->Stop();
        }
    }
}