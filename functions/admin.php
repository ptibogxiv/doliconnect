<?php

function doliconnect_admin_notice_error() {
global $wpdb;

$dolibarr = CallAPI("GET", "/status", null, 10 * MINUTE_IN_SECONDS);
if ( is_object($dolibarr) && version_compare($dolibarr->success->dolibarr_version, '9.0.0', '<') && !defined("DOLIBUG") ) {
$class = 'notice notice-error ';  //is-dismissible
$message = __( 'It seems that your version of Dolibarr and/or its plugins are not up to date!', 'doliconnect' );

printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
} 
}
add_action( 'admin_notices', 'doliconnect_admin_notice_error');
add_action( 'network_admin_notices', 'doliconnect_admin_notice_error');

function doliconnect_admin_page1() {
add_menu_page(__( 'Doliconnect settings', 'doliconnect' ), __( 'Doliconnect', 'Doliconnect' ), 'manage_options', 'ptibogxiv_management_page', 'ptibogxiv_management_page', plugins_url( 'doliconnect/images/icon_16.png' ));
add_submenu_page(__( 'Doliconnect settings', 'doliconnect' ), "Management", "Management", 'manage_options', 'ptibogxiv_management_page', 'ptibogxiv_management_page');
add_submenu_page('ptibogxiv_management_page', "Key and settings", "Key and settings", 'manage_options', 'doliconnect_network_page', 'doliconnect_network_page');
}

function doliconnect_admin_page2() {
add_menu_page(__( 'Doliconnect', 'Doliconnect' ), __( 'Doliconnect', 'Doliconnect' ), 'manage_options', 'doliconnect_network_page', 'doliconnect_network_page', plugins_url( 'doliconnect/images/icon_16.png' ));
add_submenu_page('doliconnect_network_page', "Management", "Management", 'manage_options', 'doliconnect_network_page', 'doliconnect_network_page');
}

function doliconnect_admin_page3() {
add_menu_page(__( 'Doliconnect settings', 'doliconnect' ),__( 'Doliconnect', 'Doliconnect' ), 'manage_options', 'ptibogxiv_management_page', 'ptibogxiv_management_page', plugins_url( 'doliconnect/images/icon_16.png' ));
add_submenu_page(__( 'Doliconnect settings', 'doliconnect' ), "Management", "Management", 'manage_options', 'ptibogxiv_management_page', 'ptibogxiv_management_page');
}
function doliconnect_admin_page4() {
add_users_page( 'doliboard', "Gestion des admins", 'manage_options', 'doliconnect_admin_page', 'doliconnect_admin_page');
}

if ( is_multisite() ) {
add_action( 'network_admin_menu', 'doliconnect_admin_page2' );
add_action( 'admin_menu', 'doliconnect_admin_page3' );
add_action( 'admin_menu', 'doliconnect_admin_page4' );
}
else {
add_action( 'admin_menu', 'doliconnect_admin_page1' );
add_action( 'admin_menu', 'doliconnect_admin_page4' );
}

