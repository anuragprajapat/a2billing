<?php
$menu_section='menu_creport';
include ("../lib/defines.php");
include ("../lib/module.access.php");


if (! has_rights (ACX_CALL_REPORT)) {
	Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

getpost_ifset(array('customer', 'entercustomer', 'enterprovider', 'entertariffgroup', 'entertrunk', 'enterratecard', 'posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'dsttype', 'srctype', 'clidtype', 'channel', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens', 'dst', 'src', 'clid', 'choose_agent', 'choose_currency', 'terminatecause'));



if (($_GET[download]=="file") && $_GET[file] ) {
	
	$value_de=base64_decode($_GET[file]);
	$dl_full = MONITOR_PATH."/".$value_de;
	$dl_name=$value_de;

	if (!file_exists($dl_full)) { 
		echo gettext("ERROR: Cannot download file ".$dl_full.", it does not exist.<br>");
		exit();
	}
	
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$dl_name");
	header("Content-Length: ".filesize($dl_full));
	header("Accept-Ranges: bytes");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-transfer-encoding: binary");
	
	@readfile($dl_full);
	exit();
}


if (!isset ($current_page) || ($current_page == "")) {
	$current_page=0; 
}


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_call t1 LEFT OUTER JOIN cc_trunk t3 ON t1.id_trunk = t3.id_trunk";

if ($_SESSION["is_admin"]==0) {
 	$FG_TABLE_NAME.=", cc_card t2";
}


// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";

$yesno = array();
$yesno["1"]  = array( "Yes", "1");
$yesno["0"]  = array( "No", "0");

// 0 = NORMAL CALL ; 1 = VOIP CALL (SIP/IAX) ; 2= DIDCALL + TRUNK ; 3 = VOIP CALL DID ; 4 = CALLBACK call
$list_calltype = array();
$list_calltype["0"]  = array( "STANDARD", "0");
$list_calltype["1"]  = array( "SIP/IAX", "1");
$list_calltype["2"]  = array( "DIDCALL", "2");
$list_calltype["3"]  = array( "DID_VOIP", "3");
$list_calltype["4"]  = array( "CALLBACK", "4");
$list_calltype["5"]  = array( "PREDICT", "5");


$DBHandle  = DbConnect();

$FG_TABLE_COL = array();
$FG_TABLE_COL[]=array (gettext("Calldate"), "starttime", "15%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Source"), "src", "7%", "center", "SORT", "30");
$FG_TABLE_COL[]=array (gettext("CalledNumber"), "calledstation", "10%", "center", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Destination"), "destination", "10%", "center", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Duration"), "sessiontime", "6%", "center", "SORT", "30", "", "", "", "", "", "display_minute");
$FG_TABLE_COL[]=array (gettext("CardUsed"), "username", "6%", "center", "SORT", "", "30", "", "", "", "", "linktocustomer");
$FG_TABLE_COL[]=array (gettext("Trunk"), "trunkcode", "6%", "center", "SORT", "30");
$FG_TABLE_COL[]=array ('<acronym title="'.gettext("Terminate Cause").'">'.gettext("TC").'</acronym>', "terminatecause", "7%", "center", "SORT", "30");
$FG_TABLE_COL[]=array (gettext("Calltype"), "sipiax", "6%", "center", "SORT",  "", "list", $list_calltype);
$FG_TABLE_COL[]=array (gettext("Buy"), "buycost", "8%", "center", "SORT", "30", "", "", "", "", "", "display_2bill");
$FG_TABLE_COL[]=array (gettext("Sell"), "sessionbill", "8%", "center", "SORT", "30", "", "", "", "", "", "display_2bill");
$FG_TABLE_COL[]=array (gettext("Margin"), "margin", "7%", "center", "SORT", "30", "", "", "", "", "", "display_2dec_percentage");
$FG_TABLE_COL[]=array (gettext("Markup"), "markup", "7%", "center", "SORT", "30", "", "", "", "", "", "display_2dec_percentage");

if (LINK_AUDIO_FILE){
	$FG_TABLE_COL[]=array ("", "uniqueid", "1%", "center", "", "30", "", "", "", "", "", "linkonmonitorfile");
}

$FG_TABLE_DEFAULT_ORDER = "t1.starttime";
$FG_TABLE_DEFAULT_SENS = "DESC";
	
// This Variable store the argument for the SQL query
$FG_COL_QUERY='t1.starttime, t1.src, t1.calledstation, t1.destination, t1.sessiontime, t1.username, t3.trunkcode, t1.terminatecause, t1.sipiax, t1.buycost, t1.sessionbill, case when t1.sessionbill!=0 then ((t1.sessionbill-t1.buycost)/t1.sessionbill)*100 else NULL end as margin,case when t1.buycost!=0 then ((t1.sessionbill-t1.buycost)/t1.buycost)*100 else NULL end as markup';
if (LINK_AUDIO_FILE) {
	$FG_COL_QUERY .= ', t1.uniqueid';
}

$FG_COL_QUERY_GRAPH='t1.callstart, t1.duration';

// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=25;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

// The variable $FG_EDITION define if you want process to the edition of the database record
$FG_EDITION=true;

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE = gettext(" - Call Logs - ");

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH = '98%';

if ($FG_DEBUG >= 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";
$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);
$instance_table_graph = new Table($FG_TABLE_NAME, $FG_COL_QUERY_GRAPH);


if ( is_null ($order) || is_null($sens) ) {
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}

if ($posted==1) {
	$SQLcmd = '';
	$SQLcmd = do_field($SQLcmd, 'src', 'src');
	$SQLcmd = do_field($SQLcmd, 'dst', 'calledstation');
}


$date_clause='';
if (DB_TYPE == "postgres") {
	$UNIX_TIMESTAMP = "";
} else {
	$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}
