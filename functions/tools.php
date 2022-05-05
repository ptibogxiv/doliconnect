<?php

function dolimenu($name, $traduction, $right, $content) {


}

function dolicheckie($server) {
$return = false;
$ua = htmlentities($server, ENT_QUOTES, 'UTF-8');
if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false)) {
$return = '<div class="float-start"><i class="fab fa-internet-explorer fa-3x a-fw"></i> </div><div class="text-justify">'.__( 'Dear user, you are using Internet Explorer. We regret to inform you that it is no longer supported by our site. You must now use a modern browser.', 'doliconnect').'</div>';
$return .= '<p class="text-center"><a href="https://www.microsoft.com/edge" target="_blank"><i class="fab fa-edge"></i> '.__( 'Download Edge', 'doliconnect').'</a> | <a href="https://www.mozilla.org/firefox/new/" target="_blank"><i class="fab fa-firefox"></i> '.__( 'Download Firefox', 'doliconnect').'</a> | <a href="https://www.google.com/chrome/" target="_blank"><i class="fab fa-chrome"></i> '.__( 'Download Chrome', 'doliconnect').'</a></p>';
}
return $return;
}

function doliCheckRights($right1, $right2 = null, $right3 = null, $right4 = null, $version = '13.0.0') {
$return = false;
if ( doliversion($version) ) {
if (!empty($right2) && preg_match("/_advance/i", $right2) && !doliconst('MAIN_USE_ADVANCED_PERMS')) { 
return true;
} else {
$user = callDoliApi("GET", "/users/info?includepermissions=1", null, dolidelay('dolibarr')); 
if (isset($user->rights)) {
$user = $user->rights->$right1;
} else {
$user = null;
$return = true;
}
if ($user && !empty($right2) && isset($user->$right2)) { $user = $user->$right2; } elseif (!empty($right2)) { $user = null; }
if ($user && !empty($right3) && isset($user->$right3)) { $user = $user->$right3; } elseif (!empty($right3)) { $user = null; }
if ($user && !empty($right4) && isset($user->$right4)) { $user = $user->$right4; } elseif (!empty($right4)) { $user = null; }
if (isset($user) && !empty($user)) {
$return = true;
} 
}} else {
$return = true;
}
return $return;
}

function dolicaptcha($id = null) {
  $arrX = array(
    "car"=>array("label"=>__( "car", "doliconnect"),"icon"=>"car"),
    "carrot"=>array("label"=>__( "carrot", "doliconnect"),"icon"=>"carrot"),
    "male"=>array("label"=>__( "male", "doliconnect"),"icon"=>"male"),
    "laptop"=>array("label"=>__( "laptop", "doliconnect"),"icon"=>"laptop"),
    "female"=>array("label"=>__( "female", "doliconnect"),"icon"=>"female"),
    "seedling"=>array("label"=>__( "seedling", "doliconnect"),"icon"=>"seedling"),
    "bacterium"=>array("label"=>__( "bacterium", "doliconnect"),"icon"=>"bacterium"));
 
  // get 2 random indexes from array $arrX
  $randIndex = array_rand($arrX, 4);
  $controle = array_rand($randIndex, 1); 

  $captcha = '<div id="'.$id.'-captcha"><input type="hidden" name="ctrldolicaptcha" value="'.wp_create_nonce( 'ctrldolicaptcha-'.$arrX[$randIndex[$controle]]['icon']).'"><label for="btndolicaptcha" class="form-label">'.__( 'Please select the correct icon: ', 'doliconnect').''.$arrX[$randIndex[$controle]]['label'].'</label><div class="d-flex btn-group" role="group" aria-label="Basic radio toggle button group" required>
  <input type="radio" class="btn-check" name="btndolicaptcha" id="btndolicaptcha1" value="'.$arrX[$randIndex[0]]['icon'].'" autocomplete="off">
  <label class="btn btn-outline-secondary" for="btndolicaptcha1"><i class="fas fa-'.$arrX[$randIndex[0]]['icon'].' fa-fw"></i></label>

  <input type="radio" class="btn-check" name="btndolicaptcha" id="btndolicaptcha2" value="'.$arrX[$randIndex[1]]['icon'].'" autocomplete="off">
  <label class="btn btn-outline-secondary" for="btndolicaptcha2"><i class="fas fa-'.$arrX[$randIndex[1]]['icon'].' fa-fw"></i></label>

  <input type="radio" class="btn-check" name="btndolicaptcha" id="btndolicaptcha3" value="'.$arrX[$randIndex[2]]['icon'].'" autocomplete="off">
  <label class="btn btn-outline-secondary" for="btndolicaptcha3"><i class="fas fa-'.$arrX[$randIndex[2]]['icon'].' fa-fw"></i></label>

  <input type="radio" class="btn-check" name="btndolicaptcha" id="btndolicaptcha4" value="'.$arrX[$randIndex[3]]['icon'].'" autocomplete="off">
  <label class="btn btn-outline-secondary" for="btndolicaptcha4"><i class="fas fa-'.$arrX[$randIndex[3]]['icon'].' fa-fw"></i></label>
</div></div>';

return $captcha;
}

function dolisanitize($object) {

if (isset($object['firstname'])) $object['firstname'] = ucfirst(strtolower(stripslashes(sanitize_text_field($object['firstname']))));
if (isset($object['lastname'])) $object['lastname'] = strtoupper(stripslashes(sanitize_text_field($object['lastname'])));
if (isset($object['name'])) { $object['name'] = strtoupper(stripslashes(sanitize_text_field($object['name'])));
} elseif (isset($object['morphy']) && $object['morphy'] != 'mor' && get_option('doliconnect_disablepro') != 'mor' ) {
$object['name'] = $object['firstname']." ".$object['lastname'];
} else {
$object['name'] = null;
} 
if (isset($object['name_alias'])) $object['name_alias'] = strtoupper(stripslashes(sanitize_text_field($object['name_alias'])));
if (isset($object['address'])) $object['address'] = stripslashes(sanitize_textarea_field($object['address']));
if (isset($object['ziptown'])) {
  $object['zip'] = explode(',', $object['ziptown'])[0];
  $object['town'] = explode(',', $object['ziptown'])[1];
} 
if (isset($object['zip'])) $object['zip'] = strtoupper(stripslashes(sanitize_text_field($object['zip'])));
if (isset($object['town'])) $object['town'] = strtoupper(stripslashes(sanitize_text_field($object['town'])));
if (isset($object['email'])) $object['email'] = sanitize_email($object['email']);
if (isset($object['url'])) $object['url'] = sanitize_text_field($object['url']);
if (isset($object['note_public'])) $object['note_public'] = stripslashes(sanitize_textarea_field($object['note_public']));
if (isset($object['tva_intra'])) $object['tva_intra'] = strtoupper(sanitize_text_field($object['tva_intra']));

return $object;
}

function doliversion($version) {
$ret = false;
if (!empty(get_site_option('dolibarr_public_url')) && !empty(get_site_option('dolibarr_private_key'))) {
$dolibarr = callDoliApi("GET", "/status", null, dolidelay('dolibarr'));
if ( is_object($dolibarr) && isset($dolibarr->success) && isset($dolibarr->success->dolibarr_version)) $versiondoli = explode("-", $dolibarr->success->dolibarr_version);
if ( is_object($dolibarr) && isset($versiondoli) && version_compare($versiondoli[0], $version) >= 0 ) {
$ret = $versiondoli[0];
}
}
return $ret;
}
add_action( 'admin_init', 'doliversion', 5, 1); 

function dolipage($object, $url, $page = 0, $limit = 8) {

if (empty($object) || isset($object->error)) {
$count = 0;
} else { 
$count = count($object);
}

$pagination = "<nav aria-label='Page navigation example'><ul class='pagination pagination-sm'>";
if ($page > '1') {
$pagination .= '<li class="page-item">
      <a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page)), $url) ).'" aria-label="Previous">
        <span aria-hidden="true">'.__( 'Previous', 'doliconnect').'</span>
        <span class="sr-only">'.__( 'Previous', 'doliconnect').'</span>
     </a>
  </li>';
}
if ($page > 0) {
$pagination .= '<li class="page-item"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page)), $url) ).'">'.esc_attr($page).'</a></li>';
}    
$pagination .= '<li class="page-item active"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+1)), $url) ).'">'.esc_attr($page+1).'</a></li>';
if ($count >= $limit) {
$pagination .= '<li class="page-item"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+2)), $url) ).'">'.esc_attr($page+2).'</a></li>';
if ($page < 1) {
//$pagination .= '<li class="page-item"><a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+3)), $url) ).'">'.esc_attr($page+3).'</a></li>';
} 
$pagination .= '<li class="page-item">
      <a class="page-link" href="'.esc_url( add_query_arg( array( 'pg' => esc_attr($page+2)), $url) ).'" aria-label="Next">
        <span aria-hidden="true">'.__( 'Next', 'doliconnect').'</span>
        <span class="sr-only">'.__( 'Next', 'doliconnect').'</span>
      </a>
  </li>';
}
$pagination .= "</ul></nav>";
return $pagination;
}

function doliconnect_image($module, $id, $options = array(), $refresh = false) {

$class = isset($options['class']) ? $options['class'] : 'img-fluid rounded-lg';
$entity = dolibarr_entity(isset($options['entity'])?$options['entity']:null);
if (is_numeric($id)) {
$imgs = callDoliApi("GET", "/documents?modulepart=".$module."&id=".$id, null, dolidelay('document', $refresh), $entity);   
$image = "<div class='row'>";
$subdir = '';
$dir = '/'.$id;
if ($module == 'category') {
$num = preg_replace('/([^0-9])/i', '', $id);
$subdir = substr($num, 1, 1).'/'.substr($num, 0, 1).'/'.$id.'/';
$dir = '/'.substr($num, 1, 1).'/'.substr($num, 0, 1).'/'.$id;
}
if ( !isset($imgs->error) && $imgs != null ) {
$imgs = array_slice((array) $imgs, 0, isset($options['limit'])?$options['limit']:null);
if (empty($options['limit'])) $image .= "<div class='card-columns'>";
foreach ($imgs as $img) {
$up_dir = wp_upload_dir();
if (empty($options['limit'])) { $image .= "<div class='card'>";
} else {
$image .= "<div class='col'>";
}
$file=$up_dir['basedir'].'/doliconnect/'.$module.$dir.'/'.$img->relativename;
if (!is_file($file)) {
$imgj =  callDoliApi("GET", "/documents/download?modulepart=".$module."&original_file=".$subdir.$img->level1name."/".$img->relativename, null, dolidelay('document', $refresh), $entity);
//$image .= var_dump($imgj);
$imgj = (array) $imgj; 
if (is_array($imgj) && !isset($imgj['error']) && preg_match('/^image/', $imgj['content-type'])) {
//$data = "data:".$imgj['content-type'].";".$imgj['encoding'].",".$imgj['content'];

if (!is_dir($up_dir['basedir'].'/doliconnect/'.$module.$dir)) {
mkdir($up_dir['basedir'].'/doliconnect/'.$module.$dir, 0755, true);
}
$size = null;
if (isset($options['size'])) $size = '-'.$options['size'];
//$files = glob($up_dir['basedir'].'/doliconnect/'.$module.'/'.$id."/*");
//foreach($files as $file){
//if(is_file($file))
//unlink($file); 
//}
$file=$up_dir['basedir'].'/doliconnect/'.$module.$dir.'/'.$img->relativename;
file_put_contents($file, base64_decode($imgj['content']));

if (!is_file($up_dir['basedir'].'/doliconnect/'.$module.$dir.'/'.explode('.', $img->relativename, 2)[0].$size.'.'.explode('.', $img->relativename, 2)[1])) {
$imgy = wp_get_image_editor($file); 
$imgy->resize( 350, 350, true );
$avatar = $imgy->generate_filename($size,$up_dir['basedir']."/doliconnect/".$module.$dir."/", NULL );
$imgy->save($avatar);
}
$image .= "<img src='".$up_dir['baseurl'].'/doliconnect/'.$module.$dir.'/'.explode('.', $img->relativename, 2)[0].$size.'.'.explode('.', $img->relativename, 2)[1]."' class='";
if (empty($options['limit'])) {
$image .= "img-fluid card-img";
} else {
$image .=  $class;
}
$image .= "' alt='".$img->relativename."' loading='lazy'>";

} else {
$image .= "<i class='fa fa-cube fa-fw fa-2x'></i>";
}
} else {
$picture = '/doliconnect/'.$module.$dir.'/'.$img->relativename;
if (isset($options['size'])) {
$picture2 = '/doliconnect/'.$module.$dir.'/'.explode('.', $img->relativename, 2)[0].'-'.$options['size'].'.'.explode('.', $img->relativename, 2)[1];
$picture = $picture2;
}
if (isset($options['size']) && !is_file($up_dir['basedir'].$picture)) {
$imgy = wp_get_image_editor($file); 
$imgy->resize( 350, 350, true );
$avatar = $imgy->generate_filename($options['size'],$up_dir['basedir']."/doliconnect/".$module.$dir."/", NULL );
$imgy->save($avatar);
}
$image .= "<img src='".$up_dir['baseurl'].$picture."' class='";
if (empty($options['limit'])) {
$image .= "img-fluid card-img";
} else {
$image .=  $class;
}
$image .= "' alt='".$img->relativename."' loading='lazy'>";

}
$image .= "</div>";
}
if (empty($options['limit'])) $image .= "</div>";
} elseif ($module == 'product' || $module == 'category') {
$image .= "<div class='col'><i class='fa fa-cube fa-fw fa-2x'></i></div>";
}
$image .= "</div>";
} else {
$up_dir = wp_upload_dir();
$file=$up_dir['basedir'].'/doliconnect/'.$module.'/'.$id;
if (!is_file($file)) {
$imgj =  callDoliApi("GET", "/documents/download?modulepart=".$module."&original_file=".$id, null, dolidelay('document', $refresh), $entity);
//$image .= var_dump($imgj);
$imgj = (array) $imgj; 
if (is_array($imgj) && isset($imgj['content-type']) && preg_match('/^image/', $imgj['content-type'])) {
//$data = "data:".$imgj['content-type'].";".$imgj['encoding'].",".$imgj['content'];

if (!is_dir($up_dir['basedir'].'/doliconnect/'.$module.'/'.$id)) {
mkdir($up_dir['basedir'].'/doliconnect/'.$module.'/'.explode('/'.$imgj['filename'], $id, 2)[0], 0755, true);
}
//$files = glob($up_dir['basedir'].'/doliconnect/'.$module.'/'.$id."/*");
//foreach($files as $file){
//if(is_file($file))
//unlink($file); 
//}
$file=$up_dir['basedir'].'/doliconnect/'.$module.'/'.$id;
file_put_contents($file, base64_decode($imgj['content']));
$image = "<img src='".$up_dir['baseurl'].'/doliconnect/'.$module.'/'.$id."' class='".$class."' alt='".$imgj['filename']."' loading='lazy'>"; 
} else {
$image = "<i class='fa fa-cube fa-fw fa-2x'></i>";
}
} else {
$image = "<img src='".$up_dir['baseurl'].'/doliconnect/'.$module.'/'.$id."' class='".$class."' alt='".$up_dir['baseurl'].'/doliconnect/'.$module.'/'.$id."' loading='lazy'>";
}
}
return $image;
}

function doliconnect_categories($type, $object, $url = null){
$cats = "";

if ( !empty(doliconst('MAIN_MODULE_CATEGORIE')) ) {
$categories =  callDoliApi("GET", "/categories/object/".$type."/".$object->id."?sortfield=s.rowid&sortorder=ASC", null, dolidelay($type));

if ( !isset($categories->error) && $categories != null ) {
$cats .= "<small><i class='fas fa-tags fa-fw'></i> ";
$cats .= _n( 'Category:', 'Categories:', count($categories), 'doliconnect' );
foreach ($categories as $category) {
if ($category->id != doliconst("DOLICONNECT_CATSHOP")) {
if (!empty($url)) {
$cats .= " <a href='".esc_url( add_query_arg( 'category', $category->id, $url) )."'";
} else { 
$cats .= " <span ";
}
$cats .= "class='badge rounded-pill bg-secondary text-white text-decoration-none''>";

$cats .= doliproduct($category, 'label');
if (!empty($url)) {
$cats .= "</a>";
} else {
$cats .= "</span>";
}
}
$cats .= "</small>";
}
}
}
return $cats;
}

function socialconnect( $url ) {
$connect = null;

include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/config.php');

$hybridauth = new Hybridauth\Hybridauth($config);
$adapters = $hybridauth->getConnectedAdapters();

foreach ($hybridauth->getProviders() as $name) {

if (!isset($adapters[$name])) {
$connect .= "<div class='d-grid gap-2'><a href='".doliconnecturl('doliaccount')."?provider=".$name."' onclick='loadingLoginModal()' role='button' class='btn btn-outline-dark' title='".__( 'Sign in with', 'doliconnect')." ".$name."'><b><i class='fab fa-".strtolower($name)." fa-lg float-start'></i> ".__( 'Sign in with', 'doliconnect')." ".$name."</b></a></div>";
}
}
if (!empty($hybridauth->getProviders())) {
$connect .= '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect').'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
}

return $connect;
}

function doliopeninghours($constante){
if (!empty(doliconst($constante))) { 
  return doliconst($constante);
} else {
  return __( 'closed', 'doliconnect');
}
}

