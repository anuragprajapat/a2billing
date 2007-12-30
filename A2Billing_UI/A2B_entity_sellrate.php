<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
require_once (DIR_COMMON."Form/Class.RevRef.inc.php");
$menu_section='menu_ratecard';

HelpElem::DoHelp(gettext("Sell rates are the prices the end customers will pay us."));

$HD_Form= new FormHandler('cc_sellrate',_("Sell rates"),_("Sell rate"));
$HD_Form->checkRights(ACX_RATECARD);
$HD_Form->init();


$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Destination"),'destination');
$HD_Form->model[] = new SqlRefField(_("Plan"),'idrp','cc_retailplan','id','name', _("Retail plan"));

$HD_Form->model[] = new FloatField(_("Rate"),'rateinitial',_("End rate, per minute"));
$HD_Form->model[] = new IntField(_("Init Block"),'initblock',_("Set the minimum duration charged to the customer. (i.e. 30 secs)"));
$HD_Form->model[] = new IntField(_("Increment"),'billingblock',_("Set the billing increment, in seconds (billing block)"));

$HD_Form->model[] = new FloatField(_("Connect charge"),'connectcharge',_("Fixed fee applied upon connection"));
$HD_Form->model[] = new FloatField(_("Disconnect charge"),'disconnectcharge',_("Fixed fee applied <u>after the customer hangs up an answered call</u>"));

$HD_Form->model[] = new FloatField(_("Step A"),'stepchargea',_("Fee charged at beginning of period A"));
$HD_Form->model[] = new FloatField(_("Charge A"),'chargea',_("Charge (per minute) of period A"));
$HD_Form->model[] = new FloatField(_("Time charge A"),'timechargea',_("Duration of period A, in seconds"));
$HD_Form->model[] = new FloatField(_("Block A"),'billingblocka',_("Block, in seconds of charge during period A"));

$HD_Form->model[] = new FloatField(_("Step B"),'stepchargeb');
$HD_Form->model[] = new FloatField(_("Charge B"),'chargeb');
$HD_Form->model[] = new FloatField(_("Time charge B"),'timechargeb');
$HD_Form->model[] = new FloatField(_("Block B"),'billingblockb');

$HD_Form->model[] = new FloatField(_("Step C"),'stepchargec');
$HD_Form->model[] = new FloatField(_("Charge C"),'chargec');
$HD_Form->model[] = new FloatField(_("Time charge C"),'timechargec');
$HD_Form->model[] = new FloatField(_("Block C"),'billingblockc');

for ($i=8;$i<count($HD_Form->model);$i++)
	$HD_Form->model[$i]->does_add=$HD_Form->model[$i]->does_list=false;

$HD_Form->model[] = new RevRefTxt(_("Prefixes"),'prefx','id','cc_sell_prefix','srid','dialprefix',_("Dial prefixes covered by this rate."));

//RevRef2::html_body($action);

$HD_Form->model[] = new DelBtnField();

	// Add import functionality to the entity
require_once(DIR_COMMON."Form/Class.ImportView.inc.php");
require_once(DIR_COMMON."Class.DynConf.inc.php");

$HD_Form->views['ask-import'] = new AskImportView();
$HD_Form->views['import-analyze'] = new ImportAView($HD_Form->views['ask-import']);
$HD_Form->views['import'] = new ImportView($HD_Form->views['ask-import']);

$HD_Form->views['ask-import']->common = array('idrp');
$HD_Form->views['ask-import']->mandatory = array('prefx','destination', 'rateinitial');
$HD_Form->views['ask-import']->optional = array('initblock','billingblock','connectcharge','disconnectcharge',
					'stepchargea','chargea','timechargea','billingblocka',
					'stepchargeb','chargeb','timechargeb','billingblockb',
					'stepchargec','chargec','timechargec','billingblockc');

$HD_Form->views['ask-import']->examples = array( array(_('Simple'), "importsamples.php?sample=RateCard_Simple"),
					     array(_('Complex'),"importsamples.php?sample=RateCard_Complex"));

$HD_Form->views['import-analyze']->allowed_mimetypes=array('text/csv');
$HD_Form->views['ask-import']->multiple[] = 'prefx';
$HD_Form->views['ask-import']->multi_sep = '|';

require("PP_page.inc.php");
?>
