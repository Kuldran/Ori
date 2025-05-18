<?php

declare(strict_types=1);

namespace Origin\Utilities\Fetch;

use \DOMDocument;
use \Origin\Utilities\Bucket\Bucket;
use \Origin\Utilities\Bucket\Common;
use \Origin\Utilities\Types\CustomStorage;

class Result {
    use Bucket, Common {
        Blob as Body;
        Any as Headers;
        Any as RawHeaders;
        Any as FullHeaders;
        Number as ResponseCode;
        Boolean as Completed;
    }
    
    public function __construct() {
        $this->Headers((new CustomStorage()));
        $this->RawHeaders((new CustomStorage()));
        $this->FullHeaders((new CustomStorage()));
        $this->Completed(FALSE);
    }
    
    private $json;
    public function JSON() {
        if ($this->json === NULL) {
            $this->json = json_decode($this->Body(), TRUE);
        }
        
        return $this->json;
    }
    
    private $document;
    public function Document() {
        if ($this->document === NULL) {
            $document = new DOMDocument();
            libxml_use_internal_errors(TRUE);
            if ($document->loadHTML($this->Body())) {
                $this->document = $document;
            }
        }
        
        return $this->document;
    }
}
