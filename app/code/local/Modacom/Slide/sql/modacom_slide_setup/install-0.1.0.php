<?php
/**
* Slides install table
*
* @author Miroslav Petrov <miroslav.petrov@modacom.com>
*/

/**
* @var $installer Mage_Core_Model_Resource_Setup
*/
$installer = $this;

/**
* Creating table modacom_slide
*/
$table = $installer->getConnection()
    ->newTable($installer->getTable('modacom_slide/slide'))
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Entity id')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
        ), 'Title')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
        ), 'Status')
    ->addColumn('url_key', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        ), 'URL Key')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
        'default' => null,
        ), 'Image')
    ->addColumn('url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        ), 'Url')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
        'default' => null,
        ), 'Position')
    ->addColumn('new_window', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0'), 'Open in new window')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array(
            'nullable' => true,
            'default' => null,
            ), 'Creation Time');
$installer->getConnection()->createTable($table);