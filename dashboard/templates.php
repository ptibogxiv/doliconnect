<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function doliaccount_display($content, $controle = false) {
global $current_user;

  if ( (in_the_loop() && is_main_query() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) || ( (!is_user_logged_in() && !empty(get_option('doliconnectrestrict')) && !is_page(doliconnectid('doliaccount')) && !empty($controle) ) || (!is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !empty(get_option('doliconnectrestrict')) && !is_page(doliconnectid('doliaccount')) && !empty($controle) ) )) {

    doliconnect_enqueues();
      $ID = $current_user->ID;
      $time = current_time( 'timestamp', 1);

      $content .=  "<div class='row'>";
      if ( empty(get_option('doliconnectrestrict')) || is_user_logged_in() ) {
        $content .=  "<div class='col-xs-12 col-sm-12 col-md-3'><div class='row'><div class='col-3 col-xs-4 col-sm-4 col-md-12 col-xl-12'><div class='card shadow-sm' style='width: 100%'>";
        $content .=  get_avatar($ID);

        if ( !defined("DOLIBUG") && is_user_logged_in() && is_user_member_of_blog( $current_user->ID, get_current_blog_id())) {
        $content .=  "<a href='".esc_url( add_query_arg( 'module', 'avatars', doliconnecturl('doliaccount')) )."' title='".__( 'Edit my avatar', 'doliconnect')."' class='card-img-overlay'><div class='d-block d-sm-block d-xs-block d-md-none'></div><div class='d-none d-md-block'><i class='fas fa-camera fa-2x'></i></div></a>";
        } 
        $content .=  '<ul class="list-group list-group-flush">';
        if ( isset($_GET['module']) && !empty($_GET['module'])) {
        $content .=  "<a href='".esc_url( doliconnecturl('doliaccount') )."' class='list-group-item list-group-item-light list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-arrow-circle-left fa-fw'></i></div><div class='d-none d-md-block'><i class='fas fa-arrow-circle-left fa-fw'></i> ".__( 'Return', 'doliconnect')."</div></center></a>";
        } else {
        $content .=  "<a href='".esc_url(home_url())."' class='list-group-item list-group-item-light list-group-item-action'><center><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-home'></i></div><div class='d-none d-md-block'><i class='fas fa-home fa-fw'></i> ".__( 'Home', 'doliconnect')."</div></center></a>";
        }
        $content .=  '</ul>';

        $content .=  '</div><br></div>';
        $content .=  "<div class='col-9 col-xs-8 col-sm-8 col-md-12 col-xl-12'>";
      } else {
        $content .=  "<div class='col-md-6 offset-md-3'>";
      }
      if ( is_user_logged_in() && is_user_member_of_blog( $current_user->ID, get_current_blog_id())) {

        $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        $request = "/thirdparties/".$thirdparty->id;

        if ( isset($thirdparty->status) && $thirdparty->status != '1' ) {
          $content .=  "</div></div></div>";
          $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'><div class='card shadow-sm'><div class='card-body'>";
          $content .=  '<br><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>'.__( 'This account is closed. Please contact us for reopen it.', 'doliconnect').'</h4></div></center><br><br><br><br><br>';
          $content .=  "</div></div></div></div>";
        } else { 
          if ( isset($_GET['module']) && !empty($_GET['module'])) {
            if ( has_filter('user_doliconnect_'.esc_attr($_GET['module'])) ) {
              if ( has_filter('user_doliconnect_menu') ) {
                $content .= "<div class='list-group shadow-sm'>";
                $content .= apply_filters('user_doliconnect_menu', null, esc_attr($_GET['module']));
                $content .= "</div><br>";
              }
              $content .=  "</div></div></div>";
              $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
              $content .= apply_filters('user_doliconnect_'.esc_attr($_GET['module']), $content, esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
            } elseif ( has_filter('customer_doliconnect_'.esc_attr($_GET['module'])) && isset($thirdparty->client) && !empty($thirdparty->client)) {
              if ( has_filter('customer_doliconnect_menu') ) {
                $content .=  "<div class='list-group shadow-sm'>";
                $content .= apply_filters('customer_doliconnect_menu', null, esc_attr($_GET['module']));
                $content .=  "</div><br>";
              }
              $content .=  "</div></div></div>";
              $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
              $content .= apply_filters( 'customer_doliconnect_'.esc_attr($_GET['module']), $content, esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
            } elseif ( has_filter('member_doliconnect_'.esc_attr($_GET['module'])) ) {
              if ( has_filter('member_doliconnect_menu') ) {
                $content .=  "<div class='list-group shadow-sm'>";
                $content .= apply_filters('member_doliconnect_menu', null, esc_attr($_GET['module']));
                $content .=  "</div><br>";
              }
              $content .=  "</div></div></div>";
              $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
              $content .= apply_filters('member_doliconnect_'.esc_attr($_GET['module']), $content, esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
            } elseif ( has_filter('supplier_doliconnect_'.esc_attr($_GET['module'])) && isset($thirdparty->fournisseur) && !empty($thirdparty->fournisseur)) {
              if ( has_filter('supplier_doliconnect_menu') ) {
                $content .=  "<div class='list-group shadow-sm'>";
                $content .= apply_filters('supplier_doliconnect_menu', null, esc_attr($_GET['module']));
                $content .=  "</div><br>";
              }
              $content .=  "</div></div></div>";
              $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
              $content .= apply_filters('supplier_doliconnect_'.esc_attr($_GET['module']), $content, esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
            } elseif ( has_filter('grh_doliconnect_'.esc_attr($_GET['module'])) ) {
              if ( has_filter('grh_doliconnect_menu') ) {
                $content .=  "<div class='list-group shadow-sm'>";
                $content .= apply_filters('grh_doliconnect_menu', null, esc_attr($_GET['module']));
                $content .=  "</div><br>";
              }
              $content .=  "</div></div></div>";
              $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
              $content .= apply_filters('grh_doliconnect_'.esc_attr($_GET['module']), $content, esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
            } elseif ( has_filter('settings_doliconnect_'.esc_attr($_GET['module'])) ) {
              if ( has_filter('settings_doliconnect_menu') ) {
                $content .=  "<div class='list-group shadow-sm'>";
                $content .= apply_filters('settings_doliconnect_menu', null, esc_attr($_GET['module']));
                $content .=  "</div><br>";
              }
              $content .=  "</div></div></div>";
              $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
              $content .= apply_filters('settings_doliconnect_'.esc_attr($_GET['module']), $content, esc_url( add_query_arg( 'module', esc_attr($_GET['module']), doliconnecturl('doliaccount')) ) ); 
            } else {
              wp_redirect( esc_url(doliconnecturl('doliaccount')) );
              exit;
            }
            $content .=  "</div>";
          } elseif ( isset($_GET["action"]) && $_GET["action"] == 'confirmaction' ) {
            $content .=  "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5><small class='text-muted'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</small></p></div></div></div>";
            $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
            $content .=  "<div class='card shadow-sm'><div class='card-body'>";
            if ( ! isset( $_GET['request_id'] ) ) {
              $content .=  __( 'Missing request ID.');
            }
            if ( ! isset( $_GET['confirm_key'] ) ) {
              $content .=  __( 'Missing confirm key.');
            }   
            if ( isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
              $request_id = (int) $_GET['request_id'];
              $key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
              $result     = wp_validate_user_request_key( $request_id, $key );
              //if ( !is_wp_error( $result ) ) {
              do_action( 'user_request_action_confirmed', $request_id );
              $message = _wp_privacy_account_request_confirmed_message( $request_id );
              $content .=  $message;
              //}
            }
            $content .=  "</div></div></div>";
          } else {
            $content .=  "<p class='font-weight-light' align='justify'><h5>".sprintf(__('Hello %s', 'doliconnect'), $current_user->first_name)."</h5><small class='text-muted'>".__( 'Manage your account, your informations, orders and much more via this secure client area.', 'doliconnect')."</small></p></div></div></div>";
            $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";

            if ( has_filter('user_doliconnect_menu') ) {
              $content .=  '<div class="card shadow-sm"><div class="card-header">'.sprintf(__('%s My profil', 'doliconnect'), '<i class="fa-solid fa-user"></i>').'</div><ul class="list-group list-group-flush">';
              $content .=  apply_filters('user_doliconnect_menu', null, null);
              $content .=  "</ul></div><br>";
            }  
            if ( has_filter('customer_doliconnect_menu') && isset($thirdparty->client) && !empty($thirdparty->client)) {
              $content .=  '<div class="card shadow-sm"><div class="card-header">'.sprintf(__('%s My purchases', 'doliconnect'), '<i class="fa-solid fa-user-tag"></i>').'</div><ul class="list-group list-group-flush">';
              $content .=  apply_filters('customer_doliconnect_menu', null, null);
              $content .=  "</ul></div><br>";
            }
            if ( has_filter('member_doliconnect_menu') ) {
              $content .=  '<div class="card shadow-sm"><div class="card-header">'.sprintf(__('%s My membership', 'doliconnect'), '<i class="fa-solid fa-user-plus"></i>').'</div><ul class="list-group list-group-flush">';
              $content .=  apply_filters('member_doliconnect_menu', null, null);
              $content .=  "</ul></div><br>";
            }
            if ( has_filter('supplier_doliconnect_menu') && isset($thirdparty->fournisseur) && !empty($thirdparty->fournisseur)) {
              $content .=  '<div class="card shadow-sm"><div class="card-header">'.sprintf(__('%s My supplies', 'doliconnect'), '<i class="fa-solid fa-boxes-packing"></i>').'</div><ul class="list-group list-group-flush">';
              $content .=  apply_filters('supplier_doliconnect_menu', null, null);
              $content .=  "</ul></div><br>";
            }
            if ( has_filter('grh_doliconnect_menu') ) {
              $content .=  '<div class="card shadow-sm"><div class="card-header">'.sprintf(__('%s My human resources', 'doliconnect'), '<i class="fa-solid fa-user-tie"></i>').'</div><ul class="list-group list-group-flush">';
              $content .=  apply_filters('grh_doliconnect_menu', null, null);
              $content .=  "</ul></div><br>";
            }
            if ( has_filter('settings_doliconnect_menu') ) {
              $content .=  '<div class="card shadow-sm"><div class="card-header">'.sprintf(__('%s My settings & contacts', 'doliconnect'), '<i class="fa-solid fa-user-gear"></i>').'</div><ul class="list-group list-group-flush">';
              $content .=  apply_filters('settings_doliconnect_menu', null, null);
              $content .=  "</ul></div><br>";
            }
            $content .=  '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
            $content .=  "<a href='".wp_logout_url( home_url() )."' class='list-group-item list-group-item-light list-group-item-action";
            $content .=  "'>".__( 'Sign out', 'doliconnect')."</a>";
            $content .=  "</ul></div>";
            $content .=  "</div>";
          }
          $content .=  "</div>";
        }
      } else { 
        $content .=  "</div></div></div>";
        if ( empty(get_option('doliconnectrestrict')) ) {
          $content .=  "<div class='col-xs-12 col-sm-12 col-md-9'>";
        } else {
          $content .=  "<div class='col-md-6 offset-md-3'>";
        }

        if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
          $content .=  '<div class="card shadow-sm">';
          $content .=  '<div class="card-body">';
          $content .=  dolicheckie($_SERVER['HTTP_USER_AGENT']);
          $content .=  "</div></div>";
        } elseif ( isset($_GET["action"]) && $_GET["action"] == 'confirmaction' ) {

          if ( ! isset( $_GET['request_id'] ) ) {
            $content .=  __( 'Missing request ID.');
          }
          if ( ! isset( $_GET['confirm_key'] ) ) {
            $content .=  __( 'Missing confirm key.');
          }   

          if ( isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
            $request_id = (int) $_GET['request_id'];
            $key = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
            $result = wp_validate_user_request_key( $request_id, $key );

            //if ( !is_wp_error( $result ) ) {
            do_action( 'user_request_action_confirmed', $request_id );
            $message = _wp_privacy_account_request_confirmed_message( $request_id );
            $content .=  $message;
            //}
          }
          $content .=  "</div></div>";
        } elseif ( isset($_GET["action"]) && $_GET["action"] == 'register' && !is_user_logged_in() ) {
          if ( is_multisite() && !get_option( 'users_can_register' ) && (get_site_option( 'registration' ) != 'user' or get_site_option( 'registration' ) != 'all') ) {
            //wp_redirect(esc_url(doliconnecturl('doliaccount')));
            //exit;
          } elseif ( !get_option( 'users_can_register' ) ) {
            //wp_redirect(esc_url(doliconnecturl('doliaccount')));
            //exit;
          }

          if (isset($_GET["morphy"]) && (($_GET["morphy"] == 'mor' && (empty(get_option('doliconnect_disablepro')) || get_option('doliconnect_disablepro') == 'mor')) || ($_GET["morphy"] == 'phy' && (empty(get_option('doliconnect_disablepro')) || get_option('doliconnect_disablepro') == 'phy')))) {
            $content .=  "<div id='doliuserinfos-alert'></div><form action='".admin_url('admin-ajax.php')."' id='doliuserinfos-form' method='post' class='was-validated' enctype='multipart/form-data'>";

            $content .=  doliAjax('doliuserinfos', null, 'create');

            $content .=  '<div class="card shadow-sm"><div class="card-header">';
            if ($_GET["morphy"] == 'phy') {
              $content .=  __( 'Create a personnal account', 'doliconnect');   
            } elseif ($_GET["morphy"] == 'mor') {
              $content .=  __( 'Create an enterprise account', 'doliconnect');    
            }
            $content .=  '<a class="float-end text-decoration-none" href="'.wp_registration_url(get_permalink()).'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a>';  
            $content .=  '</div>';

            $content .=  doliuserform( null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'thirdparty', doliCheckRights('societe', 'creer'));

            $content .=  "<div class='card-body'><div class='d-grid gap-2'><button class='btn btn-outline-secondary' type='submit'";
            if ( get_option('users_can_register')=='1' && ( get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all' ) || ( !is_multisite() && get_option( 'users_can_register' )) ) {
              $content .=  "";
            } else { 
              $content .=  " aria-disabled='true'  disabled"; 
            }
            $content .=  ">".__( 'Create an account', 'doliconnect')."</button></form>";
            $content .=  '</div></div></div></form>';

            do_action( 'login_footer');

          } else {
            $content .=  '<div class="card shadow-sm"><div class="card-header">'.__( 'Create an account', 'doliconnect');
            //$content .=  '<a class="float-end text-decoration-none" href="'.esc_url( doliconnecturl('doliaccount') ).'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a>';  
            $content .=  '</div>';

            $content .=  '<div class="card-body"><div class="card-group">
              <div class="card">
                
                <div class="card-body">
                  <h5 class="card-title">'.__( 'Create a personnal account', 'doliconnect').'</h5>
                  <p class="card-text"><small class="text-muted"></small></p>
                  <div class="d-grid gap-2">';
              if (get_option('doliconnect_disablepro') == 'mor') {
                $content .=  '<a class="btn btn-outline-secondary disabled" href="'.wp_registration_url(get_permalink()).'&morphy=phy" role="button" title="'.__( 'Create a personnal account', 'doliconnect').'" aria-disabled="true">'.__( 'Create a personnal account', 'doliconnect').'</a>';
              } else {
                $content .=  '<a class="btn btn-outline-secondary" href="'.wp_registration_url(get_permalink()).'&morphy=phy" role="button" title="'.__( 'Create a personnal account', 'doliconnect').'">'.__( 'Create a personnal account', 'doliconnect').'</a>';    
              }
                  $content .=  '</div>
                </div>
              </div>
              <div class="card">
                
                <div class="card-body">
                  <h5 class="card-title">'.__( 'Create an enterprise account', 'doliconnect').'</h5>
                  <p class="card-text"><small class="text-muted"></small></p>
                  <div class="d-grid gap-2">';
              if (get_option('doliconnect_disablepro') == 'phy') {
                $content .=  '<a class="btn btn-outline-secondary disabled" href="'.wp_registration_url(get_permalink()).'&morphy=mor" role="button" title="'.__( 'Create an enterprise account', 'doliconnect').'" aria-disabled="true">'.__( 'Create a personnal account', 'doliconnect').'</a>';
              } else {
                $content .=  '<a class="btn btn-outline-secondary" href="'.wp_registration_url(get_permalink()).'&morphy=mor" role="button" title="'.__( 'Create an enterprise account', 'doliconnect').'">'.__( 'Create an enterprise account', 'doliconnect').'</a>';    
              }
                  $content .=  '</div>
              </div>
            </div>';
            $content .=  '</div></div>';
          }
        } elseif ( isset($_GET["action"]) && $_GET["action"] == 'rpw' ) {
          if ( function_exists('secupress_get_module_option') && !empty(get_site_option('secupress_active_submodule_move-login')) && secupress_get_module_option('move-login_slug-login', '', 'users-login' ) ) {
            $login_url = site_url()."/".secupress_get_module_option('move-login_slug-login', '', 'users-login'); 
          } elseif (get_site_option('doliconnect_login')) {
              $login_url = site_url()."/".get_site_option('doliconnect_login');
          } else {
            $login_url = site_url()."/wp-login.php"; 
          }
          if (!$_GET["login"] || !$_GET["key"]) {
            ob_clean();
            wp_redirect(wp_login_url( get_permalink() ));
            exit;
          } else {   
            $user = check_password_reset_key( esc_attr($_GET["key"]), esc_attr($_GET["login"]) );
            if ( ! $user || is_wp_error( $user ) ) {
              if ( $user && $user->get_error_code() === 'expired_key' ){
                ob_clean();
                $arr_params = array( 'action' => 'lostpassword', 'error' => 'expiredkey');  
                wp_redirect(esc_url( add_query_arg( $arr_params, wp_login_url( get_permalink() )) ));
                exit;
              } else {
                ob_clean();
                $arr_params = array( 'action' => 'lostpassword', 'error' => 'invalidkey');  
                wp_redirect(esc_url( add_query_arg( $arr_params, wp_login_url( get_permalink() )) ));
                exit;
              }
            } else {
              $content .=  doliPasswordForm($user, doliconnecturl('doliaccount'));
            }
          }
        } elseif ( isset($_GET["provider"]) && $_GET["provider"] != null ) {
          include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/autoload.php');
          include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/config.php');
          try {
              //Feed configuration array to Hybridauth
              $hybridauth = new Hybridauth\Hybridauth($config);

              //Attempt to authenticate users with a provider by name
              $adapter = $hybridauth->authenticate($_GET["provider"]); 

              //Returns a boolean of whether the user is connected with Twitter
              $isConnected = $adapter->isConnected();
          
              //Retrieve the user's profile
              $userProfile = $adapter->getUserProfile();
          if ( !email_exists($userProfile->email) ) {
          $emailError = __( 'No account seems to be linked to this email address', 'doliconnect');
                  $hasError = true;   
              } else {
          $user=get_user_by( 'email', $userProfile->email);    
          wp_set_current_user($user->ID); 
          if (wp_validate_auth_cookie()==FALSE)
          {
              wp_set_auth_cookie($user->ID, true, true);
          }   
          do_action('wp_login', $user->user_login, $user); 

          $adapter->disconnect();     
          wp_redirect(esc_url(home_url()));
          exit;   
              }
              //Inspect profile's public attributes
          //var_dump($userProfile);
          //var_dump($adapter->getAccessToken());
              //Disconnect the adapter 
              $adapter->disconnect();
          }
          catch(\Exception $e) {
              // In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
              // let hybridauth forget all about the user so we can try to authenticate again.
              // Display the recived error, 
              // to know more please refer to Exceptions handling section on the userguide
              switch( $e->getCode() ){ 
                  case 0 : $content .=  "Unspecified error."; break;
                  case 1 : $content .=  "Hybriauth configuration error."; break;
                  case 2 : $content .=  "Provider not properly configured."; break;
                  case 3 : $content .=  "Unknown or disabled provider."; break;
                  case 4 : $content .=  "Missing provider application credentials."; break;
                  case 5 : $content .=  "Authentication failed. " 
                            . "The user has canceled the authentication or the provider refused the connection."; 
                  case 6 : $content .=  "User profile request failed. Most likely the user is not connected "
                            . "to the provider and he should to authenticate again."; 
                        $adapter->logout(); 
                        break;
                  case 7 : $content .=  "User not connected to the provider."; 
                        $adapter->logout(); 
                        break;
              } 
              $content .=  "<br><br><b>Original error message:</b> " . $e->getMessage();
          //$content .=  "<hr /><h3>Trace</h3> <pre>" . $e->getTraceAsString() . "</pre>";  
          }
        } elseif ( isset($_GET["action"]) && $_GET["action"] == 'fpw' ) { 
          $content .=  "<div id='dolifpw-alert'></div><form id='dolifpw-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
          $content .=  doliAjax('dolifpw');
          
          $content .=  '<div class="card shadow-sm"><div class="card-header">'.__( 'Forgot password?', 'doliconnect');
          //$content .=  '<a class="float-end text-decoration-none" href="'.esc_url( doliconnecturl('doliaccount') ).'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a>';  
          $content .=  '</div>';
          $content .=  "<ul class='list-group list-group-flush'><li class='list-group-item list-group-item-light list-group-item-action'>";
          $content .=  "<p class='text-justify'>".__( 'Please enter the email address by which you registered your account.', 'doliconnect')."</p>";

          $content .=  '<div class="form-floating mb-2">
          <input type="email" class="form-control" id="user_email" placeholder="name@example.com" name="user_email" value="" required>
          <label for="user_email"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label>
          </div>';

          $content .=  dolicaptcha('dolifpw');

          $content .=  "</li></lu><div class='card-body'>";
          $content .=  '<div class="d-grid gap-2"><button id="dolifpw-button" class="btn btn-outline-secondary" type="submit" value="submit">'.__( 'Submit', 'doliconnect').'</button></div>';

          $content .=  "</form></div></div>";
        } else {
          if (is_front_page()) {
            $redirect_to=home_url();
          } else {
            $redirect_to=get_permalink();
          }
          wp_safe_redirect(wp_login_url( $redirect_to ));
          exit;
        }
      }
    return $content;
  } else {
    return $content;
  }
}

add_filter( 'the_content', 'doliaccount_display', 10, 2);

//*****************************************************************************************

function dolifaq_display($content) {
  global $current_user;
    
  if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolifaq')) && !empty(doliconnectid('dolifaq')) ) {
    
    doliconnect_enqueues();
      $limit=10;
      if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']); }  else { $page = 0; }
      $request = "/knowledgemanagement/knowledgerecords?sortfield=t.rowid&sortorder=ASC&limit=".$limit."&page=".$page."&pagination_data=true&sqlfilters=(t.status:=:'1')and((t.lang:in:'0','".doliUserLang($current_user)."'))";
      if (isset($_GET['category']) && is_numeric(esc_attr($_GET['category'])) && esc_attr($_GET['category']) > 0 ) $request .= "&category=".esc_attr($_GET['category']);
      $object = callDoliApi("GET", $request, null, dolidelay('knowledgemanagement', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
      if ( doliversion('21.0.0') && isset($object->data) ) { $listfaq = $object->data; } else { $listfaq = $object; }

      $url = doliconnecturl('dolifaq');
      $content = '<div class="card"><div class="card-header">'.__( 'Knowledge base', 'doliconnect').'</div>';
      $content .= '<div class="card-body">';
      $content .= '</div>';
      $content .= '<div class="accordion accordion-flush" id="accordionDolifaq">';
      if ( !isset( $listfaq->error ) && $listfaq != null ) {
        foreach ( $listfaq as $postfaq ) { 
          $content .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingDolifaq'.$postfaq->id.'">';
          $content .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseDolifaq'.$postfaq->id.'" aria-expanded="false" aria-controls="flush-collapseDolifaq'.$postfaq->id.'">';
          $content .= $postfaq->question;
          $content .= '</button></h2>
          <div id="flush-collapseDolifaq'.$postfaq->id.'" class="accordion-collapse collapse" aria-labelledby="flush-headingDolifaq'.$postfaq->id.'" data-bs-parent="#accordionDolifaq">
          <div class="accordion-body">'.$postfaq->answer;
          //$content .=  doliCardFooter($request, 'agenda', $object);
          if (!empty(doliconnect_categories('knowledgemanagement', $postfaq, doliconnecturl('dolifaq')))) $content .= '<br>'.doliconnect_categories('knowledgemanagement', $postfaq, doliconnecturl('dolifaq'));
          $content .= '</div></div></div>';
        }
      }
    $content .= '</div>';

    $content .= '<div class="card-body">';
    $content .= doliPagination($object, $url, $page);
    $content .= '</div>';
    $content .= doliCardFooter($object, 'knowledgemanagement');
    $content .= '</div>';
  return $content;
} else {
  return $content;
}
    
}
    
add_filter( 'the_content', 'dolifaq_display');
    
//*****************************************************************************************

function dolicontact_display($content) {
global $current_user;

  if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicontact')) && !empty(doliconnectid('dolicontact')) ) {
    doliconnect_enqueues();
    $content =  "<div class='row mw-100'><div class='col-md-4'><h4>".__( 'Address', 'doliconnect')."</h4>";
    $company = callDoliApi("GET", "/setup/company", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    $content .=  doliCompanyCard($company);
    $content .=  "<br><h4>".__( 'Opening hours', 'doliconnect')."</h4>";
    $content .=  __( 'Monday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_MONDAY').'<br>';
    $content .=  __( 'Tuesday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_TUESDAY').'<br>';
    $content .=  __( 'Wednesday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_WEDNESDAY').'<br>';
    $content .=  __( 'Thursday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_THURSDAY').'<br>';
    $content .=  __( 'Friday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_FRIDAY').'<br>';
    $content .=  __( 'Saturday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_SATURDAY').'<br>';
    $content .=  __( 'Sunday', 'doliconnect').": ".doliopeninghours('MAIN_INFO_OPENINGHOURS_SUNDAY');

    $content .=  "</div><div class='col-md-8'><div id='content'>";
    $content .=  "<div id='dolicontact-alert'></div><form id='dolicontact-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";

    $content .=  doliAjax('dolicontact');

    $content .=  "<div class='card shadow-sm'><ul class='list-group list-group-flush'>
    <li class='list-group-item'>";
    if (is_user_logged_in()) {
    $fullname = $current_user->user_firstname." ".$current_user->user_lastname;
    } else {
    $fullname = '';
    }
    $content .=  '<div class="form-floating mb-2">
    <input type="text" class="form-control" name="contactName" autocomplete="off" id="contactName" placeholder="Name" value="'.$fullname.'"';
    if ( is_user_logged_in() ) { $content .=  " readonly"; } else { $content .=  " required"; }
    $content .=  '>
    <label for="contactName"><i class="fas fa-user fa-fw"></i> '.__( 'Complete name', 'doliconnect').'</label>
    </div>';
    $content .=  '<div class="form-floating mb-2">
    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="'.$current_user->user_email.'" autocomplete="off" ';
    if ( is_user_logged_in() ) { $content .=  " readonly"; } else { $content .=  " required"; }
    $content .=  '>
    <label for="email"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label>
    </div>';
    $type = callDoliApi("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100&lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    if ( isset($type) ) { 
    $content .=  '<div class="form-floating mb-2"><select class="form-select" id="ticket_type"  name="ticket_type" aria-label="'.__( 'Type', 'doliconnect').'" required>';
    if ( count($type) > 1 ) {
      $content .=  "<option value='' disabled selected >".__( '- Select -', 'doliconnect')."</option>";
    }
    foreach ($type as $postv) {
      $content .=  "<option value='".$postv->code."' ";
      if ( isset($_GET['type']) && $_GET['type'] == $postv->code ) {
        $content .=  "selected ";
      } elseif ( $postv->use_default == 1 ) {
        $content .=  "selected ";
      }
      $content .=  ">".$postv->label."</option>";
      }
      $content .=  '</select><label for="ticket_type">'.__( 'Type', 'doliconnect').'</label></div>';
    }
    $content .=  '<div class="form-floating mb-2">
    <textarea class="form-control" placeholder="Leave a comment here" name="comments" id="commentsText" style="height: 200px" required></textarea>
    <label for="commentsText">'.__( 'Message', 'doliconnect').'</label>
    </div>';
    $content .=  dolicaptcha('dolicontact');
    if ( !is_user_logged_in() ) {
      $content .=  '</li><li class="list-group-item"><div class="form-check"><input id="rgpdinfo" class="form-check-input form-check-sm" type="checkbox" name="rgpdinfo" value="ok"><label class="form-check-label w-100" for="rgpdinfo"><small class="form-text text-muted"> '.__( 'I agree to save my personnal informations in order to contact me', 'doliconnect').'</small></label></div>';  
    }
    $content .=  "</li></ul>";
    $content .=  "<div class='card-body'><div class='d-grid gap-2'><button id='dolicontact-button' class='btn btn-outline-secondary' type='submit'>".__( 'Send', 'doliconnect')."</button></div></div></div></div></div></form>";
    $content .=  "</div>";
    return $content;
  } else {
    return $content;
  }
}
add_filter( 'the_content', 'dolicontact_display');

//*****************************************************************************************

function dolisupplier_display($content) {
global $current_user;

  if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolisupplier')) && !empty(doliconnectid('dolisupplier')) ) {

    doliconnect_enqueues();

    $shopsupplier = doliconst("DOLICONNECT_CATSHOP_SUPPLIER", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $category = "";
      if ( isset($_GET['supplier']) && is_numeric(esc_attr($_GET['supplier'])) && esc_attr($_GET['supplier']) > 0 ) { 
        $request = "/thirdparties/".esc_attr($_GET['supplier']);
        $module = 'thirdparty';
        $thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
      }

      $content .=  "<div class='card shadow-sm'>";

      if ( !isset($thirdparty->error) && isset($_GET['supplier']) && isset($thirdparty->id) && ($_GET['supplier'] == $thirdparty->id) && $thirdparty->status == 1 && $thirdparty->fournisseur == 1 ) {

        $content .=  "<ul class='list-group list-group-flush'><li class='list-group-item'>";
        $content .=  "<div class='row'><div class='col-4 col-md-2'><center>";
        $content .=  doliconnect_image('thirdparty', $thirdparty->id.'/logos/'.$thirdparty->logo, array('entity'=> $thirdparty->entity), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        $content .=  "</center></div><div class='col-8 col-md-10'>".(!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name);
        if ( !empty($thirdparty->country_id) ) {  
          $country = callDoliApi("GET", "/setup/dictionary/countries/".$thirdparty->country_id."?lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
          $content .=  "<br><span class='flag-icon flag-icon-".strtolower($thirdparty->country_code)."'></span> ".$country->label.""; 
        }
        $content .=  "</div></div>";
        $content .=  "<p class='text-justify'>".$thirdparty->note_private."</p>";
        $photos = callDoliApi("GET", "/documents?modulepart=thirdparty&id=".$thirdparty->id, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if (!empty(doliconnect_categories('supplier', $thirdparty))) $content .=  doliconnect_categories('supplier', $thirdparty, doliconnecturl('dolisupplier'))."<br><br>";
        $content .=  doliconnect_image('thirdparty', $thirdparty->id, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $thirdparty->entity);
        $content .=  "</li>"; 

        $module = 'product';
        $limit=20;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request = "/products/purchase_prices?sortfield=t.ref&sortorder=ASC&limit=".$limit."&page=".$page."&supplier=".esc_attr($_GET["supplier"])."&pagination_data=true&sqlfilters=(t.tosell:=:1)";
        $resultats2 = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        //if ( doliversion('20.0.0') && isset($object->data) ) { $resultats = $object->data; } else { $resultats = $object; }
        if ( !isset($resultats2->error) && $resultats2 != null ) {
          foreach ($resultats2 as $product) {
            if (isset($product[0]->id) && !empty($product[0]->id)) {            
              $product = callDoliApi("GET", "/products/".$product[0]->id."?includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
              $content .=  apply_filters( 'doliproductlist', $product);
            }
          }
        } else {
          $content .=  "<li class='list-group-item list-group-item-light'><center>".__( 'No item currently on sale', 'doliconnect')."</center></li>";
        }

        $content .=  "</ul>";
        if (isset($resultats2)) { 
          $content .=  '<div class="card-body">';
          //$content .=  doliPagination($resultats2, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
          $content .=  '</div>';
        }
      } else {

        if (isset($shopsupplier) && !empty($shopsupplier)) $category = "&category=".$shopsupplier;
        $module = 'thirdparty';
        $limit=20;
        if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']-1); }  else { $page = 0; }
        $request = "/thirdparties?sortfield=t.nom&sortorder=ASC&limit=".$limit."&page=".$page."&mode=4".$category."&pagination_data=true&sqlfilters=(t.status:=:'1')";
        $object = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('20.0.0') && isset($object->data) ) { $resultats = $object->data; } else { $resultats = $object; }

        if ( doliversion('20.0.0') && isset($object->pagination) ) { 
          $count = $object->pagination->total;
        } else { 
          if (empty($object) || isset($object->error)) {
            $count = 0;
          } else {
            $count = count($object);
          }
        }

        if (!empty(get_option('dolicartsuppliergrid'))) { 
          $content .=  '<div class="card-body"><div class="row" data-masonry='; ?> {"percentPosition":true} <?php $content .= '>';
        } else {
          $content .=  '<ul class="list-group list-group-flush">';
        }

        if ( !isset($resultats->error) && $resultats != null ) {
          foreach ($resultats as $supplier) {

            if (!empty(get_option('dolicartsuppliergrid'))) { 
              $content .=  '<div class="col-sm-6 col-lg-4 mb-4"><div class="card">';
              if (!empty($supplier->logo)) { 
                $content .=  '<a href="'.esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) ).'">'.doliconnect_image('thirdparty', $supplier->id.'/logos/'.$supplier->logo, array('entity'=>$supplier->entity, 'class'=>'card-img'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)).'</a>';
              } else {
                $content .=  '<div class="card-body"><a href="'.esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) ).'"><center>'.(!empty($supplier->name_alias)?$supplier->name_alias:$supplier->name).'</center></a></div>';
              }
              $content .=  "</div></div>";
            } else {
              $content .=  "<a href='".esc_url( add_query_arg( 'supplier', $supplier->id, doliconnecturl('dolisupplier')) )."' class='list-group-item list-group-item-action'>".(!empty($supplier->name_alias)?$supplier->name_alias:$supplier->name)."</a>";
            }

          }
        } else {
          $content .=  "<li class='list-group-item list-group-item-light'><center>".__( 'No supplier', 'doliconnect')."</center></li>";
        }

        if (!empty(get_option('dolicartsuppliergrid'))) { 
          $content .=  "</div></div>";
        } else {
          $content .=  "</ul>";
        } 
      } 
      if (isset($object)) { 
        $content .=  '<div class="card-body">';
        $content .=  doliPagination($object, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $page, $limit);
        $content .=  '</div>';
      }
      if (isset($object)) $content .=  doliCardFooter($object, $module, $request);
      $content .=  '</div>';
    return $content;
  } else {
    return $content;
  }
}
add_filter( 'the_content', 'dolisupplier_display');

//*****************************************************************************************

function dolishop_display($content) {

  if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolishop')) && !empty(doliconnectid('dolishop')) ) {

    doliconnect_enqueues();

    $shop = doliconst("DOLICONNECT_CATSHOP", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

    $content .=  "<div class='card shadow-sm'>";

    if (dolicheckie($_SERVER['HTTP_USER_AGENT'])) {
      $content .=  '<div class="card shadow-sm">';
      $content .=  '<div class="card-body">';
      $content .=  dolicheckie($_SERVER['HTTP_USER_AGENT']);
      $content .=  "</div></div>";

    } elseif ( isset($_GET['product']) && is_numeric(esc_attr($_GET['product'])) ) {

      $request = "/products/".esc_attr($_GET['product'])."?includesubproducts=true&includetrans=true";
      $product = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

      $content .=  apply_filters( 'doliproductcard', $product, null);

      $content .=  doliCardFooter($product, 'product');
      $content .=  "</div>";

    } else {

      $limit=20;
      $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
      if ( isset($_GET['field']) ) { $field = esc_attr($_GET['field']); } else { $field = 'label'; }
      if ( isset($_GET['order']) ) { $order = esc_attr($_GET['order']); } else { $order = 'ASC'; }

      $cat = esc_attr(isset($_GET["subsubcategory"]) ? $_GET["subsubcategory"] : (isset($_GET["subcategory"]) ? $_GET["subcategory"] : (isset($_GET["category"]) ? $_GET["category"] : null)));
      $subcat = esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $cat);
      $subsubcat = esc_attr(isset($_GET["subsubcategory"]) ? $_GET["subsubcategory"] : $cat);
      $subsubsubcat = esc_attr(isset($_GET["subsubsubcategory"]) ? $_GET["subsubsubcategory"] : $cat);
      $category = callDoliApi("GET", "/categories/".$cat."?include_childs=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

      if ((is_numeric($cat) && isset($category->id) && $category->id > 0) || (isset($_GET["category"]) && $_GET["category"] == 'all' && !isset($_GET['product'])) || (isset($_GET["category"]) && $_GET["category"] == 'new' && !isset($_GET['product'])) || (isset($_GET['category']) && $_GET['category'] == 'discount' && !isset($_GET['product'])) || !isset($_GET["category"]) || (isset($_GET['search'])&& !empty($_GET['search']) && !isset($_GET['product']))) {

        if (isset($_GET['search'])&& !empty($_GET['search']))  {
          $search = explode(' ', esc_attr($_GET['search']));
          $sqlfilters = null;
          foreach($search as $i=>$key) {
            $sqlfilters .= "((t.label:like:'%25".esc_attr($key)."%25')or(t.description:like:'%25".esc_attr($key)."%25')or(t.ref:like:'%25".esc_attr($key)."%25')or(t.barcode:like:'%25".esc_attr($key)."%25'))and";
          }
          $request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&ids_only=true&pagination_data=true&sqlfilters=".$sqlfilters."(t.tosell:=:1)";
          $object = callDoliApi("GET", $request, null, dolidelay('search', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        } elseif (get_option('dolicartnewlist') != 'none' && isset($_GET['category']) && $_GET['category'] == 'new' && !isset($_GET['product'])) {
          $date = new DateTime(); 
          $date->modify('NOW');
          $duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
          $date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
          $lastdate = $date->format('Y-m-d');
          $request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".esc_attr($shop)."&ids_only=true&pagination_data=true&sqlfilters=(t.datec%3A%3E%3D%3A'".$lastdate."')and(t.tosell:=:1)";
          $object = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        } elseif ( doliCheckModules('discountprice') && isset($_GET['category']) && $_GET['category'] == 'discount' && !isset($_GET['product'])) { 
          $date = new DateTime(); 
          $date->modify('NOW');
          $lastdate = $date->format('Y-m-d');
          $request = "/discountprice?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&pagination_data=true&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')and(t.date_end%3A%3E%3D%3A'".$lastdate."')and(d.tosell:=:1)";
          $object = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        } elseif (!isset($_GET["category"]) || isset($_GET["category"]) && $_GET["category"] == 'all') {
          $request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".esc_attr($shop)."&ids_only=true&pagination_data=true&sqlfilters=(t.tosell:=:1)";
          $object= callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        } else {
          $request = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".$cat."&ids_only=true&pagination_data=true&sqlfilters=(t.tosell:=:1)";
          $object = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        } 
        if ( doliversion('19.0.0') && isset($object->data) ) { $resultats = $object->data; } else { $resultats = $object; }

        if ( doliversion('19.0.0') && isset($object->pagination) ) { 
          $count = $object->pagination->total;
        } else { 
          if (empty($object) || isset($object->error)) {
            $count = 0;
          } else {
            $count = count($object);
          }
        }
        //$content .=  var_dump($object);
        $content .=  '<div class="card-header">';
        if ( !isset($_GET["category"]) || isset($_GET["category"]) && $_GET["category"] == 'all') {
          $content .=  __(  'All items', 'doliconnect');
          //$content .= f( _n( 'There is %s item', 'There are %s items', $count, 'doliconnect' ), number_format_i18n( $count ) );
        } elseif (get_option('dolicartnewlist') != 'none' && isset($_GET['category']) && $_GET['category'] == 'new' && !isset($_GET['product'])) {  
          $content .=  __(  'Novelties', 'doliconnect');
          //$content .= f( _n( 'There is %s new item', 'There are %s new items', $count, 'doliconnect' ), number_format_i18n( $count ) );
        } elseif ( doliCheckModules('discountprice') && isset($_GET['category']) && $_GET['category'] == 'discount' && !isset($_GET['product'])) { 
          $content .=  __(  'Discounted items', 'doliconnect');
          //$content .= f( _n( 'There is %s discounted item', 'There are %s discounted items', $count, 'doliconnect' ), number_format_i18n( $count ) );
        } else {
          $content .=  doliproduct($category, 'label');
        } 
        if ( strpos(esc_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), 'subsubcategory') !== false ) {
          $arr_params = array( 'subsubcategory');
          $return =  esc_url( remove_query_arg( $arr_params ), $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
          $content .=  '<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( $return ).'"><i class="fas fa-arrow-left"></i>'.__( 'Back', 'doliconnect').'</a>';
        } elseif ( strpos(esc_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), 'subcategory') !== false ) {
          $arr_params = array( 'subcategory');
          $return =  esc_url( remove_query_arg( $arr_params ), $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
          $content .=  '<a class="fbtn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( $return ).'"><i class="fas fa-arrow-left"></i>'.__( 'Back', 'doliconnect').'</a>';
        } elseif ( strpos(esc_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), 'category') !== false && $_GET['category'] != 'all' ) {
          $arr_params = array( 'category');
          $return =  esc_url( remove_query_arg( $arr_params ), $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
          $content .=  '<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( $return ).'"><i class="fas fa-arrow-left"></i>'.__( 'Back', 'doliconnect').'</a>';
        }
        $content .=  '<div class="input-group w-50 float-end">
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-filter"></i></span><select id="selectbox" class="form-select form-select-sm" aria-label=".form-select-sm example" name="" onchange="javascript:location.href = this.value;">
        <option value="" disabled selected>'.__( '- Select -', 'doliconnect').'</option>
        <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'pg' => $page, 'field' => 'label', 'order' => 'ASC'), $_SERVER['REQUEST_URI']) ).'"';
        if ($field == 'label' && $order == 'ASC') { $content .=  'selected'; }
        $content .=  '>'.__( 'Name A->Z', 'doliconnect').'</option>
        <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'pg' => $page, 'field' => 'label', 'order' => 'DESC'), $_SERVER['REQUEST_URI']) ).'"';
        if ($field == 'label' && $order == 'DESC') { $content .=  'selected'; }
        $content .=  '>'.__( 'Name Z->A', 'doliconnect').'</option>
        <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'pg' => $page, 'field' => 'rowid', 'order' => 'DESC'), $_SERVER['REQUEST_URI']) ).'"';
        if ($field == 'rowid' && $order == 'DESC') { $content .=  'selected'; }
        $content .=  '>'.__( 'Novelties', 'doliconnect').'</option>
        <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'pg' => $page, 'field' => 'price', 'order' => 'ASC'), $_SERVER['REQUEST_URI']) ).'"';
        if ($field == 'price' && $order == 'ASC') { $content .=  'selected'; }
        $content .=  '>'.__( 'Lowest prices', 'doliconnect').'</option>
        <option value="'.esc_url( add_query_arg( array( 'search' =>isset($_GET['search'])?esc_attr($_GET['search']):null, 'pg' => $page, 'field' => 'price', 'order' => 'DESC'), $_SERVER['REQUEST_URI']) ).'"';
        if ($field == 'price' && $order == 'DESC') { $content .=  'selected'; }
        $content .=  '>'.__( 'Highest prices', 'doliconnect').'</option>
        </select></div>';
        $content .=  "</div><ul class='list-group list-group-flush'><li class='list-group-item'>";
        $content .=  "<div class='row'><div class='col-6 col-md-7'>";
        $content .= sprintf( _n( 'There is %s item', 'There are %s items', $count, 'doliconnect' ), number_format_i18n( $count ) );
        $content .=  '</div><div class="col-6 col-md-5">';
