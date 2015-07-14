<?php 
/**
 * Create tabs in left navigation in edit page
 * 
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Block_Adminhtml_Slide_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Set tabs title and other settings
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('slides_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('modacom_slide')->__('Slide Information'));
    }

    /**
     * Add block tabs
     * 
     * @return Mage_Adminhtml_Block_Widget_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('modacom_slide')->__('Item Information'),
            'title'     => Mage::helper('modacom_slide')->__('Item Information'),
            'content'   => $this->getLayout()->createBlock('modacom_slide/adminhtml_slide_edit_tab_form')->toHtml(),
            )
        );
        
        return parent::_beforeToHtml();
    }
}