function doliajax($id, $url = null, $case = null){
  $ajax = "<input type='hidden' name='action' value='".$id."_request'>";
  $ajax.= "<input type='hidden' name='case' value='".$case."'>";
  $ajax.= "<input type='hidden' name='".$id."-nonce' value='".wp_create_nonce( $id.'-nonce')."'>";
  $ajax.= "<script>";
  $ajax.= 'jQuery(document).ready(function($) {
    jQuery("#'.$id.'-form").on("submit", function(e) {
    jQuery("#DoliconnectLoadingModal").modal("show");
    e.preventDefault(); 
    var $form = $(this);
    var url = "'.$url.'";
    jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) { 
      $.post($form.attr("action"), $form.serialize(), function(response) {
        $(window).scrollTop(0); 
        if (response.success) {
          if (document.getElementById("'.$id.'-alert")) {
          document.getElementById("'.$id.'-alert").innerHTML = response.data.message;      
          }
          if (!!url) document.location = url;
        } else {
          if (document.getElementById("'.$id.'-alert")) {
          document.getElementById("'.$id.'-alert").innerHTML = response.data.message;      
          }
        }
        if (document.getElementById("'.$id.'-captcha") && response.data.captcha) {
          document.getElementById("'.$id.'-captcha").innerHTML = response.data.captcha;      
        }
      jQuery("#DoliconnectLoadingModal").modal("hide");
      }, "json");  
    });
  });
  });';
  $ajax.= "</script>";
return $ajax;
}

function doliSelectForm($name, $request, $selectlang = '- Select -', $valuelang = 'Value', $value = null, $idobject = 0, $rights = 1, $delay = null, $id = 'id') {
  
  $object = callDoliApi("GET", $request, null, $delay);
  if ( isset($object) ) {
    $doliSelect = '<select class="form-select" id="'.$name.'" name="'.$idobject.'['.$name.']" aria-label="'.$valuelang.'" ';
  if ($rights) {
    $doliSelect .= 'required';
  } else {
    $doliSelect .= 'disabled';
  }
    $doliSelect .= '>';
    $doliSelect .= "<option value='' disabled ";
  if ( !isset($value) && empty($value) || empty(array_search($value, array_column($object, 'id')))) {
    $doliSelect .= "selected ";}
    $doliSelect .= ">".$selectlang."</option>";
  foreach ( $object as $postv ) { 
    if (isset($postv->rowid) && $id == 'id') $postv->$id = $postv->rowid;
    if (isset($postv->zip)&&isset($postv->town)) $postv->$id = $postv->zip.','.$postv->town;
    $doliSelect .= "<option value='".$postv->$id."' ";
  if ( isset($value) && !empty($value) && $value == $postv->$id && $postv->$id != '0' ) {
    $doliSelect .= "selected ";
  } elseif ( $postv->$id == '0' ) { $doliSelect .= "disabled "; }
   if (isset($postv->libelle)) $postv->label = $postv->libelle;
   if (isset($postv->zip)&&isset($postv->town)) $postv->label = $postv->zip.' - '.$postv->town;  
    $doliSelect .= ">".(isset($postv->label)?$postv->label:$postv->name)."</option>";
  }
    $doliSelect .= '</select><label for="'.$name.'"><i class="fas fa-map-marked fa-fw"></i> '.$valuelang.'</label>';
  } else {
    $doliSelect = '<input type="text" class="form-control" id="'.$name.'" placeholder="'.$valuelang.'" name="'.$idobject.'['.$name.']" value="'.$object->$name.'" autocomplete="off" ';
  if ($rights) {
    $doliSelect .= 'required';
  } else {
    $doliSelect .= 'readonly';
  }
  $doliSelect .= '>';
  }
return $doliSelect;
}

function dolipasswordform($user, $url, $return = null){
if (doliconnector($user, 'fk_user') > 0){  
$request = "/users/".doliconnector($user, 'fk_user');
$doliuser = callDoliApi("GET", $request, null, dolidelay('thirdparty'));
}

$password = "<div id='dolirpw-alert'></div><form id='dolirpw-form' method='post' class='was-validated' action='".admin_url('admin-ajax.php')."'>";
if (isset($_GET["key"]) && isset($_GET["login"])) {
$password .= "<input type='hidden' name='key' value='".esc_attr($_GET["key"])."'><input type='hidden' name='login' value='".esc_attr($_GET["login"])."'>";
}
$password .= doliajax('dolirpw', $return);

$password .= '<div class="card shadow-sm"><div class="card-header">'.__( 'Edit my password', 'doliconnect').'</div><ul class="list-group list-group-flush">';
if ( doliconnector($user, 'fk_user') > '0' ) {
$password .= "<li class='list-group-item list-group-item-info list-group-item-action'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect')."</b></li>";
} elseif  ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= "<li class='list-group-item list-group-item-info list-group-item-action'><i class='fas fa-info-circle'></i> <b>".__( 'Password cannot be modified in demo mode', 'doliconnect')."</b></li>";
} 
if (is_user_logged_in() && $user) {
$password .= '<li class="list-group-item list-group-item-light list-group-item-action">';
$password .= '<div class="form-floating';
if ( !empty(get_option('doliconnectbeta')) ) {
$password .= ' input-group';
}
$password .= '"><input type="password" class="form-control" id="pwd0" name="pwd0" placeholder="Password" ';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' readonly';
} else {
$password .= ' required';
}
$password .= '><label for="pwd0">'.__( 'Confirm your password', 'doliconnect').'</label>';
if ( !empty(get_option('doliconnectbeta')) ) {
$password .= '<button id="toggle-password" type="button" onclick="revealpwd0()" class="btn btn-outline-secondary" type="button" aria-label="Show password as plain text. Warning: this will display your password on the screen."><i id="toggle-password-fa" class="far fa-fw fa-eye-slash"></i></button>';
}
$password .= '</div></li>';
}

if ( !empty(get_option('doliconnectbeta')) ) {
$password .= "<script>";
$password .= 'function revealpwd0() {
  var x = document.getElementById("pwd0");
  if (x.type === "password") {
    x.type = "text";
    document.getElementById("toggle-password-fa").classList.toggle("fa-eye");
  } else {
    x.type = "password";
    document.getElementById("toggle-password-fa").classList.toggle("fa-eye-slash");
  }

}';
$password .= "</script>";
}

$password .= '<li class="list-group-item list-group-item-light list-group-item-action"><p class="text-justify">'.__( 'Your new password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect').'</p>';
$password .= '<div class="row g-2"><div class="col-md">';
$password .= '<div class="form-floating"><input type="password" class="form-control" id="pwd1" name="pwd1" placeholder="Password" ';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' readonly';
} else {
$password .= ' required';
}
$password .= '><label for="pwd1">'.__( 'New password', 'doliconnect').'</label></div>';
$password .= '</div><div class="col-md">';
$password .= '<div class="form-floating"><input type="password" class="form-control" id="pwd2" name="pwd2" placeholder="Password" ';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' readonly';
} else {
$password .= ' required';
}
$password .= '><label for="pwd2">'.__( 'Confirm the password', 'doliconnect').'</label></div>';
$password .= '</div></div>';
$password .= '</li>';
$password .= "</ul><div class='card-body'>";
$password .= '<div class="d-grid gap-2"><button class="btn btn-secondary" id="pwd-submit-button" type="submit"';
if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $user->ID ) {
$password .= ' disabled';
}
$password .= '>'.__( 'Update', 'doliconnect').'</button></div></form>';
$password .= "</div><div class='card-footer text-muted'>";
$password .= "<small><div class='float-start'>";
if ( isset($request) ) $password .= dolirefresh($request, $url, dolidelay('thirdparty'));
$password .= "</div><div class='float-end'>";
$password .= dolihelp('ISSUE');
$password .= "</div></small>";
$password .= '</div></div>';

return $password;
}

function doliProfId($idprof1 = null, $idprof2 = null, $idprof3 = null, $idprof4 = null, $country_code = 0, $idobject = 0, $rights = 1) {
  $ifprod = '';
if (is_numeric($country_code)) { 
$country_code = callDoliApi("GET", "/setup/dictionary/countries/".$country_code, null, dolidelay('constante',))->code;
}
$ProfId1 = callDoliApi("GET", "/doliconnector/translation/ProfId1".$country_code."?filename=companies&langcode=".str_replace("-","_",get_bloginfo("language")), null, dolidelay('constante',));
if ($ProfId1 != '-') {
  $ifprod .= '<div class="col-md-6 col-lg"><div class="form-floating"><input type="text" class="form-control" id="idprof1" name="'.$idobject.'[idprof1]" placeholder="'.$ProfId1.'" value="'.(isset($idprof1) ? $idprof1 : null).'" required autocomplete="off">
  <label for="idprof1"><i class="fas fa-building fa-fw"></i> '.$ProfId1.'</label></div></div>'; 
}
$ProfId2 = callDoliApi("GET", "/doliconnector/translation/ProfId2".$country_code."?filename=companies&langcode=".str_replace("-","_",get_bloginfo("language")), null, dolidelay('constante',));
if ($ProfId2 != '-') {
  $ifprod .= '<div class="col-md-6 col-lg"><div class="form-floating"><input type="text" class="form-control" id="idprof2" name="'.$idobject.'[idprof2]" placeholder="'.$ProfId1.'" value="'.(isset($idprof2) ? $idprof2 : null).'" required autocomplete="off">
  <label for="idprof2"><i class="fas fa-building fa-fw"></i> '.$ProfId2.'</label></div></div>';
} 
$ProfId3 = callDoliApi("GET", "/doliconnector/translation/ProfId2".$country_code."?filename=companies&langcode=".str_replace("-","_",get_bloginfo("language")), null, dolidelay('constante',));
if ($ProfId3 != '-') {
  $ifprod .= '<div class="col-md-6 col-lg"><div class="form-floating"><input type="text" class="form-control" id="idprof3" name="'.$idobject.'[idprof3]" placeholder="'.$ProfId3.'" value="'.(isset($idprof3) ? $idprof3 : null).'" required autocomplete="off">
  <label for="idprof3"><i class="fas fa-building fa-fw"></i> '.$ProfId3.'</label></div></div>';
} 
$ProfId4 = callDoliApi("GET", "/doliconnector/translation/ProfId4".$country_code."?filename=companies&langcode=".str_replace("-","_",get_bloginfo("language")), null, dolidelay('constante',));
if ($ProfId4 != '-') {
  $ifprod .= '<div class="col-md-6 col-lg"><div class="form-floating"><input type="text" class="form-control" id="idprof4" name="'.$idobject.'[idprof4]" placeholder="'.$ProfId4.'" value="'.(isset($idprof4) ? $idprof4 : null).'" required autocomplete="off">
  <label for="idprof4"><i class="fas fa-building fa-fw"></i> '.$ProfId4.'</label></div></div>';      
} 
return $ifprod;
}

function doliuserform($object, $delay, $mode, $rights) {
global $current_user;
//$rights = 0;
if ( is_object($object) && $object->id > 0 ) {
$idobject=$mode."[".$object->id."]";
} else { $idobject=$mode; }

$company = callDoliApi("GET", "/setup/company", null, dolidelay('constante'));

print "<ul class='list-group list-group-flush'>";

if ( ! isset($object) && in_array($mode, array('thirdparty')) && empty(get_option('doliconnect_disablepro')) ) {
if ( isset($_GET["morphy"]) && $_GET["morphy"] == 'mor' && get_option('doliconnect_disablepro') != 'mor' ) {                                                                                                                                                                                                                                                                                                                                   
print "<input type='hidden' id='morphy' name='".$idobject."[morphy]' value='mor'>";
}
elseif (get_option('doliconnect_disablepro') != 'phy') {
print "<input type='hidden' id='morphy' name='".$idobject."[morphy]' value='phy'>";
}
print "<li class='list-group-item list-group-item-light list-group-item-action'>";
} elseif ( isset($object) && in_array($mode, array('thirdparty')) && empty(get_option('doliconnect_disablepro')) ) { //|| $mode == 'member'
print "<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-row'><div class='col-12'><label for='inputMorphy'><small><i class='fas fa-user-tag fa-fw'></i> ".__( 'Type of account', 'doliconnect')."</small></label><br>";
print "<div class='form-check form-check-inline'><input type='radio' id='morphy1' name='".$idobject."[morphy]' value='phy' class='form-check-input'";
if ( $current_user->billing_type != 'mor' || empty($current_user->billing_type) ) { print " checked"; }
if (!$rights) {
print ' disabled';
}
print " required><label class='form-check-label' for='morphy1'>".__( 'Personnal account', 'doliconnect')."</label>
</div>
<div class='form-check form-check-inline'><input type='radio' id='morphy2' name='".$idobject."[morphy]' value='mor' class='form-check-input'";
if ( $current_user->billing_type == 'mor' ) { print " checked"; }
if (!$rights) {
print ' disabled';
}
print " required><label class='form-check-label' for='morphy2'>".__( 'Entreprise account', 'doliconnect')."</label>
</div>";
print "</div></div></li><li class='list-group-item list-group-item-light list-group-item-action'>";
} elseif ( in_array($mode, array('thirdparty')) ) { //|| $mode == 'member'
print "<li class='list-group-item list-group-item-light list-group-item-action'><input type='hidden' id='morphy' name='".$idobject."[morphy]' value='phy'>";
} elseif ( !is_user_logged_in() && in_array($mode, array('linkthirdparty')) ) {

print '<li class="list-group-item list-group-item-light list-group-item-action"><div class="form-group">
  <label for="FormCustomer"><small><i class="fas fa-user-tie"></i> '.__( 'Customer', 'doliconnect').'</small></label><div class="input-group" id="FormCustomer">
  <input type="text" aria-label="Last name" name="code_client" placeholder="'.__( 'Customer code', 'doliconnect').'" class="form-control" required>
</div><div>';
print '<div class="form-group">
  <label for="FormObject"><small><i class="fas fa-file-invoice"></i> '.__( 'Order or Invoice', 'doliconnect').'</small></label><div class="input-group" id="FormObject">
  <input type="text" aria-label="Reference" name="reference" placeholder="'.__( 'Reference', 'doliconnect').'" class="form-control" required>
  <input type="number" aria-label="Amount" name="amount" placeholder="'.__( 'Total incl. tax', 'doliconnect').'" class="form-control" required>
</div><div><li class="list-group-item list-group-item-light list-group-item-action">';

} else {
print "<li class='list-group-item list-group-item-light list-group-item-action'>";
}

if ( in_array($mode, array('member')) ) {
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-user-tag fa-fw'></i> ".__( 'Type', 'doliconnect')."</small></label><select class='custom-select' id='typeid'  name='".$idobject."[typeid]' required>";
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$object->morphy."')", null, $delay);
//print $typeadhesion;
print "<option value='' disabled ";
if ( empty($object->typeid) ) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect')."</option>";
if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
print "<option value ='".$postadh->id."' ";
if ( isset($object->typeid) && $object->typeid == $postadh->id && $object->typeid != null ) {
print "selected ";
} elseif ( $postadh->family == '1' || $postadh->automatic_renew != '1' || $postadh->automatic != '1' ) { print "disabled "; }
print ">".$postadh->label;
if (! empty ($postadh->duration_value)) print " - ".doliduration($postadh);
print " ";
//if ( ! empty($postadh->note) ) { print ", ".$postadh->note; }
$tx=1;
if ( ( ($postadh->welcome > '0') && ($object->datefin == null )) || (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $object->next_subscription_valid) && (current_time( 'timestamp',1) > $object->datefin) && $object->next_subscription_valid != $object->datefin ) ) { 
print " (";
print doliprice(($tx*$postadh->price)+$postadh->welcome)." ";
print __( 'then', 'doliconnect' )." ".doliprice($postadh->price)." ".__( 'yearly', 'doliconnect' ).")"; 
} else {
print " (".doliprice($postadh->price);
print " ".__( 'yearly', 'doliconnect' ).")";
} 

print "</option>";
}}
print "</select></div></div></li><li class='list-group-item list-group-item-light list-group-item-action'>";
}

