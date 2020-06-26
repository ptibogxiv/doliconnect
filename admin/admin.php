<?php

function doliconnect_admin_notice_error() {

if ( ! doliversion(constant("DOLIBARR_LEGAL_VERSION"))) {
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

if (isset($_REQUEST['doliboard'])) {
foreach ( $_REQUEST['doliboard'] as $id => $value ) {
echo " $id $value <br />";
if ( $value == '0' ) { delete_user_meta($value,'doliboard_4');
echo 'delete'; } else {
update_user_meta($value,'doliboard_4', $i);}
if ($_REQUEST['doliboard_title_'.$i]) { update_option('doliboard_title_'.$i, $_REQUEST['doliboard_title_'.$i]); } 
if ($_REQUEST['doliboard_email_'.$i]) { update_option('doliboard_email_'.$i, $_REQUEST['doliboard_email_'.$i]); }
}
} 

$total[]=0;
foreach($result['avail_roles'] as $role => $count){
if ($role == 'editor' OR $role == 'administrator') {$total[$role]=$count;}
    echo ', ', $count, ' are ', $role, '';}
echo "<form action='' method='post'>";
for($i=1;$i<=array_sum($total);$i++){ 
echo "<br />$i ";

$usera = reset(
 get_users(
  array(
   'meta_key' => 'doliboard_'.get_current_blog_id().'',
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
echo "<SELECT name='doliboard[".$i."]'>";
$args = array( 
'blog_id'      => $GLOBALS['blog_id'],
'role'         => 'administrator',
'meta_key' => 'first_name',
'orderby' => 'meta_value',
'order'        => 'ASC',
);
}
elseif ($i>$total['administrator']){ echo "editeur";
echo "<select name='doliboard[".$i."]'>";
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
echo "<option value='0'>no one</option>";
foreach ( $user_query->results as $user ) {
echo "<option value='".$user->ID."' ";
if ($USERID[$i]==$user->ID) {echo " selected";}
echo ">" . esc_html( $user->user_firstname ) . ' ' . esc_html( $user->user_lastname ) . "</option>";
}}
echo "</select>";

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
if ( add_site_option( 'doliconnect_login', sanitize_text_field($_REQUEST['doliconnect_login'])) ) {
} else {
update_site_option('doliconnect_login', sanitize_text_field($_REQUEST['doliconnect_login']));
}       
if ( isset($_REQUEST['dolibarr_entity']) ) {
update_site_option( 'dolibarr_entity', sanitize_text_field($_REQUEST['dolibarr_entity']));
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

<?php
$dolibarr = callDoliApi("GET", "/status", null, -5 * MINUTE_IN_SECONDS);
?>

    <p>Version Dolibarr <a href='https://sourceforge.net/projects/dolibarr/files/Dolibarr%20ERP-CRM/<?php echo constant("DOLIBARR_MINIMUM_VERSION"); ?>/' target='_blank'><?php echo constant("DOLIBARR_MINIMUM_VERSION"); ?></a> minimum - <a href='https://sourceforge.net/projects/dolibarr/files/Dolibarr%20ERP-CRM/<?php echo constant("DOLIBARR_LEGAL_VERSION"); ?>/' target='_blank'><?php echo constant("DOLIBARR_LEGAL_VERSION"); ?></a> recommandée - votre version est <?php echo doliversion(constant("DOLIBARR_LEGAL_VERSION")); ?></p>
    <p>Doliconnector <?php echo constant("DOLIBARR_LEGAL_VERSION"); ?> minimum requis à <a href='https://github.com/ptibogxiv/doliconnector/releases' target='_blank'>télécharger ici</a> pour lier WordPress à Dolibarr</p>
    <form action="" method="post">
        <table class="form-table" width="100%">
            <tr>
                <th style="width:150px;"><label for="license_key_doliconnect-pro">License Doliconnect</label></th>
                <td ><input class="regular-text" type="text" id="license_key_doliconnect-pro" name="license_key_doliconnect-pro" value="<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
echo get_site_option('license_key_doliconnect-pro');?> " <?php } else { echo "";?>" disabled <?php } ?> > <b>PRO</b> 
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
            <tr>          
                <th style="width:150px;"><label for="status">Status Dolibarr</label></th>
                <td>
<?php if ( is_object($dolibarr) ) {
?>                 
                <p class="text-success">Status: <?php echo $dolibarr->success->code; ?></p>
                <p class="text-success">Version: <?php echo $dolibarr->success->dolibarr_version; ?></p>
                <p class="text-success">Access Locked: <?php echo $dolibarr->success->access_locked; ?></p>
<?php } else { ?><p class="text-danger">Offline</p><?php } ?></td>
            </tr>
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

$dolibarr = callDoliApi("GET", "/multicompany/".dolibarr_entity(), null, -5 * MINUTE_IN_SECONDS);
//echo var_dump($dolibarr);  
?>
	<div id="ptibogxiv_management_page" class="postbox">
	<div class="inside">
<?php                                                    

if (isset($_REQUEST['doliconnect_settings'])) {            
if (isset($_REQUEST['users_can_register']) && $_REQUEST['users_can_register']==1){
update_option('users_can_register', sanitize_text_field($_REQUEST['users_can_register']));
}else {
delete_option('users_can_register');}
if (isset($_REQUEST['dolicustsupp_can_register']) && $_REQUEST['dolicustsupp_can_register']==1){
update_option('dolicustsupp_can_register', sanitize_text_field($_REQUEST['dolicustsupp_can_register']));
}else {
delete_option('dolicustsupp_can_register');}
if (isset($_REQUEST['dolibarr_b2bmode']) && $_REQUEST['dolibarr_b2bmode']==1){
update_option('dolibarr_b2bmode', sanitize_text_field($_REQUEST['dolibarr_b2bmode']));
}else {
delete_option('dolibarr_b2bmode');}
if (isset($_REQUEST['doliloginmodal']) && $_REQUEST['doliloginmodal']==1){
update_option('doliloginmodal', sanitize_text_field($_REQUEST['doliloginmodal']));
}else {
delete_option('doliloginmodal');}
if (isset($_REQUEST['doliconnectbeta']) && $_REQUEST['doliconnectbeta']==1){
update_option('doliconnectbeta', sanitize_text_field($_REQUEST['doliconnectbeta']));
}else {
delete_option('doliconnectbeta');}
if (isset($_REQUEST['doliconnectrestrict']) && $_REQUEST['doliconnectrestrict']==1){
update_option('doliconnectrestrict', sanitize_text_field($_REQUEST['doliconnectrestrict']));
}else {
delete_option('doliconnectrestrict');}
if (isset($_REQUEST['dolicartsuppliergrid']) && $_REQUEST['dolicartsuppliergrid']==1){
update_option('dolicartsuppliergrid', sanitize_text_field($_REQUEST['dolicartsuppliergrid']));
}else {
delete_option('dolicartsuppliergrid');}
if (isset($_REQUEST['doliconnect_facebook']) && $_REQUEST['doliconnect_facebook']>0){
update_option('doliconnect_facebook', sanitize_text_field($_REQUEST['doliconnect_facebook']));
}else {
delete_option('doliconnect_facebook');} 
if (isset($_REQUEST['doliconnect_google']) && $_REQUEST['doliconnect_google']>0){
update_option('doliconnect_google', sanitize_text_field($_REQUEST['doliconnect_google']));
}else {
delete_option('doliconnect_google');}                               
            update_option('doliaccount', sanitize_text_field($_REQUEST['doliaccount']));
            update_option('doliconnect_disablepro', sanitize_text_field($_REQUEST['doliconnect_disablepro']));
            if (isset($_REQUEST['doliconnectrestrict_role'])) update_option('doliconnectrestrict_role', sanitize_text_field($_REQUEST['doliconnectrestrict_role']));
            if (isset($_REQUEST['dolibarr_entity'])) update_option('dolibarr_entity', sanitize_text_field($_REQUEST['dolibarr_entity']));
            update_option('dolicart', sanitize_text_field($_REQUEST['dolicart']));
            if (isset($_REQUEST['dolidonation'])) update_option('dolidonation', sanitize_text_field($_REQUEST['dolidonation']));
            if (isset($_REQUEST['doliticket'])) update_option('doliticket', sanitize_text_field($_REQUEST['doliticket']));
            if (isset($_REQUEST['doliclassifieds'])) update_option('doliclassifieds', sanitize_text_field($_REQUEST['doliclassifieds'])); 
            update_option('doliconnect_ipkiosk', array_values(array_filter(array_map('trim', explode(PHP_EOL, $_REQUEST['doliconnect_ipkiosk'])))));             
            update_option('wp_page_for_privacy_policy', sanitize_text_field($_REQUEST['wp_page_for_privacy_policy']));
            update_option('dolishop', sanitize_text_field($_REQUEST['dolishop']));
            update_option('dolishipping', sanitize_text_field($_REQUEST['dolishipping']));
            update_option('dolicartnewlist', sanitize_text_field($_REQUEST['dolicartnewlist']));
            update_option('dolicartlist', sanitize_text_field($_REQUEST['dolicartlist']));
            update_option('dolisupplier', sanitize_text_field($_REQUEST['dolisupplier']));             
            update_option('doliconnect_social_facebook', sanitize_text_field($_REQUEST['doliconnect_social_facebook']));
            update_option('doliconnect_social_twitter', sanitize_text_field($_REQUEST['doliconnect_social_twitter']));
            update_option('doliconnect_social_instagram', sanitize_text_field($_REQUEST['doliconnect_social_instagram']));
            update_option('doliconnect_social_youtube', sanitize_text_field($_REQUEST['doliconnect_social_youtube']));
            update_option('doliconnect_social_github', sanitize_text_field($_REQUEST['doliconnect_social_github']));
            update_option('doliconnect_social_linkedin', sanitize_text_field($_REQUEST['doliconnect_social_linkedin']));
            update_option('doliconnect_social_skype', sanitize_text_field($_REQUEST['doliconnect_social_skype']));                        
            update_option('dolicontact', sanitize_text_field($_REQUEST['dolicontact']));          
            update_option('doliconnect_facebook_key', sanitize_text_field($_REQUEST['doliconnect_facebook_key']));
            update_option('doliconnect_facebook_secret', sanitize_text_field($_REQUEST['doliconnect_facebook_secret']));     
            update_option('doliconnect_google_key', sanitize_text_field($_REQUEST['doliconnect_google_key']));
            update_option('doliconnect_google_secret', sanitize_text_field($_REQUEST['doliconnect_google_secret']));                       
    }   
    ?>
    <form action="" method="post">
        <table class="form-table" width="100%">
            <tr>
                <th style="width:150px;"><label for="doliloginmodal"><?php _e('Modal login', 'doliconnect') ?></label></th>
                <td ><input name="doliloginmodal" type="checkbox" id="doliloginmodal" value="1" <?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
checked('1', get_option('doliloginmodal')); } else { ?> disabled <?php } ?> > <b>PRO</b>            
                </td>
            </tr> 
            <tr>
                <th style="width:150px;"><label for="doliconnectbeta"><?php _e('Beta mode', 'doliconnect') ?></label></th>
                <td ><input name="doliconnectbeta" type="checkbox" id="doliconnectbeta" value="1" <?php checked('1', get_option('doliconnectbeta')); ?> /> <?php _e('Active beta functions, can be unstable', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnectbeta"><?php _e('Restricted mode', 'doliconnect') ?></label></th>
                <td ><input name="doliconnectrestrict" type="checkbox" id="doliconnectrestrict" value="1" <?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) {
checked('1', get_option('doliconnectrestrict')); } else { ?> disabled <?php } ?> > <b>PRO</b><br><br><?php _e("Roles to be assigned for existing users. If none, the connection will not be allowed.", 'doliconnect') ?>
<select class='custom-select' id='doliconnectrestrict_role'  name='doliconnectrestrict_role' <?php if ( !get_option('doliconnectrestrict') )  { ?>  disabled <?php } ?> >
<option value=""><?php _e("None", 'doliconnect') ?></option>
<?php $wp_roles = new WP_Roles();
    $roles = $wp_roles->get_names();
    $roles = array_map( 'translate_user_role', $roles );
foreach ( $roles as $role => $label ) {
echo "<option value='".$role."' ";
if ( get_option('doliconnectrestrict_role') == $role ) {
echo "selected ";
}
echo ">".$label."</option>";
} 
?></select>
                </td>
            </tr>             
<?php if ( is_multisite() ) {
$multicompany = callDoliApi("GET", "/multicompany?sortfield=t.rowid&sortorder=ASC", null, 30 * MINUTE_IN_SECONDS, 1);
?>                  
            <tr>
                <th style="width:150px;"><label for="dolibarr_register"><?php _e("Dolibarr's entity", 'doliconnect') ?></label></th>
                <td>
<?php if ( !isset($multicompany->error) && $multicompany != null ) { ?>
<select class='custom-select' id='dolibarr_entity'  name='dolibarr_entity' <?php if (empty(get_site_option('dolibarr_entity')) || !is_super_admin()) { echo 'disabled'; } ?> >
<?php
foreach ( $multicompany as $company ) {
echo "<option value='".$company->id."' ";
if ( get_option('dolibarr_entity') == $company->id ) {
echo "selected ";
} elseif ( $company->id == (!empty(get_option('dolibarr_entity'))?get_option('dolibarr_entity'):get_current_blog_id()) ) {
echo "selected ";}
echo ">".$company->label."</option>";
} 
} elseif ( !empty(get_site_option('dolibarr_entity')) ) {
echo "<input id='dolibarr_entity'  name='dolibarr_entity' type='text' value='".(!empty(get_option('dolibarr_entity'))?get_option('dolibarr_entity'):get_current_blog_id())."'> Il semble que n'avez pas le module multicompany ";
} ?>
</select>
                </td>
            </tr>
<?php } ?>             
            <tr>
                <th style="width:150px;"><label for="dolibarr_register">dolibarr_register</label></th>
                <td ><input name="users_can_register" type="checkbox" id="users_can_register" value="1" <?php checked('1', get_option('users_can_register')); ?> /> <?php _e('Anyone can register', 'doliconnect') ?><br>
                     <input name="dolicustsupp_can_register" type="checkbox" id="dolicustsupp_can_register" value="1" <?php checked('1', get_option('dolicustsupp_can_register')); ?> /> <?php _e('Existing Customer/Supplier on Dolibarr can register', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_register"><?php _e('B2B mode', 'doliconnect') ?></label></th>
                <td ><input name="dolibarr_b2bmode" type="checkbox" id="dolibarr_b2bmode" value="1" <?php checked('1', get_option('dolibarr_b2bmode')); ?> /> <?php _e('Display all prices excluding VAT', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnect_disablepro"><?php _e('Personnal / Enterprise mode', 'doliconnect') ?></label></th>
                <td ><select name="doliconnect_disablepro" type="checkbox" id="doliconnect_disablepro">
                <option value="0" <?php selected('O', get_option('doliconnect_disablepro'));?>>Perso & Pro</option>
                <option value="phy" <?php selected('phy', get_option('doliconnect_disablepro'));?>>Only Perso</option>
                <option value="mor" <?php selected('mor', get_option('doliconnect_disablepro'));?>>Only PRO</option>
                </select>
                </td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnect_ipkiosk"><?php _e('Kiosk mode', 'doliconnect') ?></label></th>
                <td ><textarea rows="6" cols="75" name="doliconnect_ipkiosk" type="text" id="doliconnect_ipkiosk"><?php if ( ! empty(get_option('doliconnect_ipkiosk')) ) { echo implode("\n",get_option('doliconnect_ipkiosk'));} ?></textarea><br><?php _e('IP address', 'doliconnect') ?>: <?php echo $_SERVER['REMOTE_ADDR']; ?><br><?php _e('one IP per line without space or comma', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_account">dolibarr_account</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliaccount', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('doliaccount') 
);
           wp_dropdown_pages($args); ?>
<br><br><textarea name="doliconnect_login_info" placeholder="<?php _e('Message on login page', 'doliconnect') ?>" class="form-control" id="exampleFormControlTextarea1" rows="3" cols="75"><?php echo get_option('doliaccountinfo'); ?></textarea>   
           </td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_shop">dolibarr_shop</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolishop', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolishop') 
);
           wp_dropdown_pages($args); ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_dolishipping">dolibarr_dolishipping</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolishipping', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolishipping') 
);
           wp_dropdown_pages($args); ?></td>
            </tr>  
            <tr>
                <th style="width:150px;"><label for="dolibarr_shop">dolibarr_supplier</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolisupplier', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolisupplier') 
);
           wp_dropdown_pages($args); ?><input name="dolicartsuppliergrid" type="checkbox" id="dolicartsuppliergrid" value="1" <?php checked('1', get_option('dolicartsuppliergrid')); ?> /> <?php _e('Display in grid rather than a list', 'doliconnect') ?></td>
            </tr> 
            <tr>
                <th style="width:150px;"><label for="dolibarr_cart">dolibarr_cart</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolicart', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolicart') 
);
           wp_dropdown_pages($args); ?>
           
           <select name="dolicartlist" id="dolicartlist">
           <option value="5" <?php if (get_option('dolicartlist') == '5') { ?> selected <?php } ?>>5</option>
           <option value="10" <?php if (get_option('dolicartlist') == '10' || empty(get_option('dolicartlist'))) { ?> selected <?php } ?>>10 (<?php _e('by default', 'doliconnect') ?>)</option>
           <option value="15" <?php if (get_option('dolicartlist') == '15') { ?> selected <?php } ?>>15</option>
           <option value="20" <?php if (get_option('dolicartlist') == '20') { ?> selected <?php } ?>>20</option>
           <option value="25" <?php if (get_option('dolicartlist') == '25') { ?> selected <?php } ?>>25</option>
           <option value="30" <?php if (get_option('dolicartlist') == '30') { ?> selected <?php } ?>>30</option>
           <option value="40" <?php if (get_option('dolicartlist') == '40') { ?> selected <?php } ?>>40</option>
           <option value="50" <?php if (get_option('dolicartlist') == '50') { ?> selected <?php } ?>>50</option>
           <option value="75" <?php if (get_option('dolicartlist') == '75') { ?> selected <?php } ?>>75</option>
           <option value="100" <?php if (get_option('dolicartlist') == '100') { ?> selected <?php } ?>>100</option>
           </select> <?php _e('choices of the amount of product', 'doliconnect') ?>
           
           <select name="dolicartnewlist" id="dolicartnewlist">
           <option value="month" <?php if (get_option('dolicartnewlist') == 'month' || empty(get_option('dolicartnewlist'))) { ?> selected <?php } ?>><?php _e('Last month', 'doliconnect') ?> (<?php _e('by default', 'doliconnect') ?>)</option>
           <option value="week" <?php if (get_option('dolicartnewlist') == 'week') { ?> selected <?php } ?>><?php _e('Last week', 'doliconnect') ?></option>
           <option value="day" <?php if (get_option('dolicartnewlist') == 'day') { ?> selected <?php } ?>><?php _e('Last day', 'doliconnect') ?></option>
           <option value="none" <?php if (get_option('dolicartnewlist') == 'none') { ?> selected <?php } ?>><?php _e('None', 'doliconnect') ?></option>
           </select> <?php _e('Duration of new product', 'doliconnect') ?>
           </td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_shop">dolibarr_donation</label></th>
                <td >
