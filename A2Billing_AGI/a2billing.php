#!/usr/bin/php -q
<?php   
/***************************************************************************
 *
 * a2billing.php : PHP A2Billing Core
 * Written for PHP 5.X versions.
 *
 * A2Billing -- Asterisk billing solution.
 * Copyright (C) 2007, P. Christeas <p_christeas A yahoo.com>
 * Copyright (C) 2004, 2007 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.asterisk2billing.org for more information about
 * the A2Billing project. 
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/

/* Dev's note: use "conlog()" for debug-only messages, "verbose()" for things that
  will always output. */

declare(ticks = 1);

function sig_handler($signo)
{

     switch ($signo) {
         case SIGTERM:
             // handle shutdown tasks
             throw new Exception("Term signal!",SIGTERM);
             break;
         case SIGHUP:
             // Better ignore it..
             //throw new Exception("Hangup signal!",SIGHUP);
             break;
         case SIGINT:
             throw new Exception("Interrupt signal!",SIGINT);
             break;
         case SIGUSR1:
             echo "Caught SIGUSR1...\n";
             break;
         default:
             echo "Caught sighal $signo ..\n";
             // handle all other signals
     }

}

// Required!
pcntl_signal(SIGHUP, 'sig_handler');
pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

require_once('a2blib/Class.Config.inc.php');
require_once('a2blib/Misc.inc.php');
require_once('a2blib/Class.DynConf.inc.php');
require_once('phpagi/phpagi.php');

$charge_callback=0;
$G_startime = time();
$agi_date = "Release: lost";
$agi_version = "Asterisk2Billing - Version v220/xrg - Alpha";
$conf_file = NULL;

if ($argc > 1 && ($argv[1] == '--version' || $argv[1] == '-V'))
{
	echo "A2Billing - Version $agi_version - $agi_date\n";
	exit;
}
$verbose_mode = false;

if ($argc > 1 && ($argv[1] == '--verbose' || $argv[1] == '-v')){
	AGI::verbose_s("Verbose mode!",0);
	error_reporting(E_ALL);
	$verbose_mode = true;
	array_shift($argv);
	$argc--;
}

if ($argc > 1 && ($argv[1] == '--test')){
	AGI::verbose_s("Testing mode!",0);
	define('DEFAULT_CONFIG', "../a2billing.conf");
	array_shift($argv);
	$argc--;
} else {
	define('DEFAULT_CONFIG', '/etc/a2billing.conf');
}


require_once('Class.A2Billing.inc.php');

// create the objects
$a2b = A2Billing::instance();
if(!$a2b->load_res_dbsettings('/etc/asterisk/res_pgsql.conf')){
	@syslog(LOG_ERR,"Cannot fetch settings from res_pgsql.conf");
	exit(2);
}
$dynconf = DynConf::instance();

if ($argc > 1 && is_numeric($argv[1]) && $argv[1] >= 0){
	$idconfig = $argv[1];
}else{
	$idconfig = 1;
}

try {
	$dynconf->init();
	$dynconf->PrefetchGroup('agiconf'.$idconfig);
} catch (Exception $ex){
	error_log($ex->getMessage());
	@syslog(LOG_ERR,"Cannot Fetch config!");
	@syslog(LOG_ERR,$ex->getMessage());
	exit();
}

if ($verbose_mode)
	$dynconf->SetDefVar('agiconf'.$idconfig,'debug',true);

$agi = new AGI($dynconf,'agiconf'.$idconfig);

function getAGIconfig($var,$default){
	global $dynconf;
	global $idconfig;
	return $dynconf->GetCfgVar('agiconf'.$idconfig,$var,$default);
}

if (!$agi->is_alive)
	exit();

$mode = 'standard';
// get the running mode -> DeadAGI(a2billing.php|1|voucher)
if ($argc > 2 && strlen($argv[2]) > 0 )
	switch ($argv[2]) {
	case 'did':
	case 'callback':
	case 'cid-callback':
	case 'all-callback':
	case 'predictivedialer':
	case 'voucher':
		$mode = $argv[2];
		break;
	default:
		$mode = 'standard';
	}

// get the area code for the cid-callback & all-callback
if ($argc > 3 && strlen($argv[3]) > 0) $caller_areacode = $argv[3];

/** Match and return string from prefix.
   \param $match_empty  Succeed if $prefix is empty or fail..
   \param $prefix the string $str must start from
*/
function str_match($str, $prefix, $match_empty =false) {
	$len = strlen($prefix);
	if ($len<1){
		if ($match_empty)
			return $str;
		else
			return false;
	}
	
	if (strncmp($str,$prefix,$len)==0)
		return substr($str,$len);
	else
		return false;
}

require("cardfns.inc.php");
require("dialfns.inc.php");
include("voucher.inc.php");
require("dialstring.inc.php");
require("dialspecial.inc.php");


switch ($mode){
case 'standard':
	require("mode-standard.inc.php");
	break;
case 'voucher':
	require("mode-voucher.inc.php");
	break;
case 'did':
	require("mode-did.inc.php");
	break;
default:
	@syslog(LOG_ERR,"A2Billing AGI: cannot handle mode $mode");
	$agi->verbose("Cannot handle mode $mode",1);
	exit(1);
}

//exit();
?>
