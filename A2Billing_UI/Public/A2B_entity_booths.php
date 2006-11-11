<?php
include ("../lib/defines.php");
require("../lib/module.access.php");
require("../lib/Form/Class.FormHandler.inc.php");


if (! has_rights (ACX_AGENTS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

include("PP_header.php");

require("./form_data/FG_var_booth.inc");

$HD_Form -> init();

if ($id!="" || !is_null($id)){	
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}



// Fill booth action must be carried out before this, because this queries for the empty ones.

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;

$list = $HD_Form -> perform_action($form_action);

$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
if (!($popup_select>=1)) include("PP_footer.php");
?>