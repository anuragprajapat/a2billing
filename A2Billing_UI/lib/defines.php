<?php
include (dirname(__FILE__)."/Class.A2Billing.php");
require_once('adodb/adodb.inc.php'); // AdoDB
include (dirname(__FILE__)."/Class.Table.php");

$A2B = new A2Billing();

// SELECT THE FILES TO LOAD THE CONFIGURATION
	$A2B -> load_conf($agi, AST_CONFIG_DIR."a2billing.conf", 1);


// DEFINE FOR THE DATABASE CONNECTION
define ("HOST", isset($A2B->config['database']['hostname'])?$A2B->config['database']['hostname']:null);
define ("PORT", isset($A2B->config['database']['port'])?$A2B->config['database']['port']:null);
define ("USER", isset($A2B->config['database']['user'])?$A2B->config['database']['user']:null);
define ("PASS", isset($A2B->config['database']['password'])?$A2B->config['database']['password']:null);
define ("DBNAME", isset($A2B->config['database']['dbname'])?$A2B->config['database']['dbname']:null);
define ("DB_TYPE", isset($A2B->config['database']['dbtype'])?$A2B->config['database']['dbtype']:null); 	

define ("LEN_ALIASNUMBER", isset($A2B->config['global']['len_aliasnumber'])?$A2B->config['global']['len_aliasnumber']:null);
define ("LEN_VOUCHER", isset($A2B->config['global']['len_voucher'])?$A2B->config['global']['len_voucher']:null);
define ("BASE_CURRENCY", isset($A2B->config['global']['base_currency'])?$A2B->config['global']['base_currency']:null);

define ("BUDDY_SIP_FILE", isset($A2B->config['webui']['buddy_sip_file'])?$A2B->config['webui']['buddy_sip_file']:null);
define ("BUDDY_IAX_FILE", isset($A2B->config['webui']['buddy_iax_file'])?$A2B->config['webui']['buddy_iax_file']:null);
define ("API_SECURITY_KEY", isset($A2B->config['webui']['api_security_key'])?$A2B->config['webui']['api_security_key']:null);

// WEB DEFINE FROM THE A2BILLING.CONF FILE
define ("EMAIL_ADMIN", isset($A2B->config['webui']['email_admin'])?$A2B->config['webui']['email_admin']:null);
define ("NUM_MUSICONHOLD_CLASS", isset($A2B->config['webui']['num_musiconhold_class'])?$A2B->config['webui']['num_musiconhold_class']:null);
define ("MANAGER_HOST", isset($A2B->config['webui']['manager_host'])?$A2B->config['webui']['manager_host']:null);
define ("MANAGER_USERNAME", isset($A2B->config['webui']['manager_username'])?$A2B->config['webui']['manager_username']:null);
define ("MANAGER_SECRET", isset($A2B->config['webui']['manager_secret'])?$A2B->config['webui']['manager_secret']:null);
define ("SHOW_HELP", isset($A2B->config['webui']['show_help'])?$A2B->config['webui']['show_help']:null);	
define ("MY_MAX_FILE_SIZE_IMPORT", isset($A2B->config['webui']['my_max_file_size_import'])?$A2B->config['webui']['my_max_file_size_import']:null);
define ("DIR_STORE_MOHMP3",isset($A2B->config['webui']['dir_store_mohmp3'])?$A2B->config['webui']['dir_store_mohmp3']:null);
define ("DIR_STORE_AUDIO", isset($A2B->config['webui']['dir_store_audio'])?$A2B->config['webui']['dir_store_audio']:null);
define ("MY_MAX_FILE_SIZE_AUDIO", isset($A2B->config['webui']['my_max_file_size_audio'])?$A2B->config['webui']['my_max_file_size_audio']:null);
$file_ext_allow = is_array($A2B->config['webui']['file_ext_allow'])?$A2B->config['webui']['file_ext_allow']:null;
$file_ext_allow_musiconhold = is_array($A2B->config['webui']['file_ext_allow_musiconhold'])?$A2B->config['webui']['file_ext_allow_musiconhold']:null;
define ("LINK_AUDIO_FILE", isset($A2B->config['webui']['link_audio_file'])?$A2B->config['webui']['link_audio_file']:null);
define ("MONITOR_PATH", isset($A2B->config['webui']['monitor_path'])?$A2B->config['webui']['monitor_path']:null);
define ("MONITOR_FORMATFILE", isset($A2B->config['webui']['monitor_formatfile'])?$A2B->config['webui']['monitor_formatfile']:null); 
define ("SHOW_ICON_INVOICE", isset($A2B->config['webui']['show_icon_invoice'])?$A2B->config['webui']['show_icon_invoice']:null);
define ("SHOW_TOP_FRAME", isset($A2B->config['webui']['show_top_frame'])?$A2B->config['webui']['show_top_frame']:null);
define ("ADVANCED_MODE", isset($A2B->config['webui']['advanced_mode'])?$A2B->config['webui']['advanced_mode']:null);
define ("CURRENCY_CHOOSE", isset($A2B->config['webui']['currency_choose'])?$A2B->config['webui']['currency_choose']:null);
define ("DELETE_FK_CARD", isset($A2B->config['webui']['delete_fk_card'])?$A2B->config['webui']['delete_fk_card']:null);

