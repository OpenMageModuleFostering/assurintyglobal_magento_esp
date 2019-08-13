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

class CLS_Assuranty_Model_Convert_Adapter_Io extends Mage_Dataflow_Model_Convert_Adapter_Io
{
    /**
     * Saves csv file with product data. May append data to existing csv and upload ready csv by FTP
     * @return \CLS_Assuranty_Model_Convert_Adapter_Io
     */
    public function save()
    {
        if (!$this->getResource(true)) {
            return $this;
        }
        
        $helper = Mage::helper('cls_assuranty');

        if ($helper->getConfigValue(CLS_Assuranty_Model_Cron::EXPORT_STATUS) == CLS_Assuranty_Model_Cron::STATUS_FINISHED) {
            $csvFile = Mage::getBaseDir() . DS . trim($this->getVar('path')) . DS . $this->getVar('filename');
            $toFile = Mage::helper('cls_assuranty')->generateFilenameForDomain('pro_');
            Mage::helper('cls_assuranty')->uploadInfo($csvFile, $toFile);
        } else {
            $batchModel = Mage::getSingleton('dataflow/batch');

            $dataFile = $batchModel->getIoAdapter()->getFile(true);

            $filename = $this->getVar('filename');

            $mainFilename = Mage::getBaseDir() . DS . trim($this->getVar('path')) . DS . $filename;
            $helper->filesMerge($mainFilename, $dataFile);

            $message = Mage::helper('dataflow')->__('Saved successfully: "%s" [%d byte(s)].', $filename, $batchModel->getIoAdapter()->getFileSize());
            if ($this->getVar('link')) {
                $message .= Mage::helper('dataflow')->__('<a href="%s" target="_blank">Link</a>', $this->getVar('link'));
            }
            $this->addException($message);
            
            Mage::getConfig()->saveConfig(CLS_Assuranty_Model_Cron::EXPORT_OFFSET, $helper->getConfigValue(CLS_Assuranty_Model_Cron::EXPORT_OFFSET) + $helper->getExportLimit());
            Mage::getConfig()->saveConfig(CLS_Assuranty_Model_Cron::EXPORT_STATUS, CLS_Assuranty_Model_Cron::STATUS_READY);
        }
        return $this;
    }
}