function doliconnect_admin_page() {
echo '<div class="wrap">';
echo '<h2>Gestion des admins</h2>';
$result = count_users();
echo 'There are ', $result['total_users'], ' total users';
$total[]=0;
foreach($result['avail_roles'] as $role => $count){
if ($role == 'editor' OR $role == 'administrator') {$total[$role]=$count;}
    echo ', ', $count, ' are ', $role, '';}
echo "<form action='' method='post'>";
for($i=1;$i<=array_sum($total);$i++){ 
echo "<br />$i ";
if ($_REQUEST['doliboard_'.$i]) {update_usermeta($_REQUEST['doliboard_'.$i], 'doliboard_'.get_current_blog_id(), $i );}
$usera = reset(
 get_users(
  array(
   'meta_key' => 'doliboard_'.get_current_blog_id(),
   'meta_value' => ''.$i.'',
   'number' => 1,
   'count_total' => false
  )
 )
);
if ( ! empty( $usera ) ) {
$USERID[$i]=$usera->ID;
} 

if ($i<=$total['administrator']){ echo "admin"; 
echo "<SELECT name='doliboard_".$i."'>";
$args = array( 
'blog_id'      => $GLOBALS['blog_id'],
'role'         => 'administrator',
'meta_key' => 'first_name',
'orderby' => 'meta_value',
'order'        => 'ASC',
);
}
elseif ($i>$total['administrator']){ echo "editeur";
echo "<select name='doliboard_".$i."'>";
$args = array( 
'blog_id'      => $GLOBALS['blog_id'],
'role'         => 'editor',
'meta_key' => 'first_name',
'orderby' => 'meta_value',
'order'        => 'ASC',
);
}
$user_query = new WP_User_Query( $args );

if ( ! empty( $user_query->results ) ) { 
echo "<option value=''>no one</option>";
foreach ( $user_query->results as $user ) {
echo "<option value='".$user->ID."' ";
if ($USERID[$i]==$user->ID) {echo " selected";}
echo ">" . esc_html( $user->user_firstname ) . ' ' . esc_html( $user->user_lastname ) . "</option>";
}}
echo "</select>";
if ($_REQUEST['doliboard_title_'.$i]) {update_option('doliboard_title_'.$i, $_REQUEST['doliboard_title_'.$i]);}
if ($_REQUEST['doliboard_email_'.$i]) {update_option('doliboard_email_'.$i, $_REQUEST['doliboard_email_'.$i]);}
echo "<input type='text' id='doliboard_title_".$i."' name='doliboard_title_".$i."'  value='".get_option('doliboard_title_'.$i.'')."' placeholder='Fonction'> <input type='text' id='doliboard_email_".$i."' name='doliboard_email_".$i."' placeholder='Email de fonction' value='".get_option('doliboard_email_'.$i.'')."' >";
}
echo "<br /><br /><input type='submit' name='activate_license' value='Mettre a jour' class='button-primary' /></form>";
}

function doliconnect_network_page() {
    echo '<div class="wrap">';
    echo '<h2>Key and settings</h2>';

/*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
    
if ( add_site_option( 'license_key_doliconnect-pro', sanitize_text_field($_REQUEST['license_key_doliconnect-pro'])) ) {
} else {
update_site_option('license_key_doliconnect-pro', sanitize_text_field($_REQUEST['license_key_doliconnect-pro'])); 
}
if ( add_site_option( 'dolibarr_public_url', esc_url_raw($_REQUEST['dolibarr_public_url'])) ) {
} else {
update_site_option('dolibarr_public_url', esc_url_raw($_REQUEST['dolibarr_public_url']) );
} 
if ( add_site_option( 'dolibarr_private_key', sanitize_text_field($_REQUEST['dolibarr_private_key'])) ) {
} else {
update_site_option('dolibarr_private_key', sanitize_text_field($_REQUEST['dolibarr_private_key'])); 
}
if ( add_site_option( 'doliconnect_mode', sanitize_text_field($_REQUEST['doliconnect_mode'])) ) {
} else {
update_site_option('doliconnect_mode', sanitize_text_field($_REQUEST['doliconnect_mode']));
}
if ( add_site_option( 'doliconnect_login', sanitize_text_field($_REQUEST['doliconnect_login'])) ) {
} else {
update_site_option('doliconnect_login', sanitize_text_field($_REQUEST['doliconnect_login']));
}       
if ( add_site_option( 'dolibarr_entity', sanitize_text_field($_REQUEST['dolibarr_entity'])) ) {
} else {
delete_site_option('dolibarr_entity');
}

    }
    /*** End of license activation ***/
    
    /*** End of sample license deactivation ***/    
		?>       
<div id="<?php echo $id; ?>" class="postbox">
<div class="inside">

<?php
$link='https://www.ptibogxiv.net/?update_action=get_metadata&slug=doliconnect&license='.get_site_option('license_key_doliconnect-pro');
?> 

    <p><a href='https://github.com/ptibogxiv/doliconnector/releases' target='_blank'>Télécharger le module doliconnector</a> pour Dolibarr afin de faire fonctionner ce module</p>
    <form action="" method="post">
        <table class="form-table" width="100%">
            <tr>
                <th style="width:150px;"><label for="license_key_doliconnect-pro">License Doliconnect</label></th>
                <td ><input class="regular-text" type="text" id="license_key_doliconnect-pro" name="license_key_doliconnect-pro" value="<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
echo get_option('license_key_doliconnect-pro');?> " <?php } else { echo "";?>" disabled <?php } ?> > <b>PRO</b> 
                </td>
            </tr>
                      
            
            <tr>
                <th style="width:150px;"><label for="dolibarr_public_url">DOLIBARR URL</label></th>
                <td ><input class="regular-text" type="text" id="dolibarr_public_url" name="dolibarr_public_url"  value="<?php echo get_site_option('dolibarr_public_url'); ?>" required>/api/index.php<br>ex: https://dolibarr.example.com</td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_private_key">DOLIBARR REST API USER KEY</label></th>
                <td ><input class="regular-text" type="text" id="dolibarr_private_key" name="dolibarr_private_key"  value="<?php echo get_site_option('dolibarr_private_key'); ?>" required></td>
            </tr>
