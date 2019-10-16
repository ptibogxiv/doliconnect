<?php
/**
 * Build a configuration array to pass to `Hybridauth\Hybridauth`
 */

$config = [
    //Location where to redirect users once they authenticate with a provider
    'callback' => doliconnecturl('doliaccount')."?provider=".(isset($_GET["provider"])?$_GET["provider"]:null),

    //Providers specifics
    'providers' => [
        'Facebook' => [ 
            'enabled' => get_option('doliconnect_facebook'),     
            'keys' => [ 
                'key'    => get_option('doliconnect_facebook_key'),
                'secret' => get_option('doliconnect_facebook_secret') 
            ]
        ],
        'Google' => [ 
            'enabled' => get_option('doliconnect_google'),     
            'keys' => [ 
                'key'    => get_option('doliconnect_google_key'), 
                'secret' => get_option('doliconnect_google_secret')  
            ]
        ],    
        'Twitter' => [ 
            'enabled' => get_option('doliconnect_twitter'),     
            'keys' => [ 
                'key'    => '...', 
                'secret' => '...'  
            ]
        ],    
]

]; 
