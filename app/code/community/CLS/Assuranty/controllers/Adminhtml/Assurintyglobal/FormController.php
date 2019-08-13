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

class CLS_Assuranty_Adminhtml_Assurintyglobal_FormController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this
            ->loadLayout()
            ->_setActiveMenu('catalog')
            ->_addContent($this->getLayout()->createBlock('cls_assuranty/adminhtml_edit'))
            ->renderLayout();
    }
    
    public function saveAction()
    {
        try {
            $post = Mage::app()->getRequest()->getPost();
            $retailerName = $post['company_name'] . '_' . hash('md5', $post['company_telephone']);
            $post['retailer_name'] = Mage::helper('cls_assuranty')->escapeString($retailerName);
            $this->_sendSignupNotification($post);
            Mage::getConfig()->saveConfig(CLS_Assuranty_Model_Cron::SIGN_UP_COMPLETE, true);
            Mage::getConfig()->saveConfig(CLS_Assuranty_Helper_Config::RETAILER_NAME, $retailerName);
            
            Mage::helper('cls_assuranty/setup')->init();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cls_assuranty')->__("Thank you for selecting the ASSURINTYglobal Extended Warranty Extension.  As part of the integration, Magento is transmitting select data from your product catalogue so that we can map the appropriate warranties to relevant products.  Depending on the size of your catalogue, the process can take several hours up to several days.  We will contact you upon integration and notify you that the program is live.  Note that we are receiving only product IDs, categories and descriptions and no cost or vendor related data will be transmitted.<br /><br />We look forward to working with you."));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cls_assuranty')->__('Error while submitting form, try again later or contact us: %s', CLS_Assuranty_Helper_Data::NEW_CUSTOMER_EMAIL));
        }
        $this->_redirectReferer();
    }
    
    protected function _sendSignupNotification($variables)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        
        if (is_array($variables['primary_categories_for_sale'])) {
            $variables['primary_categories_for_sale'] = implode(',', $variables['primary_categories_for_sale']);
        }
        if (is_array($variables['seconadary_categories_for_sale'])) {
            $variables['seconadary_categories_for_sale'] = implode(',', $variables['seconadary_categories_for_sale']);
        }
        $variables['billing_want_to_be_billed'] = empty($variables['billing_want_to_be_billed']) ? 'no' : 'yes';
        
        Mage::getModel('core/email_template')->sendTransactional(
            Mage::helper('cls_assuranty/config')->getSignUpTemplate(),
            'general',
            CLS_Assuranty_Helper_Data::NEW_CUSTOMER_EMAIL,
           'Admin',
           $variables
        );
        $translate->setTranslateInline(true);
    }
}