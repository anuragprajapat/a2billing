<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/smarty.php");

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}


if (!$A2B->config["webcustomerui"]['webphone']) exit();

//require (LANGUAGE_DIR.FILENAME_WEBPHONE);

$QUERY = "SELECT  activated, sip_buddy, iax_buddy, username FROM cc_card WHERE username = '".$_SESSION["pr_login"]."' AND uipass = '".$_SESSION["pr_password"]."'";

$DBHandle_max  = DbConnect();
$resmax = $DBHandle_max -> Execute($QUERY);
$numrow = 0;	
if ($resmax)
	$numrow = $resmax -> RecordCount();

if ($numrow == 0) exit();
$customer_info =$resmax -> fetchRow();

if( $customer_info [1] == "t" || $customer_info [1] == "1" ) {
	$SIPQUERY="SELECT secret FROM cc_sip_buddies WHERE username = '".$customer_info[3]."'";
	$sipresmax = $DBHandle_max -> Execute($SIPQUERY);
	if ($sipresmax){
		$sipnumrow = $sipresmax -> RecordCount();
		$sip_info =$sipresmax -> fetchRow();
	}	
}

if( $customer_info [2] == "t" || $customer_info [2] == "1" ) {
	$IAXQUERY="SELECT secret FROM cc_iax_buddies WHERE username = '".$customer_info[3]."'";
	$iaxresmax = $DBHandle_max -> Execute($IAXQUERY);
	if ($iaxremax){
		$iaxnumrow = $iaxresmax -> RecordCount();
		$iax_info =$iaxresmax -> fetchRow();
	}
}



$customer = $_SESSION["pr_login"];

$smarty->display( 'main.tpl');
	

	// #### HELP SECTION
echo '<br>'.$CC_help_webphone;

?><br>




	   <br><center>
	  <br></br>
	  <b><?php echo gettext("Account/Phone");?> :</b> <?php echo $customer_info[3]; ?>
	  </center>

	  <?php if (false){ ?>
	<table align="center" class="bgcolor_006" border="0" width="75%">
		<FORM NAME="phonesip" METHOD="POST" ACTION="jiaxclient/sipphone.php" target="_blank">
		<?php
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_server\" VALUE=\"".$A2B->config['webcustomerui']['webphoneserver']."\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_user\" VALUE=\"".$customer_info[3]."\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_secret\" VALUE=\"".$sip_info[0]."\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_number\" VALUE=\"\">\n";
		?>
        <tbody><tr  class="bgcolor_006">
		<td align="center" valign="bottom">
				<img src="Css/kicons/stock_cell-phone.png" class="kikipic"/>
				<br><b><?php echo gettext("SIP WEB-PHONE")?></b>
					</br></br>
			</td>
			<td align="center" valign="middle">
					<?php
						if( $customer_info [1] != "t" && $customer_info [1] != "1" ) {
							echo gettext("&nbsp;NO SIP ACCOUNT&nbsp;");
						}else{ ?>
						<input class="form_input_button"  value="[ <?php echo gettext("Click to start SIP WebPhone")?>]" type="submit">
					<?php } ?>
			</td>
        </tr>

        </tbody>
		</FORM>
	</table>
	<?php } ?>
	<br>
	<table align="center" class="bgcolor_006" border="0" width="75%">
		<FORM NAME="phoneiax" METHOD="POST" ACTION="jiaxclient/iaxphone.php" target="_blank">
		<?php
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_server\" VALUE=\"".$A2B->config['webcustomerui']['webphoneserver']."\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_user\" VALUE=\"".$customer_info[3]."\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_secret\" VALUE=\"".$iax_info[0]."\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"webphone_number\" VALUE=\"\">\n";
		?>
        <tbody><tr class="bgcolor_006">
		<td align="center" valign="bottom">
				<img src="<?php echo KICON_PATH ?>/stock_cell-phone.png" class="kikipic"/><br>
				<b><?php echo gettext("IAX WEB-PHONE")?></b>
					</br></br>
			</td>
			<td align="center" valign="middle">
					<?php
						if( $customer_info [2] != "t" && $customer_info [2] != "1" ) {
							echo gettext("NO IAX ACCOUNT");
						}else{ ?>
						<input class="form_input_button" value="[ <?php echo gettext("START IAX PHONE")?>]" type="submit">
					<?php } ?>
			</td>
        </tr>
        </tbody>
		</FORM>
	</table>
	<br><br><br><br>


<?php
$smarty->display( 'footer.tpl');
?>
