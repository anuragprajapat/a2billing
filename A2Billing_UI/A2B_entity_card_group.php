<?php
require_once ("./lib/defines.php");
require_once ("./lib/module.access.php");
require_once (DIR_COMMON."Form.inc.php");
require_once (DIR_COMMON."Class.HelpElem.inc.php");
require_once (DIR_COMMON."Form/Class.SqlRefField.inc.php");

$menu_section='menu_customers';


HelpElem::DoHelp(gettext("Common settings for a group of cards (customers)"),'vcard.png');

$HD_Form= new FormHandler('cc_card_group',_("Card groups"),_("Card group"));
$HD_Form->checkRights(ACX_CUSTOMER);
$HD_Form->init();

$PAGE_ELEMS[] = &$HD_Form;
$PAGE_ELEMS[] = new AddNewButton($HD_Form);

$HD_Form->model[] = new PKeyFieldEH(_("ID"),'id');

$HD_Form->model[] = new TextFieldEH(_("Name"),'name',_('Name of group'));
$HD_Form->model[] = new SqlRefFieldN(_("Agent"),'agentid','cc_agent','id','name', _("If cards belong to an agent."));
$HD_Form->model[] = new IntField(_("Parallel access"),'simultaccess',_("Number of simultaneous calls a card can make, -1 for unlimited. Beware of negative credit!"));

$tpaid_list = array();
$tpaid_list[] = array('0',_("Prepaid"));
$tpaid_list[] = array('1',_("Postpay"));

$HD_Form->model[] = new RefField(_("Pay type"), "typepaid", $tpaid_list);
$HD_Form->model[] = new SqlRefFieldN(_("Tariff group"),'tariffgroup','cc_tariffgroup','id','name');

$HD_Form->model[] = new SqlRefFieldN(_("Currency"),'def_currency','cc_currencies','currency','name', _("Default currency for new cards in this group. This can later change per card."));
$HD_Form->model[] = new FloatField(_("VAT"),'vat',_("Value Added Tax"));

$HD_Form->model[] = new IntField(_("Invoice day"),'invoiceday',_("Day of month to issue invoice at *-*"));
//TODO more fields

//$HD_Form->model[] = new TextField(_("Username"),'login',_("Login name"));

$HD_Form->model[] = new DelBtnField();

require("PP_page.inc.php");

?>
