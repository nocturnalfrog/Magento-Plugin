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
        if ($action == 'cms_index_index' || $action == 'cms_page_view')
        {
            $title = Mage::getSingleton('cms/page')->getTitle();
            $helper->addEvent('track', 'pageview', $title);
            $pageTracked = true;
        }
        // category view pages
        if($action == 'catalog_category_view')
        {
            $category = Mage::registry('current_category');
            $data =  array(
                'id'    =>  $category->getId(), 
                'name'  =>  $category->getName()
            );
            $helper->addEvent('track', 'view_category', $data);
            $pageTracked = true;
        }
        // product view pages
        if ($action == 'catalog_product_view')
        {
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
        if($action == 'checkout_cart_index')
        {
            $helper->addEvent('track', 'view_cart', array());
            $pageTracked = true;
        }
        // checkout
        if ($action != 'checkout_cart_index' && strpos($action, 'checkout') !== false)
        {
            $helper->addEvent('track', 'checkout_start', array());
            $pageTracked = true;
        }
        // Any other pages
        if(!$pageTracked)
        {
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

}