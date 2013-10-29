# PayU-Integration-Kit-Cs-Cart

PayU-Integration-Kit-Cs-Cart is used in CS-Cart PHP E-Commerce Application 

This library provides support for payment gateway PayU by iBiBo Group. (See [PayU.in](http://payu.in/))

# Installation

Copy the files into below mentioned directory

	/skin/basic/customer/views/orders/components/payments/payu.tpl
	/skin/basic/admin/views/payments/components/cc_processor/payu.tpl 
	/payments/PayU.php 

Run the below SQL Commands in your CS-Cart Database

cscart_payment_processors table

	INSERT INTO `cscart_payment_processors` (`processor_id`, `processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`) VALUES (82, 'PayU', 'PayU.php', 'cc_outside.tpl', 'payu.tpl', 'N', 'P');

cscart_payments table

	INSERT INTO `cscart_payments` (`payment_id`, `usergroup_ids`, `position`, `status`, `template`, `processor_id`, `params`, `a_surcharge`, `p_surcharge`, `localization`) VALUES (12, '0', 0, 'A', 'cc_outside.tpl', 68, 'a:6:{s:7:"account";s:6:"C0Dr8m";s:4:"salt";s:8:"3sf0jURk";s:9:"item_name";s:4:"PayU";s:8:"currency";s:3:"USD";s:4:"mode";s:4:"test";s:12:"order_prefix";s:0:"";}', '0.000', '0.000', '');

cscart_payment_descriptions table

	INSERT INTO `cscart_payment_descriptions` (`payment_id`, `payment`, `description`, `lang_code`) VALUES (12,'PayU Checkout','PayU','EN');

You will get the PayU payment gateway options in the admin side

In the admin side, you need to change the configuration of PayU under Administration->paymentmethods

Under configure section in the Account & Salt field – you need to pass your PayU Account-Id (Merchant Key) and SALT

# Configuration

See Installation section

# Author(s)

Ashok Vishwakarma ( avishwakarma at payu dot in )

# Copyright

Copyright (c) 2013 PayU India

# MIT License
Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
