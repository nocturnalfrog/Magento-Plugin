<?php
/**
 * Catch events and track them to Metrilo API
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 * @author Zhivko Draganov <zhivko@metrilo.com>
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
            'id' => $customer->getEmail(),
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
        $action = (string)$observer->getEvent()->getAction()->getFullActionName();

        if ($this->_isRejected($action)) {
            return;
        }
        // Those 2 lines are checking for AJAX events
        // if ($observer->getEvent()->getAction()->getRequest()->isAjax()) {
        //     return;
        // }

        // Catalog search pages
        if ($action == 'catalogsearch_result_index') {
            $query = Mage::helper('catalogsearch')->getQuery();
            if ($text = $query->getQueryText()) {
                $resultCount = Mage::app()->getLayout()->getBlock('search.result')->getResultCount();
                $params = array(
                    'query' => $text,
                    'result_count' => $resultCount
                );
                $helper->addEvent('track', 'search', $params);
                return;
            }
        }
        // Fishpigs_Attribute_Splash_Page integration plugin
        if ($action == 'attributeSplash_page_view') {
            $this->_viewSplashPage($helper);
            return;
        }

        // homepage & CMS pages
        if ($action == 'cms_index_index' || $action == 'cms_page_view') {
            $title = Mage::getSingleton('cms/page')->getTitle();
            $helper->addEvent('track', 'pageview', $title, array('backend_hook' => $action));
            return;
        }
        // category view pages
        if ($action == 'catalog_category_view') {
            $category = Mage::registry('current_category');
            $data =  array(
                'id'    =>  $category->getId(),
                'name'  =>  $category->getName()
            );
            $helper->addEvent('track', 'view_category', $data);
            return;
        }
        // product view pages
        if ($action == 'catalog_product_view') {
            $product = Mage::registry('current_product');
            $data =  array(
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => $product->getFinalPrice(),
                'url'   => $product->getProductUrl()
            );
            // Additional information ( image and categories )
            if ($product->getImage())
                $data['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'image');

            if (count($product->getCategoryIds())) {
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
            return;
        }
        // cart view
        if ($action == 'checkout_cart_index') {
            $helper->addEvent('track', 'view_cart', array());
            return;
        }
        // checkout
        if ($action != 'checkout_cart_index' && strpos($action, 'checkout') !== false && strpos($action, 'success') === false && strpos($action, 'add') === false) {
            $helper->addEvent('track', 'checkout_start', array());
            return;
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
        /**
         * @var Mage_Sales_Model_Quote_Item
         */
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        $cartProduct = $observer->getProduct();

        if ($cartProduct->isGrouped()) {
            $options = Mage::app()->getRequest()->getParam('super_group');
            if (is_array($options)) {
                foreach ($options as $productId => $qty) {
                    $this->_addToCart((int)$productId, $cartProduct, (int)$qty);
                }
            }
        } elseif($cartProduct->isConfigurable()) {
            $this->_addToCart($product->getId(), $cartProduct, $item->getQty());
        } else {
            $this->_addToCart($cartProduct->getId(), $cartProduct, $item->getQty());
        }

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
            $data = $helper->prepareOrderDetails($order);
            if($order->getCustomerIsGuest()) {
                $identify = array(
                    'id' => $order->getCustomerEmail(),
                    'params' => array(
                        'email'         => $order->getCustomerEmail(),
                        'name'          => $order->getCustomerFirstname(). ' '. $order->getCustomerLastname(),
                        'first_name'    => $order->getCustomerFirstname(),
                        'last_name'     => $order->getCustomerLastname(),
                    )
                );
                $helper->addEvent('identify', 'identify', $identify);
            }
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
        $helper->callApi($observer->getOrder(), true);
    }

    /**
    * Events that we don't want to track
    *
    * @param string event
    */
    private function _isRejected($event)
    {
        return in_array(
            $event,
            array('catalogsearch_advanced_result', 'catalogsearch_advanced_index')
        );
    }

    /**
     * Add to cart event
     *
     * @param integer $productId
     * @param Mage_Catalog_Model_Product  $item
     * @param integer $qty
     */
    private function _addToCart($productId, $item, $qty)
    {
        $helper = Mage::helper('metrilo_analytics');
        $product = Mage::getModel('catalog/product')->load($productId);

        $data =  array(
            'id'            => (int)$product->getId(),
            'price'         => (float)$product->getFinalPrice(),
            'name'          => $product->getName(),
            'url'           => $product->getProductUrl(),
            'quantity'      => $qty
        );

        // Add options for grouped or configurable products
        if ($item->isGrouped() || $item->isConfigurable()) {
            $data['id']     = $item->getId();
            $data['name']   = $item->getName();
            $data['url']    = $item->getProductUrl();
            // Options
            $data['option_id'] = $product->getSku();
            $data['option_name'] = trim(str_replace("-", " ", $product->getName()));
            $data['option_price'] = (float)$product->getFinalPrice();
        }

        $helper->addEvent('track', 'add_to_cart', $data);
    }

    /**
    * Splash page event
    */
    private function _viewSplashPage($helper)
    {
        $splashPage = Mage::registry('splash_page');

        if (is_null($splashPage) || !$splashPage->canDisplay()) {
            return false;
        }

        $data =  array(
            'id'    =>  'SPL-'.$splashPage->getId(),
            'name'  =>  $splashPage->getName()
        );
        $helper->addEvent('track', 'view_category', $data);
    }
}
