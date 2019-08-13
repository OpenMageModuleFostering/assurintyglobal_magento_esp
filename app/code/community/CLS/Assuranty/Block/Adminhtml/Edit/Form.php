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

class CLS_Assuranty_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/save'), 'method' => 'post'));
        $fieldset = $form->addFieldset('section1', array('legend'=>$this->__('ASSURINTYglobal Account Information')));
        
        $fieldset->addField('website_address', 'text', array(
            'name' => 'website_address',
            'label' => $this->__('Website Address'),
            'title' => $this->__('Website Address'),
            'class' => 'validate-url',
            'required'  => true,
        ));
        
        $fieldset->addField('main_email', 'text', array(
            'name' => 'main_email',
            'label' => $this->__('Email Address (This will be your username)'),
            'title' => $this->__('Email Address (This will be your username)'),
            'class' => 'validate-email',
            'required'  => true,
        ));
        
        $fieldset->addField('password', 'password', array(
            'name' => 'password',
            'label' => $this->__('Password'),
            'title' => $this->__('Password'),
            'class' => 'validate-password',
            'required'  => true,
        ));
        
        $fieldset->addField('confirm_password', 'password', array(
            'name' => 'confirm_password',
            'label' => $this->__('Confirm Password'),
            'title' => $this->__('Confirm Password'),
            'class' => 'validate-cpassword',
            'required'  => true,
        ));
        
        $fieldset = $form->addFieldset('section2', array('legend'=>$this->__('Website Information')));
        
        $fieldset->addField('company_information', 'label', array(
            'value' => $this->__('Company Information'),
            'bold'   => true,
        ));
        
        $fieldset->addField('first_name', 'text', array(
            'name' => 'first_name',
            'label' => $this->__('First Name'),
            'title' => $this->__('First Name'),
            'required'  => true,
        ));
        
        $fieldset->addField('last_name', 'text', array(
            'name' => 'last_name',
            'label' => $this->__('Last Name'),
            'title' => $this->__('Last Name'),
            'required'  => true,
        ));
        
        $fieldset->addField('company_name', 'text', array(
            'name' => 'company_name',
            'label' => $this->__('Company Name'),
            'title' => $this->__('Company Name'),
            'required'  => true,
        ));
        
        $fieldset->addField('dba', 'text', array(
            'name' => 'dba',
            'label' => $this->__('DBA'),
            'title' => $this->__('DBA'),
            'required'  => true,
        ));
        
        $fieldset->addField('company_address', 'text', array(
            'name' => 'company_address',
            'label' => $this->__('Company Address'),
            'title' => $this->__('Company Address'),
            'required'  => true,
        ));
        
        $fieldset->addField('state', 'text', array(
            'name' => 'state',
            'label' => $this->__('State'),
            'title' => $this->__('State'),
            'required'  => true,
        ));
        
        $fieldset->addField('zip_code', 'text', array(
            'name' => 'zip_code',
            'label' => $this->__('ZIP Code'),
            'title' => $this->__('ZIP Code'),
            'required'  => true,
        ));
        
        $fieldset->addField('country', 'text', array(
            'name' => 'country',
            'label' => $this->__('Country'),
            'title' => $this->__('Country'),
            'required'  => true,
        ));
        
        $fieldset->addField('company_telephone', 'text', array(
            'name' => 'company_telephone',
            'label' => $this->__('Company Telephone'),
            'title' => $this->__('Company Telephone'),
            'required'  => true,
        ));
        
        $fieldset->addField('year_founded', 'text', array(
            'name' => 'year_founded',
            'label' => $this->__('Year founded'),
            'title' => $this->__('Year founded'),
            'required'  => true,
        ));
        
        $fieldset->addField('technical_contact', 'label', array(
            'value' => $this->__('Technical Contact'),
            'bold'   => true,
        ));
        
        $fieldset->addField('technical_contact_name', 'text', array(
            'name' => 'technical_contact_name',
            'label' => $this->__('Technical contact name'),
            'title' => $this->__('Technical contact name'),
            'required'  => true,
        ));
        
        $fieldset->addField('technical_contact_number', 'text', array(
            'name' => 'technical_contact_number',
            'label' => $this->__('Telephone number'),
            'title' => $this->__('Telephone number'),
            'required'  => true,
        ));
        
        $fieldset->addField('technical_contact_email', 'text', array(
            'name' => 'technical_contact_email',
            'label' => $this->__('Email address'),
            'title' => $this->__('Email address'),
            'class' => 'validate-email',
            'required'  => true,
        ));
        
        $fieldset->addField('product_information', 'label', array(
            'value' => $this->__('Product Information'),
            'bold'   => true,
        ));
        
        $fieldset->addField('primary_categories_for_sale', 'checkboxes', array(
            'name' => 'primary_categories_for_sale[]',
            'label' => $this->__('Primary categories listed for sale on your website'),
            'title' => $this->__('Primary categories listed for sale on your website'),
            'values' => $this->_getCategoriesArray(),
        ));
        
        $fieldset->addField('seconadary_categories_for_sale', 'checkboxes', array(
            'name' => 'seconadary_categories_for_sale[]',
            'label' => $this->__('Secondary categories listed for sale on your website'),
            'title' => $this->__('Secondary categories listed for sale on your website'),
            'values' => $this->_getCategoriesArray(),
        ));
        
        $fieldset->addField('billing_information', 'label', array(
            'value' => $this->__('Billing Information'),
            'bold'   => true,
        ));
        
        $fieldset->addField('billing_accounts_payable_contact_name', 'text', array(
            'name' => 'billing_accounts_payable_contact_name',
            'label' => $this->__('Accounts payable contact name'),
            'title' => $this->__('Accounts payable contact name'),
            'required'  => true,
        ));
        
        $fieldset->addField('billing_email', 'text', array(
            'name' => 'billing_email',
            'label' => $this->__('Email address'),
            'title' => $this->__('Email address'),
            'class' => 'validate-email',
            'required'  => true,
        ));
        
        $fieldset->addField('billing_telephone', 'text', array(
            'name' => 'billing_telephone',
            'label' => $this->__('Telephone'),
            'title' => $this->__('Telephone'),
            'required'  => true,
        ));
        
        $fieldset->addField('billing_additional', 'note', array(
            'label' => 'billing_additional',
        ))->setRenderer($this->getLayout()->createBlock('cls_assuranty/adminhtml_edit_form_renderer_billingadditional'));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    protected function _getCategoriesArray()
    {
        return array(
            array('value'=>'electronics', 'label'=>'Electronics'),
            array('value'=>'appliances', 'label'=>'Appliances'),
            array('value'=>'jewelry', 'label'=>'Jewelry'),
            array('value'=>'watches', 'label'=>'Watches'),
            array('value'=>'carpets_and_flooring', 'label'=>'Carpets and flooring'),
            array('value'=>'furniture', 'label'=>'Furniture'),
            array('value'=>'toys', 'label'=>'Toys'),
            array('value'=>'apparel', 'label'=>'Apparel'),
            array('value'=>'tools', 'label'=>'Tools'),
            array('value'=>'hardware', 'label'=>'Hardware'),
            array('value'=>'automotive', 'label'=>'Automotive'),
            array('value'=>'food', 'label'=>'Food'),
        );
    }
}