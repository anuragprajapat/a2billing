<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){ 
	Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}
//require (LANGUAGE_DIR.FILENAME_USERINFO);


$DBHandle_max  = DbConnect();
$QUERY = "SELECT  username, credit, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, lastuse, activated, currency FROM cc_card WHERE username = '".$_SESSION["pr_login"]."' AND uipass = '".$_SESSION["pr_password"]."'";


$numrow = 0;	
$resmax = $DBHandle_max -> Execute($QUERY);

if ($resmax)
	$numrow = $resmax -> RecordCount();
else if ($FG_DEBUG>0) {
	echo "Error: ";
	echo $DBHandle_max->Error_Msg();
	echo "<br>No user info. <br>\n";
}

if ($numrow == 0) exit();
$customer_info =$resmax -> fetchRow();

if( $customer_info [13] != "t" && $customer_info [13] != "1" ) {
	if ($FG_DEBUG>2)
		echo "customer info[13] = " .$customer_info [13] ."<br>\n";
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

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

//-->
</script>

<?php
	include("PP_header.php");
?><br>


<?php if ($A2B->config["webcustomerui"]['customerinfo']){ ?>

<table width="90%" class="tablebackgroundblue" align="center">
<tr>
<td><img src="./images/personal.png" align="left" class="kikipic"/></td>
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
			<td><img src="./images/gnome-finance.png" class="kikipic"/></td>
			<td width="50%">
			<br><font class="fontstyle_002"><?php echo gettext("CARD NUMBER");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[0]; ?></font>
			<br></br>
			</td>
			<td width="50%">
			<br><font class="fontstyle_002"><?php echo gettext("BALANCE REMAINING");?> :</font><font class="fontstyle_007"> <?php echo $credit_cur.' '.$customer_info[14]; ?> </font>
			<br></br>
			</td>
			<td valign="bottom" align="right"><img src="./images/help_index.png" class="kikipic"></td>
		</tr>
		</table>
	</td>
</tr>
</table>


<?php if ($A2B->config["epayment_method"]['enable']){ ?>

<table width="100%">
<tr>
<td valign=top align=center>
<img src="./images/paypal_logo.png" /> &nbsp;&nbsp;
<img  src="http://www.moneybookers.com/images/banners/88_en_mb.png" width=88 height=31 border=0>&nbsp;&nbsp;
<img src="./images/authorize.png" />

<table width="70%" align="center">
	<tr>
		<TD  valign="top" align="center" class="tableBodyRight" background="<?php echo Images_Path; ?>/background_cells.gif" >
			<font size="2"><?php echo gettext("Click below to buy credit : ");?> </font>
			
			<?php
			$arr_purchase_amount = split(":", EPAYMENT_PURCHASE_AMOUNT);
			if (!is_array($arr_purchase_amount)){
				$to_echo = 10;
			}else{
				$to_echo = join(" - ", $arr_purchase_amount);
			}
			echo $to_echo;
			?>
			<font size="2"><?php echo strtoupper(BASE_CURRENCY);?> </font>
			
			<form action="checkout_payment.php" method="post">
				
				<input type="submit" class="form_input_button" value="BUY NOW">
			</form>
		</TD>
	</tr>
</table>




<?php }else{ ?>
<br></br><br></br>

<?php } ?>
<?php
	include("PP_footer.php");
?>
