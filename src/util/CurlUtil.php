<?php

namespace ngs\AdminTools\util;

class CurlUtil
{
    private array $params;
    private int $retired = 0;


    public function __construct(array $params = []) {
        $this->params = $params;
    }


    /**
     * do GET request to given url
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     *
     * @return array
     */
    public function get(string $url, array $params = [], array $headers = []) {
        return $this->doRequest($url, $params, $headers, 'GET');
    }


    /**
     * do POST request to given url
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     *
     * return array
     */
    public function post(string $url, array $params, array $headers = []) {
        return $this->doRequest($url, $params, $headers, 'POST');
    }


    /**
     * init curl and do request, on fail will try retries_count times, if not set by default will try 3 times
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param string $method
     *
     * @return array
     *
     * @throws \Exception
     */
    private function doRequest(string $url, array $params = [], array $headers = [], string $method = 'GET') :array {
        $ch = curl_init();
        if($method === 'GET' && $params) {
            $paramsAsString = http_build_query($params);
            if($paramsAsString) {
                $url .= '?' . $paramsAsString;
            }
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, $method === 'POST');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->params['timeout'] ?? 0); //timeout in seconds

        if($method === 'POST') {
            $paramsAsString = json_encode($params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch,CURLOPT_POSTFIELDS, $paramsAsString);
        }
        if($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        $resultDecoded = json_decode($result, true);

        if(!$resultDecoded) {
            throw new \Exception('failed to decode response: ' . $result);
        }

        $retriesPossibleCount = $this->params['retries_count'] ?? 3;

        if($curlErrno > 0 && $this->retired < $retriesPossibleCount) {
            unset($result);
            unset($resultDecoded);
            $this->retired++;
            return $this->doRequest($url, $params, $headers, $method);
        }

        $this->retired = 0;
        return $resultDecoded;
    }
}

