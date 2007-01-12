<?php include (dirname(__FILE__)."/lib/company_info.php");


require_once("lib/languageSettings.php");

if (isset($_GET['language'])){
	// Taken from defines.php
	define ("LIBDIR", dirname(__FILE__)."/lib/");
	define ("LANGUAGE",$_GET['language']);
	SetLocalLanguage($_GET['language']);
}

?>
<html>

<head>
<link rel="shortcut icon" href="images/favicon.ico">
<link rel="icon" href="images/animated_favicon1.gif" type="image/gif">

<title>..:: <?php echo CCMAINTITLE; ?> ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="Css/menu.css" rel="stylesheet" type="text/css">


<script LANGUAGE="JavaScript">
<!--
	function test()
	{
		if(document.form.pr_login.value=="" || document.form.pr_password.value=="")
		{
			alert("You must enter an user and a password!");
			return false;
		}
		else
		{
			return true;
		}
	}
-->
</script>

<style TEXT="test/css">
<!-- 
.form_enter {
	font-family: Arial, Helvetica, Sans-Serif;
	font-size: 11px;
	font-weight: bold;
	color: #FF9900;
	border: 1px solid #C1C1C1;
}
-->
</style>
</head>

<body onload="document.form.pr_login.focus()">
<table width="100%" height="75%">
<tr align="center" valign="middle">
<td>
	<form name="form" method="POST" action="index2.php" onsubmit="return test()">
	<input type="hidden" name="done" value="submit_log">

  	<?php if (isset($_GET["error"]) && $_GET["error"]==1) { ?>
		<font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b><?= _("AUTHENTICATION REFUSED, please check your user/password!"); ?></b>
		</font>
    <?php }elseif (isset($_GET["error"]) && $_GET["error"]==2){ ?>
        <font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b><?= _("INACTIVE ACCOUNT, Please activate your account!"); ?></b>
		</font>
    <?php }elseif (isset($_GET["error"]) && $_GET["error"]==3){ ?>
        <font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b><?= _("BLOCKED ACCOUNT, Please contact your administrator!"); ?></b>
		</font>
    <?php } ?>
    <br><br>

	<table style="border: 1px solid #C1C1C1">
	<tr>
		<td class="form_enter" align="center">
			<img src="images/icon_arrow_orange.gif" width="15" height="15">
			<font size="3" color="red" ><b><?= _("AUTHENTICATION"); ?></b></font>
		</td>
	</tr>
	<tr>
		<td style="padding: 5px, 5px, 5px, 5px" bgcolor="#EDF3FF">
			<table border="0" cellpadding="0" cellspacing="10">
			<tr align="center">
				<td rowspan="3" style="padding-left: 8px; padding-right: 8px"><img src="images/security.png"></td>
				<td></td>
				<td align="left"><font size="2" face="Arial, Helvetica, Sans-Serif"><b><?= _("User:");?></b></font></td>
				<td><input class="form_enter" type="text" name="pr_login"></td>
			</tr>
			<tr align="center">
				<td></td>
				<td align="left"><font face="Arial, Helvetica, Sans-Serif" size="2"><b><?= _("Password:") ?></b></font></td>
				<td><input class="form_enter" type="password" name="pr_password"></td>
			</tr>
			<tr align="center">
				<td></td>
				<td></td>
				<td><input type="submit" name="submit" value="LOGIN" class="form_enter"></td>
			</tr>

            <tr align="center">
                <td colspan=3><?= _("Forgot your password? Click "); ?><a href="forgotpassword.php"><?= _("here") ?></a>.</td>
            </tr>

			</table>
		</td>
	</tr>
      	</table>
<?php
foreach($language_list as $lang)
	if ($lang['flag']!=null)
	echo "	<a href=\"index.php?language=" .$lang['cname'] . "\" target=\"_parent\"><img src=\"images/flags/" . $lang['flag'] . "\" border=\"0\" title=\"" . $lang['name'] ."\" alt=\"" .$lang['name']."\"></a>\n";
?>
	</form>
</td>
</tr>
</table>
</body>

</html>