// old select
        $content .=  '</div></div></li>'; 

        if (isset($category->description) && !empty(doliproduct($category, 'description'))) {
        $content .=  '<li class="list-group-item"><small>'.doliproduct($category, 'description').'</small></li>';
        }

        if ( !isset($resultats->error) && $resultats != null ) {
          foreach ($resultats as $productid) {
            if ( doliCheckModules('discountprice') && isset($_GET['category']) && $_GET['category'] == 'discount' && !isset($_GET['product'])) { $productid = $productid->fk_product; }
            $content .=  apply_filters( 'doliproductlist', $productid);
          }
        } else {
          $content .=  "<li class='list-group-item list-group-item-light'><center>".__( 'No item currently on sale', 'doliconnect')."</center></li>";
        }
      } else {
        $content .=  "<li class='list-group-item list-group-item-white'><center><br><br><br><br><div class='align-middle'><i class='fas fa-bomb fa-7x fa-fw'></i><h4>".__( 'Oops! This category does not appear to exist', 'doliconnect' )."</h4></div><br>";
        $content .=  '<button type="button" class="btn btn-sm btn-outline-secondary border border-0 float-end" onclick="window.history.back()">'.__( 'Return', 'doliconnect').'</button>';
        $content .=  "<br><br><br></center></li>";
      }
      $content .=  '</ul>';

      if (isset($object)) { 
        $content .=  '<div class="card-body">';
        $content .=  doliPagination($object, $_SERVER['REQUEST_URI'], $page);
        $content .=  '</div>';
      }
      $content .=  doliCardFooter($object, 'product', $request);
      $content .=  '</div>';
    }
    return $content;
  } else {
    return $content;
  }
}

