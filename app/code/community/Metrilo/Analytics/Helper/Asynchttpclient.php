<?php
/**
 * Helper class for metrilo properties
 *
 * @author Zhivko Draganov <zhivko@metrilo.com>
 */
class Metrilo_Analytics_Helper_Asynchttpclient extends Mage_Core_Helper_Abstract
{
    /**
    * Create HTTP GET async request to URL
    *
    * @param String $url
    * @return void
    */
    public function get($url, $async = true)
    {
        $parsedUrl = parse_url($url);
        $raw = $this->_buildRawGet($parsedUrl['host'], $parsedUrl['path']);

        $this->_executeRequest($parsedUrl, $raw, $async);
    }

    /**
     * Create HTTP POSTasync request to URL
     * @param string $url
     * @param $bodyArray
     * @param bool $async
     * @return void
    */
    public function post($url, $bodyArray = false, $async = true)
    {
        $parsedUrl = parse_url($url);
        $encodedBody = $bodyArray ? json_encode($bodyArray) : '';

        $raw = $this->_buildRawPost($parsedUrl['host'], $parsedUrl['path'], $encodedBody);

        $this->_executeRequest($parsedUrl, $raw, $async);
    }

    private function _buildRawGet($host, $path)
    {
        $out  = "GET ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        // $out .= "Accept: application/json\r\n";
        $out .= "Connection: Close\r\n\r\n";

        return $out;
    }

    private function _buildRawPost($host, $path, $encodedCall)
    {
        $out  = "POST ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Content-Length: ".strlen($encodedCall)."\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "User-Agent: AsyncHttpClient/1.0.0\r\n";
        $out .= "Connection: Close\r\n\r\n";

        $out .= $encodedCall;

        return $out;
    }

    private function _executeRequest($parsedUrl, $raw, $async = true)
    {
        $fp = fsockopen($parsedUrl['host'],
                        isset($parsedUrl['port']) ? $parsedUrl['port'] : 80,
                        $errno, $errstr, 30);

        if ($fp) {
            fwrite($fp, $raw);

            if (!$async) {
                $this->_waitForResponse($fp);
            }

            fclose($fp);
        }
    }

    private function _waitForResponse($fp) {
        while (!feof($fp)) {
            fgets($fp, 1024);
        }
    }
}
