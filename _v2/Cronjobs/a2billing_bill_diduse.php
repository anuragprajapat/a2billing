#!/usr/bin/php -q
<?php 
/***************************************************************************
 *            a2billing_bill_diduse.php
 *
 *  Usage : this script will browse all the DID that are reserve and check if the customer need to pay for it
 *	bill them or warn them per email to know if they want to pay in order to keep their DIDs 
 *
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
	crontab -e
	0 2 * * * php /var/lib/asterisk/agi-bin/libs_a2billing/crontjob/a2billing_bill_diduse.php
	
	field	 allowed values
	-----	 --------------
	minute	 		0-59
	hour		 	0-23
	day of month	1-31
	month	 		1-12 (or names, see below)
	day of week	 	0-7 (0 or 7 is Sun, or use names)
	
****************************************************************************/
	
set_time_limit(0);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

include_once (dirname(__FILE__)."/lib/Class.Table.php");
include (dirname(__FILE__)."/lib/Class.A2Billing.php");
include (dirname(__FILE__)."/lib/Misc.php");

$verbose_level=0;

$groupcard=5000;


if ($A2B->config["database"]['dbtype'] == "postgres"){
	$UNIX_TIMESTAMP = "date_part('epoch',";
}else{
	$UNIX_TIMESTAMP = "UNIX_TIMESTAMP(";
}

$A2B = new A2Billing();
$A2B -> load_conf($agi, NULL, 0, $idconfig);


write_log(LOGFILE_CRONT_BILL_DIDUSE, basename(__FILE__).' line:'.__LINE__."[#### BATCH DIDUSE BEGIN ####]");

if (!$A2B -> DbConnect()){				
			echo "[Cannot connect to the database]\n";
			write_log(LOGFILE_CRONT_BILL_DIDUSE, basename(__FILE__).' line:'.__LINE__."[Cannot connect to the database]");
			exit;						
}
//$A2B -> DBHandle
$instance_table = new Table();

// CHECK THE CARD WITH DID'S
$QUERY = "SELECT id_did, reservationdate, month_payed, fixrate, cc_card.id, credit, email, did FROM (cc_did_use INNER JOIN cc_card on cc_card.id=id_cc_card) INNER JOIN cc_did ON (id_did=cc_did.id) WHERE ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') AND cc_did_use.activated=1";

if ($verbose_level>=1) echo "==> SELECT CARD WIHT DID'S QUERY : $QUERY\n";
$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY);

if ($verbose_level>=1) print_r ($result);

if( !is_array($result)) {
	if ($verbose_level>=1) echo "[No DID in use to run the DIDBilling recurring service]\n";
	write_log(LOGFILE_CRONT_BILL_DIDUSE, basename(__FILE__).' line:'.__LINE__."[No DID in use to run the DIDBilling recurring service]");
	exit();
}

$oneday = 60*60*24;
// count day that user have to recharge his account
$daytopay = $A2B->config['global']['didbilling_daytopay'];
if ($verbose_level>=1) echo "daytopay=$daytopay \n";

