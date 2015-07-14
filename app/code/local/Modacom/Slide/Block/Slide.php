<?php
/**
 * @category    Modacom_Slide
 * @author      Miroslav Petrov <miroslav.petrov@modacom.com>
*/
class Modacom_Slide_Block_Slide extends Mage_Core_Block_Template
{
    /**
     * url_key for slide
     * 
     * @var String
     */
    private $_key;

    /**
     * helper
     * 
     * @var Modacom_Slide_Helper_Data
     */
    private $_helper;

    /**
     * Check if key is setted or need to find key depends on current page
     *
     * @return void
     */
    public function __construct()
    {
        if(is_null($this->_key)) {
            $this->_key = $this->_findUrlKey();
        }
        $this->_helper = Mage::helper('modacom_slide');
    }

    /**
     * Check if module is enabled to render slides
     * 
     * @return mixed
     */
    protected function _toHtml()
    {
        if(!$this->_helper->isEnabled() || !$this->_key)
            return "";
        return parent::_toHtml();
    }

    /**
     * Retrieve current landing page if exist
     * 
     * @return Modacom_Slide_Model_Slide
     */
    public function getSlides()
    {
        if(!$this->_key)
            return;
        $slides = Mage::getModel('modacom_slide/slide')->getCollection();
        $slides->addFieldToFilter('url_key', $this->_key);
        $slides->addFieldToFilter('status', 1); // Only enabled slides
        $slides->getSelect()->order('position', 'ASC'); // Sort by position
        return $slides;
    }

    /**
     * Retrieve first slide of the collection
     * @return bool | Modacom_Slide_Model_Slide
     */
    public function getSlide()
    {
        $slides = $this->getSlides();
        if(count($slides)) {
            return $slides->getFirstItem();
        }
        return false;
    }

    /**
     * Finds url key for product category or for article category
     * 
     * @return String|bool
     */
    protected function _findUrlKey()
    {
        $string = Mage::app()->getRequest()->getRequestString();
        if(substr($string, -1) == '/') // if last symbol is "/" then remove it
            return substr($string, 1, -1);
        else
            return substr($string, 1); // always remove first "/" of the string
    }

    /**
     * Set url key for landing page
     * Method used in layout xml files
     * 
     * @param String $key
     */
    public function setUrlKey($key)
    {
        $this->_key = $key;
    }
}