$lastdayofmonth = date("t", strtotime($tostatsmonth.'-01'));
if ($Period=="Month") {
	if ($frommonth && isset($fromstatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
	if ($tomonth && isset($tostatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')"; 
} else {
	if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday')";
	if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday)/*+1*/)." 23:59:59')";
}


  
if (strpos($SQLcmd, 'WHERE') > 0) { 
	$FG_TABLE_CLAUSE = substr($SQLcmd,6).$date_clause; 
} elseif (strpos($date_clause, 'AND') > 0) {
	$FG_TABLE_CLAUSE = substr($date_clause,5); 
}


if (!isset ($FG_TABLE_CLAUSE) || strlen($FG_TABLE_CLAUSE)==0) {
	$cc_yearmonth = sprintf("%04d-%02d-%02d",date("Y"),date("n"),date("d"));
	$FG_TABLE_CLAUSE=" $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$cc_yearmonth')";
}


if (isset($customer)  &&  ($customer>0)) {
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE.="t1.username='$customer'";
} else {
	if (isset($entercustomer)  &&  ($entercustomer>0)) {
		if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
		$FG_TABLE_CLAUSE.="t1.username='$entercustomer'";
	}
}

if ($_SESSION["is_admin"] == 1) {
	if (isset($enterprovider) && $enterprovider > 0) {
		if (strlen($FG_TABLE_CLAUSE) > 0) $FG_TABLE_CLAUSE .= " AND ";
		$FG_TABLE_CLAUSE .= "t3.id_provider = '$enterprovider'";
	}
	if (isset($entertrunk) && $entertrunk > 0) {
		if (strlen($FG_TABLE_CLAUSE) > 0) $FG_TABLE_CLAUSE .= " AND ";
		$FG_TABLE_CLAUSE .= "t3.id_trunk = '$entertrunk'";
	}
	if (isset($entertariffgroup) && $entertariffgroup > 0) {
		if (strlen($FG_TABLE_CLAUSE) > 0) $FG_TABLE_CLAUSE .= " AND ";
		$FG_TABLE_CLAUSE .= "t1.id_tariffgroup = '$entertariffgroup'";
	}
	if (isset($enterratecard) && $enterratecard > 0) {
		if (strlen($FG_TABLE_CLAUSE) > 0) $FG_TABLE_CLAUSE .= " AND ";
		$FG_TABLE_CLAUSE .= "t1.id_ratecard = '$enterratecard'";
	}
}

if ($_SESSION["is_admin"]==0) {
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE.="t1.cardID=t2.IDCust AND t2.IDmanager='".$_SESSION["pr_reseller_ID"]."'";
}

if (isset($choose_agent) && ($choose_agent != '')) {
	switch ($choose_agent) {
	case 'all':
		$tmp_agent_clause = 't1.username IN (SELECT cc_card.username FROM cc_card, cc_agent_cards WHERE cc_card.id = cc_agent_cards.card_id)';
		break;
	case 'no':
		$tmp_agent_clause = 't1.username NOT IN (SELECT cc_card.username FROM cc_card, cc_agent_cards WHERE cc_card.id = cc_agent_cards.card_id)';
		break;
	default:
		$tmp_agent_clause = str_dbparams($DBHandle,'t1.username IN (SELECT cc_card.username FROM cc_card, cc_agent_cards WHERE cc_card.id = cc_agent_cards.card_id AND cc_agent_cards.agentid = %1)',
			array((integer)$choose_agent ));
		break;
	}
}
if (true) { /*-*/ 	
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE.=$tmp_agent_clause;
}
$FG_ASR_CIC_CLAUSE = $FG_TABLE_CLAUSE;
//To select just terminatecause=ANSWER
if (!isset($terminatecause)) {
	$terminatecause="ANSWER";
}
if ($terminatecause=="ANSWER") {
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE .= " (t1.terminatecause='ANSWER' OR t1.terminatecause='ANSWERED') ";
}


if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
}

// EXPORT 
// NOTE : MAYBE REWRITE THIS PAGE FROM THE FRAMEWORK
$FG_EXPORT_SESSION_VAR = "pr_export_entity_call";

// Query Preparation for the Export Functionality
$_SESSION[$FG_EXPORT_SESSION_VAR]= "SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

if (!is_null ($order) && ($order!='') && !is_null ($sens) && ($sens!='')) {
	$_SESSION[$FG_EXPORT_SESSION_VAR].= " ORDER BY $order $sense";
}


//************************************************************/
// calculate nbr of success calls,nbr fail calls, max nbr of fail calls successfally /
//************************************************************/

$QUERY="CREATE TEMPORARY TABLE ASR_CIC_TEMP AS (SELECT substring(t1.starttime,1,10) AS day,case when t1.terminatecause='ANSWER' then 1 else 0 end as success,case when t1.terminatecause ='ANSWER'  OR t1.terminatecause='ANSWERED' then 0 else 1 end as fail,0 as maxfail FROM $FG_TABLE_NAME WHERE ".$FG_ASR_CIC_CLAUSE." ORDER BY day)";
$max_fail=0;
$max=0;
$total_fail_succ=0;
$total_max_succ=0;
$update=array();
if (!$nodisplay){
	$num = 0;
	$res = $DBHandle -> Execute($QUERY);
	$QUERY="SELECT * FROM ASR_CIC_TEMP order by day";
	$res = $DBHandle -> Execute($QUERY);
	if ($res)
		$num = $res -> RecordCount();
	$pos=0;
	for($i=0;$i<$num;$i++)
	{	
		$asr_cic_list [] =$res -> fetchRow();
		if ($i>0)
		{	
			if ($asr_cic_list[$i][0] == $asr_cic_list[$i-1][0] && $i<$num-1 && $asr_cic_list[$i][2]==1) {
				$max++;
			}else {
				if (($i==$num-1) && ($asr_cic_list[$i][2]==1)) $max++;
				if ($max > $max_fail) {
					$max_fail=$max;
					$asr_cic_list1[$pos][3]=$max_fail;
					$max=0;
				}
				if($asr_cic_list[$i][0] != $asr_cic_list[$i-1][0]){
					$pos++;
					$success=0;
					$fail=0;
					$max_fail=0;
				}
			}
			
		}elseif($asr_cic_list[$i][2]==1){
			$max++;
		}
		$success+=$asr_cic_list[$i][1];
		$fail+=$asr_cic_list[$i][2];
		$asr_cic_list1[$pos][0] = $asr_cic_list[$i][0];
		$asr_cic_list1[$pos][1] = $success; 
		$asr_cic_list1[$pos][2] = $fail;
		
		if ($asr_cic_list[$i][2]==1){
			$total_fail_succ++;	
		}elseif($total_fail_succ > $total_max_succ){
			$total_max_succ=$total_fail_succ;
			$total_fail_succ=0;
		}
	}
}

/************************/
//$QUERY = "SELECT substring(calldate,1,10) AS day, sum(duration) AS calltime, count(*) as nbcall FROM cdr WHERE ".$FG_TABLE_CLAUSE." GROUP BY substring(calldate,1,10)"; //extract(DAY from calldate) 
$QUERY = "SELECT substring(t1.starttime,1,10) AS day, sum(t1.sessiontime) AS calltime, sum(t1.sessionbill) AS cost, count(*) as nbcall, sum(t1.buycost) AS buy FROM $FG_TABLE_NAME WHERE ".$FG_ASR_CIC_CLAUSE." GROUP BY substring(t1.starttime,1,10) ORDER BY day"; //extract(DAY from calldate) 

if (!$nodisplay){
	$res = $DBHandle -> Execute($QUERY);
	if ($res){
		$num = $res -> RecordCount();
		for($i=0;$i<$num;$i++)
		{				
			$list_total_day [] =$res -> fetchRow();
		}
	}
	
if ($FG_DEBUG >= 2) echo "<br>Clause : $FG_TABLE_CLAUSE";
	$nb_record = $instance_table -> Table_count ($DBHandle, $FG_TABLE_CLAUSE);
if ($FG_DEBUG >= 4) var_dump ($list);
	
}//end IF nodisplay



if ($nb_record<=$FG_LIMITE_DISPLAY){ 
	$nb_record_max=1;
}else{ 
	if ($nb_record % $FG_LIMITE_DISPLAY == 0){
		$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY));
	}else{
		$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY)+1);
	}	
}