<?php
$infodoliconnect = CallAPI("GET", "/status", null, 5 * MINUTE_IN_SECONDS);
?>          <tr>          
                <th style="width:150px;"><label for="status">Status Dolibarr</label></th>
                <td>
<?php if ( is_object($infodoliconnect) ) {
?>                 
                <p class="text-success">Status: <?php echo $infodoliconnect->success->code; ?></p>
                <p class="text-success">Version: <?php echo $infodoliconnect->success->dolibarr_version; ?></p>
                <p class="text-success">Access Locked: <?php echo $infodoliconnect->success->access_locked; ?></p>
<?php } else { ?><p class="text-danger">Offline</p><?php } ?></td>
            </tr>
<?php if ( is_multisite() ) { ?>
            <tr>
                <th style="width:150px;"><label for="doliconnect_mode">Doliconnect mode</label></th>
                <td ><select id=doliconnect_mode" name="doliconnect_mode">
                <option value="multi" <?php if (get_site_option('doliconnect_mode')=='multi') { ?>selected<?php } ?>> Multisite to multi Dolibarr</option>
                <option value="one" <?php if (get_site_option('doliconnect_mode')=='one') { ?>selected<?php } ?>> Multisite to one Dolibarr</option>
                </select></td>
            </tr><?php } ?>
            <tr>
                <th style="width:150px;"><label for="dolibarr_login">Wordpress Login Page</label></th>
                <td ><?php echo site_url(); ?>/<input class="regular-text" type="text" id="dolibarr_login" name="doliconnect_login"  value="<?php echo get_site_option('doliconnect_login'); ?>" required><br>ex: wp-login.php (wordpress default)</td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_entity">Personalize entity</label></th>
                <td ><input name="dolibarr_entity" type="checkbox" id="dolibarr_entity" value="1" <?php checked('1', get_site_option('dolibarr_entity')); ?> /> permettre de personnaliser les entités liés par defaut entité-wordpress == entité-dolibarr</td>
            </tr>    
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="Activate" class="button-primary" />
        </p>
    </form>     				
    </div>
			</div>
    <?php
}


