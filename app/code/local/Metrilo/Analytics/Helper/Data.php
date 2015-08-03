<?php
/**
 * Helper class for metrilo properties
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get session instance
     *
     * @return Mage_Core_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get API Token from system configuration
     *
     * @return string
     */
    public function getApiToken()
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_key');
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiSecret()
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_secret');
    }

    /**
     * Add event to queue
     *
     * @param string $method Can be identiy|track
     * @param string $type
     * @param string|array $data
     */
    public function addEvent($method, $type, $data)
    {
        $events = (array)$this->getSession()->getData(Metrilo_Analytics_Block_Head::DATA_TAG);
        $events[] = array(
            'method' => $method,
            'type' => $type,
            'data' => $data
        );
        $this->getSession()->setData(Metrilo_Analytics_Block_Head::DATA_TAG, $events);
    }

    /**
     * Get order details and sort them for metrilo
     *
     * @param  Mage_Sales_Model_Order $order
     * @return array
     */
    public function prepareOrderDetails($order)
    {
        $data = array(
            'order_id'          => $order->getIncrementId(),
            'order_status'      => $order->getStatus(),
            'amount'            => (float)$order->getGrandTotal(),
            'shipping_amount'   => (float)$order->getShippingAmount(),
            'tax_amount'        => $order->getTaxAmount(),
            'items'             => array(),
            'shipping_method'   => $order->getShippingDescription(),
            'payment_method'    => $order->getPayment()->getMethodInstance()->getTitle(),
        );

        if ($order->getCouponCode()) {
            $data['coupons'] = $order->getCouponCode();
        }
        $skusAdded = array();
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getSku(), $skusAdded)) continue;

            $skusAdded[] = $item->getSku();
            $dataItem = array(
                'id'        => $item->getProductId(),
                'price'     => (float)number_format($item->getPrice(), 2),
                'name'      => $item->getName(),
                'url'       => $item->getProduct()->getProductUrl(),
                'quantity'  => $item->getQtyOrdered()
            );
            if ($item->getProductType() == 'configurable') {
                $mainProduct = Mage::getModel('catalog/product')->load($item->getProductId());
                $options = $item->getProductOptions();
                $dataItem['price'] = number_format($mainProduct->getFinalPrice(), 2);
                $dataItem['option_id'] = $item->getSku();
                $dataItem['option_name'] = $item->getName();
                $dataItem['option_price'] = (float)number_format($item->getPrice(), 2);
            }
            $data['items'][] = $dataItem;
        }
        return $data;
    }

    /**
     * Create HTTP request to metrilo server
     *
     * @param  string  $ident
     * @param  string  $event
     * @param  array  $params
     * @param  boolean|array $identityData
     * @param  boolean|int $time
     * @param  boolean|array $callParameters
     * @return void
     */
    public function callApi($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
        try {
            $call = array(
                'event_type'    => $event,
                'params'        => $params,
                'uid'           => $ident,
                'token'         => $this->getApiToken()
            );
            if($time) {
                $call['time'] = $time;
            }

            // check for special parameters to include in the API call
            if($callParameters) {
                if($callParameters['use_ip']) {
                    $call['use_ip'] = $callParameters['use_ip'];
                }
            }
            // put identity data in call if available
            if($identityData) {
                $call['identity'] = $identityData;
            }

            // sort for salting and prepare base64
            ksort($call);
            $based_call = base64_encode(Mage::helper('core')->jsonEncode($call));
            $signature = md5($based_call.$this->getApiSecret());
            // Use Varien_Http_Client
            // to generate API call end point and call it
            $url = 'http://p.metrilo.com/t?s='.$signature.'&hs='.$based_call;
            $client = new Varien_Http_Client($url);
            $response = $client->request();
            $result = Mage::helper('core')->jsonDecode($response->getBody());
            if (!$result['status']) {
                Mage::log($result['error'], null, 'Metrilo_Analytics.log');
            }
        } catch (Exception $e){
            Mage::log($e->getMessage(), null, 'Metrilo_Analytics.log');
        }
    }
}