<?php if ( is_plugin_active( 'doliconnect-pro/doliconnect-pro.php' ) ) { ?>
<?php 
           $args = array(
    'name' => 'dolidonation', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolidonation') 
);
           wp_dropdown_pages($args); ?>
<?php } else { ?>
<select name="dolidonation" type="checkbox" id="dolidonation" value="0" disabled><option> --- </option></select>
<?php } ?> <b>PRO</b></td>
            </tr>                          
            <tr>
                <th style="width:150px;"><label for="dolibarr_contact">dolibarr_contact</label></th>
                <td >
           <?php 
           $args = array(
    'name' => 'dolicontact', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('dolicontact') 
);
           wp_dropdown_pages($args); ?> </td>
            </tr>                           
             <tr>
                <th style="width:150px;"><label for="dolibarr_legacy">dolibarr_legacy</label></th>
                <td ><?php 
           $args = array(
    'name' => 'wp_page_for_privacy_policy', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option( 'wp_page_for_privacy_policy' ) 
);
           wp_dropdown_pages($args); ?> <?php _e('(set your default wordpress legacy page)', 'doliconnect') ?></td>
            </tr>
<?php            
if (is_plugin_active( 'doliconnect-ticket/doliconnect-ticket.php' ) ) { ?>
            <tr>
                <th style="width:150px;"><label for="dolibarr_ticket">dolibarr_ticket</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliticket', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('doliticket') 
);
           wp_dropdown_pages($args); ?></td>
            </tr> 
