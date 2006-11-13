<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");


if (! has_rights (ACX_CALL_REPORT)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

getpost_ifset(array('inputtopvar','topsearch', 'posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens', 'choose_currency', 'terminatecause', 'nodisplay'));

//var $field=array();

switch ($topsearch){
	case "topuser":
		$field=array('CARD ID','username');
	break;
	case "topdestination":
		$field=array('DESTINATION','destination');
	break;	
	default:
		$field=array('CARD ID','username');
}


if (!isset ($current_page) || ($current_page == "")){	
	$current_page=0; 
}

if (!isset ($FG_TABLE_CLAUSE) || strlen($FG_TABLE_CLAUSE)==0){
		
		$cc_yearmonth = sprintf("%04d-%02d-%02d",date("Y"),date("n"),date("d")); 	
		$FG_TABLE_CLAUSE=" $UNIX_TIMESTAMP(starttime) <= $UNIX_TIMESTAMP('$cc_yearmonth')";
}

// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_call";

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_HEAD_COLOR = "#D1D9E7";


$FG_TABLE_EXTERN_COLOR = "#7F99CC"; //#CC0033 (Rouge)
$FG_TABLE_INTERN_COLOR = "#EDF3FF"; //#FFEAFF (Rose)


// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";

//$link = DbConnect();
$DBHandle  = DbConnect();

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


if ((!isset($field)) && (count($field)<=1)){
	$field[0]="CARD ID";
	$field[1]="username";	}



$FG_TABLE_COL[]=array (gettext($field[0]), $field[1], "20%", "center","SORT");
$FG_TABLE_COL[]=array (gettext("Duration"), "calltime", "6%", "center", "SORT", "30", "", "", "", "", "", "display_minute");
$FG_TABLE_COL[]=array (gettext("Buy"), "buy", "20%", "center","sort","","","","","","","display_2bill");
$FG_TABLE_COL[]=array (gettext("Sell"), "cost", "20%", "center","sort","","","","","","","display_2bill");
$FG_TABLE_COL[]=array (gettext("Calldate"), "day", "20%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("terminatecause"), "terminatecause", "7%", "center", "SORT", "30");

if ((isset($inputtopvar)) && ($inputtopvar!="") && (isset($topsearch)) && ($topsearch!="")){
	$FG_TABLE_COL[]=array (gettext("NbrCall"), 'nbcall', "7%", "center", "SORT");
}

$FG_COL_QUERY=$field[1].', sum(sessiontime) AS calltime, sum(sessionbill) as cost, sum(buycost) as buy,substring(starttime,1,10) AS day,terminatecause, count(*) as nbcall';
$SQL_GROUP="GROUP BY ".$field[1].",day,terminatecause ";
$FG_TABLE_DEFAULT_ORDER = "username";
$FG_TABLE_DEFAULT_SENS = "DESC";
	
// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=20;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

// The variable $FG_EDITION define if you want process to the edition of the database record
$FG_EDITION=false;

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=gettext(" - Call Report - ");

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="96%";




	if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";
	


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}




$date_clause='';
// Period (Month-Day)
if (DB_TYPE == "postgres"){		
	 	$UNIX_TIMESTAMP = "";
}else{
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}
$lastdayofmonth = date("t", strtotime($tostatsmonth.'-01'));
if ($Period=="Month"){
	if ($frommonth && isset($fromstatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
	if ($tomonth && isset($tostatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(starttime) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')"; 
}else{
	if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday)) $date_clause.=" AND $UNIX_TIMESTAMP(starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday')";
	if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday)) $date_clause.=" AND $UNIX_TIMESTAMP(starttime) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday)/*+1*/)." 23:59:59')";
}


if (strpos($date_clause, 'AND') > 0){
	$FG_TABLE_CLAUSE = substr($date_clause,5); 
}


//To select just terminatecause=ANSWER
if (!isset($terminatecause)){
$terminatecause="ANSWER";
}
if ($terminatecause=="ANSWER") {
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE.="terminatecause='$terminatecause'";
	}


$QUERY_TOTAL = "SELECT sum(sessiontime) AS calltime, sum(sessionbill) as cost, sum(buycost) as buy, count(*) as nbcall FROM $FG_TABLE_NAME";
if ((isset($inputtopvar)) && ($inputtopvar!="") && (isset($topsearch)) && ($topsearch!="")){
	$FG_COL_QUERY1=$field[1].', sum(sessiontime) AS sessiontime, sum(sessionbill) as sessionbill, sum(buycost) as buycost,substring(starttime,1,10) AS starttime,terminatecause, count(*) as nbcall';
	$SQL_GROUP1=" GROUP BY $field[1],starttime,terminatecause";
	$QUERY = "CREATE TEMPORARY TABLE temp_result AS (SELECT $FG_COL_QUERY1 FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE  $SQL_GROUP1 ORDER BY nbcall DESC LIMIT $inputtopvar)";
	echo $QUERY;
	$res = $DBHandle -> query($QUERY);
	if ($res){
		$FG_TABLE_NAME="temp_result";
		$FG_COL_QUERY=$field[1].', sum(sessiontime) AS calltime, sum(sessionbill) as cost, sum(buycost) as buy,substring(starttime,1,10) AS day,terminatecause, nbcall';
		$SQL_GROUP=" GROUP BY ".$field[1].",day,terminatecause,nbcall ";
		$order = "nbcall";
		$QUERY_TOTAL = "SELECT sum(sessiontime) AS calltime, sum(sessionbill) as cost, sum(buycost) as buy, sum(nbcall) as nbcall FROM $FG_TABLE_NAME";
	}
}

$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);