function ptibogxiv_management_page() {
echo '<DIV class="wrap">';
echo '<h2>'.__( 'Doliconnect settings', 'doliconnect' ).'</h2>';  
?>
	<div id="ptibogxiv_management_page" class="postbox">
	<div class="inside">
<?php

    if (isset($_REQUEST['doliconnect_settings'])) {            
            update_option('dolicart', sanitize_text_field($_REQUEST['dolicart']));
if ($_REQUEST['users_can_register']==1){
update_option('users_can_register', sanitize_text_field($_REQUEST['users_can_register']));
}else {
delete_option('users_can_register');}
if ($_REQUEST['doliloginmodal']==1){
update_option('doliloginmodal', sanitize_text_field($_REQUEST['doliloginmodal']));
}else {
delete_option('doliloginmodal');}
if ($_REQUEST['doliconnectbeta']==1){
update_option('doliconnectbeta', sanitize_text_field($_REQUEST['doliconnectbeta']));
}else {
delete_option('doliconnectbeta');}
if ($_REQUEST['doliconnectrestrict']==1){
update_option('doliconnectrestrict', sanitize_text_field($_REQUEST['doliconnectrestrict']));
}else {
delete_option('doliconnectrestrict');}
if (get_site_option('dolibarr_entity')=='1' && is_super_admin()) { 
if ($_REQUEST['dolibarr_entity'] > 0 ){
update_option('dolibarr_entity', sanitize_text_field($_REQUEST['dolibarr_entity']));
}else {
delete_option('dolibarr_entity');} 
}
if ($_REQUEST['doliconnect_disablepro']>0){
update_option('doliconnect_disablepro', sanitize_text_field($_REQUEST['doliconnect_disablepro']));
}else {
delete_option('doliconnect_disablepro');} 
if ($_REQUEST['doliconnect_facebook']>0){
update_option('doliconnect_facebook', sanitize_text_field($_REQUEST['doliconnect_facebook']));
}else {
delete_option('doliconnect_facebook');} 
if ($_REQUEST['doliconnect_google']>0){
update_option('doliconnect_google', sanitize_text_field($_REQUEST['doliconnect_google']));
}else {
delete_option('doliconnect_google');}                       
            update_option('dolicheckout', sanitize_text_field($_REQUEST['dolicheckout']));          
            update_option('doliaccount', sanitize_text_field($_REQUEST['doliaccount']));
            update_option('doliaccountinfo', sanitize_text_field($_REQUEST['doliconnect_login_info']));
            update_option('doliticket', sanitize_text_field($_REQUEST['doliticket']));
            update_option('doliclassifieds', sanitize_text_field($_REQUEST['doliclassifieds'])); 
            update_option('doliconnect_ipkiosk', array_values(array_filter(array_map('trim', explode(PHP_EOL, $_REQUEST['doliconnect_ipkiosk'])))));             
            update_option('wp_page_for_privacy_policy', sanitize_text_field($_REQUEST['wp_page_for_privacy_policy']));
            update_option('dolishop', sanitize_text_field($_REQUEST['dolishop']));             
            update_option('doliconnect_social_facebook', sanitize_text_field($_REQUEST['doliconnect_social_facebook']));
            update_option('doliconnect_social_twitter', sanitize_text_field($_REQUEST['doliconnect_social_twitter']));
            update_option('doliconnect_social_instagram', sanitize_text_field($_REQUEST['doliconnect_social_instagram']));
            update_option('doliconnect_social_youtube', sanitize_text_field($_REQUEST['doliconnect_social_youtube']));
            update_option('doliconnect_social_github', sanitize_text_field($_REQUEST['doliconnect_social_github']));
            update_option('doliconnect_social_linkedin', sanitize_text_field($_REQUEST['doliconnect_social_linkedin']));
            update_option('doliconnect_social_skype', sanitize_text_field($_REQUEST['doliconnect_social_skype']));                        
            update_option('dolicontact', sanitize_text_field($_REQUEST['dolicontact']));            
            update_option('doliconnect_captcha_sitekey', sanitize_text_field($_REQUEST['doliconnect_captcha_sitekey']));   
            update_option('doliconnect_captcha_secretkey', sanitize_text_field(['doliconnect_captcha_secretkey']));
            update_option('doliconnect_facebook_key', sanitize_text_field($_REQUEST['doliconnect_facebook_key']));
            update_option('doliconnect_facebook_secret', sanitize_text_field($_REQUEST['doliconnect_facebook_secret']));     
            update_option('doliconnect_google_key', sanitize_text_field($_REQUEST['doliconnect_google_key']));
            update_option('doliconnect_google_secret', sanitize_text_field($_REQUEST['doliconnect_google_secret']));                       
    }   
    ?>
    <form action="" method="post">
        <table class="form-table" width="100%">
            <tr>
                <th style="width:150px;"><label for="doliloginmodal">Modal login</label></th>
                <td ><input name="doliloginmodal" type="checkbox" id="doliloginmodal" value="1" <?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
checked('1', get_option('doliloginmodal')); } else { ?> disabled <?php } ?> > <b>PRO</b>            
                </td>
            </tr> 
            <tr>
                <th style="width:150px;"><label for="doliconnectbeta">Mode Beta</label></th>
                <td ><input name="doliconnectbeta" type="checkbox" id="doliconnectbeta" value="1" <?php checked('1', get_option('doliconnectbeta')); ?> /> Active beta functions / May be instable or not functionnal</td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnectbeta">Mode site restreint</label></th>
                <td ><input name="doliconnectrestrict" type="checkbox" id="doliconnectrestrict" value="1" <?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
