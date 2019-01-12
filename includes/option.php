<?php
$catoption = CallAPI("GET", "/doliconnect/constante/ADHERENT_MEMBER_CATEGORY", "");
$catoption = json_decode($catoption, true);
if ($catoption!=NULL && $posta->link>=0) {
$listoptions="&category=".$catoption;
} else{
$listoptions="&category=x";
}
?>