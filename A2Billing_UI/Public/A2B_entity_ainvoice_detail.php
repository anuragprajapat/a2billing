<?php
$menu_section='menu_invoicing';
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");

if (! has_rights (ACX_INVOICING)){ 
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");
	die();
}

getpost_ifset(array('id'));

$DBHandle = DbConnect();
$payment_sl = array();
$payment_sl["0"] = array( gettext("UNPAID"), "0");
$payment_sl["1"] = array( gettext("SENT-UNPAID"), "1");
$payment_sl["2"] = array( gettext("SENT-PAID"),  "2");
$payment_sl["3"] = array( gettext("PAID"),  "3");

$FG_DEBUG = 4;
$show_actions = true;
$show_history  = true;
$show_calls = true;

include('PP_header.php');

$QUERY = str_dbparams($DBHandle,'SELECT invoicecreated_date, cover_startdate,cover_enddate, '.
	'orderref, amount, tax, total,invoicetype, filename, payment_status, '.
	'name, email, location, vat '.
	' FROM cc_invoices, cc_agent WHERE cc_invoices.agentid = cc_agent.id AND cc_invoices.id = %#1;',
	array($id));
$res = $DBHandle->Execute($QUERY);

if (!$res){
	if ($FG_DEBUG >0){
		echo "Query failed: " . htmlspecialchars($QUERY). "<br>\n";
		echo $DBHandle->ErrorMsg();
		echo "<br><br>\n";
	}
	exit(_("Cannot locate invoice."));
}
if ($res->RecordCount()<1)
	exit(_("Cannot locate invoice."));

$info_invoice = $res->fetchRow();

if ($FG_DEBUG>3)
	print_r($info_invoice);
?>
<div>
<table width="60%" cellpadding="0" cellspacing="0">
	<tr>
	<td width="35%">&nbsp; </td>
	<td width="65%">&nbsp; </td>
	</tr>
	<tr>
	<td><?php echo gettext("Name");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['name']) ?></td>
	<tr>
	<td><?php echo gettext("Address");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['location']) ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Invoice date");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['invoicecreated_date']); ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Period from");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['cover_startdate']); ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Period to");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['cover_enddate']); ?></td>
	</tr>

	<tr>
	<td><?php echo gettext("Status");?>&nbsp;: </td>
	<td><?= $payment_sl[$info_invoice['payment_status']][0] ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Order");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['orderref']) ?></td>
	</tr>
</table>

<?php
if ($show_actions){
	if ($info_invoice['payment_status']< 2)
		echo "Edit!<br>\n";
	if (($info_invoice['payment_status']== 0)||($info_invoice['payment_status'])){
		echo "Pay!<br>\n";
	}
	if ($info_invoice['payment_status']== 0){
		echo "Send!<br>\n";
	}
} ?>

<?php
if ($show_history){
	$QUERY = str_dbparams($DBHandle,'SELECT invoicesent_date, invoicestatus FROM cc_invoice_history' .
		' WHERE invoiceid = %#1 ORDER BY invoicesent_date DESC;', array($id));
	$res = $DBHandle->Execute($QUERY);

	if (!$res){
		if ($FG_DEBUG >0){
			echo "Query failed: " . htmlspecialchars($QUERY). "<br>\n";
			echo $DBHandle->ErrorMsg();
			echo "<br><br>\n";
		}
	}else {
		?><div><?= _("History") ?> </div>
		<table>
<?php
		while ($row = $res->fetchRow()){
		?><tr><td><?= _("At:")?> <?= $row['invoicesent_date'] ?></td>
		<td><?= $payment_sl[$row['invoicestatus']][0] ?> </td></tr>
<?php
		}
		?></table>
<?php
	}
}
?>

<table width="60%" cellpadding="0" cellspacing="0">
	<tr>
	<td width="35%">&nbsp; </td>
	<td width="65%">&nbsp; </td>
	</tr>
	<tr>
	<td><?php echo gettext("Amount");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['amount']) ?></td>
	<tr>
	<td><?= str_params(gettext("VAT (%1)%%"), array($info_invoice['vat']),1) ?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['tax']) ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Total");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['total']); ?></td>
	</tr>
</table>

</div>

<?
include('PP_footer.php');
?>