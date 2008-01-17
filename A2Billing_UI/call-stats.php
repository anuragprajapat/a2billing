<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");
require_once (DIR_COMMON."Form/Class.TextSearchField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.SelectionForm.inc.php");
require_once (DIR_COMMON."Form/Class.SumMultiView.inc.php");

$menu_section='menu_creport';

$SEL_Form = new SelectionForm();
$SEL_Form->init();
$SEL_Form->model[] = new DateTimeField(_("Period from"),'date_from');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = '00:00 last month';
	end($SEL_Form->model)->fieldexpr = 'starttime';
$SEL_Form->model[] = new DateTimeField(_("Period to"),'date_to');
	end($SEL_Form->model)->does_add = false;
	end($SEL_Form->model)->def_date = 'now';
	end($SEL_Form->model)->fieldexpr = 'starttime';

/*$SEL_Form->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name');
	end($SEL_Form->model)->does_add = false;*/

$SEL_Form->search_exprs['date_from'] = '>=';
$SEL_Form->search_exprs['date_to'] = '<=';

$SEL_Form->model[] = new TextSearchField(_("Destination"),'destination');
$SEL_Form->model[] = new TextSearchField(_("Called number"),'calledstation');
/*$SEL_Form->model[] = new SqlRefField(_("Plan"),'idrp','cc_retailplan','id','name', _("Retail plan"));
	end($SEL_Form->model)->does_add = false;*/
$SEL_Form->model[] =dontAdd(new SqlRefFieldN(_("Server"),'srvid','cc_a2b_server','id','host'));
$SEL_Form->model[] = dontAdd(new SqlRefFieldN(_("Trunk"),'trunk','cc_trunk','id','trunkcode',
		 _("Trunk used for the call")));

$PAGE_ELEMS[] = &$SEL_Form;

// HelpElem::DoHelp(gettext("*-*"),'vcard.png');

$sform= new FormHandler('cc_call_v',_("Calls"),_("Call"));
$sform->checkRights(ACX_CALL_REPORT);
$sform->init(null,false);
$sform->setAction('sums');
$sform->views['sums'] = new SumMultiView();
if ($FG_DEBUG)
	$sform->views['dump-form'] = new DbgDumpView();

$clauses= $SEL_Form->buildClauses();
foreach($clauses as $cla)
	$sform->model[] = new FreeClauseField($cla);

$sform->model[] = new DateField(_("Date"),'starttime');
	end($sform->model)->fieldexpr='date_trunc(\'day\', starttime)';
$sform->model[] = new TextField(_("Destination"), "destination");
	end($sform->model)->fieldacr = _("Dest");

$sform->model[] = new IntField(_("Calls"),'uniqueid');

$sform->model[] = new SecondsField(_("Duration"), "sessiontime");

$sform->model[] = new PercentField(_("Answer to Seizure Ratio"),'asr');
	end($sform->model)->fieldacr = _("ASR");
	end($sform->model)->fieldexpr= 'COUNT(CASE WHEN tcause = \'ANSWER\' THEN 1 ELSE null END)::FLOAT / COUNT(uniqueid)';

$sform->model[] = new SecondsField(_("Average Length of Calls"), "aloc");
	end($sform->model)->fieldacr = _("ALOC");
	end($sform->model)->fieldexpr= 'sessiontime';
	
	//$Sum_Form->model[] = new FloatField(_("Credit"), "pos_charge");
$sform->model[] = new MoneyField(_("Bill"), "sessionbill");
$sform->model[] = new MoneyField(_("Cost"), "buycost");

$sform->views['sums']->sums[] = array('title' => _("Per day calls"),
	'fns' => array( 'starttime' =>true, 'uniqueid' => 'COUNT',
		'sessiontime' => 'SUM', 'asr' => '', 'aloc' => 'AVG',
		'sessionbill' => 'SUM', 'buycost' => 'SUM'),
	'order' => 'starttime', 'sens' => 'DESC');

$sform->views['sums']->sums[] = array('title' => _("Per destination calls"),
	'fns' => array( 'destination' =>true, 'uniqueid' => 'COUNT',
		'sessiontime' => 'SUM', 'asr' => '', 'aloc' => 'AVG',
		'sessionbill' => 'SUM', 'buycost' => 'SUM'),
	'order' => 'COUNT(uniqueid)', 'sens' => 'DESC');

$PAGE_ELEMS[] = &$sform;

require("PP_page.inc.php");

?>
