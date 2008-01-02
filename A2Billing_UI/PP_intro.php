<?php
require_once("lib/common/Class.ElemBase.inc.php");

require_once ("lib/defines.php");
require_once ("lib/module.access.php");
include_once (DIR_COMMON."languageSettings.inc.php");

class IntroPage extends ElemBase{
	function Render(){
	?>
<table align="center" width="90%" bgcolor="white" cellpadding="5" cellspacing="5" style="border-bottom: medium dotted #AA0000">
	<tr>
		<td width="340">
			<img src="./Images/logoA2B-white-300.png">
			<center>A2Billing is licensed under GPL.</center>
			<br><br>
		</td>
		<td align="left"> <?php  echo ''; ?>
		For information and documentation on A2Billing, <br> please visit <a href="http://www.asterisk2billing.org" target="_blank">www.asterisk2billing.org</a><br><br>
		
		For Commercial Installations, Hosted Systems, Customisation and Commercial support, please email <a href="mailto:info@asterisk2billing.org">info@asterisk2billing.org</a><br><br>
		
		<center>
		<a href="http://www.call-labs.com/" target="_blank"><img src="./Images/call-labs-com.png" alt="call-labs"/></a>
		</center>
		For VoIP termination, please visit <a href="http://www.call-labs.com" target="_blank">http://www.call-labs.com</a> <br>
		Profits from Call-Labs are used to support the A2Billing project.<br><br>
		
	
		
		</td>
	</tr>
</table>

	<br>
	
<table align=center width="90%" bgcolor="white" cellpadding="5" cellspacing="5">
	<tr>
		<td align="center"> 
		
		<center>
			<?php echo gettext("If you find A2Billing useful, please donate to the A2Billing project by clicking the Donate button :");?>  
			
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="blank">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="info@areski.net">
				<input type="hidden" name="item_name" value="Asterisk2Billing">
				<input type="hidden" name="buyer_credit_promo_code" value="">
				<input type="hidden" name="buyer_credit_product_category" value="">
				<input type="hidden" name="buyer_credit_shipping_method" value="">
				<input type="hidden" name="buyer_credit_user_address_change" value="">
				<input type="hidden" name="no_shipping" value="0">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="tax" value="0">
				<input type="hidden" name="lc" value="US">
				<input type="hidden" name="country" value="USA">
				<input type="hidden" name="bn" value="PP-DonationsBF">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make Donation with PayPal - it's fast, free and secure!">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</center>
			 <br>
		<span class="liens">
	           BY USING THIS SOFTWARE, YOU ASSUME ALL RISKS OF USE AND NO WARRANTIES EXPRESS OR IMPLIED  <BR>
			   ARE PROVIDED WITH THIS SOFTWARE INCLUDING FITNESS FOR A PARTICULAR PURPOSE AND MERCHANTABILITY.
		</span> 		</td>
	</tr>
	<tr><td>
	<?php
	global $language_list;
	if (isset($language_list))
foreach($language_list as $lang)
	if ($lang['flag']!=null)
	echo "	<a href=\"".$_SERVER['PHP_SELF']."?language=" .$lang['cname'] . "\" target=\"_parent\"><img src=\"Images/flags/" . $lang['flag'] . "\" border=\"0\" title=\"" . $lang['name'] ."\" alt=\"" .$lang['name']."\"></a>\n";
?>
	</td></tr>
</table>
	<?php } //Render()
};
	
$PAGE_ELEMS=array();
$PAGE_ELEMS[]= new IntroPage();

require("PP_page.inc.php");
?>
