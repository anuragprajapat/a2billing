<?php
session_name("FORGOT");
session_start();
	
	
include (dirname(__FILE__)."/lib/company_info.php");
include ("./lib/defines.php");

getpost_ifset(array('pr_email','action'));
$error = 0; //$error = 0 No Error; $error=1 No such User; $error = 2 Wrong Action
$show_message = false;
$login_message = "";

if(isset($pr_email) && isset($action))
{
    if($action == "email")
    {
		
		if (!isset($_SESSION["date_forgot"]) || (time()-$_SESSION["date_forgot"]) > 60){
			$_SESSION["date_forgot"]=time();
		}else{
			sleep(3);
			echo gettext("Please wait 1 minutes before making any other request for the forgot password!");
			exit();
		}
		
        $show_message = true;
        $DBHandle  = DbConnect();
        $QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='forgetpassword' ";
        $res = $DBHandle -> query($QUERY);
        $num = $res -> numRows();
        if (!$num) exit();
        for($i=0;$i<$num;$i++)
        {
        	$listtemplate[] = $res->fetchRow();
        }

        list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
        if ($FG_DEBUG == 1)
        {
            echo "<br><b>mailtype : </b>$mailtype</br><b>from:</b> $from</br><b>fromname :</b> $fromname</br><b>subject</b> : $subject</br><b>ContentTemplate:</b></br><pre>$messagetext</pre></br><hr>";
        }
        $QUERY = "SELECT username, lastname, firstname, email, uipass, useralias FROM cc_card WHERE email='".$pr_email."' ";

        $res = $DBHandle -> query($QUERY);

        $num = $res -> numRows();
        if (!$num)
        {
            $error = 1;
			sleep(4);
        }
        if($error == 0)
        {
            for($i=0;$i<$num;$i++)
            {
            	$list[] = $res->fetchRow();
            }
            $keepmessagetext = $messagetext;
            foreach ($list as $recordset)
            {
            	$messagetext = $keepmessagetext;
            	list($username, $lastname, $firstname, $email, $uipass, $cardalias) = $recordset;

            	if ($FG_DEBUG == 1) echo "<br># $username, $lastname, $firstname, $email, $uipass, $credit, $cardalias #</br>";

				$messagetext = str_replace('$cardalias', $cardalias, $messagetext);
            	$messagetext = str_replace('$card_gen', $username, $messagetext);
            	$messagetext = str_replace('$password', $uipass, $messagetext);
            	$em_headers  = "From: ".$fromname." <".$from.">\n";
            	$em_headers .= "Reply-To: ".$from."\n";
            	$em_headers .= "Return-Path: ".$from."\n";
            	$em_headers .= "X-Priority: 3\n";
            	mail($recordset[3], $subject, $messagetext, $em_headers);
            }
        }
    }
    else
    {
        $error = 2;
    }
}
else
{
    $error = 3;
}

switch($error)
{
    case 0:
        $login_message = gettext("Your login information email has been sent to you.");
    break;
    case 1:
        $login_message = gettext("No such login exists.");
    break;
    case 2:
        $login_message = gettext("Invalid Action.");
    break;
    case 3:
        $login_message = gettext("Pleaes provide your email address to get your login information.");
    break;
}


//include("PP_header.php");
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
		if(document.form.pr_email.value=="")
		{
			alert("You must enter an email address!");
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

<body onload="document.form.pr_email.focus()">
<br></br>
<table width="100%" height="75%">
<tr align="center" valign="middle">
<td>
	<form name="form" method="POST" action="forgotpassword.php?action=email" onsubmit="return test()">
	<input type="hidden" name="done" value="submit_log">

  	<?php if (isset($_GET["error"]) && $_GET["error"]==1) { ?>
		<font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b>AUTHENTICATION REFUSED, please check your user/password!</b>
		</font>
	<?php } ?><br><br>
    <?php if($show_message== false){ ?>
	<table style="border: 1px solid #C1C1C1">
	<tr>
		<td class="form_enter" align="center">
			<img src="images/icon_arrow_orange.gif" width="15" height="15">
			<font size="3" color="red" ><b> Forgot your password?</b></font>
		</td>
	</tr>
	<tr>
		<td style="padding: 5px, 5px, 5px, 5px" bgcolor="#EDF3FF">
			<table border="0" cellpadding="0" cellspacing="10">
			<tr align="center">
				<td rowspan="3" style="padding-left: 8px; padding-right: 8px"><img src="images/security.png"></td>
				<td></td>
				<td align="left"><font size="2" face="Arial, Helvetica, Sans-Serif"><b>Email:</b></font></td>
				<td><input class="form_enter" type="text" name="pr_email" size="32"></td>
			</tr>
			<tr align="center">
				<td></td>
				<td></td>
				<td><input type="submit" name="submit" value="SUBMIT" class="form_enter"></td>
			</tr>
			</table>
		</td>
	</tr>
    </table>

   <?php
   }
   else
   {
   ?>
			<center>
			
			<br></br><br></br>
			
			<table width="400">
			<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
			<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
			<tr>
			<td bgcolor="#EEEEEE">
			<img src="Css/kicons/khelpcenter.png"/></td>
			<td bgcolor="#EEEEEE">
			
			<b>
			<?php echo $login_message;?></b>
			
			</td></tr>
			<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
			<tr><td colspan="2" bgcolor="#DDDDDD"></td></tr>
			</table>
			
			<br></br><br></br>
			
			</center>
			   
    <?php } ?>
	</form>




<br></br><br></br>

</body>
</html>
