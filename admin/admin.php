<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
    add_menu_page(
        __( 'Settings', 'doliconnect' ),
        __( 'Doliconnect', 'doliconnect' ),
        'manage_options',
        'doliconnect_settings',
        'doliconnect_settings',
        plugins_url( 'doliconnect/images/icon_16.png' )
    );
    add_submenu_page(
        'doliconnect_settings',
        __( 'Settings', 'doliconnect' ),
        __( 'Settings', 'doliconnect' ),
        'manage_options',
        'doliconnect_settings',
        'doliconnect_settings'
    );
    add_submenu_page(
        'doliconnect_settings',
        __( 'Sync with Dolibarr', 'doliconnect' ),
        __( 'Sync with Dolibarr', 'doliconnect' ),
        'manage_options',
        'doliconnect_network_page',
        'doliconnect_network_page'
    );
    add_submenu_page(
        'doliconnect_settings',
        __( 'Help', 'doliconnect' ),
        __( 'Help', 'doliconnect' ),
        'manage_options',
        'doliconnect_help',
        'doliconnect_help'
    );
}

function doliconnect_admin_page2() {
    add_menu_page(
        __( 'Doliconnect', 'Doliconnect' ),
        __( 'Doliconnect', 'Doliconnect' ),
        'manage_options', 'doliconnect_network_page',
        'doliconnect_network_page',
        plugins_url( 'doliconnect/images/icon_16.png' )
    );
    add_submenu_page(
        'doliconnect_network_page',
        __( 'Management', 'doliconnect' ),
        __( 'Management', 'doliconnect' ),
        'manage_options',
        'doliconnect_network_page',
        'doliconnect_network_page'
    );
}

function doliconnect_admin_page3() {
    add_menu_page(
        __( 'Doliconnect settings', 'doliconnect' ),
        __( 'Doliconnect', 'doliconnect' ),
        'manage_options',
        'ptibogxiv_management_page',
        'ptibogxiv_management_page',
        plugins_url( 'doliconnect/images/icon_16.png' )
    );
    add_submenu_page(
        'ptibogxiv_management_page',
        __( 'Management', 'doliconnect' ),
        __( 'Management', 'doliconnect' ),
        'manage_options',
        'ptibogxiv_management_page',
        'ptibogxiv_management_page'
    );
}

function doliconnect_admin_page4() {
    add_users_page(
        __( 'Gestion des admins', 'doliconnect' ),
        __( 'Gestion des admins', 'doliconnect' ),
        'manage_options',
        'doliconnect_admin_page',
        'doliconnect_admin_page'
    );
}

if ( is_multisite() ) {
    add_action( 'network_admin_menu', 'doliconnect_admin_page2' );
    add_action( 'admin_menu', 'doliconnect_admin_page3' );
    add_action( 'admin_menu', 'doliconnect_admin_page4' );
} else {
    add_action( 'admin_menu', 'doliconnect_admin_page1' );
    add_action( 'admin_menu', 'doliconnect_admin_page4' );
}

function doliconnect_help() {
    echo '<div class="wrap">';
    echo '<h2>' . esc_html__( 'Help', 'doliconnect' ) . '</h2>';
    echo '</div>';
}

