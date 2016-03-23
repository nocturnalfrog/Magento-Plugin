<?php
/**
 * Helper class for metrilo properties
 *
 * @author Zhivko Draganov <zhivko@metrilo.com>
 */
class Metrilo_Analytics_Helper_AsyncHttpClient extends Mage_Core_Helper_Abstract
{
    /**
    * Create HTTP GET async request to URL
    *
    * @param String $url
    * @return void
    */
    public function get($url) {
        $parsedUrl = parse_url($url);
        $raw = $this->_getHeaders($parsedUrl['host'], $parsedUrl['path']);

        $fp = fsockopen(
            $parsedUrl['host'],
            isset($parsedUrl['port']) ? $parsedUrl['port'] : 80,
            $errno, $errstr, 30);

        fwrite($fp, $raw);
        fclose($fp);
    }

    /**
    * Create HTTP POSTasync request to URL
    *
    * @param String $url
    * @param Array $bodyArray
    * @return void
    */
    public function post($url, $bodyArray = false) {
        $parsedUrl = parse_url($url);
        $raw = $this->_getHeaders($parsedUrl['host'], $parsedUrl['path']);

        if ($bodyArray) {
            $raw .= jsonEncode($bodyArray);
        }

        $fp = fsockopen(
            $parsedUrl['host'],
            isset($parsedUrl['port']) ? $parsedUrl['port'] : 80,
            $errno, $errstr, 30);

        fwrite($fp, $raw);
        fclose($fp);
    }

    private function _getHeaders($host, $path) {
        $out  = "GET ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        // $out .= "Accept: application/json\r\n";
        $out .= "Connection: Close\r\n\r\n";

        return $out;
    }

    private function _postHeaders($host, $path) {
        $out  = "POST ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        // $out .= "Accept: application/json\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Content-Length: ".strlen($encoded_call)."\r\n";
        $out .= "Connection: close\r\n\r\n";

        return $out;
    }
}