add_filter( 'the_content', 'dolishop_display');

//*****************************************************************************************

function dolidonation_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolidonation')) && !empty(doliconnectid('dolidonation')) ) {

doliconnect_enqueues();

$art200 = doliconst("DONATION_ART200",esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$art238 = doliconst("DONATION_ART238",esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$art835 = doliconst("DONATION_ART835", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
//$content .=  $shop;

if ( !doliCheckModules('commande') ) {
$content .=  "<div class='card shadow-sm'><div class='card-body'>";
$content .=  dolibug(__( 'Inactive module on Dolibarr', 'doliconnect'));
$content .=  "</div></div>";
} elseif (is_user_logged_in())  {

  $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
  $request = "/thirdparties/".$thirdparty->id;

$content .=  "<form action='".doliconnecturl('dolidonation')."' id='doliconnect-donationform' method='post' class='was-validated' enctype='multipart/form-data'>";

$content .=  doliloaderscript('doliconnect-donationform');

$content .=  "<div class='card shadow-sm'>";

if (isset($_GET["create"])) {
$content .=  doliuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'donation', doliCheckRights('societe', 'creer'));

$content .=  "<div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect')."</b></button></div>";

} else {
$content .=  "<div class='card-body'>"; 

$content .=  "<h5><i class='fas fa-donate fa-fw'></i> Don hors ligne</h5>";

//if ( $object->mode_reglement_code == 'CHQ') {

$chq = doliconst("FACTURE_CHQ_NUMBER",esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

$bank = callDoliApi("GET", "/bankaccounts/".$chq, null, dolidelay('constante'));

$content .=  "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), 'votre choix', __( 'donation', 'doliconnect'), $bank->proprio ).":</p><p><b>$bank->owner_address</b></p></div>";

//} 
//if ($object->mode_reglement_code == 'VIR') {

$vir = doliconst("FACTURE_RIB_NUMBER", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

$bank = callDoliApi("GET", "/bankaccounts/".$vir, null, dolidelay('constante'));

$content .=  "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), 'votre choix', __( 'donation', 'doliconnect') ).":";
$content .=  "<br><b>".__( 'Bank', 'doliconnect').": $bank->bank</b>";
$content .=  "<br><b>IBAN: $bank->iban</b>";
if ( ! empty($bank->bic) ) { $content .=  "<br><b>BIC/SWIFT: $bank->bic</b>";}
$content .=  "</p></div>";

//}
$content .=  "<h5><i class='fas fa-donate fa-fw'></i> ".__( 'Tax exemptions', 'doliconnect')."</h5>";
if (! empty($art200->value) || ! empty($art238->value) || ! empty($art835->value)) {
if (! empty($art200->value)) {
$content .=  __( 'DonationArt200', 'doliconnect');
}

if (! empty($art238->value)) {
$content .=  __( 'DonationArt238', 'doliconnect');
}

if (! empty($art835->value)) {
$content .=  __( 'DonationArt835', 'doliconnect');
}
} else {
$content .=  __( "You should't have tax exemptions", 'doliconnect');
}
$content .=  "</div>";
}

$content .=  doliCardFooter($request, 'thirdparty');
}
return $content;
} else {
return $content;
}

}

add_filter( 'the_content', 'dolidonation_display');

//*****************************************************************************************
 
function dolicart_display($content) {
global $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicart')) && !empty(doliconnectid('dolicart')) )  {

  doliconnect_enqueues();

  $time = current_time( 'timestamp', 1);
  $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

  if ( isset($_GET['module']) && ($_GET['module'] == 'orders' || $_GET['module'] == 'invoices') && isset($_GET['id']) && isset($_GET['ref']) ) {
    $request = "/".esc_attr($_GET['module'])."/".esc_attr($_GET['id'])."?contact_list=0";
    $module = esc_attr($_GET['module']);
    $id = $_GET['id']; 
    $object = callDoliApi("GET", $request, null, dolidelay('cart', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
  } else {
    $object = doliConnect('order', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    //$content .=  var_dump($object);
    if (isset($object->id)) {
      //$request = "/orders/".$object->id."?contact_list=0";
      //$object = callDoliApi("GET", $request, null, dolidelay('cart', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
      $module = 'orders';
      $id = $object->id;  
    }
  }

if ( !doliCheckModules('commande') ) {
  $content .=  "<div class='card shadow-sm'><div class='card-body'>";
  $content .=  dolibug(__( "Oops, Order's module is not available", "doliconnect"));
  $content .=  "</div></div>";
} else {
  if ( current_user_can('administrator') && !empty(get_option('doliconnectbeta')) ) {
    if ( isset($_GET['checkout']) && wp_verify_nonce( $_GET['checkout'], 'dolicart-'.$object->id.'-'.$current_user->ID) && ((isset($object->lines) && $object->lines != null && $object->statut == 0 && !isset($_GET['module']) ) || ( ($_GET['module'] == 'orders' && $object->billed != 1 ) || ($_GET['module'] == 'invoices' && $object->paye != 1) )) && $object->socid == $thirdparty->id ) {
    $content .=  'finish checkout';
    } else {
    $content .=  doliOffcanvasCart($current_user);
    }
  } else {
if ( isset($_GET['step']) && $_GET['step'] == 'validation' && isset($_GET['cart']) && wp_verify_nonce( $_GET['cart'], 'valid_dolicart-'.$object->id) && ((isset($object->lines) && $object->lines != null && $object->statut == 0 && !isset($_GET['module']) ) || ( ($_GET['module'] == 'orders' && $object->billed != 1 ) || ($_GET['module'] == 'invoices' && $object->paye != 1) )) && $object->socid == $thirdparty->id ) {

$data = [
  'paymentintent' => isset($_POST['paymentintent']) ? $_POST['paymentintent'] : null,
  'paymentmethod' => isset($_POST['paymentmethod']) ? $_POST['paymentmethod'] : null,
  'save' => isset($_POST['default']) ? $_POST['default'] : 0 ,
	];
$payinfo = callDoliApi("POST", "/doliconnector/pay/".$module."/".$object->id, $data, 0);
//$content .=  var_dump($payinfo);

$object = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, dolidelay('cart'));

$content .=  "<div class='card shadow-sm' id='cart-form'><div class='card-body'><center><h2>".__( 'Your order has been registered', 'doliconnect')."</h2>".__( 'Reference', 'doliconnect').": ".$object->ref;
$mode_reglement = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code:=:'".$object->mode_reglement_code."')", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if (isset($mode_reglement[0]->label)) $content .=  "<br>".__( 'Payment method', 'doliconnect').": ".$mode_reglement[0]->label."<br><br>";
$TTC = doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null);

if ( $object->statut == '1' && !isset($_GET['error']) ) {
if (!empty($object->billed) || !empty($object->paid)) {

$content .=  "<div class='alert alert-success' role='alert'><p>".__( 'Your payment has been registered', 'doliconnect');
if (isset($_GET['charge'])) "<br>".__( 'Reference', 'doliconnect').": ".$_GET['charge'];
$content .=  "</p>";

} elseif ( $object->mode_reglement_code == 'CHQ') {

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".$thirdparty->id."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$content .=  "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), $TTC, $object->ref, $listpaymentmethods->CHQ->proprio).":</p><p><b>".$listpaymentmethods->CHQ->owner_address."</b></p>";

} elseif ($object->mode_reglement_code == 'VIR') {

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".$thirdparty->id."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$content .=  "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), $TTC, $object->ref ).":";
$content .=  "<br><b>".__( 'Bank', 'doliconnect').": ".$listpaymentmethods->VIR->bank."</b>";
$content .=  "<br><b>IBAN: ".$listpaymentmethods->VIR->iban."</b>";
if ( ! empty($listpaymentmethods->VIR->bic) ) { $content .=  "<br><b>BIC/SWIFT : ".$listpaymentmethods->VIR->bic."</b>";}
$content .=  "</p>";

}

if ( (! empty(dolikiosk()) && empty($object->billed) && empty($object->paid) ) || $object->mode_reglement_code == 'LIQ') {
$content .=  "<br><p><b>".__( 'or go to reception desk', 'doliconnect')."</b></p>";
}


} else {

}

$content .=  "</div></div></div>";

} elseif ( isset($_GET['step']) && $_GET['step'] == 'info' && isset($_GET['cart']) && wp_verify_nonce( $_GET['cart'], 'valid_dolicart-'.$object->id) && isset($_POST['dolichecknonce']) && $_GET['cart'] == $_POST['dolichecknonce'] && isset($object->lines) && $object->lines != null && $object->socid == $thirdparty->id && !isset($object->resteapayer) && $object->statut == 0 && !isset($_GET['module']) && !isset($_GET['id']) ) {

if ( isset($_POST['update_thirdparty']) && $_POST['update_thirdparty'] == 'validation' ) {


                                   
} elseif ( isset($_POST['info']) && $_POST['info'] == 'validation' ) {


                                   
} elseif ( !$object->id > 0 && $object->lines == null ) {
wp_redirect(doliconnecturl('dolicart'));
exit;

}

} else {

if ( isset($_GET['step']) || isset($_GET['cart']) || isset($_GET['id']) || isset($_GET['module']) ) {
wp_safe_redirect(doliconnecturl('dolicart'));
exit;
} 

if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $percent = 100;
} elseif (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $percent = 50;
} else {
  $percent = 0;
}

$content .=  '<div class="position-relative m-4">
<div class="progress" style="height: 2px;">';
if (isset($_GET['stage']) && $_GET['stage'] == 'validation' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $content .=  '<div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>';
} elseif (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $content .=  '<div class="progress-bar bg-success" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>';
} elseif (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $content .=  '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>';
} else {
  $content .=  '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>';
}
$content .=  '</div>';
if (isset($_GET['stage']) && $_GET['stage'] == 'validation' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $content .=  '<button class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;"><i class="fas fa-shopping-bag"></i></button>
  <button class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;"><i class="fas fa-user-check"></i></button>
  <button class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;"><i class="fas fa-money-bill-wave"></i></button>';
} elseif (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $content .=  '<button class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;"><i class="fas fa-shopping-bag"></i></button>
  <button class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;"><i class="fas fa-user-check"></i></button>
  <button class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-primary rounded-pill" style="width: 2rem; height:2rem;" disabled><i class="fas fa-money-bill-wave"></i></button>';
} elseif (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) {
  $content .=  '<button class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-success rounded-pill" style="width: 2rem; height:2rem;"><i class="fas fa-shopping-bag"></i></button>
  <button class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-primary rounded-pill" style="width: 2rem; height:2rem;" disabled><i class="fas fa-user-check"></i></button>
  <button class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-light rounded-pill" style="width: 2rem; height:2rem;" disabled><i class="fas fa-money-bill-wave"></i></button>';
} else {
  $content .=  '<button class="position-absolute top-0 start-0 translate-middle btn btn-sm btn-primary rounded-pill" style="width: 2rem; height:2rem;" disabled><i class="fas fa-shopping-bag"></i></button>
  <button class="position-absolute top-0 start-50 translate-middle btn btn-sm btn-light rounded-pill" style="width: 2rem; height:2rem;" disabled><i class="fas fa-user-check"></i></button>
  <button class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-light rounded-pill" style="width: 2rem; height:2rem;" disabled><i class="fas fa-money-bill-wave"></i></button>';
}
$content .=  '</div>';

$content .=  "<ul class='nav bg-white nav-pills rounded nav-justified flex-column flex-sm-row' role='tablist'>";

$content .=  '<li id="li-tab-cart" class="nav-item"><a id="a-tab-cart" class="nav-link';
if (!isset($_GET['stage']) || !isset($object) || !is_object($object) || !isset($object->lines)) { $content .=  ' active'; }
$content .=  '" data-bs-toggle="pill" role="tab" href="#nav-tab-cart" aria-controls="nav-tab-cart" aria-selected="';
if (!isset($_GET['stage']) || !isset($object) || !is_object($object) || !isset($object->lines)) { $content .=  'true'; } else { $content .=  'false'; }
$content .=  '"><i class="fas fa-shopping-bag fa-fw"></i> '.__( 'Cart', 'doliconnect').'</a></li>';

$content .=  '<li id="li-tab-info" class="nav-item"><a id="a-tab-info" class="nav-link';
if (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  ' active'; }
elseif (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  ''; } else { $content .=  ' disabled'; }
$content .=  '" data-bs-toggle="pill" role="tab" href="#nav-tab-info" aria-controls="nav-tab-info" aria-selected="';
if (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  'true'; } else { $content .=  'false'; }
$content .=  '"><i class="fas fa-user-check fa-fw"></i> '.__( 'Coordinates', 'doliconnect').'</a></li>';

$content .=  '<li id="li-tab-pay" class="nav-item"><a id="a-tab-pay" class="nav-link';
if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  ' active'; } else { $content .=  ' disabled'; }
$content .=  '" data-bs-toggle="pill" role="tab" href="#nav-tab-pay" aria-controls="nav-tab-pay" aria-selected="';
if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  'true'; } else { $content .=  'false'; }
$content .=  '"><i class="fas fa-money-bill-wave fa-fw"></i> '.__( 'Payment', 'doliconnect').'</a></li>';
 
$content .=  "</ul><br><div id='tab-cart-content' class='tab-content'>";

$content .=  '<div class="tab-pane fade';
if (!isset($_GET['stage']) || !isset($object) || !is_object($object) || !isset($object->lines)) { $content .=  ' show active'; }
$content .=  '" role="tabpanel" id="nav-tab-cart">';

if ( isset($object->id) && $object->id > 0 && isset($object->lines) && $object->lines != null ) {  //&& $timeout>'0'                                                                                         
  //$content .=  "<div id='timer' class='text-center'><small>".sprintf( esc_html__('Your basket #%s is reserved for', 'doliconnect'), $object->id)." <span class='duration'></span></small></div>";
}

$content .=  "<div class='card shadow-sm' id='cart-form'><div class='card-header'>".__( 'Cart', 'doliconnect')."</div><ul id='doliline' class='list-group list-group-flush'>";
//$content .=  doliOffcanvasCart( $current_user, $object, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$content .=  doliline($object, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), 'cart');

if ( has_filter('mydoliconnectcartfilter') ) {
  $content .=  "<li class='list-group-item bg-light'>";
  $content .=  apply_filters('mydoliconnectcartfilter', $object);
  $content .=  "</li>";
}

if (isset($thirdparty->id) && $thirdparty->id > 0) {
  $outstandingamount = 0;
  if ($thirdparty->outstanding_limit) {
    $outstandinginvoice = callDoliApi("GET", "/thirdparties/".$thirdparty->id ."/outstandinginvoices?mode=customer", null, dolidelay('cart', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
    $content .=  "<li class='list-group-item bg-light'><b>".__( 'Amount outstanding', 'doliconnect').": ".doliprice($outstandinginvoice->opened, null, null)." ".__( 'out of', 'doliconnect')." ".doliprice($thirdparty->outstanding_limit, null, null)." ".__( 'allowed', 'doliconnect');
    $outstandingamount = $outstandinginvoice->opened-$thirdparty->outstanding_limit;
    if ($outstandingamount > 0) $content .=  " - ".__( "Your account is blocked, this order can't be processed. Please, contact us to pay overdue unpaid invoices.", 'doliconnect');
    $content .=  "</b></li>";
  }
}

$content .=  "</ul>"; 
 
if ( get_option('dolishop') || (!get_option('dolishop') && isset($object) && isset($object->lines) && $object->lines != null) ) {
$content .=  "<div class='card-body'><ul class='list-group list-group-horizontal-sm'>";
if ( get_option('dolishop') ) {
$content .=  "<a href='".doliconnecturl('dolishop')."' class='list-group-item list-group-item-action flex-fill'><center>".__( 'Continue shopping', 'doliconnect')."</center></a>";
} 
if ( isset($object) && is_object($object) && isset($object->lines) && $object->lines != null && ($thirdparty->id == $object->socid) ) { 
if ( $object->lines != null && $object->statut == 0 ) {
$content .=  "<button type='button' id='purgebtn_cart' name='purge_cart' value='purge_cart' class='list-group-item list-group-item-action flex-fill'><center>".__( 'Empty the basket', 'doliconnect')."</center></button>";
}
if ( $object->lines != null ) {
$content .=  "<button type='button' id='validatebtn_cart' name='validate_cart' value='validate_cart' class='list-group-item list-group-item-action list-group-item-warning flex-fill ' ";
if ($outstandingamount > 0 || (defined('dolilockcart') && !empty(constant('dolilockcart')))) $content .=  " disabled";
$content .=  "><center>".__( 'Process', 'doliconnect')."</center></button>";
}
}
$content .=  "</ul></div>";
}

$nonce = wp_create_nonce( 'dolicart-nonce');
$arr_params = array( 'stage' => 'informations', 'security' => $nonce);  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
$content .=  "<script>";
$content .=  "(function ($) {
$(document).ready(function(){
$('#purgebtn_cart, #validatebtn_cart').on('click',function(event){
  event.preventDefault();
  event.stopPropagation();
var actionvalue = $(this).val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".$nonce."',
            'case': actionvalue,
            'module': '".$module."',
            'id': '".$id."'
          }
        }).done(function(response) {
$(window).scrollTop(0); 
//console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'purge_cart')  {
document.getElementById('doliline').innerHTML = response.data.lines;
if (document.getElementById('dolitotal')) {
  document.getElementById('dolitotal').remove();
}
if (document.getElementById('purgebtn_cart')) {
  document.getElementById('purgebtn_cart').remove();
}
if (document.getElementById('validatebtn_cart')) {
  document.getElementById('validatebtn_cart').remove();
}
if (document.getElementById('DoliHeaderCartItems')) {
  document.getElementById('DoliHeaderCartItems').innerHTML = response.data.items;
}
if (document.getElementById('DoliFooterCartItems')) {  
  document.getElementById('DoliFooterCartItems').innerHTML = response.data.items;
}
if (document.getElementById('DoliCartItemsList')) {  
  document.getElementById('DoliCartItemsList').innerHTML = response.data.list;
}
if (document.getElementById('DoliWidgetCartItems')) {
  document.getElementById('DoliWidgetCartItems').innerHTML = response.data.items;      
} 
$('#a-tab-info').addClass('disabled');
} else if (actionvalue == 'validate_cart') {
//$('#a-tab-cart').removeClass('active');
//$('#a-tab-info').removeClass('disabled');
//$('#a-tab-info').addClass('active');    
//$('#nav-tab-cart').removeClass('show active');
//$('#nav-tab-info').addClass('show active');
//$('#nav-tab-cart').tab('dispose');
//$('#nav-tab-info').tab('show');
document.location = '".$return."';
}

console.log(response.data.message);
//$('#DoliconnectLoadingModal').modal('hide');
} 
        });
});
});
})(jQuery);";
$content .=  "</script>";

