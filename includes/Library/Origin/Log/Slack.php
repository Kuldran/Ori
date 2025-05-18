<?php

declare(strict_types=1);

namespace Origin\Log;

use \Origin\Utilities\Settings;

/* Requires a slack_url setting in 'site' */
class Slack extends \Origin\Utilities\Types\Singleton {
    public function Notify($message) {
        $slack = curl_init(Settings::Get()->Value(['site', 'slack_url']));
        curl_setopt($slack, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($slack, CURLOPT_POSTFIELDS, json_encode(['text' => $message]));
        curl_setopt($slack, CURLOPT_RETURNTRANSFER, FALSE);
        //curl_setopt($slack, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_string)));
        curl_exec($slack);
        
        return TRUE;
    }
}