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

class CLS_Assuranty_Helper_Setup
{
    const GENERATE_PRODUCTS_STEP = 5;
    
    /**
     * Assuranty setup init script. Works after assuranty form submit
     */
    public function init()
    {
        $this->_createProductExportProfile();
        $this->_saveWarrantyProductsSkuList();
        Mage::helper('cls_assuranty')->addCronJob('products_create_on_install', 'cls_assuranty/cron::productsCreateOnInstall', '*/5 * * * *');
        Mage::helper('cls_assuranty')->addCronJob('ftp_products_export', 'cls_assuranty/cron::ftpProductsExport', '*/5 * * * *');
    }
    
    protected function _saveWarrantyProductsSkuList()
    {
        $assurantySkusFile = Mage::getBaseDir() . DS . 'var' . DS . 'cls_assuranty' . DS . 'sku_assuranty.csv';
        $handle = fopen($assurantySkusFile, "r");
        if ($handle === false) {
            Mage::throwException('Cannot find assuranty products csv');
        }
        
        $head = fgetcsv($handle, 1000, ",");
        if ($head !== false) {
            $head = array_map('strtolower', array_map('trim', $head));
        } else {
            Mage::throwException('Incorect assuranty products csv');
        }
        
        $skus = array();
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $productData = array_combine($head, array_map('trim', $data));
            $skus[] = $productData['sku'];
        }
        Mage::helper('cls_assuranty')->setWarrantySkus($skus);
    }
    
    protected function _createProductExportProfile()
    {
        $exportProfile = Mage::getModel('dataflow/profile')->load(Mage::helper('cls_assuranty')->getFtpExportProfileName(), 'name');
        if ($exportProfile->getId()) {
            return;
        }
        
        $actionsXml = '
            <action type="cls_assuranty/convert_adapter_product" method="load">
                <var name="store"><![CDATA[0]]></var>
            </action>

            <action type="cls_assuranty/convert_parser_product" method="unparse">
                <var name="store"><![CDATA[0]]></var>
            </action>

            <action type="dataflow/convert_mapper_column" method="map">
            </action>

            <action type="dataflow/convert_parser_csv" method="unparse">
                <var name="delimiter"><![CDATA[,]]></var>
                <var name="enclose"><![CDATA["]]></var>
                <var name="fieldnames">true</var>
            </action>

            <action type="cls_assuranty/convert_adapter_io" method="save">
                <var name="type">file</var>
                <var name="path">var/export</var>
                <var name="filename"><![CDATA[cls_assuranty_ftp_export_all_products.csv]]></var>
            </action> 
        ';

        $exportProfile
            ->setName(Mage::helper('cls_assuranty')->getFtpExportProfileName())
            ->setActionsXml($actionsXml)
            ->save();
    }
    
    /**
     * Generate Assuranty warranty products from csv
     */
    public function generateAssurantyProducts($amountToGenerate = self::GENERATE_PRODUCTS_STEP)
    {
        $assurantySkusFile = Mage::getBaseDir() . DS . 'var' . DS . 'cls_assuranty' . DS . 'sku_assuranty.csv';
        $handle = fopen($assurantySkusFile, "r");
        if ($handle === false) {
            Mage::throwException('Cannot find assuranty products csv');
        }
        
        $head = fgetcsv($handle, 1000, ",");
        if ($head !== false) {
            $head = array_map('strtolower', array_map('trim', $head));
        } else {
            Mage::throwException('Incorect assuranty products csv');
        }
        
        $importFinished = true;
        $attributeSetId = $this->_generateAssurantyAttributeSet();
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($amountToGenerate <= 0) {
                $importFinished = false;
                break;
            }
            $productData = array_combine($head, array_map('trim', $data));
            if (Mage::getModel('catalog/product')->getIdBySku($productData['sku'])) {
                continue;
            }
            $this->_generateProduct($productData, $attributeSetId);
            $amountToGenerate--;
        }
        
        $indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
        foreach ($indexingProcesses as $process) {
              $process->reindexEverything();
        }
        
        fclose($handle);
        
        return $importFinished;
    }
    
    /**
     * Generate Assuranty attribute set
     * @return type
     */
    protected function _generateAssurantyAttributeSet()
    {
        $attibuteSetId = $this->_getAssurantyAttributeSet()->getId();
        if (!$attibuteSetId) {
            $defaultAttributeSetId = Mage::getSingleton('eav/config')
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
                ->getDefaultAttributeSetId();

            $attibuteSet = Mage::getModel('eav/entity_attribute_set')
                ->setEntityTypeId(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->setAttributeSetName('Assuranty')
                ->save();
            $attibuteSet
                ->initFromSkeleton($defaultAttributeSetId)
                ->save();
            
            $attibuteSetId = $attibuteSet->getId();
        }
        return $attibuteSetId;
    }
    
    protected function _getAssurantyAttributeSet()
    {
        return Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->addFieldToFilter('attribute_set_name', 'Assuranty')
            ->addFieldToFilter('entity_type_id', Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->getFirstItem();
    }
    
    /**
     * Generate product based on data and attribute set
     * @param type $data
     * @param type $attributeSetId
     */
    protected function _generateProduct($data, $attributeSetId)
    {
        $allWebsiteIds = array();
        foreach (Mage::app()->getWebsites(true) as $website) {
            $allWebsiteIds[] = $website->getId();
        }
        
        $product = Mage::getModel('catalog/product')
            ->setAttributeSetId($attributeSetId)
            ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL)
            ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG)
            ->setWebsiteIds($allWebsiteIds)
            ->setTaxClassId(0)
            ->setSku($data['sku'])
            ->setName($data['item'])
            ->setShortDescription($data['description'])
            ->setDescription($data['description'])
            ->setCost($data['retailer cost'])
            ->setPrice($data['retail price'])
            ->setStockData(
                array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 0,
                )
            );
        
        $product->setMediaGallery(array('images' => array(), 'values' => array()));
        $filePath = Mage::getBaseDir('media') . DS . 'import' . DS . 'assurintygrey.jpg';
        if ( file_exists($filePath) ) {
            try {
                $product->addImageToMediaGallery($filePath, array ('image', 'small_image', 'thumbnail'), false, false);
            } catch (Exception $e) {
            }
        }
        $product->save();
        
        /* Stock re-save because of bugs in some Magento versions */
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        if ($stockItem->getId()) {
            $stockItem
                ->setData('use_config_manage_stock', 0)
                ->setData('manage_stock', 0)
                ->save();
            
        }
    }
}
