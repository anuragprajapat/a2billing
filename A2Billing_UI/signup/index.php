<?php
session_name("UISIGNUP");
session_start();

include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_index.inc");



if ($_GET["dotest"]){
	$_POST["lastname"] = $_POST["firstname"] = $_POST["address"] = $_POST["city"] = $_POST["state"] = $_POST["country"] = 'SIGN-'.MDP_STRING(5).'-'.MDP_NUMERIC(3);
	$_POST["email"] = MDP_STRING(10).'@sign-up.com';
	$_POST["zipcode"] = $_POST["phone"] = '12345667789';
}

/*
if (! has_rights (ACX_CUSTOMER)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}
*/


/***********************************************************************************/
if (!$A2B->config["signup"]['enable_signup']) exit;

$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();

if ($id!="" || !is_null($id)){
	$HD_Form -> FG_EDITION_CLAUSE = str_replace("%id", "$id", $HD_Form -> FG_EDITION_CLAUSE);
}

if (!isset($form_action))  $form_action="ask-add"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);



// #### HEADER SECTION
include("PP_header.php");




// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$HD_Form -> create_form ($form_action, $list, $id=null);

if($form_action == "add")
{

    unset($_SESSION["cardnumber_signup"]);
    $_SESSION["cardnumber_signup"] = $maxi;	
    Header ("Location: signup_confirmation.php");
}

// #### FOOTER SECTION
// include("PP_footer.php");
?>