if ( in_array($mode, array('thirdparty', 'donation')) && ($current_user->billing_type == 'mor' || ( isset($_GET["morphy"]) && $_GET["morphy"] == 'mor') || get_option('doliconnect_disablepro') == 'mor' ) ) {

print '<div class="row g-2 mb-2"><div class="col-lg">';    
print '<div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[name]" name="'.$idobject.'[name]" placeholder="'.__( 'Name of company', 'doliconnect').'" value="'.(isset($object->name) ? stripslashes(htmlspecialchars($object->name, ENT_QUOTES)) : null).'" ';
if ($rights) {
print 'required';
} else {
print 'readonly';
}
print '><label for="'.$idobject.'[name]"><i class="fas fa-building fa-fw"></i> '.__( 'Name of company', 'doliconnect').'</label></div>';   
print '</div><div class="col-md">';
print '<div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[name_alias]" name="'.$idobject.'[name_alias]" placeholder="'.__( 'Commercial name / Brand', 'doliconnect').'" value="'.(isset($object->name_alias) ? stripslashes(htmlspecialchars($object->name_alias, ENT_QUOTES)) : null).'" ';
if (!$rights) {
print 'readonly';
}
print '><label for="'.$idobject.'[name_alias]"><i class="fas fa-building fa-fw"></i> '.__( 'Commercial name / Brand', 'doliconnect').'</label></div>';
print '</div></div>';

print '<div id="profids" class="row mb-2 g-2">';
    
if ( doliversion('15.0.0') ) {
print doliProfId((isset($object->idprof1)?$object->idprof1:''), (isset($object->idprof2)?$object->idprof2:''), (isset($object->idprof3)?$object->idprof3:''), (isset($object->idprof4)?$object->idprof4:''), (isset($object->country_code)?$object->country_code:$company->country_code), $idobject, $rights);
}

print '</div><div class="row g-2"><div class="col-md-6 col-lg-4"><div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[tva_intra]" name="'.$idobject.'[tva_intra]" placeholder="tva" value="'.(isset($object->tva_intra) ? $object->tva_intra : null).'"';
if ((isset($object->tva_intra) && !empty($object->tva_intra)) || !$rights) { print ' readonly'; } else { print ''; }
print ' autocomplete="off">
<label for="'.$idobject.'[tva_intra]"><i class="fas fa-building fa-fw"></i> '.__( 'VAT number', 'doliconnect').'</label></div></div>';

if ( doliversion('15.0.0') ) {
print '<div class="col-md-6 col-lg-4"><div class="form-floating" id="forme_juridique_code_form">';
print doliSelectForm("forme_juridique_code", "/setup/dictionary/legal_form?sortfield=libelle&sortorder=ASC&active=1&limit=500&country=".(isset($object->country_id) ? $object->country_id : $company->country_id), __( '- Select your legal form -', 'doliconnect'), __( 'Legal form', 'doliconnect'), (isset($object->forme_juridique_code) ? $object->forme_juridique_code : null), $idobject, $rights, $delay, 'code');
//print '<label for="yyyy"><i class="fas fa-building fa-fw"></i> '.__( 'Legal form', 'doliconnect').'</label>';
print '</div></div>';
}

if ( doliversion('15.0.0') ) {
$staff = callDoliApi("GET", "/setup/dictionary/staff?sortfield=id&sortorder=ASC&limit=100&active=1", null, $delay);
if ( isset($staff) ) { 
print '<div class="col-md-6 col-lg-4"><div class="form-floating"><select class="form-select" id="'.$idobject.'[effectif_id]" name="'.$idobject.'[effectif_id]" aria-label="'.__( 'Staff', 'doliconnect').'" ';
if ($rights) {
print 'required';
} else {
print 'disabled';
}
print '>';
print "<option value='' disabled ";
if ( (isset($object->effectif_id) && empty($object->effectif_id)) || $staff == 0) {
print "selected ";}
print ">".__( '- Select your staff -', 'doliconnect')."</option>";
foreach ( $staff as $postv ) { 
print "<option value='".$postv->id."' ";
if ( isset($object->effectif_id) && $object->effectif_id == $postv->id && $object->effectif_id != null && $postv->id != '0' ) {
print "selected ";
} elseif ( $postv->code == '0' ) { print "disabled "; }
print ">".$postv->libelle."</option>";
}
print '</select><label for="'.$idobject.'[effectif_id]"><i class="fas fa-building fa-fw"></i> '.__( 'Staff', 'doliconnect').'</label></div></div>';
}
}

print '</div>';
print "</li><li class='list-group-item list-group-item-light list-group-item-action'>";
}

print '<div class="row g-2 mb-2">';

if ( doliversion('10.0.0') ) {
$civility = callDoliApi("GET", "/setup/dictionary/civilities?sortfield=code&sortorder=ASC&limit=100", null, $delay);
} else {
$civility = callDoliApi("GET", "/setup/dictionary/civility?sortfield=code&sortorder=ASC&limit=100", null, $delay);
}
print '<div class="col-md-12 col-lg-3 col-xl-2"><div class="form-floating"><select class="form-select" id="'.$idobject.'[civility_code]"  name="'.$idobject.'[civility_code]" aria-label="'.__( 'Civility', 'doliconnect').'" ';
if ($rights) {
print 'required';
} else {
print 'disabled';
}
print '>';
print "<option value='' disabled ";
if ( empty($object->civility_code) ) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect')."</option>";
if ( !isset($civility->error ) && $civility != null ) { 
foreach ( $civility as $postv ) {

print "<option value='".$postv->code."' ";
if ( (isset($object->civility_code) ? $object->civility_code : $current_user->civility_code) == $postv->code && (isset($object->civility_code) ? $object->civility_code : $current_user->civility_code) != null) {
print "selected "; }
print ">".$postv->label."</option>";

}} else {
print "<option value='MME' ";
if ( $current_user->civility_code == 'MME' && $object->civility_code != null) {
print "selected ";}
print ">".__( 'Miss', 'doliconnect')."</option>";
print  "<option value='MR' ";
if ( $current_user->civility_code == 'MR' && $object->civility_code != null) {
print "selected ";}
print ">".__( 'Mister', 'doliconnect')."</option>";
}
print '</select><label for="'.$idobject.'[civility_code]"><i class="fas fa-user fa-fw"></i> '.__( 'Civility', 'doliconnect').'</label></div></div>';
                                                                                                                                                            
print '<div class="col-md-6 col-lg-4 col-xl-5"><div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[firstname]" name="'.$idobject.'[firstname]" placeholder="'.__( 'Firstname', 'doliconnect').'" value="'.(isset($object->firstname) ? $object->firstname : stripslashes(htmlspecialchars($current_user->user_firstname, ENT_QUOTES))).'" ';
if ($rights) {
print 'required';
} else {
print 'readonly';
}
print '><label for="'.$idobject.'[firstname]"><i class="fas fa-user fa-fw"></i> '.__( 'Firstname', 'doliconnect').'</label></div></div>';

print '<div class="col-md-6 col-lg-5 col-xl-5"><div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[lastname]" name="'.$idobject.'[lastname]" placeholder="'.__( 'Firstname', 'doliconnect').'" value="'.(isset($object->lastname) ? $object->lastname : stripslashes(htmlspecialchars($current_user->user_lastname, ENT_QUOTES))).'" ';
if ($rights) {
print 'required';
} else {
print 'readonly';
}
print '><label for="'.$idobject.'[lastname]"><i class="fas fa-user fa-fw"></i> '.__( 'Lastname', 'doliconnect').'</label></div></div>';   

print '</div>';

if ( !in_array($mode, array('donation')) ) {
print '<div class="row g-2 mb-2">';

if ( !empty($object->birth) ) { $birth = wp_date('Y-m-d', $object->birth); }
print '<div class="col-md-6"><div class="form-floating"><input type="date" class="form-control" id="'.$idobject.'[birth]" name="'.$idobject.'[birth]" placeholder="yyyy-mm-dd" value="'.(isset($birth) ? $birth : $current_user->billing_birth).'"';
if (($mode != 'contact' && $rights) || $rights) {
print 'required';
} else {
print 'readonly';
}
print '><label for="'.$idobject.'[birth]"><i class="fas fa-user fa-fw"></i> '.__( 'Birthday', 'doliconnect').'</label></div></div>';   

print '<div class="col-md-6">';
if ( $mode != 'contact' ) {
print '<div class="form-floating"><input type="text" class="form-control" id="user_nicename" name="user_nicename" placeholder="DirectExample" value="'.stripslashes(htmlspecialchars($current_user->nickname, ENT_QUOTES)).'" autocomplete="off" ';
if ($rights) {
print 'required';
} else {
print 'readonly';
}
print '>
<label for="user_nicename"><i class="fas fa-user-secret fa-fw"></i> '.__( 'Display name', 'doliconnect').'</label></div>';  
} else {
print '<div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[poste]" name="'.$idobject.'[poste]" placeholder="Director" value="'.stripslashes(htmlspecialchars(isset($object->poste) ? $object->poste : null, ENT_QUOTES)).'" autocomplete="off" ';
if ($rights) {
print 'required';
} else {
print 'readonly';
}
print '>
<label for="'.$idobject.'[poste]"><i class="fas fa-user-secret fa-fw"></i> '.__( 'Title / Job', 'doliconnect').'</label></div>';  
}
print '</div>';
print '</div>';
}

print '<div class="row g-2">';
print '<div class="col-md"><div class="form-floating"><input type="email" class="form-control" id="'.$idobject.'[email]" placeholder="name@example.com" name="'.$idobject.'[email]" value="'.(isset($object->email) ? $object->email : $current_user->user_email).'" autocomplete="off" ';
if ( !$rights || (defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $current_user->ID && is_user_logged_in() && in_array($mode, array('thirdparty'))) || (defined("DOLICONNECT_SELECTEDEMAIL") && is_array(constant("DOLICONNECT_SELECTEDEMAIL")) && is_user_logged_in())) {
print 'readonly';
} else {
print 'required';
}
print '><label for="'.$idobject.'[email]"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label>';
if (defined("DOLICONNECT_SELECTEDEMAIL") && is_array(constant("DOLICONNECT_SELECTEDEMAIL")) && !is_user_logged_in()) {
print '<small><i class="fas fa-info-circle"></i> Only emails from these domains are allowed:';
$array = constant("DOLICONNECT_SELECTEDEMAIL");
$i = 0;
foreach($array as $val) { 
if (!empty($i)) print ',';
print ' @'.$val; 
$i++; }
print '</small>';
}
print '</div></div>';

if ( ( !is_user_logged_in() && ((isset($_GET["morphy"])&& $_GET["morphy"] == "mor" && get_option('doliconnect_disablepro') != 'phy') || get_option('doliconnect_disablepro') == 'mor' || (function_exists('dolikiosk') && ! empty(dolikiosk())) ) && in_array($mode, array('thirdparty'))) || (is_user_logged_in() && in_array($mode, array('thirdparty','contact','member','donation'))) ) {
print '<div class="col-md"><div class="form-floating"><input type="tel" class="form-control" id="'.$idobject.'[phone]" placeholder="0012345678" name="'.$idobject.'[phone]" value="'.(isset($object->phone) ? $object->phone : (isset($object->phone_pro) ? $object->phone_pro : null)).'" autocomplete="off" ';
if (!$rights) {
print 'readonly';
}
print '><label for="'.$idobject.'[phone]"><i class="fas fa-phone fa-fw"></i> '.__( 'Phone', 'doliconnect').'</label></div></div>';
}

print "</div></li>";

if ( ( !is_user_logged_in() && ((isset($_GET["morphy"])&& $_GET["morphy"] == "mor" && get_option('doliconnect_disablepro') != 'phy') || get_option('doliconnect_disablepro') == 'mor' || (function_exists('dolikiosk') && ! empty(dolikiosk())) ) && in_array($mode, array('thirdparty'))) || (is_user_logged_in() && in_array($mode, array('thirdparty','contact','member','donation'))) ) {       
print "<li class='list-group-item list-group-item-light list-group-item-action'>";

print '<div class="form-floating mb-2"><textarea class="form-control" placeholder="'.__( 'Address', 'doliconnect').'"  name="'.$idobject.'[address]" id="'.$idobject.'[address]" style="height: 100px" ';
if ($rights) {
print 'required';
} else {
print 'readonly';
}
print '>'.(isset($object->address) ? stripslashes(htmlspecialchars($object->address, ENT_QUOTES)) : null).'</textarea>
<label for="'.$idobject.'[address]"><i class="fas fa-map-marked fa-fw"></i> '.__( 'Address', 'doliconnect').'</label></div>';

if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}

print '<div class="row mb-2 g-2"><div class="col"><div class="form-floating">';
print doliSelectForm("country_id", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=500&lang=".$lang, __( '- Select your country -', 'doliconnect'), __( 'Country', 'doliconnect'), (isset($object->country_id) ? $object->country_id : $company->country_id), $idobject, $rights);
print '</div></div>';

if ( doliversion('16.0.0') ) { 
  print '<div class="col-12 col-md"><div class="form-floating" id="state_form">';
  print doliSelectForm("state_id", "/setup/dictionary/states?sortfield=code_departement&sortorder=ASC&limit=500&country=".(isset($object->country_id) ? $object->country_id : $company->country_id), __( '- Select your state -', 'doliconnect'), __( 'State', 'doliconnect'), (isset($object->state_id) ? $object->state_id : $company->state_id), $idobject, $rights);
  print '</div></div>';
}

print '<script type="text/javascript">';
print 'jQuery(document).ready(function() {
  // Country dependent ajax
  $("#country_id").on("change",function(){
    //jQuery("#DoliconnectLoadingModal").modal("show");
    var countryId = $(this).val();
    if ( typeof(document.getElementById("idprof1")) != "undefined" && document.getElementById("idprof1") != null ) { 
      var idprof1 = $("#idprof1").val();
    } else {
      var idprof1 = "'.(isset($object->idprof1) ? $object->idprof1 : '').'";
    } 
    if ( typeof(document.getElementById("idprof2")) != "undefined" && document.getElementById("idprof2") != null ) { 
      var idprof2 = $("#idprof2").val();
    } else {
      var idprof2 = "'.(isset($object->idprof2) ? $object->idprof2 : '').'";
    } 
    if ( typeof(document.getElementById("idprof3")) != "undefined" && document.getElementById("idprof3") != null ) { 
      var idprof3 = $("#idprof3").val();
    } else {
      var idprof3 = "'.(isset($object->idprof3) ? $object->idprof3 : '').'";
    } 
    if ( typeof(document.getElementById("idprof4")) != "undefined" && document.getElementById("idprof4") != null ) { 
      var idprof4 = $("#idprof4").val();
    } else {
      var idprof4 = "'.(isset($object->idprof4) ? $object->idprof4 : '').'";
    } 
    //console.log("country is changed to " + countryId );
    $.ajax({
      url :"'.admin_url('admin-ajax.php').'",
      type:"POST",
      cache:false,
      data: {
        "action": "doliselectform_request",
        "case": "update",
        "countryId": countryId,
        "country_code": countryId,
        "idprof1": idprof1,
        "idprof2": idprof2,
        "idprof3": idprof3,
        "idprof4": idprof4, 
        "objectId": "'.$idobject.'",
        "stateId": '.(isset($object->state_id) ? $object->state_id : 0).',
        "ziptownId": "'.(isset($object->zip) ? $object->zip : null).','.(isset($object->town) ? $object->town : null).'", 
        "legalformId": '.(isset($object->forme_juridique_code) ? $object->forme_juridique_code : 0).',
        "rights": '.$rights.',
        "delay": '.$delay.'
      },
    }).done(function(response) {
      if ( document.getElementById("state_id") ) { 
        document.getElementById("state_id").innerHTML = response.data.state_id;
      }
      if ( document.getElementById("forme_juridique_code") ) { 
        document.getElementById("forme_juridique_code").innerHTML = response.data.forme_juridique_code;
      }
      if ( document.getElementById("ziptown") ) { 
        document.getElementById("ziptown").innerHTML = response.data.ziptown;
      }
      if ( document.getElementById("profids") ) { 
        document.getElementById("profids").innerHTML = response.data.profids;
      }
      //jQuery("#DoliconnectLoadingModal").modal("hide");
    });
  });

  // State dependent ajax
  $("#state_id").on("change",function(){
    //jQuery("#DoliconnectLoadingModal").modal("show");
    var stateId = $(this).val();
    //console.log("state is changed to " +  stateId );
    $.ajax({
      url :"'.admin_url('admin-ajax.php').'",
      type:"POST",
      cache:false,
      data: {
        "action": "doliselectform_request",
        "case": "update",
        "countryId": $("#country_id").val(),
        "objectId": "'.$idobject.'",
        "stateId": stateId,
        "ziptownId": "'.(isset($object->zip) ? $object->zip : null).','.(isset($object->town) ? $object->town : null).'",
        "rights": '.$rights.',
        "delay": '.$delay.'
      },
    }).done(function(response) {
      if ( document.getElementById("ziptown") ) { 
        document.getElementById("ziptown").innerHTML = response.data.ziptown;
      }
      //jQuery("#DoliconnectLoadingModal").modal("hide");
    });
  });
});';
print '</script>';

print '</div><div class="row g-2">';
  
if (!empty(get_option('doliconnectbeta')) && doliconst("MAIN_USE_ZIPTOWN_DICTIONNARY")) {
  print '<div class="col-12 col-md"><div class="form-floating" id="ziptown_form">';
  print doliSelectForm("ziptown", "/setup/dictionary/towns?sortfield=town&sortorder=ASC&active=1&limit=1000&sqlfilters=(t.fk_pays%3A%3D%3A'".(isset($object->country_id) ? $object->country_id : $company->country_id)."')%20AND%20(t.fk_county%3A%3D%3A'".(isset($object->state_id) ? $object->state_id : null)."')", __( '- Select your town -', 'doliconnect'), __( 'Town', 'doliconnect'), (isset($object->zip) ? $object->zip : null).','.(isset($object->town) ? $object->town : null), $idobject, $rights);
  print '</div></div>';
} else {
  print '<div class="col-lg-8"><div class="form-floating" id="town">';
  print '<input type="text" class="form-control" id="'.$idobject.'[town]" name="'.$idobject.'[town]" placeholder="'.__( 'Town', 'doliconnect').'" value="'.(isset($object->town) ? $object->town : null).'" ';
  if ($rights) {
  print 'required';
  } else {
  print 'readonly';
  }
  print '><label for="'.$idobject.'[town]"><i class="fas fa-map-marked fa-fw"></i> '.__( 'Town', 'doliconnect').'</label></div>';  
  print '</div><div class="col-lg-4">';   
  print '<div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[zip]" name="'.$idobject.'[zip]" placeholder="'.__( 'Zipcode', 'doliconnect').'" value="'.(isset($object->zip) ? $object->zip : null).'" ';
  if ($rights) {
  print 'required';
  } else {
  print 'readonly';
  }
  print '><label for="'.$idobject.'[zip]"><i class="fas fa-map-marked fa-fw"></i> '.__( 'Zipcode', 'doliconnect').'</label></div>';  
  print '</div>';
}

