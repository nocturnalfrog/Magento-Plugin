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
    * Get storeId for the current request context
    *
    * @return string
    */
    public function getStoreId($request = null) {
        if ($request) {
            # If request is passed retrieve store by storeCode
            $storeCode = $request->getParam('store');

            if ($storeCode) {
                return Mage::getModel('core/store')->load($storeCode)->getId();
            }
        }

        # If no request or empty store code
        return Mage::app()->getStore()->getId();
    }

    /**
     * Check if metrilo module is enabled
     *
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/enable', $storeId);
    }

    /**
     * Get API Token from system configuration
     *
     * @return string
     */
    public function getApiToken($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_key', $storeId);
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiSecret($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_secret', $storeId);
    }

    /**
     * Add event to queue
     *
     * @param string $method Can be identify|track
     * @param string $type
     * @param string|array $data
     */
    public function addEvent($method, $type, $data, $metaData = false)
    {
        $events = array();

        if ($this->getSession()->getData(Metrilo_Analytics_Block_Head::DATA_TAG) != '') {
            $events = (array)$this->getSession()->getData(Metrilo_Analytics_Block_Head::DATA_TAG);
        }

        $eventToAdd = array(
            'method' => $method,
            'type' => $type,
            'data' => $data
        );

        if ($metaData) {
            $eventToAdd['metaData'] = $metaData;
        }

        if ($method == 'identify') {
            array_unshift($events, $eventToAdd);
        } else {
            array_push($events, $eventToAdd);
        }

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

        $this->_assignBillingInfo($data, $order);

        if ($order->getCouponCode()) {
            $data['coupons'] = array($order->getCouponCode());
        }
        $skusAdded = array();
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getSku(), $skusAdded)) continue;

            $skusAdded[] = $item->getSku();
            $dataItem = array(
                'id'        => $item->getProductId(),
                'price'     => (float)$item->getPrice() ? $item->getPrice() : $item->getProduct()->getFinalPrice(),
                'name'      => $item->getName(),
                'url'       => $item->getProduct()->getProductUrl(),
                'quantity'  => (int)$item->getQtyOrdered()
            );
            if ($item->getProductType() == 'configurable' || $item->getProductType() == 'grouped') {
                if ($item->getProductType() == 'grouped') {
                    $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($item->getProductId());
                    $parentId = $parentIds[0];
                } else {
                    $parentId = $item->getProductId();
                }
                $mainProduct = Mage::getModel('catalog/product')->load($parentId);
                $dataItem['id']     = $mainProduct->getId();
                $dataItem['name']   = $mainProduct->getName();
                $dataItem['url']    = $mainProduct->getProductUrl();
                $dataItem['option_id'] = $item->getSku();
                $dataItem['option_name'] = trim(str_replace("-", " ", $item->getName()));
                $dataItem['option_price'] = (float)$item->getPrice();
            }
            $data['items'][] = $dataItem;
        }

        return $data;
    }

    /**
     * Create HTTP request to Metrilo API to sync multiple orders
     *
     * @param Array(Mage_Sales_Model_Order) $orders
     * @return void
     */
    public function callBatchApi($storeId, $orders, $async = true)
    {
        try {
            $ordersForSubmition = $this->_buildOrdersForSubmition($orders);
            $call = $this->_buildCall($storeId, $ordersForSubmition);

            $this->_callMetriloApi($storeId, $call, $async);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'Metrilo_Analytics.log');
        }
    }

    // Private functions start here
    private function _callMetriloApi($storeId, $call, $async = true) {
        ksort($call);

        $basedCall = base64_encode(Mage::helper('core')->jsonEncode($call));
        $signature = md5($basedCall.$this->getApiSecret($storeId));

        $requestBody = array(
            's'   => $signature,
            'hs'  => $basedCall
        );

        /** @var Metrilo_Analytics_Helper_Asynchttpclient $asyncHttpHelper */
        $asyncHttpHelper = Mage::helper('metrilo_analytics/asynchttpclient');
        $asyncHttpHelper->post('http://p.metrilo.com/bt', $requestBody, $async);
    }

    /**
     * Create submition ready arrays from Array of Mage_Sales_Model_Order
     * @param Array(Mage_Sales_Model_Order) $orders
     * @return Array of Arrays
     */
    private function _buildOrdersForSubmition($orders) {
        $ordersForSubmition = array();

        foreach ($orders as $order) {
            if ($order->getId()) {
                array_push($ordersForSubmition, $this->_buildOrderForSubmition($order));
            }
        }

        return $ordersForSubmition;
    }

    /**
     * Build event array ready for encoding and encrypting. Built array is returned using ksort.
     *
     * @param  string  $ident
     * @param  string  $event
     * @param  array  $params
     * @param  boolean|array $identityData
     * @param  boolean|int $time
     * @param  boolean|array $callParameters
     * @return void
     */
    private function _buildEventArray($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
        $call = array(
            'event_type'    => $event,
            'params'        => $params,
            'uid'           => $ident
        );

        if($time) {
            $call['time'] = $time;
        }

        $call['server_time'] = round(microtime(true) * 1000);
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
        // Prepare keys is alphabetical order
        ksort($call);

        return $call;
    }

    private function _buildOrderForSubmition($order) {
        $orderDetails = $this->prepareOrderDetails($order);
        // initialize additional params
        $callParameters = false;
        // check if order has customer IP in it
        $ip = $order->getRemoteIp();
        if ($ip) {
            $callParameters = array('use_ip' => $ip);
        }
        // initialize time
        $time = false;
        if ($order->getCreatedAtStoreDate()) {
            $time = $order->getCreatedAtStoreDate()->getTimestamp() * 1000;
        }

        $identityData = $this->_orderIdentityData($order);

        return $this->_buildEventArray(
            $identityData['email'], 'order', $orderDetails, $identityData, $time, $callParameters
        );
    }


    private function _orderIdentityData($order) {
        return array(
            'email'         => $order->getCustomerEmail(),
            'first_name'    => $order->getBillingAddress()->getFirstname(),
            'last_name'     => $order->getBillingAddress()->getLastname(),
            'name'          => $order->getBillingAddress()->getName(),
        );
    }

    private function _buildCall($storeId, $ordersForSubmition) {
        return array(
            'token'    => $this->getApiToken($storeId),
            'events'   => $ordersForSubmition,
            // for debugging/support purposes
            'platform' => 'Magento ' . Mage::getEdition() . ' ' . Mage::getVersion(),
            'version'  => (string)Mage::getConfig()->getModuleConfig("Metrilo_Analytics")->version
        );
    }

    private function _assignBillingInfo(&$data, $order)
    {
        $billingAddress = $order->getBillingAddress();
        # Assign billing data to order data array
        $data['billing_phone']    = $billingAddress->getTelephone();
        $data['billing_country']  = $billingAddress->getCountryId();
        $data['billing_region']   = $billingAddress->getRegion();
        $data['billing_city']     = $billingAddress->getCity();
        $data['billing_postcode'] = $billingAddress->getPostcode();
        $data['billing_address']  = $billingAddress->getStreetFull();
        $data['billing_company']  = $billingAddress->getCompany();
    }
}
