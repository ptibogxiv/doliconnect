=== Doliconnect ===

Contributors: ptibogxiv, audrasjb
Tags: erp, crm, ecommerce, dolibarr, payment, interface, customer, subscription, membership, doliconnect, dashboard, paypal, stripe, doliconnect, invoice, order, donation, GDPR, RGPD, LGPD, CCPA
Requires at least: 5.0
Tested up to: 5.4
Stable tag: 4.1.7
Requires PHP: 7.0
License: GPL v3 or later
Donate link: https://www.paypal.me/ptibogxiv

This plugin will interface your Dolibarr within a customer interface in WordPress

== Description ==

Use this plugin to greatly improve relations with yours customers. No Woocommerce or another e-shop need!

If you like the plugin, feel free to rate it (on the right side of this page)! :)

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

= Dolibarr compatibility =

Doliconnect v4.x -> Dolibarr 11.x
Doliconnect v3.x -> Dolibarr 10.x

== Screenshots ==

1. Shortcode integration.
2. Front-end form feature.
3. Front-end form request succeed.
4. Confirmation email sent to the user/visitor.

== Credits ==

We would like to thank this contributors and plugins:

[gdpr-data-request-form](https://wordpress.org/plugins/gdpr-data-request-form/ "gdpr-data-request-form")

== Changelog ==
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