print "</div></li>";

if( has_filter('mydoliconnectuserform') && !in_array($mode, array('donation')) ) {
print "<li class='list-group-item list-group-item-light list-group-item-action'>";
print apply_filters('mydoliconnectuserform', $object, $idobject);
print "</li>";
}

if ( in_array($mode, array('contact')) && doliversion('12.0.0') ) {
$contact_types = callDoliApi("GET", "/setup/dictionary/contact_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.source%3A%3D%3A'external')%20AND%20(t.element%3A%3D%3A'commande')", null, $delay);//%20OR%20(t.element%3A%3D%3A'propal')
print "<li class='list-group-item list-group-item-light list-group-item-action'>";
if ( !isset($contact_types->error ) && $contact_types != null ) {
$typecontact = array();
if ( isset($object->roles) && $object->roles != null ) {
foreach ( $object->roles as $role ) {
$typecontact[] .= $role->id; 
}}
foreach ( $contact_types as $contacttype ) {                                                          
print "<div class='form-check'><input type='checkbox' class='form-check-input' id='".$idobject."[roles][".$contacttype->rowid."]' name='".$idobject."[roles][]' value='".$contacttype->rowid."' ";
if ( isset($object->roles) && $object->roles != null && in_array($contacttype->rowid, $typecontact)) { print ' checked'; }
if (!$rights) {
print ' disabled';
}
print "><label class='form-check-label' for='".$idobject."[roles][".$contacttype->rowid."]'>".$contacttype->label.'</label></div>';
}}
}

if ( !in_array($mode, array('donation', 'linkthirdparty')) ) {
print "<li class='list-group-item list-group-item-light list-group-item-action'>";

if ( !in_array($mode, array('member', 'contact', 'linkthirdparty')) ) {
print '<div class="form-floating mb-2"><input type="url" class="form-control" id="'.$idobject.'[url]" name="'.$idobject.'[url]" placeholder="www.example.com" value="'.stripslashes(htmlspecialchars((isset($object->url) ? $object->url : null), ENT_QUOTES)).'" ';
if (!$rights) {
print 'readonly';
}
print '><label for="'.$idobject.'[url]">'.__( 'Website', 'doliconnect').'</label></div>';
}

if ( !in_array($mode, array('member', 'linkthirdparty')) ) {
print '<div class="form-floating"><textarea class="form-control" placeholder="Leave a comment here"  name="'.$idobject.'[note_public]" id="note_public" style="height: 100px" ';
if (!$rights) {
print 'readonly';
}
print '>'.stripslashes(htmlspecialchars(isset($object->note_public)?$object->note_public:$current_user->description, ENT_QUOTES)).'</textarea>
<label for="note_public">'.__( 'About Yourself', 'doliconnect').'</label></div>';
}

print "</li>";
}


if ( doliversion('11.0.0') ) { 
$socialnetworks = callDoliApi("GET", "/setup/dictionary/socialnetworks?sortfield=rowid&sortorder=ASC&limit=100&active=1", null, $delay);
if ( !isset($socialnetworks->error) && $socialnetworks != null ) { 
print "<li class='list-group-item list-group-item-light list-group-item-action'><div class='row g-2'>";
foreach ( $socialnetworks as $social ) { 
$code = $social->code;
print '<div class="col-12 col-sm-6 col-lg-4"><div class="form-floating"><input type="text" class="form-control" id="'.$idobject.'[socialnetworks]['.$social->code.']" name="'.$idobject.'[socialnetworks]['.$social->code.']" placeholder="'.$social->label.'" value="'.stripslashes(htmlspecialchars((isset($object->socialnetworks->$code) ? $object->socialnetworks->$code : null), ENT_QUOTES)).'" ';
if (!$rights) {
print 'readonly';
}
print '><label for="'.$idobject.'[socialnetworks]['.$social->code.']"><i class="fab fa-'.$social->code.' fa-fw"></i> '.$social->label.'</label></div></div>';
}
print "</div></li>";
}

} else { 
print "<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-row'>";
if ( !empty(doliconst("SOCIALNETWORKS_FACEBOOK", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-facebook fa-fw'></i> Facebook</small></label>
<input type='text' name='".$idobject."[facebook]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->facebook) ? $object->facebook : null), ENT_QUOTES))."'></div>";
}
if ( !empty(doliconst("SOCIALNETWORKS_TWITTER", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-twitter fa-fw'></i> Twitter</small></label>
<input type='text' name='".$idobject."[twitter]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->twitter) ? $object->twitter : null), ENT_QUOTES))."'></div>";
}
if ( !empty(doliconst("SOCIALNETWORKS_SKYPE", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-skype fa-fw'></i> Skype</small></label>
<input type='text' name='".$idobject."[skype]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->skype) ? $object->skype : null), ENT_QUOTES))."'></div>";
}
if ( !empty(doliconst("SOCIALNETWORKS_LINKEDIN", $delay)) ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-linkedin-in fa-fw'></i> Linkedin</small></label>
<input type='text' name='".$idobject."[linkedin]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect')."' value='".stripslashes(htmlspecialchars((isset($object->linkedin) ? $object->linkedin : null), ENT_QUOTES))."'></div>";
}
print "</div></li>";
}

}

if ( function_exists('dolikiosk') && ! isset($object) && (! empty(dolikiosk()) && $mode == 'thirdparty') ) {
print "<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-row'><div class='col'><label for='pwd1'><small><i class='fas fa-key fa-fw'></i> ".__( 'Password', 'doliconnect')."</small></label>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Choose your password', 'doliconnect')."' autocomplete='off' required>
<small id='pwd1' class='form-text text-justify text-muted'>".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect')."</small></div></div>
<div class='form-row'><div class='col'><label for='pwd2'><small><i class='fas fa-key fa-fw'></i> ".__( 'Confirm your password', 'doliconnect')."</small></label>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your password', 'doliconnect')."' autocomplete='off' required></div>";
print "</div></li>";
}

if ( !is_user_logged_in() ) {
  print "<li class='list-group-item list-group-item-light list-group-item-action'>";
  print dolicaptcha();
  print "</li>";
}

if ( !is_user_logged_in() && in_array($mode, array('thirdparty','linkthirdparty')) ) {

if( has_action('register_form') ) {
if (!empty(do_action( 'register_form'))){
print "<li class='list-group-item list-group-item-light list-group-item-action'>";
print do_action( 'register_form');
print "</li>";
}
}

//print "<li class='list-group-item list-group-item-light list-group-item-action'>";
//print "<div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
//<input type='checkbox' class='custom-control-input' value='1' id='optin1' name='optin1'>
//<label class='custom-control-label' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect')."</label></div></div>";
//print "<div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
//<input type='checkbox' class='custom-control-input' value='forever' id='validation' name='validation' required>
//<label class='custom-control-label' for='validation'>".__( 'I read and accept the <a href="#" data-bs-toggle="modal" data-target="#cgvumention">Terms & Conditions</a>.', 'doliconnect')."</label></div></div>";

//if ( get_option( 'wp_page_for_privacy_policy' ) ) {
//print "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='cgvumention' aria-hidden='true'><div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header'><h5 class='modal-title' id='cgvumentionLabel'>".__( 'Terms & Conditions', 'doliconnect')."</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
//<div class='modal-body'>";
//$post = get_post(get_option( 'wp_page_for_privacy_policy' ));
//print $post->post_content;
//print apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' )));
//print get_the_content( 'Read more', '', get_option( 'wp_page_for_privacy_policy' )); 
//print "</div></div></div>";}
//print "</li>";
}

print "</ul>";
}
//add_action( 'wp_loaded', 'doliconnectuserform', 10, 2);

function doliloading($id=loading) {
$loading = '<div id="doliloading-'.$id.'" style="display:none"><br><br><br><br><center><div class="align-middle">';
$loading .= '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>'; 
$loading .= '<h4>'.__( 'Loading', 'doliconnect').'</h4></div></center><br><br><br><br></div>';
return $loading;
}

function doliconnect_loading() {

doliconnect_enqueues();

print '<div id="DoliconnectLoadingModal" class="modal fade bd-example-modal" tabindex="-1" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-show="true" data-bs-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-fullscreen modal-dialog-centered">
<div class="text-center text-light w-100">
<div class="spinner-border" role="status"><span class="sr-only">loading...</span></div>
<h4>'.__( 'Processing', 'doliconnect').'</h4>
</div></div></div>';
}
add_action( 'wp_footer', 'doliconnect_loading');

function dolicart_modal() {

doliconnect_enqueues();

print '<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasDolicart" aria-labelledby="offcanvasDolicartLabel">
<div class="offcanvas-header">
  <h5 class="offcanvas-title" id="offcanvasDolicartLabel">'.__( 'Cart', 'doliconnect').'</h5>
  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
</div>
<div class="offcanvas-body"><div id="message-dolicart"></div>';
print '<div id="DoliCartItemsList">'.doliconnect_CartItemsList().'</div>';
print '</div></div>';
}
add_action( 'wp_footer', 'dolicart_modal');

function dolibug($msg = null, $request = null) {
//header('Refresh: 180; URL='.esc_url(get_permalink()).'');
$bug = '<div id="dolibug" ><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-7x fa-fw"></i><h4>';
if ( ! empty($msg) ) {
$bug .= var_dump($msg);
} else { $bug .= __( 'Oops, our servers are unreachable.<br>Thank you for coming back in a few minutes.', 'doliconnect'); }
$bug .= '</h4>';
if ( defined("DOLIBUG") && ! empty(constant("DOLIBUG")) ) {
$bug .= '<h6>'.__( 'Error code', 'doliconnect').' #'.constant("DOLIBUG").'</h6>';
}
if ($request) $bug .= '<h6>'.__( 'Request', 'doliconnect').' '.$request.'</h6>';
$bug .='</div></center><br><br><br><br></div>';
return $bug;
}

function Doliconnect_MailAlert( $user_login, $user) {

if ( $user->loginmailalert == 'on' ) { //&& $user->ID != ''.constant("DOLICONNECT_DEMO").''
$sitename = get_option('blogname');
$siteurl = get_option('siteurl');
$subject = "[$sitename] ".__( 'Connection notification', 'doliconnect');
$body = __( 'It appears that you have just logged on to our site the following IP address:', 'doliconnect')."<br /><br />".$_SERVER['REMOTE_ADDR']."<br /><br />".__( 'If you have not made this action, please change your password immediately.', 'doliconnect')."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($user->user_email, $subject, $body, $headers);
}

}
add_action('wp_login', 'Doliconnect_MailAlert', 10, 2);

function dolidocdownload($type, $ref=null, $fichier=null, $name=null, $refresh = false, $entity = null, $style = 'btn-outline-dark btn-sm btn-block') {
global $wpdb;
 
if ( $name == null ) { $name=$fichier; } 

if ( doliversion('11.0.0') ) {
$doc = callDoliApi("GET", "/documents/download?modulepart=".$type."&original_file=".$ref."/".$fichier, null, 0, $entity);
} else {
$doc = callDoliApi("GET", "/documents/download?module_part=".$type."&original_file=".$ref."/".$fichier, null, 0, $entity);
}
//print var_dump($doc);

if ( isset($ref) && isset($fichier) && isset($doc->content) ) { 

$data = "data:application/pdf;".$doc->encoding.",".$doc->content;
$filename = explode(".", $doc->filename)[0];

if (!empty(get_option('doliconnectbeta')) && 7==4) {
$document = '<button type="button" class="btn btn btn-outline-dark btn-sm btn-block" data-bs-toggle="modal" data-bs-target=".modal-'.$filename.'">'.$name.' <i class="fas fa-file-download"></i></button>';
$document .= '<div class="modal fade modal-'.$filename.'" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg" role="document"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="exampleModalCenterTitle"><a href="'.$data.'" download="'.$doc->filename.'">'.__( 'Download', 'doliconnect').' '.$doc->filename.'</a></h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">';
$document .= '<iframe class="pdfjs-viewer" style="width:100%;height:70vh" src="'.plugins_url("doliconnect/includes/pdfjs/web/viewer.html").'?file=" id="pdfjsframe-'.$filename.'"></iframe>
<script>
document.getElementById("pdfjsframe-'.$filename.'").onload = function() {
document.getElementById("pdfjsframe-'.$filename.'").contentWindow.PDFViewerApplication.open("'.$data.'");
};
</script>';
$document .= '</div></div></div></div>';
} else {
$document = '<a href="'.$data.'" role="button" class="btn '.$style.'" download="'.$doc->filename.'">'.$name.' <i class="fas fa-file-download"></i></a>';
}
} else {
$document = '<button class="btn '.$style.'" disabled>'.$name.' <i class="fas fa-file-download"></i></button>';
}

return $document;
}

function dolihelp($type) {

if ( is_user_logged_in() && !empty(doliconst('MAIN_MODULE_TICKET')) ) {
$arr_params = array( 'module' => 'tickets', 'type' => $type, 'action' => 'create'); 
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount'))); 
} elseif ( !empty(get_option('dolicontact')) ) {
$arr_params = array( 'action' => 'create'); //'type' => $postorder->id,  
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('dolicontact')));
} else {
$link='#';
}

$help = "<a href='".$link."' role='button' title='".__( 'Help?', 'doliconnect')."'><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-question-circle'></i></div><div class='d-none d-md-block'><i class='fas fa-question-circle'></i> ".__( 'Need help?', 'doliconnect')."</div></a>";

return $help;
}

function dolidelay($delay = null, $refresh = false, $protect = false) {

if (! is_numeric($delay)) {

if (false ===  get_site_option('doliconnect_delay_'.$delay) ) {

if ($delay == 'constante' || $delay == 'constantes') { $delay = MONTH_IN_SECONDS; }
elseif ($delay == 'dolibarr') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'doliconnector') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'search') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'paymentmethods') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'thirdparty' || $delay == 'customer' || $delay == 'supplier' || $delay == 'contact') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'proposal') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'order') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'invoice') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'contract') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'project') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'member') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'donation') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'ticket') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'product') { $delay = 8 * HOUR_IN_SECONDS; }
elseif ($delay == 'cart') { $delay = 20 * MINUTE_IN_SECONDS; }
elseif ($delay == 'document') { $delay = MONTH_IN_SECONDS; }
} else {
$delay = HOUR_IN_SECONDS;
//$delay = get_site_option('doliconnect_delay_'.$delay);
}
 
}

$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
$delay=0;
}
if ( $refresh && is_user_logged_in() ) {
$delay=$delay*-1;
}

return $delay;
}

function dolirefresh( $origin, $url, $delay, $element = null) {

$refresh = "<script>";
$refresh .= 'function refreshloader(){
jQuery("#DoliconnectLoadingModal").modal("show");
jQuery(window).scrollTop(0); 
this.form.submit();
}';
$refresh .= "</script>";

if ( isset($element->date_modification) && !empty($element->date_modification) ) {
$refresh .= "<i class='fas fa-database'></i> ".wp_date( get_option( 'date_format' ).' - '.get_option('time_format'), $element->date_modification, false);
} elseif ( get_option("_transient_timeout_".$origin) > 0 ) {
$refresh .= "<i class='fas fa-database'></i> ".wp_date( get_option( 'date_format' ).' - '.get_option('time_format'), get_option("_transient_timeout_".$origin)-$delay, false);
} elseif (is_user_logged_in() ) {
$refresh .= __( 'Refresh', 'doliconnect');
}
 
if (is_user_logged_in() ) {
$refresh .= " <a onClick='refreshloader()' href='".esc_url( add_query_arg( 'refresh', true, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) )."' title='".__( 'Refresh datas', 'doliconnect')."'><i class='fas fa-sync-alt'></i></a>";
}

return $refresh;
}

function dolikiosk() {
$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
return true;
} else {
return false;
}
}

function dolialert($type, $msg) { //__( 'Oops!', 'doliconnect')
$alert = '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">';
if ($type == 'success') {
$alert .= '<strong>'.__( 'Congratulations!', 'doliconnect').'</strong>';
} elseif ($type == 'warning') {
$alert .= '<strong>'.__( 'Be carefull', 'doliconnect').'</strong>';
} else {
$alert .= '<strong>'.__( 'Oops', 'doliconnect').'</strong>';
}
$alert .= ' '.$msg;
$alert .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
return $alert;
}

function doliloaderscript($idform, $scrolltop = true) {
$loader = "<script>";
$loader .= 'window.setTimeout(function () {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);';

$loader .= 'var form = document.getElementById("'.$idform.'");';
$loader .= 'form.addEventListener("submit", function(event) {
jQuery("#DoliconnectLoadingModal").modal("show");';
if (!empty($scrolltop)) $loader .= 'jQuery(window).scrollTop(0);'; 
$loader .= 'console.log("submit");
form.submit();
});';
$loader .= "</script>";
return $loader;
}

function dolimodalloaderscript($idform) {
print "<script>";
?>
var form = document.getElementById('<?php print $idform; ?>');
form.addEventListener('submit', function(event) { 
jQuery(window).scrollTop(0);
jQuery('#Close<?php print $idform; ?>').hide(); 
jQuery('#Footer<?php print $idform; ?>').hide();
jQuery('#<?php print $idform; ?>').hide(); 
jQuery('#doliloading-<?php print $idform; ?>').show(); 
console.log("submit");
form.submit();
});
<?php
print "</script>";
}

