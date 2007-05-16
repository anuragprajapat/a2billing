<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/smarty.php");


if (! has_rights (ACX_ACCESS)){ 
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");	   
	die();	   
}


//require (LANGUAGE_DIR.FILENAME_USERINFO);
$QUERY = "SELECT  username, credit, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, lastuse, activated, currency FROM cc_card WHERE username = '".$_SESSION["pr_login"]."' AND uipass = '".$_SESSION["pr_password"]."'";

$DBHandle_max  = DbConnect();
$numrow = 0;	
$resmax = $DBHandle_max -> Execute($QUERY);
if ($resmax)
	$numrow = $resmax -> RecordCount();

if ($numrow == 0) exit();


$customer_info =$resmax -> fetchRow();

if( $customer_info [13] != "t" && $customer_info [13] != "1" ) {
	 exit();
}

$customer = $_SESSION["pr_login"];

getpost_ifset(array('posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'dsttype', 'sourcetype', 'clidtype', 'channel', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens', 'dst', 'src', 'clid'));

$currencies_list = get_currencies();

if (!isset($currencies_list[strtoupper($customer_info [14])][2]) || !is_numeric($currencies_list[strtoupper($customer_info [14])][2])) $mycur = 1;
else $mycur = $currencies_list[strtoupper($customer_info [14])][2];
$credit_cur = $customer_info[1] / $mycur;
$credit_cur = round($credit_cur,3);
?>



<?php

	$smarty->display( 'main.tpl');
	
?>


<?php if ($A2B->config["webcustomerui"]['customerinfo']){ ?>

<table width="90%" class="tablebackgroundblue" align="center">
<tr>
	<td><img src="<?php echo KICON_PATH ?>/personal.gif" align="left" class="kikipic"/></td>
	<td width="50%"><font class="fontstyle_002">
	<?php echo gettext("LAST NAME");?> :</font>  <font class="fontstyle_007"><?php echo $customer_info[2]; ?></font>
	<br/><font class="fontstyle_002"><?php echo gettext("FIRST NAME");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[3]; ?></font>
	<br/><font class="fontstyle_002"><?php echo gettext("EMAIL");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[10]; ?></font> 
	<br/><font class="fontstyle_002"><?php echo gettext("PHONE");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[9]; ?></font> 
	<br/><font class="fontstyle_002"><?php echo gettext("FAX");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[11]; ?></font> 
	</td>
	<td width="50%">
	<font class="fontstyle_002"><?php echo gettext("ADDRESS");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[4]; ?></font> 
	<br/><font class="fontstyle_002"><?php echo gettext("ZIP CODE");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[8]; ?></font> 
	<br/><font class="fontstyle_002"><?php echo gettext("CITY");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[5]; ?></font> 
	<br/><font class="fontstyle_002"><?php echo gettext("STATE");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[6]; ?></font> 
	<br/><font class="fontstyle_002"><?php echo gettext("COUNTRY");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[7]; ?></font> 
	</td>
</tr>
<?php if ($A2B->config["webcustomerui"]['personalinfo']){ ?>
<tr>
	<td></td>
	<td></td>
	<td align="right">
	<a href="A2B_entity_card.php?atmenu=password&form_action=ask-edit&stitle=Personal+Information"><span class="cssbutton"><font color="red"><?php echo gettext("EDIT PERSONAL INFORMATION");?></font></span></a>
	</td>
</tr>
<?php } ?>
</table>

<?php } ?>
<br>
<table width="100%" align="center" >
<tr>
	<td align="center">
		<table width="80%" align="center" class="tablebackgroundcamel">
		<tr>
			<td><img src="<?php echo KICON_PATH ?>/gnome-finance.gif" class="kikipic"/></td>
			<td width="50%">
			<br><font class="fontstyle_002"><?php echo gettext("CARD NUMBER");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[0]; ?></font>
			<br></br>
			</td>
			<td width="50%">
			<br><font class="fontstyle_002"><?php echo gettext("BALANCE REMAINING");?> :</font><font class="fontstyle_007"> <?php echo $credit_cur.' '.$customer_info[14]; ?> </font>
			<br></br>
			</td>
			<td valign="bottom" align="right"><img src="<?php echo KICON_PATH ?>/help_index.gif" class="kikipic"></td>
		</tr>
		</table>
	</td>
</tr>
</table>


<?php if ($A2B->config["epayment_method"]['enable']){ ?>

<table width="100%">
<tr>
<td valign=top align=center>

<?php 
echo $PAYMENT_METHOD;
?>
</td>
</tr>
<tr>
<td>
<div id="div2200" style="display:visible;">
<div id="kiblue">
<div class="w4">
	<div class="w2">
<table width="80%" align="center">
	<tr>
		<td align="center"> <br>
			<font size="1"><?php echo gettext("Click below to buy")."<br>".gettext("credit");?> </font>
			<form action="checkout_payment.php" method="post">
				
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit"
				alt='<?php  gettext("Make payments - it's fast, free and secure!");?>'>
			</form>
		</td>
	</tr>
</table>

</div></div></div>
</div>
</td>
</tr>
</table>

<table width="70%" align="center">
	<tr>
		<TD valign="top" align="center" class="tableBodyRight" background="'.Images_Path.'/background_cells.gif" >
			<?php echo gettext('The fee from $5 is $0.45, from $10 is $0.59, from $20 is $0.88, from $40 is $1.46.').' <br><font class="fontstyle_002">'.gettext('Paypal Fee Calculator').'</font>';?> <a target="_blank" href="http://www.ppcalc.com/">http://www.ppcalc.com/</a></b>
		</td>
	</tr>
</table>


<?php }else{ ?>
<br></br><br></br>

<?php } ?>
<?php
	$smarty->display( 'footer.tpl');
?>
