<?php
	include (dirname(__FILE__)."/Class.A2Billing.php");
	include (dirname(__FILE__)."/Class.Table.php");
//	require_once('DB.php'); // PEAR
	require_once('adodb/adodb.inc.php'); // AdoDB

	$A2B = new A2Billing();
	
	// SELECT THE FILES TO LOAD THE CONFIGURATION
	$A2B -> load_conf($agi, AST_CONFIG_DIR."a2billing.conf", 1);
	

	// DEFINE FOR THE DATABASE CONNECTION
	define ("HOST", isset($A2B->config["database"]['hostname'])?$A2B->config["database"]['hostname']:null);
	define ("PORT", isset($A2B->config["database"]['port'])?$A2B->config["database"]['port']:null);
	define ("USER", isset($A2B->config["database"]['user'])?$A2B->config["database"]['user']:null);
	define ("PASS", isset($A2B->config["database"]['password'])?$A2B->config["database"]['password']:null);
	define ("DBNAME", isset($A2B->config["database"]['dbname'])?$A2B->config["database"]['dbname']:null);
	define ("DB_TYPE", isset($A2B->config["database"]['dbtype'])?$A2B->config["database"]['dbtype']:null); 	


    // For ePayment Modules
    define('PULL_DOWN_DEFAULT', 'Please Select');
    define('DIR_WS_IMAGES', 'images/');
    define('TYPE_BELOW', 'Type Below');
    define('TEXT_CCVAL_ERROR_INVALID_DATE', 'The expiry date entered for the credit card is invalid.<br>Please check the date and try again.');
    define('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'The credit card number entered is invalid.<br>Please check the number and try again.');
    define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'The first four digits of the number entered are: %s<br>If that number is correct, we do not accept that type of credit card.<br>If it is wrong, please try again.');

    // javascript messages
    define('JS_ERROR', 'Errors have occured during the process of your form.\n\nPlease make the following corrections:\n\n');
    define('JS_REVIEW_TEXT', '* The \'Review Text\' must have at least ' . REVIEW_TEXT_MIN_LENGTH . ' characters.\n');
    define('JS_REVIEW_RATING', '* You must rate the product for your review.\n');
    define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Please select a payment method for your order.\n');
    define('JS_ERROR_SUBMITTED', 'This form has already been submitted. Please press Ok and wait for this process to be completed.');
    define('ERROR_NO_PAYMENT_MODULE_SELECTED', 'Please select a payment method for your order.');
    //CC
    define('MODULE_PAYMENT_CC_TEXT_TITLE', 'Credit Card');
    define('MODULE_PAYMENT_CC_TEXT_DESCRIPTION', 'Credit Card Test Info:<br><br>CC#: 4111111111111111<br>Expiry: Any');
    define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
    define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
    define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
    define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
    define('MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
    define('MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
    define('MODULE_PAYMENT_CC_TEXT_ERROR', 'Credit Card Error!');
    //IPAY
    define('MODULE_PAYMENT_IPAYMENT_TEXT_TITLE', 'iPayment');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_DESCRIPTION', 'Credit Card Test Info:<br><br>CC#: 4111111111111111<br>Expiry: Any');
    define('IPAYMENT_ERROR_HEADING', 'There has been an error processing your credit card');
    define('IPAYMENT_ERROR_MESSAGE', 'Please check your credit card details!');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER', 'Credit Card Checknumber:');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION', '(located at the back of the credit card)');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
    define('MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
	
	
	// WEB DEFINE FROM THE A2BILLING.CONF FILE
	define ("EMAIL_ADMIN", isset($A2B->config["webui"]['email_admin'])?$A2B->config["webui"]['email_admin']:null);
	define ("LEN_CARDNUMBER", isset($A2B->config["webui"]['len_cardnumber'])?$A2B->config["webui"]['len_cardnumber']:null);
	define ("LEN_ALIASNUMBER", isset($A2B->config["webui"]['len_aliasnumber'])?$A2B->config["webui"]['len_aliasnumber']:null);
	define ("LEN_VOUCHER", isset($A2B->config["webui"]['len_voucher'])?$A2B->config["webui"]['len_voucher']:null);
	define ("NUM_MUSICONHOLD_CLASS", isset($A2B->config["webui"]['num_musiconhold_class'])?$A2B->config["webui"]['num_musiconhold_class']:null);
	define ("MANAGER_HOST", isset($A2B->config["webui"]['manager_host'])?$A2B->config["webui"]['manager_host']:null);
	define ("MANAGER_USERNAME", isset($A2B->config["webui"]['manager_username'])?$A2B->config["webui"]['manager_username']:null);
	define ("MANAGER_SECRET", isset($A2B->config["webui"]['manager_secret'])?$A2B->config["webui"]['manager_secret']:null);
	define ("SHOW_HELP", isset($A2B->config["webui"]['show_help'])?$A2B->config["webui"]['show_help']:null);	
	define ("MY_MAX_FILE_SIZE_IMPORT", isset($A2B->config["webui"]['my_max_file_size_import'])?$A2B->config["webui"]['my_max_file_size_import']:null);
	define ("MY_MAX_FILE_SIZE", isset($A2B->config["webui"]['my_max_file_size'])?$A2B->config["webui"]['my_max_file_size']:null);
	define ("DIR_STORE_MOHMP3",isset($A2B->config["webui"]['dir_store_mohmp3'])?$A2B->config["webui"]['dir_store_mohmp3']:null);
	define ("DIR_STORE_AUDIO", isset($A2B->config["webui"]['dir_store_audio'])?$A2B->config["webui"]['dir_store_audio']:null);
	define ("MY_MAX_FILE_SIZE_AUDIO", isset($A2B->config["webui"]['my_max_file_size_audio'])?$A2B->config["webui"]['my_max_file_size_audio']:null);
	$file_ext_allow = isset($A2B->config["webui"]['file_ext_allow'])?$A2B->config["webui"]['file_ext_allow']:null;
	$file_ext_allow_musiconhold = isset($A2B->config["webui"]['file_ext_allow_musiconhold'])?$A2B->config["webui"]['file_ext_allow_musiconhold']:null;
	define ("LINK_AUDIO_FILE", isset($A2B->config["webui"]['link_audio_file'])?$A2B->config["webui"]['link_audio_file']:null);
	define ("MONITOR_PATH", isset($A2B->config["webui"]['monitor_path'])?$A2B->config["webui"]['monitor_path']:null);
	define ("MONITOR_FORMATFILE", isset($A2B->config["webui"]['monitor_formatfile'])?$A2B->config["webui"]['monitor_formatfile']:null); 
	define ("SHOW_ICON_INVOICE", isset($A2B->config["webui"]['show_icon_invoice'])?$A2B->config["webui"]['show_icon_invoice']:null);
	define ("SHOW_TOP_FRAME", isset($A2B->config["webui"]['show_top_frame'])?$A2B->config["webui"]['show_top_frame']:null);
	define ("ADVANCED_MODE", isset($A2B->config["webui"]['advanced_mode'])?$A2B->config["webui"]['advanced_mode']:null);
		
	define ("BASE_CURRENCY", isset($A2B->config["webui"]['base_currency'])?$A2B->config["webui"]['base_currency']:null);
	define ("CURRENCY_CHOOSE", isset($A2B->config["webui"]['currency_choose'])?$A2B->config["webui"]['currency_choose']:null);
	
		// PAYPAL	
	define ("PAYPAL_EMAIL", isset($A2B->config["paypal"]['paypal_email'])?$A2B->config["paypal"]['paypal_email']:null);
	define ("PAYPAL_FROM_EMAIL",isset( $A2B->config["paypal"]['from_email'])?$A2B->config["paypal"]['from_email']:null);
	define ("PAYPAL_FROM_NAME", isset($A2B->config["paypal"]['from_name'])?$A2B->config["paypal"]['from_name']:null);
	define ("PAYPAL_COMPANY_NAME", isset($A2B->config["paypal"]['company_name'])?$A2B->config["paypal"]['company_name']:null);
	define ("PAYPAL_ERROR_EMAIL", isset($A2B->config["paypal"]['error_email'])?$A2B->config["paypal"]['error_email']:null);
	define ("PAYPAL_ITEM_NAME", isset($A2B->config["paypal"]['item_name'])?$A2B->config["paypal"]['item_name']:null);
	define ("PAYPAL_CURRENCY_CODE", isset($A2B->config["paypal"]['currency_code'])?$A2B->config["paypal"]['currency_code']:null);
	define ("PAYPAL_NOTIFY_URL", isset($A2B->config["paypal"]['notify_url'])?$A2B->config["paypal"]['notify_url']:null);
	define ("PAYPAL_PURCHASE_AMOUNT", isset($A2B->config["paypal"]['purchase_amount'])?$A2B->config["paypal"]['purchase_amount']:null);
	define ("PAYPAL_LOGFILE", isset($A2B->config["paypal"]['paypal_logfile'])?$A2B->config["paypal"]['paypal_logfile']:null);
	
	
	
	define ("ACTIVATEDBYUSER", isset($A2B->config["signup"]['activatedbyuser'])?$A2B->config["signup"]['activatedbyuser']:null);


	// INCLUDE FILES
	define ("FSROOT", substr(dirname(__FILE__),0,-3));
	define ("LIBDIR", FSROOT."lib/");
	include (FSROOT."lib/Misc.php");

	/*
	 *		GLOBAL USED VARIABLE
	 */
	$PHP_SELF = $_SERVER["PHP_SELF"];


	$CURRENT_DATETIME = date("Y-m-d H:i:s");

	/*
	 *		GLOBAL POST/GET VARIABLE
	 */
	getpost_ifset(array('form_action', 'atmenu', 'action', 'stitle', 'sub_action', 'IDmanager', 'current_page', 'order', 'sens', 'mydisplaylimit', 'filterprefix','language', 'cssname', 'exporttype'));

	
	// Include general language file
        // Language session
    session_start();
    if(ini_get('register_globals'))
    {
        foreach($_REQUEST as $key => $value)
        {
            $$key = $value;
        }
    }
    if (!isset($_SESSION["language"]))
    {
        $_SESSION["language"]='english';
    }
    else if (isset($language))
    {
      $_SESSION["language"] = $language;
    }

    define ("LANGUAGE",$_SESSION["language"]);
	//include (FSROOT."lib/languages/".LANGUAGE.".php");
	//define ("LANGUAGE_DIR",FSROOT."lib/languages/".LANGUAGE."/");
    require("languageSettings.php");

    SetLocalLanguage();

	/*
	 *		CONNECT / DISCONNECT DATABASE
	 */

	function DbConnect($db= NULL)
	{
		if (DB_TYPE == "postgres"){
			$datasource = 'pgsql://'.USER.':'.PASS.'@'.HOST.'/'.DBNAME;
		}else{
			$datasource = 'mysql://'.USER.':'.PASS.'@'.HOST.'/'.DBNAME;
		}

		$DBHandle = NewADOConnection($datasource);

		if (!$DBHandle) die("Connection failed");

		return $DBHandle;
	}


	function DbDisconnect($DBHandle)
	{
		$DBHandle ->disconnect();
	}

	function get_currencies()
	{
		$handle = DbConnect();
		$instance_table = new Table();

		$QUERY =  "SELECT id,currency,name,value from cc_currencies order by id";
		$result = $instance_table -> SQLExec ($handle, $QUERY);

		/*
			$currencies_list['ADF'][1]="Andorran Franc";
			$currencies_list['ADF'][2]="0.1339";
			[ADF] => Array ( [1] => Andorran Franc (ADF), [2] => 0.1339 )
		*/
		if (is_array($result)){
			$num_cur = count($result);
				for ($i=0;$i<$num_cur;$i++)
					$currencies_list[$result[$i][1]] = array (1 => $result[$i][2], 2 => $result[$i][3]);
		}

		return $currencies_list;
	}
		
	if(isset($cssname) && $cssname != "")
	{
		if ($_SESSION["stylefile"]!=$cssname){
			foreach (glob("./templates_c/*.*") as $filename)
			{
				unlink($filename);
			}			
		}
		$_SESSION["stylefile"] = $cssname;		
	}
	
	if(!isset($_SESSION["stylefile"]) || $_SESSION["stylefile"]==''){
		$_SESSION["stylefile"]='default';
	}

    //Images Path
    define ("Images_Path","./templates/".$_SESSION["stylefile"]."/images");
	define ("Images_Path_Main","./templates/".$_SESSION["stylefile"]."/images");
	define ("KICON_PATH","./templates/".$_SESSION["stylefile"]."/images/kicons");
	
	
	define ("WEBUI_DATE", 'Release : Somewhere in November 2006');
	define ("WEBUI_VERSION", 'Asterisk2Billing - Version 1.2.4 (Yellowjacket) ');
	include (FSROOT."lib/help.php");	

?>
