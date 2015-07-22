<?php
/**
* Copyright 2015 André Ribeiro
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
namespace andrevabo\yii2\urlshortener;

use yii\base\Exception;
use linslin\yii2\curl;

class GoogleUrlShortener extends \yii\base\Component
{
    public $apiEndpoint = 'https://www.googleapis.com/urlshortener/v1/url';
    public $apiKey = null;

    function init()
    {
        parent::init();
        $this->apiEndpoint .= $this->apiKey !== null ? '?key=' . $this->apiKey . '&' : '';
    }

    /**
    * Shortens an URL
    * @param string $url  URL to be shortened
    * @return string  The shortened URL.
    * @throws Exception if the request fails
    */
    public function shorten($url)
    {
        $curl = new curl\Curl();
        $resp = $curl->setOption(CURLOPT_POSTFIELDS, json_encode(['longUrl' => $url]))
                     ->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json'])
                     ->post($this->apiEndpoint);

        if($curl->responseCode !== 200)
        {
            if($resp = json_decode($curl->response))
            {
                throw new Exception(sprintf('Error: %s - Response: %s', $resp->error->message, $curl->response), $resp->error->code);
            }
            else
            {
                throw new Exception('Unknown Error. Response: ' . $curl->response, $curl->responseCode);
            }
        }
        else
        {
            $resp = json_decode($resp);
            return $resp->id;
        }

        return false;
    }

    /**
    * Expands an URL
    * @param string $url  URL to be expanded
    * @return string|boolean  The expanded URL if the longUrl is set in the response, FALSE otherwise.
    * @throws Exception if the request fails
    */
    public function expand($url)
    {
        $curl = new curl\Curl();

        $resp = $curl->get($this->apiEndpoint . 'shortUrl=' . $url);

        if($curl->responseCode !== 200)
        {
            if($resp = json_decode($curl->response))
            {
                throw new Exception(sprintf('Error: %s - Response: %s', $resp->error->message, $curl->response), $resp->error->code);
            }
            else
            {
                throw new Exception('Unknown Error. Response: ' . $curl->response, $curl->responseCode);
            }
        }
        else
        {
            $resp = json_decode($resp);

            if(!empty($resp->longUrl))
            {
                return $resp->longUrl;
            }
        }

        return false;
    }

}