function doliaddress($object, $refresh = false) {
global $current_user;
if ( !empty($object->name) ) {
$address = "<b><i class='fas fa-building fa-fw'></i> ".$object->name;
} else {
$address = "<b><i class='fas fa-building fa-fw'></i> ".($object->civility ? $object->civility : $object->civility_code)." ".$object->firstname." ".$object->lastname;
}
if ( !empty($object->default) ) { $address .= " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($object->poste) ) { $address .= ", ".$object->poste; }
if ( !empty($object->type) ) { $address .= "<br>".__( 'Type', 'doliconnect').": ".$object->type; }
$address .= "</b><br>";
if ( !empty($object->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$object->country_id."?lang=".$lang, null, dolidelay('constante', $refresh)); }
$address .= "<small class='text-muted'>".$object->address.", ".$object->zip." ".$object->town." - ".$country->label."<br>".$object->email." - ".(isset($object->phone) ? $object->phone : (isset($object->phone_pro)?$object->phone_pro:null))."</small>";
return $address;
}

function dolicontact($id, $refresh = false) {
global $current_user;
$object = callDoliApi("GET", "/contacts/".$id, null, dolidelay('contact', esc_attr(isset($refresh) ? $refresh : null)));  
if (isset($object->id)) {
$address = "<b><i class='fas fa-address-book fa-fw'></i> ".($object->civility ? $object->civility : $object->civility_code)." ".$object->firstname." ".$object->lastname;
if ( !empty($object->default) ) { $address .= " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($object->poste) ) { $address .= ", ".$object->poste; }
$address .= "</b><br>";
if ( !empty($object->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$object->country_id."?lang=".$lang, null, dolidelay('constante', $refresh)); }
$address .= "<small class='text-muted'>".$object->address.", ".$object->zip." ".$object->town." - ".$country->label."<br>".$object->email." - ".$object->phone_pro."</small>";
return $address;
}
}

function dolitotal($object) { 
$total = "<li class='list-group-item bg-light'><b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($object, 'ht', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b></li>";
$total .= "<li class='list-group-item bg-light'><b>".__( 'Total VAT', 'doliconnect').": ".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b></li>";
$total .= "<li class='list-group-item list-group-item-primary'><b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b></li>";
//if ( ! empty($object->cond_reglement_id) ) { $total .= "<b>".__( 'Terms of the settlement', 'doliconnect').":</b> ".$object->cond_reglement; }
//$total .= "</li>";
return $total;
}

function doliline($object, $refresh = false, $refreshstock = false) {
global $current_user;

$doliline = null;

if ( isset($object) && is_object($object) && isset($object->lines) && $object->lines != null && (doliconnector($current_user, 'fk_soc') == $object->socid) ) {  
foreach ( $object->lines as $line ) { 

if ( $line->fk_product > 0 ) {
if ($refresh || $refreshstock) $refreshstock = true;
$product = callDoliApi("GET", "/products/".$line->fk_product."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('cart', $refreshstock));
}

$warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE', $refresh);
if (isset($product->stock_warehouse) && !empty($product->stock_warehouse) && !empty($warehouse) && $warehouse > 0) {
if (isset($product->stock_warehouse->$warehouse)) {
$realstock = min(array($product->stock_reel,$product->stock_warehouse->$warehouse->real,$product->stock_theorique));
} else {
$realstock = 0;
}
} else {
$realstock = min(array($product->stock_theorique,$product->stock_reel));
}

if (( $realstock <= 0 || (isset($product->array_options->options_packaging) && $realstock < $product->array_options->options_packaging ) ) && is_page(doliconnectid('dolicart')) && empty($product->type) && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) ) {
$doliline .= "<li class='list-group-item list-group-item-danger list-group-item-action'>";
define('dolilockcart', '1'); 
} elseif ($realstock < $line->qty && $realstock > 0 && is_page(doliconnectid('dolicart')) && empty($product->type) && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) ) {
$doliline .= "<li class='list-group-item list-group-item-warning list-group-item-action'>";
define('dolilockcart', '1'); 
} else {
$doliline .= "<li class='list-group-item list-group-item-light list-group-item-action'>";
//define('dolilockcart', '0'); 
}    
if ( $line->date_start != '' && $line->date_end != '' )
{
$start = wp_date('d/m/Y', $line->date_start);
$end = wp_date('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

$doliline .= '<div class="w-100 justify-content-between"><div class="row"><div class="d-none d-sm-block col-sm-2 col-lg-1"><center>';
if ( !empty(doliconst('MAIN_MODULE_FRAISDEPORT', $refresh)) && doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE', $refresh) == $line->fk_product ) {
$doliline .= '<i class="fas fa-shipping-fast fa-2x fa-fw"></i>';
} else {
$doliline .= doliconnect_image('product', $line->fk_product, array('limit'=>1, 'size'=>'50x50'), $refresh);
}

$doliline .= '</center></div><div class="col-8 col-sm-7 col-md-5 col-lg-6"><h6 class="mb-1">'.doliproduct($line, 'product_label').'</h6>';

if ( doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE') != $line->fk_product ) {
$doliline .= "<p><small>";
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $doliline .= "<i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-'); }
if ( !empty($product->barcode) ) { 
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $doliline .= " | "; }
$doliline .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
$doliline .= "</small></p>";
if(!empty(doliconst('PRODUIT_DESC_IN_FORM', $refresh)) && !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_DESC', $refresh) ) { $doliline .= '<p class="mb-1"><small>'.doliproduct($line, 'product_desc').'</small></p>'; }
$doliline .= '<p><small><i>'.(isset($dates) ? $dates : null).'</i></small></p>';
} elseif (doliconnectid('dolishipping')) {
$doliline .= '<small><a href="'.doliconnecturl('dolishipping').'">'.esc_html__( 'Shipping informations', 'doliconnect').'</a></small>';
}

if (( $realstock <= 0 || (isset($product->array_options->options_packaging) && $realstock < $product->array_options->options_packaging ) ) && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) ) {
$doliline .= "<b>".__( "Sorry, this product is no longer available. Please, delete it to finalize your order", 'doliconnect')."</b>";
} elseif ($realstock < $line->qty && $realstock > 0 && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) ) {
$doliline .= "<b>".__( "Sorry, this product is not available with this quantity. Please, change it to finalize your order", 'doliconnect')."</b>";
}

$doliline .= '</div><div class="col d-none d-md-block col-md-3 text-right">';
if ( $object->statut == 0 && !is_page(doliconnectid('doliaccount')) && doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE', $refresh) != $line->fk_product  ) {
$doliline .= '<center>'.doliproductstock($product).'</center>';
if ( !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', $refresh));
$doliline .= "<center><small><span class='flag-icon flag-icon-".strtolower($product->country_code)."'></span> ".$country->label."</small></center>"; }
}

$doliline .= '</div><div class="col-4 col-sm-3 col-md-2 text-right"><h6 class="mb-1">'.doliprice($line, (empty(get_option('dolibarr_b2bmode'))?'total_ttc':'total_ht'), isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h6>';
if ( !empty(doliconst('MAIN_MODULE_FRAISDEPORT', $refresh)) && doliconst('FRAIS_DE_PORT_ID_SERVICE_TO_USE', $refresh) == $line->fk_product ) {
$doliline .= '<h6 class="mb-1">x'.$line->qty.'</h6>';
} elseif ( $object->statut == 0 && !is_page(doliconnectid('doliaccount')) ) {
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][product]' value='".$line->fk_product."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][price]' value='".$line->subprice."'>";
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][remise_percent]' value='".$line->remise_percent."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_start]' value='".$line->date_start."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_end]' value='".$line->date_end."'>";
$doliline .= "<div class='input-group input-group-sm mb-3'>";
if ((empty($realstock) && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) )) )  { 
$doliline .= "<button type='button' class='btn btn-danger btn-sm btn-block' id='deleteorderproduct-".$line->fk_product."' name='deleteorderproduct-".$line->fk_product."' value='0' title='".__( 'Delete', 'doliconnect')."'><i class='fas fa-trash fa-fw'></i></button>";
} else {
$doliline .= "<button type='button' class='btn btn-danger' id='deleteorderproduct-".$line->fk_product."' name='deleteorderproduct-".$line->fk_product."' value='0' title='".__( 'Delete', 'doliconnect')."'><i class='fas fa-trash fa-fw'></i></button>";
}
//} else {
if (empty($product->type) && !empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) && empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER', $refresh))) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m0 = 1*$product->array_options->options_packaging;
$m1 = get_option('dolicartlist')*$product->array_options->options_packaging;
} else {
$m0 = 1;
$m1 = get_option('dolicartlist');
}
$m2 = $m1; 
} elseif ( $realstock-$line->qty >= 0 && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) ) ) {
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$m0 = 1*$product->array_options->options_packaging;
$m1 = get_option('dolicartlist')*$product->array_options->options_packaging;
} else {
$m0 = 1;
$m1 = get_option('dolicartlist');
}
if ( $realstock-$line->qty >= $m1 || empty(doliconst('MAIN_MODULE_STOCK', $refresh)) ) {
$m2 = $m1;
} elseif ( $realstock > $line->qty ) {
$m2 = $realstock;
} else { $m2 = $line->qty; }
} else {
$m0 = 1;
if ( isset($line) && $line->qty > 1 ) { $m2 = $line->qty; }
else { $m2 = 1; }
} 
if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
$step = $product->array_options->options_packaging;
} else {
$step = 1;
}              
if ((empty($realstock) && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES', $refresh)) )) || $m2 < $step)  { 
//$doliline .= "<option value='0' selected>".__( 'Unavailable', 'doliconnect')."</option></select>"; 
} elseif (!empty($m2) && $m2 >= $step) {
$doliline .= "<select class='form-control btn-light btn-outline-secondary' id='updateorderproduct-".$line->fk_product."' name='updateorderproduct-".$line->fk_product."'>";
foreach (range($m0, $m2, $step) as $number) {
if ( ($number == $step && empty($line->qty) ) || $number == $line->qty || ($number == $m0 && empty($line->qty) )) {
$doliline .= "<option value='$number' selected='selected'";
if ($realstock < $number && is_page(doliconnectid('dolicart')) && $product->type == '0' && !empty(doliconst('MAIN_MODULE_STOCK', $refresh)) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER', $refresh)) ) $doliline .= " disabled";
$doliline .= ">x ".$number."</option>";
} else {
$doliline .= "<option value='$number' >x ".$number."</option>";
}
	}
$doliline .= "</select>";
} else {
$doliline .= '<h6 class="mb-1">x'.$line->qty.'</h6>';
}
$doliline .= "</div>";
$doliline .= "<script>";
$doliline .= "(function ($) {
$(document).ready(function(){
$('#deleteorderproduct-".$line->fk_product."').on('click',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
var qty = $(this).val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'update_cart',
            'productid': '".$line->fk_product."',
            'qty': qty,
            'price': '".$line->subprice."' 
          }
        }).done(function(response) {
      if (response.success) {
$('#a-tab-info').addClass('disabled'); 
$('#a-tab-pay').addClass('disabled');
window.location.reload();  
//document.getElementById('doliline').innerHTML = response.data.lines;
//if (document.getElementById('DoliHeaderCarItems')) {
//document.getElementById('DoliHeaderCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliFooterCarItems')) {  
//document.getElementById('DoliFooterCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliWidgetCarItems')) {
//document.getElementById('DoliWidgetCarItems').innerHTML = response.data.items;
//} 
console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
$('#updateorderproduct-".$line->fk_product."').on('change',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
var qty = $(this).val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'update_cart',
            'productid': '".$line->fk_product."',
            'qty': qty,
            'price': '".$line->subprice."' 
          }
        }).done(function(response) {
      if (response.success) {
$('#a-tab-info').addClass('disabled'); 
$('#a-tab-pay').addClass('disabled');
window.location.reload();  
//document.getElementById('doliline').innerHTML = response.data.lines;
//if (document.getElementById('DoliHeaderCarItems')) {
//document.getElementById('DoliHeaderCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliFooterCarItems')) {  
//document.getElementById('DoliFooterCarItems').innerHTML = response.data.items;
//}
//if (document.getElementById('DoliWidgetCarItems')) {
//document.getElementById('DoliWidgetCarItems').innerHTML = response.data.items;
//} 
console.log(response.data.message);
}
//$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
$doliline .= "</script>";
//} 
} else {
$doliline .= '<h6 class="mb-1">x'.$line->qty.'</h6>';
}
$doliline .= "</div></div></li>";
}
} else {
$doliline .= "<li class='list-group-item list-group-item-light'><br><br><br><br><br><center><h5>".__( 'Your basket is empty', 'doliconnect')."</h5></center>";
if ( !is_user_logged_in() ) {
$doliline .= '<center>'.__( 'If you already have an account,', 'doliconnect').' ';

if ( get_option('doliloginmodal') == '1' ) {
       
$doliline .= '<a href="#" data-bs-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('sign in', 'doliconnect').'" role="button">'.__( 'sign in', 'doliconnect').'</a> ';
} else {
$doliline .= "<a href='".wp_login_url( doliconnecturl('dolicart') )."?redirect_to=".doliconnecturl('dolicart')."' title='".__('sign in', 'doliconnect')."'>".__( 'sign in', 'doliconnect').'</a> ';
}
$doliline .= __( 'to see your basket.', 'doliconnect').'</center>';
}
$doliline .= "<br><br><br><br><br></li>";
} 
return $doliline;
}

function doliunit($scale, $type, $refresh = null) {
$unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.scale%3A%3D%3A'".$scale."')%20AND%20(t.unit_type%3A%3D%3A'".$type."')", null, dolidelay('constante', $refresh));
return $unit[0]->short_label;
}

function doliduration($object) {
if ( !is_null($object->duration_unit) && 0 < ($object->duration_value)) {
$duration = $object->duration_value.' ';
if ( $object->duration_value > 1 ) {
if ( $object->duration_unit == 'y' ) { $duration .=__( 'years', 'doliconnect'); }
elseif ( $object->duration_unit == 'm' )  { $duration .=__( 'months', 'doliconnect'); }
elseif ( $object->duration_unit == 'w' )  { $duration .=__( 'weeks', 'doliconnect'); }
elseif ( $object->duration_unit == 'd' )  { $duration .=__( 'days', 'doliconnect'); }
elseif ( $object->duration_unit == 'h' )  { $duration .=__( 'hours', 'doliconnect'); }
elseif ( $object->duration_unit == 'i' )  { $duration .=__( 'minutes', 'doliconnect'); }
} else {
if ( $object->duration_unit == 'y' ) { $duration .=__( 'year', 'doliconnect');}
elseif ( $object->duration_unit == 'm' )  { $duration .=__( 'month', 'doliconnect'); }
elseif ( $object->duration_unit == 'w' )  { $duration .=__( 'week', 'doliconnect'); }
elseif ( $object->duration_unit == 'd' )  { $duration .=__( 'day', 'doliconnect'); }
elseif ( $object->duration_unit == 'h' )  { $duration .=__( 'hour', 'doliconnect'); }
elseif ( $object->duration_unit == 'i' )  { $duration .=__( 'minute', 'doliconnect'); }
}

if ( $object->duration_unit == 'i' ) {
$altdurvalue=60/$object->duration_value; 
}

} else {
$duration = '';
}
return $duration;
}

function dolipaymentterm($id, $refresh = false) {
$paymenterm = callDoliApi("GET", "/setup/dictionary/payment_terms?sortfield=rowid&sortorder=ASC&limit=100&active=1&sqlfilters=(t.rowid%3A%3D%3A'".$id."')", null, dolidelay('constante', $refresh)); 
//print var_dump($paymenterm[0]);
if ($paymenterm[0]->type_cdr == 1) {
$term = sprintf( _n( '%s day', '%s days', $paymenterm[0]->nbjour, 'doliconnect'), $paymenterm[0]->nbjour);
$term .= ", ".__( 'end of month', 'doliconnect');
} elseif ($paymenterm[0]->type_cdr == 2) {
$term = sprintf( _n( '%s day', '%s days', $paymenterm[0]->nbjour, 'doliconnect'), $paymenterm[0]->nbjour);
$term .= ", ".sprintf( __( 'the %s of month', 'doliconnect'), $paymenterm[0]->decalage);
} else {
$term = sprintf( _n( '%s day', '%s days', $paymenterm[0]->nbjour, 'doliconnect'), $paymenterm[0]->nbjour);
}
return $term;
}

function dolishipmentmethods($id, $refresh = false) {
if ( !empty($id) && $id > 0) {
$paymenterm = callDoliApi("GET", "/setup/dictionary/shipping_methods?sortfield=rowid&sortorder=ASC&limit=100&active=1&sqlfilters=(t.rowid%3A%3D%3A'".$id."')", null, dolidelay('constante', $refresh)); 
//print var_dump($paymenterm[0]);
$term = (isset($paymenterm[0]->label)?$paymenterm[0]->label:$paymenterm[0]->libelle); 
if (isset($paymenterm[0]->description) && !empty($paymenterm[0]->description)) $term .= ' <small>('.$paymenterm[0]->description.')</small>'; 
return $term;
} else {
return __('Transporter by default', 'doliconnect'); 
}
}

