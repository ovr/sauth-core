<?php

namespace Sauth\Util;

class Util
{

    /**
     * Executes post http request
     * @static
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return bool|mixed
     */
    public static function postRequest($url, $parameters = array(), $headers = array())
    {
        return self::request('POST', $url, $parameters, $headers);
    }

    /**
     * Executes get http request
     * @static
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return bool|mixed
     */
    public static function getRequest($url, $parameters = array(), $headers = array())
    {
        return self::request('GET', $url, $parameters, $headers);
    }

    /**
     * Executes http request with curl library
     * @static
     * @param $type
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return bool|mixed
     */
    public static function request($type, $url, $parameters = array(), $headers = array())
    {
        if (strtoupper($type) == 'POST') {
            $session = curl_init($url);

            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $parameters);
        } else {
            $url .= '?' . http_build_query($parameters, null, '&');
            $session = curl_init($url);
        }
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($session);
        if ($response) {

            $parsedResponse = self::parseResponse($session, $response);
            curl_close($session);
            return $parsedResponse;
        }
        return false;
    }

    /**
     * Parses response
     * @static
     * @param $session
     * @param $response
     * @return mixed
     */
    public static function parseResponse($session, $response)
    {
        $contentType = curl_getinfo($session, CURLINFO_CONTENT_TYPE);

        if(strpos($contentType, 'application/json') !== false ) {
            return json_decode($response, true);
        } else {
            return $response;
        }
    }
}
