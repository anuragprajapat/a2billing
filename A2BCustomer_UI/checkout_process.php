<?php
include ("./lib/defines.php");


getpost_ifset(array('transactionID', 'sess_id'));

write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." ----EPAYMENT TRANSACTION START----");
if ($sess_id =="")
{
	write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." ERROR NO SESSION ID PROVIDED IN RETURN URL TO PAYMENT MODULE");
    exit(gettext("No session id provided in return URL to Payment Module"));
}
if($transactionID == "")
{	
	write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." NO TRANSACTION ID PROVIDED IN REQUEST");
    exit;
}


include ("./lib/module.access.php");
include ("./lib/Form/Class.FormHandler.inc.php");
include ("./lib/epayment/classes/payment.php");
include ("./lib/epayment/classes/order.php");
include ("./lib/epayment/classes/currencies.php");
include ("./lib/epayment/includes/general.php");
include ("./lib/epayment/includes/html_output.php");
include ("./lib/epayment/includes/configure.php");
include ("./lib/epayment/includes/loadconfiguration.php");
//include("PP_header.php");


$DBHandle_max  = DbConnect();
$paymentTable = new Table();

$QUERY = "SELECT * from cc_epayment_log WHERE id = ".$transactionID;
$transaction_data = $paymentTable->SQLExec ($DBHandle_max, $QUERY);
if(!is_array($transaction_data) && count($transaction_data) == 0)
{
	write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." ERROR INVALID TRANSACTION ID PROVIDED, TRANSACTION ID =".$transactionID);
	exit();
}
else
{
	write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." EPAYMENT RESPONSE: TRANSACTIONID = ".$transactionID." FROM ".$transaction_data[0][4]."; FOR CUSTOMER ID ".$transaction_data[0][1]."; OF AMOUNT ".$transaction_data[0][2]);
}

$payment_modules = new payment($transaction_data[0][4]);
// load the before_process function from the payment modules
//$payment_modules->before_process();

$tansaction_ID = null;


$QUERY = "SELECT  username, credit, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, lastuse, activated, currency FROM cc_card WHERE id = '".$transaction_data[0][1]."'";


$numrow = 0;
$resmax = $DBHandle_max -> Execute($QUERY);
if ($resmax)
	$numrow = $resmax -> RecordCount();

if ($numrow == 0)
{
    write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." ERROR NO SUCH CUSTOMER EXISTS, CUSTOMER ID = ".$transaction_data[0][1]);
    exit(gettext("No Such Customer exists."));
}
$customer_info =$resmax -> fetchRow();


$currencyObject = new currencies();
$currCurrency = $payment_modules->get_CurrentCurrency();
$nowDate = date("y-m-d H:i:s");


$pmodule = $transaction_data[0][4];

$orderStatus = $payment_modules->get_OrderStatus();


$Query = "Insert into cc_payments ( customers_id,
                                    customers_name,
                                    customers_email_address,
                                    item_name,
                                    item_id,
                                    item_quantity,
                                    payment_method,
                                    cc_type,
                                    cc_owner,
                                    cc_number,
                                    cc_expires,
                                    orders_status,
                                    last_modified,
                                    date_purchased,
                                    orders_date_finished,
                                    orders_amount,
                                    currency,
                                    currency_value)
                                    values
                                    (
                                    '".$customer_info[0]."',
                                    '".$customer_info[3]." ".$customer_info[2]."',
                                    '".$customer_info["email"]."',
                                    'balance',
                                    '".$customer_info[0]."',
                                    1,
                                    '$pmodule',
                                    '".$_SESSION["p_cardtype"]."',
                                    '".$transaction_data[0][5]."',
                                    '".$transaction_data[0][6]."',
                                    '".$transaction_data[0][7]."',
                                     $orderStatus,
                                    '".$nowDate."',
                                    '".$nowDate."',
                                    '".$nowDate."',
                                     ".$transaction_data[0][2].",
                                     '".$currCurrency."',
                                     '".$currencyObject->get_value($currCurrency)."'
                                    )";

$result = $DBHandle_max -> Execute($Query);

$QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='payment' ";
$res = $DBHandle_max -> Execute($QUERY);

