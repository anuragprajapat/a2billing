<?php
include ("lib/defines.php");
include ("lib/module.access.php");
include ("lib/smarty.php");



if (!$A2B->config["webcustomerui"]['invoice']) exit();

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

getpost_ifset(array('customer', 'posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'dsttype', 'sourcetype', 'clidtype', 'channel', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens', 'dst', 'src', 'clid', 'fromstatsmonth_sday', 'fromstatsmonth_shour', 'tostatsmonth_sday', 'tostatsmonth_shour', 'srctype', 'src', 'choose_currency','exporttype'));

$customer = $_SESSION["pr_login"];
$vat = $_SESSION["vat"];
//require (LANGUAGE_DIR.FILENAME_INVOICES);

if (($_GET[download]=="file") && $_GET[file] ) 
{
	
	$value_de=base64_decode($_GET[file]);
	$dl_full = MONITOR_PATH."/".$value_de;
	$dl_name=$value_de;

	if (!file_exists($dl_full))
	{ 
		echo gettext("ERROR: Cannot download file"). $dl_full.", ".gettext("it does not exist").'<br>';
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



if (!isset ($current_page) || ($current_page == "")){	
		$current_page=0; 
	}


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_call t1";

$DBHandle  = DbConnect();

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


/*******
Calldate Clid Src Dst Dcontext Channel Dstchannel Lastapp Lastdata Duration Billsec Disposition Amaflags Accountcode Uniqueid Serverid
*******/

$FG_TABLE_COL[]=array (gettext("Calldate"), "starttime", "18%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Source"), "src", "10%", "center", "SORT", "30");
$FG_TABLE_COL[]=array (gettext("Callednumber"), "calledstation", "18%", "right", "SORT", "30", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Destination"), "destination", "18%", "center", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Duration"), "sessiontime", "8%", "center", "SORT", "30", "", "", "", "", "", "display_minute");

if (!(isset($customer)  &&  ($customer>0)) && !(isset($entercustomer)  &&  ($entercustomer>0))){
	$FG_TABLE_COL[]=array (gettext("Cardused"), "username", "11%", "center", "SORT", "30");
}
$FG_TABLE_COL[]=array (gettext("Cost"), "sessionbill", "9%", "center", "SORT", "30", "", "", "", "", "", "display_2bill");

// ??? cardID
$FG_TABLE_DEFAULT_ORDER = "t1.starttime";
$FG_TABLE_DEFAULT_SENS = "DESC";
	
// This Variable store the argument for the SQL query

$FG_COL_QUERY='t1.starttime, t1.src, t1.calledstation, t1.destination, t1.sessiontime  ';
if (!(isset($customer)  &&  ($customer>0)) && !(isset($entercustomer)  &&  ($entercustomer>0))){
	$FG_COL_QUERY.=', t1.username';
}
$FG_COL_QUERY.=', t1.sessionbill';
if (LINK_AUDIO_FILE == 'YES') 
	$FG_COL_QUERY .= ', t1.uniqueid';

$FG_COL_QUERY_GRAPH='t1.callstart, t1.duration';

// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=500;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

// The variable $FG_EDITION define if you want process to the edition of the database record
$FG_EDITION=true;

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - Call Logs - ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="70%";

	if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";
	$instance_table = new Table($FG_TABLE_NAME, $FG_COL_QUERY);
	$instance_table_graph = new Table($FG_TABLE_NAME, $FG_COL_QUERY_GRAPH);


if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}

if ($posted==1){
  
  function do_field($sql,$fld,$dbfld){
  		$fldtype = $fld.'type';
		global $$fld;
		global $$fldtype;		
        if ($$fld){
                if (strpos($sql,'WHERE') > 0){
                        $sql = "$sql AND ";
                }else{
                        $sql = "$sql WHERE ";
                }
				$sql = "$sql t1.$dbfld";
				if (isset ($$fldtype)){                
                        switch ($$fldtype) {							
							case 1:	$sql = "$sql='".$$fld."'";  break;
							case 2: $sql = "$sql LIKE '".$$fld."%'";  break;
							case 3: $sql = "$sql LIKE '%".$$fld."%'";  break;
							case 4: $sql = "$sql LIKE '%".$$fld."'";  break;
							case 5:	$sql = "$sql <> '".$$fld."'";  
						}
                }else{ $sql = "$sql LIKE '%".$$fld."%'"; }
		}
        return $sql;
  }  
  $SQLcmd = '';
  
  $SQLcmd = do_field($SQLcmd, 'src', 'src');
  $SQLcmd = do_field($SQLcmd, 'dst', 'calledstation'); 
}
$date_clause='';
  
