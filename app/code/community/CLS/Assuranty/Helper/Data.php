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

class CLS_Assuranty_Helper_Data extends Mage_Core_Helper_Data
{
    const EXPORT_LIMIT = 100;
    
    const FTP_EXPORT_PROFILE_NAME = 'ASSURINTYglobal Export';
    
    const WARRANTY_PRODUCTS_SKUS = 'cls_assuranty_warranty_skus';
    const ASSURANTY_UPLOAD_URL = 'https://www.assurintyglobal.com/magento/magento_orders.php';
    const NEW_CUSTOMER_EMAIL = 'newcustomer@assurintyglobal.com';

    protected $_manufacturerArray = array();

    /**
     * Get value from core_config_data. Difference between getStoreConfig is that this method does not use cache
     * @param type $name
     * @return id
     */
    public function getConfigValue($name)
    {
        return $this->getConfigRecord($name)->getValue();
    }
    
    /**
     * Get record from core_config_data. Difference between getStoreConfig is that this method does not use cache
     * @param type $name
     * @return object
     */
    public function getConfigRecord($name)
    {
        return Mage::getModel('core/config_data')
            ->getCollection()
            ->addFieldToFilter('scope', 'default')
            ->addFieldToFilter('scope_id', 0)
            ->addFieldToFilter('path', $name)
            ->getFirstItem();
    }

    /**
     * Return manufacturer text by option id
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @return string
     */
    public function getManufacturerText($manufacturerId, $storeId) {
        if (empty($this->_manufacturerArray[$storeId])) {
            $type = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
            if ($type <= 0) {
                return '';
            }
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter(Mage::getModel('eav/entity_attribute')->loadByCode($type, 'manufacturer')->getId())
                ->setStoreFilter($storeId);

            foreach ($optionCollection as $optionId => $option) {
                $this->_manufacturerArray[$storeId][$optionId] = $option->getValue();
            }
        }

        return isset($this->_manufacturerArray[$storeId][$manufacturerId]) ? $this->_manufacturerArray[$storeId][$manufacturerId] : '';
    }

    /**
     * Get export limit for each cron run
     * @return int
     */
    public function getExportLimit()
    {
        return self::EXPORT_LIMIT;
    }
    
    public function getFtpExportProfileName()
    {
        return self::FTP_EXPORT_PROFILE_NAME;
    }
    
    /**
     * Concat of 2 csv files
     * @param type $filename1
     * @param type $filename2
     */
    public function filesMerge($filename1, $filename2)
    {
        $skipFirstLine = false;
        /* Removes header (stores, websites, ..) after the first file */
        if (file_exists($filename1)) {
            $skipFirstLine = true;
        }
        
        $file1 = fopen($filename1, 'a+');
        $file2 = fopen($filename2, 'r');
        
        if ($skipFirstLine) {
            fgets($file2);
        }
        while (!feof($file2)) {
            fwrite($file1, fgets($file2));
        }
        fclose($file1);
        fclose($file2);
    }
    
    public function escapeString($string)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9\-_]/', '', str_replace(' ', '_', $string)));
    }
    
    public function generateFilenameForDomain($nameIdentifier = '')
    {
        return $nameIdentifier . Mage::helper('cls_assuranty/config')->getEscapedRetailerName() . Mage::getModel('core/date')->date('Ymd') . '.csv';
    }
    
    public function getCategoryNamesByIds($categoryIds)
    {
        if (!is_array($categoryIds)) {
            return array();
        }
        
        $categoriesCollection = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToFilter('entity_id', array('in' => $categoryIds));
        
        $categoryNames = array();
        foreach ($categoriesCollection as $category) {
            $categoryNames[] = $category->getName();
        }
        return $categoryNames;
    }
    
    public function addCronJob($code, $model, $cronExpr)
    {
        $this->getConfigRecord('crontab/jobs/cls_assuranty_' . $code . '/run/model')
            ->setValue($model)
            ->setPath('crontab/jobs/cls_assuranty_' . $code . '/run/model')
            ->save();
        
        $this->getConfigRecord('crontab/jobs/cls_assuranty_' . $code . '/schedule/cron_expr')
            ->setValue($cronExpr)
            ->setPath('crontab/jobs/cls_assuranty_' . $code . '/schedule/cron_expr')
            ->save();
        
        Mage::app()->getCacheInstance()->cleanType('config');
    }
    
    public function removeCronJob($code)
    {
        $configRecord = $this->getConfigRecord('crontab/jobs/cls_assuranty_' . $code . '/run/model');
        if ($configRecord->getId()) {
            $configRecord->delete();
        }
        $configRecord = $this->getConfigRecord('crontab/jobs/cls_assuranty_' . $code . '/schedule/cron_expr');
        if ($configRecord->getId()) {
            $configRecord->delete();
        }
        Mage::app()->getCacheInstance()->cleanType('config');
    }
    
    /**
     * Get warranty skus array
     * @return array
     */
    public function getWarrantySkus()
    {
        return explode(',', $this->getConfigValue(self::WARRANTY_PRODUCTS_SKUS));
    }
    
    /**
     * Set warranty skus
     * @param array $skus
     */
    public function setWarrantySkus($skus)
    {
        Mage::getConfig()->saveConfig(self::WARRANTY_PRODUCTS_SKUS, implode(',', $skus));
    }
    
    public function uploadInfo($fromFile, $toFile = null, $toUrl = self::ASSURANTY_UPLOAD_URL)
    {
        $config = array(
            'adapter'      => 'Zend_Http_Client_Adapter_Socket',
            'ssltransport' => 'tls'
        );

        $client = new Zend_Http_Client($toUrl, $config);
        
        if ($toFile) {
            $client->setFileUpload($toFile, 'userfile', file_get_contents($fromFile));
        } else {
            $client->setFileUpload($fromFile, 'userfile');
        }
        
        $client->setParameterPost('upload_sbmt', 'Upload');

        $response = $client->request(Zend_Http_Client::POST);
    }
}
