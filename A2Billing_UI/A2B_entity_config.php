<?php
require("./lib/defines.php");
require("./lib/module.access.php");
include_once("./lib/help.php");
require_once(DIR_COMMON."Form/Class.FormHandler.inc.php");

if (! has_rights (ACX_MISC)){
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");
	die();
}

require("./form_data/FG_var_config.inc");

$HD_Form -> init();


if (!isset($form_action))  $form_action="list"; //ask-add
if (!isset($action)) $action = $form_action;


$list = $HD_Form -> perform_action($form_action);

require("PP_header.php");

// #### HELP SECTION
show_help('config');

// #### TOP SECTION PAGE
$HD_Form -> create_toppage ($form_action);


// #### CREATE FORM OR LIST
//$HD_Form -> CV_TOPVIEWER = "menu";
if (strlen($_GET["menu"])>0) $_SESSION["menu"] = $_GET["menu"];
if($form_action == "list" || $form_action == "list"){
?>
<br>
<script language="javascript">
function go(URL)
{
	if ( Check() )
	{
		document.searchform.action = URL;		
		alert(document.searchform.action);
		document.searchform.submit();
	}
}	

function Check()
{
	if(document.searchform.filterradio[1].value == "payment")	
	{
		if (document.searchform.paymenttext.value < 0)
		{
			alert("Payment amount cannot be less than Zero.");
			document.searchform.paymenttext.focus();
			return false;
		}
	}	
	return true;
}
</script>
<form name="searchform" id="searchform" method="post" action="A2B_entity_config.php">
<input type="hidden" name="searchenabled" value="yes">
<input type="hidden" name="posted" value="1">

<table class="bar-status" width="85%" border="0" cellspacing="1" cellpadding="2" align="center">
			<tbody>			
			<tr>
				<td width="19%" align="left" valign="top" class="bgcolor_004">					
					<font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("SELECT GROUP");?></font>
				</td>				
				<td width="81%" align="left" class="bgcolor_005">
				<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
				  <td class="fontstyle_searchoptions">
				  <?php
					$DBHandle  = A2Billing::DBHandle();
					$instance_table = new Table();
					$QUERY = "SELECT * from cc_config_group"; 					
					$list_total_groups  = $instance_table->SQLExec ($DBHandle, $QUERY);		
				   ?>
				<select name="groupselect" class="form_input_select">
				<option value="-1" ><?php echo gettext("Select Group");?></option>
				<?php 
				foreach($list_total_groups as $groupname){
				?>
				<option value="<?php echo $groupname[0]?>" <?php if($groupselect == $groupname[0] || $groupname[0] == $_SESSION['ss_groupselect']) echo "selected"?>><?php echo $groupname[1]?></option>
				<?php 
				}
				?>
				</select>
					</td>					
				</tr></table></td>
			</tr>			

			<tr>
        		<td class="bgcolor_002" align="left">&nbsp;</td>
      			<td class="bgcolor_003" align="left">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
					  <td class="fontstyle_searchoptions">					<div align="center"><span class="bgcolor_005">
				      <input type="image"  name="image16" align="left" border="0" src="<?php echo Images_Path;?>/button-search.gif" />
				        </span> </div></td>
					</tr>
					</table>
	  			</td>
    		</tr>
		</tbody></table>
</FORM>
</center>

<?php 
}
$HD_Form -> create_form ($form_action, $list, $id=null) ;

require ('PP_footer.php');

?>
