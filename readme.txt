=== Doliconnect ===

Contributors: ptibogxiv, audrasjb
Tags: erp, crm, dolibarr, connector
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 10.6.0
Requires PHP: 8.1
License: GPL v3 or later
Donate link: https://ptibogxiv.eu

This plugin interfaces your Dolibarr installation with a customer interface in WordPress.

== Description ==

Use this plugin to greatly improve relations with your customers. No WooCommerce or other e-shop is needed!

This module only support Dolibarr's internal plugins and options. For more support, you will need addons.

If you like the plugin, feel free to rate it (on the right side of this page)!

You can test it live on [https://ptibogxiv.dev](https://ptibogxiv.dev)

== Installation ==

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to "Doliconnect", follow the instructions, and complete the settings.

Dolibarr configuration steps:

1. Activate the REST API for Dolibarr: go to menu _Home > Setup > Modules/Applications_ and activate the _API/Web services (REST server)_.
2. Generate and copy a specific user API key with full/admin rights: go to menu _Home > Users & groups_, select your admin user in the list, click _Modify_, generate and copy the _Key for API_ (see screenshot), then click _Save_.
3. Your Dolibarr installation needs the Doliconnector module, which can be freely downloaded from Dolistore or GitHub: [GitHub](https://github.com/ptibogxiv/doliconnector/releases)

WordPress configuration steps:
1. Go to WordPress Admin > Settings > Doliconnect
2. BE CAREFUL: in a multisite blog, there is also a network settings page for keys and general settings!

== Frequently Asked Questions ==

1. From Dolibarr 13.x, Doliconnect supports Dolibarr permissions for viewing, editing, and deleting data via WordPress.
2. Using an old or outdated Dolibarr version may cause errors or reduced functionality. Please check compatibility!
3. Always delete WordPress transients after changing settings in Dolibarr. All data are cached using transients in WordPress.

= Dolibarr compatibility =

Doliconnect v10.x -> Dolibarr >19.x
Doliconnect v9.x -> Dolibarr >17.x
Doliconnect v8.x -> Dolibarr >15.x
Doliconnect v6.7 -> Dolibarr >13.x
Doliconnect v6.x -> Dolibarr >12.x
Doliconnect v5.x -> Dolibarr >11.x
Doliconnect v4.x -> Dolibarr >11.x
Doliconnect v3.x -> Dolibarr 10.x

= REST API compatibility =

In some cases, the REST API with Dolibarr needs extra settings to work. Please read this:
https://github.com/Dolibarr/dolibarr/pull/12089

== Screenshots ==

1. Shortcode integration.
2. Front-end form feature.
3. Front-end form request success.
4. Confirmation email sent to the user/visitor.

== Credits ==

We would like to thank these contributors and plugins:

[gdpr-data-request-form](https://wordpress.org/plugins/gdpr-data-request-form/ "gdpr-data-request-form")

== Changelog ==
   = 10.x.y =
* Better UX/UI
* Upgrade Dolibarr version
* Fix PHP bugs and errors
* Please note that a bug can block updating. You need to rollback or uninstall this plugin before a new install
* Rebuild and more powerful plugin

   = 9.6.x =
* Better UX/UI
* Upgrade Dolibarr version
* Fix PHP bugs and errors

   = 9.5.x =
* Better UX/UI
* Upgrade Dolibarr version
* More customization with child theme template
* Fix PHP bugs and errors

   = 9.4.x =
* Better UX/UI
* Fix vulnerability
* Upgrade library
* Step for more WordPress legacy template with Doliconnect
* Fix PHP bugs and errors

   = 9.3.x =
* Better UX/UI
* Upgrade library
* Less dependence on the Doliconnector module
* First beta step for more WordPress legacy templates with Doliconnect
* Fix PHP bugs and errors

   = 9.2.x =
* Better UX/UI
* Upgrade library
* Less dependence on the Doliconnector module
* Fix PHP bugs and errors

   = 9.1.x =
* Better UX/UI
* Better support for related products
* Fix PHP bugs and errors

   = 9.0.x =
* Better UX/UI
* Fix PHP bugs and errors

   = 8.11.x =
* Better UX/UI
* Better shop with negative price
* Fix PHP bugs and errors

   = 8.10.x =
* Better UX/UI
* Better support for agenda events
* Better cart
* Better shop
* Better transients
* Better captcha
* Better multilingual support with Polylang & WPML
* Fix PHP bugs and errors

   = 8.9.x =
* Better UX/UI
* Better support for agenda events
* Better membership subscription
* Fix PHP bugs and errors

   = 8.8.x =
* Better UX/UI
* Support agenda events
* Better admin and multilingual support
* Fix PHP bugs and errors

   = 8.7.x =
* Better UX/UI
* Fix PHP bugs and errors

   = 8.6.x =
* Support of expense report
* Fix PHP bugs and errors

   = 8.5.x =
* Better account and support of Dolibarr v20 & v21
* Fix PHP bugs and errors

   = 8.4.x =
* Better cart/wishlist button and debug
* Fix PHP bugs and errors

   = 8.3.x =
* Better cart/wishlist button and debug
* Fix PHP bugs and errors

   = 8.2.x =
* Better e-shop
* Fix PHP bugs and errors

   = 8.1.x =
* New pagination with new Dolibarr API
* Fix PHP bugs and errors

   = 8.0.x =
* Upgrade Bootstrap 5.3.3
* Fix PHP bugs and errors
* Support Dolibarr v19 & v20

  = 7.8.x =
* Fix PHP errors
* Upgrade libraries
* Support related products

   = 7.7.x =
* Fix PHP errors
* Wishlist and member-linked functionalities

   = 7.6.x =
* Better e-shop and modal info

   = 7.5.x =
* Better e-shop

   = 7.4.0 =
* Better e-shop

   = 7.3.0 =
* Bugs and fixes for PHP 8
* Better e-shop

   = 7.2.x =
* Bugs and fixes
* Better online shop
* Upgrade Bootstrap

   = 7.0.x =
* Bugs and fixes
* Better password change fixes and sync with Dolibarr
* Use Dolibarr password rules

   = 6.9.0 =
* Bugs and fixes
* Fix password changes and sync with Dolibarr
* Use Dolibarr password rules

   = 6.8.x =
* Bugs and fixes
* Better and more AJAX forms
* Better captcha

   = 6.7.x =
* Fix password changes
* Compatibility with PHP 8 and Dolibarr 16/17
* Update libraries

   = 6.6.3 =
* Bugs and fixes
* Fix PHP errors

   = 6.6.2 =
* Bugs and fixes

   = 6.6.1 =
* Bugs and fixes

   = 6.6.0 =
* Bugs and fixes
* Upgrade libraries
* Better stock management

   = 6.5.4 =
* Bugs and fixes
* Fix forgot password form

   = 6.5.3 =
* Bugs and fixes

   = 6.5.2 =
* Bugs and fixes
* Fix translation

   = 6.5.1 =
* Bugs and fixes for payment

   = 6.5.0 =
* Bugs and fixes
* New price code
* Better AJAX

   = 6.4.0 =
* Bugs and fixes
* Fix PHP and Apache errors

   = 6.3.1 =
* Bugs and fixes

   = 6.3.0 =
* Support for Dolibarr v15.0.1
* Bugfixes

   = 6.2.1 =
* Better support for Dolibarr v15

   = 6.2.0 =
* Better support for Dolibarr v15
* Bugs and fixes
* Fix PHP 8 bugs

   = 6.1.2 =
* Better support for Dolibarr v15
* Bugs and fixes

   = 6.1.1 =
* Better support for Dolibarr v15
* Bugs and fixes
* More AJAX forms

   = 6.1.0 =
* Better support for Dolibarr v15
* Bugs and fixes
* More AJAX forms
* More new captcha on forms

   = 6.0.2 =
* Bugs and fixes
* New captcha on forgot password page

   = 6.0.1 =
* Bugs and fixes

   = 6.0.0 =
* Support Dolibarr 15
* New captcha
* Bugs and fixes

   = 5.6.6 =
* Support Dolibarr 14.0.4

   = 5.6.5 =
* Upgrade flat icon
* Debug template

   = 5.6.4 =
* Upgrade Bootstrap to 5.1.2

   = 5.6.2 =
* Fix login/logout URL with SecuPress
* Better membership module

   = 5.6.1 =
* Fix login/logout URL

   = 5.6.0 =
* Support Dolibarr rights. Be careful to set the correct rights!
* Better support for alternative names or URLs for wp-login.php

   = 5.5.3 =
* Fix membership and PHP 8 errors

   = 5.5.2 =
* Fix membership and PHP 8 errors

   = 5.5.1 =
* Cart with AJAX and offcanvas

   = 5.5.0 =
* Upgrade Bootstrap
* AJAX for membership

   = 5.4.2 =
* Fixes and bugfixes for membership

   = 5.4.1 =
* Fixes and bugfixes

   = 5.4.0 =
* Fixes and bugfixes
* Fix languages
* Upgrade Bootstrap

   = 5.3.3 =
* Fixes and bugfixes
* Fix languages
* Upgrade Bootstrap

   = 5.3.2 =
* Fixes and bugfixes

   = 5.3.1 =
* Support Dolibarr 13.0.2

   = 5.3.0 =
* Optimization and fixes
* Add constant

   = 5.2.7 =
* Better home/return button

   = 5.2.6 =
* Fix avatar form

   = 5.2.5 =
* Fix payment method list

   = 5.2.4 =
* Fix membership

   = 5.2.3 =
* Fix membership

   = 5.2.2 =
* Fix membership

   = 5.2.1 =
* Fix PDF download if in another entity

   = 5.2.0 =
* Enable management of membership in the free version
* Todo: more AJAX forms

   = 5.1.0 =
* New public release with Bootstrap 5 and support for Dolibarr 13
* Please note the end of support for DoliconnectPRO; all functionality will be open in the free version.

   = 5.0.6 =
* Fixes and bugfixes

   = 5.0.5 =
* Fixes and bugfixes
* Protect contact form

   = 5.0.4 =
* Fixes and bugfixes
* Block using Internet Explorer

   = 5.0.4 =
* Fixes and bugfixes
* New shipping API

   = 5.0.3 =
* Fixes and bugfixes

   = 5.0.2 =
* Upgrade Bootswatch to 4.6.0
* Fix template and form

  = 5.0.1 =
* Fix critical bug

  = 5.0.0 =
* Compatibility with Dolibarr 13
* New workflow
* Support Bootstrap 5
* Better code
* Lots of bugfixes

  = 4.11.4 =
* Better search

  = 4.11.3 =
* Upgrade Bootswatch

  = 4.11.2 =
* Fix add to cart button

  = 4.11.0 =
* New: add option for displaying invoices in menu

  = 4.10.0 =
* Better product search

  = 4.9.3 =
* Fix product block

  = 4.9.2 =
* Fix add to cart AJAX

  = 4.9.1 =
* Fix add to cart AJAX

  = 4.9.0 =
* Better stock
* Upgrade Hybridauth

  = 4.8.3 =
* Add option to disable FontAwesome enqueue

  = 4.8.2 =
* Fix AJAX

  = 4.8.1 =
* Fix cart

  = 4.8.0 =
* Fix contact address
* Better code compatibility with other modules

  = 4.7.1 =
* Fix cron

  = 4.7.0 =
* Introduce cron for better transient refresh with soft or full refresh (may need a powerful server)
* Fix special characters in form
* Minor bugfixes

  = 4.6.1 =
* Fix real stock display

  = 4.6.0 =
* Beta version with lots of AJAX and new features
* Lots of debug and fixes
* Support Dolibarr 12

  = 4.5.1 =
* Beta version with lots of AJAX and new features
* Support Dolibarr 12

  = 4.5.0 =
* Beta version with lots of AJAX and new features
* Support Dolibarr 12

  = 4.4.5 =
* Fix pagination
* Add filter mydoliconnectuserform
* Better footer on e-shop

  = 4.4.4 =
* Fix PHP errors
* Add pagination on list

  = 4.4.3 =
* Fix stock
* Dolibarr v11.0.3

  = 4.4.2 =
* Fix and clean code

  = 4.4.1 =
* Fix sale service with AJAX

  = 4.4.0 =
* Better forms with AJAX
* Better online shop
* Lots of fixes and clean code

  = 4.3.4 =
* better restricted mode with default role
* better default role
* add outstanding amount support before processing order
* support excl. incl VAT display price
* fix & clean code

  = 4.3.3 =
* security settings in akax
* fix & clean code

  = 4.3.2 =
* fix product & membership block
* support federal part membership display
* fix & clean code

  = 4.3.1 =
* add option to product's category widget
* fix & clean code

  = 4.3.0 =
* new ajax add to cart
* design fix & clean
* fix & clean code

  = 4.2.7 =
* support of Dolibarr 11.0.1
* need upgrade to doliconnector 11.0.1
* fix & clean code

  = 4.2.6 =
* fix & clean code
* 
  = 4.2.5 =
* fix & clean code

  = 4.2.4 =
* fix & clean code
* better stripe payment error message
* better restricted mode
* better price modes display

  = 4.2.3 =
* fix & clean code
* introduce CGV check

  = 4.2.2 =
* fix & clean code

  = 4.2.1 =
* better support of restricted mode (PRO)

 = 4.2.0 =
* lot of fix & clean code
* update font-awesome
* add extrafields packaging for products (sale by x qty)
* update flag css

 = 4.1.8 =
* fix date with wp_date()

 = 4.1.7 =
* fix signup link & fpw

 = 4.1.6 =
* fix alert & translation

 = 4.1.5 =
* fix fpw & rpw form

 = 4.1.4 =
* fix payment form

 = 4.1.3 =
* fix sepa debit form

 = 4.1.2 =
* fix form

 = 4.1.1 =
* fix privacy form

 = 4.1.0 =
* new release
* need Dolibarr 11.x.x
* revamp payment module and design and more security (token in forms)
* new pdf download
* lot of fixes

 = 4.0.8 =
* fix pre-release

 = 4.0.7 =
* fix pre-release

 = 4.0.6 =
* fix pre-release

 = 4.0.5 =
* fix pre-release

 = 4.0.4 =
* fix pre-release

 = 4.0.3 =
* fix pre-release

 = 4.0.2 =
* fix pre-release

 = 4.0.1 =
* fix pre-release

 = 4.0.0 =
* pre-release

 = 3.12.1 =
* fix readme and credit

 = 3.12.0 =
* fix compatibility with dolibarr 10.0.6

 = 3.11.5 =
* fix & clean code
* fix product block

 = 3.11.4 =
* fix & clean code
* update to wordpress 5.4
* automation github to SVN wordpress

 = 3.11.3 =
* fix & clean code
* update to wordpress 5.4
* automation github to SVN wordpress

 = 3.11.2 =
* fix & clean code

 = 3.11.1 =
* fix & clean code

 = 3.11.0 =
* fix & clean code
* work on end of doliconnect PRO -> all in free module without support or addon
* new widgets, new pages for doliconnect

 = 3.10.8 =
* fix & clean code
* work on end of doliconnect PRO -> all in free module without support or addon
* new widgets for doliconnect

 = 3.10.7 =
* fix & clean code
* work on end of doliconnect PRO -> all in free module without support or addon

 = 3.10.6 =
* fix & clean code
* work on end of doliconnect PRO -> all in free module without support or addon

 = 3.10.5 =
* fix & clean code
* work style, lang & icon

 = 3.10.4 =
* fix & clean code
* work on dolishop & contact's roles for dolibarr v11

 = 3.10.3 =
* fix & clean code (remise percent, display price ttc...)
* work on dolishop

 = 3.10.2 =
* fix & clean code (refresh, polylang link...)
* work on dolishop

 = 3.10.1 =
* fix & clean code

 = 3.10.0 =
* fix & clean code

 = 3.9.2 =
* fix & clean code
* better multicompany compatibility
* fix password change form

 = 3.9.1 =
* fix & clean code
* fix multicompany compatibility
* prepare v11
* fix stock display

 = 3.9.0 =
* fix & clean code
* fontawesome 5.10.2

 = 3.8.2 =
* fix & clean code
* Compatibility with Dolibarr 10.0.1

 = 3.8.1 =
* fix & clean code
* better product (duration, documents...)
* add shop without cart
* move wishlist in an external addon

 = 3.8.0 =
* fix & clean code
* WIP payment intent for Stripe
* WIP wishlist
* better support multilang with polylang for products & categories
* better restrict signup pro, perso or both

 = 3.7.2 =
* fix & clean code
* WIP external captcha for login or signup

 = 3.7.1 =
* fix & clean code kiosk mode
* WIP support WPML

 = 3.7.0 =
* fix & clean code
* prepare dolibarr v10
* WIP donation page
* no more need shortcodes for doliconnect's pages
* Due to EU's rules, you 'll need dolibarr 10 for online payment from september 2019

 = 3.6.7 =
* fix & clean code
* prepare dolibarr v10
* introduce dolialert function

 = 3.6.6 =
* fix & clean code
* prepare dolibarr v10
* better contact and linked members

 = 3.6.5 =
* fix & clean code
* prepare dolibarr v10

 = 3.6.4 =
* best JS & clean code

 = 3.6.3 =
* best JS & clean code

 = 3.6.1 & 3.6.2 =
* debug & clean code

 = 3.6.0 =
* debug ++++ & clean code
* upgrade font-awesome 5.8.2
* Require an update of Doliconnector to 9.0.4 <https://github.com/ptibogxiv/doliconnector/archive/9.0.4.zip>
* no more need shortcodes for include doliconnect content

 = 3.5.6 =
* debug & clean code

 = 3.5.5 =
* debug & clean code

= 3.5.4.2 =
* fix user form & clean code

= 3.5.2 =
* clean code
* minor fix & functionnalize
* update lang

= 3.5.2 =
* clean code
* minor fix
* upgrade lang

= 3.5.1 =
* clean code
* minor fix & change payment methods form
* upgrade lang

= 3.5.0 =
* clean code
* fix & revamp functions
* upgrade lang

= 3.4.7 =
* clean code
* minor fix
* fix settings update

= 3.4.6 =
* clean code
* minor fix

= 3.4.5 =
* clean code
* minor fix
* compatibility PHP 7.2

= 3.4.4 =
* clean code
* minor fix

* = 3.4.3 =
* clean code
* minor fix

= 3.4.2 =
* clean code
* minor fix

= 3.4.1 =
* clean code

= 3.4.0 =
* April update
* new thirdparty / contact form
* prepare transient duration manager
* clean code
 
= 3.3.x =
* clean code & debug
* WIP donations
* add better date_modification
* wip multi network
* fix reset password
* better management of thirdparty's fields

* = 3.2.x =
* update readme
* bootstrap 4.3 & fontawesome 5.7.1
* fix loader in login form

= 3.1.x =
* First public version on wordpress.org

= 3.0.X =
* Release Candidate version

= 2.X.X =
* Beta version

= 1.X.X =
* Alpha version
