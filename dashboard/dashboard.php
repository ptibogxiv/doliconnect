<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function informations_menu( $menu, $arg) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'informations', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg == 'informations') { $menu .= " active";}
    $menu .= "'>".__( 'Edit my informations', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'user_doliconnect_menu', 'informations_menu', 10, 2);

function informations_module($content, $url) {
    global $current_user;

    $return = null;
    if ( isset($_GET['return']) ) {
        $url = esc_url( add_query_arg( 'return', $_GET['return'], $url) );
        $return = esc_url_raw( $_GET['return']);
    }

    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $request = "/thirdparties/".$thirdparty->id;

    $content = "<div id='doliuserinfos-alert'></div><form action='".admin_url('admin-ajax.php')."' id='doliuserinfos-form' method='post' class='was-validated' enctype='multipart/form-data'>";

    $content .= doliAjax('doliuserinfos', $return, 'update');

    $content .= '<div class="card shadow-sm"><div class="card-header">'.__( 'Edit my informations', 'doliconnect').'</div>';
    $content .= doliuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'thirdparty', doliCheckRights('societe', 'creer'));

    $content .= "<div class='card-body'><div class='d-grid gap-2'><button id='doliuserinfos-button' class='btn btn-outline-secondary' type='submit' ";
    if (!doliCheckRights('societe', 'creer')) { $content .= 'disabled'; }
    $content .= ">".__( 'Update', 'doliconnect')."</button></div></div>";
    $content .= doliCardFooter($thirdparty, 'thirdparty');
    $content .= '</div></form>';
    return $content;
}
add_filter( 'user_doliconnect_informations', 'informations_module', 10, 2);

//*****************************************************************************************

function password_menu( $menu, $arg){
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'password', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg == 'password') { $menu .= " active";}
    $menu .= "'>".__( 'Edit my password', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'user_doliconnect_menu', 'password_menu', 20, 2);

function password_module($content, $url) {
global $current_user;
    $return = null;
    if ( isset($_GET['return']) ) {
        $url = esc_url( add_query_arg( 'return', $_GET['return'], $url) );
        $return = esc_url_raw( $_GET['return']);
    }
    return doliPasswordForm($current_user, $url, $return);
}
add_filter( 'user_doliconnect_password', 'password_module', 10, 2);

//*****************************************************************************************