if ($FG_DEBUG >= 3) echo "<br>Nb_record : $nb_record";
if ($FG_DEBUG >= 3) echo "<br>Nb_record_max : $nb_record_max";


$instance_table_customer = new Table("cc_card", "id,  username, lastname");

$FG_TABLE_CLAUSE = "";

$list_customer = $instance_table_customer -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "id", "ASC", null, null, null, null);

$nb_customer = count($list_customer);

$instance_table_agent = new Table("cc_agent", "id, name");
$FG_TABLE_CLAUSE = "";
$list_agent = $instance_table_agent -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "name", "ASC", null, null, null, null);
$nb_agent = count($list_agent);


$smarty->display('main.tpl');

	include("PP_header.php");
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

//-->
</script>


<br/><br/>
<!-- ** ** ** ** ** Part for the research ** ** ** ** ** -->
<center>
<FORM METHOD=POST name="myForm" ACTION="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>">
<INPUT TYPE="hidden" NAME="posted" value=1>
<INPUT TYPE="hidden" NAME="current_page" value=0>	
	<TABLE class="bar-status" width="85%" border="0" cellspacing="1" cellpadding="2" align="center">
		<?php  if ($_SESSION["pr_groupID"]==2 && is_numeric($_SESSION["pr_IDCust"])){ ?>
		<?php  }else{ ?>
		<tr>
			<td align="left" valign="top" class="bgcolor_004">					
				<font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("CUSTOMERS");?></font>
			</td>				
			<td class="bgcolor_005" align="left">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
						<?php echo gettext("Enter the cardnumber");?>: <INPUT TYPE="text" NAME="entercustomer" value="<?php echo $entercustomer?>">
						<a href="#" onclick="window.open('A2B_entity_card.php?popup_select=2&popup_formname=myForm&popup_fieldname=entercustomer' , 'CardNumberSelection','scrollbars=1,width=550,height=330,top=20,left=100,scrollbars=1');"><img src="../Images/icon_arrow_orange.png"></a>
					</td>
					<td align="right" class="fontstyle_searchoptions">
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td align="left" class="fontstyle_searchoptions"><?php echo gettext("CallPlan");?> :</td>
							<td align="left" class="fontstyle_searchoptions"><INPUT TYPE="text" NAME="entertariffgroup" value="<?php echo $entertariffgroup?>" size="4" class="form_input_text">&nbsp;<a href="#" onclick="window.open('A2B_entity_tariffgroup.php?popup_select=2&popup_formname=myForm&popup_fieldname=entertariffgroup' , 'CallPlanSelection','scrollbars=1,width=550,height=330,top=20,left=100');"><img src="<?php echo Images_Path;?>/icon_arrow_orange.gif"></a></td>
							<td align="left" class="fontstyle_searchoptions"><?php echo gettext("Provider");?> :
							<td align="left" class="fontstyle_searchoptions"><INPUT TYPE="text" NAME="enterprovider" value="<?php echo $enterprovider?>" size="4" class="form_input_text">&nbsp;<a href="#" onclick="window.open('A2B_entity_provider.php?popup_select=2&popup_formname=myForm&popup_fieldname=enterprovider' , 'ProviderSelection','scrollbars=1,width=550,height=330,top=20,left=100');"><img src="<?php echo Images_Path;?>/icon_arrow_orange.gif"></a></td>
						</tr>
						<tr>
							<td align="left" class="fontstyle_searchoptions"><?php echo gettext("Trunk");?> :</td>
							<td align="left" class="fontstyle_searchoptions"><INPUT TYPE="text" NAME="entertrunk" value="<?php echo $entertrunk?>" size="4" class="form_input_text">&nbsp;<a href="#" onclick="window.open('A2B_entity_trunk.php?popup_select=2&popup_formname=myForm&popup_fieldname=entertrunk' , 'TrunkSelection','scrollbars=1,width=550,height=330,top=20,left=100');"><img src="<?php echo Images_Path;?>/icon_arrow_orange.gif"></a></td>
							<td align="left" class="fontstyle_searchoptions"><?php echo gettext("Rate");?> :</td>
							<td align="left" class="fontstyle_searchoptions"><INPUT TYPE="text" NAME="enterratecard" value="<?php echo $enterratecard?>" size="4" class="form_input_text">&nbsp;<a href="#" onclick="window.open('A2B_entity_def_ratecard.php?popup_select=2&popup_formname=myForm&popup_fieldname=enterratecard' , 'RatecardSelection','scrollbars=1,width=550,height=330,top=20,left=100');"><img src="<?php echo Images_Path;?>/icon_arrow_orange.gif"></a></td>
						</tr>
					</table>
				</tr>
			</table>
			</td>
		</tr>			
		<?php  }?>
		<tr>
			<td class="bgcolor_002" align="left">

				<input type="radio" name="Period" value="Month" <?php  if (($Period=="Month") || !isset($Period)){ ?>checked="checked" <?php  } ?>> 
				<font class="fontstyle_003"><?php echo gettext("SELECT MONTH");?></font>
			</td>
			<td class="bgcolor_003" align="left">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr><td class="fontstyle_searchoptions">
				<input type="checkbox" name="frommonth" value="true" <?php  if ($frommonth){ ?>checked<?php }?>>
				<?php echo gettext("From");?> : <select name="fromstatsmonth" class="form_input_select">
				<?php
						// *-*
					$monthname = array( gettext("January"), gettext("February"),gettext("March"), gettext("April"), gettext("May"), gettext("June"), gettext("July"), gettext("August"), gettext("September"), gettext("October"), gettext("November"), gettext("December"));
					$year_actual = date("Y");  	
					for ($i=$year_actual;$i >= $year_actual-1;$i--)
					{		   
					   if ($year_actual==$i){
						$monthnumber = date("n")-1; // Month number without lead 0.
					   }else{
						$monthnumber=11;
					   }		   
					   for ($j=$monthnumber;$j>=0;$j--){	
						$month_formated = sprintf("%02d",$j+1);
						if ($fromstatsmonth=="$i-$month_formated")	$selected="selected";
						else $selected="";
						echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";				
					   }
					}
				?>		
				</select>
				</td><td  class="fontstyle_searchoptions">&nbsp;&nbsp;
				<input type="checkbox" name="tomonth" value="true" <?php  if ($tomonth){ ?>checked<?php }?>> 
					<?php echo gettext("To"); ?> : <select name="tostatsmonth" class="form_input_select">
				<?php 	$year_actual = date("Y");  	
					for ($i=$year_actual;$i >= $year_actual-1;$i--)
					{		   
					   if ($year_actual==$i){
						$monthnumber = date("n")-1; // Month number without lead 0.
					   }else{
						$monthnumber=11;
					   }		   
					   for ($j=$monthnumber;$j>=0;$j--){	
						$month_formated = sprintf("%02d",$j+1);
						if ($tostatsmonth=="$i-$month_formated") $selected="selected";
						else $selected="";
						echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";				
					   }
					}
				?>
				</select>
				</td></tr></table>
			</td>
		</tr>
		
		<tr>
			<td align="left" class="bgcolor_004">
				<input type="radio" name="Period" value="Day" <?php  if ($Period=="Day"){ ?>checked="checked" <?php  } ?>> 
				<font class="fontstyle_003"><?php echo gettext("SELECT DAY");?></font>
			</td>
			<td align="left" class="bgcolor_005">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr><td class="fontstyle_searchoptions">
				<input type="checkbox" name="fromday" value="true" <?php  if ($fromday){ ?>checked<?php }?>> <?php echo gettext("From");?> :
				<select name="fromstatsday_sday" class="form_input_select">
					<?php  
					for ($i=1;$i<=31;$i++){
						if ($fromstatsday_sday==sprintf("%02d",$i)) $selected="selected";
						else	$selected="";
						echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
					}
					?>	
				</select>
				<select name="fromstatsmonth_sday" class="form_input_select">
				<?php 	$year_actual = date("Y");  	
					for ($i=$year_actual;$i >= $year_actual-1;$i--)
					{		   
						if ($year_actual==$i){
							$monthnumber = date("n")-1; // Month number without lead 0.
						}else{
							$monthnumber=11;
						}		   
						for ($j=$monthnumber;$j>=0;$j--){	
							$month_formated = sprintf("%02d",$j+1);
							if ($fromstatsmonth_sday=="$i-$month_formated") $selected="selected";
							else $selected="";
							echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";				
						}
					}
				?>
				</select>
				</td><td class="fontstyle_searchoptions">&nbsp;&nbsp;
				<input type="checkbox" name="today" value="true" <?php  if ($today){ ?>checked<?php }?>> 
				<?php echo gettext("To");?>  :
				<select name="tostatsday_sday" class="form_input_select">
				<?php  
					for ($i=1;$i<=31;$i++){
						if ($tostatsday_sday==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
						echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
					}
				?>						
				</select>
				<select name="tostatsmonth_sday" class="form_input_select">
				<?php 	$year_actual = date("Y");  	
					for ($i=$year_actual;$i >= $year_actual-1;$i--)
					{		   
						if ($year_actual==$i){
							$monthnumber = date("n")-1; // Month number without lead 0.
						}else{
							$monthnumber=11;
						}		   
						for ($j=$monthnumber;$j>=0;$j--){	
							$month_formated = sprintf("%02d",$j+1);
							if ($tostatsmonth_sday=="$i-$month_formated") $selected="selected";
							else	$selected="";
							echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";				
						}
					}
				?>
				</select>
				</td></tr></table>
			</td>
		</tr>
		<tr>
			<td class="bgcolor_002" align="left">			
				<font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("CALLEDNUMBER");?></font>
			</td>				
			<td class="bgcolor_003" align="left">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="dst" value="<?php echo $dst?>" class="form_input_text"></td>
				<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="dsttype" value="1" <?php if((!isset($dsttype))||($dsttype==1)){?>checked<?php }?>><?php echo gettext("Exact");?></td>
				<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="dsttype" value="2" <?php if($dsttype==2){?>checked<?php }?>><?php echo gettext("Begins with");?></td>
				<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="dsttype" value="3" <?php if($dsttype==3){?>checked<?php }?>><?php echo gettext("Contains");?></td>
				<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="dsttype" value="4" <?php if($dsttype==4){?>checked<?php }?>><?php echo gettext("Ends with");?></td>
				</tr>
			</table></td>
		</tr>			
		<tr>
			<td align="left" class="bgcolor_004">					
				<font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("SOURCE");?></font>
			</td>				
			<td class="bgcolor_005" align="left">
			<table width="100%" border="0" cellspacing="0" cellpadding="0" >
			<tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="src" value="<?php echo "$src";?>" class="form_input_text"></td>
			<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="srctype" value="1" <?php if((!isset($srctype))||($srctype==1)){?>checked<?php }?>><?php echo gettext("Exact");?></td>
			<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="srctype" value="2" <?php if($srctype==2){?>checked<?php }?>><?php echo gettext("Begins with");?></td>
			<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="srctype" value="3" <?php if($srctype==3){?>checked<?php }?>><?php echo gettext("Contains");?></td>
			<td class="fontstyle_searchoptions" align="center" ><input type="radio" NAME="srctype" value="4" <?php if($srctype==4){?>checked<?php }?>><?php echo gettext("Ends with");?></td>
			</tr></table></td>
		</tr>
		
		
			<tr>
				<td align="left" valign="top" class="bgcolor_004">					
					<font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("AGENT");?></font>
				</td>				
				<td class="bgcolor_005" align="left">
				<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
					<td class="fontstyle_searchoptions">
						<?= _("Select an agent") ?>: 
						<select name="choose_agent" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
						<option value=''><?= _("All cards");?></option>
						<option value='all'><?= _("All agents");?></option>
						<option value='no'><?= _("No agent");?></option>
						<?php
						foreach ($list_agent as $recordset){
						?>
							<option class=input value='<?php echo $recordset[0]?>' ><?php echo $recordset[1]?></option>
						<?php 	 }
						?>
						</select>
					</td>
				</tr></table></td>
			</tr>
		<!-- Select Option : to show just the Answered Calls or all calls, Result type, currencies... -->
		<tr>
		  <td class="bgcolor_002" align="left" ><font class="fontstyle_003">&nbsp;&nbsp;<?php echo gettext("OPTIONS");?></font></td>
		  <td class="bgcolor_003" align="center"><div align="left">
		  
		  <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="20%"  class="fontstyle_searchoptions">
					<?php echo gettext("SHOW");?> :  						
			   </td>
			   <td width="80%"  class="fontstyle_searchoptions">
					<?php echo gettext("Answered Calls");?>  
					<input name="terminatecause" type="radio" value="ANSWER" <?php if((!isset($terminatecause))||($terminatecause=="ANSWER")){?>checked<?php }?> /> 
					<?php echo gettext("All Calls");?>  
					<input name="terminatecause" type="radio" value="ALL" <?php if($terminatecause=="ALL"){?>checked<?php }?>/>
				</td>
			</tr>
			<tr class="bgcolor_005">
				<td  class="fontstyle_searchoptions">
					<?php echo gettext("RESULT");?> : 
			   </td>
			   <td  class="fontstyle_searchoptions">
					<?php echo gettext("mins");?><input type="radio" NAME="resulttype" value="min" <?php if((!isset($resulttype))||($resulttype=="min")){?>checked<?php }?>> - <?php echo gettext("secs")?> <input type="radio" NAME="resulttype" value="sec" <?php if($resulttype=="sec"){?>checked<?php }?>>
				</td>
			</tr>
			<tr>
				<td  class="fontstyle_searchoptions">
					<?php echo gettext("CURRENCY");?> :
				</td>
				<td  class="fontstyle_searchoptions">
					<select NAME="choose_currency" size="1" class="form_input_select">
						<?php
							$currencies_list = get_currencies();
							foreach($currencies_list as $key => $cur_value) {
						?>
							<option value='<?php echo $key ?>' <?php if (($choose_currency==$key) || (!isset($choose_currency) && $key==strtoupper(BASE_CURRENCY))){?>selected<?php } ?>><?php echo $cur_value[1].' ('.$cur_value[2].')' ?>
							</option>
						<?php 	} ?>
					</select>
				</td>
			</tr>
			</table>
		</td>
		</tr>
		<!-- Select Option : to show just the Answered Calls or all calls, Result type, currencies... -->
		
		<tr>
			<td class="bgcolor_004" align="left" > </td>
				<td class="bar-search" align="center" bgcolor="#acbdee">
					<input type="image"  name="image16" align="top" border="0" src="../Images/button-search.png" />
			</td>
		</tr>
	</table>
</FORM>
</center>


<!-- ** ** ** ** ** Part to display the CDR ** ** ** ** ** -->

<center><?php echo gettext("Number of call");?> : <?php  if (is_array($list) && count($list)>0){ echo $nb_record; }else{echo "0";}?></center>

      <table width="<?php echo $FG_HTML_TABLE_WIDTH?>" border="0" align="center" cellpadding="0" cellspacing="0">
		<TR bgcolor="#ffffff"> 
          <TD bgColor=#7f99cc height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
            <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
              <TBODY>
                <TR> 
                  <TD><SPAN style="COLOR: #ffffff; FONT-SIZE: 11px"><B><?php echo $FG_HTML_TABLE_TITLE?></B></SPAN></TD>
                  <TD align=right> <IMG alt="Back to Top" border=0 height=12 src="../Images/btn_top_12x12.png" width=12> 
                  </TD>
                </TR>
              </TBODY>
            </TABLE></TD>
        </TR>
        <TR> 
          <TD> <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
<TBODY>
                <TR bgColor=#F0F0F0> 
				  <TD width="<?php echo $FG_ACTION_SIZE_COLUMN?>" align=center class="tableBodyRight" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"></TD>					
				  
                  <?php 
				  	if (is_array($list) && count($list)>0){
					
				  	for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
					?>				
				  
					
                  <TD width="<?php echo $FG_TABLE_COL[$i][2]?>" align=middle class="tableBody" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"> 
                    <center><strong> 
                    <?php  if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    <a href="<?php  echo $PHP_SELF."?customer=$customer&s=1&t=0&stitle=$stitle&atmenu=$atmenu&current_page=$current_page&order=".$FG_TABLE_COL[$i][1]."&sens="; if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} 
					echo "&entercustomer=$entercustomer&enterprovider=$enterprovider&entertrunk=$entertrunk&posted=$posted&Period=$Period&frommonth=$frommonth&fromstatsmonth=$fromstatsmonth&tomonth=$tomonth&tostatsmonth=$tostatsmonth&fromday=$fromday&fromstatsday_sday=$fromstatsday_sday&fromstatsmonth_sday=$fromstatsmonth_sday&today=$today&tostatsday_sday=$tostatsday_sday&tostatsmonth_sday=$tostatsmonth_sday&dsttype=$dsttype&srctype=$srctype&clidtype=$clidtype&channel=$channel&resulttype=$resulttype&dst=$dst&src=$src&clid=$clid&terminatecause=$terminatecause";?>"> 
                    <span class="liens"><?php  } ?>
                    <?php echo $FG_TABLE_COL[$i][0]?> 
                    <?php if ($order==$FG_TABLE_COL[$i][1] && $sens=="ASC"){?>
                    &nbsp;<img src="../Images/icon_up_12x12.png" width="12" height="12" border="0"> 
                    <?php }elseif ($order==$FG_TABLE_COL[$i][1] && $sens=="DESC"){?>
                    &nbsp;<img src="../Images/icon_down_12x12.png" width="12" height="12" border="0"> 
                    <?php }?>
                    <?php  if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    </span></a> 
                    <?php }?>
                    </strong></center></TD>
				   <?php } ?>		
				   <?php if ($FG_DELETION || $FG_EDITION){ ?>
				   
                  
				   <?php } ?>		
                </TR>
                <TR> 
                  <TD bgColor=#e1e1e1 colSpan=<?php echo $FG_TOTAL_TABLE_COL?> height=1><IMG 
                              height=1 
                              src="../Images/clear.png" 
                              width=1></TD>
                </TR>
				<?php
					  
				  	 $ligne_number=0;					 
					 //print_r($list);
				  	 foreach ($list as $recordset){ 
						 $ligne_number++;
				?>
				
               		 <TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>'"> 
						<TD vAlign=top align="<?php echo $FG_TABLE_COL[$i][3]?>" class=tableBody><?php  echo $ligne_number+$current_page*$FG_LIMITE_DISPLAY.".&nbsp;"; ?></TD>
							 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ ?>
						
						  
						<?php 	
							if ($FG_TABLE_COL[$i][6]=="lie"){

									$instance_sub_table = new Table($FG_TABLE_COL[$i][7], $FG_TABLE_COL[$i][8]);
									$sub_clause = str_replace("%id", $recordset[$i], $FG_TABLE_COL[$i][9]);																																	
									$select_list = $instance_sub_table -> Get_list ($DBHandle, $sub_clause, null, null, null, null, null, null);
									
									
									$field_list_sun = split(',',$FG_TABLE_COL[$i][8]);
									$record_display = $FG_TABLE_COL[$i][10];
									
									for ($l=1;$l<=count($field_list_sun);$l++){										
										$record_display = str_replace("%$l", $select_list[0][$l-1], $record_display);	
									}
								
							}elseif ($FG_TABLE_COL[$i][6]=="list"){
									$select_list = $FG_TABLE_COL[$i][7];
									$record_display = $select_list[$recordset[$i]][0];
							
							}else{
									$record_display = $recordset[$i];
							}
							
							
							if ( is_numeric($FG_TABLE_COL[$i][5]) && (strlen($record_display) > $FG_TABLE_COL[$i][5])  ){
								$record_display = substr($record_display, 0, $FG_TABLE_COL[$i][5]-3)."";  
															
							}
							
							
				 		 ?>
                 		 <TD vAlign=top align="<?php echo $FG_TABLE_COL[$i][3]?>" class=tableBody><?php 
						 if (isset ($FG_TABLE_COL[$i][11]) && strlen($FG_TABLE_COL[$i][11])>1){
						 		call_user_func($FG_TABLE_COL[$i][11], $record_display);
						 }else{
						 		echo stripslashes($record_display);
						 }						 
						 ?></TD>
				 		 <?php  } ?>
                  
					</TR>
				<?php
					 }//foreach ($list as $recordset)
					 if ($ligne_number < $FG_LIMITE_DISPLAY)  $ligne_number_end=$ligne_number +2;
					 while ($ligne_number < $ligne_number_end){
					 	$ligne_number++;
				?>
					<TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>"> 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
				 		 ?>
                 		 <TD vAlign=top class=tableBody>&nbsp;</TD>
				 		 <?php  } ?>
                 		 <TD align="center" vAlign=top class=tableBodyRight>&nbsp;</TD>				
					</TR>
									
				<?php					 
					 } //END_WHILE
					 
				  }else{
				  		echo gettext("No data found !!!");
				  }//end_if
				 ?>
                <TR> 
                  <TD class=tableDivider colSpan=<?php echo $FG_TOTAL_TABLE_COL?>><IMG height=1 
                              src="../Images/clear.png" 
                              width=1></TD>
                </TR>
                <TR> 
                  <TD class=tableDivider colSpan=<?php echo $FG_TOTAL_TABLE_COL?>><IMG height=1 
                              src="../Images/clear.png" 
                              width=1></TD>
                </TR>
              </TBODY>
            </TABLE></td>
        </tr>
        <TR bgcolor="#ffffff"> 
          <TD bgColor=#ADBEDE height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
			<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
              <TBODY>
                <TR> 
                  <TD align="right"><SPAN style="COLOR: #ffffff; FONT-SIZE: 11px"><B> 
                    <?php if ($current_page>0){?>
                    <img src="../Images/fleche-g.png" width="5" height="10"> <a href="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php  echo ($current_page-1)?><?php  if (!is_null($letter) && ($letter!="")){ echo "&letter=$letter";} 
					echo "&customer=$customer&posted=$posted&Period=$Period&frommonth=$frommonth&fromstatsmonth=$fromstatsmonth&tomonth=$tomonth&tostatsmonth=$tostatsmonth&fromday=$fromday&fromstatsday_sday=$fromstatsday_sday&fromstatsmonth_sday=$fromstatsmonth_sday&today=$today&tostatsday_sday=$tostatsday_sday&tostatsmonth_sday=$tostatsmonth_sday&dsttype=$dsttype&srctype=$srctype&clidtype=$clidtype&channel=$channel&resulttype=$resulttype&dst=$dst&src=$src&clid=$clid&terminatecause=$terminatecause&entercustomer=$entercustomer&enterprovider=$enterprovider&entertrunk=$entertrunk";?>">
                    <?php echo gettext("Previous");?> </a> -
                    <?php }?>
                    <?php echo ($current_page+1);?> / <?php  echo $nb_record_max;?>
                    <?php if ($current_page<$nb_record_max-1){?>
                    - <a href="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php  echo ($current_page+1)?><?php  if (!is_null($letter) && ($letter!="")){ echo "&letter=$letter";}
					echo "&customer=$customer&posted=$posted&Period=$Period&frommonth=$frommonth&fromstatsmonth=$fromstatsmonth&tomonth=$tomonth&tostatsmonth=$tostatsmonth&fromday=$fromday&fromstatsday_sday=$fromstatsday_sday&fromstatsmonth_sday=$fromstatsmonth_sday&today=$today&tostatsday_sday=$tostatsday_sday&tostatsmonth_sday=$tostatsmonth_sday&dsttype=$dsttype&srctype=$srctype&clidtype=$clidtype&channel=$channel&resulttype=$resulttype&dst=$dst&src=$src&clid=$clid&terminatecause=$terminatecause&entercustomer=$entercustomer&enterprovider=$enterprovider&entertrunk=$entertrunk";?>">
                    <?php echo gettext("Next");?></a> <img src="../Images/fleche-d.png" width="5" height="10">
                    </B></SPAN> 
                    <?php }?>
                  </TD>
              </TBODY>
            </TABLE></TD>
        </TR>
      </table>