$content .=  doliCardFooter($object, 'doliconnector');
$content .=  "</div>";

$content .=  "</div>";

if ( is_user_logged_in() ) { 
$content .=  '<div class="tab-pane fade';
if (isset($_GET['stage']) && $_GET['stage'] == 'informations' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  ' show active'; }
$content .=  '" role="tabpanel" id="nav-tab-info">';

$content .=  "<div class='card'><ul class='list-group list-group-flush'>";

$content .=  "<li class='list-group-item list-group-item-action'><div class='row'><div class='col-12 col-md-6'><h6>".__( 'Billing address', 'doliconnect')."</h6><small class='text-muted'>";

$listcontact = callDoliApi("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".$thirdparty->id."&includecount=1&sqlfilters=(t.statut:=:1)", null, dolidelay('contact', true));

$contactbilling = array(); 
if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) { 
  foreach ($object->contacts_ids as $contact) {
    if (isset($contact->code) && 'BILLING' == $contact->code) {
      $contactbilling[] = $contact->id;
    }
  }
}

$content .=  '<div class="form-check"><input type="checkbox" id="billing-0" name="contact_billing" class="form-check-input" value="0" ';
if (empty($contactbilling)) $content .=  ' checked ';
$content .=  'disabled><label class="form-check-label" for="billing-0">'.doliaddress($thirdparty).'</label></div>';

