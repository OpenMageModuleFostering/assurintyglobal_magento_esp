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

class CLS_Assuranty_Helper_Config
{
    const IS_ENABLED = 'cls_assuranty/general/enabled';
    const RETAILER_NAME = 'cls_assuranty/general/retailer_name';
    const SIGN_UP_TEMPLATE = 'cls_assuranty/general/sign_up';
    
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::IS_ENABLED);
    }
    
    public function getRetailerName()
    {
        return Mage::getStoreConfig(self::RETAILER_NAME);
    }
    
    public function getEscapedRetailerName()
    {
        return Mage::helper('cls_assuranty')->escapeString($this->getRetailerName());
    }
    
    public function getSignUpTemplate()
    {
        return Mage::getStoreConfig(self::SIGN_UP_TEMPLATE);
    }
}
