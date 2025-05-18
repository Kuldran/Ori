<?php

declare(strict_types=1);

namespace Origin\Utilities\Fetch;

use \Exception;
use \Origin\Utilities\Bucket\Bucket;
use \Origin\Utilities\Bucket\Common;

class Request {
    use Bucket, Common {
        Blob as URL;
        Hash as GetParameters;
        Hash as PostParameters;
        Blob as UserAgent;
        Hash as Headers;
        Any as HeaderCallback;
        Blob as Fragment;
        Blob as CustomRequest;
        Boolean as JSONPost;
        Blob as RawPost;
        Blob as Error;
        Any as Cookie;
        Any as Proxy;
        
        Number as Timeout;
        Number as ConnectTimeout;
        
        Any as Result;
    }
    
    const GENERIC_USERAGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36';
    public function __construct() {
        $this->JSONPost(FALSE);
        $this->Headers(['accept-language' => 'en-US,en;q=0.9']);
        $this->UserAgent(static::GENERIC_USERAGENT);
        
        $this->Timeout(10);
        $this->ConnectTimeout(3);
    }
    
    public function RandomProxy() {
        $proxy = new Proxy();
        $proxy->OrderBy('RAND()');
        if ($proxy->QueryObject(NULL, FALSE, TRUE)) {
            $this->Proxy($proxy);
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function Fetch() {
        if ($this->Result() === NULL) {
            $this->Result((new Result()));
            $curl = $this->CreateCURL();

            $result = curl_exec($curl);
            if ($result === FALSE) {
                $this->Error(curl_error($curl));
                return FALSE;
            }
            
            $this->Result()->Body($result);
            $this->Result()->ResponseCode((int) curl_getinfo($curl, CURLINFO_HTTP_CODE));
            curl_close($curl);

            return $this->Result()->Completed(TRUE);
        }
        
        throw new Exception('You can\'t make two requests from one request object. Make a new object.');
        return FALSE;
    }
    
    /*
    * For use by the multirequest processor. DO NOT CALL THIS FOR NORMAL USE.
    */
    private $curl;
    public function FetchCURL() {
        if ($this->curl === NULL) {
            $this->curl = $this->CreateCURL();
        }
        
        return $this->curl;
    }
    
    private function CreateCURL() {
        $curl = curl_init();
        
        $last_line = NULL;
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($c, $line) use (&$last_line) {
            // Reset stored headers if following a redirect.
            if ($last_line === "\r\n") {
                $this->Result()->Headers()->exchangeArray([]);
                $this->Result()->RawHeaders()->exchangeArray([]);
            }
            
            $last_line = $line;
            if ($this->HeaderCallback() !== NULL) {
                $tmp = $this->HeaderCallback();
                $tmp($line);
            }
            
            $key = strtok($line, ':');
            $value = strtok('\n');
            $this->Result()->Headers()->offsetSet($key, $value);
            $this->Result()->RawHeaders()->append($line);
            $this->Result()->FullHeaders()->append($line);
            
            return strlen($line);
        });
        
        if ($this->Cookie() === NULL) {
            $this->Cookie((new Cookie()));
        }
        
        if ($this->Proxy() !== NULL) {
            curl_setopt($curl, CURLOPT_PROXY, $this->Proxy()->Connection());
            
            if ($this->Proxy()->Password() !== NULL && $this->Proxy()->Username() !== NULL) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->Proxy()->Username().':'.$this->Proxy()->Password());
            }
        }
        
        curl_setopt($curl, CURLOPT_URL, $this->DetermineURL().$this->DetermineGETParameters().$this->DetermineFragment());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->UserAgent());
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->Cookie()->CookieFile());
        
        if ($this->Headers() === NULL) {
            curl_setopt($curl, CURLOPT_HEADER, FALSE);
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->ProcessedHeaders());
        }
        
        if ($this->PostParameters() !== NULL) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->DeterminePOSTParameters());
        }
        
        if ($this->CustomRequest() !== NULL) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->CustomRequest());
        }
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->ConnectTimeout());
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->Timeout());
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        
        return $curl;
    }
    
    private function DetermineURL() {
        $query = [];
        $parsed_url = parse_url($this->URL());
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
        $pass = ($user || $pass) ? "$pass@" : ''; 
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $fragment = isset($parsed_url['fragment']) ? $parsed_url['fragment'] : NULL;
        
        parse_str($parsed_url['query'] ?? '', $query);
        
        if (!empty($query)) {
            if ($this->GetParameters() === NULL) {
                $this->GetParameters($query);
            } else {
                $this->GetParameters(array_merge($query, $this->GetParameters()));
            }
        }
        
        if ($fragment !== NULL && $this->Fragment() === NULL) {
            $this->Fragment($fragment);
        }
        
        return $scheme.$user.$pass.$host.$port.$path;
    }
    
    private function DetermineGETParameters() {
        if ($this->GetParameters() !== NULL) {
            return '?'.http_build_query($this->GetParameters());
        }
    }
    
    private function DetermineFragment() {
        if ($this->Fragment() !== NULL) {
            return '#'.$this->Fragment();
        }
    }
    
    private function ProcessedHeaders() {
        $result = [];
        foreach ($this->Headers() as $key => $header) {
            $result[] = $key.': '.$header;
        }
        
        if ($this->PostParameters() !== NULL && $this->JSONPost() === FALSE) {
            //$result[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
        }
        
        return $result;
    }
    
    private function DeterminePOSTParameters() {
        if ($this->JSONPost()) {
            return json_encode($this->PostParameters());
        }
        
        if ($this->RawPost() !== NULL) {
            return $this->RawPost();
        }
        
        return http_build_query($this->PostParameters());
    }
}
