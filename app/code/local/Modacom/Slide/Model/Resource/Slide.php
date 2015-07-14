<?php 
/**
 * Resource model for slides
 * 
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Model_Resource_Slide extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Define table and unique key
     * @return void
     */
    public function _construct()
    {    
        $this->_init('modacom_slide/slide', 'slide_id');
    }
}