function doliconnect_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h2>' . esc_html__( 'Gestion des admins', 'doliconnect' ) . '</h2>';
    $result = count_users();
    echo '<p>' . esc_html__( 'There are', 'doliconnect' ) . ' ' . absint( $result['total_users'] ) . ' ' . esc_html__( 'total users', 'doliconnect' ) . '</p>';

    if ( isset( $_POST['doliboard'] ) ) {
        check_admin_referer( 'doliconnect_admin_page_nonce', 'doliconnect_admin_page_nonce' );

        $doliboard = array_map( 'sanitize_text_field', wp_unslash( $_POST['doliboard'] ) );

        foreach ( $doliboard as $position => $user_id ) {
            $position = absint( $position );
            $user_id  = absint( $user_id );

            $assigned_users = get_users(
                array(
                    'meta_key'    => 'doliboard_4',
                    'meta_value'  => $position,
                    'fields'      => 'ID',
                    'number'      => -1,
                    'count_total' => false,
                )
            );

            foreach ( $assigned_users as $assigned_user_id ) {
                if ( $assigned_user_id !== $user_id ) {
                    delete_user_meta( $assigned_user_id, 'doliboard_4' );
                }
            }

            if ( $user_id > 0 ) {
                update_user_meta( $user_id, 'doliboard_4', $position );
            }

            $title_key = 'doliboard_title_' . $position;
            if ( isset( $_POST[ $title_key ] ) ) {
                update_option( $title_key, sanitize_text_field( wp_unslash( $_POST[ $title_key ] ) ) );
            }

            $email_key = 'doliboard_email_' . $position;
            if ( isset( $_POST[ $email_key ] ) ) {
                update_option( $email_key, sanitize_text_field( wp_unslash( $_POST[ $email_key ] ) ) );
            }
        }

        echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__( 'Admin settings saved.', 'doliconnect' ) . '</p></div>';
    }

    $total = array( 'administrator' => 0, 'editor' => 0 );
    foreach ( $result['avail_roles'] as $role => $count ) {
        if ( in_array( $role, array( 'editor', 'administrator' ), true ) ) {
            $total[ $role ] = absint( $count );
        }
        echo ', ' . absint( $count ) . ' ' . esc_html( $role );
    }

    echo '<form action="' . esc_url( admin_url( 'admin.php?page=doliconnect_admin_page' ) ) . '" method="post">';
    wp_nonce_field( 'doliconnect_admin_page_nonce', 'doliconnect_admin_page_nonce' );

    $max_position = array_sum( $total );
    for ( $i = 1; $i <= $max_position; $i++ ) {
        echo '<br />' . esc_html( $i ) . ' ';

        $usera = reset(
            get_users(
                array(
                    'meta_key'    => 'doliboard_' . get_current_blog_id(),
                    'meta_value'  => strval( $i ),
                    'number'      => 1,
                    'count_total' => false,
                )
            )
        );

        if ( ! empty( $usera ) ) {
            $USERID[ $i ] = absint( $usera->ID );
        }

        if ( $i <= $total['administrator'] ) {
            echo esc_html__( 'admin', 'doliconnect' );
            echo '<select name="doliboard[' . esc_attr( $i ) . ']">';
            $args = array(
                'blog_id'      => $GLOBALS['blog_id'],
                'role'         => 'administrator',
                'meta_key'     => 'first_name',
                'orderby'      => 'meta_value',
                'order'        => 'ASC',
            );
        } else {
            echo esc_html__( 'editeur', 'doliconnect' );
            echo '<select name="doliboard[' . esc_attr( $i ) . ']">';
            $args = array(
                'blog_id'      => $GLOBALS['blog_id'],
                'role'         => 'editor',
                'meta_key'     => 'first_name',
                'orderby'      => 'meta_value',
                'order'        => 'ASC',
            );
        }

        $user_query = new WP_User_Query( $args );

        if ( ! empty( $user_query->results ) ) {
            echo '<option value="0">' . esc_html__( 'no one', 'doliconnect' ) . '</option>';
            foreach ( $user_query->results as $user ) {
                printf(
                    '<option value="%d" %s>%s</option>',
                    absint( $user->ID ),
                    selected( isset( $USERID[ $i ] ) ? $USERID[ $i ] : 0, $user->ID, false ),
                    esc_html( trim( $user->user_firstname . ' ' . $user->user_lastname ) )
                );
            }
        }

        echo '</select>';
        echo '<input type="text" id="doliboard_title_' . esc_attr( $i ) . '" name="doliboard_title_' . esc_attr( $i ) . '" value="' . esc_attr( get_option( 'doliboard_title_' . $i ) ) . '" placeholder="' . esc_attr__( 'Fonction', 'doliconnect' ) . '" />';
        echo '<input type="text" id="doliboard_email_' . esc_attr( $i ) . '" name="doliboard_email_' . esc_attr( $i ) . '" value="' . esc_attr( get_option( 'doliboard_email_' . $i ) ) . '" placeholder="' . esc_attr__( 'Email de fonction', 'doliconnect' ) . '" />';
    }

    echo '<br /><br /><input type="submit" name="activate_license" value="' . esc_attr__( 'Mettre à jour', 'doliconnect' ) . '" class="button-primary" />';
    echo '</form>';
}