if ( !isset($listcontact->error) && $listcontact != null ) {
  foreach ( $listcontact as $contact ) {
    $content .=  '<div class="form-check"><input type="checkbox" id="billing-'.$contact->id.'" name="contact_billing" class="form-check-input" value="'.$contact->id.'" ';
    if ( (isset($contact->default) && !empty($contact->default)) || in_array($contact->id, $contactbilling) ) { $content .=  "checked"; }
    $content .=  ' disabled><label class="form-check-label" for="billing-'.$contact->id.'">';
    $content .=  dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $content .=  '</label></div>';
  }
}
$content .=  "</small></div>";

$content .=  "<div class='col-12 col-md-6'><h6>".__( 'Shipping address', 'doliconnect')."</h6><small class='text-muted'>";

$contactshipping = array(); 
if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {
  foreach ($object->contacts_ids as $contact) {
    if (isset($contact->code) && 'SHIPPING' == $contact->code) {
      $contactshipping[] = $contact->id;
    }
  }
}

$content .=  '<div class="form-check"><input type="checkbox" id="shipping-0" name="contact_shipping" class="form-check-input" value="0" ';
if (empty($contactshipping)) $content .=  ' checked ';
$content .=  'disabled><label class="form-check-label" for="shipping-0">'.doliaddress($thirdparty).'</label></div>';

