<?php
require("./lib/defines.php");
require("a2blib/Misc.inc.php");

$err_type = getpost_single('err_type');
$c = getpost_single('c');

if (!isset($err_type)) {
	$err_type = 0;
}

//Error Type == 0 Mean Critical Error dont need to show left menu.
//Error Type == 1 Mean User generated error.and it will show menu to him too.
if($err_type == 0) {
	$popup_select=1;
} else {
	require("./lib/module.access.php");
}

if (!isset($c))	$c="0";


$error["0"] 		= gettext("ERROR : ACCESS REFUSED");
$error["syst"] 		= gettext("Sorry a problem occur on our system, please try later!");
$error["errorpage"] 	= gettext("There is an error on this page!");
$error["accessdenied"] 	= gettext("Sorry, you don t have access to this page !");
$error["construction"] 	= gettext("Sorry, this page is in construction !");
$error["ERR-0001"] 	= gettext("Invalid User Id !");
$error["ERR-0002"] 	= gettext("No such card number found. Please check your card number!");

?>
<html><head>
<title>..:: <?= _("Error") ?> ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<br></br><br></br>
<table width="460" border="2" align="center" cellpadding="1" cellspacing="2" bordercolor="#eeeeff" bgcolor="#FFFFFF">
	<tr  class="pp_error_maintable_tr1"> 
		
		<td> 					
			<div align="center"><b><font size="3"><?php echo gettext("Error Page");?></font></b></div>
		</td>
	</tr>				 
	<tr> 
	<td align="center" colspan=2> 
		<table width="100%" border="0" cellpadding="5" cellspacing="5">		  
		<tr> 
			<td align="center"><br/>
						<img src="./Images/kicons/messagebox_critical.png"> <img src="./Images/kicons/messagebox_critical.png"> <img src="./Images/kicons/system-config-rootpassword.png"> <img src="./Images/kicons/messagebox_critical.png"> <img src="./Images/kicons/messagebox_critical.png">
				<br/>
				<b><font size="3"><?php echo $error[$c]?></font></b>
				<br/><br/>
			</td>
		</tr>
		<tr><td>
			<a href="index.php"><?= _("Click here to login again.") ?></a>
		<td></tr>
		</table>			
	</td>
	</tr>
</table>
<br/><br/>


</body>
</html>