function doliconnect_network_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h2>' . esc_html__( 'Sync with Dolibarr', 'doliconnect' ) . '</h2>';

    if ( isset( $_POST['activate_license'] ) ) {
        check_admin_referer( 'doliconnect_network_page_nonce', 'doliconnect_network_page_nonce' );

        $dolibarr_public_url  = isset( $_POST['dolibarr_public_url'] ) ? esc_url_raw( wp_unslash( $_POST['dolibarr_public_url'] ) ) : '';
        $dolibarr_private_key = isset( $_POST['dolibarr_private_key'] ) ? sanitize_text_field( wp_unslash( $_POST['dolibarr_private_key'] ) ) : '';
        $dolibarr_entity      = isset( $_POST['dolibarr_entity'] ) ? sanitize_text_field( wp_unslash( $_POST['dolibarr_entity'] ) ) : '';
        $cronjob_multisite    = isset( $_POST['doliconnect_cronjob_multisite'] ) ? sanitize_text_field( wp_unslash( $_POST['doliconnect_cronjob_multisite'] ) ) : '';

        if ( '' !== $dolibarr_public_url ) {
            if ( ! add_site_option( 'dolibarr_public_url', $dolibarr_public_url ) ) {
                update_site_option( 'dolibarr_public_url', $dolibarr_public_url );
            }
        }

        if ( '' !== $dolibarr_private_key ) {
            if ( ! add_site_option( 'dolibarr_private_key', $dolibarr_private_key ) ) {
                update_site_option( 'dolibarr_private_key', $dolibarr_private_key );
            }
        }

        if ( '' !== $dolibarr_entity ) {
            update_site_option( 'dolibarr_entity', $dolibarr_entity );
        } else {
            delete_site_option( 'dolibarr_entity' );
        }

        if ( '' !== $cronjob_multisite ) {
            update_site_option( 'doliconnect_cronjob_multisite', $cronjob_multisite );
        } else {
            delete_site_option( 'doliconnect_cronjob_multisite' );
        }

        echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__( 'Settings saved.', 'doliconnect' ) . '</p></div>';
    }

    ?>       
<div class="inside">

<?php
$link='https://www.ptibogxiv.net/?update_action=get_metadata&slug=doliconnect&license='.get_site_option('license_key_doliconnect-pro');
?> 

<?php
$dolibarr = callDoliApi("GET", "/status", null, -5 * MINUTE_IN_SECONDS);
?>

    <p>Version Dolibarr <a href='https://sourceforge.net/projects/dolibarr/files/Dolibarr%20ERP-CRM/<?php echo constant("DOLIBARR_MINIMUM_VERSION"); ?>/' target='_blank'><?php echo constant("DOLIBARR_MINIMUM_VERSION"); ?></a> minimum - <a href='https://sourceforge.net/projects/dolibarr/files/Dolibarr%20ERP-CRM/<?php echo constant("DOLIBARR_LEGAL_VERSION"); ?>/' target='_blank'><?php echo constant("DOLIBARR_LEGAL_VERSION"); ?></a> recommandée - votre version est <?php echo $dolibarr->success->dolibarr_version; ?></p>
    <p>Doliconnector <?php echo constant("DOLIBARR_LEGAL_VERSION"); ?> minimum requis à <a href='https://github.com/ptibogxiv/doliconnector/releases' target='_blank'>télécharger ici</a> pour lier WordPress à Dolibarr</p>
    <form action="" method="post">
        <?php wp_nonce_field('doliconnect_network_page_nonce', 'doliconnect_network_page_nonce'); ?>
        <table class="form-table" width="100%">
            <tr>
                <th style="width:150px;"><label for="dolibarr_public_url">DOLIBARR URL</label></th>
                <td ><input class="regular-text" type="text" id="dolibarr_public_url" name="dolibarr_public_url"  value="<?php echo esc_url(get_site_option('dolibarr_public_url')); ?>" required>/api/index.php<br>ex: https://dolibarr.example.com</td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_private_key">DOLIBARR REST API USER KEY</label></th>
                <td ><input class="regular-text" type="text" id="dolibarr_private_key" name="dolibarr_private_key"  value="<?php echo esc_attr(get_site_option('dolibarr_private_key')); ?>" required></td>
            </tr>
            <tr>          
                <th style="width:150px;"><label for="status"><?php _e('Status Dolibarr', 'doliconnect') ?></label></th>
                <td>
