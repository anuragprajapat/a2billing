<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
// require_once (DIR_COMMON."Class.HelpElem.inc.php");
// require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.TimeField.inc.php");
require_once (DIR_COMMON."Form/Class.ClauseField.inc.php");
require_once (DIR_COMMON."Form/Class.ListSumView.inc.php");
require_once (DIR_COMMON."Form/Class.SumMultiView.inc.php");
require_once (DIR_COMMON."Form/Class.RevRefForm.inc.php");

require_once (DIR_COMMON."Class.SqlActionElem.inc.php");

$menu_section='menu_agents';

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

$tmp = new RevRefForm(_("calls"),'call','id','cc_call_v','invoice_id');
$dform->meta_elems[] = $tmp;
	$tmp->at_action = 'details';
	$tmp->Form->checkRights(ACX_AGENTS);
	$tmp->Form->init(null,false);
	$tmp->Form->views['list'] = new SumMultiView();
	
	$tmp->Form->model[] = new DateTimeField(_("Time"),'starttime');
	$tmp->Form->model[] = new TextField(_("Number"),'calledstation');
	$tmp->Form->model[] = new TextField(_("Destination"),'destination');
	$tmp->Form->model[] = new IntField(_("Duration"),'sessiontime');
	end($tmp->Form->model)->fieldacr=_("Dur");
	//$tmp->Form->model[] = new PKeyFieldTxt(_("ID"),'id');
	$tmp->Form->model[] = new MoneyField(_("Bill"),'sessionbill');

		//one non-summed group
	$tmp->Form->views['list']->sums[] =array( 'fns' => array( 'starttime' => true,
			'calledstation' => true,
			'destination' => true,
			'sessiontime' => true, 'sessionbill' => true));

		//Per day/destination
	$tmp->Form->views['list']->sums[] =array( 'title' => _("Sum per destination"),
		'fns' => array( 'starttime' => false,
				'destination' => true,
				'sessiontime' => 'SUM', 
				'sessionbill' => 'SUM'));

	$tmp->Form->views['list']->sums[] =array('title' => _("Total"),
		'fns' => array( 'calledstation' => 'COUNT',
			'sessiontime' => 'SUM', 'sessionbill' => 'SUM'));
	
	
$PAGE_ELEMS[] = &$dform;

require("PP_page.inc.php");

?>
