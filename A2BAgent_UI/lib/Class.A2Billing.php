<?php
/***************************************************************************
 *
 * Class.A2Billing.php : PHP A2Billing Functions for A2Billing
 * Written for PHP 4.x & PHP 5.X versions.
 *
 * A2Billing -- Asterisk billing solution.
 * Copyright (C) 2004, 2007 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.asterisk2billing.org for more information about
 * the A2Billing project. 
 * Please submit bug reports, patches, etc to <areski _atl_ gmail com>
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/


define('AST_CONFIG_DIR', '/etc/asterisk/'); 
define('DEFAULT_A2BILLING_CONFIG', AST_CONFIG_DIR . '/a2billing.conf');

// DEFINE STATUS FOR DEBUG
define ('VERBOSE',			1);
define ('WRITELOG',			2);			// 1 << 1

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
    * store the file name to store the logs
    *
    * @var string
    * @access public
    */
	var $log_file = '';
	
	
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
	
	var $ivr_voucher;
	var $vouchernumber;
	var $add_credit;
	
	var $cardnumber_range;
	
	// Define if we have changed the status of the card
	var $set_inuse = 0;
	
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
	
	
	/* 
	 * Debug
	 *
	 * usage : $A2B -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $buffer_debug);
	 */
	function debug( $debug, $agi, $file, $line, $buffer_debug){
		
		$file = basename($file);
		
		// RUN VERBOSE ON CLI
		if ($debug & VERBOSE){
			if ($this->agiconfig['debug']>=1)   $agi->verbose('file:'.$file.' - line:'.$line.' - '.$buffer_debug);
		}
		
		// RIGHT DEBUG IN LOG FILE
		if ($debug & WRITELOG){
			$this -> write_log($buffer_debug, 1, "[file:$file - line:$line]:");
		}
	}
	
	/* 
	 * Write log into file 
	 */
	function write_log($output, $tobuffer = 1, $line_file_info = ''){
		//$tobuffer = 0;
		
		if (strlen($this->log_file) > 1){
			
			$string_log = "[".date("d/m/Y H:i:s")."]:".$line_file_info."[CallerID:".$this->CallerID."]:[CN:".$this->cardnumber."]:[$output]\n";
			if ($this->CC_TESTING) echo $string_log;
			
			$this -> BUFFER .= $string_log;
			if (!$tobuffer || $this->CC_TESTING){
				error_log ($this -> BUFFER, 3, $this->log_file);
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

	/** \brief Sets the default value for a config entry
		\param sect   The config section, like 'global' for the [global] section
		\param name   the config entry name
		\param def    Default value. If you don't mind, use 'null'.
		\param const  If not null, set a superglobal constant with that name
		\param handl  Special handling for the entry:
				Values: 'error' Exit with a fatal error if var not set in config
				        'no-set' Don't set the constant to default. $def makes no sense then.
				        null   The defalut. Assign default etc.
	
		\return The value assigned to the entry
	*/
	function set_def_conf($sect,$name,$def,$const=null,$handl = null) {
		if (!isset($this->config[$sect][$name])){
			switch($handl){
			case 'error':
				error_log("Fatal: Config entry $sect/$name not found in config!");
				die();
				break;
			case 'no-set':
				break;
			case null:
			default:
				if (defined('DEBUG_CONF') && constant('DEBUG_CONF'))
					error_log("Warning: conf entry $sect/$name not in config, using default.");
				$this->config[$sect][$name] = $def;
				break;
			}
		}
		// second pass
		if (isset($this->config[$sect][$name])) {
			if($const !=null){
			define($const,$this->config[$sect][$name]);
			//echo "define('$const',\$this->config[$sect][$name]);<br>\n";
			}
			return $this->config[$sect][$name];
		}
		else {
			if($const !=null){
			define($const,null);
			//echo "define('$const',null);<br>\n";
			}
			return null;
		}
	}
	
	function load_conf( &$agi, $config=NULL, $webui=0, $idconfig=1, $optconfig=array())
	{
	  
		$this -> idconfig = $idconfig;
		// load config
		if(!is_null($config) && file_exists($config))
			$this->config = parse_ini_file($config, true);
		elseif(file_exists(DEFAULT_A2BILLING_CONFIG)){
			$this->config = parse_ini_file(DEFAULT_A2BILLING_CONFIG, true);		
		} /*else
			error_log("Cannot locate config: ". DEFAULT_A2BILLING_CONFIG);*/
	  
	  
		// If optconfig is specified, stuff vals and vars into 'a2billing' config array.
		foreach($optconfig as $var=>$val)
			$this->config["agi-conf$idconfig"][$var] = $val;
		
		// add default values to config for uninitialized values
        
		
		if (isset($this->config['general']['timezone'])) // PHP >=5.1
			date_default_timezone_set($this->config['general']['timezone']);
		//Card Number Length Code
		$card_length_range = isset($this->config['global']['interval_len_cardnumber'])?$this->config['global']['interval_len_cardnumber']:null;
		if ($card_length_range == NULL)
			$card_length_range='10-15';
		$this -> cardnumber_range = $this -> splitable_data ($card_length_range);
		
		if(is_array($this -> cardnumber_range) && ($this -> cardnumber_range[0] >= 4))
		{
			define ("CARDNUMBER_LENGTH_MIN", $this -> cardnumber_range[0]);
			define ("CARDNUMBER_LENGTH_MAX", $this -> cardnumber_range[count($this -> cardnumber_range)-1]);
			define ("LEN_CARDNUMBER", CARDNUMBER_LENGTH_MIN);
		}
		else
		{
			echo gettext("Invalid card number lenght defined in configuration.");
			exit;
		}
		$this->set_def_conf('global','len_aliasnumber',15,'LEN_ALIASNUMBER');
		$this->set_def_conf('global','len_voucher',15,'LEN_VOUCHER');
		$this->set_def_conf('global','base_currency',null,'BASE_CURRENCY','error');
		$this->set_def_conf('global','didbilling_daytopay',5);
		$this->set_def_conf('global','admin_email','root@localhost');
		
		// conf for the database connection
		//if(!isset($this->config["database"]['hostname']))	$this->config["database"]['hostname'] = 'localhost';
		$this->set_def_conf('database','port','5432','PORT');	//is that right for mysql?
		$this->set_def_conf('database','user','a2billing','USER');	// changed from 'postgres'
		//if(!isset($this->config["database"]['password']))	$this->config["database"]['password'] = '';
		$this->set_def_conf('database','dbname','a2billing','DBNAME');
		$this->set_def_conf('database','dbtype','postgres','DB_TYPE');
		
		
		
		
		// Conf for the Callback
		$this->set_def_conf('callback','context_callback','a2billing-callback');
		$this->set_def_conf('callback','ani_callback_delay','10');
		$this->set_def_conf('callback','extension','1000');
		$this->set_def_conf('callback','sec_avoid_repeate','30');
		$this->set_def_conf('callback','timeout','20');
		$this->set_def_conf('callback','answer_call','1');
		$this->set_def_conf('callback','nb_predictive_call','10');
		$this->set_def_conf('callback','nb_day_wait_before_retry','1');
		$this->set_def_conf('callback','context_preditctivedialer','a2billing-predictivedialer');
		$this->set_def_conf('callback','predictivedialer_maxtime_tocall','5400');
		$this->set_def_conf('callback','sec_wait_before_callback','10');	
		
		
		
		// Conf for the signup 
		$this->set_def_conf('signup','enable_signup','1');
		$this->set_def_conf('signup','credit','0');
		$this->set_def_conf('signup','tariff','8');
		$this->set_def_conf('signup','activated','t');
		$this->set_def_conf('signup','simultaccess','0');
		$this->set_def_conf('signup','typepaid','0');
		$this->set_def_conf('signup','creditlimit','0');
		$this->set_def_conf('signup','runservice','0');
		$this->set_def_conf('signup','enableexpire','0');
		$this->set_def_conf('signup','expiredays','0');
		
		$this->set_def_conf('epayment_method','purchase_amount',null,'EPAYMENT_PURCHASE_AMOUNT');
		
		// Conf for Paypal
		$this->set_def_conf('paypal','item_name','Credit Purchase');
		$this->set_def_conf('paypal','currency_code','USD');
		$this->set_def_conf('paypal','purchase_amount','5;10;15');
		$this->set_def_conf('paypal','paypal_fees','1');
		
		// Conf for Backup
		$this->set_def_conf('backup','backup_path','/tmp');
		$this->set_def_conf('backup','gzip_exe','/bin/gzip');
		$this->set_def_conf('backup','gunzip_exe','/bin/gunzip');
		$this->set_def_conf('backup','mysqldump','/usr/bin/mysqldump');
		$this->set_def_conf('backup','pg_dump','/usr/bin/pg_dump');
		$this->set_def_conf('backup','mysql','/usr/bin/mysql');
		$this->set_def_conf('backup','psql','/usr/bin/psql');
	
		
		// Conf for Customer Web UI
		$this->set_def_conf('webcustomerui','customerinfo','1');
		$this->set_def_conf('webcustomerui','sipiaxinfo','1');
		$this->set_def_conf('webcustomerui','personalinfo','1');
		$this->set_def_conf('webcustomerui','cdr','1');
		$this->set_def_conf('webcustomerui','invoice','1');
		$this->set_def_conf('webcustomerui','voucher','1');
		$this->set_def_conf('webcustomerui','paypal','1');
		$this->set_def_conf('webcustomerui','speeddial','1');
		$this->set_def_conf('webcustomerui','did','1');
		$this->set_def_conf('webcustomerui','ratecard','1');
		$this->set_def_conf('webcustomerui','simulator','1');
		$this->set_def_conf('webcustomerui','callback','1');
		$this->set_def_conf('webcustomerui','predictivedialer','1');
		$this->set_def_conf('webcustomerui','webphone','1');
		$this->set_def_conf('webcustomerui','callerid','1');
		$this->set_def_conf('webcustomerui','limit_callerid','5');
		$this->set_def_conf('webcustomerui','error_email','root@localhost');
		
		// conf for the web ui
		$this->set_def_conf('webui','buddy_sip_file', '/etc/asterisk/additional_a2billing_sip.conf');
		$this->set_def_conf('webui','buddy_iax_file', '/etc/asterisk/additional_a2billing_iax.conf');
		$this->set_def_conf('webui','api_logfile', '/tmp/api_ecommerce_request.log');
		if(isset($this->config['webui']['api_ip_auth']))
			$this->config['webui']['api_ip_auth'] = explode(";", $this->config['webui']['api_ip_auth']);
		
		$this->set_def_conf('webui','dir_store_mohmp3','/var/lib/asterisk/mohmp3');
		$this->set_def_conf('webui','num_musiconhold_class', 10,'NUM_MUSICONHOLD_CLASS');
		$this->set_def_conf('webui','show_help', 1,'SHOW_HELP');
		$this->set_def_conf('webui','my_max_file_size_import', 1024000);
		$this->set_def_conf('webui','dir_store_audio', '/var/lib/asterisk/sounds/a2billing');
		$this->set_def_conf('webui','my_max_file_size_audio', 3072000,'MY_MAX_FILE_SIZE_AUDIO');

		if(isset($this->config['webui']['file_ext_allow']))
			$this->config['webui']['file_ext_allow'] = explode(",", $this->config['webui']['file_ext_allow']);
		else $this->config['webui']['file_ext_allow'] = explode(",", "gsm, mp3, wav");
		
		if(isset($this->config['webui']['file_ext_allow_musiconhold']))	$this->config['webui']['file_ext_allow_musiconhold'] = explode(",", $this->config['webui']['file_ext_allow_musiconhold']);
		else $this->config['webui']['file_ext_allow_musiconhold'] = explode(",", "mp3");

		$this->set_def_conf('webui','show_top_frame', 1,'SHOW_TOP_FRAME');
		$this->set_def_conf('webui','currency_choose', 'all','CURRENCY_CHOOSE');
		$this->set_def_conf('webui','card_export_field_list', 'creationdate, username, credit, lastname, firstname');
		$this->set_def_conf('webui','voucher_export_field_list', 'id, voucher, credit, tag, activated, usedcardnumber, usedate, currency');
		$this->set_def_conf('webui','advanced_mode', 0,'ADVANCED_MODE');
		$this->set_def_conf('webui','delete_fk_card', 1);

		  
		// conf for the recurring process
		$this->set_def_conf("recprocess",'batch_log_file', '/tmp/batch-a2billing.log');
		
		// conf for the peer_friend
		$this->set_def_conf('peer_friend','type', 'friend');
		$this->set_def_conf('peer_friend','allow', 'ulaw, alaw, gsm, g729');
		$this->set_def_conf('peer_friend','context', 'a2billing');
		$this->set_def_conf('peer_friend','nat', 'yes');
		$this->set_def_conf('peer_friend','amaflags', 'billing');
		$this->set_def_conf('peer_friend','qualify', 'yes');
		$this->set_def_conf('peer_friend','host', 'dynamic');
		$this->set_def_conf('peer_friend','dtmfmode', 'RFC2833');
		
		
		// conf for the log-files
		/*
		if(!isset($this->config['log-files']['cront_alarm'])) $this->config['log-files']['cront_alarm'] = '/tmp/cront_a2b_alarm.log';
		if(!isset($this->config['log-files']['cront_autorefill'])) $this->config['log-files']['cront_autorefill'] = '/tmp/cront_a2b_autorefill.log';
		if(!isset($this->config['log-files']['cront_batch_process'])) $this->config['log-files']['cront_batch_process'] = '/tmp/cront_a2b_batch_process.log';
		if(!isset($this->config['log-files']['cront_bill_diduse'])) $this->config['log-files']['cront_bill_diduse'] = '/tmp/cront_a2b_bill_diduse.log';
		if(!isset($this->config['log-files']['cront_subscriptionfee'])) $this->config['log-files']['cront_subscriptionfee'] = '/tmp/cront_a2b_subscriptionfee.log';
		if(!isset($this->config['log-files']['cront_currency_update'])) $this->config['log-files']['cront_currency_update'] = '/tmp/cront_a2b_currency_update.log';
		if(!isset($this->config['log-files']['cront_invoice'])) $this->config['log-files']['cront_invoice'] = '/tmp/cront_a2b_invoice.log';
		
		if(!isset($this->config['log-files']['paypal'])) $this->config['log-files']['paypal'] = '/tmp/a2billing_paypal.log';
		if(!isset($this->config['log-files']['epayment'])) $this->config['log-files']['epayment'] = '/tmp/a2billing_epayment.log';
		if(!isset($this->config['log-files']['ecommerce_api'])) $this->config['log-files']['ecommerce_api'] = '/tmp/api_ecommerce_request.log';
		if(!isset($this->config['log-files']['soap_api'])) $this->config['log-files']['soap_api'] = '/tmp/api_soap_request.log';
		if(!isset($this->config['log-files']['callback_api'])) $this->config['log-files']['callback_api'] = '/tmp/api_callback_request.log';
		if(!isset($this->config['log-files']['agi'])) $this->config['log-files']['agi'] = '/tmp/a2billing_agi.log';
		*/
		
		//TODO:
		if(isset($this->config['log-files']['agi']) && strlen ($this->config['log-files']['agi']) > 1)
		{
			$this -> log_file = $this -> config['log-files']['agi'];
		}
		define ("LOGFILE_CRONT_ALARM", 			isset($this->config['log-files']['cront_alarm'])			?$this->config['log-files']['cront_alarm']:null);
		define ("LOGFILE_CRONT_AUTOREFILL", 	isset($this->config['log-files']['cront_autorefill'])		?$this->config['log-files']['cront_autorefill']:null);
		define ("LOGFILE_CRONT_BATCH_PROCESS", 	isset($this->config['log-files']['cront_batch_process'])	?$this->config['log-files']['cront_batch_process']:null);
		define ("LOGFILE_CRONT_BILL_DIDUSE", 	isset($this->config['log-files']['cront_bill_diduse'])		?$this->config['log-files']['cront_bill_diduse']:null);
		define ("LOGFILE_CRONT_SUBSCRIPTIONFEE",isset($this->config['log-files']['cront_subscriptionfee'])	?$this->config['log-files']['cront_subscriptionfee']:null);
		define ("LOGFILE_CRONT_CURRENCY_UPDATE",isset($this->config['log-files']['cront_currency_update'])	?$this->config['log-files']['cront_currency_update']:null);
		define ("LOGFILE_CRONT_INVOICE",		isset($this->config['log-files']['cront_invoice'])			?$this->config['log-files']['cront_invoice']:null);
		define ("LOGFILE_CRONT_CHECKACCOUNT",	isset($this->config['log-files']['cront_check_account'])	?$this->config['log-files']['cront_check_account']:null);
		
		define ("LOGFILE_API_ECOMMERCE", 		isset($this->config['log-files']['api_ecommerce'])			?$this->config['log-files']['api_ecommerce']:null);
		define ("LOGFILE_API_CALLBACK", 		isset($this->config['log-files']['api_callback'])			?$this->config['log-files']['api_callback']:null);
		define ("LOGFILE_PAYPAL", 				isset($this->config['log-files']['paypal'])					?$this->config['log-files']['paypal']:null);
		define ("LOGFILE_EPAYMENT", 			isset($this->config['log-files']['epayment'])				?$this->config['log-files']['epayment']:null);
		
		
		// conf for the AGI
		$str_agiconf="agi-conf$idconfig";
		$this->set_def_conf($str_agiconf,'play_audio',1,'PLAY_AUDIO');
		
		$this->set_def_conf($str_agiconf,'debug', false);
		$this->set_def_conf($str_agiconf,'logger_enable', 1);
		$this->set_def_conf($str_agiconf,'log_file', '/tmp/a2billing.log');
		
		$this->set_def_conf($str_agiconf,'answer_call', 1);
		$this->set_def_conf($str_agiconf,'auto_setcallerid', 1);
		$this->set_def_conf($str_agiconf,'say_goodbye', 0);
		$this->set_def_conf($str_agiconf,'play_menulanguage', 0);
		$this->set_def_conf($str_agiconf,'force_language', 'EN');
		$this->set_def_conf($str_agiconf,'min_credit_2call', 0);
		$this->set_def_conf($str_agiconf,'min_duration_2bill', 0);
		
		$this->set_def_conf($str_agiconf,'use_dnid', 0);
		// Explode the no_auth_dnid string 
		if(isset($this->config[$str_agiconf]['no_auth_dnid'])) $this->config[$str_agiconf]['no_auth_dnid'] = explode(",",$this->config[$str_agiconf]['no_auth_dnid']);
		
		// Explode the extracharge_did and extracharge_fee strings
		if(isset($this->config[$str_agiconf]['extracharge_did'])) $this->config[$str_agiconf]['extracharge_did'] = explode(",",$this->config[$str_agiconf]['extracharge_did']);
		if(isset($this->config[$str_agiconf]['extracharge_fee'])) $this->config[$str_agiconf]['extracharge_fee'] = explode(",",$this->config[$str_agiconf]['extracharge_fee']);

		$this->set_def_conf($str_agiconf,'number_try', 3);
		$this->set_def_conf($str_agiconf,'say_balance_after_auth', 1);
		$this->set_def_conf($str_agiconf,'say_balance_after_call', 0);
		$this->set_def_conf($str_agiconf,'say_rateinitial', 0);
		$this->set_def_conf($str_agiconf,'say_timetocall', 1);
		$this->set_def_conf($str_agiconf,'cid_enable', 0);
		$this->set_def_conf($str_agiconf,'cid_sanitize', 0);
		$this->set_def_conf($str_agiconf,'cid_askpincode_ifnot_callerid', 1);
		$this->set_def_conf($str_agiconf,'cid_auto_assign_card_to_cid', 0);
		$this->set_def_conf($str_agiconf,'notenoughcredit_cardnumber', 0);
		$this->set_def_conf($str_agiconf,'notenoughcredit_assign_newcardnumber_cid', 0);
		$this->set_def_conf($str_agiconf,'maxtime_tocall_negatif_free_route', 1800);
		$this->set_def_conf($str_agiconf,'callerid_authentication_over_cardnumber', 0);
		$this->set_def_conf($str_agiconf,'cid_auto_create_card_len', 10);
		
		$this->set_def_conf($str_agiconf,'sip_iax_friends', 0);
		$this->set_def_conf($str_agiconf,'sip_iax_pstn_direct_call', 0);
		$this->set_def_conf($str_agiconf,'dialcommand_param', '|30|HL(%timeout%:61000:30000)');
		$this->set_def_conf($str_agiconf,'dialcommand_param_sipiax_friend', '|30|HL(3600000:61000:30000)');
		$this->set_def_conf($str_agiconf,'switchdialcommand', 0);
		$this->set_def_conf($str_agiconf,'failover_recursive_limit', 1);
		$this->set_def_conf($str_agiconf,'record_call', 0);
		$this->set_def_conf($str_agiconf,'monitor_formatfile', 'gsm');
		$this->set_def_conf($str_agiconf,'currency_association','all:credit');
		$this->config[$str_agiconf]['currency_association'] = explode(",",$this->config[$str_agiconf]['currency_association']);
		
		foreach($this->config[$str_agiconf]['currency_association'] as $cur_val){
			$cur_val = explode(":",$cur_val);
			$this->config[$str_agiconf]['currency_association_internal'][$cur_val[0]]=$cur_val[1];
		}
					
		$this->set_def_conf($str_agiconf,'file_conf_enter_destination','prepaid-enter-number-u-calling-1-or-011');
		$this->set_def_conf($str_agiconf,'file_conf_enter_menulang','prepaid-menulang');
		$this->set_def_conf($str_agiconf,'send_reminder', 0);
		if(isset($this->config[$str_agiconf]['debugshell']) && $this->config[$str_agiconf]['debugshell'] == 1 && isset($agi))
			$agi->nlinetoread = 0;
		
		$this->set_def_conf($str_agiconf,'ivr_voucher', 0);
		$this->set_def_conf($str_agiconf,'ivr_voucher_prefixe', 8);
		$this->set_def_conf($str_agiconf,'jump_voucher_if_min_credit', 0);
		
		$this->agiconfig = $this->config[$str_agiconf];
		
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
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RES Menu Language DTMF : ".$res_dtmf ["result"]);
			
			$this -> languageselected = $res_dtmf ["result"];
			
			if 		($this->languageselected=="2")		
				$language = 'es';
			elseif 	($this->languageselected=="3")		
				$language = 'fr';
			else									
				$language = 'en';
			
			if($this->agiconfig['asterisk_version'] == "1_2")
			{
				$lg_var_set = 'LANGUAGE()';				
			}
			else
			{
				$lg_var_set = 'CHANNEL(language)';
			}
			$agi -> set_variable($lg_var_set, $language);
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[SET $lg_var_set $language]");
			
		}elseif (strlen($this->agiconfig['force_language'])==2){
			
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "FORCE LANGUAGE : ".$this->agiconfig['force_language']);	
			$this->languageselected = 1;
			$language = strtolower($this->agiconfig['force_language']);
			if($this->agiconfig['asterisk_version'] == "1_2")
			{
				$lg_var_set = 'LANGUAGE()';				
			}
			else
			{
				$lg_var_set = 'CHANNEL(language)';
			}
			$agi -> set_variable($lg_var_set, $language);
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[SET $lg_var_set $language]");
			
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
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, ' get_agi_request_parameter = '.$this->CallerID.' ; '.$this->channel.' ; '.$this->uniqueid.' ; '.$this->accountcode.' ; '.$this->dnid);
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
		
		if ($inuse){		
			$QUERY = "UPDATE cc_card SET inuse=inuse+1 WHERE username='".$this->username."'";
			$this -> set_inuse = 1;
		}else{ 			
			$QUERY = "UPDATE cc_card SET inuse=inuse-1 WHERE username='".$this->username."'";
			$this -> set_inuse = 0;
		}
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CARD STATUS UPDATE : $QUERY]");
		if (!$this -> CC_TESTING) $result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
		
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
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__,  $this->agiconfig['use_dnid']." && ".in_array ($this->dnid, $this->agiconfig['no_auth_dnid'])." && ".strlen($this->dnid)."&& $try_num");
		
		// CHECK IF USE_DNID IF NOT GET THE DESTINATION NUMBER
		if ($this->agiconfig['use_dnid']==1 && !in_array ($this->dnid, $this->agiconfig['no_auth_dnid']) && strlen($this->dnid)>2 && $try_num==0){
			$this->destination = $this->dnid;
		}else{
			$res_dtmf = $agi->get_data($prompt_enter_dest, 6000, 20);
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RES DTMF : ".$res_dtmf ["result"]);
			$this->destination = $res_dtmf ["result"];
		}
		
		//REDIAL FIND THE LAST DIALED NUMBER (STORED IN THE DATABASE)
		if (strlen($this->destination)<=2 && is_numeric($this->destination) && $this->destination>=0){

			$QUERY = "SELECT phone FROM cc_speeddial WHERE id_cc_card='".$this->id_card."' AND speeddial='".$this->destination."'";
			$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n RESULT : ".print_r($result,true));
			if( is_array($result))	$this->destination = $result[0][0];		
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "REDIAL : DESTINATION ::> ".$this->destination);
		}
		
		// FOR TESTING : ENABLE THE DESTINATION NUMBER
		if ($this->CC_TESTING) $this->destination="1800300200";
		if ($this->CC_TESTING) $this->destination="3390010022";
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "DESTINATION ::> ".$this->destination);					
		if ($this->removeinterprefix) $this->destination = $this -> apply_rules ($this->destination);			
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RULES APPLY ON DESTINATION ::> ".$this->destination);
		
		// TRIM THE "#"s IN THE END, IF ANY
		// usefull for SIP or IAX friends with "use_dnid" when their device sends also the "#"
		// it should be safe for normal use
		$this->destination = rtrim($this->destination, "#");
		
		// SAY BALANCE
		// this is hardcoded for now but we might have a setting in a2billing.conf for the combination
		if ($this->destination=='*0'){
			$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[SAY BALANCE ::> ".$this->credit."]");
			$this -> fct_say_balance ($agi, $this->credit);
			return -1;
		}
			
		//REDIAL FIND THE LAST DIALED NUMBER (STORED IN THE DATABASE)
		if ($this->destination=='*1'){
			$this->destination = $this->redial;
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[REDIAL : DTMF DESTINATION ::> ".$this->destination."]");		
		}
		
		if ($this->destination<=0){
			$prompt = "prepaid-invalid-digits";
			// do not play the error message if the destination number is not numeric
			// because most probably it wasn't entered by user (he has a phone keypad remember?)
			// it helps with using "use_dnid" and extensions.conf routing
			if (is_numeric($this->destination)) $agi-> stream_file($prompt, '#');
			return -1;
		}
		
		// STRIP * FROM DESTINATION NUMBER
		$this->destination = str_replace('*', '', $this->destination);
		
		// LOOKUP RATE : FIND A RATE FOR THIS DESTINATION
		$resfindrate = $RateEngine->rate_engine_findrates($this, $this->destination,$this->tariff);
		if ($resfindrate==0){
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "ERROR ::> RateEngine didnt succeed to match the dialed number over the ratecard (Please check : id the ratecard is well create ; if the removeInter_Prefix is set according to your prefix in the ratecard ; if you hooked the ratecard to the Call Plan)");
		}else{
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "OK - RESFINDRATE::> ".$resfindrate);
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
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RES_ALL_CALCULTIMEOUT ::> $res_all_calcultimeout");
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
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "TIMEOUT::> ".$this->timeout."  : minutes=$minutes - seconds=$seconds");
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
		if ( ($this->agiconfig['use_dnid']==1) && (!in_array ($this->dnid, $this->agiconfig['no_auth_dnid'])) && (strlen($this->dnid)>2 ))
		{								
			$this->destination = $this->dnid;
		}
		else
		{
			$res_dtmf = $agi->get_data('prepaid-sipiax-enternumber', 6000, $this->config['global']['len_aliasnumber'], '#');			
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RES DTMF : ".$res_dtmf ["result"]);
			$this->destination = $res_dtmf ["result"];
			
			if ($this->destination<=0){
				return -1;
			}
		}
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "SIP o IAX DESTINATION : ".$this->destination);
		
		$sip_buddies = 0; $iax_buddies = 0;
		
		
		//$QUERY =  "SELECT name FROM cc_iax_buddies WHERE name='".$this->destination."'";
		$QUERY = "SELECT name FROM cc_iax_buddies, cc_card WHERE cc_iax_buddies.name=cc_card.username AND useralias='".$this->destination."'";			
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $QUERY);										
		$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $result);		
		
		if( is_array($result)){	$iax_buddies = 1; $destiax=$result[0][0];}
		
			
		//$QUERY =  "SELECT name FROM cc_sip_buddies WHERE name='".$this->destination."'";
		$QUERY = "SELECT name FROM cc_sip_buddies, cc_card WHERE cc_sip_buddies.name=cc_card.username AND useralias='".$this->destination."'";
		$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n RESULT : ".print_r($result,true));
		
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
				$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
			}
			
			$agi->set_callerid($this->useralias);
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[EXEC SetCallerID : ".$this->useralias."]");
			
			$dialparams = $this->agiconfig['dialcommand_param_sipiax_friend'];
			$dialstr = $this->tech."/".$this->destination.$dialparams;

			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "app_callingcard sip/iax friend: Dialing '$dialstr' ".$this->tech." Friend.\n");
			
			//# Channel: technology/number@ip_of_gw_to PSTN
			// Dial(IAX2/guest@misery.digium.com/s@default) 
			$myres = $agi->exec("DIAL $dialstr");
			$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "DIAL");
		
			$answeredtime = $agi->get_variable("ANSWEREDTIME");
			$answeredtime = $answeredtime['data'];
			$dialstatus = $agi->get_variable("DIALSTATUS");
			$dialstatus = $dialstatus['data'];
				
				
			if ($this->agiconfig['record_call'] == 1){
				// Monitor(wav,kiki,m)					
				$myres = $agi->exec("STOPMONITOR");
				$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC StopMonitor (".$this->uniqueid."-".$this->cardnumber.")");
			}
				
			$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[".$this->tech." Friend][K=$k]:[ANSWEREDTIME=".$answeredtime."-DIALSTATUS=".$dialstatus."]");
				
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
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "-> dialstatus : $dialstatus, answered time is ".$answeredtime." \n");
			} elseif ($k+1 == $sip_buddies+$iax_buddies){
				$prompt="prepaid-dest-unreachable";
				$agi-> stream_file($prompt, '#');
			}
			
			if (($dialstatus  == "CHANUNAVAIL") || ($dialstatus  == "CONGESTION"))	continue;
				
			if ($answeredtime >0){ 
				$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CC_RATE_ENGINE_UPDATESYSTEM: usedratecard K=$K - (answeredtime=$answeredtime :: dialstatus=$dialstatus :: cost=$cost)]");
				$QUERY = "INSERT INTO cc_call (uniqueid,sessionid,username,nasipaddress,starttime,sessiontime, calledstation, ".						
					" terminatecause, stoptime, calledrate, sessionbill, calledcountry, calledsub, destination, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax) VALUES ".
					"('".$this->uniqueid."', '".$this->channel."',  '".$this->username."', '".$this->hostname."',";
				if ($this->config['database']['dbtype'] == "postgres"){
					$QUERY .= " CURRENT_TIMESTAMP - interval '$answeredtime seconds' ";
				}else{
					$QUERY .= " CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND ";
				}						
				$QUERY .= ", '$answeredtime', '".$this->destination."', '$dialstatus', now(), '0', '0', ".
					" '".$this->countrycode."', '".$this->subcode."', '".$this->tech." CALL', '0', '0', '0', '0', '$this->CallerID', '1' )";
				
				$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n RESULT : ".$result);
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
			
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[A2Billing] DID call friend: FOLLOWME=$callcount (cardnumber:".$inst_listdestination[6]."|destination:".$inst_listdestination[4]."|tariff:".$inst_listdestination[3].")\n");
			
			$this->agiconfig['cid_enable']= 0;
			$this->accountcode = $inst_listdestination[6];
			$this->tariff = $inst_listdestination[3];
			$this->destination = $inst_listdestination[4];
			$this->username = $inst_listdestination[6];
			
			
			// MAKE THE AUTHENTICATION TO GET ALL VALUE : CREDIT - EXPIRATION - ...
			if ($this -> callingcard_ivr_authenticate($agi)!=0){
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[A2Billing] DID call friend: AUTHENTICATION FAILS !!!\n");
			}else{				
				// CHECK IF DESTINATION IS SET
				if (strlen($inst_listdestination[4])==0) continue;
				
				// IF VOIP CALL
				if ($inst_listdestination[5]==1){
					
					// RUN MONITOR TO RECORD CALL
					if ($this->agiconfig['record_call'] == 1){
						$myres = $agi->exec("MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
						$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC MONITOR ".$this->agiconfig['monitor_formatfile']."|".$this->uniqueid."|mb");
					}
						
					$dialparams = $this->agiconfig['dialcommand_param_sipiax_friend'];
					$dialstr = $inst_listdestination[4].$dialparams;
					
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[A2Billing] DID call friend: Dialing '$dialstr' Friend.\n");
					
					//# Channel: technology/number@ip_of_gw_to PSTN
					// Dial(IAX2/guest@misery.digium.com/s@default) 
					// DIAL OUT
					$myres = $agi->exec("DIAL $dialstr");
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "DIAL");
				
					$answeredtime = $agi->get_variable("ANSWEREDTIME");
					$answeredtime = $answeredtime['data'];
					$dialstatus = $agi->get_variable("DIALSTATUS");
					$dialstatus = $dialstatus['data'];
						
						
					if ($this->agiconfig['record_call'] == 1){				
						$myres = $agi->exec("STOPMONITOR");
						$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "EXEC StopMonitor (".$this->uniqueid."-".$this->cardnumber.")");
					}
						
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[".$inst_listdestination[4]." Friend][followme=$callcount]:[ANSWEREDTIME=".$answeredtime."-DIALSTATUS=".$dialstatus."]");
						
						
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
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[A2Billing] DID call friend: dialstatus : $dialstatus, answered time is ".$answeredtime." \n");
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
							
						$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[DID CALL - LOG CC_CALL: FOLLOWME=$callcount - (answeredtime=$answeredtime :: dialstatus=$dialstatus :: cost=$cost)]");
							
						$QUERY = "INSERT INTO cc_call (uniqueid,sessionid,username,nasipaddress,starttime,sessiontime, calledstation, ".						
							" terminatecause, stoptime, calledrate, sessionbill, calledcountry, calledsub, destination, id_tariffgroup, id_tariffplan, id_ratecard, id_trunk, src, sipiax) VALUES ".
							"('".$this->uniqueid."', '".$this->channel."',  '".$this->username."', '".$this->hostname."',";
						if ($this->config['database']['dbtype'] == "postgres"){
							$QUERY .= " CURRENT_TIMESTAMP - interval '$answeredtime seconds' ";
						}else{
							$QUERY .= " CURRENT_TIMESTAMP - INTERVAL $answeredtime SECOND ";
						}						
						$QUERY .= ", '$answeredtime', '".$inst_listdestination[4]."', '$dialstatus', now(), '0', '0', ".
							" '".$this->countrycode."', '".$this->subcode."', 'DID CALL', '0', '0', '0', '0', '$this->CallerID', '3' )";
						
						$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[DID CALL - LOG CC_CALL: SQL: $QUERY]:[result:$result]");
						
						// CC_DID & CC_DID_DESTINATION - cc_did.id, cc_did_destination.id							
						$QUERY = "UPDATE cc_did SET secondusedreal = secondusedreal + $answeredtime WHERE id='".$inst_listdestination[0]."'";
						$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[UPDATE DID: SQL: $QUERY]:[result:$result]");
						
						$QUERY = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + $answeredtime WHERE id='".$inst_listdestination[1]."'";
						$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[UPDATE DID_DESTINATION: SQL: $QUERY]:[result:$result]");
						
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
							$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[UPDATE DID: SQL: $QUERY]:[result:$result]");
							
							$QUERY = "UPDATE cc_did_destination SET secondusedreal = secondusedreal + ".$RateEngine->answeredtime." WHERE id='".$inst_listdestination[1]."'";
							$result = $this->instance_table -> SQLExec ($this -> DBHandle, $QUERY, 0);
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[UPDATE DID_DESTINATION: SQL: $QUERY]:[result:$result]");
							
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
	function fct_say_balance ($agi, $credit, $fromvoucher = 0){
		
		global $currencies_list;
		
		if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency'])==3)
		{ 
			$this->currency = $this->agiconfig['agi_force_currency'];
		}
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CURRENCY : $this->currency]");
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
		if ($fromvoucher!=1)$agi-> stream_file('prepaid-you-have', '#');
		else $agi-> stream_file('prepaid-account_refill', '#');
		
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

		$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[SAY RATE ::> ".$rate."]");

		global $currencies_list;

		if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency'])==3)
		{ 
			$this->currency = $this->agiconfig['agi_force_currency'];
		}
		
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

	/**
	 *	Function refill_card_with_voucher
	 *
	 *  @param object $agi
	 *  @param object $RateEngine     
	 *  @param object $voucher number
		
     *  @return 1 if Ok ; -1 if error
	**/
	function refill_card_with_voucher ($agi, $try_num){
		
		global $currencies_list;
		
		$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[VOUCHER REFILL CARD LOG BEGIN]");
		if (isset($this->agiconfig['agi_force_currency']) && strlen($this->agiconfig['agi_force_currency'])==3){ 
			$this -> currency = $this->agiconfig['agi_force_currency'];
		}
		
		if (!isset($currencies_list[strtoupper($this->currency)][2]) || !is_numeric($currencies_list[strtoupper($this->currency)][2])){ 
			$mycur = 1;
		} else { 
			$mycur = $currencies_list[strtoupper($this->currency)][2];
		}
		$timetowait = ($this->config['global']['len_voucher'] < 6) ? 8000 : 20000;
		$res_dtmf = $agi->get_data('prepaid-voucher_enter_number', $timetowait, $this->config['global']['len_voucher'], '#');
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "VOUCHERNUMBER RES DTMF : ".$res_dtmf ["result"]);
		$this -> vouchernumber = $res_dtmf ["result"];
		if ($this -> vouchernumber <= 0){
			return -1;
		}
		
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "VOUCHER NUMBER : ".$this->vouchernumber);
		
		$QUERY = "SELECT voucher, credit, activated, tag, currency, expirationdate FROM cc_voucher WHERE expirationdate >= CURRENT_TIMESTAMP AND activated='t' AND voucher='".$this -> vouchernumber."'";
		
		$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY);
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[VOUCHER SELECT: $QUERY]\n".print_r($result,true));	
		
		if ($result[0][0]==$this->vouchernumber)
		{
			if (!isset ($currencies_list[strtoupper($result[0][4])][2]))
			{
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "System Error : No currency table complete !!!");
				$agi-> stream_file('prepaid-unknow_used_currencie', '#');				
				return -1;
			}
			else
			{	
				// DISABLE THE VOUCHER 
				$this -> add_credit = $result[0][1] * $currencies_list[strtoupper($result[0][4])][2];
				$QUERY = "UPDATE cc_voucher SET activated='f', usedcardnumber='".$this->accountcode."', usedate=now() WHERE voucher='".$this->vouchernumber."'";
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY UPDATE VOUCHER: $QUERY");
				$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
				
				// UPDATE THE CARD AND THE CREDIT PROPERTY OF THE CLASS
				$QUERY = "UPDATE cc_card SET credit=credit+'".$this ->add_credit."' WHERE username='".$this->accountcode."'";
				$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
				$this -> credit += $this -> add_credit; 
				
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY UPDATE CARD: $QUERY");
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, ' The Voucher '.$this->vouchernumber.' has been used, We added '.$this ->add_credit/$mycur.' '.strtoupper($this->currency).' of credit on your account!');
				$this->fct_say_balance ($agi, $this->add_credit, 1);
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[VOUCHER REFILL CARD: $QUERY]");
				return 1;
			}
		}
		else
		{
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[VOUCHER REFILL ERROR: ".$this->vouchernumber." Voucher not avaible or dosn't exist]");
			$agi-> stream_file('voucher_does_not_exist');
			return -1;
		}
		$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[VOUCHER REFILL CARD LOG END]");
		return 1;
	}

	
	/*
	 * Function to generate a cardnumber
	 */	 
	function MDP()
	{
		$chrs = $this->agiconfig['cid_auto_create_card_len'];  
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
			
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CID_SANITIZE - CID:".$this->CallerID."]");
			
			if (strlen($this->CallerID)==0) {
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CID_SANITIZE - CID: NO CID]");
				return '';
			}
			$QUERY="";
			if($this->agiconfig['cid_sanitize']=="CID" || $this->agiconfig['cid_sanitize']=="BOTH"){
				$QUERY .=  "SELECT cc_callerid.cid ".
					  " FROM cc_callerid ".
					  " JOIN cc_card ON cc_callerid.id_cc_card=cc_card.id ".
					  " WHERE cc_card.username='".$this->cardnumber."' ";
				$QUERY .= "ORDER BY 1";
				$result1 = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n".print_r($result1,true));
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
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY");
				$result2 = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			}
			if (count($result1)>0 || count($result2)>0) 
				$result = array_merge($result1, $result2);
			
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RESULT MERGE -> ".print_r($result,true));
			
			if( !is_array($result)) {
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CID_SANITIZE - CID: NO DATA]");
				return '';
			}
			for ($i=0;$i<count($result);$i++){
				$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CID_SANITIZE - CID COMPARING: ".substr($result[$i][0],strlen($this->CallerID)*-1)." to ".$this->CallerID."]");
				if(substr($result[$i][0],strlen($this->CallerID)*-1)==$this->CallerID) {
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CID_SANITIZE - CID: ".$result[$i][0]."]");
					return $result[$i][0];
				}
			}
		$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CID_SANITIZE - CID UNIQUE RESULT: ".$result[0][0]."]");
		return $result[0][0];
	}
	
	function callingcard_auto_setcallerid($agi){
		// AUTO SetCallerID
		$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[AUTO SetCallerID]");
		if ($this->agiconfig['auto_setcallerid']==1){
			if ( strlen($this->agiconfig['force_callerid']) >=1 ){
				$agi -> set_callerid($this->agiconfig['force_callerid']);
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[EXEC SetCallerID : ".$this->agiconfig['force_callerid']."]");
			}elseif ( strlen($this->CallerID) >=1 ){
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[REQUESTED SetCallerID : ".$this->CallerID."]");
				
      			// IF REQUIRED, VERIFY THAT THE CALLERID IS LEGAL
      			$cid_sanitized = $this->CallerID;
				/*if ($this->agiconfig['cid_sanitize']=='DID' || $this->agiconfig['cid_sanitize']=='CID' || $this->agiconfig['cid_sanitize']=='BOTH') {
					$cid_sanitized = $this -> callingcard_cid_sanitize($agi);
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[TRY : callingcard_cid_sanitize]");
					if ($this->agiconfig['debug']>=1) $agi->verbose('CALLERID SANITIZED: "'.$cid_sanitized.'"');
				}*/
				if (strlen($cid_sanitized)>0){
					$agi->set_callerid($cid_sanitized);
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[EXEC SetCallerID : ".$cid_sanitized."]");
				}else{
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CANNOT SetCallerID : cid_san is empty]");
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
		
		if ($callerID_enable==1 && is_numeric($this->CallerID) && $this->CallerID>0){
			$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CID_ENABLE - CID_CONTROL - CID:".$this->CallerID."]");
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CID_ENABLE - CID_CONTROL - CID:".$this->CallerID."]");
			
			$QUERY =  "SELECT cc_callerid.cid, cc_callerid.id_cc_card, cc_callerid.activated, cc_card.credit, ".
				  " cc_card.tariff, cc_card.activated, cc_card.inuse, cc_card.simultaccess,  ".
				  " cc_card.typepaid, cc_card.creditlimit, cc_card.language, cc_card.username, removeinterprefix, cc_card.redial, ";
			if ($this->config['database']['dbtype'] == "postgres"){	  
				$QUERY .=  " enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), ";
			}else{
				$QUERY .=  " enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), ";
			}
			
			$QUERY .=  " cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias ".
						" FROM cc_callerid ".
						" LEFT JOIN cc_card ON cc_callerid.id_cc_card=cc_card.id ".
						" LEFT JOIN cc_tariffgroup ON cc_card.tariff=cc_tariffgroup.id ".
			" WHERE cc_callerid.cid=".$this->DBHandle->Quote($this->CallerID) .
			"OR replace(cc_callerid.cid,'.','') =". $this->DBHandle->Quote($this->CallerID);
			$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n RESULT : ".print_r($result,true));
			
			if( !is_array($result)) {
				
				if ($this->agiconfig['cid_auto_create_card']==1){
					
					for ($k=0;$k<=20;$k++){
						if ($k==20){
							$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "ERROR : Impossible to generate a cardnumber not yet used!");
							$prompt="prepaid-auth-fail";
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
							$agi-> stream_file($prompt, '#');
							return -2;
						}
						$card_gen = MDP();
						//echo "SELECT username FROM card where username='$card_gen'<br>";
						$numrow = 0;
						$resmax = $this->DBHandle -> Execute("SELECT username FROM $FG_TABLE_NAME where username='$card_gen'");
						if ($resmax)
							$numrow = $resmax -> RecordCount();
							
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
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CARDNUMBER: $card_gen]:[CARDID CREATED : $result]");
					
					//CREATE A CARD AND AN INSTANCE IN CC_CALLERID
					$QUERY_FIELS = 'cid, id_cc_card';
					$QUERY_VALUES = "'".$this->CallerID."','$result'";
							
					$result = $this->instance_table -> Add_table ($this->DBHandle, $QUERY_VALUES, $QUERY_FIELS, 'cc_callerid');
					if (!$result){
						$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CALLERID CREATION ERROR TABLE cc_callerid]");
						$prompt="prepaid-auth-fail";
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
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
					
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CID_CONTROL - STOP - NO CALLERID]");
							
					// $callerID_enable=1; -> we are checking later if the callerID/accountcode has been define if not ask for pincode
					if ($this->agiconfig['cid_askpincode_ifnot_callerid']==1) {
						$this->accountcode='';
						$callerID_enable=0;
					}else {
						// caller id not found, fail authentication!
						$prompt="prepaid-auth-fail";
						if ($this->agiconfig['debug']>=1) 
							$agi->verbose('line:'.__LINE__.' - '.strtoupper($prompt));
						//$agi->agi_exec("STREAM FILE $prompt #");
					$agi-> stream_file($prompt, '#');
						return -2;
					}
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
				
				// CHECK credit < min_credit_2call / you have zero balance
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
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, 'prompt:'.strtoupper($prompt));
					
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[ERROR CHECK CARD : $prompt (cardnumber:".$this->cardnumber.")]");
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT - Refill with vouchert]");
					
					if ($this->agiconfig['jump_voucher_if_min_credit']==1 && $prompt == "prepaid-zero-balance"){
					
						$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT - refill_card_withvoucher] ");
						$vou_res = $this -> refill_card_with_voucher($agi,2);
						if ($vou_res==1){
							return 0;
						}else {
							$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[NOTENOUGHCREDIT - refill_card_withvoucher fail] ");
						}
					}
					if ($prompt == "prepaid-zero-balance" && $this->agiconfig['notenoughcredit_cardnumber']==1) { 
						$this->accountcode=''; $callerID_enable=0;
						$this->agiconfig['cid_auto_assign_card_to_cid']=0;
						if ($this->agiconfig['notenoughcredit_assign_newcardnumber_cid']==1) $this -> ask_other_cardnumber=1;
					}else{
						return -2;
					}
				}
				
			} // elseif We -> found a card for this callerID
			
		}else{
			// NO CALLERID AUTHENTICATION
			$callerID_enable=0;
		}
		
		// check if we can authenticate through the "accountcode"
		
		$prompt_entercardnum= "prepaid-enter-pin-number";
		$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, ' - Account code - '.$this->accountcode);
		if (strlen ($this->accountcode)>=1) {
			$this->username = $this -> cardnumber = $this->accountcode;
			for ($i=0;$i<=0;$i++){									 
					
				if ($callerID_enable!=1 || !is_numeric($this->CallerID) || $this->CallerID<=0){
					
					$QUERY =  "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, ";
					if ($this->config['database']['dbtype'] == "postgres"){
						$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias FROM cc_card ";
					}else{
						$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign, cc_card.id, useralias FROM cc_card ";
					}
					
					$QUERY .=  "LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $QUERY);
					
					$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
					//$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, print_r($result,true));
					
					if( !is_array($result)) {
						$prompt="prepaid-auth-fail";
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
						$res = -2;
						break;
					}else{
						// -%-%-%-	WE ARE GOING TO CHECK IF THE CALLERID IS CORRECT FOR THIS CARD	-%-%-%-
						if ($this->agiconfig['callerid_authentication_over_cardnumber']==1){
							
							if (!is_numeric($this->CallerID) && $this->CallerID<=0){
								$res = -2;
								break;
							}
							
							$QUERY = " SELECT cid, id_cc_card, activated FROM cc_callerid "
									." WHERE cc_callerid.cid='".$this->CallerID."' AND cc_callerid.id_cc_card='".$result[0][22]."'";
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $QUERY);
							
							$result_check_cid = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, $result_check_cid);
							
							if( !is_array($result_check_cid)) {
								$prompt="prepaid-auth-fail";
								$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
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
					
					if($this->agiconfig['asterisk_version'] == "1_2")
					{
						$lg_var_set = 'LANGUAGE()';
					}
					else 
					{
						$lg_var_set = 'CHANNEL(language)';
					}
					$agi -> set_variable($lg_var_set, $language);
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[SET $lg_var_set $language]");
				}
				
				$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[credit=".$this->credit." :: tariff=".$this->tariff." :: active=".$this->active." :: isused=$isused :: simultaccess=$simultaccess :: typepaid=".$this->typepaid." :: creditlimit=$creditlimit :: language=$language]");
				
				
				
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
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[ERROR CHECK CARD : $prompt (cardnumber:".$this->cardnumber.")]");
					$res = -2;
					break;
				}
							
			} // For end			
		}elseif ($callerID_enable==0){
		
			// 		  -%-%-%-%-%-%-		IF NOT PREVIOUS WE WILL ASK THE CARDNUMBER AND AUTHENTICATE ACCORDINGLY 	-%-%-%-%-%-%-				
			for ($retries = 0; $retries < 3; $retries++) {
				if (($retries>0) && (strlen($prompt)>0)){					
					$agi-> stream_file($prompt, '#');					
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
				}												
				if ($res < 0) {
					$res = -1;
					break;
				}
				
				$res = 0;
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "Requesting DTMF, CARDNUMBER_LENGTH_MAX ".CARDNUMBER_LENGTH_MAX);
				$res_dtmf = $agi->get_data($prompt_entercardnum, 6000, CARDNUMBER_LENGTH_MAX);
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "RES DTMF : ".$res_dtmf ["result"]);
				$this->cardnumber = $res_dtmf ["result"];
				
				if ($this->CC_TESTING) $this->cardnumber="2222222222";
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "CARDNUMBER ::> ".$this->cardnumber);
				
				if ( !isset($this->cardnumber) || strlen($this->cardnumber) == 0) {
					$prompt = "prepaid-no-card-entered";
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
					continue;
				}
				
				if ( strlen($this->cardnumber) > CARDNUMBER_LENGTH_MAX || strlen($this->cardnumber) < CARDNUMBER_LENGTH_MIN) {
					$prompt = "prepaid-invalid-digits";
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
					continue;
				}
				$this->username = $this->cardnumber;
				
				$QUERY =  "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, ";
				if ($this->config['database']['dbtype'] == "postgres"){
					$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id, cc_card.id_campaign, cc_card.id, useralias FROM cc_card "."LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
				}else{
					$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id, cc_card.id_campaign, cc_card.id, useralias FROM cc_card "."LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
				}
				
				$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n".print_r($result,true));
				
				if( !is_array($result)) {
					$prompt="prepaid-auth-fail";
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
					continue;
				}else{
					// 		  -%-%-%-	WE ARE GOING TO CHECK IF THE CALLERID IS CORRECT FOR THIS CARD	-%-%-%-
					if ($this->agiconfig['callerid_authentication_over_cardnumber']==1){
						
						if (!is_numeric($this->CallerID) && $this->CallerID<=0){
							$prompt="prepaid-auth-fail";
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
							continue;
						}
						
						$QUERY = " SELECT cid, id_cc_card, activated FROM cc_callerid "
								." WHERE cc_callerid.cid='".$this->CallerID."' AND cc_callerid.id_cc_card='".$result[0][23]."'";
						
						$result_check_cid = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "QUERY = $QUERY\n".print_r($result_check_cid,true));
						
						if( !is_array($result_check_cid)) {
							$prompt="prepaid-auth-fail";
							$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
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
				
				if (strlen($language)==2  && !($this->languageselected>=1))
				{
					// http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SetLanguage
					// Set(CHANNEL(language)=<lang>) 1_4 & Set(LANGUAGE()=language) 1_2
					
					if($this->agiconfig['asterisk_version'] == "1_2")
					{
						$lg_var_set = 'LANGUAGE()';
					}
					else 
					{
						$lg_var_set = 'CHANNEL(language)';
					}
					$agi -> set_variable($lg_var_set, $language);
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[SET $lg_var_set $language]");
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
					
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[CREATE AN INSTANCE IN CC_CALLERID -  QUERY_VALUES:$QUERY_VALUES, QUERY_FIELS:$QUERY_FIELS]");
					$result = $this->instance_table -> Add_table ($this->DBHandle, $QUERY_VALUES, $QUERY_FIELS, 'cc_callerid');
					
					if (!$result){
						$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[CALLERID CREATION ERROR TABLE cc_callerid]");
						$prompt="prepaid-auth-fail";
						$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, strtoupper($prompt));
						$agi-> stream_file($prompt, '#');
						return -2;
					}
				}
				
				//UPDATE THE CARD ASSIGN TO THIS CC_CALLERID								
				if ($this->agiconfig['notenoughcredit_assign_newcardnumber_cid']==1 && strlen($this->CallerID)>1 && $this -> ask_other_cardnumber==1){
					$this -> ask_other_cardnumber=0;																				
					$QUERY = "UPDATE cc_callerid SET id_cc_card='$the_card_id' WHERE cid='".$this->CallerID."'";
					$this -> debug( WRITELOG, $agi, __FILE__, __LINE__, "[Start update cc_callerid : $QUERY]");
					$result = $this -> instance_table -> SQLExec ($this->DBHandle, $QUERY, 0);
				}		
				
				if (strlen($prompt)>0){
					$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[ERROR CHECK CARD : $prompt (cardnumber:".$this->cardnumber.")]");
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
			
			$this -> callingcard_acct_start_inuse($agi,1);			
			
			if ($this->agiconfig['say_balance_after_auth']==1){
				$this -> debug( VERBOSE | WRITELOG, $agi, __FILE__, __LINE__, "[A2Billing] SAY BALANCE : $this->credit \n");
				$this -> fct_say_balance ($agi, $this->credit);
			}
				
					
		} else if ($res == -2 ) {
			$agi-> stream_file($prompt, '#');
		} else {
			$res = -1;
		}

		return $res;
	}
	
	
	function callingcard_ivr_authenticate_light (&$error_msg, $debug = false){
		$res=0;
		
		$QUERY =  "SELECT credit, tariff, activated, inuse, simultaccess, typepaid, ";
		if ($this->config['database']['dbtype'] == "postgres")
			$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, date_part('epoch',expirationdate), expiredays, nbused, date_part('epoch',firstusedate), date_part('epoch',cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign FROM cc_card ";
		else
			$QUERY .=  "creditlimit, language, removeinterprefix, redial, enableexpire, UNIX_TIMESTAMP(expirationdate), expiredays, nbused, UNIX_TIMESTAMP(firstusedate), UNIX_TIMESTAMP(cc_card.creationdate), cc_card.currency, cc_card.lastname, cc_card.firstname, cc_card.email, cc_card.uipass, cc_card.id_campaign FROM cc_card ";
		
		$QUERY .=  "LEFT JOIN cc_tariffgroup ON tariff=cc_tariffgroup.id WHERE username='".$this->cardnumber."'";
			
		if ($debug)
			echo "<br> QUERY: ". $QUERY ."<br>";
		$result = $this->instance_table -> SQLExec ($this->DBHandle, $QUERY);
			
		if( !is_array($result)) {
			if ($debug) echo $this->DBHandle->ErrorMsg() . "<br>";
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
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>' . gettext("Error : Not enough credit to call !!!") .'</b></font><br>';
			if($debug) $error_msg .= "credit = $this->credit &lt; min_credit_2call = " .$this->agiconfig['min_credit_2call'];
			return 0;
		}
		// CHECK POSTPAY
		if( $this->typepaid==1 && $this->credit <= -$creditlimit && $creditlimit!=0){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>'.gettext("Error : Not enough credit to call !!!").'</b></font><br>';
			if($debug) $error_msg .= 'credit &lt; creditlimit';
			return 0;
		}
		
		// CHECK activated=t / CARD NOT ACTIVE, CONTACT CUSTOMER SUPPORT
		if( $this->active != "t" && $this->active != "1" ){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>'.gettext("Error : Card is not active!!!").'</b></font><br>';
			return 0;
		}
		
		// CHECK IF THE CARD IS USED
		if (($isused>0) && ($simultaccess!=1)){
			$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>'.gettext("Error : Card is actually in use!!!").'</b></font><br>';
			return 0;
		}
		
		// CHECK FOR EXPIRATION  -  enableexpire ( 0 : none, 1 : expire date, 2 : expire days since first use, 3 : expire days since creation)
		if ($this->enableexpire>0){
			if ($this->enableexpire==1  && $this->expirationdate!='00000000000000' && strlen($this->expirationdate)>5){
				// expire date						
				if (intval($this->expirationdate-time())<0){ // CARD EXPIRED :(				
					$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>'.gettext("Error : Card have expired!!!").'</b></font><br>';
					return 0;	
				}
					
			}elseif ($this->enableexpire==2  && $this->firstusedate!='00000000000000' && strlen($this->firstusedate)>5 && ($this->expiredays>0)){
				// expire days since first use			
				$date_will_expire = $this->firstusedate+(60*60*24*$this->expiredays);
				if (intval($date_will_expire-time())<0){ // CARD EXPIRED :(				
				$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>'.gettext("Error : Card have expired!!!").'</b></font><br>';
				return 0;	
			}
		
			}elseif ($this->enableexpire==3  && $this->creationdate!='00000000000000' && strlen($this->creationdate)>5 && ($this->expiredays>0)){
				// expire days since creation
				$date_will_expire = $this->creationdate+(60*60*24*$this->expiredays); 
				if (intval($date_will_expire-time())<0){ // CARD EXPIRED :(				
					$error_msg = '<font face="Arial, Helvetica, sans-serif" size="2" color="red"><b>'.gettext("Error : Card have expired!!!").'</b></font><br>';
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
		
		if ($this->config['database']['dbtype'] == "postgres"){
			if (isset($this->config["database"]['hostname']) && (strlen($this->config["database"]['hostname'])>0))
				$datasource = 'pgsql://'.$this->config["database"]['user'].':'.$this->config["database"]['password'].'@'.
					$this->config["database"]['hostname'].'/'.$this->config["database"]['dbname'];
			else
				$datasource = 'pgsql://dbname='.$this->config["database"]['dbname'] .' user=' . $this->config["database"]['user'];			
		}else{
			$datasource = 'mysql://'.$this->config['database']['user'].':'.$this->config['database']['password'].'@'.$this->config['database']['hostname'].'/'.$this->config['database']['dbname'];
		}		
		$this->DBHandle = NewADOConnection($datasource);
		if (!$this->DBHandle)
			return false;
				
		return true;
	}
	
	
	function DbDisconnect()
	{
		$this -> DBHandle -> disconnect();
	}
	
	
	/*
	 * function splitable_data
	 */
	function splitable_data ($splitable_value){
		
		$arr_splitable_value = explode(",", $splitable_value);
		foreach ($arr_splitable_value as $arr_value){
			$arr_value = trim ($arr_value);
			$arr_value_explode = explode("-", $arr_value,2);
			if (count($arr_value_explode)>1){
				if (is_numeric($arr_value_explode[0]) && is_numeric($arr_value_explode[1]) && $arr_value_explode[0] < $arr_value_explode[1] ){
					for ($kk=$arr_value_explode[0];$kk<=$arr_value_explode[1];$kk++){
						$arr_value_to_import[] = $kk;
					}
				}elseif (is_numeric($arr_value_explode[0])){
					$arr_value_to_import[] = $arr_value_explode[0];
				}elseif (is_numeric($arr_value_explode[1])){
					$arr_value_to_import[] = $arr_value_explode[1];
				}
				
			}else{
				$arr_value_to_import[] = $arr_value_explode[0];
			}
		}
		
		$arr_value_to_import = array_unique($arr_value_to_import);
		sort($arr_value_to_import);
		return $arr_value_to_import;
	}


};
	
?>