<?php if ( is_object($dolibarr) ) { ?>                 
                <p class="text-success">Status: <?php echo $dolibarr->success->code; ?></p>
                <p class="text-success">Version: <?php echo $dolibarr->success->dolibarr_version; ?></p>
                <p class="text-success">Access Locked: <?php echo $dolibarr->success->access_locked; ?></p>
                <p class="text-success">Environment: <?php echo (isset($dolibarr->success->environment)?$dolibarr->success->environment:' -- '); ?></p>
<?php } else { ?><p class="text-danger">Offline</p><?php } ?></td>
            </tr>
<?php if ( is_multisite() ) { ?>       
            <tr>
                <th style="width:150px;"><label for="dolibarr_entity"><?php _e('Personalize entity', 'doliconnect') ?></label></th>
                <td ><input name="dolibarr_entity" type="checkbox" id="dolibarr_entity" value="1" <?php checked('1', get_site_option('dolibarr_entity')); ?> /> permettre de personnaliser les entités liés par defaut entité-wordpress == entité-dolibarr</td>
            </tr>
<?php } ?>
            <tr>
                <th style="width:150px;"><label for="doliconnect_cronjob_multisite"><?php _e('Cronjobs', 'doliconnect') ?></label></th>
                <td ><select name="doliconnect_cronjob_multisite" id="doliconnect_cronjob_multisite">
                <option value="0" <?php selected('0', get_site_option('doliconnect_cronjob_multisite'));?>><?php _e('By blog', 'doliconnect') ?></option>
                <option value="1" <?php selected('1', get_site_option('doliconnect_cronjob_multisite'));?>><?php _e('Soft refresh', 'doliconnect') ?></option>
                <option value="2" <?php selected('2', get_site_option('doliconnect_cronjob_multisite'));?>><?php _e('Full refresh', 'doliconnect') ?></option>
                </select>
                </td>
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