if (strpos($SQLcmd, 'WHERE') > 0) { 
	$FG_TABLE_CLAUSE = substr($SQLcmd,6).$date_clause; 
}elseif (strpos($date_clause, 'AND') > 0){
	$FG_TABLE_CLAUSE = substr($date_clause,5); 
}


if (isset($customer)  &&  ($customer>0)){
	if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
	$FG_TABLE_CLAUSE.="t1.username='$customer'";
}else{
	if (isset($entercustomer)  &&  ($entercustomer>0)){
		if (strlen($FG_TABLE_CLAUSE)>0) $FG_TABLE_CLAUSE.=" AND ";
		$FG_TABLE_CLAUSE.="t1.username='$entercustomer'";
	}
}

if (strlen($FG_TABLE_CLAUSE)>0)
{
	$FG_TABLE_CLAUSE.=" AND ";
}
$FG_TABLE_CLAUSE .=" t1.starttime >(Select CASE  WHEN max(cover_enddate) IS NULL THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices WHERE cardid = ".$_SESSION["card_id"].")";


if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
}
$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

/************************/
$QUERY = "SELECT substring(t1.starttime,1,10) AS day, sum(t1.sessiontime) AS calltime, sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE." GROUP BY substring(t1.starttime,1,10) ORDER BY day"; //extract(DAY from calldate)
//echo "$QUERY";

if (!$nodisplay)
{
	$list_total_day  = $instance_table->SQLExec ($DBHandle, $QUERY);
	$nb_record = $instance_table -> Table_count ($DBHandle, $FG_TABLE_CLAUSE);
}//end IF nodisplay


// GROUP BY DESTINATION FOR THE INVOICE

$QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE." GROUP BY destination";

if (!$nodisplay)
{
	$list_total_destination  = $instance_table->SQLExec ($DBHandle, $QUERY);
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

if ((isset($customer)  &&  ($customer>0)) || (isset($entercustomer)  &&  ($entercustomer>0))){

	$FG_TABLE_CLAUSE = "";
	if (isset($customer)  &&  ($customer>0)){		
		$FG_TABLE_CLAUSE =" username='$customer' ";
	}elseif (isset($entercustomer)  &&  ($entercustomer>0)){
		$FG_TABLE_CLAUSE =" username='$entercustomer' ";
	}

	$instance_table_customer = new Table("cc_card", "id,  username, lastname, firstname, address, city, state, country, zipcode, phone, email, fax ,activated, vat, creationdate");
	$info_customer = $instance_table_customer -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "id", "ASC", null, null, null, null);
}
/************************************************************/

$QUERY = "SELECT substring(t1.creationdate,1,10) AS day, sum(t1.amount) AS cost, count(*) as nbcharge, t1.currency FROM cc_charge t1 ".
		 " WHERE id_cc_card='".$_SESSION["card_id"]."' AND t1.creationdate >= (Select CASE WHEN max(cover_enddate) is NULL  THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices) GROUP BY substring(t1.creationdate,1,10) ORDER BY day"; //extract(DAY from calldate)

if (!$nodisplay)
{
	$list_total_day_charge  = $instance_table->SQLExec ($DBHandle, $QUERY);
}//end IF nodisplay

$QUERY = "Select CASE WHEN max(cover_enddate) is NULL  THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices WHERE cardid = ".$info_customer[0][0];

if (!$nodisplay)
{
	$invoice_dates = $instance_table->SQLExec ($DBHandle, $QUERY);			 
}//end IF nodisplay
if ($invoice_dates[0][0] == '0001-01-01 01:00:00')
{
	$invoice_dates[0][0] = $info_customer[0][14];
}

if($exporttype!="pdf"){
$currencies_list = get_currencies();
$smarty->display( 'main.tpl');
?>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

//-->
</script>

<?php  
}else{
   require('pdf-invoices/html2pdf/html2fpdf.php');
   ob_start();

} ?>

<table width="14%" align="center">
<tr>
<td height="93"> <img src="templates/default/images/companylogo.gif"/> </td>
</tr>
</table>

<br>

