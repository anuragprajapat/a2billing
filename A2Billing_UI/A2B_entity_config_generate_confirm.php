<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_config.inc");
include ("../lib/smarty.php");

if (! has_rights (ACX_MISC)){
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");
	die();
}

/***********************************************************************************/

$HD_Form -> setDBHandler (DbConnect());


$HD_Form -> init();

// #### HEADER SECTION
$smarty->display('main.tpl');

// #### HELP SECTION
if ($form_action=='list') echo $CC_help_add_agi_confx;
else echo $CC_help_add_agi_confx;


// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);

// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];

$link = "A2B_entity_config.php?form_action=list&atmenu=config&stitle=Configuration&section=8&agi_conf=true";

$config_group = array();
$config_group  = agi_confx_title(); // calling function  to generate agi-conf(title_number)
$group_title = $config_group[0];
$group_description = $config_group[2];
?>
<table width="92%" align="center" class="bar-status">
	<tr>
		<td>
			<table width="100%" style="border:1px solid">
			<thead>
				<tr>
					<td colspan="2"  class="bgcolor_005"><font style="color:#FFFFFF;padding-left:3px"><strong><?=gettext("Group Configurations");?></strong></font></td>
				</tr>
			</thead>
			<tbody>
				<tr class="form_head">
					<th class="tableBody" style="padding: 2px;" align="center"><?=gettext("Title")?></th>
					<th class="tableBody" style="padding: 2px;" align="center"><?=gettext("Description")?></th>
				</tr>
				<tr bgcolor="#FCFBFB"  onmouseover="bgColor='#FFDEA6'" onMouseOut="bgColor='#FCFBFB'">
					<td class="tableBody"><?=$group_title?></td>
					<td class="tableBody"><?=$group_description?></td>
				</tr>
			</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td width="70%">&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table width="100%" style="border:1px solid">
			<thead>
				<tr>
					<td colspan="5" class="bgcolor_005"><font style="color:#FFFFFF;padding-left:3px"><strong><?=gettext("List of Configurations")?></strong></font></td>
				</tr>
			</thead>
			<tbody>
				<tr class="form_head">
					<th align="center" width="15%" class="tableBody" style="padding: 2px;" ><?=gettext("Title")?></th>
					<th class="tableBody" style="padding: 2px;" align="center" width="10%"><?=gettext("Key")?></th>
					<th class="tableBody" style="padding: 2px;" align="center" width="10%"><?=gettext("Value")?></th>
					<th class="tableBody" style="padding: 2px;" align="center" width="50%"><?=gettext("Description")?></th>
					<th class="tableBody" style="padding: 2px;" align="center" width="5%"><?=gettext("Group")?></th>
				</tr>
<?
$DBHandle  = DbConnect();
$instance_table = new Table();

$QUERY = "SELECT config_title,config_key,config_value,config_description from cc_config where config_group_id = 11 order by id limit 10"; 					
$config  = $instance_table->SQLExec ($DBHandle, $QUERY);	
$i=0;	
foreach($config as $values){
	$config_title = $values[0]; 
	$config_key = $values[1]; 
	$config_value = $values[2]; 
	$config_description = $values[3]; 
	if($i % 2 == 0){
		$bgcolor = "bgColor='#FCFBFB'"; 
	}else{
		$bgcolor = "bgColor='#F2F2EE'";	
	}
	
?>				
				<tr <?=$bgcolor?> onmouseover="bgColor='#FFDEA6'" onMouseOut="<?=$bgcolor?>">
					<td align="left" class="tableBody"><?=$config_title?></td>
					<td align="left" class="tableBody"><?=$config_key?></td>
					<td align="left" class="tableBody"><?=$config_value?></td>
					<td align="left" class="tableBody"><?=$config_description?></td>
					<td align="left" class="tableBody"><?=$group_title?></td>
				</tr>
<?php $i++;}?>				
			</tbody>
			</table>
		</td>
	</tr>
	<br>
	<?php 
	$text = gettext("CREATE");
	$group_title = $text." ".ucwords($group_title);
	?>
	<tr>
		<td align="right">
		<form name="theform">
		<input class="form_input_button" 
				TYPE="button" VALUE="<?=$group_title;?>" onClick="window.open('<?=$link?>')">
		</form></td>
	</tr>
</table>

<?
// #### FOOTER SECTION
$smarty->display('footer.tpl');
?>
