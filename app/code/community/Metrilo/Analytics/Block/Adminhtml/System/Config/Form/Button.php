<?php
/**
 * Button widget class
 * Add import model and render button view
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('metrilo/system/config/button.phtml');
    }

    /**
     * Get import instance
     *
     * @return Metrilo_Analytics_Model_Import
     */
    public function getImport()
    {
        return Mage::getSingleton('metrilo_analytics/import');
    }

    /**
     * Get import instance
     *
     * @return boolean
     */
    public function showInStore()
    {
        return Mage::app()->getRequest()->getParam('store');
    }


    /**
     * Get import instance
     *
     * @return boolean
     */
    public function buttonEnabled()
    {
        $helper = Mage::helper('metrilo_analytics');

        $request = Mage::app()->getRequest();
        $storeId = $helper->getStoreId($request);

         return $helper->isEnabled($storeId) &&
            $helper->getApiToken($storeId) && $helper->getApiSecret($storeId);
    }

    /**
    * Return element html
    *
    * @param  Varien_Data_Form_Element_Abstract $element
    * @return string
    */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
    * Return ajax url for button
    *
    * @return string
    */
    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl("metrilo_analytics/adminhtml_ajax", array('isAjax'=> true));
    }

    /**
    * Generate button html
    *
    * @return string
    */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
                       ->createBlock('adminhtml/widget_button')
                       ->setData(array(
                           'id'        => 'metrilo_button',
                           'label'     => $this->helper('adminhtml')->__('Import orders'),
                           'onclick'   => 'javascript:import_metrilo(); return false;'
                       ));

        return $button->toHtml();
    }
}