// PAYPAL	
define ("PAYPAL_EMAIL", isset($A2B->config['paypal']['paypal_email'])?$A2B->config['paypal']['paypal_email']:null);
define ("PAYPAL_FROM_EMAIL",isset( $A2B->config['paypal']['from_email'])?$A2B->config['paypal']['from_email']:null);
define ("PAYPAL_FROM_NAME", isset($A2B->config['paypal']['from_name'])?$A2B->config['paypal']['from_name']:null);
define ("PAYPAL_COMPANY_NAME", isset($A2B->config['paypal']['company_name'])?$A2B->config['paypal']['company_name']:null);
define ("PAYPAL_ERROR_EMAIL", isset($A2B->config['paypal']['error_email'])?$A2B->config['paypal']['error_email']:null);
define ("PAYPAL_ITEM_NAME", isset($A2B->config['paypal']['item_name'])?$A2B->config['paypal']['item_name']:null);
define ("PAYPAL_CURRENCY_CODE", isset($A2B->config['paypal']['currency_code'])?$A2B->config['paypal']['currency_code']:null);
define ("PAYPAL_NOTIFY_URL", isset($A2B->config['paypal']['notify_url'])?$A2B->config['paypal']['notify_url']:null);
define ("PAYPAL_PURCHASE_AMOUNT", isset($A2B->config['paypal']['purchase_amount'])?$A2B->config['paypal']['purchase_amount']:null);
define ("PAYPAL_FEES", isset($A2B->config['paypal']['paypal_fees'])?$A2B->config['paypal']['paypal_fees']:null); 


// BACKUP
define ("BACKUP_PATH", isset($A2B->config['backup']['backup_path'])?$A2B->config['backup']['backup_path']:null);
define ("GZIP_EXE", isset($A2B->config['backup']['gzip_exe'])?$A2B->config['backup']['gzip_exe']:null);
define ("GUNZIP_EXE", isset($A2B->config['backup']['gunzip_exe'])?$A2B->config['backup']['gunzip_exe']:null);
define ("MYSQLDUMP", isset($A2B->config['backup']['mysqldump'])?$A2B->config['backup']['mysqldump']:null);
define ("PG_DUMP", isset($A2B->config['backup']['pg_dump'])?$A2B->config['backup']['pg_dump']:null);
define ("MYSQL", isset($A2B->config['backup']['mysql'])?$A2B->config['backup']['mysql']:null);
define ("PSQL", isset($A2B->config['backup']['psql'])?$A2B->config['backup']['psql']:null);

    //Images Path
    define ("Images_Path","./images");
    define ("Images_Path_Main","../Images");
// SIP IAX FRIEND CREATION
define ("FRIEND_TYPE", isset($A2B->config['peer_friend']['type'])?$A2B->config['peer_friend']['type']:null);
define ("FRIEND_ALLOW", isset($A2B->config['peer_friend']['allow'])?$A2B->config['peer_friend']['allow']:null);
define ("FRIEND_CONTEXT", isset($A2B->config['peer_friend']['context'])?$A2B->config['peer_friend']['context']:null);
define ("FRIEND_NAT", isset($A2B->config['peer_friend']['nat'])?$A2B->config['peer_friend']['nat']:null);
define ("FRIEND_AMAFLAGS", isset($A2B->config['peer_friend']['amaflags'])?$A2B->config['peer_friend']['amaflags']:null);
define ("FRIEND_QUALIFY", isset($A2B->config['peer_friend']['qualify'])?$A2B->config['peer_friend']['qualify']:null);
define ("FRIEND_HOST", isset($A2B->config['peer_friend']['host'])?$A2B->config['peer_friend']['host']:null);
define ("FRIEND_DTMFMODE", isset($A2B->config['peer_friend']['dtmfmode'])?$A2B->config['peer_friend']['dtmfmode']:null);

// INCLUDE FILES
define ("FSROOT", substr(dirname(__FILE__),0,-3));
	define ("LIBDIR", FSROOT."lib/");
	include (FSROOT."lib/help.php");
include (FSROOT."lib/Misc.php");