function avatars_module( $content, $url) {
global $wpdb, $current_user;

$ID = $current_user->ID;
$time = current_time( 'timestamp', 1);
$adherent = doliConnect('member', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

require_once ABSPATH . WPINC . '/class-phpass.php';
$content = null;
if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

if ( isset($_POST["case"]) && $_POST["case"] == 'updateavatar' ) {

if ( isset($_POST['doliavatar']) && empty($_POST['doliavatar']) ) {

$upload_dir = wp_upload_dir();
$nam=$wpdb->prefix."member_photo";

$files = glob($upload_dir['basedir']."/doliconnect/".$ID."/*");
foreach($files as $file){
if(is_file($file))
unlink($file); 
}

delete_user_meta( $ID, $nam,$current_user->$nam);

if ( isset($adherent->id) && $adherent->id > 0 ) {
$data = [
    'photo' => ''
	];
$adherent = callDoliApi("PUT", "/members/".$adherent->id, $data, dolidelay('member'));
}

} elseif ( isset($_POST['doliavatar']) && !empty($_POST['doliavatar']) && $_FILES['inputavatar']['tmp_name'] != null ) {
$types = array('image/jpeg', 'image/jpg');
if ( $_FILES['inputavatar']['tmp_name'] != null ) {
list($width, $height) = getimagesize($_FILES['inputavatar']['tmp_name']);
}
if ( ( $width >= '350' && $height >= '350' ) && ( isset($_FILES['inputavatar']['tmp_name'])) && (in_array($_FILES['inputavatar']['type'], $types)) && ($_FILES['inputavatar']['size'] <= 10000000)) {

$upload_dir = wp_upload_dir();
$nam=$wpdb->prefix."member_photo";

if (file_exists($upload_dir['basedir']."/doliconnect/".$ID."/".$current_user->$nam)){
$files = glob($upload_dir['basedir']."/doliconnect/".$ID."/*");
foreach($files as $file){
if(is_file($file))
unlink($file); 
}}

$uploadedfile = $_FILES['inputavatar'];
   
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter');
function custom_upload_filter( $file ){

    $file['name'] = "avatar.jpg";
    return $file;
}

function dolipropal_upload_dir($fileup) {
	$fileup['subdir']		= '/doliconnect/'.$_POST["userid"];
	$fileup['path']		= $fileup['basedir'] . $fileup['subdir'];
	$fileup['url']		= $fileup['baseurl'] . $fileup['subdir'];
return $fileup;
}
 
$upload_overrides = array( 'test_form' => false );
add_filter('upload_dir', 'dolipropal_upload_dir');
$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
remove_filter('upload_dir', 'dolipropal_upload_dir');

$filename=$upload_dir['basedir']."/doliconnect/".$ID."/avatar.jpg";
$img = wp_get_image_editor($filename);
 
if ( ! is_wp_error( $img ) ) {
$exif = exif_read_data($filename);               
if ( isset($exif['Orientation']) && $exif['Orientation'] == '8') {
$img->rotate( 90 );
} elseif ( isset($exif['Orientation']) && $exif['Orientation'] == '3' ) {
$img->rotate( 180 );
} elseif ( isset($exif['Orientation']) && $exif['Orientation'] == '6' ) {
$img->rotate( -90 );
} 

$img->resize( 350, 350, true );
$avatar = $img->generate_filename($time,$upload_dir['basedir']."/doliconnect/".$ID."/", NULL );
$img->save($avatar);
update_user_meta( $_POST["userid"], $wpdb->prefix."member_photo","avatar-$time.jpg");
$filename2=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time.jpg";
$img = wp_get_image_editor($filename2);
$img->resize( 72, 72, true );
$avatar1 = $img->generate_filename('72x72',$upload_dir['basedir']."/doliconnect/".$ID."/", NULL );
$img->save($avatar1);
$img = wp_get_image_editor($filename2);
$img->resize( 150, 150, true );
$avatar2 = $img->generate_filename('150x150',$upload_dir['basedir']."/doliconnect/".$ID."/", NULL );
$img->save($avatar2);
if ( file_exists($filename) ) {
unlink($filename);
}
}

$minifile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time-72x72.jpg";
$smallfile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time-150x150.jpg";
$avatarfile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time.jpg";

if ( file_exists($avatarfile) ) {
$imgData = base64_encode(file_get_contents("$avatarfile"));
$datat = [
  'filename' => 'avatar.jpg',
  'modulepart' => 'member',
  'subdir' => $adherent->id.'/photos',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = callDoliApi("POST", "/documents/upload", $datat, 0);
}
if ( file_exists($minifile) ) {
$imgData = base64_encode(file_get_contents("$minifile"));
$datat = [
  'filename' => 'avatar_mini.jpg',
  'modulepart' => 'member',
  'subdir' => $adherent->id.'/photos/thumbs',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = callDoliApi("POST", "/documents/upload", $datat, 0);
}
if ( file_exists($smallfile) ) {
$imgData = base64_encode(file_get_contents("$smallfile"));
$datat = [
  'filename' => 'avatar_small.jpg',
  'modulepart' => 'member',
  'subdir' => $adherent->id.'/photos/thumbs',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = callDoliApi("POST", "/documents/upload", $datat, 0);
}

if ( isset($adherent->id) && $adherent->id > 0 ) {
$data = [
    'photo' => 'avatar.jpg'
	];
$adherent = callDoliApi("PUT", "/members/".$adherent->id, $data, dolidelay('member'));
}

} else {
$content .= dolialert ('warning', "Votre photo n'a pu être chargée. Elle doit obligatoirement être au format .jpg et faire moins de 10 Mo. Taille minimum requise 350x350 pixels.");
}
}

$content .= dolialert ('success', __( 'Your informations have been updated.', 'doliconnect'));
}

$content .= "<form action='".$url."' id='doliconnect-avatarform' method='post' class='was-validated' enctype='multipart/form-data'><input type='hidden' name='case' value='updateavatar'>";

$content .='<div class="card shadow-sm"><div class="card-header">'.__( 'Edit my avatar', 'doliconnect').'</div>';
$content .='<ul class="list-group list-group-flush"><li class="list-group-item">';

$content .='<div class="mb-2"><div class="input-group mb-2"><div class="input-group-text">
<input id="doliavatar" name="doliavatar" value="1" class="form-check-input mt-0" type="radio" aria-label="Radio button for following text input" checked>
</div>
<input type="file" id="inputavatar" name="inputavatar" accept="image/*" class="form-control" id="inputGroupFile03" aria-describedby="doliavatarHelp" aria-label="Upload">
</div><div id="doliavatarHelp" class="form-text">'.__( 'Your avatar must be a .jpg/.jpeg file, <10Mo and 350x350pixels minimum.', 'doliconnect').'</div></div>';

$content .='<div class="input-group"><div class="input-group-text">
<input id="doliavatar" name="doliavatar" value="0" class="form-check-input mt-0" type="radio" aria-label="Radio button for following text input">
</div>
<input type="text" class="form-control" aria-label="Text input with radio button" value="'.__( 'Delete your picture', 'doliconnect').'" readonly>
</div>';

$content .='</li>';
$content .="</ul><div class='card-body'><input type='hidden' name='userid' value='$ID'><div class='d-grid gap-2'><button class='btn btn-outline-secondary' type='submit'>".__( 'Update', 'doliconnect')."</button></div></div>";
if (isset($request) && isset($thirdparty)) $content .= doliCardFooter($request, 'thirdparty', $thirdparty);
$content .='</div></form>';
return $content;
}
add_filter( 'user_doliconnect_avatars', 'avatars_module', 10, 2);

//*****************************************************************************************

if ( empty(doliconst('MAIN_DISABLE_CONTACTS_TAB')) && doliCheckRights('societe', 'contact', 'lire') ) {
    function contacts_menu( $menu, $arg) {
        $menu .= "<a href='".esc_url( add_query_arg( 'module', 'contacts', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
        if ( $arg == 'contacts' ) { $menu .= " active"; }
        $menu .= "'>".__( 'Manage address book', 'doliconnect')."</a>";
        return $menu;
    }
    add_filter( 'user_doliconnect_menu', 'contacts_menu', 30, 2);

    function contacts_module($content, $url){
    global $current_user;

        $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

        $content = "<div id='dolicontactinfos-alert'></div>";

        $limit=12;
        if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']); }  else { $page = 0; }

        $content .= '<div class="card shadow-sm"><div class="card-header">'.__( 'Manage address book', 'doliconnect').'</div><ul class="list-group list-group-flush" id="dolicontact-list">';
        
        $request = "/contacts?sortfield=t.rowid&sortorder=DESC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true";                              
        $object = callDoliApi("GET", $request, null, dolidelay('contact', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listcontact  = $object->data; } else { $listcontact  = $object; }
        
        if ( doliCheckRights('societe', 'contact', 'creer') ) {
            $content .= doliModalButton('contact', 'addcontact', '<center><i class="fas fa-plus-circle"></i> '.__( 'Create a contact', 'doliconnect').'</center>', 'button', 'list-group-item lh-condensed list-group-item-action list-group-item-primary');
        }
        if ( !isset($listcontact->error) && $listcontact != null ) {
            foreach ($listcontact  as $postcontact) { 
                $content .= doliModalButton('contact', 'editcontact'.$postcontact->id, '<div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">'.($postcontact->civility ? $postcontact->civility : $postcontact->civility_code).' '.$postcontact->firstname.' '.$postcontact->lastname.'</h5>
                    <small class="text-body-secondary">3 days ago</small></div>
                    <p class="mb-1">'.$postcontact->address.', '.$postcontact->zip.' '.$postcontact->town.'</p>
                    <small class="text-body-secondary">'.$postcontact->email.'</small>', 'button', 'list-group-item lh-condensed list-group-item-action list-group-item-light', $postcontact->id);                                                                           
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No contact', 'doliconnect')."</center></li>";
        }

        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'contact');
        $content .= "</div>";
        return $content;
    }
    add_filter( 'user_doliconnect_contacts', 'contacts_module', 10, 2);
}

//*****************************************************************************************

if ( doliversion('20.0.0') && doliCheckModules('notification') && !empty(get_option('doliconnectbeta')) ) {

function notifications_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'notifications', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg == 'notifications') { $menu .= " active";}
    $menu .= "'>".__( 'Manage notifications', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'user_doliconnect_menu', 'notifications_menu', 40, 2);

function notifications_module( $content, $url ) {
global $current_user;

    $limit=12;
    if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']); }  else { $page = 0; }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $request = "/thirdparties/".$thirdparty->id."/notifications";   
    $object = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    if ( doliversion('21.0.0') && isset($object->data) ) { $listnotif = $object->data; } else { $listnotif = $object; }

    $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Manage notifications', 'doliconnect').'</div><ul class="list-group list-group-flush">';
    
    if ( !isset($listnotif->error) && $listnotif != null ) {
        foreach ( $listnotif as $postnotif ) { 
            $nonce = wp_create_nonce( 'doli-notifications-'. $postnotif->id.'-'.$postnotif->event);
            $arr_params = array( 'id' => $postnotif->id, 'ref' => $postnotif->event, 'security' => $nonce);  
            $return = esc_url( add_query_arg( $arr_params, $url) );
            $request = "/contacts/".esc_attr($postnotif->contact_id)."?includecount=1&includeroles=1";
            $contactfo = callDoliApi("GET", $request, null, dolidelay('contact', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
            $unit = callDoliApi("GET", "/setup/actiontriggers?sortfield=t.rowid&sortorder=ASC&limit=100&lang=fr_fr&sqlfilters=(t.rowid:=:'".$postnotif->event."')", null, dolidelay('constante'));           
            $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa-solid fa-bell fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$unit[0]->label."</h6><small class='text-muted'><span>".$postnotif->type."</span></small></div><span>".($contactfo->civility ? $contactfo->civility : $contactfo->civility_code)." ".$contactfo->firstname." ".$contactfo->lastname."<br>".$contactfo->email."</span>";
            $content .= "</a>";
        }
    } else {
        $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No proposal', 'doliconnect')."</center></li>";
    }

    $content .= "</ul><div class='card-body'>";
    $content .= doliPagination($object, $url, $page);
    $content .= "</div>";
    $content .= doliCardFooter($object, 'thirdparty', $request);
    $content .= "</div>";
    return $content;
}
add_filter( 'user_doliconnect_notifications', 'notifications_module', 10, 2);

}

//*****************************************************************************************

function dolipaymentmodes_lock() {
    return apply_filters( 'doliconnect_paymentmethods_lock', null);
}

function paymentmethods_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'paymentmethods', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg == 'paymentmethods') { $menu .= " active";}
    $menu .= "'>".__( 'Manage payment methods', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'user_doliconnect_menu', 'paymentmethods_menu', 50, 2);

function paymentmethods_module( $content, $url ) {
    return doliconnect_paymentmethods(null, null, $url, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
}
add_filter( 'user_doliconnect_paymentmethods', 'paymentmethods_module', 10, 2);

//*****************************************************************************************

if ( doliCheckModules('propal') && doliCheckRights('propal', 'lire') ) {

function proposals_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'proposals', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'proposals' ) { $menu .= " active";}
    $menu .= "'>".__( 'Proposals tracking', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'customer_doliconnect_menu', 'proposals_menu', 10, 2);

function proposals_module( $content,$url ) {
global $current_user;

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
        $request = "/proposals/".esc_attr($_GET['id'])."?contact_list=0";
        $proposalfo = callDoliApi("GET", $request, null, dolidelay('proposal', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( !isset($proposalfo->error) && isset($_GET['id']) && isset($_GET['ref']) && ( $thirdparty->id == $proposalfo->socid ) && ( $_GET['ref'] == $proposalfo->ref ) && $proposalfo->statut != 0 && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-proposals-'.$proposalfo->id.'-'.$proposalfo->ref)) {
$content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Proposal %s', 'doliconnect'), $proposalfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'proposals', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
$content .= doliObjectInfos($proposalfo);
$content .= "</div><div class='col-md-7'>";
$content .= doliObjectStatus($proposalfo, 'proposal', 1);
$content .= "</div>";

$content .= "</div><br>"; 

$content .= doliObjectStatus($proposalfo, 'proposal', 3);

$content .= "</div><ul class='list-group list-group-flush'>";
 
$content .= doliline($proposalfo, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( $proposalfo->last_main_doc != null ) {
$doc = array_reverse( explode("/", $proposalfo->last_main_doc) );      
$document = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Summary', 'doliconnect'), true, $proposalfo->entity);
} 
    
$fruits[$proposalfo->date_creation.'p'] = array(
"timestamp" => $proposalfo->date_creation,
"type" => __( 'Propal', 'doliconnect'),  
"label" => $proposalfo->ref,
"document" => $document,
"description" => null,
);

sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
foreach ( $fruits as $key => $val ) {
$content .= "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . wp_date('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
$content .= "<div class='col-md-7'><h6>" . $val['label'] . "</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
} 
//var_dump($fruits);
$content .= '</ul>';
$content .= doliCardFooter($proposalfo, 'proposal');
$content .= '</div>';

    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request = "/proposals?sortfield=t.date_valid&sortorder=DESC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true&sqlfilters=(t.fk_statut:!=:0)";
        $object = callDoliApi("GET", $request, null, dolidelay('proposal', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('20.0.0') && isset($object->data) ) { $listpropal = $object->data; } else { $listpropal = $object; }

        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Proposals tracking', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';
        
        if ( !isset($listpropal->error) && $listpropal != null ) {
            foreach ( $listpropal as $postproposal ) { 
                $nonce = wp_create_nonce( 'doli-proposals-'. $postproposal->id.'-'.$postproposal->ref);
                $arr_params = array( 'id' => $postproposal->id, 'ref' => $postproposal->ref, 'security' => $nonce);  
                $return = esc_url( add_query_arg( $arr_params, $url) );
                                
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa fa-file-signature fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postproposal->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postproposal->date_creation)."</small></div><span>".doliprice($postproposal, 'ttc', isset($postproposal->multicurrency_code) ? $postproposal->multicurrency_code : null)."</span><span>";
                $content .= doliObjectStatus($postproposal, 'proposal', 2);
                $content .= "</span></a>";
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No proposal', 'doliconnect')."</center></li>";
        }
        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'proposal');
        $content .= "</div>";
    }
    return $content;
}
add_filter( 'customer_doliconnect_proposals', 'proposals_module', 10, 2);

}

//*****************************************************************************************

if ( doliCheckModules('commande') && doliCheckRights('commande', 'lire') ) {

function orders_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'orders', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'orders' ) { $menu .= " active"; }
    $menu .= "'>".__( 'Orders tracking', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'customer_doliconnect_menu', 'orders_menu', 20, 2);

function orders_module( $content, $url ) {
global $current_user;

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) { 
        $request = "/orders/".esc_attr($_GET['id'])."?contact_list=0";
        $orderfo = callDoliApi("GET", $request, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( !isset($orderfo->error) && isset($_GET['id']) && isset($_GET['ref']) && ($thirdparty->id == $orderfo->socid ) && ($_GET['ref'] == $orderfo->ref) && $orderfo->statut != 0 && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-orders-'.$orderfo->id.'-'.$orderfo->ref)) {

$content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Order %s', 'doliconnect'), $orderfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'orders', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
$content .= doliObjectInfos($orderfo);
$content .= "</div><div class='col-md-7'>";
$content .= doliObjectStatus($orderfo, 'order', 1);
$content .= "</div>";

if ( $orderfo->billed != 1 && $orderfo->statut > 0 ) {
    $nonce = wp_create_nonce( 'valid_dolicart-'.$orderfo->id );
    $arr_params = array( 'cart' => $nonce, 'step' => 'payment', 'module' => $_GET["module"], 'id' => $orderfo->id,'ref' => $orderfo->ref);  
    $return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
    if ( $orderfo->mode_reglement_code == 'CHQ' ) {

    $listpaymentmethods = callDoliApi("GET", "/doliconnector/".$thirdparty->id."/paymentmethods?type=order&rowid=".$orderfo->id, null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

    $content .= "<div class='col'><div class='card bg-light' style='border:0'><div class='card-body'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), doliprice($orderfo, 'ttc', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null), $orderfo->ref, $listpaymentmethods->CHQ->proprio).":</p>";                                                                                                                                                                                                                                                                                                                                      
    $content .= "<p><b>".$listpaymentmethods->CHQ->owner_address."</b></p>";
    //$content .= "<button class='btn btn-link btn-sm' onclick='ValidDoliCart(\"".wp_create_nonce( 'valid_dolicart-'.$orderfo->id )."\")' id='button-source-payment'><small><span class='fa-solid fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect')."</small></button>";
    $content .= "</div></div></div>";
    } elseif ( $orderfo->mode_reglement_code == 'VIR' ) { 

    $listpaymentmethods = callDoliApi("GET", "/doliconnector/".$thirdparty->id."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

    $content .= "<div class='col'><div class='card bg-light' style='border:0'><div class='card-body'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), doliprice($orderfo, 'ttc', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null), $orderfo->ref ).":";
    if (isset($listpaymentmethods->VIR->bank)) $content .= "<br><b>".__( 'Bank', 'doliconnect').": ".$listpaymentmethods->VIR->bank."</b>";
    if (isset($listpaymentmethods->VIR->iban)) $content .=  "<br><b>IBAN: ".$listpaymentmethods->VIR->iban."</b></p>";
    if (isset($listpaymentmethods->VIR->bic) && ! empty($listpaymentmethods->VIR->bic) ) { $content .= "<br><b>BIC/SWIFT : ".$listpaymentmethods->VIR->bic."</b>";}
    //$content .= "<button class='btn btn-link btn-sm' onclick='ValidDoliCart(\"".wp_create_nonce( 'valid_dolicart-'.$orderfo->id )."\")' id='button-source-payment'><small><span class='fa-solid fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect')."</small></button>";
    $content .= "</div></div></div>";
    } elseif ( $orderfo->mode_reglement_code == 'PRE' ) { 

    } else {
    //$content .= "<button type='button' onclick='ValidDoliCart(\"".wp_create_nonce( 'valid_dolicart-'.$orderfo->id )."\")' id='button-source-payment' class='btn btn-warning btn-block' ><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect')."</button>";
    }
    $content .= '<script type="text/javascript">';
    $content .= "function ValidDoliCart(nonce) {
    jQuery('#DoliconnectLoadingModal').modal('show');
    var form = document.createElement('form');
    form.setAttribute('action', '".$return."');
    form.setAttribute('method', 'post');
    form.setAttribute('id', 'doliconnect-cartform');
    var inputvar = document.createElement('input');
    inputvar.setAttribute('type', 'hidden');
    inputvar.setAttribute('name', 'dolichecknonce');
    inputvar.setAttribute('value', nonce);
    form.appendChild(inputvar);
    document.body.appendChild(form);
    form.submit();
            }";                  
    $content .= "</script>";
}

$content .= "</div><br>";

$content .= "<div class='card-group'>"; 
if (!empty($orderfo->contacts_ids) && is_array($orderfo->contacts_ids)) {
    foreach ($orderfo->contacts_ids as $contact) {
    if ('BILLING' == $contact->code) {
    $billingcard = dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    }
    if ('SHIPPING' == $contact->code) {
    $shippingcard = dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    }
    }

    $content .= "<div class='card bg-light' style='border:0'><div class='card-body'><h6>".__( 'Billing address', 'doliconnect')."</h6><small class='text-muted'>";
    if (isset($billingcard) && !empty($billingcard)) {
    $content .= $billingcard;
    } else {
    $content .= doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    }
    $content .= "</small></div></div>";
    $content .= "<div class='card bg-light' style='border:0'><div class='card-body'><h6>".__( 'Shipping address', 'doliconnect')."</h6><small class='text-muted'>";
    if (isset($shippingcard) && !empty($shippingcard)) {
    $content .= $shippingcard;
    } else {
    $content .= doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    }
    $content .= "</small></div></div>";
} else {
    $content .= "<div class='card bg-light' style='border:0'><div class='card-body'><h6>".__( 'Billing and shipping address', 'doliconnect')."</h6><small class='text-muted'>";
    $content .= doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $content .= "</small></div></div>";
}
$content .= "</div><br>";

$content .= doliObjectStatus($orderfo, 'order', 3);

$content .= "</div><ul class='list-group list-group-flush'>";
 
$content .= doliline($orderfo, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( $orderfo->last_main_doc != null ) {
$doc = array_reverse(explode("/", $orderfo->last_main_doc)); 
$document_order = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Summary', 'doliconnect'), true, $orderfo->entity);
} else {
$document_order = dolidocdownload('order', $orderfo->ref, $orderfo->ref.'.pdf', __( 'Summary', 'doliconnect'), true, $orderfo->entity);
} 
    
$fruits[$orderfo->date_commande.'o'] = array(
"timestamp" => $orderfo->date_creation,
"type" => __( 'Order', 'doliconnect'),  
"label" => $orderfo->ref,
"document" => $document_order,
"description" => null,
);

if ( isset($orderfo->linkedObjectsIds->facture) && $orderfo->linkedObjectsIds->facture != null ) {
foreach ($orderfo->linkedObjectsIds->facture as $f => $value) {

if ($value > 0) {
$invoice = callDoliApi("GET", "/invoices/".$value."?contact_list=0", null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= $invoice;
$payment = callDoliApi("GET", "/invoices/".$value."/payments", null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= $payment;
}

if ( $payment != null ) { 
foreach ( $payment as $pay ) {
$fruits[strtotime($pay->date).'p'] = array(
"timestamp" => strtotime($pay->date),
"type" => __( 'Payment', 'doliconnect'),  
"label" => "$pay->type de ".doliprice($pay->amount, isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null),
"description" => $pay->num,
"document" => null,
); 
}
}

if ( $invoice->last_main_doc != null ) {
$doc = array_reverse(explode("/", $invoice->last_main_doc)); 
$document_invoice = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Invoice', 'doliconnect'), true, $invoice->entity);
} else {
$document_invoice = dolidocdownload('invoice', $invoice->ref, $invoice->ref.'.pdf', __( 'Invoice', 'doliconnect'), true, $invoice->entity);
}

if ( $invoice->paye != 1 && $invoice->remaintopay != 0 && function_exists('dolipaymentmodes') ) {

$payment_invoice = "<a href='".doliconnecturl('dolicart')."?pay&module=invoices&id=".$invoice->id."&ref=".$invoice->ref."' id='button-source-payment' class='btn btn-warning btn-block' role='button'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect')."</a><br>";

} elseif ( $invoice->paye != 1 && $invoice->remaintopay != 0 &&  isset($orderfo->public_payment_url) && !empty($orderfo->public_payment_url) ) {

$payment_invoice = "<a href='".$orderfo->public_payment_url."' id='button-source-payment' class='btn btn-warning btn-block' role='button'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect')."</a><br>";

} else {
$payment_invoice = null;
}
  
$fruits[$invoice->date_creation.'i'] = array(
"timestamp" => $invoice->date_creation,
"type" => __( 'Invoice', 'doliconnect'),  
"label" => $invoice->ref,
"document" => $document_invoice,
"description" => $payment_invoice,
);  
} 
} 
 
if ( isset($orderfo->linkedObjectsIds->shipping) ) {
foreach ( $orderfo->linkedObjectsIds->shipping as $s => $value ) {

if ($value > 0) {
$ship = callDoliApi("GET", "/shipments/".$value, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= var_dump($ship);
}

$lnship ="<ul>";
foreach ( $ship->lines as $sline ) {
$lnship .="<li>".$sline->qty_shipped."/".$sline->qty_shipped." ".$sline->libelle."</li>";
}
$lnship .="</ul>";
if ( $ship->trueWeight != null ) {
$poids = " ".__( 'of', 'doliconnect')." ".$ship->trueWeight." ".doliunit($ship->weight_units, 'weight');
} else { $poids = ''; }
if ( $ship->trueSize != null && $ship->trueSize != 'xx' ) {
$dimensions = " - ".__( 'size', 'doliconnect')." ".$ship->trueSize." ".doliunit($ship->size_units, 'size');
} else  { $dimensions = ''; }
if ( $ship->status > 0 ) {
if ( !empty($ship->date_delivery) ) {
$datedelivery = "<br>".__( 'Estimated delivery', 'doliconnect').": ".wp_date( get_option( 'date_format' ), $ship->date_delivery, false);
} else { $datedelivery = ''; }
$fruits[$ship->date_creation] = array(
"timestamp" => $ship->date_creation,
"type" => __( 'Shipment', 'doliconnect'),  
"label" => $ship->ref." ".$ship->tracking_url.$datedelivery,
"description" => "<small>".$lnship.__( 'Parcel', 'doliconnect')." ".$ship->shipping_method.$poids.$dimensions."</small>",
"document" => null,
);
} else {
$fruits[$ship->date_creation] = array(
"timestamp" => $ship->date_creation,
"type" => __( 'Shipment', 'doliconnect'),  
"label" => __( 'Packaging in progress', 'doliconnect'),
"description" => null,
"document" => null,
);
}
 } 
 }

    sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
    foreach ( $fruits as $key => $val ) {
        $content .= "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . wp_date('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
        $content .= "<div class='col-md-7'><h6>".$val['label']."</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
    } 
    //var_dump($fruits);
    $content .= '</ul>';
    $content .= doliCardFooter($orderfo, 'order');
    $content .= '</div>';
    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request= "/orders?sortfield=t.date_valid&sortorder=DESC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true&sqlfilters=(t.fk_statut:!=:'0')";
        $object = callDoliApi("GET", $request, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('20.0.0') && isset($object->data) ) { $listorder = $object->data; } else { $listorder = $object; }
        
        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Orders tracking', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';

        if ( !isset($listorder->error) && $listorder != null ) {
            foreach ( $listorder as $postorder ) {
                $nonce = wp_create_nonce( 'doli-orders-'. $postorder->id.'-'.$postorder->ref);
                $arr_params = array( 'id' => $postorder->id, 'ref' => $postorder->ref, 'security' => $nonce);  
                $return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                                    
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa fa-file-invoice fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postorder->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postorder->date_commande)."</small></div><span>".doliprice($postorder, 'ttc', isset($postorder->multicurrency_code) ? $postorder->multicurrency_code : null)."</span><span>";
                $content .= doliObjectStatus($postorder, 'order', 2);
                $content .= "</span></a>";
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No order', 'doliconnect')."</center></li>";
        }
        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'order');
        $content .= "</div>";
    }
    return $content;
}
add_filter( 'customer_doliconnect_orders', 'orders_module', 10, 2);

}

//*****************************************************************************************

if ( doliCheckModules('facture') && get_option('doliconnectdisplayinvoice') && doliCheckRights('facture', 'lire') ) {
    add_action( 'customer_doliconnect_invoices', 'invoices_module');

function invoices_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'invoices', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'invoices' ) { $menu .= " active"; }
    $menu .= "'>".__( 'Invoices tracking', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'customer_doliconnect_menu', 'invoices_menu', 30, 2);

function invoices_module( $content, $url ) {
global $current_user;

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) { 
        $request = "/invoices/".esc_attr($_GET['id'])."?contact_list=0";
        $invoicefo = callDoliApi("GET", $request, null, dolidelay('invoice', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( !isset($orderfo->error) && isset($_GET['id']) && isset($_GET['ref']) && ($thirdparty->id == $invoicefo->socid ) && ($_GET['ref'] == $invoicefo->ref) && $invoicefo->statut != 0 && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-invoices-'.$invoicefo->id.'-'.$invoicefo->ref)) {

$content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Invoice %s', 'doliconnect'), $invoicefo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'invoices', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
$content .= doliObjectInfos($invoicefo);
$content .= "</div><div class='col-md-7'>";
$content .= doliObjectStatus($invoicefo, 'invoice', 1);
$content .= "</div>";
 
if ( $invoicefo->paye != 1 && $invoicefo->statut > 0 ) {
$nonce = wp_create_nonce( 'valid_dolicart-'.$invoicefo->id );
$arr_params = array( 'cart' => $nonce, 'step' => 'payment', 'module' => $_GET["module"], 'id' => $invoicefo->id,'ref' => $invoicefo->ref);  
$return = add_query_arg( $arr_params, doliconnecturl('dolicart'));
if ( $invoicefo->mode_reglement_code == 'CHQ' ) {

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".$thirdparty->id."/paymentmethods?type=order&rowid=".$invoicefo->id, null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$content .= "<div class='col'><div class='card bg-light' style='border:0'><div class='card-body'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect'), doliprice($invoicefo, 'ttc', isset($invoicefo->multicurrency_code) ? $invoicefo->multicurrency_code : null), $invoicefo->ref, $listpaymentmethods->CHQ->proprio).":</p>";                                                                                                                                                                                                                                                                                                                                      
$content .= "<p><b>".$listpaymentmethods->CHQ->owner_address."</b></p>";
//$content .= "<button class='btn btn-link btn-sm' onclick='ValidDoliCart(\"".wp_create_nonce( 'valid_dolicart-'.$invoicefo->id )."\")' id='button-source-payment'><small><span class='fa-solid fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect')."</small></button>";
$content .= "</div></div></div>";
} elseif ( $invoicefo->mode_reglement_code == 'VIR' ) { 

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".$thirdparty->id."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$content .= "<div class='col'><div class='card bg-light' style='border:0'><div class='card-body'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect'), doliprice($invoicefo, 'ttc', isset($invoicefo->multicurrency_code) ? $invoicefo->multicurrency_code : null), $invoicefo->ref ).":";
$content .= "<br><b>".__( 'Bank', 'doliconnect').": ".$listpaymentmethods->VIR->bank."</b>";
$content .= "<br><b>IBAN: ".$listpaymentmethods->VIR->iban."</b></p>";
if ( ! empty($listpaymentmethods->VIR->bic) ) { $content .= "<br><b>BIC/SWIFT : ".$listpaymentmethods->VIR->bic."</b>";}
//$content .= "<button class='btn btn-link btn-sm' onclick='ValidDoliCart(\"".wp_create_nonce( 'valid_dolicart-'.$invoicefo->id )."\")' id='button-source-payment'><small><span class='fa-solid fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect')."</small></button>";
$content .= "</div></div></div>";
} elseif ( $invoicefo->mode_reglement_code == 'PRE' ) { 

} else {
//$content .= "<button type='button' onclick='ValidDoliCart(\"".wp_create_nonce( 'valid_dolicart-'.$invoicefo->id )."\")' id='button-source-payment' class='btn btn-warning btn-block' ><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect')."</button>";
}
$content .= '<script type="text/javascript">';
$content .= "function ValidDoliCart(nonce) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$return."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-cartform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'dolichecknonce');
inputvar.setAttribute('value', nonce);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }";                  
$content .= '</script>';
}

$content .= "</div><br>"; 

$content .= "<div class='card-group'>"; 
if (!empty($invoicefo->contacts_ids) && is_array($invoicefo->contacts_ids)) {

foreach ($invoicefo->contacts_ids as $contact) {
if ('BILLING' == $contact->code) {
$billingcard = dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
}
if ('SHIPPING' == $contact->code) {
$shippingcard = dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
}
}
$content .= "<div class='card bg-light' style='border:0'><div class='card-body'><h6>".__( 'Billing address', 'doliconnect')."</h6><small class='text-muted'>";
if (isset($billingcard) && !empty($billingcard)) {
$content .= $billingcard;
} else {
$content .= doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
}
$content .= "</small></div></div>";
$content .= "<div class='card bg-light' style='border:0'><div class='card-body'><h6>".__( 'Shipping address', 'doliconnect')."</h6><small class='text-muted'>";
if (isset($shippingcard) && !empty($shippingcard)) {
$content .= $shippingcard;
} else {
$content .= doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
}
$content .= "</small></div></div>";
} else {
$content .= "<div class='card bg-light' style='border:0'><div class='card-body'><h6>".__( 'Billing and shipping address', 'doliconnect')."</h6><small class='text-muted'>";
$content .= doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$content .= "</small></div></div>";
}
$content .= "</div><br>";

$content .= doliObjectStatus($invoicefo, 'invoice', 3);

$content .= "</div><ul class='list-group list-group-flush'>";
 
$content .= doliline($invoicefo, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( $invoicefo->last_main_doc != null ) {
$doc = array_reverse(explode("/", $invoicefo->last_main_doc)); 
$document_order = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Invoice', 'doliconnect'), true, $invoicefo->entity);
} else {
$document_order = dolidocdownload('invoice', $invoicefo->ref, $invoicefo->ref.'.pdf', __( 'Invoice', 'doliconnect'), true, $invoicefo->entity);
} 
    
$fruits[$invoicefo->date_creation.'o'] = array(
"timestamp" => $invoicefo->date_creation,
"type" => __( 'Invoice', 'doliconnect'),  
"label" => $invoicefo->ref,
"document" => $document_order,
"description" => null,
);

if ( isset($invoicefo->linkedObjectsIds->facture) && $invoicefo->linkedObjectsIds->facture != null ) {
foreach ($invoicefo->linkedObjectsIds->facture as $f => $value) {

if ($value > 0) {
$invoice = callDoliApi("GET", "/invoices/".$value."?contact_list=0", null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= $invoice;
$payment = callDoliApi("GET", "/invoices/".$value."/payments", null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//$content .= $payment;
}

if ( $payment != null ) { 
foreach ( $payment as $pay ) {
$fruits[strtotime($pay->date).'p'] = array(
"timestamp" => strtotime($pay->date),
"type" => __( 'Payment', 'doliconnect'),  
"label" => "$pay->type de ".doliprice($pay->amount, isset($invoicefo->multicurrency_code) ? $invoicefo->multicurrency_code : null),
"description" => $pay->num,
"document" => null,
); 
}
}

if ( $invoice->last_main_doc != null ) {
$doc = array_reverse(explode("/", $invoice->last_main_doc)); 
$document_invoice = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Invoice', 'doliconnect'), true, $invoice->entity);
} else {
$document_invoice = dolidocdownload('invoice', $invoice->ref, $invoice->ref.'.pdf', __( 'Invoice', 'doliconnect'), true, $invoice->entity);
}

if ( $invoice->paye != 1 && $invoice->remaintopay != 0 && function_exists('dolipaymentmodes') ) {

$payment_invoice = "<a href='".doliconnecturl('dolicart')."?pay&module=invoices&id=".$invoice->id."&ref=".$invoice->ref."' id='button-source-payment' class='btn btn-warning btn-block' role='button'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect')."</a><br>";

} elseif ( $invoice->paye != 1 && $invoice->remaintopay != 0 &&  isset($invoicefo->public_payment_url) && !empty($invoicefo->public_payment_url) ) {

$payment_invoice = "<a href='".$invoicefo->public_payment_url."' id='button-source-payment' class='btn btn-warning btn-block' role='button'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect')."</a><br>";

} else {
$payment_invoice = null;
}
  
$fruits[$invoice->date_creation.'i'] = array(
"timestamp" => $invoice->date_creation,
"type" => __( 'Invoice', 'doliconnect'),  
"label" => $invoice->ref,
"document" => $document_invoice,
"description" => $payment_invoice,
);  
} 
}

sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
foreach ( $fruits as $key => $val ) {
$content .= "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . wp_date('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
$content .= "<div class='col-md-7'><h6>".$val['label']."</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
} 
//var_dump($fruits);
$content .= '</ul>';
$content .= doliCardFooter($invoicefo, 'invoice');
$content .= '</div>';

    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request= "/invoices?sortfield=t.datec&sortorder=DESC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true&sqlfilters=(t.fk_statut:!=:0)";
        $object = callDoliApi("GET", $request, null, dolidelay('invoice', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('20.0.0') && isset($object->data) ) { $listinvoice = $object->data; } else { $listinvoice = $object; }

        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Invoices tracking', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';

        if ( !isset($listinvoice->error) && $listinvoice != null ) {
            foreach ( $listinvoice as $postinvoice ) {
                $nonce = wp_create_nonce( 'doli-invoices-'.$postinvoice->id.'-'.$postinvoice->ref);
                $arr_params = array( 'id' => $postinvoice->id, 'ref' => $postinvoice->ref, 'security' => $nonce);  
                $return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                                    
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa fa-file-invoice fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postinvoice->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postinvoice->date_creation)."</small></div><span>".doliprice($postinvoice, 'ttc', isset($postinvoice->multicurrency_code) ? $postinvoice->multicurrency_code : null)."</span><span>";
                if ( $postinvoice->statut > 0 ) { $content .= "<span class='fa-solid fa-check-circle fa-fw text-success'></span> ";
                if ( $postinvoice->paye == 1 ) { $content .= "<span class='fa-solid fa-money-bill-alt fa-fw text-success'></span> "; 
                if ( $postinvoice->statut > 1 ) { $content .= "<span class='fa-solid fa-dolly fa-fw text-success'></span> "; }
                else { $content .= "<span class='fa-solid fa-dolly fa-fw text-warning'></span> "; }
                }
                else { $content .= "<span class='fa-solid fa-money-bill-alt fa-fw text-warning'></span> "; 
                if ( $postinvoice->statut > 1 ) { $content .= "<span class='fa-solid fa-dolly fa-fw text-success'></span> "; }
                else { $content .= "<span class='fa-solid fa-dolly fa-fw text-danger'></span> "; }
                }}
                elseif ( $postinvoice->statut == 0 ) { $content .= "<span class='fa-solid fa-check-circle fa-fw text-warning'></span> <span class='fa-solid fa-money-bill-alt fa-fw text-danger'></span> <span class='fa-solid fa-dolly fa-fw text-danger'></span>"; }
                elseif ( $postinvoice->statut == -1 ) { $content .= "<span class='fa-solid fa-check-circle fa-fw text-secondary'></span> <span class='fa-solid fa-money-bill-alt fa-fw text-secondary'></span> <span class='fa-solid fa-dolly fa-fw text-secondary'></span>"; }
                $content .= "</span></a>";
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No invoice', 'doliconnect')."</center></li>";
        }

        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'invoice');
        $content .= "</div>";
    }
    return $content;
}
add_filter( 'customer_doliconnect_invoices', 'invoices_module', 10, 2);

}

//*****************************************************************************************

if ( doliCheckModules('contrat') && doliCheckRights('contrat', 'lire') ) {

function contracts_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'contracts', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'contracts' ) { $menu .= " active"; }
    $menu .= "'>".__( 'Contracts tracking', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'customer_doliconnect_menu', 'contracts_menu', 40, 2);

function contracts_module( $content, $url ) {
global $current_user;

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) {  
        $request = "/contracts/".esc_attr($_GET['id'])."?contact_list=0";
        $contractfo = callDoliApi("GET", $request, null, dolidelay('contract', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

    if ( !isset($contractfo->error) && isset($_GET['id']) && isset($_GET['id']) && isset($_GET['ref']) && ($thirdparty->id == $contractfo->socid) && ($_GET['ref'] == $contractfo->ref) && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-contracts-'.$contractfo->id.'-'.$contractfo->ref)) {
        $content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Contract %s', 'doliconnect'), $contractfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'contracts', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
        $content .= doliObjectInfos($contractfo);
        $content .= "</div><div class='col-md-7'>";
        $content .= doliObjectStatus($contractfo, 'contract', 1);
        $content .= "</div>";

        $content .= "</div><br>";

        $content .= doliObjectStatus($contractfo, 'contract', 3);

        $content .= "</div><ul class='list-group list-group-flush'>";

        $content .= doliline($contractfo, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

        if ( $contractfo->last_main_doc != null ) {
        $doc = array_reverse( explode("/", $contractfo->last_main_doc) );      
        $document = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Summary', 'doliconnect'), true, $contractfo->entity);
        } 
            
        $fruits[$contractfo->date_creation.'p'] = array(
        "timestamp" => $contractfo->date_creation,
        "type" => __( 'contract', 'doliconnect'),  
        "label" => $contractfo->ref,
        "document" => "",
        "description" => null,
        );

        sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
        foreach ( $fruits as $key => $val ) {
        $content .= "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . wp_date('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
        $content .= "<div class='col-md-7'><h6>" . $val['label'] . "</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
        } 

        //var_dump($fruits);
        $content .= '</ul>';
        $content .= doliCardFooter($contractfo, 'contract');
        $content .= '</div>';
    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request = "/contracts?sortfield=t.rowid&sortorder=ASC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true";                              
        $object = callDoliApi("GET", $request, null, dolidelay('contract', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('20.0.0') && isset($object->data) ) { $listcontract = $object->data; } else { $listcontract = $object; }

        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Contracts tracking', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';

        if ( !isset($listcontract->error) && $listcontract != null ) {
            foreach ($listcontract  as $postcontract) {                                                                                 
                $nonce = wp_create_nonce( 'doli-contracts-'. $postcontract->id.'-'.$postcontract->ref);
                $arr_params = array( 'id' => $postcontract->id, 'ref' => $postcontract->ref, 'security' => $nonce);  
                $return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                                    
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa-solid fa-suitcase fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postcontract->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postcontract->date_creation)."</small></div><span>".doliprice($postcontract, 'ttc', isset($postcontract->multicurrency_code) ? $postcontract->multicurrency_code : null)."</span><span>";
                $content .= doliObjectStatus($postcontract, 'contract', 2);
                $content .= "</span></a>";
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No contract', 'doliconnect')."</center></li>";
        }

        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'contract');
        $content .= "</div>";
    }
    return $content;
}
add_filter( 'customer_doliconnect_contracts', 'contracts_module', 10, 2);

}

//*****************************************************************************************

if ( doliCheckModules('projet') && !empty(get_option('doliconnectbeta')) && doliCheckRights('projet', 'lire') ) {

function projects_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'projects', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'projects' ) { $menu .= " active"; }
    $menu .= "'>".__( 'Projets tracking', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'customer_doliconnect_menu', 'projects_menu', 50, 2);

function projects_module( $content, $url ) {
global $current_user;

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) {  
        $request = "/projects/".esc_attr($_GET['id'])."?contact_list=0";
        $projectfo = callDoliApi("GET", $request, null, dolidelay('contract', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

    if ( !isset($projectfo->error) && isset($_GET['id']) && isset($_GET['id']) && isset($_GET['ref']) && ($thirdparty->id == $projectfo->socid) && ($_GET['ref'] == $projectfo->ref) && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-projects-'.$projectfo->id.'-'.$projectfo->ref)) {
        $content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Project %s', 'doliconnect'), $projectfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'projects', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
        $content .= doliObjectInfos($projectfo);
        $content .= "</div><div class='col-md-7'>";
        $content .= doliObjectStatus($projectfo, 'project', 1);
        $content .= "</div>";

        $content .= "</div><br>";

        $content .= doliObjectStatus($projectfo, 'project', 3);
        $content .= "</div><ul class='list-group list-group-flush'>";

        if ( $projectfo->last_main_doc != null ) {
            $doc = array_reverse( explode("/", $projectfo->last_main_doc) );      
            $document = dolidocdownload($doc[2], $doc[1], $doc[0], __( 'Summary', 'doliconnect'), true, $projectfo->entity);
        } 
            
        $fruits[$projectfo->date_creation.'p'] = array(
        "timestamp" => $projectfo->date_creation,
        "type" => __( 'contract', 'doliconnect'),  
        "label" => $projectfo->ref,
        "document" => "",
        "description" => null,
        );

        sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
        foreach ( $fruits as $key => $val ) {
            $content .= "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . wp_date('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
            $content .= "<div class='col-md-7'><h6>" . $val['label'] . "</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
        } 

        //var_dump($fruits);
        $content .= '</ul>';
        $content .= doliCardFooter($projectfo, 'project');
        $content .= '</div>';

    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request = "/projects?sortfield=t.rowid&sortorder=DESC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true";                                
        $object = callDoliApi("GET", $request, null, dolidelay('project', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listproject = $object->data; } else { $listproject = $object; }

        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Projects tracking', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';

        if ( !isset($listproject->error) && $listproject != null ) {
            foreach ($listproject  as $postproject) {                                                                              
                $nonce = wp_create_nonce( 'doli-projects-'. $postproject->id.'-'.$postproject->ref);
                $arr_params = array( 'id' => $postproject->id, 'ref' => $postproject->ref, 'security' => $nonce);  
                $return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                                    
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa-solid fa-diagram-project fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postproject->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postproject->date_creation)."</small></div><span></span><span>";
                $content .= doliObjectStatus($postproject, 'project', 2);
                $content .= "</span></a>";
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No project', 'doliconnect')."</center></li>";
        }

        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'project');
        $content .= "</div>";
    }
    return $content;
}
add_filter( 'customer_doliconnect_projects', 'projects_module', 10, 2);

}

//*****************************************************************************************

if ( doliCheckModules('don') ) {

function donations_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'donations', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg=='donations') { $menu .= " active";}
    $menu .= "'>".__( 'Donations tracking', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'customer_doliconnect_menu', 'donations_menu', 60, 2);

function donations_module( $content, $url ) {
global $current_user;
$entity = get_current_blog_id();
$ID = $current_user->ID;

    if ( isset($_GET['id']) && $_GET['id'] > 0 ) { 
        $request = "/donations/".esc_attr($_GET['id']);
        $donationfo = callDoliApi("GET", $request, null, dolidelay('donation', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

    if ( !isset($donationfo->error) && isset($_GET['id']) && isset($_GET['ref']) && ($thirdparty->id == $donationfo->socid ) && ($_GET['ref'] == $donationfo->ref) && $donationfo->statut != 0 ) {
        $content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Donation %s', 'doliconnect'), $donationfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'donations', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
        $content .= doliObjectInfos($donationfo);
        $content .= "</div><div class='col-md-7'>";
        $content .= doliObjectStatus($donationfo, 'donation', 1);
        $content .= "</div>";

        $content .= "</div><br>"; 

        $content .= doliObjectStatus($donationfo, 'donation', 3);
        $content .= "</div><ul class='list-group list-group-flush'>";
 
        if ( $donationfo->lines != null ) {
        foreach ( $donationfo->lines as $line ) {
        $content .= "<li class='list-group-item'>";     
        if ( $line->date_start != '' && $line->date_end != '' )
        {
        $start = wp_date('d/m/Y', $line->date_start);
        $end = wp_date('d/m/Y', $line->date_end);
        $dates =" <i>(Du $start au $end)</i>";
        }

        $content .= '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
        <h6 class="mb-1">'.$line->libelle.'</h6>
        <p class="mb-1">'.$line->description.'</p>
        <small>'.$dates.'</small>'; 
        $content .= '</div><div class="col-4 col-md-2 text-end"><h5 class="mb-1">'.doliprice($line, 'ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';
        $content .= '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
        $content .= "</div></div></li>";
        }
        }

        $content .= "<li class='list-group-item list-group-item-info'>";
        $content .= "<b>".__( 'Amount', 'doliconnect').": ".doliprice($donationfo, 'amount', isset($donationfo->multicurrency_code) ? $donationfo->multicurrency_code : null)."</b>";
        $content .= "</li>";
        $content .= "</ul>";
        $content .= doliCardFooter($donationfo, 'donation');
        $content .= "</div>";
    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request= "/donations?sortfield=t.date_valid&sortorder=DESC&limit=".$limit."&page=".$page."&thirdparty_ids=".$thirdparty->id."&pagination_data=true";// ".$page."   ."&sqlfilters=(t.fk_statut!=0)"
        $object = callDoliApi("GET", $request, null, dolidelay('donation', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listdonation = $object->data; } else { $listdonation = $object; }

        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Donations tracking', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">'; 
        if ( !empty(doliconnectid('dolidonation'))) {
        $content .= '<a href="'.doliconnecturl('dolidonation').'" class="list-group-item lh-condensed list-group-item-action list-group-item-primary "><center><i class="fa-solid fa-plus-circle"></i> '.__( 'Donate', 'doliconnect').'</center></a>';  
        }

        if ( !isset( $listdonation->error ) && $listdonation != null ) {
            foreach ( $listdonation as $postdonation ) { 
                $arr_params = array( 'id' => $postdonation->id, 'ref' => $postdonation->ref);  
                $return = esc_url( add_query_arg( $arr_params, $url) );
                        
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa fa-donate fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postdonation->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postdonation->date_creation)."</small></div><span>".doliprice($postdonation, 'amount', isset($postdonation->multicurrency_code) ? $postdonation->multicurrency_code : null)."</span><span>";
                $content .= doliObjectStatus($postdonation, 'donation', 2);
                $content .= "</span></a>";
            }
        } else{
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No donation', 'doliconnect')."</center></li>";
        }

        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'donation');
        $content .= "</div>";
    }
    return $content;
}
add_filter( 'customer_doliconnect_donations', 'donations_module', 10, 2);
}

//*****************************************************************************************

if ( doliCheckModules('recruitment') && doliversion('19.0.0') && !empty(get_option('doliconnectbeta')) ) {
    
function recruitment_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'recruitment', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg=='recruitment') { $menu .= " active";}
    $menu .= "'>".__( 'List of jobpositions', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'grh_doliconnect_menu', 'recruitment_menu', 10, 2);
    
function recruitment_module( $content,$url ) {
    global $current_user;
    $entity = get_current_blog_id();
    $ID = $current_user->ID;
    
    if ( isset($_GET['id']) && $_GET['id'] > 0 ) { 
        $request = "/recruitments/jobposition/".esc_attr($_GET['id']);
        $donationfo = callDoliApi("GET", $request, null, dolidelay('donation', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    }
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    
    if ( !isset($donationfo->error) && isset($_GET['id']) && isset($_GET['ref']) && ($thirdparty->id == $donationfo->fk_soc ) && ($_GET['ref'] == $donationfo->ref) && $donationfo->status != 0 ) {
        $content ='<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Job position %s', 'doliconnect'), $donationfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'recruitment', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
        $datecreation =  wp_date('d/m/Y', $donationfo->date_creation);
        $content .="<b>".__( 'Date of creation', 'doliconnect').":</b> $datecreation<br>";
        $content .= "<b>".__( 'Payment method', 'doliconnect').":</b>";
        
        $content .= "<br></div><div class='col-md-7'>";
        $content .= doliObjectStatus($donationfo, 'recruitmentjobposition', 1);
        $content .= "</div>";
        
        $content .= "</div><div class='row'><div class='col-12'>"; 
        
        $content .= doliObjectStatus($donationfo, 'recruitmentjobposition', 3);
        $content .= $donationfo->description;

        $content .= "</div></div></div>";
        $content .= doliCardFooter($donationfo, 'donation');
        $content .= "</div>";

    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request= "/recruitments/jobposition?sortfield=t.rowid&sortorder=DESC&limit=".$limit."&page=".$page."&pagination_data=true&sqlfilters=(t.fk_soc:=:'".$thirdparty->id."')";//    ."&sqlfilters=(t.fk_statut!=0)"
        $object = callDoliApi("GET", $request, null, dolidelay('recruitment', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listjobposition = $object->data; } else { $listjobposition = $object; }
        
        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'List of jobpositions', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';
        if ( doliCheckRights('recruitment', 'recruitmentjobposition', 'write') && !empty(get_option('doliconnectbeta'))) {
            $content .= '<a href="" class="list-group-item lh-condensed list-group-item-action list-group-item-primary" disabled><center><i class="fa-solid fa-plus-circle"></i> '.__( 'Create a job position', 'doliconnect').'</center></a>';  
        }
        if ( !isset( $listjobposition->error ) && $listjobposition != null ) {
            foreach ( $listjobposition as $postjobposition ) { 
                $arr_params = array( 'id' => $postjobposition->id, 'ref' => $postjobposition->ref);  
                $return = esc_url( add_query_arg( $arr_params, $url) );              
                $content .=  "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa-solid fa-id-card-clip fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postjobposition->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postjobposition->date_creation)."</small></div><span></span><span>";
                $content .= doliObjectStatus($postjobposition, 'recruitmentjobposition', 2);
                $content .= "</span></a>";
            }
        } else{
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No jobposition', 'doliconnect')."</center></li>";
        }

        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'recruitment');
        $content .= "</div>";
    }

return $content;
}
add_filter( 'grh_doliconnect_recruitment', 'recruitment_module', 10, 2);
}

//*****************************************************************************************

if ( doliCheckModules('expensereport') && doliversion('19.0.0') && doliCheckRights('expensereport', 'lire') ) {
    
function expensereport_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'expensereport', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg=='expensereport') { $menu .= " active";}
    $menu .= "'>".__( 'List of expense reports', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'grh_doliconnect_menu', 'expensereport_menu', 20, 2);
    
function expensereport_module( $content, $url ) {
    
    if ( isset($_GET['id']) && $_GET['id'] > 0 ) { 
        $request = "/expensereports/".esc_attr($_GET['id']);
        $expensereportfo = callDoliApi("GET", $request, null, dolidelay('expensereport', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        //$content .= $expensereportfo;
    }
    
    if ( !isset($expensereportfo->error) && isset($_GET['id']) && isset($_GET['ref']) && (doliConnect('user')->id == $expensereportfo->fk_user_author ) && ($_GET['ref'] == $expensereportfo->ref) && $expensereportfo->status != 0 && isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'doli-expensereports-'.$expensereportfo->id.'-'.$expensereportfo->ref)) {
        $content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Expense report %s', 'doliconnect'), $expensereportfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'expensereport', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
        $content .= "<b>".__( 'Period', 'doliconnect').":</b> ".wp_date('d/m/Y', $expensereportfo->date_debut)." au ".wp_date('d/m/Y', $expensereportfo->date_fin)."<br>";
        $content .= "<b>".__( 'Date of submition', 'doliconnect').":</b> ".wp_date('d/m/Y', $expensereportfo->date_validation)."<br>";
        $content .= "<b>".__( 'Date of approbation', 'doliconnect').":</b> ".wp_date('d/m/Y', $expensereportfo->date_approbation)."<br>";
        $content .= "<b>".__( 'Approbator', 'doliconnect').":</b> ".$expensereportfo->user_validator_infos."<br>";
        $content .= "<br></div><div class='col-md-7'>";
        $content .= doliObjectStatus($expensereportfo, 'expensereport', 1);
        $content .= "</div>";
        
        $content .= "</div><div class='row'><div class='col-12'>"; 
        $content .= doliObjectStatus($expensereportfo, 'expensereport', 3);

        $content .= "</div></div></div><ul class='list-group list-group-flush'>";
        if ( $expensereportfo->lines != null ) {
            foreach ( $expensereportfo->lines as $line ) {
            $content .= "<li class='list-group-item'>";     
            $content .= '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
            <h6 class="mb-1">'.'</h6>';
            if (isset($line->comments)) $content .= '<p class="mb-1">'.$line->comments.'</p>';
            $content .= '<small><i>('.wp_date("d/m/Y", $line->dates).')</i></small>'; 
            $content .= '</div><div class="col-4 col-md-2 text-end"><h5 class="mb-1">'.doliprice($line, 'ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';
            $content .= '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
            $content .= "</div></div></li>";
            }
        }
        $content .= dolitotal($expensereportfo);
        $content .= "</ul>";
        $content .= doliCardFooter($expensereportfo, 'expensereport');
        $content .= "</div>";
    } elseif (isset(doliConnect('user')->id)) {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        if ( isset($_GET['field']) ) { $field = esc_attr($_GET['field']); } else { $field = 'label'; }
        if ( isset($_GET['order']) ) { $order = esc_attr($_GET['order']); } else { $order = 'ASC'; }
        $request= "/expensereports?sortfield=t.rowid&sortorder=DESC&limit=".$limit."&page=".$page."&user_ids=".doliConnect('user')->id."&pagination_data=true";
        $object = callDoliApi("GET", $request, null, dolidelay('expensereport', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listexpensereport = $object->data; } else { $listexpensereport = $object; }
        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'List of expense reports', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';
        if ( doliCheckRights('expensereport', 'creer') && !empty(get_option('doliconnectbeta'))) {
            $content .= '<a href="" class="list-group-item lh-condensed list-group-item-action list-group-item-primary" disabled><center><i class="fa-solid fa-plus-circle"></i> '.__( 'Create an expense report', 'doliconnect').'</center></a>';  
        }
        if ( !isset( $listexpensereport->error ) && $listexpensereport != null && !empty(doliConnect('user'))) {
            foreach ( $listexpensereport as $postexpensereport ) { 
                $nonce = wp_create_nonce( 'doli-expensereports-'. $postexpensereport->id.'-'.$postexpensereport->ref);
                $arr_params = array( 'id' => $postexpensereport->id, 'ref' => $postexpensereport->ref, 'security' => $nonce);  
                $return = esc_url( add_query_arg( $arr_params, $url) );          
                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa-solid fa-wallet fa-3x fa-fw'></i></div><div><h6 class='my-0'>".$postexpensereport->ref."</h6><small class='text-muted'>".wp_date('d/m/Y', $postexpensereport->date_debut)." au ".wp_date('d/m/Y', $postexpensereport->date_fin)."</small></div><span></span><span>";
                $content .= doliObjectStatus($postexpensereport, 'expensereport', 2);
                $content .= "</span></a>";
            }
        } else{
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No expense report', 'doliconnect')."</center></li>";
        }
        $content .= "</ul><div class='card-body'>";
        $content .= doliPagination($object, $url, $page);
        $content .= "</div>";
        $content .= doliCardFooter($object, 'expensereport');
        $content .= "</div>";
    }
return $content;
}
add_filter( 'grh_doliconnect_expensereport', 'expensereport_module', 10, 2);
}

//*****************************************************************************************

if ( doliCheckModules('adherent') && doliCheckRights('adherent', 'lire') ) {

function members_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg=='members') { $menu .= " active";}
    $menu .= "'>".__( 'Manage my subscription', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'member_doliconnect_menu', 'members_menu', 10, 2);

function members_module( $content, $url ) {
global $current_user;

    $time = current_time( 'timestamp',1);
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $adherent = doliConnect('member', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

    $productadhesion = doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS");
    $requestp = "/products/".$productadhesion."?includesubproducts=true&includetrans=true";
    $product = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    $price = doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

    if ( isset($_POST["update_membership"]) && function_exists('doliconnect_membership') ) {
        $typeadherent = isset($_POST["typeadherent"]) ? $_POST["typeadherent"] : null;
        $adherent = doliconnect_membership($current_user, $_POST["update_membership"], $typeadherent, dolidelay('member', true));
        //$content .= var_dump($_POST["update_membership"]);
        $content .= dolialert('success', __( 'Your membership has been updated.', 'doliconnect'));
        $adherent = doliConnect('member', $current_user, false, true);
    }

    $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'Manage my subscription', 'doliconnect').'</div><div class="card-body">';

if ( isset($adherent->id) && $adherent->id > 0 && !empty($adherent->typeid) ) { 
$request= "/adherentsplus/type/".$adherent->typeid;
$adherenttype = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}

$content .= "<div class='row'><div class='col-12 col-md-5 border-end'>";
$content .="<b>".__( 'Status', 'doliconnect').":</b> ";
if ( isset($adherent->status) && $adherent->status > 0) {
    if  ($adherent->datefin == null ) {
        $content .=  "<span class='badge rounded-pill bg-danger'>".__( 'Waiting payment', 'doliconnect')."</span>";
    } else {
        if ( $adherent->datefin+86400>$time) {
            $content .=  "<span class='badge rounded-pill bg-success'>".__( 'Active', 'doliconnect')."</span>";
        } else { 
            $content .=  "<span class='badge rounded-pill bg-danger'>".__( 'Waiting payment', 'doliconnect')."</span>";
        }
    }
} elseif ( isset($adherent->status) && empty($adherent->status) ) {
    $content .=  "<span class='badge rounded-pill bg-dark'>".__( 'Terminated', 'doliconnect')."</span>";
} elseif ( isset($adherent->status) && $adherent->status == '-1' ) {
    $content .= "<span class='badge rounded-pill bg-warning'>".__( 'Waiting validation', 'doliconnect')."</span>";
} elseif ( isset($adherent->status) && $adherent->status == '-2' ) {
    $content .= "<span class='badge rounded-pill bg-dark'>".__( 'Excluded', 'doliconnect')."</span>";
} else { 
    $content .=  "<span class='badge rounded-pill bg-dark'>".__( 'No membership', 'doliconnect')."</span>"; 
}
$content .= "<br><b>".__( 'Reference', 'doliconnect').":</b> ";
if ( isset($adherent->ref) ) { 
    $content .= $adherent->ref;
} else {
    $content .=  "***"; 
}
$type=(! empty($adherent->typeid) ? doliproduct($adherenttype, 'label') : __( 'nothing', 'doliconnect'));
if ( isset($adherenttype) ) $content .=  "<br><b>".__( 'Type', 'doliconnect').":</b> ".$type." - ".doliduration($adherenttype);
$content .=  "<br><b>".__( 'Validity', 'doliconnect').":</b> ";
if ( !isset($adherent->datefin) || empty($adherent->datefin) ) { 
    $content .=  "***";
} else { 
    $content .=  wp_date('d/m/Y', $adherent->last_subscription_date_start).' '.__( 'to', 'doliconnect').' '.wp_date('d/m/Y', $adherent->last_subscription_date_end); 
}
$content .=  "<br><b>".__( 'Renewal', 'doliconnect').":</b> ".__( 'manual', 'doliconnect');
$content .=  "<br><b>".__( 'Commitment', 'doliconnect').":</b> ";
if ( (isset($adherent->datecommitment) && current_time('timestamp') > $adherent->datecommitment) || !isset($adherent->datecommitment) ) { 
    $content .=  __( 'no', 'doliconnect');
} else {
    $datefin =  wp_date('d/m/Y', $adherent->datecommitment);
    $content .= "$datefin";
}

$content .= "</div><div class='col-12 col-md-7'>";

if ( doliCheckModules('commande') && !empty($productadhesion) ) {
    if ( isset($adherent->status) && $adherent->status > 0) {
        $content .= '<div class="d-grid gap-2">';
        if  ($adherent->datefin == null ) {
            $price = doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
            $content .= doliProductCart($product, $price, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), false, array('options_member_beneficiary' => $adherent->id));
        } else {
            if ( $adherent->datefin+86400>$time) {
                $content .= "<button class='btn btn-light btn-block' disabled>".sprintf(__('Renew from %s', 'doliconnect'), wp_date('d/m/Y', $adherent->datefin))."</button>";
            } else { 
                $price = doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
                $content .= doliProductCart($product, $price, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), false, array('options_member_beneficiary' => $adherent->id));
            }
        }
        $content .= '</div>';
    } elseif ( isset($adherent->status) && empty($adherent->status) ) {
        $content .= '<div class="alert alert-primary d-flex align-items-center" role="alert">';
        $content .= '<i class="fa-solid fa-circle-info fa-beat"></i>';
        $content .= '<div>'.__('Your subscription is terminated. For keeping your benefits, please renew it.', 'doliconnect').'</div>';
        $content .= '</div>';
    } elseif ( isset($adherent->status) && $adherent->status == '-1' ) {
        $content .= '<div class="alert alert-primary d-flex align-items-center" role="alert">';
        $content .= '<i class="fa-solid fa-circle-info fa-beat"></i>';
        $content .= '<div>'.__('Your request has been registered. You will be notified by email at validation.', 'doliconnect').'</div>';
        $content .= '</div>';
    } elseif ( isset($adherent->status) && $adherent->status == '-2' ) {
        $content .= '<div class="alert alert-primary d-flex align-items-center" role="alert">';
        $content .= '<i class="fa-solid fa-circle-info fa-beat"></i>';
        $content .= '<div>'.__('Please contact us for more informations or subscribe again.', 'doliconnect').'</div>';
        $content .= '</div>';
    }
}
$content .= '<br><div class="d-grid gap-2">';
if ( ! empty($adherent) && $adherent->statut != '-2' ) {
$content .= '<div class="btn-group" role="group" aria-label="Update membership">';
    if (empty($adherent->statut)) { 
        $title = __( 'Reactivate my subscription', 'doliconnect');
    } else {
        $title = __( 'Update', 'doliconnect');
    }
    $content .= doliModalButton('editmembership', 'editmembership', $title, 'button', 'btn btn-outline-secondary'); 
    if ( $adherent->statut != '0' ) {
        $content .= doliModalButton('resiliatemembership', 'resiliatemembership', __( 'Resiliate', 'doliconnect'), 'button', 'btn btn-outline-secondary'); 
    }
    $content .= '</div>';
 } else { 
    $content .= '<div class="d-grid gap-2">';
    $content .= doliModalButton('editmembership', 'editmembership', __('Become a member', 'doliconnect'), 'button' , 'btn btn-outline-secondary btn-block');
    $content .= '</div>';
}
$content .= '</div>';

$content .= "</div></div>";

if( has_action('mydoliconnectmemberform') ) {
    $content .= do_action('mydoliconnectmemberform', $adherent);
}
$content .= "</div>";

if ( isset($adherent->id) && doliCheckRights('adherent', 'cotisation', 'lire') ) {
    $content .= "<ul class='list-group list-group-flush'>";
    $limit=12;
    $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
    if ($adherent->id > 0) {
        $object = callDoliApi("GET", "/subscriptions?sortfield=dateadh&sortorder=DESC&limit=".$limit."&page=".$page."&pagination_data=true&sqlfilters=t.fk_adherent:=:".$adherent->id, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listcotisation = $object->data; } else { $listcotisation = $object; }
    } 
    if ( isset($listcotisation) && !isset($listcotisation->error) && $listcotisation != null ) { 
        foreach ( $listcotisation as $cotisation ) {                                                                                 
            $dated =  wp_date('d/m/Y', $cotisation->dateh);
            $datef =  wp_date('d/m/Y', $cotisation->datef);
            $content .= "<li class='list-group-item'><table width='100%' border='0'><tr><td>";
            if ($cotisation->fk_type > 0) {
                if (doliversion('20.0.0')){ 
                    $type= callDoliApi("GET", "/members/types/".$cotisation->fk_type, null, dolidelay('member'));
                } else {
                    $type= callDoliApi("GET", "/memberstypes/".$cotisation->fk_type, null, dolidelay('member'));
                }
            }
            $content .= doliproduct($type, 'label');
            $content .= "</td><td class='text-center'>".$dated." ".__( 'to', 'doliconnect')." ".$datef;
            $content .= "</td><td class='text-end'><b>".doliprice($cotisation->amount)."</b></td></tr></table><span></span></li>";
        }
    } else { 
        $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No subscription', 'doliconnect')."</center></li>";
    }
    $content .= '</ul><div class="card-body">';
    $content .= doliPagination($object, $url, $page);
    $content .= '</div>';
    } else { 
        $content .= "<ul class='list-group list-group-flush'>";
        $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No subscription', 'doliconnect')."</center></li>";
        $content .= '</ul>';
    }
$content .= doliCardFooter($adherent, 'member');
$content .= '</div>';
return $content;
}
add_filter( 'member_doliconnect_members', 'members_module', 10, 2);
}

//*****************************************************************************************

function representatives_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'representatives', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'representatives' ) { $menu .= " active"; }
    $menu .= "'>".__( 'My sales representatives', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'settings_doliconnect_menu', 'representatives_menu', 10, 2);

function representatives_module( $url ) {
global $current_user;
    $thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    
    $content ='<div class="card shadow-sm"><div class="card-header">'.__( 'My sales representatives', 'doliconnect').'</div>';

    $request = "/thirdparties/".$thirdparty->id."/representatives?mode=1";
    $representatives = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
 
    if ( !isset( $representatives->error ) && $representatives != null ) {
        $content .= '<div class="card-body"><div class="row row-cols-1 row-cols-md-2 g-4">';
        foreach ( $representatives as $representative ) { 
            $content .= doliUserCard($representative, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        }
        $content .= '</div></div>';
    } else {
        $content .= "<ul class='list-group list-group-flush'><li class='list-group-item list-group-item-light'><center>".__( 'No sales representative', 'doliconnect')."</center></li></ul>";
    }
    $content .= doliCardFooter($representatives, 'thirdparty');
    $content .= '</div>';
return $content;
}
add_filter( 'settings_doliconnect_representatives', 'representatives_module', 10, 2);

//*****************************************************************************************

if ( doliCheckModules('ticket') ) {

function tickets_menu( $menu, $arg ) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'tickets', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ( $arg == 'tickets' ) { $menu .= " active"; }
    $menu .= "'>".__( 'My support tickets', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'settings_doliconnect_menu', 'tickets_menu', 20, 2);

function tickets_module( $content, $url ) {
global $current_user;

if ( isset($_GET['id']) && $_GET['id'] > 0 ) {  
    $request = "/tickets/".esc_attr($_GET['id']);
    $ticketfo = callDoliApi("GET", $request, null, dolidelay('ticket', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    //$content .= $ticket;
}

$thirdparty = doliConnect('thirdparty', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if ( isset($_GET['id']) && isset($_GET['ref']) && ( $thirdparty->id == $ticketfo->socid ) && ($_GET['ref'] == $ticketfo->ref ) ) {

$content = '<div class="card shadow-sm"><div class="card-header">'.sprintf(__( 'Ticket %s', 'doliconnect'), $ticketfo->ref).'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'tickets', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><div class="card-body"><div class="row"><div class="col-md-5">';
$dateticket =  wp_date('d/m/Y', $ticketfo->datec);
$content .= "<b>".__( 'Date of creation', 'doliconnect').": </b> $dateticket<br>";
$content .= "<b>".__( 'Type and category', 'doliconnect').": </b> ".__($ticketfo->type_label, 'doliconnect').", ".__($ticketfo->category_label, 'doliconnect')."<br>";
$content .= "<b>".__( 'Severity', 'doliconnect').": </b> ".__($ticketfo->severity_label, 'doliconnect')."<br>";
$content .= "</div><div class='col-md-7'>";

$content .= doliObjectStatus($ticketfo, 'ticket', 1);

$content .= "</div></div>";

$content .= doliObjectStatus($ticketfo, 'ticket', 3);
$content .=  "</div><ul class='list-group list-group-flush'>
<li class='list-group-item list-group-item-light list-group-item-action'><h5 class='mb-1'>".__( 'Subject', 'doliconnect').": ".$ticketfo->subject."</h5>
<p class='mb-1'>".__( 'Initial message', 'doliconnect').": ".$ticketfo->message."</p></li>";
$content .= "<li class='list-group-item list-group-item-light list-group-item-action'>";
if (empty($ticketfo->fk_statut)) {
    $content .=  dolialert('info', __( 'You will be able to post a message after we have read your ticket', 'doliconnect'));
} elseif ($ticketfo->fk_statut >= '8') {
    $content .=  dolialert('info', __( 'This ticket is closed so you can not comment it anymore', 'doliconnect'));
} elseif ( $ticketfo->fk_statut < '8' && $ticketfo->fk_statut > '0' ) {
    $arr_params = array( 'id' => $ticketfo->id, 'ref' => $ticketfo->ref);  
    $return = esc_url( add_query_arg( $arr_params, $url) );
    $content .= '<div id="doliticket-alert"></div><form id="doliticket-form" method="post" class="was-validated" action="'.admin_url('admin-ajax.php').'">';
    $content .= doliAjax('doliticket', $return, 'newMessage');
    $content .= '<div class="form-floating mb-2"><textarea class="form-control" name="ticket_newmessage" id="ticket_newmessage" placeholder="Leave a comment here" style="height: 200px" required></textarea>
    <label for="ticket_newmessage"><i class="fa-solid fa-comment"></i> '.__( 'Message', 'doliconnect').'</label></div>';
    $content .= '<div class="d-grid gap-2"><input type="hidden" name="id" value="'.$ticketfo->id.'"><input type="hidden" name="track_id" value="'.$ticketfo->track_id.'"><button class="btn btn-outline-secondary" type="submit">'.__( 'Answer', 'doliconnect').'</button></form></div>';
}
$content .= '</li>';

if ( isset($ticketfo->messages) ) {
    foreach ( $ticketfo->messages as $msg ) {
        $datemsg =  wp_date('d/m/Y - H:i', $msg->datec);  
        $content .=  "<li class='list-group-item list-group-item-light list-group-item-action'><b>$datemsg $msg->fk_user_action_string</b><br>$msg->message</li>";
    }
} 
$content .= '</ul>';
$content .= doliCardFooter($ticketfo, 'ticket');
$content .= '</div>';

} elseif ( isset($_GET['action']) && $_GET['action'] == 'create' ) {

$content .= '<div id="doliticket-alert"></div><form id="doliticket-form" method="post" class="was-validated" action="'.admin_url('admin-ajax.php').'">';

$content .= doliAjax('doliticket', $url, 'create');

$content .= '<div class="card shadow-sm"><div class="card-header">'.__( 'Create ticket', 'doliconnect').'<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( add_query_arg( 'module', 'tickets', doliconnecturl('doliaccount')) ).'"><i class="fa-solid fa-arrow-left"></i> '.__( 'Back', 'doliconnect').'</a></div><ul class="list-group list-group-flush">';
$content .= "<li class='list-group-item list-group-item-light list-group-item-action'>";

$content .= '<div class="row mb-2 g-2"><div class="col-md">';

$type = callDoliApi("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100&lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if ( isset($type) ) { 
$content .=  '<div class="form-floating"><select class="form-select" id="ticket_type"  name="ticket_type" aria-label="'.__( 'Type', 'doliconnect').'" required>';
if ( count($type) > 1 ) {
$content .= "<option value='' disabled selected >".__( '- Select -', 'doliconnect')."</option>";
}
foreach ($type as $postv) {
$content .=  "<option value='".$postv->code."' ";
if ( isset($_GET['type']) && $_GET['type'] == $postv->code ) {
$content .=  "selected ";
} elseif ( $postv->use_default == 1 ) {
$content .=  "selected ";}
$content .=  ">".$postv->label."</option>";
}
$content .= '</select><label for="ticket_type">'.__( 'Type', 'doliconnect').'</label></div>';
}

$content .= '</div><div class="col-md">';

$cat = callDoliApi("GET", "/setup/dictionary/ticket_categories?sortfield=pos&sortorder=ASC&limit=100&lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if ( isset($cat) ) { 
$content .= '<div class="form-floating"><select class="form-select" id="ticket_category"  name="ticket_category" aria-label="'.__( 'Category', 'doliconnect').'" required>';
if ( count($cat) > 1 ) {
$content .= "<option value='' disabled selected >".__( '- Select -', 'doliconnect')."</option>";
}
$categoryId = null;
foreach ( $cat as $postv ) {
    $content .= "<option value='".$postv->code."' ";
    if ( $postv->use_default == 1 ) {
        $categoryId = $postv->rowid;
        $content .= "selected ";
    }
    $content .= ">".$postv->label."</option>";
}   
$content .= '</select><label for="ticket_category">'.__( 'Category', 'doliconnect').'</label></div>';
} 

$content .= '</div><div class="col-md">';

$severity = callDoliApi("GET", "/setup/dictionary/ticket_severities?sortfield=pos&sortorder=ASC&limit=100&lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
if ( isset($severity) ) { 
$content .= '<div class="form-floating"><select class="form-select" id="ticket_severity"  name="ticket_severity" aria-label="'.__( 'Severity', 'doliconnect').'" required>';
if ( count($severity) > 1 ) {
$content .= "<option value='' disabled selected >".__( '- Select -', 'doliconnect')."</option>";
}
foreach ( $severity as $postv ) {
$content .= "<option value='".$postv->code."' ";
if ( $postv->use_default == 1 ) {
$content .= "selected ";}
$content .= ">".$postv->label."</option>";
}
$content .= '</select><label for="ticket_severity">'.__( 'Severity', 'doliconnect').'</label></div>';
}

$content .= '</div></div>';

if ( doliversion('11.0.0') ) {
$representatives = callDoliApi("GET", "/thirdparties/".$thirdparty->id."/representatives?mode=0", null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
if ( !isset($representatives->error) && $representatives != null ) {
$content .= '<div class="form-floating"><select class="form-select" id="fk_user_assign"  name="fk_user_assign" aria-label="'.__( 'Sales representative', 'doliconnect').'" required>';
if ( count($representatives) > 1 ) {
$content .= "<option value='' disabled selected >".__( '- Select -', 'doliconnect')."</option>";
}
foreach ($representatives as $postv) {
$content .= "<option value='".$postv->id."' >".$postv->firstname." ".$postv->lastname;
if (!empty($postv->job)) $content .= ", ".$postv->job;
$content .= "</option>";
}
$content .= '</select><label for="fk_user_assign">'.__( 'Sales representative', 'doliconnect').'</label></div>';
}
}

if (!empty(doliconnectid('dolifaq'))) {
    $content .= '</li><li class="list-group-item list-group-item-light list-group-item-action">';
    $content .= doliFaqForm($categoryId);
    $content .= '<script type="text/javascript">';
    $content .= '(function ($) {
    $(document).ready(function () {
      $("#ticket_category").on("change",function(){
        var ticket_categoryId = $(this).val();
        $.ajax({
          url :"'.admin_url('admin-ajax.php').'",
          type:"POST",
          cache:false,
          data: {
            "action": "doliselectform_request",
            "case": "update",
            "ticket_categoryId": ticket_categoryId,
          },
        }).done(function(response) {
        console.log (response);
          if ( document.getElementById("state_form") ) { 
            document.getElementById("state_form").innerHTML = response.data.state_id;
          }
        });
      });
    });
})(jQuery);';
    $content .=  '</script>';
}

$content .= '</li><li class="list-group-item list-group-item-light list-group-item-action">';

$content .= '<div class="form-floating mb-2"><input type="text" class="form-control" id="ticket_subject" name="ticket_subject" value="" placeholder="subject" required>
<label for="ticket_subject"><i class="fa-solid fa-envelope-open-text"></i> '.__( 'Subject', 'doliconnect').'</label></div>';
$content .= '<div class="form-floating"><textarea class="form-control" name="ticket_message" id="ticket_message" placeholder="Leave a comment here" style="height: 200px" required></textarea>
<label for="ticket_message"><i class="fa-solid fa-comment"></i> '.__( 'Message', 'doliconnect').'</label></div>';

$content .= '</li><li class="list-group-item list-group-item-light list-group-item-action">';

$content .= dolicaptcha('doliticket');

$content .= '</li></ul>';
$content .=  "<div class='card-body'><div class='d-grid gap-2'><button type='submit' class='btn btn-outline-secondary'>".__( 'Send', 'doliconnect')."</button></div></div>";

$content .= '</div></form>';
    } else {
        $limit=12;
        $page = doliPG(isset($_GET['pg'])?$_GET['pg']:null);
        $request = "/tickets?socid=".$thirdparty->id."&sortfield=t.rowid&sortorder=DESC&limit=".$limit."&page=".$page."&pagination_data=true";
        $object= callDoliApi("GET", $request, null, dolidelay('ticket', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( doliversion('21.0.0') && isset($object->data) ) { $listticket = $object->data; } else { $listticket = $object; }

        $content = '<div class="card shadow-sm"><div class="card-header">'.__( 'My support tickets', 'doliconnect').' ('.(isset($object->pagination->total)?$object->pagination->total:'x').')</div><ul class="list-group list-group-flush">';  
        //if ( doliCheckRights('expensereport', 'creer') ) {
            $content .= '<a href="'.esc_url( add_query_arg( 'action', 'create', $url) ).'" class="list-group-item lh-condensed list-group-item-action list-group-item-primary" disabled><center><i class="fa-solid fa-plus-circle"></i> '.__( 'Create a ticket', 'doliconnect').'</center></a>';  
        //}
        if ( !isset($listticket->error) && $listticket != null ) {
            foreach ($listticket as $postticket) {                                                                                 
                $arr_params = array( 'id' => $postticket->id, 'ref' => $postticket->ref);  
                $return = esc_url( add_query_arg( $arr_params, $url) );

                $content .= "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'><div><i class='fa-solid fa-question-circle fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postticket->subject</h6><small class='text-muted'>".wp_date('d/m/Y', $postticket->datec)."</small></div><span class='text-center'>".__($postticket->type_label, 'doliconnect')."<br/>".__($postticket->category_label, 'doliconnect')."</span><span>";
                $content .= doliObjectStatus($postticket, 'ticket', 2);
                $content .= "</span></a>";
            }
        } else {
            $content .= "<li class='list-group-item list-group-item-light'><center>".__( 'No ticket', 'doliconnect')."</center></li>";
        }
        $content .= '</ul><div class="card-body">';
        $content .= doliPagination($object, $url, $page);
        $content .= '</div>';
        $content .= doliCardFooter($object, 'ticket');
        $content .= '</div>';
    }
return $content;
}
add_filter( 'settings_doliconnect_tickets', 'tickets_module', 10, 2);
}

//*****************************************************************************************

function settings_menu( $menu, $arg) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'settings', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg=='settings') { $menu .= " active"; }
    $menu .= "'>".__( 'Safety and appearance', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'settings_doliconnect_menu', 'settings_menu', 30, 2);

function settings_module( $content,$url) {
global $wpdb, $current_user;

$content = '<div id="dolisettings-alert"></div><form id="dolisettings-form" method="post" class="was-validated" action="'.admin_url('admin-ajax.php').'">';

$content .= doliAjax('dolisettings',  null, 'settings');

$content .= '<div class="card shadow-sm"><div class="card-header">'.__( 'Settings & security', 'doliconnect').'</div><ul class="list-group list-group-flush">';
$content .= "<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-check form-switch'><input type='checkbox' class='form-check-input' name='loginmailalert' id='loginmailalert' ";
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $current_user->ID ) {
$content .= " disabled";
} elseif ( $current_user->loginmailalert == 'on' ) { $content .= " checked"; }        
$content .= " onchange='submit()'><label class='form-check-label w-100' for='loginmailalert'> ".__( 'Receive a email notification at each connection', 'doliconnect')."</label>
</div></li>";

$privacy=$wpdb->prefix."doliprivacy";
if ( $current_user->$privacy ) {
$content .= "<li class='list-group-item list-group-item-light list-group-item-action'>";
$content .= '<div class="form-floating">
<input type="text" class="form-control" id="floatingInput" value="'.wp_date( get_option( 'date_format' ).' - '.get_option('time_format'), $current_user->$privacy, false).'" readonly>
<label for="floatingInput">'.__( 'Privacy policy', 'doliconnect').'</label>
</div>';
$content .= "</li>";
}

if ( is_plugin_active( 'two-factor/two-factor.php' ) && current_user_can('administrator') && !empty(get_option('doliconnectbeta')) ) {
$content .= '<li class="list-group-item list-group-item-light list-group-item-action">';
require_once( ABSPATH . 'wp-content/plugins/two-factor/class-two-factor-core.php');

		?>
					<table class="table">
						<thead>
							<tr>
								<th ><?php esc_html_e( 'Enabled',  'doliconnect'); ?></th>
								<th ><?php esc_html_e( 'Primary',  'doliconnect'); ?></th>
								<th ><?php esc_html_e( 'Description',  'doliconnect'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( Two_Factor_Core::get_providers() as $class => $object ) : ?>
							<tr>
								<td><input type="checkbox" class="" name="<?php echo esc_attr( Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY ); ?>[]" value="<?php echo esc_attr( $class ); ?>" <?php //checked( in_array( $class, $providers ) ); ?> /></td>
								<td><input type="radio" class="" name="<?php echo esc_attr( Two_Factor_Core::PROVIDER_USER_META_KEY ); ?>" value="<?php echo esc_attr( $class ); ?>" <?php //checked( $class, $primary_provider_key ); ?> /></td>
								<td>
									<?php $object->$content .=_label(); ?>
									<?php do_action( 'two-factor-user-options-' . $class, $current_user ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
		<?php
		//do_action( 'show_user_security_settings', $current_user );
$content .= "</li>";    
}
$content .= '</ul>';
$content .= "<div class='card-body'><div class='d-grid gap-2'><button id='doliuserinfos-button' class='btn btn-outline-secondary' type='submit' ";
if (!doliCheckRights('societe', 'creer')) { $content .= 'disabled'; }
$content .= ">".__( 'Update', 'doliconnect')."</button></div>";
$content .= '</form></div>';
//$content .= doliCardFooter($request, 'expensereport', $expensereportfo);
$content .= '</div>';

if (current_user_can('administrator') && !empty(get_option('doliconnectbeta')) ) { 
//$content .=  '<div class="audio-player">
//    <audio controls>
//        <source src="'.esc_url( get_option('doliconnect_audio_file') ).'" type="audio/mpeg">
//        '._e('Votre navigateur ne supporte pas l\'élément audio.', 'doliconnect').'
//    </audio>
//</div>';
/*
$content .=  '<style>';
?>
.blur{
  -webkit-filter: blur(5px);
  -moz-filter: blur(5px);
  -o-filter: blur(5px);
  -ms-filter: blur(5px);
  filter: blur(5px);
}
<?php
$content .=  '</style>';
*/

function generate_license($suffix = null) {
    // Default tokens contain no "ambiguous" characters: 1,i,0,o
    if(isset($suffix)){
        // Fewer segments if appending suffix
        $num_segments = 3;
        $segment_chars = 6;
    }else{
        $num_segments = 5;
        $segment_chars = 5;
    }
    $tokens = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $license_string = '';
    // Build Default License String
    for ($i = 0; $i < $num_segments; $i++) {
        $segment = '';
        for ($j = 0; $j < $segment_chars; $j++) {
            $segment .= $tokens[rand(0, strlen($tokens)-1)];
        }
        $license_string .= $segment;
        if ($i < ($num_segments - 1)) {
            $license_string .= '-';
        }
    }
    // If provided, convert Suffix
    if(isset($suffix)){
        if(is_numeric($suffix)) {   // Userid provided
            $license_string .= '-'.strtoupper(base_convert($suffix,10,36));
        }else{
            $long = sprintf("%u\n", ip2long($suffix),true);
            if($suffix === long2ip($long) ) {
                $license_string .= '-'.strtoupper(base_convert($long,10,36));
            }else{
                $license_string .= '-'.strtoupper(str_ireplace(' ','-',$suffix));
            }
        }
    }
    return $license_string;
}

//$content .= generate_license();

}

return $content;
}
add_filter( 'settings_doliconnect_settings', 'settings_module', 30, 2);

//*****************************************************************************************

function gdpr_menu( $menu, $arg) {
    $menu .= "<a href='".esc_url( add_query_arg( 'module', 'gdpr', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-light list-group-item-action";
    if ($arg=='gdpr') { $menu .= " active";}
    $menu .= "'>".__( 'Privacy', 'doliconnect')."</a>";
    return $menu;
}
add_filter( 'settings_doliconnect_menu', 'gdpr_menu', 30, 2);

function gdpr_module( $content, $url) {
global $current_user;

		$params = array();
		if ( isset( $instance['request_type'] ) ) {
			if ( 'export' === $instance['request_type'] ) {
				$params['request_type'] = 'export';
			} elseif ( 'remove' === $instance['request_type'] ) {
				$params['request_type'] = 'remove';
			}
		}
		return doli_gdrf_data_request_form( $params ); 
}
add_filter( 'settings_doliconnect_gdpr', 'gdpr_module', 80, 2);

?>