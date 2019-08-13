<?php
/**
 * Classy Llama
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email to us at
 * support+paypal@classyllama.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future. If you require customizations of this
 * module for your needs, please write us at sales@classyllama.com.
 *
 * To report bugs or issues with this module, please email support+paypal@classyllama.com.
 *
 * @category   CLS
 * @package    Assuranty
 * @copyright  Copyright (c) 2014 Classy Llama Studios, LLC (http://www.classyllama.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class CLS_Assuranty_Model_Convert_Adapter_Product extends Mage_Catalog_Model_Convert_Adapter_Product
{
    /**
     * Loads products for export, set finished status if nothing to export more
     * @param type $entityType
     * @return type
     */
    protected function _getCollectionForLoad($entityType)
    {
        $collection = 
            Mage::getModel('cls_assuranty/catalog_product_collection')
                ->setStoreId($this->getStoreId())
                ->addStoreFilter($this->getStoreId());
        if ($collection->getSize() < Mage::helper('cls_assuranty')->getConfigValue(CLS_Assuranty_Model_Cron::EXPORT_OFFSET)) {
            Mage::getConfig()->saveConfig(CLS_Assuranty_Model_Cron::EXPORT_STATUS, CLS_Assuranty_Model_Cron::STATUS_FINISHED);
        }
        return $collection;
    }
}
