<?php
/**
 * Catch events and track them to metrilo api
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Model_Observer
{
    /**
     * Identify customer after login
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $customer = $observer->getEvent()->getCustomer();
        $data = array(
            'id' => $customer->getId(),
            'params' => array(
                'email'         => $customer->getEmail(),
                'name'          => $customer->getName(),
                'first_name'    => $customer->getFirstname(),
                'last_name'     => $customer->getLastname(),
            )
        );
        $helper->addEvent('identify', 'identify', $data);
    }

    /**
     * Track page views
     *   - homepage & CMS pages
     *   - product view pages
     *   - category view pages
     *   - cart view
     *   - checkout
     *   - any other pages (get title from head)
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function trackPageView(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $action = $observer->getEvent()->getAction()->getFullActionName();
        $pageTracked = false;
        // homepage & CMS pages
        if ($action == 'cms_index_index' || $action == 'cms_page_view') {
            $title = Mage::getSingleton('cms/page')->getTitle();
            $helper->addEvent('track', 'pageview', $title);
            $pageTracked = true;
        }
        // category view pages
        if($action == 'catalog_category_view') {
            $category = Mage::registry('current_category');
            $data =  array(
                'id'    =>  $category->getId(), 
                'name'  =>  $category->getName()
            );
            $helper->addEvent('track', 'view_category', $data);
            $pageTracked = true;
        }
        // product view pages
        if ($action == 'catalog_product_view') {
            $product = Mage::registry('current_product');
            $data =  array(
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => number_format($product->getFinalPrice(), 2),
                'url'   => $product->getProductUrl()
            );
            // Additional information ( image and categories )
            if($product->getImage())
                $data['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'image');

            if(count($product->getCategoryIds())) {
                $categories = array();
                $collection = $product->getCategoryCollection()->addAttributeToSelect('*');
                foreach ($collection as $category) {
                    $categories[] = array(
                        'id' => $category->getId(),
                        'name' => $category->getName()
                    );
                }
                $data['categories'] = $categories;
            }
            $helper->addEvent('track', 'view_product', $data);
            $pageTracked = true;
        }
        // cart view
        if($action == 'checkout_cart_index') {
            $helper->addEvent('track', 'view_cart', array());
            $pageTracked = true;
        }
        // checkout
        if ($action != 'checkout_cart_index' && strpos($action, 'checkout') !== false && strpos($action, 'success') === false) {
            $helper->addEvent('track', 'checkout_start', array());
            $pageTracked = true;
        }
        // Any other pages
        if(!$pageTracked) {
            $title = $observer->getEvent()->getLayout()->getBlock('head')->getTitle();
            $helper->addEvent('track', 'pageview', $title);
        }
    }

    /**
     * Event for adding product to cart
     * "checkout_cart_product_add_after"
     *
     * @param Varien_Event_Observer $observer [description]
     */
    public function addToCart(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        /**
         * @var Mage_Sales_Model_Quote_Item
         */
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        $mainProduct = $observer->getProduct();

        $data =  array(
            'id'            => (int)$mainProduct->getId(),
            'price'         => (float)number_format($mainProduct->getFinalPrice(), 2),
            'name'          => $mainProduct->getName(),
            'url'           => $mainProduct->getProductUrl(),
            'quantity'      => $item->getQty()
        );
        // Add options for configurable products
        if ($mainProduct->getId() != $product->getId()) {
            $name = trim(str_replace("-", " ", $item->getName()));
            $data['option_id'] = $item->getSku();
            $data['option_name'] = $name;
            $data['option_price'] = (float)number_format($mainProduct->getFinalPrice(), 2);
        }

        $helper->addEvent('track', 'add_to_cart', $data);
    }

    /**
     * Event for removing item from shopping bag
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function removeFromCart(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();

        $data = array(
            'id' => $product->getId()
        );

        $helper->addEvent('track', 'remove_from_cart', $data);
    }

    /**
     * Track placing a new order from customer
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function trackNewOrder(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $data = array();
        $order = $observer->getOrder();
        if ($order->getId()) {
            $data = $this->_prepareOrderDetails($order);
            $helper->addEvent('track', 'order', $data);
        }
    }

    /**
     * Track adding discount codes in shopping bag
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function trackCoupon(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $code = Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode();
        if (strlen($code)) {
            $helper->addEvent('track', 'applied_coupon', $code);
        }
    }

    /**
     * Send order information after save
     *
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function updateOrder(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $order = $observer->getOrder();
        $orderDetails = $this->_prepareOrderDetails($order);

        $callParameters = false;

        // check if order has customer IP in it
        $ip = $order->getRemoteIp();
        if($ip){
            $callParameters = array('use_ip' => $ip);
        }

        $time = false;

        $identityData = array(
            'email'         => $order->getCustomerEmail(),
            'first_name'    => $order->getBillingAddress()->getFirstname(),
            'last_name'     => $order->getBillingAddress()->getLastname(),
            'name'          => $order->getBillingAddress()->getName(),
        );

        $this->_callApi($identityData['email'], 'order', $orderDetails, $identityData, $time, $callParameters);
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
    private function _callApi($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
        $helper = Mage::helper('metrilo_analytics');
        try {
            $call = array(
                'event_type'    => $event,
                'params'        => $params,
                'uid'           => $ident,
                'token'         => $helper->getApiToken()
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
            $signature = md5($based_call.$helper->getApiSecret());
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

    /**
     * Get order details and sort them for metrilo
     *
     * @param  Mage_Sales_Model_Order $order
     * @return array
     */
    private function _prepareOrderDetails($order)
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
}