<?php 
if ((is_array($list_total_day_charge) && count($list_total_day_charge)>0 ) || (is_array($list_total_destination) && count($list_total_destination)>0))
{
?>
<table  class="invoice_main_table">
 <tr>
        <td class="invoice_heading"><?php echo gettext("Invoice Details"); ?></td>
 </tr>
 
 <tr>
        <td valign="top"><table width="60%" align="left" cellpadding="0" cellspacing="0">
            <tr>
              <td width="35%">&nbsp; </td>
              <td width="65%">&nbsp; </td>
            </tr>
            <tr>
              <td width="35%" class="invoice_td"><?php echo gettext("Name");?>&nbsp;: </td>
              <td width="65%" class="invoice_td"><?php echo $info_customer[0][3] ." ".$info_customer[0][2] ?></td>
            </tr>
            <tr>
              <td width="35%" class="invoice_td"><?php echo gettext("Card Number");?>&nbsp;:</td>
              <td width="65%" class="invoice_td"><?php echo $info_customer[0][1] ?> </td>
            </tr>           
            <tr>
              <td width="35%" class="invoice_td"><?php echo gettext("From Date");?>&nbsp;:</td>
              <td width="65%" class="invoice_td"><?php echo display_dateonly($invoice_dates[0][0]);?></td>
            </tr>
            <tr>
              <td >&nbsp;</td>
              <td >&nbsp;</td>
            </tr>
            
        </table></td>
      </tr>	   
	  <tr>
		  <td valign="top">
		  	<?php 
				if (is_array($list_total_day_charge) && count($list_total_day_charge)>0){
				
				$totalcharge=0;
				$totalcost=0;
				$total_extra_charges = 0;
				foreach ($list_total_day_charge as $data){	
					if ($mmax < $data[1]) $mmax=$data[1];
					$totalcharge+=$data[2];
					$totalcost+=$data[1];
					$total_extra_charges += convert_currency($currencies_list,$data[1], $data[3],BASE_CURRENCY);
				}
				
				?>
				<!-- FIN TITLE GLOBAL MINUTES //-->
				<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
					<tr>	
						 <td colspan="5" align="center"><b><?php echo gettext("Extra Charges");?></b></font></td>
					</tr>
					<tr class="invoice_subheading">
						<td  class="invoice_td" align="center"><?php echo gettext("DATE");?></td>
						<td class="invoice_td" align="center"><?php echo gettext("NB CHARGE");?></td>
						<td class="invoice_td" align="center"><?php echo gettext("TOTALCOST");?></td>
				
					</tr><?php  		
						$i=0;
						foreach ($list_total_day_charge as $data){	
						$i=($i+1)%2;		
					?>
					<tr class="invoice_rows">
						<td align="center" class="invoice_td"><?php echo $data[0]?></td>
						<td class="invoice_td" align="right"><?php echo $data[2]?></td>
						<td  class="invoice_td" align="right"><?php echo convert_currency($currencies_list, $data[1], $data[3], BASE_CURRENCY)." ".BASE_CURRENCY ?></td>
					                 	
					</tr>	 
					<?php 
						 }	 	 	
					 ?>  
					<tr>
						<td class="invoice_td">&nbsp;</td>
						<td class="invoice_td">&nbsp;</td>
						<td class="invoice_td">&nbsp;</td>
					</tr> 
					<tr class="invoice_subheading">
						<td class="invoice_td"><?php echo gettext("TOTAL");?></td>
						<td class="invoice_td" align="right"><?php echo $totalcharge?></td>
						<td class="invoice_td" align="right"><?php  display_2bill($total_extra_charges)?></td>
					</tr>
				</table>
					  
				<?php  } ?>			
				
		  </td>
	  </tr>
	  <?php 			
				$mmax=0;
				$totalcall=0;
				$totalminutes=0;
				$totalcost=0;
				if (is_array($list_total_destination) && count($list_total_destination)>0){
				foreach ($list_total_destination as $data){	
					if ($mmax < $data[1]) $mmax=$data[1];
					$totalcall+=$data[3];
					$totalminutes+=$data[1];
					$totalcost+=$data[2];
				}
				
				?>
	  <tr>
	  <td>&nbsp;</td>
	  </tr>
	  <tr>
	  <td>	  
	  		<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
			<tr>				
				<td  align="center" colspan="5"><b><?php echo gettext("CALLS PER DESTINATION");?></b></td>
			</tr>
			<tr class="invoice_subheading">
				<td align="center" class="invoice_td"><?php echo gettext("DESTINATION");?></td>
				<td align="right" class="invoice_td"><?php echo gettext("DUR");?></td>
				<td align="center" class="invoice_td"><?php echo gettext("GRAPHIC");?>  </td>
				<td align="right" class="invoice_td"><?php echo gettext("CALL");?></td>
				<td align="right" class="invoice_td"><?php echo gettext("TOTALCOST");?></td>
			</tr>
		<?php  		
				$i=0;
				foreach ($list_total_destination as $data){	
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
				if ($mmax>0) 	$widthbar= intval(($data[1]/$mmax)*200); 
				
			?>
			<tr class="invoice_rows">
				<td align="left" class="invoice_td"><?php echo $data[0]?></font></td>
				<td class="invoice_td" align="right"><?php echo $minutes?> </font></td>
				<td class="invoice_td" align="left">
					<img src="<?php echo Images_Path_Main ?>/sidenav-selected.gif" height="6" width="<?php echo $widthbar?>">
				</td>
				<td class="invoice_td" align="right"><?php echo $data[3]?></td>				
				<td class="invoice_td" align="right"><?php  display_2bill($data[2]) ?></td>			                  	
			</tr>	
			 <?php 	 }	 	 	
				
				if ((!isset($resulttype)) || ($resulttype=="min")){
					$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
					$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
				}else{
					$total_tmc = intval($totalminutes/$totalcall);			
				}
			 
			 ?> 
			 <tr>
				<td class="invoice_td">&nbsp;</td>
				<td class="invoice_td">&nbsp;</td>
				<td class="invoice_td">&nbsp;</td>
				<td class="invoice_td">&nbsp;</td>
				<td class="invoice_td">&nbsp;</td>
			</tr>
			
			<tr class="invoice_subheading">
				<td align="left" class="invoice_td" ><?php echo gettext("TOTAL");?></td>
				<td align="right"  class="invoice_td"><?php echo $totalminutes?> </td>				
				<td align="right" class="invoice_td" colspan="2"><?php echo $totalcall?></b></font></td>
				<td align="right" class="invoice_td"><?php  display_2bill($totalcost) ?></td>
			</tr>
	</table>	  
	  </td>
	  </tr>
	  <?php
	  }
	  ?>
	  <tr>
	  <td>&nbsp;</td>
	  </tr>	  
	  <tr>
	  <td>
	  
	   <?php 
	   $total_invoice_cost = $totalcost + $total_extra_charges;
			if (is_array($list_total_day) && count($list_total_day)>0){
			
			$mmax=0;
			$totalcall=0;
			$totalminutes=0;
			$totalcost=0;
			foreach ($list_total_day as $data){	
				if ($mmax < $data[1]) $mmax=$data[1];
				$totalcall+=$data[3];
				$totalminutes+=$data[1];
				$totalcost+=$data[2];
			}
			
			?>
			
			<!-- FIN TITLE GLOBAL MINUTES //-->
			<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
				<tr>	
					<td align="center" colspan="5"><b><?php echo gettext("CALLS PER DAY");?></b> </td>
				</tr>
				<tr class="invoice_subheading">
					<td align="center" class="invoice_td"><?php echo gettext("DATE");?></td>
					<td align="right" class="invoice_td"><?php echo gettext("DUR");?> </td>
					<td align="center" class="invoice_td"><?php echo gettext("GRAPHIC");?> </td>
					<td align="right" class="invoice_td"><?php echo gettext("CALL");?></td>
					<td align="right" class="invoice_td"><?php echo gettext("TOTALCOST");?></td>			
				</tr>
				<?php  		
					$i=0;
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
					if ($mmax>0) 	$widthbar= intval(($data[1]/$mmax)*200); 
					
				?>
				<tr class="invoice_rows">
					<td align="center"  class="invoice_td"><?php echo $data[0]?></td>
					<td class="invoice_td" align="right"><?php echo $minutes?> </td>
					<td class="invoice_td" align="left">
						<img src="<?php echo Images_Path_Main ?>/sidenav-selected.gif" height="6" width="<?php echo $widthbar?>">
					</td>
					<td class="invoice_td" align="right"><?php echo $data[3]?></font></td>
					<td class="invoice_td" align="right"><?php  display_2bill($data[2]) ?></td>
				 <?php 	 }	 	 	
					if ((!isset($resulttype)) || ($resulttype=="min")){
						$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
						$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
					}else{
						$total_tmc = intval($totalminutes/$totalcall);			
					}
				 
				 ?>                   	
				</tr>	
				<tr >
					<td align="right">&nbsp;</td>
					<td align="center" colspan="2">&nbsp;</td>
					<td align="center">&nbsp;</td>
					<td align="center">&nbsp;</td>
				</tr>
				
				<tr class="invoice_subheading">
					<td align="left" class="invoice_td"><?php echo gettext("TOTAL");?></td>
					<td align="right"  class="invoice_td"><?php echo $totalminutes?> </td>
					<td align="center"  class="invoice_td">&nbsp; </td>
					<td align="right" class="invoice_td"><?php echo $totalcall?></td>
					<td align="right" class="invoice_td"><?php  display_2bill($totalcost) ?></td>
				</tr>
			</table>
				  
		<?php  } ?>
	  </td>
	  </tr>	  
	  <tr>
	  <td>&nbsp;</td>
	  </tr>
	  <?php  if (is_array($list) && count($list)>0){ ?>
	  <tr>
	  <td>
	  <center><b><?php echo gettext("Number of call");?> : <?php  if (is_array($list) && count($list)>0){ echo $nb_record; }else{echo "0";}?></b></center>
		<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%" align="center">
                <TR class="invoice_subheading"> 
		  		<TD width="7%" class="invoice_td">nb</TD>					
                  <?php 
				  	if (is_array($list) && count($list)>0){
				  		for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
					?>				
                  <TD width="<?php echo $FG_TABLE_COL[$i][2]?>" align=middle class="invoice_td" > 
                    <center>
                    <?php echo $FG_TABLE_COL[$i][0]?> 
                  </center></TD>
				   <?php } ?>		
				   <?php if ($FG_DELETION || $FG_EDITION){ ?>
				   <?php } ?>		
                </TR>
				<?php
				  	 $ligne_number=0;					 
				  	 foreach ($list as $recordset){ 
						 $ligne_number++;
				?>
               		 <TR class="invoice_rows"> 
			<TD align="<?php echo $FG_TABLE_COL[$i][3]?>" class="invoice_td"><?php  echo $ligne_number+$current_page*$FG_LIMITE_DISPLAY; ?></TD>
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
							if ($FG_TABLE_COL[$i][6]=="lie"){
								$instance_sub_table = new Table($FG_TABLE_COL[$i][7], $FG_TABLE_COL[$i][8]);
								$sub_clause = str_replace("%id", $recordset[$i], $FG_TABLE_COL[$i][9]);
								$select_list = $instance_sub_table -> Get_list ($DBHandle, $sub_clause, null, null, null, null, null, null);
								$field_list_sun = split(',',$FG_TABLE_COL[$i][8]);
								$record_display = $FG_TABLE_COL[$i][10];
								for ($l=1;$l<=count($field_list_sun);$l++){													$record_display = str_replace("%$l", $select_list[0][$l-1], $record_display);	
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
                 		 <TD align="<?php echo $FG_TABLE_COL[$i][3]?>" class="invoice_td"><?php 
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
					<TR> 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
				 		 ?>
                 		 <TD>&nbsp;</TD>
				 		 <?php  } ?>
                 		 <TD align="center">&nbsp;</TD>				
					</TR>
									
				<?php					 
					 } //END_WHILE
					 
				  }else{
				  		echo gettext("No data found !!!");				  
				  }//end_if
				 ?>
            </TABLE>
	  </td>
	  </tr>
	  <?php } ?>
	 <tr class="invoice_subheading">
	 <td  align="right" class="invoice_td"><?php echo gettext("Total");?> = <?php echo display_2bill($total_invoice_cost);?>&nbsp;</td>
	 </tr>
	 <tr class="invoice_subheading">
	 <td  align="right" class="invoice_td"><?php echo gettext("VAT");?> = <?php 
	 $prvat = ($info_customer[0][13] / 100) * $total_invoice_cost;
	 display_2bill($prvat);?>&nbsp;</td>
	 </tr>
	 <tr class="invoice_subheading">
	 <td  align="right" class="invoice_td"><?php echo gettext("Grand Total");?> = <?php echo display_2bill($total_invoice_cost + $prvat);?>&nbsp;</td>
	 </tr>
	  
</table>

<br><br>


<?php 
}
else
{
?>
<center>
<?php 
	echo gettext("No calls in your selection!");
?>
</center>
<?php 	
}
?>



<?php  if($exporttype!="pdf"){ ?>

<?php
	$smarty->display('footer.tpl');
?>

<?php  }else{
// EXPORT TO PDF

	$html = ob_get_contents();
	// delete output-Buffer
	ob_end_clean();
	
	$pdf = new HTML2FPDF();
	
	$pdf -> DisplayPreferences('HideWindowUI');
	
	$pdf -> AddPage();
	$pdf -> WriteHTML($html);
	
	$html = ob_get_contents();
	
	$pdf->Output('CC_invoice_'.date("d/m/Y-H:i").'.pdf', 'I');



} ?>
