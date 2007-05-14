<?php
$menu_section=13;
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_prefix.inc");


if (! has_rights (ACX_RATECARD)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();

}

/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


$HD_Form -> init();


if ($id!="" || !is_null($id)){
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}

if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;



$list = $HD_Form -> perform_action($form_action);

	if ($popup_select){
?>
<SCRIPT LANGUAGE="javascript">
<!-- Begin
function sendValue(selvalue){
	window.opener.document.<?php echo $popup_formname ?>.<?php echo $popup_fieldname ?>.value = selvalue;
	window.close();
}
// End -->
</script>
<?php
	}

// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
if (!($popup_select==1) && ($form_action=='list')) echo $CC_help_list_prefix;
	elseif (!($popup_select==1)) echo $CC_help_edit_prefix;


// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null) ;

// #### FOOTER SECTION
if (!($popup_select==1)) include("PP_footer.php");




?>