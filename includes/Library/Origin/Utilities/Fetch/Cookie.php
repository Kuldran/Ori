<?php

declare(strict_types=1);

namespace Origin\Utilities\Fetch;

use \Origin\Utilities\Utilities;

class Cookie {
    private $file;
    public function CookieFile() {
        if ($this->file === NULL) {
            $this->GenerateCookieFile();
        }
        
        return $this->file;
    }
    
    public function __destruct() {
        $this->DeleteCookieFile();
    }
    
    public function DeleteCookieFile() {
        if ($this->file !== NULL) {
            unlink($this->file);
        }
        
        return TRUE;
    }
    
    const BASE_COOKIE_LOCATION = 'hidden/cache/cookies/';
    public function GenerateCookieFile() {
        if (!file_exists(static::BASE_COOKIE_LOCATION)) {
            mkdir(static::BASE_COOKIE_LOCATION);
        }
        
        $this->file = static::BASE_COOKIE_LOCATION.time().Utilities::RandomString(10);
        touch($this->file);
        
        return TRUE;
    }
}