function doliconnect_settings() {
    ?>
<div class='wrap'>
	<?php
	$default_tab = 'tab1';
	$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
	?>

	<h2><?php _e( 'Settings of Doliconnect', 'doliconnect' ); ?></h2>
	<br>

	<h2 class="nav-tab-wrapper">
			<a href="?page=doliconnect_settings&tab=tab1" class="nav-tab nav-tab-active">Dolibarr</a>
			<a href="?page=doliconnect_settings&tab=tab2" class="nav-tab">Tab 2</a>
            <a href="?page=doliconnect_settings&tab=tab3" class="nav-tab">Tab 3</a>
            <a href="?page=doliconnect_settings&tab=tab4" class="nav-tab">Tab 4</a>
	</h2>

	<div class="tab-content">
	
			<?php switch($tab) :
			case 'tab1':
					echo 'je suis tab 1'; 
					break;
			case 'tab2':
					echo 'Je suis tab 2';
					break;
			case 'tab3':
					echo 'Je suis tab 3';
					break;
			case 'tab4':
					echo 'Je suis tab 4';
					break;
			endswitch; ?>
	</div>	
</div>
<?php

echo '<div class="wrap">';
echo '<h2>'.__( 'Doliconnect settings', 'doliconnect' ).'</h2>';

$dolibarr = callDoliApi("GET", "/multicompany/".dolibarr_entity(), null, -5 * MINUTE_IN_SECONDS);
//echo var_dump($dolibarr);  
?>
	<div id="ptibogxiv_management_page" class="postbox">
	<div class="inside">
<?php                                                    

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['doliconnect_settings'] ) ) {
    check_admin_referer( 'doliconnect_management_page_nonce', 'doliconnect_management_page_nonce' );

    $post = wp_unslash( $_POST );

    $checkbox_fields = array(
        'users_can_register',
        'dolicustsupp_can_register',
        'dolibarr_b2bmode',
        'doliconnectdisplayinvoice',
        'doliconnectbeta',
        'doliconnectfontawesome',
        'doliconnectrestrict',
        'dolicartproductgrid',
        'dolicartsuppliergrid',
        'doliconnect_facebook',
        'doliconnect_google',
        'doliconnect_twitter',
        'doliconnect_linkedin',
        'doliclassifieds',
    );

    foreach ( $checkbox_fields as $field ) {
        if ( isset( $post[ $field ] ) && '1' === $post[ $field ] ) {
            update_option( $field, '1' );
        } else {
            delete_option( $field );
        }
    }

    $text_fields = array(
        'doliaccount',
        'doliDefaultclient',
        'doliProductclient',
        'doliaccountinfo',
        'doliconnect_disablepro',
        'doliconnect_cronjob',
        'doliconnectrestrict_role',
        'dolibarr_entity',
        'dolicart',
        'dolitos',
        'dolidonation',
        'doliagenda',
        'wp_page_for_privacy_policy',
        'dolishop',
        'dolifaq',
        'dolishipping',
        'dolicartnewlist',
        'dolicartlist',
        'dolisupplier',
        'dolicontact',
        'doliconnect_facebook_key',
        'doliconnect_facebook_secret',
        'doliconnect_google_key',
        'doliconnect_google_secret',
        'doliconnect_twitter_key',
        'doliconnect_twitter_secret',
        'doliconnect_linkedin_key',
        'doliconnect_linkedin_secret',
    );

    foreach ( $text_fields as $field ) {
        if ( isset( $post[ $field ] ) ) {
            update_option( $field, sanitize_text_field( $post[ $field ] ) );
        }
    }

    if ( isset( $post['doliconnect_ipkiosk'] ) ) {
        $ip_lines = array_filter( array_map( 'trim', explode( PHP_EOL, $post['doliconnect_ipkiosk'] ) ) );
        $ip_lines = array_map( 'sanitize_text_field', $ip_lines );
        update_option( 'doliconnect_ipkiosk', array_values( $ip_lines ) );
    }

    echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__( 'Settings saved.', 'doliconnect' ) . '</p></div>';
}
?>
    <form action="<?php echo esc_url( admin_url( 'admin.php?page=doliconnect_settings' ) ); ?>" method="post">
        <?php wp_nonce_field( 'doliconnect_management_page_nonce', 'doliconnect_management_page_nonce' ); ?>
        <table class="form-table" width="100%">
            <tr>
                <th style="width:150px;"><label for="doliconnectbeta"><?php _e('Beta mode', 'doliconnect') ?></label></th>
                <td ><input name="doliconnectbeta" type="checkbox" id="doliconnectbeta" value="1" <?php checked('1', get_option('doliconnectbeta')); ?> /> <?php _e('Active beta functions, can be unstable', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnectfontawesome"><?php _e('Disable enqueuing Font-awesome library', 'doliconnect') ?></label></th>
                <td ><input name="doliconnectfontawesome" type="checkbox" id="doliconnectfontawesome" value="1" <?php checked('1', get_option('doliconnectfontawesome')); ?> /> <?php _e('Disable if your template already loads it', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnectbeta"><?php _e('Restricted mode', 'doliconnect') ?></label></th>
                <td ><input name="doliconnectrestrict" type="checkbox" id="doliconnectrestrict" value="1" <?php checked('1', get_option('doliconnectrestrict')); ?> ><br><br><?php _e("Roles to be assigned for existing users. If none, the connection will not be allowed.", 'doliconnect') ?>
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
                <th style="width:150px;"><label for="doliconnectdisplayinvoice"><?php _e('Enable displaying invoices in menu', 'doliconnect') ?></label></th>
                <td ><input name="doliconnectdisplayinvoice" type="checkbox" id="doliconnectdisplayinvoice" value="1" <?php checked('1', get_option('doliconnectdisplayinvoice')); ?> /></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="doliconnect_disablepro"><?php _e('Personnal / Enterprise mode', 'doliconnect') ?></label></th>
                <td ><select name="doliconnect_disablepro" id="doliconnect_disablepro">
                <option value="0" <?php selected('0', get_option('doliconnect_disablepro'));?>>Perso & Pro (<?php _e('by default', 'doliconnect') ?>)</option>
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
                'lang' => doliUserLang(wp_get_current_user(), 'slug'),
                'selected' => get_option('doliaccount')
            );
           wp_dropdown_pages($args); ?>
                <select name="doliDefaultclient" id="doliDefaultclient">
                <option value="1" <?php selected('1', get_option('doliDefaultclient'));?>><?php _e('Customer', 'doliconnect') ?> (<?php _e('by default', 'doliconnect') ?>)</option>
                <option value="2" <?php selected('2', get_option('doliDefaultclient'));?>><?php _e('Prospect', 'doliconnect') ?></option>
                <option value="3" <?php selected('3', get_option('doliDefaultclient'));?>><?php _e('Customer and Prospect', 'doliconnect') ?></option>
                </select>
<br><br><textarea name="doliaccountinfo" placeholder="<?php _e('Message on login page', 'doliconnect') ?>" class="form-control" id="exampleFormControlTextarea1" rows="3" cols="75"><?php echo esc_attr(get_option('doliaccountinfo')); ?></textarea>   
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
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('dolishop') 
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
                <option value="month" <?php selected('month', get_option('dolicartnewlist'));?>><?php _e('Last month', 'doliconnect') ?> (<?php _e('by default', 'doliconnect') ?>)</option>
                <option value="week" <?php selected('week', get_option('dolicartnewlist'));?>><?php _e('Last week', 'doliconnect') ?></option>
                <option value="day" <?php selected('day', get_option('dolicartnewlist'));?>><?php _e('Last day', 'doliconnect') ?></option>
                <option value="none" <?php selected('none', get_option('dolicartnewlist'));?>><?php _e('None', 'doliconnect') ?></option>
            </select> <?php _e('Duration of new product', 'doliconnect') ?>
            <select name="dolicartproductgrid" id="dolicartproductgrid">
                <option value="0" <?php selected('0', get_option('dolicartproductgrid'));?>><?php _e('List', 'doliconnect') ?> (<?php _e('by default', 'doliconnect') ?>)</option>
                <option value="1" <?php selected('1', get_option('dolicartproductgrid'));?>><?php _e('Grid', 'doliconnect') ?></option>
            </select> 
            <select name="doliProductclient" id="doliProductclient">
                <option value="0" <?php selected('0', get_option('doliProductclient'));?>><?php _e('Everybody', 'doliconnect') ?> (<?php _e('by default', 'doliconnect') ?>)</option>
                <option value="1" <?php selected('1', get_option('doliProductclient'));?>><?php _e('Only for customer', 'doliconnect') ?></option>
                <option value="2" <?php selected('2', get_option('doliProductclient'));?>><?php _e('Only for Prospect', 'doliconnect') ?></option>
                <option value="3" <?php selected('3', get_option('doliProductclient'));?>><?php _e('Customer or Prospect', 'doliconnect') ?></option>
            </select>
           </td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_dolishipping">dolibarr_dolishipping</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolishipping', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
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
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('dolisupplier') 
);
           wp_dropdown_pages($args); ?>
            <select name="dolicartsuppliergrid" id="dolicartsuppliergrid">
            <option value="0" <?php selected('0', get_option('dolicartsuppliergrid'));?>><?php _e('List', 'doliconnect') ?> (<?php _e('by default', 'doliconnect') ?>)</option>
            <option value="1" <?php selected('1', get_option('dolicartsuppliergrid'));?>><?php _e('Grid', 'doliconnect') ?></option>
            </select> 
            </td>
            </tr> 
            <tr>
                <th style="width:150px;"><label for="dolibarr_cart">dolibarr_cart</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolicart', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('dolicart') 
);
           wp_dropdown_pages($args); ?>
           </td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_faq">dolibarr_faq</label></th>
                <td ><?php 
           $args = array(
    'name' => 'dolifaq', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('dolifaq')  
);
           wp_dropdown_pages($args); ?> <?php _e('(Display your knowledge base)', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_shop">dolibarr_donation</label></th>
                <td >
<?php 
           $args = array(
    'name' => 'dolidonation', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('dolidonation') 
);
           wp_dropdown_pages($args); ?>
            </td>
            </tr>                          
            <tr>
                <th style="width:150px;"><label for="dolibarr_contact">dolibarr_contact</label></th>
                <td >
           <?php 
           $args = array(
    'name' => 'dolicontact', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
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
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option( 'wp_page_for_privacy_policy' ) 
);
           wp_dropdown_pages($args); ?> <?php _e('(set your default wordpress legacy page)', 'doliconnect') ?></td>
            </tr>
             <tr>
                <th style="width:150px;"><label for="dolibarr_tos">dolibarr_tos</label></th>
                <td ><?php 
           $args = array(
    'name' => 'dolitos', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('dolitos')
);
           wp_dropdown_pages($args); ?> <?php _e('(Terms of service)', 'doliconnect') ?></td>
            </tr>
            <tr>
                <th style="width:150px;"><label for="dolibarr_agenda">dolibarr_agenda</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliagenda', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('doliagenda') 
);
           wp_dropdown_pages($args); ?></td>
            </tr> 
