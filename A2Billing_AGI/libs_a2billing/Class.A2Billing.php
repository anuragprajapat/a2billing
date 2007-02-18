<?php
 /**
  * Class.A2Billing.php : PHP A2Billing Functions for Asterisk
  * Website: http://www.areski.net/
  *
  * $Id: Class.A2Billing.php,v 3.0 2005/09/06 00:23
  *
  * Copyright (c) 2004, 2005 Belaid Arezqui <info@areski.net>
  * All Rights Reserved.
  *
  * This software is released under the terms of the GNU Lesser General Public License v2.1
  * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
  *
  * We would be happy to know peoples that experience the software, 
  * drop me an Email if you'd like us to list your program.
  * 
  *
  * Written for PHP 4.3.11, should work with older PHP 4.x versions.
  *
  * Please submit bug reports, patches, etc to info@areski.net
  *
  * @package A2Billing
  * @version 3.0
  */
  
  define('AST_CONFIG_DIR', '/etc/asterisk/'); 
  define('DEFAULT_A2BILLING_CONFIG', AST_CONFIG_DIR . '/a2billing.conf');


class A2Billing {
	
	
	/**
    * Config variables
    *
    * @var array
    * @access public
    */
	var $config;
	
	/**
    * Config AGI variables
	* Create for coding readability facilities
    *
    * @var array
    * @access public
    */
	var $agiconfig;
	
	/**
    * IDConfig variables
    *
    * @var interger
    * @access public
    */
	var $idconfig=1;
	
	
	/**
    * cardnumber & CallerID variables
    *
    * @var string
    * @access public
    */
	var $cardnumber;
	var $CallerID;
	
	
	/**
    * Buffer variables
    *
    * @var string
    * @access public
    */
	var $BUFFER;
	
	
	/**
    * DBHandle variables
    *
    * @var object
    * @access public
    */	
	var $DBHandle;
	
	
	/**
    * instance_table variables
    *
    * @var object
    * @access public
    */	
	var $instance_table;
	
	
	/**
    * request AGI variables
    *
    * @var string
    * @access public
    */
	
	var $channel;
	var $uniqueid;
	var $accountcode;
	var $dnid;
		
	
	// from apply_rules, if a prefix is removed we keep it to track exactly what the user introduce
	
	var $countrycode;
	var $subcode;
	var $myprefix;
	var $ipaddress;
	var $rate;
	var $destination;
	var $sip_iax_buddy;	
	var $credit;
	var $tariff;
	var $active;
	var $hostname='';
	var $currency='usd';
    
        
	var $timeout;
	var $newdestination;
	var $tech;
	var $prefix;
	var $username;
	
	var $typepaid = 0; 
	var $removeinterprefix = 1; 
	var $redial;
	var $nbused = 0;
	
	var $enableexpire;
	var $expirationdate;
	var $expiredays;
	var $firstusedate;
	var $creationdate;
		
	var $languageselected;
	
	
	var $cardholder_lastname;
	var $cardholder_firstname;
	var $cardholder_email;
	var $cardholder_uipass;
	var $id_campaign;
	var $id_card;
	var $useralias;
	
	// Flag to know that we ask for an othercardnumber when for instance we doesnt have enough credit to make a call
	var $ask_other_cardnumber=0;
	
	/**
	* CC_TESTING variables 
	* for developer purpose, will replace some get_data inputs in order to test the application from shell
	*
	* @var interger
	* @access public
	*/	
	var $CC_TESTING;
	
		
	/* CONSTRUCTOR */

	function A2Billing() {     
		
		  //$this -> DBHandle = $DBHandle;

	}


	/* Init */

	function Reinit () {  
			$this -> countrycode='';
			$this -> subcode='';
			$this -> myprefix='';
			$this -> ipaddress='';
			$this -> rate='';
			$this -> destination='';			
			$this -> sip_iax_buddy='';		
	}
	
	
	/* Write log into file */
	
	function write_log($output, $tobuffer = 1){
						
		//$tobuffer = 0;
		if ($this->agiconfig['logger_enable'] == 1){
				
			$string_log = "[".date("d/m/Y H:i:s")."]:[CallerID:".$this->CallerID."]:[CN:".$this->cardnumber."]:$output\n";
			if ($this->CC_TESTING) echo $string_log;
				
			$this -> BUFFER .= $string_log;
			if (!$tobuffer || $this->CC_TESTING){
				error_log ($this -> BUFFER, 3, $this->agiconfig['log_file']);
				$this-> BUFFER = '';
			}
		}
	}
	
	/* set the DB handler */ 
	function set_dbhandler ($DBHandle){
		$this->DBHandle	= $DBHandle;
	}
	
	function set_instance_table ($instance_table){
		$this->instance_table	= $instance_table;
	}