if ( !isset($listcontact->error) && $listcontact != null ) {
  foreach ( $listcontact as $contact ) {
    $content .=  '<div class="form-check"><input type="checkbox" id="shipping-'.$contact->id.'" name="contact_shipping" class="form-check-input" value="'.$contact->id.'" ';
    if ( (isset($contact->default) && !empty($contact->default)) || in_array($contact->id, $contactshipping) ) { $content .=  "checked"; }
    $content .=  ' disabled><label class="form-check-label" for="shipping-'.$contact->id.'">';
    $content .=  dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $content .=  '</label></div>';
  }
}
$content .=  "</small></div></div></li>";

if ( doliCheckModules('fraisdeport') ) {
  $listshipment = callDoliApi("GET", "/fraisdeport?modulepart=".$module."&id=1", null, dolidelay('order', true));
  //$content .=  var_dump($listshipment);
  if (!empty($object->shipping_method_id)) { $thirdparty->shipping_method_id = $object->shipping_method_id; }
  if ( !isset($listshipment->error) && $listshipment != null ) {
    $content .=  "<li class='list-group-item list-group-item-action'><h6>".__( 'Shipping method', 'doliconnect')."</h6>";
    $i=0;
    foreach ( $listshipment as $shipment ) {
      if (isset($object->total_ht) && $object->total_ht >= $shipment->palier && !isset($controlefdp[$shipment->fk_shipment_mode])) {
        $content .=  '<div class="form-check"><input type="radio" id="shipment-'.$shipment->id.'" name="shipping_method_id" class="form-check-input" value="'.$shipment->fk_shipment_mode.'" ';
        if ( empty($i) || $thirdparty->shipping_method_id == $shipment->fk_shipment_mode ) { $content .=  " checked"; }
        $content .=  '><label class="form-check-label" for="shipment-'.$shipment->id.'">'.dolishipmentmethods($shipment->fk_shipment_mode).' - '.doliprice($shipment, (empty(get_option('dolibarr_b2bmode'))?'price_ttc':'price_ht'));
        if (!empty($shipment->description)) //$content .=  ' <small>('.$shipment->description.')</small>';
        $content .=  '</label></div>';
        $controlefdp[$shipment->fk_shipment_mode] = true;
        $i++;
      }
    }
    $content .=  "</li>";
  }
} elseif ( doliCheckModules('expedition') ) {
  $listshipment = callDoliApi("GET", "/setup/dictionary/shipping_methods?limit=100&active=1&lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
  //$content .=  var_dump($listshipment);
  if (!empty($object->shipping_method_id)) { $thirdparty->shipping_method_id = $object->shipping_method_id; }
  if ( !isset($listshipment->error) && $listshipment != null ) {
    $content .=  "<li class='list-group-item list-group-item-action'><h6>".__( 'Shipping method', 'doliconnect')."</h6>";
    foreach ( $listshipment as $shipment ) {
      $content .=  '<div class="form-check"><input type="radio" id="shipment-'.$shipment->id.'" name="shipping_method_id" class="form-check-input" value="'.$shipment->id.'" ';
      if ( $thirdparty->shipping_method_id == $shipment->id ) { $content .=  " checked"; }
      $content .=  '><label class="form-check-label" for="shipment-'.$shipment->id.'">'.dolishipmentmethods($shipment->id);
      if (!empty($shipment->description)) //$content .=  ' <small>('.$shipment->description.')</small>';
      $content .=  '</label></div>';
      $controlefdp[$shipment->id] = true;
    }
    $content .=  "</li>";
  }
}

$note_public = isset($_POST['note_public']) ? $_POST['note_public'] : (isset($object->note_public) ? $object->note_public: null);

if ( empty(doliconst('MAIN_DISABLE_NOTES_TAB')) ) {
  $content .=  "<li class='list-group-item list-group-item-action'>";
  $content .=  '<div class="form-floating"><textarea class="form-control" placeholder="'.__( 'Message', 'doliconnect').'" id="note_public" name="note_public" style="height: 100px">'.$note_public.'</textarea>
  <label for="floatingTextarea"><i class="fas fa-comment fa-fw"></i> '.__( 'If you want to send us a message about your order, you can leave one here', 'doliconnect').'</label></div>';
  $content .=  "</li>";
} else {
  $content .=  '<input type="hidden" id="note_public" name="note_public" value="'.$note_public.'">';
}

$content .=  "</ul>";

$nonce = wp_create_nonce( 'dolicart-nonce');
$arr_params = array( 'stage' => 'payment', 'security' => $nonce);  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
$content .=  "<script>";
$content .=  "(function ($) {
$(document).ready(function(){
$('#infobtn_cart').on('click',function(event){
  event.preventDefault();
  event.stopPropagation();
//$('#DoliconnectLoadingModal').modal('show');
var actionvalue = $(this).val();
var note_public = $('#note_public').val();
var shipping_method_id = $('input:radio[name=shipping_method_id]:checked').val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'case': actionvalue,
            'module': '".$module."',
            'id': '".$id."',
            'shipping_method_id': shipping_method_id,
            'note_public': note_public
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'info_cart') {
//$('#a-tab-info').removeClass('active');
//$('#a-tab-pay').removeClass('disabled');
//$('#a-tab-pay').addClass('active');    
//$('#nav-tab-info').removeClass('show active');
//$('#nav-tab-pay').addClass('show active');
//$('#nav-tab-info').tab('dispose');
//$('#nav-tab-pay').tab('show'); 
document.location = '".$return."';                                                                            
}

console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
$content .=  "</script>";

$content .=  "<div class='card-body'><div class='d-grid gap-2'><button type='button' id='infobtn_cart' name='info_cart' value='info_cart'  class='btn btn-secondary'>".__( 'Validate', 'doliconnect')."</button></div></div>";
$content .=  doliCardFooter($object, 'doliconnector');
$content .=  "</div>";

$content .=  "</div>";

$content .=  '<div class="tab-pane fade';
if (isset($_GET['stage']) && $_GET['stage'] == 'payment' && isset($object) && is_object($object) && isset($object->lines) && $object->lines != null) { $content .=  ' show active'; }
$content .=  '" role="tabpanel" id="nav-tab-pay">';

if ( doliversion('11.0.0') ) {
$array = array();
if (isset($_GET["payment_intent"])) $array["payment_intent"] = $_GET["payment_intent"];
if (isset($_GET["payment_intent_client_secret"])) $array["payment_intent_client_secret"] = $_GET["payment_intent_client_secret"];
if (isset($_GET["redirect_status"])) $array["redirect_status"] = $_GET["redirect_status"];
$content .=  doliconnect_paymentmethods($object, esc_attr($module), $return, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), $array);
} else {
$content .=  __( "It seems that your version of Dolibarr and/or its plugins are not up to date!", "doliconnect");
}

$content .=  "</div>";
}

$content .=  "</div>";
}
}
    }
    return $content;
  } else {
    return $content;
  }
}