checked('1', get_option('doliconnectrestrict')); } else { ?> disabled <?php } ?> > <b>PRO</b></td>
            </tr>               
<?php if (get_site_option('dolibarr_entity')=='1' && is_super_admin()) { ?>                  
            <tr>
                <th style="width:150px;"><label for="dolibarr_register">Personnaliser l'entite Dolibarr</label></th>
                <td ><input class="regular-text" name="dolibarr_entity" type="text" id="dolibarr_entity" value="<?php echo get_option('dolibarr_entity'); ?>"></td>
            </tr>
<?php } ?>              
            <tr>
                <th style="width:150px;"><label for="dolibarr_register">dolibarr_register</label></th>
                <td ><input name="users_can_register" type="checkbox" id="users_can_register" value="1" <?php checked('1', get_option('users_can_register')); ?> /> <?php _e('Anyone can register') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnect_disablepro">dolibarr_disablepro</label></th>
                <td ><input name="doliconnect_disablepro" type="checkbox" id="doliconnect_disablepro" value="1" <?php checked('1', get_option('doliconnect_disablepro')); ?> /> disable pro account form</td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnect_ipkiosk">IP mode kiosque</label></th>
                <td ><textarea rows="6" cols="75" name="doliconnect_ipkiosk" type="text" id="doliconnect_ipkiosk"><?php if ( ! empty(get_option('doliconnect_ipkiosk')) ) { echo implode("\n",get_option('doliconnect_ipkiosk'));} ?></textarea><br>IP actuelle: <?php echo $_SERVER['REMOTE_ADDR']; ?><br>mettre une IP par ligne sans virgule ni espace</td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_account">dolibarr_account</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliaccount', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option('doliaccount') 
);
           wp_dropdown_pages($args); ?> [doliaccount] [dolilogin]
<br><br><textarea name="doliconnect_login_info" placeholder="message d'info sur la page de connexion" class="form-control" id="exampleFormControlTextarea1" rows="3" cols="75"><?php echo get_option('doliaccountinfo'); ?></textarea>           
<br><br>Google captcha sitekey<input class="regular-text" type="text" id="dolibarr_login" name="doliconnect_captcha_sitekey"  value="<?php echo get_option('doliconnect_captcha_sitekey'); ?>" >           
<br>Google captcha secretkey<input class="regular-text" type="text" id="dolibarr_login" name="doliconnect_captcha_secretkey"  value="<?php echo get_option('doliconnect_captcha_secretkey'); ?>" >     
           </td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_cart">dolibarr_cart</label></th>
                <td >
<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) { ?>
<?php 
           $args = array(
    'name' => 'dolicart', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolicart') 
);
           wp_dropdown_pages($args); ?>
<?php } else { ?>
<select name="dolicart" type="checkbox" id="dolicart" value="0" disabled><option> --- </option></select>
<?php } ?> [dolicart] <b>PRO</b></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_shop">dolibarr_shop</label></th>
                <td >
<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) { ?>
<?php 
           $args = array(
    'name' => 'dolishop', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolishop') 
);
           wp_dropdown_pages($args); ?>
<?php } else { ?>
<select name="dolishop" type="checkbox" id="dolishop" value="0" disabled><option> --- </option></select>
<?php } ?> [dolishop] <b>PRO</b></td>
            </tr>            
            <tr>
                <th style="width:150px;"><label for="dolibarr_contact">dolibarr_contact</label></th>
                <td >
           <?php 
           $args = array(
    'name' => 'dolicontact', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolicontact') 
);
           wp_dropdown_pages($args); ?> [dolicontact] </td>
            </tr>              
             <tr>
                <th style="width:150px;"><label for="dolibarr_legacy">dolibarr_legacy</label></th>
                <td ><?php 
           $args = array(
    'name' => 'wp_page_for_privacy_policy', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option( 'wp_page_for_privacy_policy' ) 
);
           wp_dropdown_pages($args); ?> [dolilegacy]</td>
            </tr>