/*
 *		GLOBAL USED VARIABLE
 */
$PHP_SELF = $_SERVER["PHP_SELF"];


$CURRENT_DATETIME = date("Y-m-d H:i:s");		
	
/*
 *		GLOBAL POST/GET VARIABLE
 */		 
getpost_ifset(array('form_action', 'atmenu', 'action', 'stitle', 'sub_action', 'IDmanager', 'current_page', 'order', 'sens', 'mydisplaylimit', 'filterprefix', 'cssname', 'popup_select'));

/*
 *		CONNECT / DISCONNECT DATABASE
 */


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
require("languageSettings.php");
    SetLocalLanguage($_SESSION["language"]);
 
function DbConnect($db= NULL)
{
	if (DB_TYPE == "postgres"){
			if (HOST!=null)
				$datasource = 'pgsql://'.USER.':'.PASS.'@'.HOST.'/'.DBNAME;
			else
				$datasource = 'pgsql://dbname='.DBNAME.' user='.USER ;
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


	function get_languages() {
	// *-*
		$language_list = array();
		$language_list["0"] = array( _("English"), "en");
		$language_list["1"] = array( _("Spanish"), "es");
		$language_list["2"] = array( _("French"),  "fr");
		return $language_list;
	}
	
	function get_languages_r(&$langs) {
		if (is_array($langs)){
			$num=count($langs);
			for ($i=0;$i<$num;$i++)
				$ret_list[$i]=array($langs[$i][1], $langs[$i][0]);
		}
		return $ret_list;
	}


/** @param $typ a string describing the charge type. It can be in the form "3|4|5" so
	that multiple sides can be selected.
*/
	function get_chargetypes($typ = '3')
	{
		$handle = DbConnect();
		$it = new Table();
		$sides= explode('|',$typ);
		foreach ($sides as $s)
			$sides_c[] = "side = " . trim($s);
		$sides_clause = implode (" OR ", $sides_c);
		$QUERY =  "SELECT id, gettexti(id,'". getenv('LANG')."'), charge FROM cc_paytypes WHERE $sides_clause ORDER BY id";
		// $it->debug_st =1;
		$result = $it -> SQLExec ($handle, $QUERY);
		
		if (is_array($result)){
			$num = count($result);
			for ($i=0;$i<$num;$i++)
				$charges_list[$result[$i][0]] = array (1 => $result[$i][0], 
					0 => $result[$i][1], 2 => $result[$i][2]);
		}
		
		return $charges_list;
	}

function send_email_attachment($emailfrom, $emailto, $emailsubject, $emailmessage,$attachmentfilename, $emailfilestream )
{
	$email_from = $emailfrom; 
	$email_subject = $emailsubject;
	$email_message = $emailmessage; 
	
	$email_to = $emailto;
	$headers = "From: ".$email_from;
	
	$semi_rand = md5(time()); 
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
	   
	$headers .= "\nMIME-Version: 1.0\n" . 
				"Content-Type: multipart/mixed;\n" . 
				" boundary=\"{$mime_boundary}\""; 
	
	$email_message .= "This is a multi-part message in MIME format.\n\n" . 
					"--{$mime_boundary}\n" . 
					"Content-Type:text/html; charset=\"iso-8859-1\"\n" . 
					"Content-Transfer-Encoding: 7bit\n\n" . 
	$email_message . "\n\n"; 
	
	$fileatt = "";           
	$fileatt_type = "application/octet-stream"; 
	$fileatt_name = $attachmentfilename;  
	$stream = chunk_split(base64_encode($emailfilestream)); 
	$email_message .= "--{$mime_boundary}\n" . 
					  "Content-Type: {$fileatt_type};\n" . 
					  " name=\"{$fileatt_name}\"\n" .                 
					  "Content-Transfer-Encoding: base64\n\n" . 
					 $stream . "\n\n" . 
					  "--{$mime_boundary}\n"; 
	unset($stream);
	unset($file);
	unset($fileatt);
	unset($fileatt_type);
	unset($fileatt_name);
	$ok = @mail($email_to, $email_subject, $email_message, $headers);
	return $ok;
}

if(isset($cssname) && $cssname != "")
{
	$_SESSION["stylefile"] = $cssname;		
}

define ("WEBUI_DATE", 'Release : Somewhere in March 2007');	 
define ("WEBUI_VERSION", 'Asterisk2Billing - Version 1.3 - Beta (Yellowjacket)');

//Enable Disable Captcha
define ("CAPTCHA_ENABLE", isset($A2B->config["signup"]['enable_captcha'])?$A2B->config["signup"]['enable_captcha']:0);



include (FSROOT."lib/help.php");

?>