add_filter( 'the_content', 'dolicart_display');

//*****************************************************************************************

function doliagenda_display($content) {
  global $current_user;
  
  if ( in_the_loop() && is_main_query() && is_page(doliconnectid('doliagenda')) && !empty(doliconnectid('doliagenda')) ) {
  
    doliconnect_enqueues();
    
    $current_offset = get_option('gmt_offset');
    $tzstring = get_option('timezone_string');
    $check_zone_info = true;
    if ( false !== strpos($tzstring,'Etc/GMT') )
      $tzstring = '';
    
    if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
      $check_zone_info = false;
      if ( 0 == $current_offset )
        $tzstring = 'UTC+0';
      elseif ($current_offset < 0)
        $tzstring = 'UTC' . $current_offset;
      else
        $tzstring = 'UTC+' . $current_offset;
    }
    date_default_timezone_set($tzstring);

    $request = "/setup/dictionary/event_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.type:!=:'systemauto')";
    $listfo = callDoliApi("GET", $request, null, dolidelay('agenda', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    $sqlfilter = null;
    if ( !isset($listfo->error) && $listfo != null ) {
      $sqlfilter .= "and(t.fk_action:in:";
      $typearray = array();
      $i = 0;
      foreach ($listfo as $postlist) {        
        if (!empty($i)) $sqlfilter .= ",";
        $sqlfilter.= "'". $postlist->id."'";
        $typearray[] = $postlist->id;
        $i++;
      }
      $sqlfilter .= ")";
    }

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) {  
      $request = "/agendaevents/".esc_attr($_GET['id']);
      $agendafo = callDoliApi("GET", $request, null, dolidelay('agenda', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
      //$content .=  $contractfo;
    }
  
    if ( !isset($agendafo->error) && isset($_GET['id']) && isset($_GET['id']) && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-agenda-'.$agendafo->id) && in_array($agendafo->type_id, $typearray) ) {
      $content .=  '<div class="card shadow-sm"><div class="card-header">'.$agendafo->label.'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.doliconnecturl('doliagenda').'"><i class="fas fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body">';
     
      $content .=  $agendafo->note_private;
      
      $content .=  '</div>';
      $content .=  doliCardFooter($agendafo, 'agenda');
      $content .=  '</div>';
    } else {
      $limit=12;
      $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
      $request= "/agendaevents?sortfield=t.datep&sortorder=ASC&limit=".$limit."&page=".$page."&sqlfilters=(t.datep2%3A%3E%3D%3A'".date("Ymd")."')".$sqlfilter."&pagination_data=true";
      $object = callDoliApi("GET", $request, null, dolidelay('agenda', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
      if ( doliversion('21.0.0') && isset($object->data) ) { $listagenda = $object->data; } else { $listagenda = $object; }

      $content .=  "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

      if ( !isset($listagenda->error) && $listagenda != null ) {
        foreach ($listagenda as $postagenda) {
          $nonce = wp_create_nonce( 'doli-agenda-'.$postagenda->id);
          $arr_params = array( 'id' => $postagenda->id, 'security' => $nonce);  
          $return = esc_url( add_query_arg( $arr_params, doliconnecturl('doliagenda')) );

          $content .=  "<a href='".$return."' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'>";
          $content .=  "<div><i class='fa-solid fa-calendar-days fa-3x fa-fw'></i></div><div>";                                                                                
          $content .=  "<h6 class='my-0'>$postagenda->label</h6><small class='text-muted'>$postagenda->location ".date('d/m/Y',$postagenda->datep)." ".date('d/m/Y',$postagenda->datef)."</small>";
          $content .=  "</div></a>";
        }
      } else {
        $content .=  "<li class='list-group-item list-group-item-light'><center>".__( 'No event', 'doliconnect')."</center></li>";
      }
      $content .=  "</ul><div class='card-body'>";
      $content .=  doliPagination($object, $_SERVER['REQUEST_URI'], $page);
      $content .=  "</div>";
      $content .=  doliCardFooter($object, 'agenda');
      $content .=  "</div>";
    }
    return $content;
  } else {
    return $content;
  }
}
  
add_filter( 'the_content', 'doliagenda_display');

//*****************************************************************************************
?>