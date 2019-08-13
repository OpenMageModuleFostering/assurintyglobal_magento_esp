CLS_Assuranty Module Features
==========================

## Instructions

Instructions assume the retailer has sufficient knowledge of Magento admin and a clean install of Magento Community 1.6 or higher.  This module requires the Magento cron to be set up and running regularly.  It is recommended that the Magento cron run every 5 minutes.  A longer period between cron runs will simply increase the amount of time for initial data submission to ASSURINTYglobal.

## Data Submission

This extension will add a signup form to the Magento Admin panel.  This can be found by going to the menu item located at Catalog -> Warranty.  As a retailer, you will need to fill out and save the form.  The form responses and products will be uploaded to the ASSURINTYglobal system.

After you submit the form, all warranty products will be created in your catalog.  Your product catalog will be exported using a dataflow profile which will be triggered automatically.  If your Magento installation does not have a functioning cron, the dataflow profile may be run manually and the product export submitted manually.

Once products are submitted to ASSURINTYglobal, please allow up to 10 business days for approval of the requested products for ESP warranty coverage.

## Warranty Inclusion on Frontend

Warranty code be available to customers at the cart level as soon as the extension is installed on your Magento site and ASSURINTYglobal turns this on manually.

ASSURINTYglobal will send email communication to confirm approval of products for warranty.  Once ASSURINTYglobal has approved products for warranty, an option to add a warranty below eligible products will appear to customers on the cart page.

Customers will see the ASSURINTYglobal warranty badge when they view their cart, prior to checkout.  Customers have the option to add a warranty or extend a warranty for each product in their cart.  Customers can also click “more” and get more information about the purchase of a warranty.  Once the warranty is added to the cart with the product, customer may proceed to checkout.

## Data Transfer

After initial form submission, a CSV file of products is generated.  Up to 100 products at a time per cron run until all products are exported to the file.  It is then uploaded to a secure form.

An order transfer process runs weekly that processes the order history to find warranty orders and export these orders to a CSV.  The CSV is then uploaded to a secure form.

If technical errors hinder a product from being submitted to ASSURINTYglobal, yet the warranty is still sold to customers, ASSURINTYglobal is not responsible for warranty of that product.