<?php           
if (is_plugin_active( 'doliconnect-classifieds/doliconnect-classifieds.php' ) ) { ?>                        <tr>
                <th style="width:150px;"><label for="dolibarr_classified">dolibarr_classified</label></th>
                <td ><?php 
           $args = array(
    'name' => 'doliclassifieds', 
    'show_option_none' => __( '- Select -', 'doliconnect' ), 
    'option_none_value' => '0',
    'lang' => doliUserLang(wp_get_current_user(), 'slug'),
    'selected' => get_option('doliclassifieds') 
);
           wp_dropdown_pages($args); ?></td>
            </tr>
<?php } ?> 
            <tr><?php $cronjob = ! empty( get_site_option( 'doliconnect_cronjob_multisite' ) ) ? get_site_option( 'doliconnect_cronjob_multisite' ) : get_option( 'doliconnect_cronjob' ); ?>
                <th style="width:150px;"><label for="doliconnect_cronjob"><?php _e('Cronjobs', 'doliconnect') ?></label></th>
                <td ><select name="doliconnect_cronjob" id="doliconnect_cronjob" <?php if ( ! empty( get_site_option( 'doliconnect_cronjob_multisite' ) ) ) { ?> disabled <?php } ?>>
                <option value="0" <?php selected( '0', $cronjob );?>><?php _e('Disabled', 'doliconnect') ?></option>
                <option value="1" <?php selected( '1', $cronjob );?>><?php _e('Soft refresh', 'doliconnect') ?></option>
                <option value="2" <?php selected( '2', $cronjob );?>><?php _e('Full refresh', 'doliconnect') ?></option>
                </select>
                </td>
            </tr>
        <tr>
            <th style="width:100px;"><label for="doliconnect_sociallogin">Social login</label></th>
            <td><input name="doliconnect_facebook" type="checkbox" id="doliconnect_facebook" value="1" <?php checked('1', get_option('doliconnect_facebook')); ?> /> Facebook<br>
            Key<input class="regular-text" type="text" id="doliconnect_facebook_key" name="doliconnect_facebook_key"  value="<?php echo esc_attr(get_option('doliconnect_facebook_key')); ?>"><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_facebook_secret" name="doliconnect_facebook_secret"  value="<?php  echo esc_attr(get_option('doliconnect_facebook_secret')); ?>"><br> 

            <input name="doliconnect_google" type="checkbox" id="doliconnect_google" value="1" <?php checked('1', get_option('doliconnect_google')); ?> /> Google<br>      
            Key<input class="regular-text" type="text" id="doliconnect_google_key" name="doliconnect_google_key" value="<?php echo esc_attr(get_option('doliconnect_google_key')); ?>"><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_google_secret" name="doliconnect_google_secret"  value="<?php echo esc_attr(get_option('doliconnect_google_secret')); ?>"><br>  

            <input name="doliconnect_twitter" type="checkbox" id="doliconnect_twitter" value="1" <?php checked('1', get_option('doliconnect_twitter')); ?> /> Twitter<br>      
            Key<input class="regular-text" type="text" id="doliconnect_twitter_key" name="doliconnect_twitter_key" value="<?php echo esc_attr(get_option('doliconnect_twitter_key')); ?>"><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_twitter_secret" name="doliconnect_twitter_secret"  value="<?php echo esc_attr(get_option('doliconnect_twitter_secret')); ?>"><br>  

            <input name="doliconnect_linkedin" type="checkbox" id="doliconnect_linkedin" value="1" <?php checked('1', get_option('doliconnect_linkedin')); ?> /> LinkedIn<br>      
            Key<input class="regular-text" type="text" id="doliconnect_linkedin_key" name="doliconnect_linkedin_key" value="<?php echo esc_attr(get_option('doliconnect_linkedin_key')); ?>"><br> 
            Secret<input class="regular-text" type="text" id="doliconnect_linkedin_secret" name="doliconnect_linkedin_secret"  value="<?php echo esc_attr(get_option('doliconnect_linkedin_secret')); ?>"><br>  

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
