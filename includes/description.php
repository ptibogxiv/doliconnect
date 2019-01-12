<?php

$typeadherent = CallAPI("GET", "/memberstypes/".$post->category_id, "");
$typeadherent = json_decode($typeadherent, true);

$glyphicon = "user-plus";
if ($post->link<0){
$glyphicon = "fas fa-shopping-bag";
$description[$post->product_id] = "<a href='".doliconnecturl('dolishop')."'>$product[label]</A>";}
elseif ($post->link==0){
$glyphicon = "user-plus";
$description[$post->product_id] = "<a href='#' data-toggle='modal' data-target='#activatemember'>";
$description[$post->product_id] .= "$product[label] du $time_start au $time_end, $typeadherent[label]</a>";}
else {
$glyphicon = "link";
$description[$post->product_id] = "$product[label]";}
$description2[$post->product_id] = "$product[label]";
?>