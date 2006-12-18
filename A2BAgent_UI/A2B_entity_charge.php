<?php
if ($wantinclude!=1){
	require("./lib/defines.php");
	require("./lib/module.access.php");
	require("./lib/Form/Class.FormHandler.inc.php");
}

if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}


include ("./FG_var_charge.inc");

$HD_Form_c -> init();


// To fix internal links due $_SERVER["PHP_SELF"] from parent include that fakes them
if ($wantinclude==1){
	$HD_Form_c -> FG_EDITION_LINK = "A2B_entity_charge.php?form_action=ask-edit&id=";
	$HD_Form_c -> FG_DELETION_LINK  = "A2B_entity_charge.php?form_action=ask-delete&id=";
}


if ($id!="" || !is_null($id)){	
	$HD_Form_c -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form_c -> FG_EDITION_CLAUSE);	
}


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form_c -> perform_action($form_action);


if ($wantinclude!=1){
	// #### HEADER SECTION
	include("PP_header.php");

	// #### HELP SECTION
	if ($form_action=='list') echo '<br><br>'.$CC_help_list_charge;
	else echo '<br><br>'.$CC_help_edit_charge;
}


// #### TOP SECTION PAGE
$HD_Form_c -> create_toppage ($form_action);

if ($form_action == 'list'){ ?>
<p class='create-btn'><a href='A2B_entity_charge.php?form_action=ask-add&popup_select=<?= $popup_select?>&booth=<?= $booth?>'><?= _("Charge a customer");?></a>
</p>
<?php }

// #### CREATE FORM OR LIST
//$HD_Form_c -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form_c -> create_form ($form_action, $list, $id=null) ;

if ($wantinclude!=1){
	// #### FOOTER SECTION
	include("PP_footer.php");
}	

?>
