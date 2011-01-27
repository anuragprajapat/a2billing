<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once ("a2blib/Form.inc.php");
// require_once ("a2blib/Class.HelpElem.inc.php");
require_once ("a2blib/Form/Class.SqlRefField.inc.php");
require_once ("a2blib/Form/Class.TimeField.inc.php");
require_once ("a2blib/Form/Class.ClauseField.inc.php");
require_once ("a2blib/Form/Class.ListSumView.inc.php");
require_once ("a2blib/Form/Class.SumMultiView.inc.php");
require_once ("a2blib/Form/Class.RevRefForm.inc.php");

require_once ("a2blib/Class.SqlActionElem.inc.php");

$menu_section='menu_invoicing';

// NOTE: for agents, we MUST put an agent clause in each (sub)form 

// Use a detail view to list the invoice details
$dform= new FormHandler('cc_invoices',_("Transactions"),_("Transaction"));
$dform->checkRights(ACX_AGENTS);
$dform->init(null,false);
$dform->setAction('details');
$dform->views['details'] = new DetailsView();

$dform->model[] = new PKeyField(_("ID"),'id');
$dform->model[] = new TextField(_("Ref"), "orderref");

$dform->model[] = new DateTimeFieldDH(_("Period begin"),'cover_startdate');
$dform->model[] = new DateTimeField(_("Period end"),'cover_enddate');
$dform->model[] = dontList(new DateTimeField(_("Invoice date"),'created',_("Date this invoice was registered")));

$dform->model[] =new MoneyField(_("Amount"),'amount');
$dform->model[] =new MoneyField(_("Tax"),'tax');

$dform->model[] = new MoneyField(_("Total"),'total');

//$dform->model[] = new IntField(_("Type"), "invoicetype" /*,"cc_texts", "id", "txt"*/);
//end($dform->model)->refclause = "lang = 'C'";

//$dform->model[] = dontList(new TextField(_("Filename"), "filename"));

//$dform->model[] = new SqlBigRefField(_("Invoice"), "invoice_id","cc_invoices", "id", "orderref");
//end($dform->model)->refclause = "agentid IS NOT NULL";

//$dform->model[] = dontList( new TextAreaField(_("Description"),'descr'));

$ilist = array();
$ilist[]  = array("0", _("Unpaid"));
$ilist[]  = array('1',_('Sent-unpaid'));
$ilist[]  = array('2',_('Sent-paid'));
$ilist[]  = array('3',_('Paid'));

$dform->model[] = new RefField(_("Status"),'payment_status', $ilist);
	
$tmp = new RevRefForm(_("calls"),'call','id','cc_agent_calls3_v','invoice_id');
$dform->meta_elems[] = $tmp;
	$tmp->at_action = 'details';
	$tmp->Form->checkRights(ACX_AGENTS);
	$tmp->Form->init(null,false);
	$tmp->Form->views['list'] = new Multi2SumView();
	$tmp->Form->views['list']->page_cols = 2;
	
	$tmp->Form->model[] = new FreeClauseField("agentbill IS NOT NULL");
	$tmp->Form->model[] = new DateField(_("Date"),'starttime');
		end($tmp->Form->model)->fieldexpr = "date_trunc('day',starttime)";
	//$tmp->Form->model[] = new TextField(_("Number"),'calledstation');
	$tmp->Form->model[] = new TextField(_("Destination"),'destination');
	$tmp->Form->model[] = new SecondsField(_("Duration"),'sessiontime');
	end($tmp->Form->model)->fieldacr=_("Dur");
	//$tmp->Form->model[] = new PKeyFieldTxt(_("ID"),'id');
	$tmp->Form->model[] = new MoneyField(_("Bill"),'agentbill');

	/*	//one non-summed group
	$tmp->Form->views['list']->sums[] =array( 'fns' => array( 'starttime' => true,
			'calledstation' => true,
			'destination' => true,
			'sessiontime' => true, 'agentbill' => true)); */
			
		// sum per day/destination
	$tmp->Form->views['list']->sums[] =array( 'fns' => array( 'starttime' => true,
		'calledstation' => true,
		'destination' => true,
		'sessiontime' => 'SUM', 'agentbill' => 'SUM'),
		'order' => 'starttime');


		//Per day/destination
	$tmp->Form->views['list']->sums[] =array( 'title' => _("Sum per destination"),
		'fns' => array( 'starttime' => false,
				'destination' => true,
				'sessiontime' => 'SUM', 
				'agentbill' => 'SUM'),
		'order' => 'sessiontime', 'sens' => 'DESC');

	$tmp->Form->views['list']->sums[] =array('title' => _("Total"),
		'fns' => array( 'calledstation' => 'COUNT',
			'sessiontime' => 'SUM', 'agentbill' => 'SUM'));
	
$hform= new FormHandler('cc_agent');
$hform->checkRights(ACX_AGENTS);
$hform->init(null,false);
$hform->setAction('details');
$hform->views['details'] = new DetailsView();

$hform->model[] = new FreeClauseField(str_dbparams(A2Billing::DBHandle(),
		'id = (SELECT agentid FROM cc_invoices WHERE id = %#1)',
		array($dform->getpost_dirty('id'))));
//$hform->model[] = new PKeyField(_("ID"),'id');
$hform->model[] = new TextField(_("Name"), "name");
$hform->model[] = new TextField(_("Address"), "location");
//$hform->model[] = new TextField(_("Name"), "name");

$hform->model[] = new SqlRefField(_("Tariff Plan"), "tariffgroup","cc_tariffgroup", "id", "pubname");

$PAGE_ELEMS[] = &$hform;	
$PAGE_ELEMS[] = &$dform;

if (isset($_GET['printable']) && ($_GET['printable']) )
	require("PP_print.inc.php");
else
	require("PP_page.inc.php");

?>