<?php }            
if (is_plugin_active( 'doliconnect-classifieds/doliconnect-classifieds.php' ) ) { ?>                        <tr>
                <th style="width:150px;"><label for="dolibarr_classified">dolibarr_classified</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliclassifieds', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0', 
    'selected' => get_option('doliclassifieds') 
);
           wp_dropdown_pages($args); ?></td>
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
            <td><input name="doliconnect_facebook" type="checkbox" id="doliconnect_facebook" value="1" <?php checked('1', get_option('doliconnect_facebook')); ?> /> Facebook<br>
            Key<input class="regular-text" type="text" id="doliconnect_facebook_key" name="doliconnect_facebook_key"  value="<?php echo get_option('doliconnect_facebook_key'); ?>"><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_facebook_secret" name="doliconnect_facebook_secret"  value="<?php  echo get_option('doliconnect_facebook_secret'); ?>"><br> 

            <input name="doliconnect_google" type="checkbox" id="doliconnect_google" value="1" <?php checked('1', get_option('doliconnect_google')); ?> /> Google<br>      
            Key<input class="regular-text" type="text" id="doliconnect_google_key" name="doliconnect_google_key" value="<?php echo get_option('doliconnect_google_key'); ?>"><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_google_secret" name="doliconnect_google_secret"  value="<?php echo get_option('doliconnect_google_secret'); ?>"><br>  
           
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
