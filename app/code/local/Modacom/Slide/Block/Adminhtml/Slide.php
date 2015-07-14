<?php 
/**
 * Main Slides block for listing all slides
 * "Add Button" for creating new slides
 * 
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Block_Adminhtml_Slide extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Init data
     *
     * @return Mage_Adminhtml_Block_Widget_Grid_Container
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_slide';
        $this->_blockGroup = 'modacom_slide'; // block group from config.xml
        $this->_headerText = Mage::helper('modacom_slide')->__('Slides');
        $this->_addButtonLabel = Mage::helper('modacom_slide')->__('Add Slide');
        parent::__construct();
    }
}