function doliconnect_langs($arg) {

if (function_exists('pll_the_languages')) {       

print '<div class="modal fade" id="DoliconnectSelectLang" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="DoliconnectSelectLangLabel" aria-hidden="true">
<div class="modal-dialog modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="DoliconnectSelectLangLabel">'.__('Choose your language', 'doliconnect').'</h5><button id="closemodalSelectLang" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></button></div>';
 
print '<script>';
?>
function loadingSelectLangModal() {
jQuery("#closemodalSelectLang").hide();
jQuery("#SelectLangmodal-form").hide();
jQuery("#loadingSelectLang").show();  
}
<?php
print '</script>';

print '<div class="modal-body"><div class="card" id="SelectLangmodal-form"><ul class="list-group list-group-flush">';
$translations = pll_the_languages( array( 'raw' => 1 ) );
foreach ($translations as $key => $value) {
print "<a href='".$value['url']."?".$_SERVER["QUERY_STRING"]."' onclick='loadingSelectLangModal()' class='list-group-item list-group-item-light list-group-item-action";
if ( $value['current_lang'] == true ) { print " active"; }
print "'><span class='flag-icon flag-icon-".strtolower(substr($value['slug'], -2))."'></span> ".$value['name'];
if ( $value['current_lang'] == true ) { print " <i class='fas fa-language fa-fw'></i>"; }
print "</a>";
}      

print '</ul></div>
<div id="loadingSelectLang" style="display:none"><br><br><br><center><div class="align-middle"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><h4>'.__('Loading', 'doliconnect').'</h4></div></center><br><br><br></div>
</div></div></div></div>';

}    

}
add_action( 'wp_footer', 'doliconnect_langs', 10, 1);

function doliconnect_paymentmethods($object = null, $module = null, $url = null, $refresh = false, $array = array()) {
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
 
if ( !empty($module) && is_object($object) && isset($object->id) ) {
if ($module == 'orders') { $module2 = 'order'; }
elseif ($module == 'invoices') { $module2 = 'invoice'; }
elseif ($module == 'donations') { $module2 = 'donation'; }
else { $module2 = $module; }
$request .= "?type=".$module2."&rowid=".$object->id;
$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');  
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;
}

$listpaymentmethods = callDoliApi("GET", $request, null, dolidelay('paymentmethods', $refresh));
//print $listpaymentmethods;
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', $refresh)); 
//print $thirdparty;

$paymentmethods = '';
 
if ( isset($listpaymentmethods->stripe) ) {
$paymentmethods .= '<script src="https://js.stripe.com/v3/"></script>';
$paymentmethods .= '<script>';
$paymentmethods .= 'var style = {
  base: {
    color: "#32325d",
    lineHeight: "25px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#6c757d"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};';
if ( !empty($listpaymentmethods->stripe->account) && isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->stripe->account."'
});";
} elseif ( isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."');";
} 
if ( isset($listpaymentmethods->stripe->publishable_key) ) {
$paymentmethods .= "var elements = stripe.elements();";
}
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= '</script>';
}

if (isset($array["payment_intent"]) && isset($array["payment_intent_client_secret"]) && isset($array["redirect_status"]) ) {
$paymentmethods .= "<script>";
$paymentmethods .= "(function ($) {
$(document).ready(function(){
$('#DoliconnectLoadingModal').modal('show');
stripe.retrievePaymentIntent('".$array["payment_intent_client_secret"]."')
  .then(function(result) {
    // Handle result.error or result.paymentIntent
    if (result.error) {
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = result.error;      
}
    $('#DoliconnectLoadingModal').modal('hide');  
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': result.paymentIntent.id,
            'paymentmethod': result.paymentIntent.payment_method,     
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
$('#DoliconnectLoadingModal').modal('hide'); 
if (response.success) {
if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};
} else {
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.error;      
}
}
});  
    }  
  });
 });  
})(jQuery);";
$paymentmethods .= "</script>";
}

//if ( isset($listpaymentmethods->stripe) && in_array('payment_request_api', $listpaymentmethods->stripe->types) && !empty($module) && is_object($object) && isset($object->id) && empty($thirdparty->mode_reglement_id) ) {
//$paymentmethods .= "<div id='pra-error-message' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
//$paymentmethods .= "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>
//<div id='else' style='display: none' ><br><div style='display:inline-block;width:46%;float:left'><hr width='90%' /></div><div style='display:inline-block;width: 8%;text-align: center;vertical-align:90%'><small class='text-muted'>".__( 'or', 'doliconnect' )."</small></div><div style='display:inline-block;width:46%;float:right' ><hr width='90%'/></div><br></div>";
//} 

$paymentmethods .= '<div id="DoliPaymentmethodAlert"></div>';

$paymentmethods .= '<div class="card shadow-sm">';
if ( empty($module) ) { $paymentmethods .= '<div class="card-header">'.__( 'Manage payment methods', 'doliconnect').'</div>'; } else{
  $paymentmethods .= '<div class="card-header">'.__( 'Choose payment method', 'doliconnect').'</div>';
}
$paymentmethods .= '<div class="accordion accordion-flush" id="accordionFlushExample">';
if (empty($listpaymentmethods->payment_methods)) {
$countPM = 0;
} else {
$countPM = count(get_object_vars($listpaymentmethods->payment_methods));
}
$maxPM = 5;

$minPM = 0;
if ( has_filter( 'doliconnect_force_minipaymentmethod') ) {
if (is_numeric(apply_filters( 'doliconnect_force_minipaymentmethod', $listpaymentmethods->payment_methods))){
$minPM = apply_filters( 'doliconnect_force_minipaymentmethod', $listpaymentmethods->payment_methods);
}
}
if ( isset($listpaymentmethods->payment_methods) && $listpaymentmethods->payment_methods != null ) {
foreach ( $listpaymentmethods->payment_methods as $method ) { 
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'PRE')", null, dolidelay('constante'));
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-heading'.$method->id.'"><button class="accordion-button';
if ( $method->default_source && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR')) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) ) { $paymentmethods .= ""; } else { $paymentmethods .= " collapsed"; }
$paymentmethods .= '" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse'.$method->id.'" aria-expanded="';
if ( $method->default_source && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR')) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) ) { $paymentmethods .= "true"; } else { $paymentmethods .= "false"; }
$paymentmethods .= '" aria-controls="flush-collapse'.$method->id.'">';
$paymentmethods .= '<i ';
if ( $method->type == 'sepa_debit' || $method->type == 'PRE' || $method->type == 'VIR' ) { $paymentmethods .= 'class="fas fa-university fa-3x fa-fw float-start" style="color:DarkGrey"'; } 
elseif ( $method->brand == 'visa' ) { $paymentmethods .= 'class="fab fa-cc-visa fa-3x fa-fw float-start" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { $paymentmethods .= 'class="fab fa-cc-mastercard fa-3x fa-fw float-start" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { $paymentmethods .= 'class="fab fa-cc-amex fa-3x fa-fw float-start" style="color:#2E78BF"'; }
else { $paymentmethods .= 'class="fab fa-credit-card fa-3x fa-fw float-start"';}
$paymentmethods .= '></i> <div>';
if ( $method->type == 'sepa_debit' || $method->type == 'PRE' || $method->type == 'VIR' ) {
$paymentmethods .= __( 'Account', 'doliconnect')." ".$method->reference;
} else {
$paymentmethods .= __( 'Card', 'doliconnect').' '.$method->reference;
}
if ( $method->default_source && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR')) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold'></i>"; }
$paymentmethods .= '<br><small class="text-muted">'.$method->holder.'</small></div><span class="flag-icon flag-icon-'.strtolower($method->country).' float-end"></span></button></h2>';
$paymentmethods .= '<div id="flush-collapse'.$method->id.'" class="accordion-collapse collapse';
if ( $method->default_source && empty($thirdparty->mode_reglement_id) && !in_array($method->type, array('PRE','VIR')) || (!empty($method->default_source) && !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) ) { $paymentmethods .= " show"; }
$paymentmethods .= '" aria-labelledby="flush-heading'.$method->id.'" data-bs-parent="#accordionFlushExample"><div class="accordion-body bg-light">';
if ( empty($module) && !is_object($object) && doliCheckRights('societe', 'thirdparty_paymentinformation_advance', 'write', null, '15.0.0') ) {
$paymentmethods .= "<script>";
$paymentmethods .= "(function ($) {
$(document).ready(function(){
$('#defaultbtn_".$method->id.", #deletebtn_".$method->id."').on('click',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
var actionvalue = $(this).val();
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolipaymentmethod_request',
            'dolipaymentmethod-nonce': '".wp_create_nonce( 'dolipaymentmethod-nonce')."',
            'payment_method': '".$method->id."',
            'case': actionvalue
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(actionvalue);
      if (response.success) {
if (actionvalue == 'delete_payment_method')  {
//document.getElementById('li-".$method->id."').remove();
//document.getElementById('nav-tab-".$method->id."').remove();
document.location = '".$url."';
} else {
document.location = '".$url."';
}
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}
console.log(response.data);
}
$('#DoliconnectLoadingModal').modal('hide');
        });
});
});
})(jQuery);";
$paymentmethods .= "</script>";
}
$paymentmethods .= "<div class='row'><div class='col-12 col-sm-6'>
  <dt>".__( 'Debtor', 'doliconnect')."</dt>
  <dd>".__( 'Holder:', 'doliconnect')." ".$method->holder;
if (isset($method->mandate->creation) && !empty($method->mandate->creation)) {
$paymentmethods .= "<br>".__( 'Creation:', 'doliconnect');
$paymentmethods .= " ".wp_date( 'j F Y', $method->mandate->creation, false); }
if (isset($method->expiration) && !empty($method->expiration)) {
$paymentmethods .= "<br>".__( 'Expiration:', 'doliconnect');
$expdate =  explode("/", $method->expiration);
$timestamp = mktime(0, 0, 0, (int) $expdate['1'], 0, (int) $expdate['0']);
$paymentmethods .= " ".wp_date( 'F Y', $timestamp, false); }
$paymentmethods .= "</dd>
</div>";
if (isset($method->mandate) && !empty($method->mandate)) { $paymentmethods .= "<div class='col-12 col-sm-6'>
  <dt>".__( 'Mandate', 'doliconnect')."</dt>
  <dd>".__( 'Reference:', 'doliconnect')." ";
if (isset($method->mandate->url) && !empty($method->mandate->url)) { $paymentmethods .= "<a href='".$method->mandate->url."' target='_blank'>"; }
$paymentmethods .= $method->mandate->reference;
if (isset($method->mandate->url) && !empty($method->mandate->url)) { $paymentmethods .= "</a>"; }
$paymentmethods .= "<br>".__( 'Type:', 'doliconnect')." ";
if (($method->mandate->type == 'multi_use') || ($method->mandate->type == 'RECUR')) {
$paymentmethods .= __( 'Recurring', 'doliconnect').' (RECUR)'; 
} elseif (($method->mandate->type == 'single_use') || ($method->mandate->type == 'FRST')) {
$paymentmethods .= __( 'Unique', 'doliconnect').' (FRST)';
}
$paymentmethods .= "</dd>
</div>"; }
$paymentmethods .= "</div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><b>".__( 'Payment term', 'doliconnect').":</b> ";
if (!empty($thirdparty->cond_reglement_id)) { 
$paymentmethods .= dolipaymentterm($thirdparty->cond_reglement_id);
} else {
$paymentmethods .= __( 'immediately', 'doliconnect');
}
$paymentmethods .= "</small>";
$paymentmethods .= '</p><div class="d-grid gap-2">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
if ( $method->type == 'card' ) {
$paymentmethods .= '<button type="button" onclick="PayCardPM(\''.$method->id.'\')" class="btn btn-danger btn-block">'.__( 'I order', 'doliconnect').'</button>';
} elseif ( $method->type == 'sepa_debit' ) {
$paymentmethods .= '<button type="button" onclick="PaySepaDebitPM(\''.$method->id.'\')" class="btn btn-danger btn-clock">'.__( 'I order', 'doliconnect').'</button>';
} else {
$paymentmethods .= '<button type="button" onclick="PayPM(\''.$method->type.'\')" class="btn btn-danger btn-block">'.__( 'I order', 'doliconnect').'</button>';
}
} elseif (doliCheckRights('societe', 'thirdparty_paymentinformation_advance', 'write', null, '15.0.0')) {
$paymentmethods .= '<div class="btn-group btn-block" role="group" aria-label="actions buttons">';
if ( !isset($method->default_source) && !in_array($method->type, array('VIR')) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= "<button type='button' id='defaultbtn_".$method->id."' name='default_payment_method' value='default' class='btn btn-outline-secondary'";
$paymentmethods .= "title='".__( 'Favourite', 'doliconnect')."'><i class='fas fa-star fa-fw' style='color:Gold'></i> ".__( "Favourite", 'doliconnect')."</button>";
}
if ( (!isset($method->default_source) && $countPM > ($minPM+1)) || ($countPM == 1 && $minPM == 0) || in_array($method->type, array('VIR')) ) { 
$paymentmethods .= "<button type='button' id='deletebtn_".$method->id."' name='delete_payment_method' value='delete' class='btn btn-outline-secondary'";
$paymentmethods .= "title='".__( 'Delete', 'doliconnect')."'><i class='fas fa-trash fa-fw' style='color:Red'></i> ".__( 'Delete', 'doliconnect').'</button>';
}
$paymentmethods .= "</div>";
}
$paymentmethods .= '</div></div></div></div>';
}}

if (isset($listpaymentmethods->stripe) && !empty(array_intersect(array('card'), $listpaymentmethods->stripe->types)) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingnewcard"><button class="accordion-button';
if (empty($countPM)) { $paymentmethods .= ""; } else { $paymentmethods .= " collapsed"; }
$paymentmethods .= '" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsenewcard" aria-expanded="';
if (empty($countPM)) { $paymentmethods .= "true"; } else { $paymentmethods .= "false"; }
$paymentmethods .= '" aria-controls="flush-collapsenewcard">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<i class="far fa-credit-card fa-3x fa-fw float-start"></i> '.__( 'Pay by bank card', 'doliconnect');
} else {
$paymentmethods .= '<i class="fas fa-plus-circle fa-3x fa-fw float-start"></i> '.__( 'Add a bank card', 'doliconnect');
}
$paymentmethods .= '</button></h2>';
$paymentmethods .= '<div id="flush-collapsenewcard" class="accordion-collapse collapse';
if (empty($countPM)) { $paymentmethods .= " show"; }
$paymentmethods .= '" aria-labelledby="flush-headingnewcard" data-bs-parent="#accordionFlushExample"><div class="accordion-body bg-light">';
if ($countPM >= $maxPM && empty($object)) {
$paymentmethods .= '<div class="text-justify"><i class="fas fa-times-circle fa-3x fa-fw float-start"></i>'.__( "You have reached limit of payment methods. Please delete a payment method for add a new one.", 'doliconnect').'</div></div></div>';
} else {
if (empty($listpaymentmethods->stripe->live)) {
$paymentmethods .= "<i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b> <small>(<a href='https://stripe.com/docs/testing#cards' target='_blank' rel='noopener'>".__( "Link to test card numbers", 'doliconnect')."</a>)</small>";
}
$paymentmethods .= "<input id='cardholder-name' name='cardholder-name' value='' type='text' class='form-control' placeholder='".__( "Full name on the card", 'doliconnect')."' autocomplete='off' required>
<label for='card-element'></label><div class='form-control' id='card-element'><!-- a Stripe Element will be inserted here. --></div>";
$paymentmethods .= "<div id='card-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<div class="form-check"><input type="radio" id="cardDefault0" name="cardDefault" value="0"  class="form-check-input" checked>
<label class="form-check-label text-muted" for="cardDefault0">'.__( "Not save", 'doliconnect').'</label></div>';
}
if ($countPM < $maxPM) {
$paymentmethods .= '<div class="form-check"><input type="radio" id="cardDefault1" name="cardDefault" value="1"  class="form-check-input"';
if (empty($countPM)) {
$paymentmethods .= ' disabled'; 
} elseif (empty($object)) {
$paymentmethods .= ' checked'; 
}
$paymentmethods .= '><label class="form-check-label text-muted" for="cardDefault1">'.__( "Save", 'doliconnect').'</label></div>';
$paymentmethods .= '<div class="form-check">
<input type="radio" id="cardDefault2" name="cardDefault" value="2" class="form-check-input"';
if (empty($countPM)) {
$paymentmethods .= ' checked'; 
} 
$paymentmethods .= '><label class="form-check-label text-muted" for="cardDefault2">'.__( "Save as favourite", 'doliconnect').'</label></div>';
}
$paymentmethods .= '<p class="text-justify">';
$paymentmethods .= '<small><strong>Note:</strong> '.sprintf( esc_html__( 'By providing your card and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to the financial institution that issued your card to take payments from your card account in accordance with those instructions. You are entitled to a refund from your financial institution under the terms and conditions of your agreement with it. A refund must be claimed within 90 days starting from the date on which your card was debited.', 'doliconnect'), get_bloginfo('name')).'</small>';
$paymentmethods .= '</p>';
$paymentmethods .= '<script>';
$paymentmethods .= "function dolistripecard(){
(function ($) {
$(document).ready(function(){";
$paymentmethods .= "var cardElement = elements.create('card', {style: style});
cardElement.mount('#card-element');
var cardholderName = document.getElementById('cardholder-name');
cardholderName.value = '';
var displayCardError = document.getElementById('card-error-message');
displayCardError.textContent = '';
cardElement.on('change', function(event) {
    console.log('Reset error message');
    displayCardError.textContent = '';
  if (event.error) {
    displayCardError.textContent = event.error.message;
    displayCardError.classList.add('visible');
  } else {
    displayCardError.textContent = '';
    displayCardError.classList.remove('visible');
  }
});";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
// pay with card script
$paymentmethods .= "$('#PayCardButton').on('click',function(event){
event.preventDefault();
$('#PayCardButton').disabled = true;
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on PayCardButton');
var cardholderName = document.getElementById('cardholder-name');
if (cardholderName.value == ''){               
console.log('Field Card holder is empty');
displayCardError.textContent = 'We need an owner as on your card';
$('#PayCardButton').disabled = false;
$('#DoliconnectLoadingModal').modal('hide');  
} else {
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = '';      
}  
  stripe.confirmCardPayment(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
$('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when using card');
displayCardError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': result.paymentIntent.id,
            'paymentmethod': result.paymentIntent.payment_method, 
            'default': $('input:radio[name=cardDefault]:checked').val()       
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
}
});
        }     
});";
} else {
// add a card
$paymentmethods .= "$('#AddCardButton').on('click',function(event){
event.preventDefault();
$('#AddCardButton').disabled = true;
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on AddCardButton');
var cardholderName = document.getElementById('cardholder-name');
if (cardholderName.value == ''){               
console.log('Field Card holder is empty');
displayCardError.textContent = 'We need an owner as on your card';
$('#AddCardButton').disabled = false;
$('#DoliconnectLoadingModal').modal('hide');  
} else {
  stripe.confirmCardSetup(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
$('#DoliconnectLoadingModal').modal('hide');
$('#AddCardButton').disabled = false;
console.log('Error occured when adding card');
displayCardError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolipaymentmethod_request',
            'dolipaymentmethod-nonce': '".wp_create_nonce( 'dolipaymentmethod-nonce')."',
            'payment_method': result.setupIntent.payment_method,
            'case': 'create',
            'default': $('input:radio[name=cardDefault]:checked').val()
          }
        }).done(function(response) {
$(window).scrollTop(0);
console.log(response.data); 
      if (response.success) {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
document.location = '".$url."';
      } else {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
$('#DoliconnectLoadingModal').modal('hide');
      }
        });
    }
  }); 
          }     
});";
}
$paymentmethods .= "});
})(jQuery);";
$paymentmethods .= '}';
$paymentmethods .= 'window.onload=dolistripecard();';
$paymentmethods .= '</script><div class="d-grid gap-2">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<button type="button" id="PayCardButton" class="btn btn-danger">'.__( 'I order', 'doliconnect').'</button>';
} else {
$paymentmethods .= "<button type='button' id='AddCardButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'>".__( 'Add', 'doliconnect')."</button>";
}
}
$paymentmethods .= '</div></div></div></div>';
}