// BROWSE THROUGH THE CARD TO APPLY THE DID USAGE
foreach ($result as $mydids){

	// mail variable for user notification
	$user_mail_adrr = '';
	$mail_user = false;
	$mail_user_content = '';
	
	if ($verbose_level>=1) print_r ($mydids);
	if ($verbose_level>=1) echo "------>>>  ID DID = ".$mydids[0]." - MONTHLY RATE = ".$mydids[3]."ID CARD = ".$mydids[4]." -BALANCE =".$mycard[5]." \n";	
	$day_remaining = 0;
	$timestamp_datetopay = mktime(date('H',(strtotime($mydids[1]))-(intval($daytopay) * $oneday)),
									date("i",(strtotime($mydids[1]))-(intval($daytopay) * $oneday)),
									date("s",(strtotime($mydids[1]))-(intval($daytopay) * $oneday)),
									date("m",(strtotime($mydids[1]))-(intval($daytopay) * $oneday))+$mydids[2],
									date("d",(strtotime($mydids[1]))-(intval($daytopay) * $oneday)),
									date("Y",(strtotime($mydids[1]))-(intval($daytopay) * $oneday)));
	
	$day_remaining = time() - $timestamp_datetopay;
	if ($verbose_level>=1) echo "Time now :".time()." - timestamp_datetopay=$timestamp_datetopay\n";
	if ($verbose_level>=1) echo "day_remaining=$day_remaining <=".(intval($daytopay) * $oneday)."\n";
	if ($day_remaining >= 0)
	{
		if ($day_remaining<=(intval($daytopay) * $oneday))
		{
			// THE USER HAVE TO PAY FOR HIS DID NOW
			
			if ($mydids[5] >= $mydids[3])
			{
				// USER HAVE ENOUGH CREDIT TO PAY FOR THE DID 
				$QUERY = "UPDATE cc_card SET credit=credit-'".$mydids[3]."' WHERE id=".$mydids[4];	
				$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
				if ($verbose_level>=1) echo "==> UPDATE CARD QUERY: 	$QUERY\n";
				
				$QUERY = "UPDATE cc_did_use set month_payed = month_payed+1 WHERE id_did = '".$mydids[0].
						"' AND activated = 1 AND ( releasedate IS NULL OR releasedate < '1984-01-01 00:00:00') " ;
				if ($verbose_level>=1) echo "==> UPDATE DID USE QUERY: 	$QUERY\n";
				$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
				
				$QUERY = "INSERT INTO cc_charge (id_cc_card, amount, chargetype, id_cc_did, currency) VALUES ('".$mydids[4]."', '".$mydids[3]."', '2','".$mydids[0]."', '".strtoupper($A2B->config['global']['base_currency'])."')";
				if ($verbose_level>=1) echo "==> INSERT CHARGE QUERY: 	$QUERY\n";
				$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
				
				$mail_user_content.="BALANCE REMAINING ".$mydids[5]-$mydids[3]."\n\n";
				$mail_user_content.="An automatic taking away of :".$mydids[3]." has been carry out of your account to pay your DID (".$mydids[7].")\n\n";	
				$mail_user_content.="Monthly cost for DID :".$mydids[0]."\n\n";
				$mail_user = true;
				$mail_user_subject="DID notification - (".$mydids[7].")";
			} else {
				// USER DONT HAVE ENOUGH CREDIT TO PAY FOR THE DID - WE WILL WARN HIM
				$mail_user_content.="BALANCE REMAINING ".$mydids[5]."\n\n";
				$mail_user_content.="Your credit is not enough to pay your DID number (".$mydids[7]."), the monthly cost is :".$mydids[0]."\n\n";
				$mail_user_content.="You have ".date ("d",$day_remaining)." days to recharge your card or the DID will be automatically released \n\n";
				$mail_user = true;
				$mail_user_subject="DID notification - (".$mydids[7].")";
			}
		} else {
			// RELEASE THE DID 
			$QUERY = "UPDATE cc_did set iduser = 0, reserved = 0 WHERE id='".$mydids[0]."'" ;
			if ($verbose_level>=1) echo "==> UPDATE DID QUERY: 	$QUERY\n";
			$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
			
			$QUERY = "UPDATE cc_did_use set releasedate = now() WHERE id_did = '".$mydids[0]."' and activated = 1" ;
			$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
			if ($verbose_level>=1) echo "==> UPDATE DID USE QUERY: 	$QUERY\n";
			
			$QUERY = "INSERT INTO cc_did_use (activated, id_did) VALUES ('0','".$mydids[0]."')";
			$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
			if ($verbose_level>=1) echo "==> INSERT NEW DID USE QUERY: 	$QUERY\n";
			
			$QUERY = "DELETE FROM cc_did_destination WHERE id_cc_did =".$mydids[0];
			$result = $instance_table -> SQLExec ($A2B -> DBHandle, $QUERY, 0);
			if ($verbose_level>=1) echo "==> DELETEDID did_destination QUERY: 	$QUERY\n";
			
			$mail_user_content.="The DID ".$mydids[7]." has been automatically released!\n\n";
			$mail_user=true;
			$mail_user_subject="DID Released";
		}
	}
	$user_mail_adrr=$mydids[6];
	if ($mail_user && strlen($user_mail_adrr)>5) mail($user_mail_adrr, $mail_user_subject, $mail_content);
}
write_log(LOGFILE_CRONT_BILL_DIDUSE, basename(__FILE__).' line:'.__LINE__."[Service DIDUSE finish]");

if ($verbose_level>=1) echo "#### END RECURRING SERVICES \n";

write_log(LOGFILE_CRONT_BILL_DIDUSE, basename(__FILE__).' line:'.__LINE__."[#### BATCH DIDUSE  PROCESS END ####]");
	
?>