<!-- ** ** ** ** ** Part to display the GRAPHIC ** ** ** ** ** -->
<br>

<?php 

if (is_array($list_total_day) && count($list_total_day)>0){


$mmax=0;
$totalcall==0;
$totalminutes=0;
foreach ($list_total_day as $data){	
	if ($mmax < $data[1]) $mmax=$data[1];
	$totalcall+=$data[3];
	$totalminutes+=$data[1];
	$totalcost+=$data[2];
	$totalbuycost+=$data[4];
}
?>



<!-- TITLE GLOBAL -->
<center>
 <table border="0" cellspacing="0" cellpadding="0" width="80%"><tbody><tr><td align="left" height="30">
		<table cellspacing="0" cellpadding="1" bgcolor="#000000" width="50%"><tbody><tr><td>
			<table cellspacing="0" cellpadding="0" width="100%"><tbody>
				<tr><td  class="bgcolor_019" align="left"><font class="fontstyle_003"><?php echo gettext("SUMMARY");?></font></td></tr>
			</tbody></table>
		</td></tr></tbody></table>
 </td></tr></tbody></table>
		  
<!-- FIN TITLE GLOBAL MINUTES //-->
				
<table border="0" cellspacing="0" cellpadding="0"  width="95%">
<tbody><tr><td bgcolor="#000000">			
	<table border="0" cellspacing="1" cellpadding="2" width="100%"><tbody>
	<tr>	
		<td align="center" bgcolor="#600101"></td>
    	<td  class="bgcolor_020" align="center" colspan="13"><font class="fontstyle_003"><?php echo gettext("CALLING CARD MINUTES");?></font></td>
    </tr>
	<tr bgcolor="#600101">
		<td align="center" class="bgcolor_020"><font class="fontstyle_003"><?php echo gettext("DATE");?></font></td>
        <td align="center"><font class="fontstyle_003"><acronym title="<?php echo gettext("DURATION");?>"><?php echo gettext("DUR");?></acronym></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("GRAPHIC");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("CALLS");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><acronym title="<?php echo gettext("AVERAGE LENGTH OF CALL");?>"><?php echo gettext("ALOC");?></acronym></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><acronym title="<?php echo gettext("ANSWER SEIZE RATIO");?>"><?php echo gettext("ASR");?></acronym></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><acronym title="<?php echo gettext("NUMBER OF FAIL CALLS");?>"><?php echo gettext("FAIL");?></acronym></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><acronym title="<?php echo gettext("MAX OF NUMBER FAIL CALLS SUCCESSIVELY");?>"><?php echo gettext("MFCS");?></acronym></b></font></td>
		<td align="center"><font class="fontstyle_003"><acronym title="<?php echo gettext("RATE OF FAIL");?>"><?php echo gettext("ROF");?></acronym></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("SELL");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("BUY");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("PROFIT");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("MARGIN");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("MARKUP");?></b></font></td>
                			
		<!-- LOOP -->
	<?php  		
		$i=0;
		$j=0;
		foreach ($list_total_day as $data){
		$i=($i+1)%2;		
		$tmc = $data[1]/$data[3];
		
		if ((!isset($resulttype)) || ($resulttype=="min")){  
			$tmc = sprintf("%02d",intval($tmc/60)).":".sprintf("%02d",intval($tmc%60));		
		}else{
		
			$tmc =intval($tmc);
		}
		
		if ((!isset($resulttype)) || ($resulttype=="min")){  
				$minutes = sprintf("%02d",intval($data[1]/60)).":".sprintf("%02d",intval($data[1]%60));
		}else{
				$minutes = $data[1];
		}
		if ($mmax>0) 	$widthbar= intval(($data[1]/$mmax)*150); 
	?>
		</tr><tr>
		<td align="right" class="sidenav" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><?php echo $data[0]?></font></td>
		<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php echo $minutes?> </font></td>
        <td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="left" nowrap="nowrap" width="<?php echo $widthbar+40?>">
        <table cellspacing="0" cellpadding="0"><tbody><tr>
        <td bgcolor="#e22424"><img src="../Images/spacer.png" width="<?php echo $widthbar?>" height="6"></td>
        </tr></tbody></table></td>
        <td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php echo $data[3]?></font></td>
        <td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php echo $tmc?> </font></td>
        <td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php display_2dec ($asr_cic_list1[$j][1]/($data[3]))?> </font></td>
        <td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php echo $asr_cic_list1[$j][2]?> </font></td>
	<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php echo $asr_cic_list1[$j][3]?> </font></td>
	<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php display_2dec_percentage(($asr_cic_list1[$j][2]/($data[3]))*100)?> </font></td>
		<!-- SELL -->
		<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php  
		display_2bill($data[2]) 
		?>
		</font></td>
		<!-- BUY -->
		<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php  
		display_2bill($data[4]) 
		?>
		</font></td>
		<!-- PROFIT -->
		<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php  
		display_2bill($data[2]-$data[4]) 
		?>
		</font></td>
		<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php  
		if ($data[2]!=0){ display_2dec_percentage((($data[2]-$data[4])/$data[2])*100); }else{ echo "NULL";} 
		?>
		</font></td>
		<td bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$i]?>" align="right" nowrap="nowrap"><font face="verdana" color="#000000" size="1"><?php  
		if ($data[4]!=0){ display_2dec_percentage((($data[2]-$data[4])/$data[4])*100); }else{ echo "NULL";} 
		?>
		</font></td>
     <?php 	 $j++;}	 	 	
	 	
		if ((!isset($resulttype)) || ($resulttype=="min")){  
			$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
			$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
		}else{
			$total_tmc = intval($totalminutes/$totalcall);			
		}
	 
	 ?>                   	
	</tr>
	<!-- FIN DETAIL -->		
	
	<!-- FIN BOUCLE -->

	<!-- TOTAL -->
	<tr bgcolor="#600101">
		<td align="right" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("TOTAL");?></b></font></td>
		<td align="center" nowrap="nowrap" colspan="2"><font face="verdana" size="1" color="#ffffff"><b><?php echo $totalminutes?> </b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo $totalcall?></b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo $total_tmc?></b></font></td>   
        	<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php display_2dec($totalsuccess/$totalcall)?> </b></font></td>
        	<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo $totalfail?></b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php echo $total_max_succ?></b></font></td>
		<td align="center" nowrap="nowrap"><font face="verdana" size="1" color="#ffffff"><b><?php display_2dec_percentage(($totalfail/$totalcall)*100)?></b></font></td>
		<td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php display_2bill($totalcost) ?></font></td>
		<td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php display_2bill($totalbuycost) ?></font></td>
		<td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php display_2bill($totalcost - $totalbuycost) ?></font></td>
		<td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php if ($totalcost!=0){ display_2dec_percentage((($totalcost - $totalbuycost)/$totalcost)*100); }else{ echo "NULL";} ?></font></td>
		<td align="center" nowrap="nowrap"><font class="fontstyle_003"><?php if ($totalbuycost!=0){ display_2dec_percentage((($totalcost - $totalbuycost)/$totalbuycost)*100);  }else{ echo "NULL";} ?></font></td>
	</tr>
	<!-- END TOTAL -->

	  </tbody></table>
	  <!-- END ARRAY GLOBAL //-->

</td></tr></tbody></table>

<br>
	<!-- SECTION EXPORT //-->
		 &nbsp; &nbsp; <a href="export_csv.php?var_export=<?php echo $FG_EXPORT_SESSION_VAR ?>&var_export_type=type_csv" target="_blank" ><img src="<?php echo Images_Path;?>/excel.gif" border="0" height="30"/><?php echo gettext("Export CSV");?></a>
		
		 - &nbsp; &nbsp; <a href="export_csv.php?var_export=<?php echo $FG_EXPORT_SESSION_VAR ?>&var_export_type=type_xml" target="_blank" ><img src="<?php echo Images_Path;?>/icons_xml.gif" border="0" height="32"/><?php echo gettext("Export XML");?></a>
		
		
		
<?php  }else{ ?>
	<center><h3><?php echo gettext("No calls in your selection");?>.</h3></center>
<?php  } ?>
</center>

<?php
	include("PP_footer.php");
?>