if (isset($listpaymentmethods->stripe) && !empty(array_intersect(array('sepa_debit'), $listpaymentmethods->stripe->types)) && empty($thirdparty->mode_reglement_id) ) {
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingnewsepa"><button class="accordion-button';
if (empty($countPM) && empty(array_intersect(array('card'), $listpaymentmethods->stripe->types))) { $paymentmethods .= ""; } else { $paymentmethods .= " collapsed"; }
$paymentmethods .= '" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsenewsepa" aria-expanded="';
if (empty($countPM) && empty(array_intersect(array('card'), $listpaymentmethods->stripe->types))) { $paymentmethods .= "true"; } else { $paymentmethods .= "false"; }
$paymentmethods .= '" aria-controls="flush-collapsenewsepa">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<i class="fas fa-university fa-3x fa-fw float-start"></i> '.__( 'Pay by SEPA bank debit', 'doliconnect');
} else {
$paymentmethods .= '<i class="fas fa-plus-circle fa-3x fa-fw float-start"></i> '.__( 'Add a SEPA bank account', 'doliconnect');
}
$paymentmethods .= '</button></h2>';
$paymentmethods .= '<div id="flush-collapsenewsepa" class="accordion-collapse collapse';
if (empty($countPM) && empty(array_intersect(array('card'), $listpaymentmethods->stripe->types))) { $paymentmethods .= " show"; }
$paymentmethods .= '" aria-labelledby="flush-headingnewsepa" data-bs-parent="#accordionFlushExample"><div class="accordion-body bg-light">';
if ($countPM >= $maxPM && empty($object)) {
$paymentmethods .= '<div class="text-justify"><i class="fas fa-times-circle fa-3x fa-fw float-start"></i>'.__( "You have reached limit of payment methods. Please delete a payment method for add a new one.", 'doliconnect').'</div></div></div>';
} else {
if (empty($listpaymentmethods->stripe->live)) {
$paymentmethods .= "<i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b> <small>(<a href='https://stripe.com/docs/testing#sepa-direct-debit' target='_blank' rel='noopener'>".__( "Link to test SEPA account numbers", 'doliconnect')."</a>)</small>";
}
$paymentmethods .= "<input id='ibanholder-name' name='ibanholder-name' value='' type='text' class='form-control' placeholder='".__( "Full name of the owner", 'doliconnect')."' autocomplete='off' required>
<label for='iban-element'></label><div class='form-control' id='iban-element'><!-- a Stripe Element will be inserted here. --></div>";
$paymentmethods .= "<div id='bank-name' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<div id='iban-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><strong>Note:</strong> ".sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with it. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect'), get_bloginfo('name'))."</small>";
$paymentmethods .= "</p>";
$paymentmethods .= '<script>';
$paymentmethods .= "function dolistripeiban(){
(function ($) {
$(document).ready(function(){";
$paymentmethods .= "var ibanElement = elements.create('iban', {style: style, supportedCountries: ['SEPA']});
ibanElement.mount('#iban-element');
var displayIbanError = document.getElementById('iban-error-message');
var bankName = document.getElementById('bank-name');
ibanElement.on('change', function(event) {
  if (event.error) {
    displayIbanError.textContent = event.error.message;
    displayIbanError.classList.add('visible');
  } else {
    displayIbanError.classList.remove('visible');
  }
  if (event.bankName) {
    bankName.textContent = event.bankName;
    bankName.classList.add('visible');
  } else {
    bankName.classList.remove('visible');
  }
});";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
// pay with sepa_debit script
$paymentmethods .= "$('#PayIbanButton').on('click',function(event){
event.preventDefault();
$('#PayIbanButton').disabled = true;
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on PayIbanButton');
var ibanholderName = document.getElementById('ibanholder-name');
if (ibanholderName.value == ''){               
console.log('Field Card holder is empty');
displayIbanError.textContent = 'We need an owner as on your account';
$('#PayIbanButton').disabled = false;
$('#DoliconnectLoadingModal').modal('hide');  
} else {
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = '';      
}  
  stripe.confirmSepaDebitPayment(
    clientSecret,
    {
    payment_method: {
      sepa_debit: ibanElement,
      billing_details: {
        name: ibanholderName.value,
        email: '".$listpaymentmethods->thirdparty->email."',
      },
    },
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
$('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when using card');
displayCardError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': result.paymentIntent.id,
            'paymentmethod': result.paymentIntent.payment_method, 
            'default': $('input:radio[name=cardDefault]:checked').val()       
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
}
});
        }     
});";
} else {
// add a sepa debit
$paymentmethods .= "$('#AddIbanButton').on('click',function(event){
event.preventDefault();
$('#AddIbanButton').disabled = true;
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on AddIbanButton');
var ibanholderName = document.getElementById('ibanholder-name');
if (ibanholderName.value == ''){               
console.log('Field Iban holder is empty');
displayIbanError.textContent = 'We need an owner as on your account';
$('#AddIbanButton').disabled = false;
$('#DoliconnectLoadingModal').modal('hide');  
} else {
  stripe.confirmSepaDebitSetup(
    clientSecret,
    {
    payment_method: {
      sepa_debit: ibanElement,
      billing_details: {
        name: ibanholderName.value,
        email: '".$listpaymentmethods->thirdparty->email."',
      },
    },
    }
  ).then(function(result) {
    if (result.error) {
$('#DoliconnectLoadingModal').modal('hide');
$('#AddIbanButton').disabled = false;
console.log('Error occured when adding iban');
displayIbanError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolipaymentmethod_request',
            'dolipaymentmethod-nonce': '".wp_create_nonce( 'dolipaymentmethod-nonce')."',
            'payment_method': result.setupIntent.payment_method,
            'case': 'create',
            'default': $('input:radio[name=cardDefault]:checked').val()
          }
        }).done(function(response) {
$(window).scrollTop(0);
console.log(response.data); 
      if (response.success) {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
document.location = '".$url."';
      } else {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
$('#DoliconnectLoadingModal').modal('hide');
      }
        });
    }
  }); 
          }     
});";
}
$paymentmethods .= "});
})(jQuery);";
$paymentmethods .= '}';
$paymentmethods .= 'window.onload=dolistripeiban();';
$paymentmethods .= '</script><div class="d-grid gap-2">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<button id="PayIbanButton" class="btn btn-danger btn-block">'.__( 'I order', 'doliconnect').'</button>';
} else {
$paymentmethods .= "<button id='AddIbanButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'>".__( 'Add', 'doliconnect')."</button>";
}
}
$paymentmethods .= '</div></div></div></div>';
}

if (isset($listpaymentmethods->stripe) && !empty(array_intersect(array('klarna'), $listpaymentmethods->stripe->types)) && empty($thirdparty->mode_reglement_id) && !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingklarna"><button class="accordion-button';
if (empty($countPM) && empty(array_intersect(array('card'), $listpaymentmethods->stripe->types))) { $paymentmethods .= ""; } else { $paymentmethods .= " collapsed"; }
$paymentmethods .= '" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseklarna" aria-expanded="';
if (empty($countPM) && empty(array_intersect(array('card'), $listpaymentmethods->stripe->types))) { $paymentmethods .= "true"; } else { $paymentmethods .= "false"; }
$paymentmethods .= '" aria-controls="flush-collapseklarna">';
$paymentmethods .= '<i class="fas fa-university fa-3x fa-fw float-start"></i> '.__( 'Buy now or pay later with Klarna', 'doliconnect');
$paymentmethods .= '</button></h2>';
$paymentmethods .= '<div id="flush-collapseklarna" class="accordion-collapse collapse';
if (empty($countPM) && empty(array_intersect(array('card'), $listpaymentmethods->stripe->types))) { $paymentmethods .= " show"; }
$paymentmethods .= '" aria-labelledby="flush-headingklarna" data-bs-parent="#accordionFlushExample"><div class="accordion-body bg-light">';
if (empty($listpaymentmethods->stripe->live)) {
$paymentmethods .= "<i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b> <small>(<a href='https://stripe.com/docs/payments/klarna/accept-a-payment?platform=web#repayment-method' target='_blank' rel='noopener'>".__( "Link to test Klarna", 'doliconnect')."</a>)</small>";
}
$paymentmethods .= "<div id='klarna-error-message' class='text-danger' role='alert'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><strong>Note:</strong> ".sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with it. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect'), get_bloginfo('name'))."</small>";
$paymentmethods .= "</p>";
$paymentmethods .= '<script>';
$paymentmethods .= "function dolistripeklarna(){
(function ($) {
$(document).ready(function(){";
$paymentmethods .= "var displayKlarnaError = document.getElementById('klarna-error-message');";
if ( !empty($module) && is_object($object) && isset($object->id) ) {
// pay with sepa_debit script
$paymentmethods .= "$('#PayKlarnaButton').on('click',function(event){
event.preventDefault();
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on PayKlarnaButton');
if (1 == 7){               
 
} else { 
  stripe.confirmKlarnaPayment(
  clientSecret,
  {
    payment_method: {
      billing_details: {
        email: '".$listpaymentmethods->thirdparty->email."',
        address: {
          country: '".$listpaymentmethods->thirdparty->countrycode."',
        },
      },
    },
    return_url: '".$url."',
  }
).then(function(result) {
    if (result.error) {
      // Display error.message
$('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when using card');
displayCardError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': result.paymentIntent.id,
            'paymentmethod': result.paymentIntent.payment_method, 
            'default': $('input:radio[name=cardDefault]:checked').val()       
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
}
});
        }     
});";
} else {
// add a sepa debit
$paymentmethods .= "$('#AddIbanButton').on('click',function(event){
event.preventDefault();
$('#AddIbanButton').disabled = true;
$('#DoliconnectLoadingModal').modal('show');
console.log('Click on AddIbanButton');
var ibanholderName = document.getElementById('ibanholder-name');
if (ibanholderName.value == ''){               
console.log('Field Iban holder is empty');
displayCardError.textContent = 'We need an owner as on your account';
$('#AddIbanButton').disabled = false;
$('#DoliconnectLoadingModal').modal('hide');  
} else {
  stripe.confirmSepaDebitSetup(
    clientSecret,
    {
    payment_method: {
      sepa_debit: ibanElement,
      billing_details: {
        name: ibanholderName.value,
        email: '".$listpaymentmethods->thirdparty->email."',
      },
    },
    }
  ).then(function(result) {
    if (result.error) {
$('#DoliconnectLoadingModal').modal('hide');
$('#AddIbanButton').disabled = false;
console.log('Error occured when adding iban');
displayIbanError.textContent = result.error.message;    
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolipaymentmethod_request',
            'dolipaymentmethod-nonce': '".wp_create_nonce( 'dolipaymentmethod-nonce')."',
            'payment_method': result.setupIntent.payment_method,
            'case': 'create',
            'default': $('input:radio[name=cardDefault]:checked').val()
          }
        }).done(function(response) {
$(window).scrollTop(0);
console.log(response.data); 
      if (response.success) {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
document.location = '".$url."';
      } else {
      if (document.getElementById('DoliPaymentmethodAlert')) {
      document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
      }
$('#DoliconnectLoadingModal').modal('hide');
      }
        });
    }
  }); 
          }     
});";
}
$paymentmethods .= "});
})(jQuery);";
$paymentmethods .= '}';
$paymentmethods .= 'window.onload=dolistripeklarna();';
$paymentmethods .= '</script><div class="d-grid gap-2">';
$paymentmethods .= '<button id="PayKlarnaButton" class="btn btn-danger btn-block">'.__( 'I order', 'doliconnect').'</button>';
$paymentmethods .= '</div></div></div></div>';
}

if ( isset($listpaymentmethods->PAYPAL) && !empty($listpaymentmethods->PAYPAL) ) {
$paymentmethods .= '<li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#nav-tab-paypal">
<i class="fab fa-paypal float-start"></i> Paypal</a></li>';
}
if ( isset($listpaymentmethods->VIR) && !empty($listpaymentmethods->VIR) ) {
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'VIR')", null, dolidelay('constante'));
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingvir"><button class="accordion-button';
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= ""; } else { $paymentmethods .= " collapsed"; }
$paymentmethods .= '" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsevir" aria-expanded="';
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= "true"; } else { $paymentmethods .= "false"; }
$paymentmethods .= '" aria-controls="flush-collapsevir"><i class="fas fa-university fa-3x fa-fw float-start" style="color:DarkGrey"></i> ';
$paymentmethods .= __( 'Pay by bank transfert', 'doliconnect');
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold'></i>"; }
$paymentmethods .= '</button></h2>';
$paymentmethods .= '<div id="flush-collapsevir" class="accordion-collapse collapse';
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= " show"; }
$paymentmethods .= '" aria-labelledby="flush-headingvir" data-bs-parent="#accordionFlushExample"><div class="accordion-body bg-light">';

