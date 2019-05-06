=== Doliconnect ===

Contributors: ptibogxiv
Tags: erp, crm, ecommerce, dolibarr, payment, interface, customer, subscription, membership, doliconnect, dashboard
Requires at least: 5.0
Tested up to: 5.2
Stable tag: 3.5.3
Requires PHP: 7.0
Donate link: https://www.paypal.me/ptibogxiv

This plugin will synchronize your dolibarr's informations in a wordpress customer interface

== Description ==

Use this plugin to greatly improve relations with yours customers. No Woocommerce or another e-shop need!

Compatibility with Dolibarr v8, v9 & v10. 

If you like the plugin, feel free to rate it (on the right side of this page)! :)

== Installation ==

Installing "Doliconnect" can be done either by searching for "Dolibarr REST API" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

Dolibarr configuration steps:

1. Activate the REST API for Dolibarr: go to menu _Home > Setup > Modules/Applications_ and activate the _API/Web services (REST server)_.
2. Generate and copy a specific user API key with full/admin rights: go to menu _Home > Users & groups_, select your admin user in the list. Once on the User card, click the _Modify_ button and generate and copy the _Key for API_ (see screenshot). Click the _Save_ button.
3. Your dolibarr needs to have the Doliconnector module which can be freely downloaded on Dolistore or Github https://github.com/ptibogxiv/doliconnector/releases

WordPress configuration steps:
1. Go to WordPress Admin > Settings > Doliconnect
2. BE CAREFUL, in multisite blog, there is a netword settings page too for keys and general settings!

== Screenshots ==

1. Shortcode integration.
2. Front-end form feature.
3. Front-end form request succeed.
4. Confirmation email sent to the user/visitor.

== Changelog ==
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

= 3.3.4 =
* clean code
* new thirdparty / contact form

= 3.3.3 =
* clean code
* update contact/adress list
* transform compta block to customer block
* fix contact form

= 3.3.2 =
* clean code

= 3.3.1 =
* fix creation account
 
= 3.3.0 =
* clean code & debug
* WIP donations
* add better date_modification
* wip multi network
* fix reset password
* better management of thirdparty's fields

= 3.2.6 =
* WIP support multi-network
* clean code & debug
* WIP donations

= 3.2.5 =
* fix password reset

= 3.2.4 =
* clean code & optimization
* fix lang

= 3.2.3 =
* clean code
* fix download main doc / regenerate

= 3.2.2 =
* clean code
* update bootswatch
 
= 3.2.1 =
* update translation
* bootstrap 4.3.1 & fontawesome 5.7.2

* = 3.2.0 =
* update readme
* bootstrap 4.3 & fontawesome 5.7.1
* fix loader in login form

= 3.1.5 =
* clean code
* fix forgotten password form

= 3.1.4 =
* clean code
* optimize contact form

= 3.1.3 =
* clean code
* fix civility if Dolibarr < v9

= 3.1.2 =
* clean code

= 3.1.1 =
* fix CSS and call Dolibarr api

= 3.1.0 =
* First public version on wordpress.org

= 3.0.X =
* Release Candidate version

= 2.X.X =
* Beta version

= 1.X.X =
* Alpha version