//************************UPDATE THE CREDIT IN THE CARD***********************
$id = 0;
if ($customer_info[0] > 0 && $orderStatus == 2)
{

    /* CHECK IF THE CARDNUMBER IS ON THE DATABASE */
    $instance_table_card = new Table("cc_card", "username, id");
    $FG_TABLE_CLAUSE_card = "username='".$customer_info[0]."'";
    $list_tariff_card = $instance_table_card -> Get_list ($DBHandle, $FG_TABLE_CLAUSE_card, null, null, null, null, null, null);
    //print_r($list_tariff_card);
    if ($customer_info[0] == $list_tariff_card[0][0])
    {
        $id = $list_tariff_card[0][1];
    }

}
$currencies_list = get_currencies();

if ($id > 0 ){
    //$addcredit = $_SESSION["p_amount"];
    $addcredit = $transaction_data[0][2]; 
	$instance_table = new Table("cc_card", "username, id");
	$param_update .= " credit = credit+'".convert_currency($currencies_list,$transaction_data[0][2], $currCurrency, BASE_CURRENCY)."'";
	$FG_EDITION_CLAUSE = " id='$id'";
	$instance_table -> Update_table ($DBHandle, $param_update, $FG_EDITION_CLAUSE, $func_table = null);

	$field_insert = "date, credit, card_id";
	$value_insert = "'$nowDate', 'convert_currency($currencies_list,$transaction_data[0][2], $currCurrency, BASE_CURRENCY)', '$id'";
	$instance_sub_table = new Table("cc_logrefill", $field_insert);
	$result_query = $instance_sub_table -> Add_table ($DBHandle, $value_insert, null, null);

	$field_insert = "date, payment, card_id";
	$value_insert = "'$nowDate', 'convert_currency($currencies_list,$transaction_data[0][2], $currCurrency, BASE_CURRENCY)', '$id'";
	$instance_sub_table = new Table("cc_logpayment", $field_insert);
	$result_query = $instance_sub_table -> Add_table ($DBHandle, $value_insert, null, null);


}


//*************************END UPDATE CREDIT************************************
$num = 0;
if ($res)
	$num = $res -> RecordCount();

if (!$num)
{
	write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." ERROR NO EMAIL TEMPLATE FOUND");    
	echo gettext("Error : No email Template Found");
    
}else{
	
	for($i=0;$i<$num;$i++)
	{
		$listtemplate[] = $res->fetchRow();
	}
	
	list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
	$statusmessage= "";
	switch($orderStatus)
		  {
			  case -2:
				$statusmessage = "Failed";
			  break;
			  case -1:
				$statusmessage = "Denied";
			  break;
			  case 0:
				$statusmessage = "Pending";
			  break;
			  case 1:
				$statusmessage = "In-Progress";
			  break;
			  case 2:
				$statusmessage = "Successful";
			  break;
		  }
	
	$messagetext = str_replace('$itemName', "balance", $messagetext);
	$messagetext = str_replace('$itemID', $customer_info[0], $messagetext);
	$messagetext = str_replace('$itemAmount', display_2bill($transaction_data[0][2]), $messagetext);
	$messagetext = str_replace('$paymentMethod', $pmodule, $messagetext);
	$messagetext = str_replace('$paymentStatus', $statusmessage, $messagetext);
	
	$em_headers  = "From: ".$fromname." <".$from.">\n";
	$em_headers .= "Reply-To: ".$from."\n";
	$em_headers .= "Return-Path: ".$from."\n";
	$em_headers .= "X-Priority: 3\n";
	
	mail($customer_info["email"], $subject, $messagetext, $em_headers);
}

$_SESSION["p_amount"] = null;
$_SESSION["p_cardexp"] = null;
$_SESSION["p_cardno"] = null;
$_SESSION["p_cardtype"] = null;
$_SESSION["p_module"] = null;
$_SESSION["p_module"] = null;


// load the after_process function from the payment modules
$payment_modules->after_process();
write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." EPAYMENT ORDER STATUS ID = ".$orderStatus." ".$statusmessage);
write_log(LOGFILE_EPAYMENT, basename(__FILE__).' line:'.__LINE__." ----EPAYMENT TRANSACTION END----");
Header ("Location: checkout_success.php?errcode=".$orderStatus);

?>