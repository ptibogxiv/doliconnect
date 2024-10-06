=== Doliconnect ===

Contributors: ptibogxiv, audrasjb
Tags: erp, crm, ecommerce, dolibarr, payment, interface, customer, subscription, membership, doliconnect, dashboard, paypal, stripe, doliconnect, invoice, order, donation, GDPR, RGPD, LGPD, CCPA
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 8.9.7
Requires PHP: 7.4
License: GPL v3 or later
Donate link: https://www.paypal.me/ptibogxiv

This plugin will interface your Dolibarr within a customer interface in WordPress

== Description ==

Use this plugin to greatly improve relations with yours customers. No Woocommerce or another e-shop need!

If you like the plugin, feel free to rate it (on the right side of this page)!

You can test it live on [https://demo.ptibogxiv.net](https://demo.ptibogxiv.net)

== Installation ==

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to "Doliconnect", follow the instructions and complete settings.

Dolibarr configuration steps:

1. Activate the REST API for Dolibarr: go to menu _Home > Setup > Modules/Applications_ and activate the _API/Web services (REST server)_.
2. Generate and copy a specific user API key with full/admin rights: go to menu _Home > Users & groups_, select your admin user in the list. Once on the User card, click the _Modify_ button and generate and copy the _Key for API_ (see screenshot). Click the _Save_ button.
3. Your dolibarr needs to have the Doliconnector module which can be freely downloaded on Dolistore or Github [Github](https://github.com/ptibogxiv/doliconnector/releases)

WordPress configuration steps:
1. Go to WordPress Admin > Settings > Doliconnect
2. BE CAREFUL, in multisite blog, there is a netword settings page too for keys and general settings!

== Frequently Asked Questions ==

1. From Dolibarr 13.X, Doliconnect supports rights of Dolibarr for view/edit/delete datas via WordPress.
2. Using old and unupdated Dolibarr can do some mistakes or less functionnality. Please check compatibility!
3. Always delete WordPress transients after changing settings in Dolibarr. All datas are saved with transients in WordPress 

= Dolibarr compatibility =

Doliconnect v8.x -> Dolibarr >15.x
Doliconnect v6.7 -> Dolibarr >13.x
Doliconnect v6.x -> Dolibarr >12.x
Doliconnect v5.x -> Dolibarr >11.x
Doliconnect v4.x -> Dolibarr >11.x
Doliconnect v3.x -> Dolibarr 10.x

= REST API compatibility =

In some cases, REST API with Dolibarr need some extra settings to work. Please read this
https://github.com/Dolibarr/dolibarr/pull/12089

== Screenshots ==

1. Shortcode integration.
2. Front-end form feature.
3. Front-end form request succeed.
4. Confirmation email sent to the user/visitor.

== Credits ==

We would like to thank this contributors and plugins:

[gdpr-data-request-form](https://wordpress.org/plugins/gdpr-data-request-form/ "gdpr-data-request-form")

== Changelog ==
   = 8.9.x =
* better UX/UI
* better support agenda event
* better membership susbcription
* fix php bugs & error

   = 8.8.x =
* better UX/UI
* support agenda event
* better admin & multilang
* fix php bugs & error

   = 8.7.x =
* better UX/UI
* fix php bugs & error

   = 8.6.x =
* support of exepnse report
* fix php bugs & error

   = 8.5.x =
* better account and support of doloibarr v20 & v21
* fix php bugs & error

   = 8.4.x =
* better cart/wishlist button and debug
* fix php bugs & error

   = 8.3.x =
* better cart/wishlist button and debug
* fix php bugs & error

   = 8.2.x =
* better eshop
* fix php bugs & error

   = 8.1.x =
* New pagination with new Dolibarr API
* fix php bugs & error

   = 8.0.x =
* upgrade bootstrap 5.3.3
* fix php bugs & error
* support dolibarr v19 & 20

  = 7.8.x =
* fix php error
* upgrade libraries
* support related product

   = 7.7.x =
* fix php error
* wishlist and memberlinked functionnalities

   = 7.6.x =
* better eshop and modal info

   = 7.5.x =
* better eshop

   = 7.4.0 =
* better eshop

   = 7.3.0 =
* bugs and fixes for php 8
* better eshop

   = 7.2.x =
* bugs and fixes
* better online shop
* upgrade bootstrap

   = 7.0.x =
* bugs and fixes
* better fix password changes and sync with Dolibarr
* use dolibarr's password rules

   = 6.9.0 =
* bugs and fixes
* fix password changes and sync with Dolibarr
* use dolibarr's password rules

   = 6.8.x =
* bugs and fixes
* better and more ajax form
* better captcha

   = 6.7.x =
* fix password changes
* compatibility with php 8 and dolibarr 16/17
* update libraries

   = 6.6.3 =
* bugs and fixes
* fix php errors

   = 6.6.2 =
* bugs and fixes

   = 6.6.1 =
* bugs and fixes

   = 6.6.0 =
* bugs and fixes
* upgrade libraries
* better stock management

   = 6.5.4 =
* bugs and fixes
* fix forget password form

   = 6.5.3 =
* bugs and fixes

   = 6.5.2 =
* bugs and fixes
* fix translation

   = 6.5.1 =
* bugs and fixes for payment

   = 6.5.0 =
* bugs and fixes
* new price code
* better Ajax

   = 6.4.0 =
* bugs and fixes
* fix php and apache error

   = 6.3.1 =
* bugs and fixes

   = 6.3.0 =
* support of dolibarr v15.0.1
* bugfixs

   = 6.2.1 =
* better support of dolibarr v15

   = 6.2.0 =
* better support of dolibarr v15
* bugs and fixes
Fix php8 bugs

   = 6.1.2 =
* better support of dolibarr v15
* bugs and fixes

   = 6.1.1 =
* better support of dolibarr v15
* bugs and fixes
* more Ajax form

   = 6.1.0 =
* better support of dolibarr v15
* bugs and fixes
* more Ajax form
* more of new captcha on forms

   = 6.0.2 =
* bugs and fixes
* new captcha on forgot password page

   = 6.0.1 =
* bugs and fixes

   = 6.0.0 =
* support dolibarr 15
* new captcha
* bugs and fixes

   = 5.6.6 =
* support dolibarr 14.0.4

   = 5.6.5 =
* upgrade flat icon
* debug template

   = 5.6.4 =
* upgrade bootstrap to 5.1.2

   = 5.6.2 =
* fix for login/logout url with secupress
* better membership module

   = 5.6.1 =
* fix for login/logout url

   = 5.6.0 =
* support Dolibarr's rights. Be carefull of your correct rights !
* better support of alternatives name or URL for wp-login.php

   = 5.5.3 =
* bugfix membership and php 8 error

   = 5.5.2 =
* bugfix membership and php 8 error

   = 5.5.1 =
* cart with ajax and offcanvas

   = 5.5.0 =
* upgrade bootstrap
* ajax for membership


   = 5.4.2 =
* fix & bugfixes membership

   = 5.4.1 =
* fix & bugfixes

   = 5.4.0 =
* fix & bugfixes
* fix languages
* upgrade bootstrap


   = 5.3.3 =
* fix & bugfixes
* fix languages
* upgrade bootstrap

   = 5.3.2 =
* fix & bugfixes

   = 5.3.1 =
* support dolibarr 13.0.2

   = 5.3.0 =
* optimization & fix
* add constante 

   = 5.2.7 =
* better home/return button

   = 5.2.6 =
* fix avatar form

   = 5.2.5 =
* fix payment method list

   = 5.2.4 =
* fix membership

   = 5.2.3 =
* fix membership

   = 5.2.2 =
* fix membership

   = 5.2.1 =
* fix download pdf if in other entity

   = 5.2.0 =
* enable mangement of membership in free version
* todo: more ajax forms

   = 5.1.0 =
* new public release with boostrap 5 and support of dolibarr 13
* Please not end of support of DoliconnectPRO, all functionnality will be open in free version.

   = 5.0.6 =
* fix & bugfixes
 
   = 5.0.5 =
* fix & bugfixes
* protect contact form
 
   = 5.0.4 =
* fix & bugfixes
* block using internet explorer

   = 5.0.4 =
* fix & bugfixes
* new shipping api
* 
   = 5.0.3 =
* fix & bugfixes

   = 5.0.2 =
* upgrade bootswatch to 4.6.0
* fix template and form

  = 5.0.1 =
* fix critical bug

  = 5.0.0 =
* compatibility avec dolibarr 13
* new workflow
* support of boostrap 5
* better code
* lots of bugfixes

  = 4.11.4 =
* better search

  = 4.11.3 =
* upgrade bootswatch

  = 4.11.2 =
* fix add to cart button

  = 4.11.0 =
* new: add option for displaying invoices in menu

  = 4.10.0 =
* better product search

  = 4.9.3 =
* fix product block

  = 4.9.2 =
* fix add to cart ajax

  = 4.9.1 =
* fix add to cart ajax

  = 4.9.0 =
* better stock
* upgrade hybridauth

  = 4.8.3 =
* add option to disable fontawesome enqueue

  = 4.8.2 =
* fix ajax

  = 4.8.1 =
* fix cart

  = 4.8.0 =
* fix contact address
* better code compatibility other other module

  = 4.7.1 =
* fix cron

  = 4.7.0 =
* introduce cron for better transient with soft ou full refresh (can need huge server)
* fix special characters in form
* minor bugfixes

  = 4.6.1 =
* fix real stock display

  = 4.6.0 =
* beta version with lot of ajax and new feature
* lots of debug and fix
* support of dolibarr 12

== Changelog ==
  = 4.5.1 =
* beta version with lot of ajax and new feature
* support of dolibarr 12

  = 4.5.0 =
* beta version with lot of ajax and new feature
* support of dolibarr 12

  = 4.4.5 =
* fix pagination
* add filter mydoliconnectuserform'
* better footer on eshop

  = 4.4.4 =
* fix php errors
* add pagination on list

  = 4.4.3 =
* fix stock
* dolibarr v11.0.3

  = 4.4.2 =
* fix & clean code

  = 4.4.1 =
* fix sale service with ajax

  = 4.4.0 =
* better forms with ajax
* better online shop
* lot of fixes & clean code

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
