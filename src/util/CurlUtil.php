<?php

namespace ngs\AdminTools\util;

class CurlUtil
{
    private $ch;
    private array $params;
    private int $retired = 0;


    public function __construct(array $params = []) {
        $this->ch = curl_init();
        $this->params = $params;
    }


    /**
     * do get request to given url
     * @param string $url
     * @param array $headers
     *
     * @return array
     */
    public function get(string $url, array $headers = []) {

        curl_setopt($this->ch,CURLOPT_URL, $url);
        curl_setopt($this->ch,CURLOPT_POST, false);
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FAILONERROR, false);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->params['timeout'] ?? 0); //timeout in seconds

        if($headers) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }

        return $this->doRequest();
    }


    /**
     * do get request to given url
     * @param string $url
     * @param array $params
     * @param array $headers
     *
     * return array
     */
    public function post(string $url, array $params, array $headers = []) {
        $paramsAsString = http_build_query($params);

        curl_setopt($this->ch,CURLOPT_URL, $url);
        curl_setopt($this->ch,CURLOPT_POST, true);
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $paramsAsString);
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FAILONERROR, false);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->params['timeout'] ?? 0); //timeout in seconds

        if($headers) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }

        return $this->doRequest();
    }


    /**
     * do request by specified ch
     *
     * @return array
     *
     * @throws \Exception
     */
    private function doRequest() :array {
        $result = curl_exec($this->ch);
        $curlErrno = curl_errno($this->ch);
        $resultDecoded = json_decode($result, true);

        if(!$resultDecoded) {
            throw new \Exception('failed to decode response: ' . $result);
        }

        $retriesPossibleCount = $this->params['retries_count'] ?? 3;

        if($curlErrno > 0 && $this->retired < $retriesPossibleCount) {
            unset($result);
            unset($resultDecoded);
            $this->retired++;
            return $this->doRequest();
        }

        $this->retired = 0;
        return $resultDecoded;
    }
}

