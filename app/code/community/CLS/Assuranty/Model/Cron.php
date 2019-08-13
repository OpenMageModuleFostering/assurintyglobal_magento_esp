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

class CLS_Assuranty_Model_Cron
{
    const STATUS_READY = 'ready';
    const STATUS_IS_RUNNING = 'running';
    const STATUS_FINISHED = 'finished';
    
    const EXPORT_STATUS = 'cls_assuranty_export_status';
    const EXPORT_OFFSET = 'cls_assuranty_export_offset';
    
    const SIGN_UP_COMPLETE = 'cls_assuranty_sign_up_status';
    
    const ORDERS_EXPORT_LAST_DATE = 'cls_assuranty_orders_export_last_date';
    const ORDERS_LOCAL_CSV_NAME = 'cls_assuranty_orders_export_ftp.csv';
    
    private static $_cronExecutionComplete = false;
    
    /**
     * Check if export can be started. Changes export statuses in DB
     * @return boolean
     */
    protected function _initExport()
    {
        if (self::$_cronExecutionComplete) {
            return false;
        }
        self::$_cronExecutionComplete = true;
        
        if (!Mage::helper('cls_assuranty')->getConfigValue(self::SIGN_UP_COMPLETE)) {
            return false;
        }
        
        if (Mage::helper('cls_assuranty')->getConfigValue(self::EXPORT_STATUS) != self::STATUS_READY) {
            return false;
        }
        
        Mage::getConfig()->saveConfig(self::EXPORT_STATUS, self::STATUS_IS_RUNNING);
        return true;
    }
    
    /**
     * FTP syncronization for all products
     * 
     * @return boolean
     */
    public function ftpProductsExport()
    {
        if (!Mage::helper('cls_assuranty/config')->isEnabled()) {
            return;
        }
        
        if (!$this->_initExport()) {
            return false;
        }
        Mage::getModel('dataflow/profile')
            ->load(Mage::helper('cls_assuranty')->getFtpExportProfileName(), 'name')
            ->run();
        
        if (Mage::helper('cls_assuranty')->getConfigValue(self::EXPORT_STATUS) == self::STATUS_FINISHED) {
            Mage::helper('cls_assuranty')->removeCronJob('ftp_products_export');
        }
    }
    
    public function ftpOrdersExport()
    {
        if (!Mage::helper('cls_assuranty/config')->isEnabled()) {
            return;
        }
        
        $warrantySkus = Mage::helper('cls_assuranty')->getWarrantySkus();
        /* Check if warranty skus exist and all store products were exported */
        if (!count($warrantySkus) || Mage::helper('cls_assuranty')->getConfigValue(self::EXPORT_STATUS) != self::STATUS_FINISHED) {
            return;
        }
        
        $orderItemsCollection = Mage::getModel('sales/order_item')->getCollection();
        if ($ordersExportLastDate = Mage::helper('cls_assuranty')->getConfigValue(self::ORDERS_EXPORT_LAST_DATE)) {
            $orderItemsCollection->addAttributeToFilter('updated_at', array(
                'from' => $ordersExportLastDate,
            ));
        }
        $orderItemsCollection
            ->getSelect()
            ->where('sku IN (?)', $warrantySkus)
            ->group('main_table.order_id');

        if ($orderItemsCollection->getSize()) {
            $assurantyOrdersFile = Mage::getBaseDir() . DS . 'var' . DS . 'export' . DS . self::ORDERS_LOCAL_CSV_NAME;
            @unlink($assurantyOrdersFile);
            $handle = fopen($assurantyOrdersFile, "a");
            $head = array(
                'transaction_number', 'date_of_purchase', 'store_id', 'customer_last_name', 'customer_first_name', 'customer_address',
                'customer_city', 'customer_state', 'customer_zip_code', 'customer_email', 'customer_telephone', 'qty_backordered',
                'qty_canceled', 'qty_invoiced', 'qty_ordered', 'qty_refunded', 'qty_shipped', 'sku', 'base_price', 'product_id',
                'product_sku', 'product_manufacturer', 'product_name', 'product_category_path'
            );
            fputcsv($handle, $head);
            foreach ($orderItemsCollection as $orderItem) {
                $this->_exportOrderItemAndSiblings($orderItem, $handle);
            }

            fclose($handle);
            
            $csvFile = Mage::getBaseDir() . DS . 'var' . DS . 'export' . DS . self::ORDERS_LOCAL_CSV_NAME;
            $toFile = Mage::helper('cls_assuranty')->generateFilenameForDomain('sls_');
            Mage::helper('cls_assuranty')->uploadInfo($csvFile, $toFile);
        }
        Mage::getConfig()->saveConfig(self::ORDERS_EXPORT_LAST_DATE, Mage::getModel('core/date')->gmtDate());
    }
    
    protected function _exportOrderItemAndSiblings($orderItem, $handle) 
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $orderItem->getOrder();
        $orderAddress = $order->getAddressesCollection()->getFirstItem();
        
        foreach ($order->getItemsCollection() as $orderItem) {
            $product = $orderItem->getProduct();
            $data = array(
                'transaction_number' => $order->getIncrementId(),
                'date_of_purchase' => $order->getCreatedAt(),
                'store_id' => $order->getStoreId(),
                'customer_last_name' => $orderAddress->getLastname(),
                'customer_first_name' => $orderAddress->getFirstname(),
                'customer_address' => implode(';', $orderAddress->getStreet()),
                'customer_city' => $orderAddress->getCity(),
                'customer_state' => $orderAddress->getRegion(),
                'customer_zip_code' => $orderAddress->getPostcode(),
                'customer_email' => $order->getCustomerEmail(),
                'customer_telephone' => $orderAddress->getTelephone(),
                'qty_backordered' => $orderItem->getQtyBackordered(),
                'qty_canceled' => $orderItem->getQtyCanceled(),
                'qty_invoiced' => $orderItem->getQtyInvoiced(),
                'qty_ordered' => $orderItem->getQtyOrdered(),
                'qty_refunded' => $orderItem->getQtyRefunded(),
                'qty_shipped' => $orderItem->getQtyShipped(),
                'sku' => $orderItem->getSku(),
                'base_price' => $orderItem->getBasePrice(),
                'product_id' => $product->getId(),
                'product_sku' => $orderItem->getSku(),
                'product_manufacturer' => Mage::helper('cls_assuranty')->getManufacturerText($product->getManufacturer(), $order->getStoreId()),
                'product_name' => $product->getName(),
                'product_category_path' => implode(',', Mage::helper('cls_assuranty')->getCategoryNamesByIds($product->getCategoryIds()))
            );
            fputcsv($handle, $data);
        }
    }
    
    public function productsCreateOnInstall()
    {
        if (!Mage::helper('cls_assuranty/config')->isEnabled()) {
            return;
        }
        
        if (Mage::helper('cls_assuranty/setup')->generateAssurantyProducts()) {
            Mage::getConfig()->saveConfig(self::EXPORT_STATUS, self::STATUS_READY);
            Mage::helper('cls_assuranty')->removeCronJob('products_create_on_install');
        }
    }

}