	function load_conf( &$agi, $config=NULL, $webui=0, $idconfig=1, $optconfig=array())
    {
	  
		$this -> idconfig = $idconfig;
		// load config
		if(!is_null($config) && file_exists($config))
			$this->config = parse_ini_file($config, true);
		elseif(file_exists(DEFAULT_A2BILLING_CONFIG)){
			$this->config = parse_ini_file(DEFAULT_A2BILLING_CONFIG, true);		
		}
	  
	  
		// If optconfig is specified, stuff vals and vars into 'a2billing' config array.
		foreach($optconfig as $var=>$val)
			$this->config["agi-conf$idconfig"][$var] = $val;
		
		// add default values to config for uninitialized values
        
		
		
		  
		// conf for the database connection
		if(!isset($this->config["database"]['hostname']))	$this->config["database"]['hostname'] = 'localhost';
		if(!isset($this->config["database"]['port']))		$this->config["database"]['port'] = '5432';
		if(!isset($this->config["database"]['user']))		$this->config["database"]['user'] = 'postgres';
		if(!isset($this->config["database"]['password']))	$this->config["database"]['password'] = '';
		if(!isset($this->config["database"]['dbname']))		$this->config["database"]['dbname'] = 'a2billing';
		if(!isset($this->config["database"]['dbtype']))		$this->config["database"]['dbtype'] = 'postgres';
		
		
		
		// Conf for the Callback
		if(!isset($this->config["callback"]['context_callback']))	$this->config["callback"]['context_callback'] = 'a2billing-callback';
		if(!isset($this->config["callback"]['ani_callback_delay']))	$this->config["callback"]['ani_callback_delay'] = '10';
		if(!isset($this->config["callback"]['extension']))		$this->config["callback"]['extension'] = '1000';
		if(!isset($this->config["callback"]['sec_avoid_repeate']))	$this->config["callback"]['sec_avoid_repeate'] = '30';
		if(!isset($this->config["callback"]['timeout']))		$this->config["callback"]['timeout'] = '20';
		if(!isset($this->config["callback"]['answer_call']))		$this->config["callback"]['answer_call'] = '1';
		if(!isset($this->config["callback"]['nb_predictive_call']))	$this->config["callback"]['nb_predictive_call'] = '10';
		if(!isset($this->config["callback"]['nb_day_wait_before_retry']))	$this->config["callback"]['nb_day_wait_before_retry'] = '1';
		if(!isset($this->config["callback"]['context_preditctivedialer']))	$this->config["callback"]['context_preditctivedialer'] = 'a2billing-predictivedialer';
		if(!isset($this->config["callback"]['predictivedialer_maxtime_tocall']))	$this->config["callback"]['predictivedialer_maxtime_tocall'] = '5400';		
		if(!isset($this->config["callback"]['sec_wait_before_callback']))	$this->config["callback"]['sec_wait_before_callback'] = '10';		
		
		
		
		// Conf for the signup 
		if(!isset($this->config["signup"]['enable_signup']))$this->config["signup"]['enable_signup'] = '1';
		if(!isset($this->config["signup"]['credit']))		$this->config["signup"]['credit'] = '0';
		if(!isset($this->config["signup"]['tariff']))		$this->config["signup"]['tariff'] = '8';
		if(!isset($this->config["signup"]['activated']))	$this->config["signup"]['activated'] = 't';
		if(!isset($this->config["signup"]['simultaccess']))	$this->config["signup"]['simultaccess'] = '0';
		if(!isset($this->config["signup"]['typepaid']))		$this->config["signup"]['typepaid'] = '0';
		if(!isset($this->config["signup"]['creditlimit']))	$this->config["signup"]['creditlimit'] = '0';
		if(!isset($this->config["signup"]['runservice']))	$this->config["signup"]['runservice'] = '0';
		if(!isset($this->config["signup"]['enableexpire']))	$this->config["signup"]['enableexpire'] = '0';
		if(!isset($this->config["signup"]['expiredays']))	$this->config["signup"]['expiredays'] = '0';
		
		// Conf for Paypal 		
		if(!isset($this->config["paypal"]['item_name']))	$this->config["paypal"]['item_name'] = 'Credit Purchase';
		if(!isset($this->config["paypal"]['currency_code']))	$this->config["paypal"]['currency_code'] = 'USD';
		if(!isset($this->config["paypal"]['purchase_amount']))	$this->config["paypal"]['purchase_amount'] = '5;10;15';
		if(!isset($this->config["paypal"]['paypal_logfile']))	$this->config["paypal"]['paypal_logfile'] = '/tmp/a2billing_paypal.log';
	

		// Conf for Backup
		if(!isset($this->config["backup"]['backup_path']))	$this->config["backup"]['backup_path'] ='/tmp';
		if(!isset($this->config["backup"]['gzip_exe']))		$this->config["backup"]['gzip_exe'] ='/bin/gzip';
		if(!isset($this->config["backup"]['gunzip_exe']))	$this->config["backup"]['gunzip_exe'] ='/bin/gunzip';
		if(!isset($this->config["backup"]['mysqldump']))	$this->config["backup"]['mysqldump'] ='/usr/bin/mysqldump';
		if(!isset($this->config["backup"]['pg_dump']))		$this->config["backup"]['pg_dump'] ='/usr/bin/pg_dump';
		if(!isset($this->config["backup"]['mysql']))		$this->config["backup"]['mysql'] ='/usr/bin/mysql';
		if(!isset($this->config["backup"]['psql']))		$this->config["backup"]['psql'] ='/usr/bin/psql';
	
		
		// Conf for Customer Web UI
		if(!isset($this->config["webcustomerui"]['customerinfo']))	$this->config["webcustomerui"]['customerinfo'] = '1';
		if(!isset($this->config["webcustomerui"]['cdr']))		$this->config["webcustomerui"]['cdr'] = '1';
		if(!isset($this->config["webcustomerui"]['invoice']))		$this->config["webcustomerui"]['invoice'] = '1';		
		if(!isset($this->config["webcustomerui"]['voucher']))		$this->config["webcustomerui"]['voucher'] = '1';
		if(!isset($this->config["webcustomerui"]['paypal']))		$this->config["webcustomerui"]['paypal'] = '1';
		if(!isset($this->config["webcustomerui"]['speeddial']))		$this->config["webcustomerui"]['speeddial'] = '1';
		if(!isset($this->config["webcustomerui"]['did']))		$this->config["webcustomerui"]['did'] = '1';
		if(!isset($this->config["webcustomerui"]['ratecard']))		$this->config["webcustomerui"]['ratecard'] = '1';
		if(!isset($this->config["webcustomerui"]['simulator']))		$this->config["webcustomerui"]['simulator'] = '1';
		if(!isset($this->config["webcustomerui"]['callback']))		$this->config["webcustomerui"]['callback'] = '1';
		if(!isset($this->config["webcustomerui"]['predictivedialer']))	$this->config["webcustomerui"]['predictivedialer'] = '1';
		if(!isset($this->config["webcustomerui"]['webphone']))		$this->config["webcustomerui"]['webphone'] = '1';
		if(!isset($this->config["webcustomerui"]['callerid']))		$this->config["webcustomerui"]['callerid'] = '1';
		if(!isset($this->config["webcustomerui"]['limit_callerid']))	$this->config["webcustomerui"]['limit_callerid'] = '5';
		if(!isset($this->config["webcustomerui"]['error_email']))	$this->config["webcustomerui"]['error_email'] = 'root@localhost';
		
		// conf for the web ui
		if(!isset($this->config["webui"]['buddy_sip_file']))		$this->config["webui"]['buddy_sip_file'] = '/etc/asterisk/additional_a2billing_sip.conf';
		if(!isset($this->config["webui"]['buddy_iax_file']))		$this->config["webui"]['buddy_iax_file'] = '/etc/asterisk/additional_a2billing_iax.conf';
		if(!isset($this->config["webui"]['api_logfile']))		$this->config["webui"]['api_logfile'] = '/tmp/api_ecommerce_request.log';
		if(isset($this->config["webui"]['api_ip_auth']))		$this->config["webui"]['api_ip_auth'] = explode(";", $this->config["webui"]['api_ip_auth']);
		
		
		if(!isset($this->config["webui"]['len_cardnumber']))		$this->config["webui"]['len_cardnumber'] = 10;
		if(!isset($this->config["webui"]['len_aliasnumber']))		$this->config["webui"]['len_aliasnumber'] = 15;
		if(!isset($this->config["webui"]['len_voucher']))		$this->config["webui"]['len_voucher'] = 15;
		if(!isset($this->config["webui"]['dir_store_mohmp3']))		$this->config["webui"]['dir_store_mohmp3'] = '/var/lib/asterisk/mohmp3';
		if(!isset($this->config["webui"]['num_musiconhold_class']))	$this->config["webui"]['num_musiconhold_class'] = 10;
		if(!isset($this->config["webui"]['show_help']))			$this->config["webui"]['show_help'] = 1;
		if(!isset($this->config["webui"]['my_max_file_size_import']))	$this->config["webui"]['my_max_file_size_import'] = 512000;
		if(!isset($this->config["webui"]['my_max_file_size']))		$this->config["webui"]['my_max_file_size'] = 512000;
		if(!isset($this->config["webui"]['dir_store_audio']))		$this->config["webui"]['dir_store_audio'] = '/var/lib/asterisk/sounds/a2billing';
		if(!isset($this->config["webui"]['my_max_file_size_audio']))	$this->config["webui"]['my_max_file_size_audio'] = 3072000;

		if(isset($this->config["webui"]['file_ext_allow']))		$this->config["webui"]['file_ext_allow'] = explode(",", $this->config["webui"]['file_ext_allow']);
		else $this->config["webui"]['file_ext_allow'] = explode(",", "gsm, mp3, wav");
		
		if(isset($this->config["webui"]['file_ext_allow_musiconhold']))	$this->config["webui"]['file_ext_allow_musiconhold'] = explode(",", $this->config["webui"]['file_ext_allow_musiconhold']);
		else $this->config["webui"]['file_ext_allow_musiconhold'] = explode(",", "mp3");

		if(!isset($this->config["webui"]['show_top_frame'])) 		$this->config["webui"]['show_top_frame'] = 1;
		if(!isset($this->config["webui"]['base_currency'])) 		$this->config["webui"]['base_currency'] = 'usd';
		if(!isset($this->config["webui"]['currency_choose'])) 		$this->config["webui"]['currency_choose'] = 'all';
		if(!isset($this->config["webui"]['card_export_field_list']))	$this->config["webui"]['card_export_field_list'] = 'creationdate, username, credit, lastname, firstname';
		if(!isset($this->config["webui"]['voucher_export_field_list']))	$this->config["webui"]['voucher_export_field_list'] = 'id, voucher, credit, tag, activated, usedcardnumber, usedate, currency';
		if(!isset($this->config["webui"]['advanced_mode']))			$this->config["webui"]['advanced_mode'] = 0;	

		  
		// conf for the recurring process
		if(!isset($this->config["recprocess"]['batch_log_file'])) 	$this->config["batch_log_file"]['buddyfilepath'] = '/etc/asterisk/';
		 
		// conf for the AGI
		if(!isset($this->config["agi-conf$idconfig"]['debug'])) 	$this->config["agi-conf$idconfig"]['debug'] = false;
		if(!isset($this->config["agi-conf$idconfig"]['logger_enable'])) $this->config["agi-conf$idconfig"]['logger_enable'] = 1;
		if(!isset($this->config["agi-conf$idconfig"]['log_file'])) $this->config["agi-conf$idconfig"]['log_file'] = '/tmp/a2billing.log';
		
		if(!isset($this->config["agi-conf$idconfig"]['answer_call'])) $this->config["agi-conf$idconfig"]['answer_call'] = 1;
		
		if(!isset($this->config["agi-conf$idconfig"]['auto_setcallerid'])) $this->config["agi-conf$idconfig"]['auto_setcallerid'] = 1;
					
		if(!isset($this->config["agi-conf$idconfig"]['say_goodbye'])) $this->config["agi-conf$idconfig"]['say_goodbye'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['play_menulanguage'])) $this->config["agi-conf$idconfig"]['play_menulanguage'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['force_language'])) $this->config["agi-conf$idconfig"]['force_language'] = 'EN';
		if(!isset($this->config["agi-conf$idconfig"]['len_cardnumber'])) $this->config["agi-conf$idconfig"]['len_cardnumber'] = 10;
		if(!isset($this->config["agi-conf$idconfig"]['len_aliasnumber']))	$this->config["agi-conf$idconfig"]['len_aliasnumber'] = 15;
		if(!isset($this->config["agi-conf$idconfig"]['len_voucher'])) $this->config["agi-conf$idconfig"]['len_voucher'] = 15;
		if(!isset($this->config["agi-conf$idconfig"]['min_credit_2call'])) $this->config["agi-conf$idconfig"]['min_credit_2call'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['min_duration_2bill'])) $this->config["agi-conf$idconfig"]['min_duration_2bill'] = 0;
		
		if(!isset($this->config["agi-conf$idconfig"]['use_dnid'])) $this->config["agi-conf$idconfig"]['use_dnid'] = 0;
		// Explode the no_auth_dnid string 
		if(isset($this->config["agi-conf$idconfig"]['no_auth_dnid'])) $this->config["agi-conf$idconfig"]['no_auth_dnid'] = explode(",",$this->config["agi-conf$idconfig"]['no_auth_dnid']);
		
		// Explode the extracharge_did and extracharge_fee strings
		if(isset($this->config["agi-conf$idconfig"]['extracharge_did'])) $this->config["agi-conf$idconfig"]['extracharge_did'] = explode(",",$this->config["agi-conf$idconfig"]['extracharge_did']);
		if(isset($this->config["agi-conf$idconfig"]['extracharge_fee'])) $this->config["agi-conf$idconfig"]['extracharge_fee'] = explode(",",$this->config["agi-conf$idconfig"]['extracharge_fee']);

		if(!isset($this->config["agi-conf$idconfig"]['number_try'])) $this->config["agi-conf$idconfig"]['number_try'] = 3;
		if(!isset($this->config["agi-conf$idconfig"]['say_balance_after_auth'])) $this->config["agi-conf$idconfig"]['say_balance_after_auth'] = 1;
		if(!isset($this->config["agi-conf$idconfig"]['say_balance_after_call'])) $this->config["agi-conf$idconfig"]['say_balance_after_call'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['say_rateinitial'])) $this->config["agi-conf$idconfig"]['say_rateinitial'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['say_timetocall'])) $this->config["agi-conf$idconfig"]['say_timetocall'] = 1;
		if(!isset($this->config["agi-conf$idconfig"]['cid_enable'])) $this->config["agi-conf$idconfig"]['cid_enable'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['cid_sanitize'])) $this->config["agi-conf$idconfig"]['cid_sanitize'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['cid_askpincode_ifnot_callerid'])) $this->config["agi-conf$idconfig"]['cid_askpincode_ifnot_callerid'] = 1;
		if(!isset($this->config["agi-conf$idconfig"]['cid_auto_assign_card_to_cid'])) $this->config["agi-conf$idconfig"]['cid_auto_assign_card_to_cid'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['notenoughcredit_cardnumber'])) $this->config["agi-conf$idconfig"]['notenoughcredit_cardnumber'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['notenoughcredit_assign_newcardnumber_cid'])) $this->config["agi-conf$idconfig"]['notenoughcredit_assign_newcardnumber_cid'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['maxtime_tocall_negatif_free_route'])) $this->config["agi-conf$idconfig"]['maxtime_tocall_negatif_free_route'] = 1800;
		if(!isset($this->config["agi-conf$idconfig"]['callerid_authentication_over_cardnumber'])) $this->config["agi-conf$idconfig"]['callerid_authentication_over_cardnumber'] = 0;
		
		
		if(!isset($this->config["agi-conf$idconfig"]['sip_iax_friends'])) $this->config["agi-conf$idconfig"]['sip_iax_friends'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['sip_iax_pstn_direct_call'])) $this->config["agi-conf$idconfig"]['sip_iax_pstn_direct_call'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['dialcommand_param'])) $this->config["agi-conf$idconfig"]['dialcommand_param'] = '|30|HL(%timeout%:61000:30000)';
		if(!isset($this->config["agi-conf$idconfig"]['dialcommand_param_sipiax_friend'])) $this->config["agi-conf$idconfig"]['dialcommand_param_sipiax_friend'] = '|30|HL(3600000:61000:30000)';
		if(!isset($this->config["agi-conf$idconfig"]['switchdialcommand'])) $this->config["agi-conf$idconfig"]['switchdialcommand'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['record_call'])) $this->config["agi-conf$idconfig"]['record_call'] = 0;
		if(!isset($this->config["agi-conf$idconfig"]['monitor_formatfile'])) $this->config["agi-conf$idconfig"]['monitor_formatfile'] = 'gsm';
		if(!isset($this->config["agi-conf$idconfig"]['base_currency'])) 	$this->config["agi-conf$idconfig"]['base_currency'] = 'usd';
		if(!isset($this->config["agi-conf$idconfig"]['currency_association']))	$this->config["agi-conf$idconfig"]['currency_association'] = 'all:credit';
		$this->config["agi-conf$idconfig"]['currency_association'] = explode(",",$this->config["agi-conf$idconfig"]['currency_association']);
		
		foreach($this->config["agi-conf$idconfig"]['currency_association'] as $cur_val){
			$cur_val = explode(":",$cur_val);
			$this->config["agi-conf$idconfig"]['currency_association_internal'][$cur_val[0]]=$cur_val[1];
		}
					
		if(!isset($this->config["agi-conf$idconfig"]['file_conf_enter_destination']))	$this->config["agi-conf$idconfig"]['file_conf_enter_destination'] = 'prepaid-enter-number-u-calling-1-or-011';
		if(!isset($this->config["agi-conf$idconfig"]['file_conf_enter_menulang']))	$this->config["agi-conf$idconfig"]['file_conf_enter_menulang'] = 'prepaid-menulang';		
		if(!isset($this->config["agi-conf$idconfig"]['send_reminder'])) $this->config["agi-conf$idconfig"]['send_reminder'] = 0;
		if(isset($this->config["agi-conf$idconfig"]['debugshell']) && $this->config["agi-conf$idconfig"]['debugshell'] == 1 && isset($agi)) $agi->nlinetoread = 0;
		
		
		$this->agiconfig = $this->config["agi-conf$idconfig"];
		
		if (!$webui) $this->conlog('A2Billing AGI internal configuration:');
      	if (!$webui) $this->conlog(print_r($this->agiconfig, true));
    }
	
	/**
    * Log to console if debug mode.
    *
    * @example examples/ping.php Ping an IP address
    *
    * @param string $str
    * @param integer $vbl verbose level
    */
    function conlog($str, $vbl=1)
    {
		static $busy = false;
		global $agi;
		
		
		if($this->agiconfig['debug'] != false)
		{
			if(!$busy) // no conlogs inside conlog!!!
			{
			  $busy = true;          
			  $agi->verbose($str, $vbl);
			  $busy = false;
			}
		}
    }


	/* 
	 * Function to create a menu to select the language
	 */
	function play_menulanguage ($agi){	
	
		// MENU LANGUAGE
		if ($this->agiconfig['play_menulanguage']==1){
			$prompt_menulang = $this->agiconfig['file_conf_enter_menulang'];
			$res_dtmf = $agi->get_data($prompt_menulang, 1500, 1);
			if ($this->agiconfig['debug']>=1)   $agi->verbose('line:'.__LINE__."RES Menu Language DTMF : ".$res_dtmf ["result"]);																											
			$this -> languageselected = $res_dtmf ["result"];
			
			if  ($this->languageselected=="2")
				$language = 'es';
			elseif ($this->languageselected=="3")
				$language = 'fr';
			else
				$language = 'en';
			
			$agi -> set_variable('LANGUAGE()', $language);
			$this -> write_log("[SET LANGUAGE() $language]");
			
		}elseif (strlen($this->agiconfig['force_language'])==2){
		
			if ($this->agiconfig['debug']>=1)   $agi->verbose('line:'.__LINE__."FORCE LANGUAGE : ".$this->agiconfig['force_language']);	
			$this->languageselected = 1;
			$language = strtolower($this->agiconfig['force_language']);
			$agi -> set_variable('LANGUAGE()', $language);
			$this -> write_log("[SET LANGUAGE() $language]");
			
		}
	}
	
	
	
	/*
	 * intialize evironement variables from the agi values
	 */
	function get_agi_request_parameter($agi){
	
		$this->CallerID 	= $agi->request['agi_callerid'];
		$this->channel		= $agi->request['agi_channel'];
		$this->uniqueid		= $agi->request['agi_uniqueid'];
		$this->accountcode	= $agi->request['agi_accountcode'];
		//$this->dnid		= $agi->request['agi_dnid'];
		$this->dnid		= $agi->request['agi_extension'];
		
		//Call function to find the cid number
		$this -> isolate_cid();
		
		if ($this->agiconfig['debug']>=1) 
			$agi->verbose('line:'.__LINE__.' get_agi_request_parameter = '.$this->CallerID.' ; '.$this->channel.' ; '.$this->uniqueid.' ; '.$this->accountcode.' ; '.$this->dnid);

	}
	
	

	/*
	 *	function to find the cid number
	 */
	function isolate_cid(){
		
		$pos_lt = strpos($this->CallerID, '<');
		$pos_gt = strpos($this->CallerID, '>');
			
		if (($pos_lt !== false) && ($pos_gt !== false)){
		   $len_gt = $pos_gt - $pos_lt - 1;
		   $this->CallerID = substr($this->CallerID,$pos_lt+1,$len_gt);
		}
	}
	
	
	/*
	 *	function would set when the card is used or when it release
	 */
	function callingcard_acct_start_inuse($agi, $inuse){
		
		if ($inuse)		$QUERY = "UPDATE cc_card SET inuse=inuse+1 WHERE username='".$this->username."'";
		else 			$QUERY = "UPDATE cc_card SET inuse=inuse-1 WHERE username='".$this->username."'";

		if ($this->agiconfig['debug']>=1)  $agi->verbose('line:'.__LINE__.' - '.$QUERY);
		$this->write_log("[Start: $QUERY]");
		$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
				
		return 0;
	}
	
	
	
	/**
	 *	Function callingcard_ivr_authorize : check the dialed/dialing number and play the time to call
	 *
	 *  @param object $agi
     *  @param float $credit
     *  @return 1 if Ok ; -1 if error
	**/
	function callingcard_ivr_authorize($agi, &$RateEngine, $try_num){
		$res=0;
			
			
		/************** 	ASK DESTINATION 	******************/
		$prompt_enter_dest = $this->agiconfig['file_conf_enter_destination'];
			
	
	
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '. $this->agiconfig['use_dnid']." && ".in_array ($this->dnid, $this->agiconfig['no_auth_dnid'])." && ".strlen($this->dnid)."&& $try_num");
			
		// CHECK IF USE_DNID IF NOT GET THE DESTINATION NUMBER
		if ($this->agiconfig['use_dnid']==1 && !in_array ($this->dnid, $this->agiconfig['no_auth_dnid']) && strlen($this->dnid)>2 && $try_num==0){
			$this->destination = $this->dnid;
		}else{
			$res_dtmf = $agi->get_data($prompt_enter_dest, 6000, 20);
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."RES DTMF : ".$res_dtmf ["result"]);
			$this->destination = $res_dtmf ["result"];
		}
		
		//REDIAL FIND THE LAST DIALED NUMBER (STORED IN THE DATABASE)
		if (strlen($this->destination)<=2 && is_numeric($this->destination) && $this->destination>=0){

			$QUERY = "SELECT phone FROM cc_speeddial WHERE id_cc_card='".$this->id_card."' AND speeddial='".$this->destination."'";
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);										
			$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);
			if( is_array($result))	$this->destination = $result[0][0];		
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."REDIAL : DESTINATION ::> ".$this->destination);
			$this -> write_log("[REDIAL : DTMF DESTINATION ::> ".$this->destination."]");		
		}
			
		// FOR TESTING : ENABLE THE DESTINATION NUMBER
		if ($this->CC_TESTING) $this->destination="011324885";
		if ($this->CC_TESTING) $this->destination="4455555";
			
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."DESTINATION ::> ".$this->destination);					
		if ($this->removeinterprefix) $this->destination = $this -> apply_rules ($this->destination);			
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."APPLY_RULES DESTINATION ::> ".$this->destination);
		$this -> write_log("[DTMF DESTINATION ::> ".$this->destination."]");
			
		// TRIM THE "#"s IN THE END, IF ANY
		// usefull for SIP or IAX friends with "use_dnid" when their device sends also the "#"
		// it should be safe for normal use
		$this->destination = rtrim($this->destination, "#");

		// SAY BALANCE
		// this is hardcoded for now but we might have a setting in a2billing.conf for the combination
		if ($this->destination=='*0'){
			$this -> write_log("[SAY BALANCE ::> ".$this->credit."]");
			$this -> fct_say_balance ($agi, $this->credit);
			return -1;
		}
			
		//REDIAL FIND THE LAST DIALED NUMBER (STORED IN THE DATABASE)
		if ($this->destination=='*1'){
			$this->destination = $this->redial;
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."REDIAL : DESTINATION ::> ".$this->destination);
			$this -> write_log("[REDIAL : DTMF DESTINATION ::> ".$this->destination."]");		
		}
		
					
			
		if ($this->destination<=0){
			$prompt = "prepaid-invalid-digits";
			// do not play the error message if the destination number is not numeric
			// because most probably it wasn't entered by user (he has a phone keypad remember?)
			// it helps with using "use_dnid" and extensions.conf routing
			if (is_numeric($this->destination)) $agi-> stream_file($prompt, '#');
			return -1;
		}
	
		// LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
		$resfindrate = $RateEngine->rate_engine_findrates($this, $this->destination,$this->tariff);
		if ($resfindrate==0){
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."ERROR ::> RateEngine didnt succeed to match the dialed number over the ratecard (Please check : id the ratecard is well create ; if the removeInter_Prefix is set according to your prefix in the ratecard ; if you hooked the ratecard to the tariffgroup)");
		}else{
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."OK - RESFINDRATE::> ".$resfindrate);
		}
			
			
		// IF DONT FIND RATE
		if ($resfindrate==0){
			$prompt="prepaid-dest-unreachable";
			$agi-> stream_file($prompt, '#');
			return -1;
		}
						
		/*$rate=$result[0][0];
		if ($rate<=0){
				//$prompt="prepaid-dest-blocked";
				$prompt="prepaid-dest-unreachable";
				continue;
		}*/
						
						
		// CHECKING THE TIMEOUT					
		$res_all_calcultimeout = $RateEngine->rate_engine_all_calcultimeout($this, $this->credit);
		
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."RES_ALL_CALCULTIMEOUT ::> $res_all_calcultimeout");
		if (!$res_all_calcultimeout){							
			$prompt="prepaid-no-enough-credit";
			$agi-> stream_file($prompt, '#');
			return -1;
		}
						
						
		// calculate timeout
		//$this->timeout = intval(($this->credit * 60*100) / $rate);  // -- RATE is millime cents && credit is 1cents
	
		$this->timeout = $RateEngine-> ratecard_obj[0]['timeout'];
		// set destination and timeout
		// say 'you have x minutes and x seconds'
		$minutes = intval($this->timeout / 60);
		$seconds = $this->timeout % 60;
			
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."TIMEOUT::> ".$this->timeout."  : minutes=$minutes - seconds=$seconds");
		if (!($minutes>0)){							
			$prompt="prepaid-no-enough-credit";
			$agi-> stream_file($prompt, '#');
			return -1;
		}
			
		if ($this->agiconfig['say_rateinitial']==1){
			$this -> fct_say_rate ($agi, $RateEngine->ratecard_obj[0][12] * 100);   // say rate in cents
		}
		
		if ($this->agiconfig['say_timetocall']==1){
			$agi-> stream_file('prepaid-you-have', '#');			
			if ($minutes>0){
				$agi->say_number($minutes);
				if ($minutes==1){
					$agi-> stream_file('prepaid-minute', '#');
				}else{
					$agi-> stream_file('prepaid-minutes', '#');
				}
			}
			if ($seconds>0){
				if ($minutes>0) $agi-> stream_file('vm-and', '#');
					
				$agi->say_number($seconds);
				if ($seconds==1){
					$agi-> stream_file('prepaid-second', '#');
				}else{
					$agi-> stream_file('prepaid-seconds', '#');
				}
			}
		}
		return 1;
	}
	
	
	
	/**
	 *	Function call_sip_iax_buddy : make the Sip/IAX free calls
	 *
	 *  @param object $agi
	 *  @param object $RateEngine
     *  @param integer $try_num
     *  @return 1 if Ok ; -1 if error
	**/
	function call_sip_iax_buddy($agi, &$RateEngine, $try_num){
		$res=0;
		if ( ($this->agiconfig['use_dnid']==1) && (!in_array ($this->dnid, $this->agiconfig['no_auth_dnid'])) && 
			(strlen($this->dnid)>2 ))
		{								
			$this->destination = $this->dnid;			
		
		}else{			
			$res_dtmf = $agi->get_data('prepaid-sipiax-enternumber', 6000, $this->agiconfig['len_aliasnumber'], '#');			
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."RES DTMF : ".$res_dtmf ["result"]);
			$this->destination = $res_dtmf ["result"];		
						
			if ($this->destination<=0){
				return -1;
			}
		}
			
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."SIP o IAX DESTINATION : ".$this->destination);
			
		$sip_buddies = 0; $iax_buddies = 0;
			
			
		//$QUERY =  "SELECT name FROM cc_iax_buddies WHERE name='".$this->destination."'";
		$QUERY = "SELECT name FROM cc_iax_buddies, cc_card WHERE cc_iax_buddies.name=cc_card.username AND useralias='".$this->destination."'";			
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);										
		$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);		
		
		if( is_array($result)){	$iax_buddies = 1; $destiax=$result[0][0];}
		
			
		//$QUERY =  "SELECT name FROM cc_sip_buddies WHERE name='".$this->destination."'";
		$QUERY = "SELECT name FROM cc_sip_buddies, cc_card WHERE cc_sip_buddies.name=cc_card.username AND useralias='".$this->destination."'";						
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);										
		$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
		if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);		
		
		if( is_array($result)){	$sip_buddies = 1; $destsip=$result[0][0];}
			
		if (!$sip_buddies && !$iax_buddies){
			$agi-> stream_file('prepaid-sipiax-num-nomatch', '#');				
			return -1;
		}
			
		if ($this -> CC_TESTING) $this->destination="kphone";

				
		for ($k=0;$k< $sip_buddies+$iax_buddies;$k++){
			if ($k==0 && $sip_buddies){ $this->tech = 'SIP'; $this->destination= $destsip; }
			else{ $this->tech = 'IAX2'; $this->destination = $destiax; }
				
			if ($this->agiconfig['record_call'] == 1){
				$myres = $agi->exec("MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
				$this -> write_log("EXEC MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
			}
			
			$agi->set_callerid($this->useralias);
			if ($this->agiconfig['debug']>=1) $agi->verbose("[EXEC SetCallerID : ".$this->useralias."]");
				
			$dialparams = $this->agiconfig['dialcommand_param_sipiax_friend'];
			$dialstr = $this->tech."/".$this->destination.$dialparams;

			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."app_callingcard sip/iax friend: Dialing '$dialstr' ".$this->tech." Friend.\n");
		
			//# Channel: technology/number@ip_of_gw_to PSTN
			// Dial(IAX2/guest@misery.digium.com/s@default) 
			$myres = $agi->exec("DIAL $dialstr");
			$this -> write_log("DIAL");
		
			$answeredtime = $agi->get_variable("ANSWEREDTIME");
			$answeredtime = $answeredtime['data'];
			$dialstatus = $agi->get_variable("DIALSTATUS");
			$dialstatus = $dialstatus['data'];
				
				
			if ($this->agiconfig['record_call'] == 1){
				// Monitor(wav,kiki,m)					
				$myres = $agi->exec("STOPMONITOR");
				$this -> write_log("EXEC StopMonitor (".$this->uniqueid."-".$this->cardnumber.")");
			}
				
			$this -> write_log("[".$this->tech." Friend][K=$k]:[ANSWEREDTIME=".$answeredtime."-DIALSTATUS=".$dialstatus."]");
				
			//# Ooh, something actually happend! 
			if ($dialstatus  == "BUSY") {										
				$answeredtime=0;
				$agi-> stream_file('prepaid-isbusy', '#');
			} elseif ($this->dialstatus == "NOANSWER") {
				$answeredtime=0;
				$agi-> stream_file('prepaid-noanswer', '#');
			} elseif ($dialstatus == "CANCEL") {
				$answeredtime=0;
			} elseif ($dialstatus == "ANSWER") {
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."-> dialstatus : $dialstatus, answered time is ".$answeredtime." \n");											
			} elseif ($k+1 == $sip_buddies+$iax_buddies){
				$prompt="prepaid-dest-unreachable";
				$agi-> stream_file($prompt, '#');
			}
								
			if (($dialstatus  == "CHANUNAVAIL") || ($dialstatus  == "CONGESTION"))	continue;
				
			if ($answeredtime >0){ 
				$this -> write_log("[CC_RATE_ENGINE_UPDATESYSTEM: usedratecard K=$K - (answeredtime=$answeredtime :: dialstatus=$dialstatus :: cost=$cost)]");
				$QUERY = "INSERT INTO cc_call (uniqueid,sessionid,username,nasipaddress,starttime,sessiontime, calledstation, ".						
					" terminatecause, stoptime, calledrate, sessionbill, calledcountry, calledsub, destination, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax) VALUES ".
					"('".$this->uniqueid."', '".$this->channel."',  '".$this->username."', '".$this->hostname."',";
				if ($this->config["database"]['dbtype'] == "postgres"){
					$QUERY .= " CURRENT_TIMESTAMP - interval '$answeredtime seconds' ";
				}else{
					$QUERY .= " CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND ";
				}						
				$QUERY .= ", '$answeredtime', '".$this->destination."', '$dialstatus', now(), '0', '0', ".
					" '".$this->countrycode."', '".$this->subcode."', '".$this->tech." CALL', '0', '0', '0', '0', $this->CallerID, '1' )";
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
				$this -> write_log("[CC_asterisk_stop 1.1: SQL: $QUERY]");
				$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
				$this -> write_log("[CC_asterisk_stop 1.1: SQL: DONE - result=$result]");
				return 1;
			}
		}
		return -1;
	
	}
	
	
	/**
	 *	Function call_did 
	 *
	 *  @param object $agi
	 *  @param object $RateEngine     
	 *  @param object $listdestination
	 	cc_did.id, cc_did_destination.id, billingtype, cc_did.id_trunk,	destination, cc_did.id_trunk, voip_call
		
     *  @return 1 if Ok ; -1 if error
	**/
	function call_did($agi, &$RateEngine, $listdestination){
		$res=0;

		if ($this -> CC_TESTING) $this->destination="kphone";
		$this->agiconfig['say_balance_after_auth']=0;
		$this->agiconfig['say_timetocall']=0;
		
		
		if (($listdestination[0][2]==0) || ($listdestination[0][2]==2)) $doibill = 1;
		else $doibill = 0;

		$callcount=0;
		foreach ($listdestination as $inst_listdestination){
			$callcount++;
			
		
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[A2Billing] DID call friend: FOLLOWME=$callcount (cardnumber:".$inst_listdestination[6]."|destination:".$inst_listdestination[4]."|tariff:".$inst_listdestination[3].")\n");
			
			$this->agiconfig['cid_enable']= 0;
			$this->accountcode = $inst_listdestination[6];
			$this->tariff = $inst_listdestination[3];
			$this->destination = $inst_listdestination[4];
			$this->username = $inst_listdestination[6];
			
			
			// MAKE THE AUTHENTICATION TO GET ALL VALUE : CREDIT - EXPIRATION - ...
			if ($this -> callingcard_ivr_authenticate($agi)!=0){
			
				$this -> write_log("AUTHENTICATION FAILS !!!");
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[A2Billing] DID call friend: AUTHENTICATION FAILS !!!\n");
			}else{				
				// CHECK IF DESTINATION IS SET
				if (strlen($inst_listdestination[4])==0) continue;
					
				// IF VOIP CALL
				if ($inst_listdestination[5]==1){
					
					// RUN MONITOR TO RECORD CALL
					if ($this->agiconfig['record_call'] == 1){
						$myres = $agi->exec("MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
						$this -> write_log("EXEC MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
					}
						
					$dialparams = $this->agiconfig['dialcommand_param_sipiax_friend'];
					$dialstr = $inst_listdestination[4].$dialparams;
			
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[A2Billing] DID call friend: Dialing '$dialstr' Friend.\n");
				
					//# Channel: technology/number@ip_of_gw_to PSTN
					// Dial(IAX2/guest@misery.digium.com/s@default) 
					// DIAL OUT
					$myres = $agi->exec("DIAL $dialstr");
					$this -> write_log("DIAL");
				
					$answeredtime = $agi->get_variable("ANSWEREDTIME");
					$answeredtime = $answeredtime['data'];
					$dialstatus = $agi->get_variable("DIALSTATUS");
					$dialstatus = $dialstatus['data'];
						
						
					if ($this->agiconfig['record_call'] == 1){				
						$myres = $agi->exec("STOPMONITOR");
						$this -> write_log("EXEC StopMonitor (".$this->uniqueid."-".$this->cardnumber.")");
					}
						
					$this -> write_log("[".$inst_listdestination[4]." Friend][followme=$callcount]:[ANSWEREDTIME=".$answeredtime."-DIALSTATUS=".$dialstatus."]");
						
						
					//# Ooh, something actually happend! 
					if ($dialstatus  == "BUSY") {
						$answeredtime=0;
						$agi-> stream_file('prepaid-isbusy', '#');
						// FOR FOLLOWME IF THERE IS MORE WE PASS TO THE NEXT ONE OTHERWISE WE NEED TO LOG THE CALL MADE
						if (count($listdestination)>$callcount) continue;
					} elseif ($this->dialstatus == "NOANSWER") {
						$answeredtime=0;
						$agi-> stream_file('prepaid-noanswer', '#');
						// FOR FOLLOWME IF THERE IS MORE WE PASS TO THE NEXT ONE OTHERWISE WE NEED TO LOG THE CALL MADE
						if (count($listdestination)>$callcount) continue;
					} elseif ($dialstatus == "CANCEL") {
						$answeredtime=0;
						// FOR FOLLOWME IF THERE IS MORE WE PASS TO THE NEXT ONE OTHERWISE WE NEED TO LOG THE CALL MADE
						if (count($listdestination)>$callcount) continue;
					} elseif ($dialstatus == "ANSWER") {
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[A2Billing] DID call friend: dialstatus : $dialstatus, answered time is ".$answeredtime." \n");
					} elseif (($dialstatus  == "CHANUNAVAIL") || ($dialstatus  == "CONGESTION")) {
						$answeredtime=0;
						// FOR FOLLOWME IF THERE IS MORE WE PASS TO THE NEXT ONE OTHERWISE WE NEED TO LOG THE CALL MADE
						if (count($listdestination)>$callcount) continue;
					} else{
						$agi-> stream_file('prepaid-noanswer', '#');
						// FOR FOLLOWME IF THERE IS MORE WE PASS TO THE NEXT ONE OTHERWISE WE NEED TO LOG THE CALL MADE
						if (count($listdestination)>$callcount) continue;
					}
									
						
						
					if ($answeredtime >0){ 
							
						$this -> write_log("[DID CALL - LOG CC_CALL: FOLLOWME=$callcount - (answeredtime=$answeredtime :: dialstatus=$dialstatus :: cost=$cost)]");
							
						$QUERY = "INSERT INTO cc_call (uniqueid,sessionid,username,nasipaddress,starttime,sessiontime, calledstation, ".						
							" terminatecause, stoptime, calledrate, sessionbill, calledcountry, calledsub, destination, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax) VALUES ".
							"('".$this->uniqueid."', '".$this->channel."',  '".$this->username."', '".$this->hostname."',";
						if ($this->config["database"]['dbtype'] == "postgres"){
							$QUERY .= " CURRENT_TIMESTAMP - interval '$answeredtime seconds' ";
						}else{
							$QUERY .= " CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND ";
						}						
						$QUERY .= ", '$answeredtime', '".$inst_listdestination[4]."', '$dialstatus', now(), '0', '0', ".
							" '".$this->countrycode."', '".$this->subcode."', 'DID CALL', '0', '0', '0', '0', $this->CallerID, '3' )";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
						$this -> write_log("[DID CALL - LOG CC_CALL: SQL: $QUERY]");
						$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
							
						// CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id							
						$QUERY = "UPDATE cc_did SET secondusedreal = secondusedreal + $answeredtime WHERE id='".$inst_listdestination[0]."'";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
						$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
							
						$QUERY = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + $answeredtime WHERE id='".$inst_listdestination[1]."'";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
						$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
							
						return 1;
					}			
					
					// ELSEIF NOT VOIP CALL
					}else{
					
						$this->agiconfig['use_dnid']=1;
						$this->agiconfig['say_timetocall']=0;
						
						$this->dnid = $this->destination = $inst_listdestination[4];
						if ($this->CC_TESTING) $this->dnid = $this->destination="011324885";
						
						
						if ($this -> callingcard_ivr_authorize($agi, $RateEngine, 0)==1){
								
							// PERFORM THE CALL	
							$result_callperf = $RateEngine->rate_engine_performcall ($agi, $this -> destination, $this);
							if (!$result_callperf) {
								$prompt="prepaid-noanswer.gsm"; 
								$agi-> stream_file($prompt, '#');
								continue;
							}
								 
							if (($RateEngine->dialstatus == "NOANSWER") || ($RateEngine->dialstatus == "CANCEL") || ($RateEngine->dialstatus == "BUSY") || ($RateEngine->dialstatus == "CHANUNAVAIL") || ($RateEngine->dialstatus == "CONGESTION")) continue;
								
							// INSERT CDR  & UPDATE SYSTEM
							$RateEngine->rate_engine_updatesystem($this, $agi, $this-> destination, $doibill, 1);
							// CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id							
							$QUERY = "UPDATE cc_did SET secondusedreal = secondusedreal + ".$RateEngine->answeredtime." WHERE id='".$inst_listdestination[0]."'";
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
							$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
								
							$QUERY = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + ".$RateEngine->answeredtime." WHERE id='".$inst_listdestination[1]."'";
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
							$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
								
							// THEN STATUS IS ANSWER
							break;
						}

					}
			} // END IF AUTHENTICATE
		}// END FOR

	}
	
	
	/**
	 *	Function to play the balance 
	 * 	format : "you have 100 dollars and 28 cents"
	 *
	 *  @param object $agi
     *  @param float $credit
     *  @return nothing
	**/
	function fct_say_balance ($agi, $credit){
				
		global $currencies_list;
		
		
		if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency'])==3) $this->currency=$this->agiconfig['agi_force_currency'];
		
		
		if (!isset($currencies_list[strtoupper($this->currency)][2]) || !is_numeric($currencies_list[strtoupper($this->currency)][2])) $mycur = 1;
		else $mycur = $currencies_list[strtoupper($this->currency)][2];
		$credit_cur = $credit / $mycur;				
		
		list($units,$cents)=split('[.]', $credit_cur);
		if (strlen($cents)>2) $cents=substr($cents,0,2);
		if ($units=='') $units=0;
		if ($cents=='') $cents=0;
		elseif (strlen($cents)==1) $cents.= '0';
		
		if (isset($this->agiconfig['currency_association_internal'][strtolower($this->currency)])){
			$unit_audio = $this->agiconfig['currency_association_internal'][strtolower($this->currency)];
		}else{
			$unit_audio = $this->agiconfig['currency_association_internal']['all'];
		}				
		$cent_audio = 'prepaid-cents';
		
		
		// say 'you have x dollars and x cents'
		$agi-> stream_file('prepaid-you-have', '#');
		
		if ($units==0 && $cents==0){					
			$agi->say_number(0);					
			$agi-> stream_file($unit_audio, '#');
		}else{
			if ($units >= 1){
				$agi->say_number($units);
				$agi-> stream_file($unit_audio, '#');
			}
			
			if ($units > 0 && $cents > 0){
				$agi-> stream_file('vm-and', '#');
			}
			if ($cents>0){
				$agi->say_number($cents);
				$agi-> stream_file($cent_audio, '#');
			}
		}
	}
	

	/**
	 *      Function to play the rate in cents
	 *      format : "7 point 5 cents per minute"
	 *
	 *  @param object $agi
	 *  @param float $rate
	 *  @return nothing
	 **/
	function fct_say_rate ($agi, $rate){

		$this -> write_log("[SAY RATE ::> ".$rate."]");

		global $currencies_list;

		if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency'])==3) $this->currency=$this->agiconfig['agi_force_currency'];

		if (!isset($currencies_list[strtoupper($this->currency)][2]) || !is_numeric($currencies_list[strtoupper($this->currency)][2])) $mycur = 1;
		else $mycur = $currencies_list[strtoupper($this->currency)][2];
		$rate_cur = $rate / $mycur;
		$cents = intval($rate_cur);
		$units = round(($rate_cur - $cents) * 1E4);
		while ($units != 0 && $units % 10 == 0) $units /= 10;

		// say 'the rate is'
		//$agi->stream_file('the-rate-is');

		$agi->say_number($cents);
		if ($units > 0) {
			$agi->stream_file('point');
			$agi->say_digits($units);
		}
		$agi->stream_file('cents-per-minute');
	}

	
	/*
	 * Function to generate a cardnumber
	 */	 
	function MDP()
	{
		$chrs = $this->agiconfig['len_cardnumber'];  
		$pwd = "";
		 mt_srand ((double) microtime() * 1000000);
		 while (strlen($pwd)<$chrs)
		 {
			$chr = chr(mt_rand (0,255));
			if (eregi("^[0-9]$", $chr))
				$pwd = $pwd.$chr;
		 };
		 return $pwd;
	}

	
	
	
	
	/*
	 * Function apply_rules to the phonenumber : Remove internation prefix
	 */	
	function apply_rules ($phonenumber){
						
		// BEGIN
		if (substr($phonenumber,0,3)=="011"){
			$this->myprefix='011';
			return substr($phonenumber,3);		
		}	
		if (substr($phonenumber,0,1)=="1"){
			$this->myprefix='1';
			return $phonenumber;		
		}
		if (substr($phonenumber,0,2)=="00"){
			$this->myprefix='00';
			return substr($phonenumber,2);		
		}
		if (substr($phonenumber,0,2)=="09"){
			$this->myprefix='09';
			return substr($phonenumber,2);		
		}
			
		$this->myprefix='';
		return $phonenumber;
		// END
		

		// THIS PART IS DEPRECIATE : IT WAS DONE TO MANAGE THE CANADIAN LOCAL CALL 
		// (NOT USEFUL ANYMORE AS WE CAN MANAGE THIS FROM THE RATECARD PROPERTIES)
		
		/*
		// EUROPE CALL TO THE CANADIAN SYSTEM
		if (substr($phonenumber,0,2)=="00"){
			$this->myprefix='011';
			return "011".substr($phonenumber,2);		
		}		
		// CALL TO US / OR US CALL IN CANADA
		if (substr($phonenumber,0,1)=="1"){
			$this->myprefix='';
			return "011".$phonenumber;		
		}
		
		// LOCAL CALL WITH 1
		$canada[]="1403";	$canada[]="1780";	$canada[]="1250";	$canada[]="1604";	$canada[]="1778";	
		$canada[]="1204";   $canada[]="1506";	$canada[]="1709";	$canada[]="1902";	$canada[]="1289";  $canada[]="1416";	
		$canada[]="1519";	$canada[]="1613";	$canada[]="1647";	$canada[]="1705";	$canada[]="1807";	$canada[]="1905";	
		$canada[]="1418";	$canada[]="1450";	$canada[]="1514";	$canada[]="1819";	$canada[]="1306";	$canada[]="1867";
		
		// LOCAL CALL WITHOUT 1
		$localcanada[]="403";	$localcanada[]="780";	$localcanada[]="250";	$localcanada[]="604";	$localcanada[]="778";	
		$localcanada[]="204";   $localcanada[]="506";	$localcanada[]="709";	$localcanada[]="902";	$localcanada[]="289";  $localcanada[]="416";	
		$localcanada[]="519";	$localcanada[]="613";	$localcanada[]="647";	$localcanada[]="705";	$localcanada[]="807";	$localcanada[]="905";	
		$localcanada[]="418";	$localcanada[]="450";	$localcanada[]="514";	$localcanada[]="819";	$localcanada[]="306";	$localcanada[]="867";
		
		foreach ($localcanada as $pn){
			if ($pn == substr($phonenumber,0,3)){
				$this->myprefix='';
				return "011"."1".$phonenumber;
			}
		}
		
		// NO CHANGE
		$this->myprefix='011';
		return $phonenumber;*/
	}
	
	
	/*
	 * Function callingcard_cid_sanitize : Ensure the caller is allowed to use their claimed CID.
	 * Returns: clean CID value, possibly empty.
	 */	
	function callingcard_cid_sanitize($agi){
			
			$this -> write_log("[CID_SANITIZE - CID:".$this->CallerID."]");
			if ($this->agiconfig['debug']>=1) $agi->verbose("[CID_SANITIZE - CID:".$this->CallerID."]");

			if (strlen($this->CallerID)==0) {
				if ($this->agiconfig['debug']>=1) $agi->verbose("[CID_SANITIZE - CID: NO CID]");
				return '';
			}
			$QUERY="";
			if($this->agiconfig['cid_sanitize']=="CID" || $this->agiconfig['cid_sanitize']=="BOTH"){
				$QUERY .=  "SELECT cc_callerid.cid ".
					  " FROM cc_callerid ".
					  " JOIN cc_card ON cc_callerid.id_cc_card=cc_card.id ".
					  " WHERE cc_card.username='".$this->cardnumber."' ";
				$QUERY .= "ORDER BY 1";
				if ($this->agiconfig['debug']>=1) $agi->verbose($QUERY);
				$result1 = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			}
			
			/*if($this->agiconfig['cid_sanitize']=="BOTH"){
				$QUERY .= " UNION ";
			}*/
			$QUERY="";
			if($this->agiconfig['cid_sanitize']=="DID" || $this->agiconfig['cid_sanitize']=="BOTH"){
				$QUERY .=  "SELECT cc_did.did ".
					  " FROM cc_did ".
					  " JOIN cc_did_destination ON cc_did_destination.id_cc_did=cc_did.id ".
					  " JOIN cc_card ON cc_did_destination.id_cc_card=cc_card.id ".
					  " WHERE cc_card.username='".$this->cardnumber."' ";
				$QUERY .= "ORDER BY 1";				
				if ($this->agiconfig['debug']>=1) $agi->verbose($QUERY);
				$result2 = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			}
			if (count($result1)>0 || count($result2)>0) 
				$result = array_merge($result1, $result2);
			
			if ($this->agiconfig['debug']>=1) $agi->verbose($result);

			if( !is_array($result)) {
				if ($this->agiconfig['debug']>=1) $agi->verbose("[CID_SANITIZE - CID: NO DATA]");
				return '';
			}
			for ($i=0;$i<count($result);$i++){
				if ($this->agiconfig['debug']>=3) $agi->verbose("[CID_SANITIZE - CID COMPARING: ".substr($result[$i][0],strlen($this->CallerID)*-1)." to ".$this->CallerID."]");
				if(substr($result[$i][0],strlen($this->CallerID)*-1)==$this->CallerID) {
				  if ($this->agiconfig['debug']>=1) $agi->verbose("[CID_SANITIZE - CID: ".$result[$i][0]."]");
					return $result[$i][0];
				}
			}
		if ($this->agiconfig['debug']>=1) $agi->verbose("[CID_SANITIZE - CID UNIQUE RESULT: ".$result[0][0]."]");
			return $result[0][0];
	}

	function callingcard_auto_setcallerid($agi){
	// AUTO SetCallerID 
		if ($this->agiconfig['auto_setcallerid']==1){
			if ( strlen($this->agiconfig['force_callerid']) >=1 ){
				$agi -> set_callerid($this->agiconfig['force_callerid']);
				if ($this->agiconfig['debug']>=1) $agi->verbose("[EXEC SetCallerID : ".$this->agiconfig['force_callerid']."]");
				$this -> write_log("EXEC SetCallerID ".$this->agiconfig['force_callerid']);
			}elseif ( strlen($this->CallerID) >=1 ){
				if ($this->agiconfig['debug']>=1) $agi->verbose("[REQUESTED SetCallerID : ".$this->CallerID."]");
				
      			// IF REQUIRED, VERIFY THAT THE CALLERID IS LEGAL
      			$cid_san=$this->CallerID;
				/*if ($this->agiconfig['cid_sanitize']=='DID' || $this->agiconfig['cid_sanitize']=='CID' || $this->agiconfig['cid_sanitize']=='BOTH') {
					$cid_san = $this -> callingcard_cid_sanitize($agi);
					$this -> write_log("[TRY : callingcard_cid_sanitize]");
					if ($this->agiconfig['debug']>=1) $agi->verbose('CALLERID SANITIZED: "'.$cid_san.'"');
				}*/
				if (strlen($cid_san)>0){
						$agi->set_callerid($cid_san);
						if ($this->agiconfig['debug']>=1) $agi->verbose("[EXEC SetCallerID : ".$cid_san."]");
						$this -> write_log("EXEC SetCallerID ".$cid_san);
				}else{
					if ($this->agiconfig['debug']>=1) $agi->verbose("[CANNOT SetCallerID : cid_san is empty]");
				}
			}
		}
	}
	
		
	function callingcard_ivr_authenticate($agi){
			
		$prompt='';
		$res=0;
		$retries=0;
		$language = 'en';
		$callerID_enable = $this->agiconfig['cid_enable'];
		
		
		//first try with the callerid authentication
		
		//$this->write_log("CID: ". $this->CallerID . "enable=" .$callerID_enable );
		if ($callerID_enable==1 && (strlen($this->CallerID)>0)){
			//&& is_numeric($this->CallerID) && $this->CallerID>0){
			$this -> write_log("[CID_ENABLE - CID_CONTROL - CID:".$this->CallerID."]");
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CID_ENABLE - CID_CONTROL - CID:".$this->CallerID."]");
				
			$QUERY =  "SELECT cc_callerid.cid, cc_callerid.id_cc_card, cc_callerid.activated, cc_card.credit, ".
				  " cc_card.tariff, cc_card.activated, cc_card.inuse, cc_card.simultaccess,  ".
				  " cc_card.typepaid, cc_card.creditlimit, cc_card.language, cc_card.username, removeinterprefix, cc_card.redial, ";
			if ($this->config["database"]['dbtype'] == "postgres"){
				  $QUERY .=  " enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), ";
			}else{
				  $QUERY .=  " enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), ";
			}

			$QUERY .=  " cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias  ".
			" FROM cc_callerid ".
			" LEFT JOIN cc_card ON cc_callerid.id_cc_card=cc_card.id ".
			" LEFT JOIN cc_tariffgroup ON cc_card.tariff=cc_tariffgroup.id ".
			" WHERE cc_callerid.cid=".$this->DBHandle->Quote($this->CallerID);
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
												
			$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);
				
			if( !is_array($result)) {
						
				if ($this->agiconfig['cid_auto_create_card']==1){
					
					for ($k=0;$k<=20;$k++){
						if ($k==20){ 
							$this -> write_log ( "ERROR : Impossible to generate a cardnumber not yet used!<br>Perhaps check the LEN_CARDNUMBER (value:".LEN_CARDNUMBER.")");
							$prompt="prepaid-auth-fail";
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
							$agi-> stream_file($prompt, '#');
							return -2;
						}
						$card_gen = MDP();
						//echo "SELECT username FROM card where username='$card_gen'<br>";
						$resmax = $this->DBHandle -> query("SELECT username FROM $FG_TABLE_NAME where username='$card_gen'");
						$numrow = $resmax -> numRows();
						if ($numrow!=0) continue;
						break;		
					}
					
					$ttcard = ($this->agiconfig['cid_auto_create_card_typepaid']=="POSTPAY") ? 1 : 0;
					// INSERT INTO cc_card (username, useralias, userpass, credit, language, tariff, activated, 
					// typepaid, creditlimit, inuse) VALUES ('123444','123444','123444','10.00','en','1','t','1','0','0');
					//CREATE A CARD AND AN INSTANCE IN CC_CARD
					$QUERY_FIELS = 'username, useralias, userpass, credit, language, tariff, activated, typepaid, creditlimit, inuse';
					$QUERY_VALUES = "'$card_gen', '$card_gen', '$card_gen', '".$this->agiconfig['cid_auto_create_card_credit']."', 'en', '".$this->agiconfig['cid_auto_create_card_tariffgroup']."', 't','$ttcard', '".$this->agiconfig['cid_auto_create_card_credit_limit']."', '0'";
					$result = $this->instance_table -> Add_table ($this->DBHandle, $QUERY_VALUES, $QUERY_FIELS, 'cc_card', 'id');
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CARDNUMBER: $card_gen]:[CARDID CREATED : $result]");
					$this -> write_log("[CARDNUMBER: $card_gen]:[CARDID CREATED : $result]");
							
								
					//CREATE A CARD AND AN INSTANCE IN CC_CALLERID
					$QUERY_FIELS = 'cid, id_cc_card';
					$QUERY_VALUES = "'".$this->CallerID."','$result'";
							
					$result = $this->instance_table -> Add_table ($this->DBHandle, $QUERY_VALUES, $QUERY_FIELS, 'cc_callerid');
					if (!$result){
						$this -> write_log("[CALLERID CREATION ERROR TABLE cc_callerid]");
						$prompt="prepaid-auth-fail";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
						$agi-> stream_file($prompt, '#');
						return -2;
					}
								
					$this->credit = $this->agiconfig['cid_auto_create_card_credit'];
					$this->tariff = $this->agiconfig['cid_auto_create_card_tariffgroup'];
					$this->active = 1;
					$isused = 0;
					$simultaccess = 0;
					$this->typepaid = $ttcard;
					$creditlimit = $this->agiconfig['cid_auto_create_card_credit_limit'];
					$language = 'en';
					$this->accountcode = $card_gen;
					if ($this->typepaid==1) $this->credit = $this->credit+$creditlimit;
				}else{
					
					$this -> write_log("[CID_CONTROL - STOP - NO CALLERID]");
							
					// $callerID_enable=1; -> we are checking later if the callerID/accountcode has been define if not ask for pincode
					if ($this->agiconfig['cid_askpincode_ifnot_callerid']==1) { $this->accountcode=''; $callerID_enable=0;}
								
					// REMOVE THE COMMAND BELOW IF YOU WANT TO STOP THE APP IF NO CALLERID IS AUTHENTICATE
					/*$prompt="prepaid-auth-fail";
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
					$agi->agi_exec("STREAM FILE $prompt #");
					$agi-> stream_file($prompt, '#');
					return -2;*/
							
				}
			}else{
				// We found a card for this callerID 
				
				$this->credit = $result[0][3];
				$this->tariff = $result[0][4];
				$this->active = $result[0][5];
				$isused = $result[0][6];
				$simultaccess = $result[0][7];
				$this->typepaid = $result[0][8];
				$creditlimit = $result[0][9];
				$language = $result[0][10];
				$this->accountcode = $result[0][11];
				$this->removeinterprefix = $result[0][12];
				$this->redial = $result[0][13];
					
				$this->enableexpire = $result[0][14];
				$this->expirationdate = $result[0][15];
				$this->expiredays = $result[0][16];
				$this->nbused = $result[0][17];
				$this->firstusedate = $result[0][18];
				$this->creationdate = $result[0][19];
				$this->currency = $result[0][20];
				$this->cardholder_lastname = $result[0][21];
				$this->cardholder_firstname = $result[0][22];
				$this->cardholder_email = $result[0][23];
				$this->cardholder_uipass = $result[0][24];
				$this->id_campaign  = $result[0][25];
				$this->id_card  = $result[0][26];
				$this->useralias = $result[0][27];
				
				if ($this->typepaid==1)
					$this->credit = $this->credit+$creditlimit;
				
				// CHECK IF CALLERID ACTIVATED
				if( $result[0][2] != "t" && $result[0][2] != "1" )
					$prompt = "prepaid-auth-fail";
						
				// CHECK credit > min_credit_2call / you have zero balance
				if( $this->credit < $this->agiconfig['min_credit_2call'] )
					$prompt = "prepaid-zero-balance";
				// CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
				if( $this->active != "t" && $this->active != "1" )
					$prompt = "prepaid-auth-fail";	// not expired but inactive.. probably not yet sold.. find better prompt
				// CHECK IF THE CARD IS USED
				if (($isused>0) && ($simultaccess!=1))
					$prompt="prepaid-card-in-use";
				// CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
				if ($this->enableexpire>0){
					if ($this->enableexpire==1  && $this->expirationdate!='00000000000000' && strlen($this->expirationdate)>5){
						// expire date						
						if (intval($this->expirationdate-time())<0) // CARD EXPIRED :(				
							$prompt = "prepaid-card-expired";	
							
					}elseif ($this->enableexpire==2  && $this->firstusedate!='00000000000000' && strlen($this->firstusedate)>5 && ($this->expiredays>0)){
						// expire days since first use			
						$date_will_expire = $this->firstusedate+(60*60*24*$this->expiredays);
						if (intval($date_will_expire-time())<0) // CARD EXPIRED :(
						$prompt = "prepaid-card-expired";
							
					}elseif ($this->enableexpire==3  && $this->creationdate!='00000000000000' && strlen($this->creationdate)>5 && ($this->expiredays>0)){
						// expire days since creation
						$date_will_expire = $this->creationdate+(60*60*24*$this->expiredays); 
						if (intval($date_will_expire-time())<0)	// CARD EXPIRED :(
							$prompt = "prepaid-card-expired";			
					}
				}
						
				if (strlen($prompt)>0){ 
					$agi-> stream_file($prompt, '#'); // Added because was missing the prompt 
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' prompt:'.strtoupper($prompt));
					$this -> write_log("[ERROR CHECK CARD : $prompt (cardnumber:".$this->cardnumber.")]");
										
					if ($prompt == "prepaid-zero-balance" && $this->agiconfig['notenoughcredit_cardnumber']==1) { 
						$this->accountcode=''; $callerID_enable=0;
						$this->agiconfig['cid_auto_assign_card_to_cid']=0;
						if ($this->agiconfig['notenoughcredit_assign_newcardnumber_cid']==1) $this -> ask_other_cardnumber=1;
					}else{
						return -2;
					}
				}
						
						
			}
		
		}
		
		// 		  -%-%-%-%-%-%-		CHECK IF WE CAN AUTHENTICATE THROUGH THE "ACCOUNTCODE" 	-%-%-%-%-%-%-
		
		$prompt_entercardnum= "prepaid-enter-pin-number";			
		if (strlen ($this->accountcode)>=1) {
			$this->username = $this -> cardnumber = $this->accountcode;
			for ($i=0;$i<=0;$i++){									 
					
				if ($callerID_enable!=1 || !is_numeric($this->CallerID) || $this->CallerID<=0){
					
					$QUERY =  "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, ";
					if ($this->config["database"]['dbtype'] == "postgres")					
						$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias FROM cc_card ";
					else
						$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias FROM cc_card ";
						 
						$QUERY .=  "LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY); 
																
					$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);
								
					if( !is_array($result)) {
						$prompt="prepaid-auth-fail";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
							$res = -2;
							break;
					}else{
						// 		  -%-%-%-	WE ARE GOING TO CHECK IF THE CALLERID IS CORRECT FOR THIS CARD	-%-%-%-
						if ($this->agiconfig['callerid_authentication_over_cardnumber']==1){
						
							if (!is_numeric($this->CallerID) && $this->CallerID<=0){
								$res = -2;
								break;
							}
							
							$QUERY = " SELECT cid, id_cc_card, activated FROM cc_callerid "
									." WHERE cc_callerid.cid='".$this->CallerID."' AND cc_callerid.id_cc_card='".$result[0][22]."'";
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
							
							$result_check_cid = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result_check_cid);
								
							if( !is_array($result_check_cid)) {
								$prompt="prepaid-auth-fail";
								if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
								$res = -2;
								break;
							}
						}					
					}
					
					$this->credit = $result[0][0];
					$this->tariff = $result[0][1];
					$this->active = $result[0][2];
					$isused = $result[0][3];
					$simultaccess = $result[0][4];
					$this->typepaid = $result[0][5];
					$creditlimit = $result[0][6];
					$language = $result[0][7];
					$this->removeinterprefix = $result[0][8];
					$this->redial = $result[0][9];
					$this->enableexpire = $result[0][10];
					$this->expirationdate = $result[0][11];
					$this->expiredays = $result[0][12];
					$this->nbused = $result[0][13];
					$this->firstusedate = $result[0][14];
					$this->creationdate = $result[0][15];
					$this->currency = $result[0][16];
					$this->cardholder_lastname = $result[0][17];
					$this->cardholder_firstname = $result[0][18];
					$this->cardholder_email = $result[0][19];
					$this->cardholder_uipass = $result[0][20];
					$this->id_campaign  = $result[0][21];
					$this->id_card  = $result[0][22];
					$this->useralias = $result[0][23];
							
					if ($this->typepaid==1) $this->credit = $this->credit+$creditlimit;
				}
							
				if (strlen($language)==2 && !($this->languageselected>=1)){								
					// SetLanguage is deprecated, please use Set(LANGUAGE()=language) instead.
					$agi -> set_variable('LANGUAGE()', $language);
					$this -> write_log("[SET LANGUAGE() $language]");
				}
						
				$this -> write_log("[credit=".$this->credit." :: tariff=".$this->tariff." :: active=".$this->active." :: isused=$isused :: simultaccess=$simultaccess :: typepaid=".$this->typepaid." :: creditlimit=$creditlimit :: language=$language]");
							
														
							
				$prompt = '';
				// CHECK credit > min_credit_2call / you have zero balance
				if( $this->credit < $this->agiconfig['min_credit_2call'] ) $prompt = "prepaid-zero-balance";
				// CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
				if( $this->active != "t" && $this->active != "1" ) 	$prompt = "prepaid-auth-fail";	// not expired but inactive.. probably not yet sold.. find better prompt
				// CHECK IF THE CARD IS USED
				if (($isused>0) && ($simultaccess!=1))	$prompt="prepaid-card-in-use";
				// CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
				if ($this->enableexpire>0){
					if ($this->enableexpire==1  && $this->expirationdate!='00000000000000' && strlen($this->expirationdate)>5){
						// expire date						
						if (intval($this->expirationdate-time())<0) // CARD EXPIRED :(				
						$prompt = "prepaid-card-expired";
					}elseif ($this->enableexpire==2  && $this->firstusedate!='00000000000000' && strlen($this->firstusedate)>5 && ($this->expiredays>0)){
					// expire days since first use			
						$date_will_expire = $this->firstusedate+(60*60*24*$this->expiredays);
						if (intval($date_will_expire-time())<0) // CARD EXPIRED :(
						$prompt = "prepaid-card-expired";
								
					}elseif ($this->enableexpire==3  && $this->creationdate!='00000000000000' && strlen($this->creationdate)>5 && ($this->expiredays>0)){
						// expire days since creation
						$date_will_expire = $this->creationdate+(60*60*24*$this->expiredays); 
						if (intval($date_will_expire-time())<0)	// CARD EXPIRED :(
							$prompt = "prepaid-card-expired";			
					}
				}
							
				if (strlen($prompt)>0){  
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' prompt:'.strtoupper($prompt));
					$this -> write_log("[ERROR CHECK CARD : $prompt (cardnumber:".$this->cardnumber.")]");
					$res = -2;
					break;
				}
							
			} // For end			
		}elseif ($callerID_enable==0){
		
			// 		  -%-%-%-%-%-%-		IF NOT PREVIOUS WE WILL ASK THE CARDNUMBER AND AUTHENTICATE ACCORDINGLY 	-%-%-%-%-%-%-				
			for ($retries = 0; $retries < 3; $retries++) {
				if (($retries>0) && (strlen($prompt)>0)){
					$agi-> stream_file($prompt, '#');
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
				}												
				
				if ($res < 0) {
					$res = -1;
					break;
				}
				
				$res = 0;
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."Requesting DTMF ::> Len-".$this->agiconfig['len_cardnumber']);
				$res_dtmf = $agi->get_data($prompt_entercardnum, 6000, $this->agiconfig['len_cardnumber']);
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."RES DTMF : ".$res_dtmf ["result"]);
				$this->cardnumber = $res_dtmf ["result"];
							
				if ($this->CC_TESTING) $this->cardnumber="2222222222";
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."CARDNUMBER ::> ".$this->cardnumber);
							
				if ( !isset($this->cardnumber) || strlen($this->cardnumber) == 0) {
					$prompt = "prepaid-no-card-entered";
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
					continue;
				}
							
				if ( strlen($this->cardnumber) != $this->agiconfig['len_cardnumber']) {
					$prompt = "prepaid-invalid-digits";
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
					continue;
				}
				$this->username = $this->cardnumber;
								
				$QUERY =  "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, ";
				if ($this->config["database"]['dbtype'] == "postgres"){
					$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id, cc_card.id_campaign, cc_card.id, useralias FROM cc_card "."LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
				}else{
					$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id, cc_card.id_campaign, cc_card.id, useralias FROM cc_card "."LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
				}
				
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
															
				$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result);
							
				if( !is_array($result)) {
					$prompt="prepaid-auth-fail";
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
					continue;
				}else{
					// 		  -%-%-%-	WE ARE GOING TO CHECK IF THE CALLERID IS CORRECT FOR THIS CARD	-%-%-%-
					if ($this->agiconfig['callerid_authentication_over_cardnumber']==1){
					
						if (!is_numeric($this->CallerID) && $this->CallerID<=0){
							$prompt="prepaid-auth-fail";
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
							continue;
						}
							
						$QUERY = " SELECT cid, id_cc_card, activated FROM cc_callerid "
								." WHERE cc_callerid.cid='".$this->CallerID."' AND cc_callerid.id_cc_card='".$result[0][23]."'";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$QUERY);
						
						$result_check_cid = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.$result_check_cid);
							
						if( !is_array($result_check_cid)) {
							$prompt="prepaid-auth-fail";
							if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
							continue;
						}
					}
				}
			   
				
				$this->credit = $result[0][0];
				$this->tariff = $result[0][1];
				$this->active = $result[0][2];
				$isused = $result[0][3];
				$simultaccess = $result[0][4];
				$this->typepaid = $result[0][5];
				$creditlimit = $result[0][6];
				$language = $result[0][7];
				$this->removeinterprefix = $result[0][8];
				$this->redial = $result[0][9];
				$this->enableexpire = $result[0][10];
				$this->expirationdate = $result[0][11];
				$this->expiredays = $result[0][12];
				$this->nbused = $result[0][13];
				$this->firstusedate = $result[0][14];
				$this->creationdate = $result[0][15];
				$this->currency = $result[0][16];
				$this->cardholder_lastname = $result[0][17];
				$this->cardholder_firstname = $result[0][18];
				$this->cardholder_email = $result[0][19];
				$this->cardholder_uipass = $result[0][20];
				$the_card_id = $result[0][21];
				$this->id_campaign  = $result[0][22];
				$this->id_card  = $result[0][23];
				$this->useralias = $result[0][24];
				
				if ($this->typepaid==1) $this->credit = $this->credit+$creditlimit;
				
				if (strlen($language)==2  && !($this->languageselected>=1)){					
					$agi -> set_variable('LANGUAGE()', $language);
					$this -> write_log("[SET LANGUAGE() $language]");
				}
				$prompt = '';
				// CHECK credit > min_credit_2call / you have zero balance
				if( $this->credit < $this->agiconfig['min_credit_2call'] ) $prompt = "prepaid-zero-balance";
				// CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
				if( $this->active != "t" && $this->active != "1" ) 	$prompt = "prepaid-auth-fail";	// not expired but inactive.. probably not yet sold.. find better prompt
				// CHECK IF THE CARD IS USED
				if (($isused>0) && ($simultaccess!=1))	$prompt="prepaid-card-in-use";
				// CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
				if ($this->enableexpire>0){
					if ($this->enableexpire==1  && $this->expirationdate!='00000000000000' && strlen($this->expirationdate)>5){
						// expire date						
						if (intval($this->expirationdate-time())<0) // CARD EXPIRED :(				
						$prompt = "prepaid-card-expired";	
									
					}elseif ($this->enableexpire==2  && $this->firstusedate!='00000000000000' && strlen($this->firstusedate)>5 && ($this->expiredays>0)){
						// expire days since first use			
						$date_will_expire = $this->firstusedate+(60*60*24*$this->expiredays);
						if (intval($date_will_expire-time())<0) // CARD EXPIRED :(
						$prompt = "prepaid-card-expired";
								
					}elseif ($this->enableexpire==3  && $this->creationdate!='00000000000000' && strlen($this->creationdate)>5 && ($this->expiredays>0)){
						// expire days since creation
						$date_will_expire = $this->creationdate+(60*60*24*$this->expiredays); 
						if (intval($date_will_expire-time())<0)	// CARD EXPIRED :(
						$prompt = "prepaid-card-expired";			
					}
				}
							
					
				//CREATE AN INSTANCE IN CC_CALLERID
				if ($this->agiconfig['cid_enable']==1 && $this->agiconfig['cid_auto_assign_card_to_cid']==1 && is_numeric($this->CallerID) && $this->CallerID>0 && $this -> ask_other_cardnumber!=1){
					$QUERY_FIELS = 'cid, id_cc_card';
					$QUERY_VALUES = "'".$this->CallerID."','$the_card_id'";
									
					if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[CREATE AN INSTANCE IN CC_CALLERID -  QUERY_VALUES:$QUERY_VALUES, QUERY_FIELS:$QUERY_FIELS]");
					$result = $this->instance_table -> Add_table ($this->DBHandle, $QUERY_VALUES, $QUERY_FIELS, 'cc_callerid');
									
					if (!$result){
						$this -> write_log("[CALLERID CREATION ERROR TABLE cc_callerid]");
						$prompt="prepaid-auth-fail";
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
						$agi-> stream_file($prompt, '#');
						return -2;
					}
				}
				
				//UPDATE THE CARD ASSIGN TO THIS CC_CALLERID								
				if ($this->agiconfig['notenoughcredit_assign_newcardnumber_cid']==1 && strlen($this->CallerID)>1 && $this -> ask_other_cardnumber==1){
					$this -> ask_other_cardnumber=0;																				
					$QUERY = "UPDATE cc_callerid SET id_cc_card='$the_card_id' WHERE cid='".$this->CallerID."'";								
					if ($this->agiconfig['debug']>=1)  $agi->verbose('line:'.__LINE__.' - '.$QUERY);
						$this->write_log("[Start update cc_callerid : $QUERY]");
						$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
					}								
							
							
					if (strlen($prompt)>0){  
						if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '.'line:'.__LINE__.' prompt:'.strtoupper($prompt));
						$this -> write_log("[ERROR CHECK CARD : $prompt (cardnumber:".$this->cardnumber.")]");
						$res = -2;
						break;
					}
				break;
			}//end for
		}else{
			$res = -2;
		}//end IF
		
		if (($retries < 3) && $res==0) {
			//ast_cdr_setaccount(chan, username);
						
			if ($this->agiconfig['say_balance_after_auth']==1){		
				if ($this->agiconfig['debug']>=1) $agi->verbose('line:'.__LINE__.' - '."[A2Billing] SAY BALANCE (".$this->agiconfig['say_balance_after_auth'].")\n");
				$this -> fct_say_balance ($agi, $this->credit);
			}
				
					
		} else if ($res == -2 ) {
			$agi-> stream_file($prompt, '#');
		} else {
			$res = -1;
		}
		
		return $res;
	}
	
	
	function callingcard_ivr_authenticate_light (&$error_msg){
		$res=0;
			
								
		$QUERY =  "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, ";
		if ($this->config["database"]['dbtype'] == "postgres")
			$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign FROM cc_card ";
		else
			$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign FROM cc_card ";
		
		$QUERY .=  "LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
			
		$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			
		if( !is_array($result)) {
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Authentication Failed !!!</b></font><br>';
			return 0;
		}
								   
		$this->credit = $result[0][0];
		$this->tariff = $result[0][1];
		$this->active = $result[0][2];
		$isused = $result[0][3];
		$simultaccess = $result[0][4];
		$this->typepaid = $result[0][5];
		$creditlimit = $result[0][6];
		$language = $result[0][7];
		$this->removeinterprefix = $result[0][8];
		$this->redial = $result[0][9];
		$this->enableexpire = $result[0][10];
		$this->expirationdate = $result[0][11];
		$this->expiredays = $result[0][12];
		$this->nbused = $result[0][13];
		$this->firstusedate = $result[0][14];
		$this->creationdate = $result[0][15];
		$this->currency = $result[0][16];
		$this->cardholder_lastname = $result[0][17];
		$this->cardholder_firstname = $result[0][18];
		$this->cardholder_email = $result[0][19];
		$this->cardholder_uipass = $result[0][20];
		$this->id_campaign  = $result[0][21];
									
		if ($this->typepaid==1) $this->credit = $this->credit+$creditlimit;
			
						
		// CHECK IF ENOUGH CREDIT TO CALL		
		if( $this->credit <= $this->agiconfig['min_credit_2call'] && $this -> typepaid==0){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Not enough credit to call !!!</b></font><br>';
			return 0;
		}
		// CHECK POSTPAY
		if( $this->typepaid==1 && $this->credit <= -$creditlimit && $creditlimit!=0){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Not enough credit to call !!!</b></font><br>';
			return 0;
		}
			
		// CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
		if( $this->active != "t" && $this->active != "1" ){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Card is not active!!!</b></font><br>';
			return 0;
		}
			
		// CHECK IF THE CARD IS USED
		if (($isused>0) && ($simultaccess!=1)){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Card is actually in use!!!</b></font><br>';
			return 0;
		}
			
		// CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
		if ($this->enableexpire>0){
			if ($this->enableexpire==1  && $this->expirationdate!='00000000000000' && strlen($this->expirationdate)>5){
				// expire date						
				if (intval($this->expirationdate-time())<0){ // CARD EXPIRED :(				
					$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Card have expired!!!</b></font><br>';
					return 0;	
				}
					
			}elseif ($this->enableexpire==2  && $this->firstusedate!='00000000000000' && strlen($this->firstusedate)>5 && ($this->expiredays>0)){
				// expire days since first use			
				$date_will_expire = $this->firstusedate+(60*60*24*$this->expiredays);
				if (intval($date_will_expire-time())<0){ // CARD EXPIRED :(				
				$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Card have expired!!!</b></font><br>';
				return 0;	
			}
		
			}elseif ($this->enableexpire==3  && $this->creationdate!='00000000000000' && strlen($this->creationdate)>5 && ($this->expiredays>0)){
				// expire days since creation
				$date_will_expire = $this->creationdate+(60*60*24*$this->expiredays); 
				if (intval($date_will_expire-time())<0){ // CARD EXPIRED :(				
					$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>Error : Card have expired!!!</b></font><br>';
					return 0;	
				}		
			}
		}
								
		return 1;
	}


	function DbConnect()
	{
		//require_once('DB.php'); // PEAR
		require_once('adodb/adodb.inc.php'); // AdoDB
		
		if ($this->config["database"]['dbtype'] == "postgres"){
			$datasource = 'pgsql://'.$this->config["database"]['user'].':'.$this->config["database"]['password'].'@'.$this->config["database"]['hostname'].'/'.$this->config["database"]['dbname'];
		}else{
			$datasource = 'mysql://'.$this->config["database"]['user'].':'.$this->config["database"]['password'].'@'.$this->config["database"]['hostname'].'/'.$this->config["database"]['dbname'];
		}
		//echo "VERBOSE \"$datasource\" \n";
		$this->DBHandle = NewADOConnection($datasource);
		if (!$this->DBHandle)
			return false;
	
		return true;
	}
	
	
	function DbDisconnect()
	{
		$this -> DBHandle -> disconnect();
	}
	

};

?>
