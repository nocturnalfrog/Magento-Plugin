<?php
/**
* Slides collection
*
* @author Miroslav Petrov <miroslav.petrov@modacom.com>
*/
class Modacom_Slide_Model_Resource_Slide_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
    * Define collection model
    */
    protected function _construct()
    {
        $this->_init('modacom_slide/slide');
    }

}