if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY,$SQL_GROUP);
}

if (!$nodisplay){
		$res = $DBHandle -> query($QUERY_TOTAL);
		$list_total=$res -> fetchRow();
		if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
		$nb_record = $instance_table -> Table_count ($DBHandle);
		if ($FG_DEBUG >= 1) var_dump ($list);

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


if ($FG_DEBUG == 3) echo "<br>Nb_record : $nb_record";
if ($FG_DEBUG == 3) echo "<br>Nb_record_max : $nb_record_max";


/*******************   TOTAL COSTS  *****************************************

$instance_table_cost = new Table($FG_TABLE_NAME, "sum(.costs), sum(.buycosts)");		
if (!$nodisplay){	
	$total_cost = $instance_table_cost -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, null, null, null, null, null, null);
}
*/


/*************************************************************/



?>
<?php
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
		<table class="bar-status" width="85%" border="0" cellspacing="1" cellpadding="2" align="center">
			<tbody>
			<tr>
        		<td class="bar-search" align="left" bgcolor="#555577">

					<input type="radio" name="Period" value="Month" <?php  if (($Period=="Month") || !isset($Period)){ ?>checked="checked" <?php  } ?>> 
					<font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("Selection of the month");?></b></font>
				</td>
      			<td class="bar-search" align="left" bgcolor="#cddeff">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#cddeff"><tr><td>
	  				<input type="checkbox" name="frommonth" value="true" <?php  if ($frommonth){ ?>checked<?php }?>>
					<?php echo gettext("From");?> : <select name="fromstatsmonth">
					<?php
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
					</td><td>&nbsp;&nbsp;
					<input type="checkbox" name="tomonth" value="true" <?php  if ($tomonth){ ?>checked<?php }?>> 
					To : <select name="tostatsmonth">
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
        		<td align="left" bgcolor="#000033">
					<input type="radio" name="Period" value="Day" <?php  if ($Period=="Day"){ ?>checked="checked" <?php  } ?>> 
					<font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("Selection of the day");?></b></font>
				</td>
      			<td align="left" bgcolor="#acbdee">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#acbdee"><tr><td>
	  				<input type="checkbox" name="fromday" value="true" <?php  if ($fromday){ ?>checked<?php }?>> <?php echo gettext("From");?> :
					<select name="fromstatsday_sday">
						<?php  
						for ($i=1;$i<=31;$i++){
							if ($fromstatsday_sday==sprintf("%02d",$i)) $selected="selected";
							else	$selected="";
							echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
						}
						?>	
					</select>
				 	<select name="fromstatsmonth_sday">
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
					</td><td>&nbsp;&nbsp;
					<input type="checkbox" name="today" value="true" <?php  if ($today){ ?>checked<?php }?>> <?php echo gettext("To");?>  :
					<select name="tostatsday_sday">
					<?php  
						for ($i=1;$i<=31;$i++){
							if ($tostatsday_sday==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
							echo '<option value="'.sprintf("%02d",$i)."\"$selected>".sprintf("%02d",$i).'</option>';
						}
					?>						
					</select>
				 	<select name="tostatsmonth_sday">
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
			<TD class="bar-search" align="left" bgcolor="#000033">
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo gettext("Top");?></font>	
			</TD>
			<td class="bar-search" align="left" bgcolor="#cddeff">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>	<TD class="bar-search" align="center" bgcolor="#cddeff">
						<input type="text" name="inputtopvar" value="<?php echo $inputtopvar;?>">
					</td>
					<td class="bar-search" align="center" bgcolor="#cddeff">
						<input type="radio" name="topsearch" value="topuser"<?php if ($topsearch=="topuser"){ ?> checked="checked" <?php  } ?>><?php echo gettext("Users making the more calls");?>
					</td>
					<td class="bar-search" align="center" bgcolor="#cddeff">
						<input type="radio" name="topsearch" value="topdestination"<?php if ($topsearch=="topdestination"){ ?> checked="checked" <?php  } ?>><?php echo gettext("More calls destination");?>
					</td>
				</tr></table>
			</TD>
		</tr>
			<!-- Select Option : to show just the Answered Calls or all calls, Result type, currencies... -->

			
			<tr>
			  <td class="bar-search" align="left" bgcolor="#000033"><font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo gettext("Options");?></b></font></td>
			  <td class="bar-search" align="center" bgcolor="#acbdee"><div align="left">
			  
			  <table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			  	<td width="60%" >
				  <b>Show:</b>
				  Answered Calls
				  <input name="terminatecause" type="radio" value="ANSWER" <?php if((!isset($terminatecause))||($terminatecause=="ANSWER")){?>checked<?php }?> /> 
				  All Calls
	
				   <input name="terminatecause" type="radio" value="ALL" <?php if($terminatecause=="ALL"){?>checked<?php }?>/>
				   
			   </td>
			   <td>
			   
					<b><?php echo gettext("RESULT");?>: </b>
					<?php echo gettext("mins");?><input type="radio" NAME="resulttype" value="min" <?php if((!isset($resulttype))||($resulttype=="min")){?>checked<?php }?>> - <?php echo gettext("secs")?> <input type="radio" NAME="resulttype" value="sec" <?php if($resulttype=="sec"){?>checked<?php }?>>


			  	</td>
				</tr>
				<tr>
					<td>
						<b><?php echo gettext("CURRENCY");?> :</b>
						<select NAME="choose_currency" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
							<?php
								$currencies_list = get_currencies();
								foreach($currencies_list as $key => $cur_value) {
							?>
								<option value='<?php echo $key ?>' <?php if (($choose_currency==$key) || (!isset($choose_currency) && $key==strtoupper(BASE_CURRENCY))){?>selected<?php } ?>><?php echo $cur_value[1].' ('.$cur_value[2].')' ?>
								</option>
							<?php 	} ?>
						</select>
						 </div>		
					</td>
					<td>
					</td>
				</tr>
				</table>
			  </td>
			  </tr>

			<!-- Select Option : to show just the Answered Calls or all calls, Result type, currencies... -->
			
			
			<tr>
        		<td class="bar-search" align="left" bgcolor="#000033"> </td>

				<td class="bar-search" align="center" bgcolor="#acbdee">
					<input type="image"  name="image16" align="top" border="0" src="../Images/button-search.gif" />
					
	  			</td>
    		</tr>
		</tbody></table>
	</FORM>
</center>


<br><br>

<!-- ** ** ** ** ** Part to display the CDR ** ** ** ** ** -->

			<center><?php echo gettext("Number of call");?> : <?php  if (is_array($list) && count($list)>0){ echo $nb_record; }else{echo "0";}?></center>
      <table width="<?php echo $FG_HTML_TABLE_WIDTH?>" border="0" align="center" cellpadding="0" cellspacing="0">
<TR bgcolor="#ffffff"> 
          <TD bgColor=#7f99cc height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
            <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
              <TBODY>
                <TR> 
                  <TD><SPAN style="COLOR: #ffffff; FONT-SIZE: 11px"><B><?php echo $FG_HTML_TABLE_TITLE?></B></SPAN></TD>
                  <TD align=right> <IMG alt="Back to Top" border=0 height=12 src="../Images/btn_top_12x12.gif" width=12> 
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
                    <a href="<?php  echo $PHP_SELF."?s=1&t=0&stitle=$stitle&atmenu=$atmenu&current_page=$current_page&order=".$FG_TABLE_COL[$i][1]."&sens="; if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} 
					echo "&topsearch=$topsearch&inputtopvar=$inputtopvar&posted=$posted&Period=$Period&frommonth=$frommonth&fromstatsmonth=$fromstatsmonth&tomonth=$tomonth&tostatsmonth=$tostatsmonth&fromday=$fromday&fromstatsday_sday=$fromstatsday_sday&fromstatsmonth_sday=$fromstatsmonth_sday&today=$today&tostatsday_sday=$tostatsday_sday&tostatsmonth_sday=$tostatsmonth_sday&resulttype=$resulttype&terminatecause=$terminatecause";?>"> 
                    <span class="liens"><?php  } ?>
                    <?php echo $FG_TABLE_COL[$i][0]?> 
                    <?php if ($order==$FG_TABLE_COL[$i][1] && $sens=="ASC"){?>
                    &nbsp;<img src="../Images/icon_up_12x12.GIF" width="12" height="12" border="0"> 
                    <?php }elseif ($order==$FG_TABLE_COL[$i][1] && $sens=="DESC"){?>
                    &nbsp;<img src="../Images/icon_down_12x12.GIF" width="12" height="12" border="0"> 
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
                              src="../Images/clear.gif" 
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
                              src="../Images/clear.gif" 
                              width=1></TD>
                </TR>
                <TR> 
                  <TD class=tableDivider colSpan=<?php echo $FG_TOTAL_TABLE_COL?>><IMG height=1 
                              src="../Images/clear.gif" 
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
                    <img src="../Images/fleche-g.gif" width="5" height="10"> <a href="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php  echo ($current_page-1)?><?php  if (!is_null($letter) && ($letter!="")){ echo "&letter=$letter";} 
					echo "&topsearch=$topsearch&inputtopvar=$inputtopvar&posted=$posted&Period=$Period&frommonth=$frommonth&fromstatsmonth=$fromstatsmonth&tomonth=$tomonth&tostatsmonth=$tostatsmonth&fromday=$fromday&fromstatsday_sday=$fromstatsday_sday&fromstatsmonth_sday=$fromstatsmonth_sday&today=$today&tostatsday_sday=$tostatsday_sday&tostatsmonth_sday=$tostatsmonth_sday&resulttype=$resulttype&terminatecause=$terminatecause&";?>">
                    <?php echo gettext("Previous");?> </a> -
                    <?php }?>
                    <?php echo ($current_page+1);?> / <?php  echo $nb_record_max;?>
                    <?php if ($current_page<$nb_record_max-1){?>
                    - <a href="<?php echo $PHP_SELF?>?s=1&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php  echo ($current_page+1)?><?php  if (!is_null($letter) && ($letter!="")){ echo "&letter=$letter";}
					echo "&posted=$posted&Period=$Period&frommonth=$frommonth&fromstatsmonth=$fromstatsmonth&tomonth=$tomonth&tostatsmonth=$tostatsmonth&fromday=$fromday&fromstatsday_sday=$fromstatsday_sday&fromstatsmonth_sday=$fromstatsmonth_sday&today=$today&tostatsday_sday=$tostatsday_sday&tostatsmonth_sday=$tostatsmonth_sday&clidtype=$clidtype&resulttype=$resulttype&clid=$clid&terminatecause=$terminatecause&topsearch=$topsearch&inputtopvar=$inputtopvar";?>">
                    <?php echo gettext("Next");?></a> <img src="../Images/fleche-d.gif" width="5" height="10">
                    </B></SPAN> 
                    <?php }?>
                  </TD>
              </TBODY>
            </TABLE></TD>
        </TR>
      </table>
<br><br><br>

<?php if ((!isset($resulttype)) || ($resulttype=="min")){  
			$total_tmc=sprintf("%02d",intval(($list_total[0]/$list_total[3])/60)).":".sprintf("%02d",intval(($list_total[0]/$list_total[3])%60));				
			$totalminutes = sprintf("%02d",intval($list_total[0]/60)).":".sprintf("%02d",intval($list_total[0]%60));
		}else{
			$total_tmc = intval($list_total[0]/$list_total[3]);			
		}
if (is_array($list) && count($list)>0){
?>
<center>
 <table border="0" cellspacing="1" cellpadding="0" width="80%" bgcolor="Black"><tbody>
	<tr bgcolor="Gray">
		<td align="left" colspan="5"><font face="verdana" size="1" color="white"><b><?php echo gettext("TOTAL");?></b></font>
		</td>
	</tr>
	<tr bgcolor="#600101">
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("DURATION");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("CALLS");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("AVERAGE CONNECTION TIME");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("SELL");?></b></font></td>
		<td align="center"><font face="verdana" size="1" color="#ffffff"><b><?php echo gettext("BUY");?></b></font></td>
		<!-- LOOP -->
	</tr>
	<tr bgcolor="Gray">
		<td align="center" class="sidenav" nowrap="nowrap"><b><font face="verdana" size="1" color="#ffffff"><?php echo $totalminutes?></b></font></td>
		<td align="center" class="sidenav" nowrap="nowrap"><b><font face="verdana" color="#ffffff" size="1"><?php echo $list_total[3]?> </b></font></td>
	        <td align="center" class="sidenav" nowrap="nowrap"><b><font face="verdana" color="#ffffff" size="1"><?php echo $total_tmc?></b></font></td>
        	<td align="center" class="sidenav" nowrap="nowrap"><b><font face="verdana" color="#ffffff" size="1"><?php display_2bill($list_total[1])?> </b></font></td>
		<td align="center" class="sidenav" nowrap="nowrap"><b><font face="verdana" color="#ffffff" size="1"><?php display_2bill($list_total[2]) ?></b></font></td>
	</tr>
</tbody></table>

<?php  }else{ ?>
	<center><h3><?php echo gettext("No calls in your selection");?>.</h3></center>
<?php  } ?>
</center>

<?php
	include("PP_footer.php");
?>
