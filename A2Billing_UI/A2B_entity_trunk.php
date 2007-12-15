<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");
// include ("./form_data/FG_var_agent.inc");
// include ("./lib/help.php");

$menu_section='menu_trunk';


HelpElem::DoHelp(gettext("Trunks are used to terminate the call!<br>" .
			"The trunk and ratecard is selected by the rating engine on the basis of the dialed digits.<br>" .
			"The trunk is used to dial out from your asterisk box which can be a zaptel interface or a voip provider."),
			'hwbrowser.png');

$HD_Form= new FormHandler('cc_trunk',_("Trunks"),_("Trunk"));
$HD_Form->checkRights(ACX_TRUNK);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$status_list = array();
$status_list[] = array('1',gettext("Active"));
$status_list[] = array('0',gettext("Inactive"));

$trunkfmt_list = array();
$trunkfmt_list[] = array('1','<Tech>/<IP>/<Number>');
$trunkfmt_list[] = array('2','<Tech>/<Number>@<IP>');
$trunkfmt_list[] = array('3','<Tech>/<IP>');

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');
$HD_Form->model[] = new TextFieldEH(_("Label"),'trunkcode',_("Human readable name for the agent"));
$HD_Form->model[] = new SqlRefFieldN(_("Provider"), "provider","cc_provider", "id", "provider_name");

$HD_Form->model[] = new TextField(_("Prefix"),'trunkprefix',_("Add a prefix to the dialled digits."));
$HD_Form->model[] = new TextField(_("Remove Prefix"),'removeprefix',_("In case of the voip provider or the gateway doesnt want a dialed prefix (can be useful with local gateway)"));

$HD_Form->model[] = new RefField(_("Format"), "trunkfmt", $trunkfmt_list,_("Select the desired format for the Dial string"));
$HD_Form->model[] = new TextField(_("Tech"),'providertech',_("Technology used on the trunk (SIP,IAX2,ZAP,H323)"));
$HD_Form->model[] = new TextField(_("Provider IP"), "providerip", _("Set the IP or URL of the VoIP provider. Alternatively, put in the name of a previously defined trunk in Asterisk. (MyVoiPTrunk, ZAP4G etc.) You can use the following tags to as variables: *-* %dialingnumber%, %cardnumber%. ie g2/1644787890wwwwwwwwww%dialingnumber%"));
// end($HD_Form->model)->fieldacr =  gettext("ACT");

$HD_Form->model[] = new TextField(_("Additional parameter"), "addparameter", _("Define any additional parameters that will be used when running the Dial Command in Asterisk. Use the following tags as variables  *-* %dialingnumber%, %cardnumber%. ie 'D(ww%cardnumber%wwwwwwwwww%dialingnumber%)'"));

// $HD_Form->model[] = new TextField(_("Additional parameter"), "addparam", _());
// $HD_Form->model[] = new TextField(_("Additional parameter"), "addparam", _());

$HD_Form->model[] = new RefField(_("Status"), "status", $status_list,_("Allow the agent to operate"),"4%");

// TODO: inuse, maxuse, if_max_use (atomic!)

$HD_Form->model[] = new DelBtnField();


require("PP_page.inc.php");

?>