$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'VIR')", null, dolidelay('constante'));
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<p class='text-justify'>".sprintf( __( 'Please send your bank transfert in the amount of <b>%1$s</b> at the following account:', 'doliconnect'), doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null))."</p>";
} else {
$paymentmethods .= "<p class='text-justify'>".__( 'Please send your bank transfert at the following account:', 'doliconnect')."</p>";
}
$paymentmethods .= "<div class='row'>";
if (!empty($listpaymentmethods->VIR->bank)) { $paymentmethods .= "<div class='col-12 col-sm-6'>
  <dt>".__( 'Bank', 'doliconnect')."</dt>
  <dd>".$listpaymentmethods->VIR->bank."</dd>
</div>"; }
if (!empty($listpaymentmethods->VIR->number)) { $paymentmethods .= "<div class='col-12 col-sm-6'>
  <dt>".__( 'Account', 'doliconnect')."</dt>
  <dd>".$listpaymentmethods->VIR->number."</dd>
</div>"; }
if (!empty($listpaymentmethods->VIR->iban)) { $paymentmethods .= "<div class='col-12 col-sm-6'>
  <dt>IBAN</dt>
  <dd>".$listpaymentmethods->VIR->iban."</dd>
</div>"; }
if (!empty($listpaymentmethods->VIR->bic)) { $paymentmethods .= "<div class='col-12 col-sm-6'>
  <dt>BIC/SWIFT</dt>
  <dd>".$listpaymentmethods->VIR->bic."</dd>
</div>"; }
$paymentmethods .= "</div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><b>".__( 'Payment term', 'doliconnect').":</b> ";
if (!empty($thirdparty->cond_reglement_id)) { 
$paymentmethods .= dolipaymentterm($thirdparty->cond_reglement_id);
} else {
$paymentmethods .= __( 'immediately', 'doliconnect');
}
$paymentmethods .= '</small>';
$paymentmethods .= '</p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<div class="d-grid gap-2"><button type="button" onclick="PayPM(\'VIR\')" class="btn btn-danger btn-block">'.__( 'I order', 'doliconnect').'</button></div>';
} 
$paymentmethods .= '</div></div></div>';
}

if ( isset($listpaymentmethods->CHQ) && !empty($listpaymentmethods->CHQ) ) {
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'CHQ')", null, dolidelay('constante'));
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingvir"><button class="accordion-button';
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= ""; } else { $paymentmethods .= " collapsed"; }
$paymentmethods .= '" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsechq" aria-expanded="';
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= "true"; } else { $paymentmethods .= "false"; }
$paymentmethods .= '" aria-controls="flush-collapsechq"><i class="fa fa-money-check fa-3x fa-fw float-start" style="color:Tan"></i> ';
$paymentmethods .= __( 'Pay by bank check', 'doliconnect');
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= " <i class='fas fa-star fa-fw' style='color:Gold'></i>"; }
$paymentmethods .= '</button></h2>';
$paymentmethods .= '<div id="flush-collapsechq" class="accordion-collapse collapse';
if ( !empty($thirdparty->mode_reglement_id) && $thirdparty->mode_reglement_id == $mode_reglement_code[0]->id ) { $paymentmethods .= " show"; }
$paymentmethods .= '" aria-labelledby="flush-headingchq" data-bs-parent="#accordionFlushExample"><div class="accordion-body bg-light">';
$mode_reglement_code = callDoliApi("GET", "/setup/dictionary/payment_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.code%3A%3D%3A'CHQ')", null, dolidelay('constante'));
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<p class='text-justify'>".sprintf( __( 'Please send your money check in the amount of <b>%1$s</b> to <b>%2$s</b> at the following address:', 'doliconnect'), doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null), $listpaymentmethods->CHQ->proprio)."</p>";
} else {
$paymentmethods .= "<p class='text-justify'>".sprintf( __( 'Please send your money check to <b>%s</b> at the following address:', 'doliconnect'), $listpaymentmethods->CHQ->proprio)."</p>";
}
$paymentmethods .= "<div class='row'>";
$paymentmethods .= "<div class='col-12'><dl class='param'>
  <dt>Address</dt>
  <dd>".$listpaymentmethods->CHQ->proprio." - ".$listpaymentmethods->CHQ->owner_address."</dd>
</dl></div>";
$paymentmethods .= "</div>";
$paymentmethods .= "<p class='text-justify'>";
$paymentmethods .= "<small><b>".__( 'Payment term', 'doliconnect').":</b> ";
if (!empty($thirdparty->cond_reglement_id)) { 
$paymentmethods .= dolipaymentterm($thirdparty->cond_reglement_id);
} else {
$paymentmethods .= __( 'immediately', 'doliconnect');
}
$paymentmethods .= "</small>";
$paymentmethods .= '</p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= '<div class="d-grid gap-2"><button type="button" onclick="PayPM(\'CHQ\')" class="btn btn-danger btn-block">'.__( 'I order', 'doliconnect').'</button></div>';
}
$paymentmethods .= '</div></div></div>';
} 

if ( ! empty(dolikiosk()) ) {
$paymentmethods .= '<div class="accordion-item"><h2 class="accordion-header" id="flush-headingThree">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
        <i class="fas fa-money-bill-alt fa-3x fa-fw float-start" style="color:#85bb65"></i> '.__( 'Pay at front desk', 'doliconnect').'
      </button>
    </h2>
    <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body bg-light">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably havent heard of them accusamus labore sustainable VHS.</div>
    </div>
  </div>';
}
  
$paymentmethods .= '</div><div class="card-footer text-muted">';
$paymentmethods .= '<small><div class="float-start">';
$paymentmethods .= dolirefresh($request, $url, dolidelay('paymentmethods'));
$paymentmethods .= '</div><div class="float-end">';
$paymentmethods .= dolihelp('ISSUE');
$paymentmethods .= '</div></small>';
$paymentmethods .= '</div></div>';

if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .= "<script>";
$paymentmethods .= "function PayPM(pm) {
(function ($) {
$(document).ready(function(){
$('#DoliconnectLoadingModal').modal('show');
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': null,
            'paymentmethod': pm,        
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
});
})(jQuery);
}";    
        
$paymentmethods .= "function PayCardPM(pm) {
(function ($) {
$(document).ready(function(){
$('#DoliconnectLoadingModal').modal('show');";
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = '';      
}  
  stripe.confirmCardPayment(
    clientSecret,
    {
      payment_method: pm
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
$('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = result.error.message;       
}  
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': null,
            'paymentmethod': pm,        
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
}
});
});
})(jQuery);
}";
$paymentmethods .= "function PaySepaDebitPM(pm) {
(function ($) {
$(document).ready(function(){
$('#DoliconnectLoadingModal').modal('show');";
if (!empty($listpaymentmethods->stripe->client_secret)) { 
$paymentmethods .= "var clientSecret = '".$listpaymentmethods->stripe->client_secret."';";
}
$paymentmethods .= "if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = '';      
}  
  stripe.confirmSepaDebitPayment(
    clientSecret,
    {
      payment_method: pm
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
$('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = result.error.message;       
}  
    } else {
        $.ajax({
          url: '".esc_url( admin_url( 'admin-ajax.php' ) )."',
          type: 'POST',
          data: {
            'action': 'dolicart_request',
            'dolicart-nonce': '".wp_create_nonce( 'dolicart-nonce')."',
            'action_cart': 'pay_cart',
            'module': '".$module."',
            'id': '".$object->id."',
            'paymentintent': null,
            'paymentmethod': pm,        
          }
        }).done(function(response) {
$(window).scrollTop(0); 
console.log(response.data);
if (response.success) {

if (document.getElementById('nav-tab-pay')) {
document.getElementById('nav-tab-pay').innerHTML = response.data;      
}
$('#a-tab-cart').addClass('disabled');
if (document.getElementById('nav-tab-cart')) {
document.getElementById('nav-tab-cart').remove();    
}
$('#a-tab-info').addClass('disabled')
if (document.getElementById('nav-tab-info')) {
document.getElementById('nav-tab-info').remove();    
};

} else {

if (document.getElementById('DoliPaymentmethodAlert')) {
document.getElementById('DoliPaymentmethodAlert').innerHTML = response.data;      
}

}
console.log(response.data.message);
$('#DoliconnectLoadingModal').modal('hide');
});
}
});
});
})(jQuery);
}";
$paymentmethods .= "</script>";
}
return $paymentmethods;
}

function doli_gdrf_data_request_form( $args = array() ) {
global $current_user;

	wp_enqueue_script( 'gdrf-public-scripts');
 
	// Captcha
	$number_one = wp_rand( 1, 9 );
	$number_two = wp_rand( 1, 9 );

	// Default strings
	$defaults = array(
		'label_select_request' => esc_html__( 'Select your request:', 'doliconnect'),
		'label_select_export'  => esc_html__( 'Export Personal Data', 'doliconnect'),
		'label_select_remove'  => esc_html__( 'Remove Personal Data', 'doliconnect'),
		'label_input_email'    => esc_html__( 'Email', 'doliconnect'),
		'label_input_captcha'  => esc_html__( 'Human verification:', 'doliconnect'),
		'value_submit'         => esc_html__( 'Send Request', 'doliconnect'),
		'request_type'         => 'both',
	);

	// Filter string array
	$args = wp_parse_args( $args, array_merge( $defaults, apply_filters( 'privacy_data_request_form_defaults', $defaults ) ) );

	// Check is 4.9.6 Core function wp_create_user_request() exists
	if ( function_exists( 'wp_create_user_request' ) ) {

		// Display the form
		ob_start();
		?>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="gdrf-form" class="was-validated" >
			<input type="hidden" name="action" value="doli_gdrf_data_request" />
			<input type="hidden" name="gdrf_data_human_key" id="gdrf_data_human_key" value="<?php echo $number_one . '000' . $number_two; ?>" />
			<input type="hidden" name="gdrf_data_nonce" id="gdrf_data_nonce" value="<?php echo wp_create_nonce( 'gdrf_nonce'); ?>" />
    <div class="card shadow-sm"><div class="card-header"><?php _e( 'Privacy', 'doliconnect'); ?></div><ul class="list-group list-group-flush">
		<?php if ( 'export' === $args['request_type'] ) : ?>
			<input type="hidden" name="gdrf_data_type" value="export_personal_data" id="gdrf-data-type-export" />
		<?php elseif ( 'remove' === $args['request_type'] ) : ?>
			<input type="hidden" name="gdrf_data_type" value="remove_personal_data" id="gdrf-data-type-remove" />
		<?php else : ?>
<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-check'>
<input id='gdrf-data-type-export' class='form-check-input' type='radio' name='gdrf_data_type' value='export_personal_data' checked>
<label class='form-check-label w-100' for='gdrf-data-type-export'><div class='row'>
		<?php if ( !isset($args['widget']) ) : ?>
<div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-download fa-3x fa-fw'></i></center>
</div>
		<?php endif; ?>
<div class='col-auto align-middle'><h6 class='my-0'><?php _e( 'Export your data', 'doliconnect'); ?></h6><small class='text-muted'><?php _e( 'You will receive an email with a secure link to your data', 'doliconnect'); ?></small>
</div></div></label></div></li>
<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-check'>
<input id='gdrf-data-type-remove' class='form-check-input' type='radio' name='gdrf_data_type' value='remove_personal_data'>
<label class='form-check-label w-100' for='gdrf-data-type-remove'><div class='row'>
		<?php if ( !isset($args['widget']) ) : ?>
<div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-eraser fa-3x fa-fw'></i></center>
</div>
		<?php endif; ?>
<div class='col-auto align-middle'><h6 class='my-0'><?php _e( 'Erase your data', 'doliconnect'); ?></h6><small class='text-muted'><?php _e( 'You will receive an email with a secure link to confirm the deletion', 'doliconnect'); ?></small>
</div></div></label></div></li>
<?php if (!empty(get_option('doliconnectbeta'))) { ?>
<li class='list-group-item list-group-item-light list-group-item-action'><div class='form-check'>
<input id='gdrf-data-type-delete' class='form-check-input' type='radio' name='gdrf_data_type' value='delete_personal_data' disabled>
<label class='form-check-label w-100' for='gdrf-data-type-delete'><div class='row'>
		<?php if ( !isset($args['widget']) ) : ?>
<div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-trash fa-3x fa-fw'></i></center>
</div>
		<?php endif; ?>
<div class='col-auto align-middle'><h6 class='my-0'><?php _e( 'Delete your account', 'doliconnect'); ?></h6><small class='text-muted'><?php _e( 'Soon, you will be able to delete your account', 'doliconnect'); ?></small>
</div></div></label></div></li>
		<?php } endif; ?>
      <li class='list-group-item list-group-item-light list-group-item-action'>
    <?php if ( empty($current_user->user_email) ) : ?>
      <div class="form-floating mb-3">
          <input type="email" class="form-control" id="gdrf_data_email" name="gdrf_data_email" placeholder="name@example.com" value="" required>
          <label for="gdrf_data_email"><i class="fas fa-at fa-fw"></i> <?php echo esc_html( $args['label_input_email'] ); ?></label>
      </div>
		<?php else : ?>
      <div class="form-floating mb-3">
          <input type="email" class="form-control" id="gdrf_data_email" name="gdrf_data_email" placeholder="name@example.com" value="<?php echo $current_user->user_email; ?>" readonly>
          <label for="gdrf_data_email"><i class="fas fa-at fa-fw"></i> <?php echo esc_html( $args['label_input_email'] ); ?></label>
      </div>
		<?php endif; ?> 
      <div class="form-floating">
          <input type="text" class="form-control" id="gdrf_data_human" name="gdrf_data_human" placeholder="number" value="" required>
          <label for="gdrf_data_human"><i class="fas fa-shield-alt fa-fw"></i> <?php echo esc_html( $args['label_input_captcha'] ); ?> <?php echo $number_one . ' + ' . $number_two . ' = ?'; ?></label>
      </div>
      </li>
      </ul>
			<div class="card-body">
          <div class="d-grid gap-2"><button class="btn btn-secondary" id="gdrf-submit-button" type="submit"><?php _e('Validate the request', 'doliconnect'); ?></button></div>
      </div>
  </div> 
</form>
		<?php
		return ob_get_clean();
	} else {
		// Display error message
		return esc_html__( 'This plugin requires WordPress 4.9.6.', 'doliconnect');
	}

}

function doliconnect_modalform() {
global $current_user;
$year = strftime("%Y", current_time( 'timestamp', 1));

if ( (!is_user_logged_in() || !is_user_member_of_blog( $current_user->ID, get_current_blog_id())) && (get_option('doliloginmodal') == '1') ) {

doliconnect_enqueues();

do_action( 'login_head' );

print "<div class='modal fade' id='DoliconnectLogin' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' role='dialog' aria-labelledby='DoliconnectLoginTitle' aria-hidden='true' ";

print "><div class='modal-dialog modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable' role='document'><div class='modal-content'><div class='modal-header'>";

if ( empty(get_option('doliconnectrestrict')) ) {
print "<h5 class='modal-title' id='DoliconnectLoginTitle'>".__( 'Welcome', 'doliconnect')."</h5>";
} else {
print "<h5 class='modal-title' id='DoliconnectLoginTitle'>".__( 'Access restricted to users', 'doliconnect')."</h5>";
}

print '<button type="button" id="Closeloginmodal-form" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div id="loginmodal-form">';
print '<b>'.get_option('doliaccountinfo').'</b>';

if ( ! function_exists('dolikiosk') || ( function_exists('dolikiosk') && empty(dolikiosk())) ) {
print socialconnect ( get_permalink() );
}

if ( function_exists('secupress_get_module_option') && !empty(get_site_option('secupress_active_submodule_move-login')) && secupress_get_module_option('move-login_slug-login', null, 'users-login' )) {
$login_url = site_url()."/".secupress_get_module_option('move-login_slug-login', null, 'users-login' ); 
} elseif (get_site_option('doliconnect_login')) {
$login_url = site_url()."/".get_site_option('doliconnect_login');
} else {
$login_url = site_url()."/wp-login.php"; }
if ( function_exists('dolikiosk') && ! empty(dolikiosk()) ) {
$redirect_to=doliconnecturl('doliaccount');
} else {
$redirect_to=get_permalink();
}

print "<form name='loginmodal-form' action='".$login_url."' method='post' class='was-validated'>";

print dolimodalloaderscript('loginmodal-form');
if  ( defined("DOLICONNECT_DEMO") ) {
print "<p><i class='fas fa-info-circle'></i> <b>".__( 'Demo mode is activated', 'doliconnect')."</b></p>";
} 
print '<div class="form-floating mb-3"><input type="email" class="form-control" id="user_login" name="log" placeholder="name@example.com" value="';
if ( defined("DOLICONNECT_DEMO") && defined("DOLICONNECT_DEMO_EMAIL") && !empty(constant("DOLICONNECT_DEMO_EMAIL")) ) {
print constant("DOLICONNECT_DEMO_EMAIL");
}
print '" required autofocus><label for="user_login"><i class="fas fa-at fa-fw"></i> '.__( 'Email', 'doliconnect').'</label></div>';

print '<div class="form-floating mb-3"><input type="password" class="form-control" id="user_pass" name="pwd" placeholder="Password" value="';
if ( defined("DOLICONNECT_DEMO") && defined("DOLICONNECT_DEMO_PASSWORD") && !empty(constant("DOLICONNECT_DEMO_PASSWORD")) ) {
print constant("DOLICONNECT_DEMO_PASSWORD");
}
print '" required><label for="user_pass"><i class="fas fa-key fa-fw"></i> '.__( 'Password', 'doliconnect').'</label></div>';

do_action( 'login_form' );

print '<div class="form-check float-start">
  <input class="form-check-input" type="checkbox" name="rememberme" value="forever" id="rememberme" checked>
  <label class="form-check-label" for="rememberme">'.__( 'Remember me', 'doliconnect').'</label>
</div>';

print "<a class='float-end' href='".wp_lostpassword_url(get_permalink())."' role='button' title='".__( 'Forgot password?', 'doliconnect')."'><small>".__( 'Forgot password?', 'doliconnect')."</small></a>"; 

print "<input type='hidden' value='$redirect_to' name='redirect_to'></div>";

print "".doliloading('loginmodal-form');

print '</div><div id="Footerloginmodal-form" class="modal-footer">';
if ( get_site_option('doliconnect_mode') == 'one' && function_exists('switch_to_blog') ) {
switch_to_blog(1);
} 
if ((!is_multisite() && get_option( 'users_can_register' )) || ((!is_multisite() && get_option( 'dolicustsupp_can_register' )) || ((get_option( 'dolicustsupp_can_register' ) || get_option('users_can_register') == '1') && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
print "<a class='btn btn-secondary' href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect')."'><small>".__( 'Create an account', 'doliconnect')."</small></a>";
}
if (get_site_option('doliconnect_mode')=='one') {
restore_current_blog();
}
print '<div class="d-grid gap-2"><button class="btn btn-outline-secondary" type="submit" value="submit">'.__( 'Sign in', 'doliconnect').'</button></div>';
print '</form></div>';
print '</div></div></div>';

//if( !array_key_exists( 'login_footer' , $GLOBALS['wp_filter']) ) { 
do_action( 'login_footer' );
//}

}

}
add_action( 'wp_footer', 'doliconnect_modalform' );

?>