<?php            
if (is_plugin_active( 'doliconnect-ticket/doliconnect-ticket.php' ) ) { ?>
            <tr>
                <th style="width:150px;"><label for="dolibarr_ticket">dolibarr_ticket</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliticket', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option('doliticket') 
);
           wp_dropdown_pages($args); ?> [doliticket]</td>
            </tr> 
<?php }            
if (is_plugin_active( 'doliconnect-classifieds/doliconnect-classifieds.php' ) ) { ?>                        <tr>
                <th style="width:150px;"><label for="dolibarr_classified">dolibarr_classified</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliclassifieds', 
    'show_option_none' => __( '— Select —' ), 
    'option_none_value' => '0', 
    'selected' => get_option('doliclassifieds') 
);
           wp_dropdown_pages($args); ?> [doliclassifieds]</td>
            </tr>
<?php } ?> 
        <tr>
            <th style="width:100px;"><label for="doliconnect_sociallink">Social link</label></th>
            <td>
            <input class="regular-text" type="text" id="doliconnect_social_facebook" name="doliconnect_social_facebook"  value="<?php echo get_option('doliconnect_social_facebook'); ?>" >Facebook<br>
            <input class="regular-text" type="text" id="doliconnect_social_twitter" name="doliconnect_social_twitter"  value="<?php echo get_option('doliconnect_social_twitter'); ?>" >Twitter<br>
            <input class="regular-text" type="text" id="doliconnect_social_instagram" name="doliconnect_social_instagram"  value="<?php echo get_option('doliconnect_social_instagram'); ?>" >Instagram<br>
            <input class="regular-text" type="text" id="doliconnect_social_youtube" name="doliconnect_social_youtube"  value="<?php echo get_option('doliconnect_social_youtube'); ?>" >Youtube<br>
            <input class="regular-text" type="text" id="doliconnect_social_github" name="doliconnect_social_github"  value="<?php echo get_option('doliconnect_social_github'); ?>" >Github<br>
            <input class="regular-text" type="text" id="doliconnect_social_linkedin" name="doliconnect_social_linkedin"  value="<?php echo get_option('doliconnect_social_linkedin'); ?>" >Linkedin<br>            
            <input class="regular-text" type="text" id="doliconnect_social_skype" name="doliconnect_social_skype"  value="<?php echo get_option('doliconnect_social_skype'); ?>" >Skype<br>            
            </td>
        </tr>
        <tr>
            <th style="width:100px;"><label for="doliconnect_sociallogin">Social login</label></th>
            <td><input name="doliconnect_facebook" type="checkbox" id="doliconnect_facebook" value="1" <?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
checked('1', get_option('doliconnect_facebook')); } else { ?> disabled <?php } ?> /> Facebook <b>PRO</b><br>
            Key<input class="regular-text" type="text" id="doliconnect_facebook_key" name="doliconnect_facebook_key"  value="<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
echo get_option('doliconnect_facebook_key');?> " <?php } else { echo "";?>" disabled <?php } ?> ><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_facebook_secret" name="doliconnect_facebook_secret"  value="<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
echo get_option('doliconnect_facebook_secret');?> " <?php } else { echo "";?>" disabled <?php } ?> ><br> 

            <input name="doliconnect_google" type="checkbox" id="doliconnect_google" value="1" <?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
checked('1', get_option('doliconnect_google')); } else { ?> disabled <?php } ?> /> Google <b>PRO</b><br>      
            Key<input class="regular-text" type="text" id="doliconnect_google_key" name="doliconnect_google_key" value="<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
echo get_option('doliconnect_google_key');?> " <?php } else { echo "";?>" disabled <?php } ?> ><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_google_secret" name="doliconnect_google_secret"  value="<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
echo get_option('doliconnect_google_secret');?> " <?php } else { echo "";?>" disabled <?php } ?> ><br>  
           
            </td>
        </tr>     
        </table>
        <p class="submit">
            <input type="submit" name="doliconnect_settings" value="Update" class="button-primary" />
        </p>
    </form>
    </div></div>
</div>
<?php
}


?>
