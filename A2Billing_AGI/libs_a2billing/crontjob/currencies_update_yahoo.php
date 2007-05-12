#!/usr/bin/php -q
<?php
include (dirname(__FILE__)."/../Class.A2Billing.php");
include (dirname(__FILE__)."/../db_php_lib/Class.Table.php");

$A2B = new A2Billing();

// SELECT THE FILES TO LOAD THE CONFIGURATION
$A2B -> load_conf($agi, DEFAULT_A2BILLING_CONFIG, 1);	


// DEFINE FOR THE DATABASE CONNECTION
define ("BASE_CURRENCY", strtoupper($A2B->config["webui"]['base_currency']));

// get in a csv file USD to EUR and USD to CAD
// http://finance.yahoo.com/d/quotes.csv?s=USDEUR=X+USDCAD=X&f=l1


$A2B -> load_conf($agi, NULL, 0, $idconfig);
$A2B -> log_file = $A2B -> config["log-files"]['cront_currencies_update'];
$A2B -> write_log("[START CURRENCY UPDATE]", 0);

if (!$A2B -> DbConnect()){
	echo "[Cannot connect to the database]\n";
	$A2B -> write_log("[Cannot connect to the database]", 0);
	exit;
}


$instance_table = new Table();
$A2B -> set_instance_table ($instance_table);

$QUERY =  "SELECT id,currency,basecurrency FROM cc_currencies ORDER BY id";
$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY);
	
$url = "http://finance.yahoo.com/d/quotes.csv?s=";

/* result[index_result][field] */

$index_base_currency = 0;

if (is_array($result)){
	$num_cur = count($result);
	$A2B -> write_log("[CURRENCIES TO UPDATE = $num_cur]", 0);
	for ($i=0;$i<$num_cur;$i++){
		
		// Finish and add termination ? 
		if ($i+1 == $num_cur) $url .= BASE_CURRENCY.$result[$i][1]."=X&f=l1";
		else $url .= BASE_CURRENCY.$result[$i][1]."=X+";
		
		// Check what is the index of BASE_CURRENCY to save it 
		if (strcasecmp(BASE_CURRENCY, $result[$i][1]) == 0) {
			$index_base_currency = $result[$i][0];
		}
	}
	
	// Create the script to get the currencies
		$outarr= array();
		$outres= -1;
		$tmpfname=tempnam("/tmp","currencies-");
		$CMD='wget ' . escapeshellarg($url) . ' -O ' . $tmpfname;

		// exec the script
		exec($CMD,$outarr,$outres);
		if ($outres!=0) {
			echo "Get currencies failed with code" . $outres . "\n";
			exit(1);
		}
	
	// get the file with the currencies to update the database
		$currencies = file($tmpfname);
	
	// update database
	foreach ($currencies as $currency){
		
		$currency = trim($currency);
		
		if (!is_numeric($currency)){ 
			continue; 
		}
		$id++;
		// if the currency is BASE_CURRENCY the set to 1
		if ($id == $index_base_currency) $currency = 1;
		
		if ($currency!=0) $currency=1/$currency;
		$QUERY="UPDATE cc_currencies set value=".$currency;
		
		if (BASE_CURRENCY != $result[$i][2]){
			$QUERY .= ",basecurrency='".BASE_CURRENCY."'";
		}
		$QUERY .= " , lastupdate = CURRENT_TIMESTAMP WHERE id =".$id;
		
			//echo $QUERY . "\n";
		$result = $A2B -> instance_table -> SQLExec ($A2B->DBHandle, $QUERY, 0);
		// echo "$QUERY \n\n"; if ($id == 5) exit;
		}
		unlink($tmpfname);
	$A2B -> write_log("[CURRENCIES UPDATED !!!]", 0);
}
?>
