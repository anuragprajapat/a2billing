<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("../lib/smarty.php");

if (! has_rights (ACX_BILLING)){ 
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");	   
	die();	   
}

getpost_ifset(array('customer', 'posted', 'Period', 'cardid','exporttype','choose_billperiod','id','invoice_type'));
if ($invoice_type == "")
{
	$invoice_type = 1;
}
if ($invoice_type == 1)
{
	if($cardid == "" )
	{
		exit("Invalid ID");
	}
}
if ( $invoice_type == 2)
{
	if(($id == "" || !is_numeric($id)))
	{
		exit(gettext("Invalid ID"));
	}
}
if ($invoice_type == 1)
{
	$invoice_heading = gettext("Unbilled Details");
	$invocie_top_heading = gettext("Unbilled Invoice Details for Card Number");	
}
else
{
	$invoice_heading = gettext("Billed Details");
	$invocie_top_heading = gettext("Billed Invoice Details for Card Number");	
}


$DBHandle = DbConnect();
$num = 0;
if ($invoice_type == 1)
{
	$QUERY = "Select username, vat, t1.id from cc_card t1 where t1.id = $cardid";
}
else
{
	$QUERY = "Select username, vat, t1.id from cc_card t1, cc_invoices t2 where t1.id = t2.cardid and t2.id = $id";
}
$res_user = $DBHandle -> Execute($QUERY);
if ($res_user)
	$num = $res_user -> RecordCount();

if($num > 0)
{
	$userRecord = $res_user -> fetchRow();
	$customer = $userRecord[0];
	$vat = $userRecord[1];
	$customerID = $userRecord[2];	
}
else
{
	exit(gettext("No User found"));
}

if (!isset ($current_page) || ($current_page == "")){	
		$current_page=0; 
	}


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME="cc_call t1";

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

$FG_TABLE_COL[]=array (gettext("Calldate"), "starttime", "18%", "center", "SORT", "19", "", "", "", "", "", "display_dateformat");
$FG_TABLE_COL[]=array (gettext("Source"), "src", "10%", "center", "SORT", "30");
$FG_TABLE_COL[]=array (gettext("Callednumber"), "calledstation", "18%", "right", "SORT", "30", "", "", "", "", "", "");
$FG_TABLE_COL[]=array (gettext("Destination"), "destination", "18%", "center", "SORT", "30", "", "", "", "", "", "remove_prefix");
$FG_TABLE_COL[]=array (gettext("Duration"), "sessiontime", "8%", "center", "SORT", "30", "", "", "", "", "", "display_minute");

if (!(isset($customer)  &&  ($customer>0)) && !(isset($entercustomer)  &&  ($entercustomer>0))){
	$FG_TABLE_COL[]=array (gettext("Cardused"), "username", "11%", "center", "SORT", "30");
}
$FG_TABLE_COL[]=array (gettext("Cost"), "sessionbill", "9%", "center", "SORT", "30", "", "", "", "", "", "display_2bill");

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


//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_DELETION || $FG_EDITION) $FG_TOTAL_TABLE_COL++;

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
// Period (Month-Day)
if (DB_TYPE == "postgres"){		
	 	$UNIX_TIMESTAMP = "";
}else{
		$UNIX_TIMESTAMP = "UNIX_TIMESTAMP";
}


$lastdayofmonth = date("t", strtotime($tostatsmonth.'-01'));

if ($Period=="Month"){
		
		
		if ($frommonth && isset($fromstatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth-01')";
		if ($tomonth && isset($tostatsmonth)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) <= $UNIX_TIMESTAMP('".$tostatsmonth."-$lastdayofmonth 23:59:59')"; 
		
}else{
		if ($fromday && isset($fromstatsday_sday) && isset($fromstatsmonth_sday) && isset($fromstatsmonth_shour) && isset($fromstatsmonth_smin) ) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) >= $UNIX_TIMESTAMP('$fromstatsmonth_sday-$fromstatsday_sday $fromstatsmonth_shour:$fromstatsmonth_smin')";
		if ($today && isset($tostatsday_sday) && isset($tostatsmonth_sday) && isset($tostatsmonth_shour) && isset($tostatsmonth_smin)) $date_clause.=" AND $UNIX_TIMESTAMP(t1.starttime) <= $UNIX_TIMESTAMP('$tostatsmonth_sday-".sprintf("%02d",intval($tostatsday_sday))." $tostatsmonth_shour:$tostatsmonth_smin')";
}

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

if ($invoice_type == 1)
{
	$FG_TABLE_CLAUSE.="t1.starttime >(Select CASE  WHEN max(cover_enddate) IS NULL THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices)";
}
else
{
	$FG_TABLE_CLAUSE.="t1.starttime >(Select cover_startdate  from cc_invoices where id ='$id') AND t1.stoptime <(Select cover_enddate from cc_invoices where id ='$id') ";
}
if (!$nodisplay){
	$list = $instance_table -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, $order, $sens, null, null, $FG_LIMITE_DISPLAY, $current_page*$FG_LIMITE_DISPLAY);
}
$_SESSION["pr_sql_export"]="SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";

/************************/

$QUERY = "SELECT substring(t1.starttime,1,10) AS day, sum(t1.sessiontime) AS calltime, sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE."  GROUP BY substring(t1.starttime,1,10) ORDER BY day"; //extract(DAY from calldate)

if (!$nodisplay){		
		$res = $DBHandle -> Execute($QUERY);
		if ($res){
			$num = $res -> RecordCount( );
			for($i=0;$i<$num;$i++)
			{					
				$list_total_day [] =$res -> fetchRow();				 
			}
		}
if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
$nb_record = $instance_table -> Table_count ($DBHandle, $FG_TABLE_CLAUSE);
if ($FG_DEBUG >= 1) var_dump ($list);

}//end IF nodisplay
// GROUP BY DESTINATION FOR THE INVOICE
$QUERY = "SELECT destination, sum(t1.sessiontime) AS calltime, 
sum(t1.sessionbill) AS cost, count(*) as nbcall FROM $FG_TABLE_NAME WHERE ".$FG_TABLE_CLAUSE."  GROUP BY destination";
if (!$nodisplay){
		$res = $DBHandle -> Execute($QUERY);
		if ($res){
			$num = $res -> RecordCount( );
			for($i=0;$i<$num;$i++)
			{				
				$list_total_destination [] =$res -> fetchRow();				 
			}
		}
if ($FG_DEBUG == 3) echo "<br>Clause : $FG_TABLE_CLAUSE";
if ($FG_DEBUG >= 1) var_dump ($list_total_destination);
}//end IF nodisplay

/************************************************ DID Billing Section *********************************************/
// Fixed + Dial = 0
// Fixed = 1
// Dail = 2
// Free = 3


// 1. Billing Type:: All DID Calls that have DID Type 0 and 2
// 1. Billing Type:: All DID Calls that have DID Type 0 and 2
if ($invoice_type == 1)
{
	$QUERY = "SELECT t1.amount, t1.creationdate, t1.description, t3.countryname, t2.did ".
	" FROM cc_charge t1 LEFT JOIN (cc_did t2, cc_country t3 ) ON ( t1.id_cc_did = t2.id AND t2.id_cc_country = t3.id ) ".
	" WHERE (t1.chargetype = 1 OR t1.chargetype = 2) AND t1.id_cc_card = ".$cardid.
	" AND t1.creationdate >(Select CASE  WHEN max(cover_enddate) IS NULL THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices)";
}
else
{
	$QUERY = "SELECT t1.amount, t1.creationdate, t1.description, t3.countryname, t2.did ".
	" FROM cc_charge t1 LEFT JOIN (cc_did t2, cc_country t3 ) ON ( t1.id_cc_did = t2.id AND t2.id_cc_country = t3.id ) ".
	" WHERE (t1.chargetype = 2 OR t1.chargetype = 1) AND t1.id_cc_card = ".$customerID.
	" AND t1.creationdate > (Select cover_startdate  from cc_invoices where id ='$id') AND t1.creationdate <(Select cover_enddate from cc_invoices where id ='$id')";
}
 
 
if (!$nodisplay)
{
	$res = $DBHandle -> Execute($QUERY);	
	if ($res){
		$num = $res -> RecordCount( );
		for($i=0;$i<$num;$i++)
		{				
			$list_total_did [] =$res -> fetchRow();
		}
	}
	if ($FG_DEBUG >= 1) var_dump ($list_total_did);
}//end IF nodisplay

/************************************************ END DID Billing Section *********************************************/
/*************************************************CHARGES SECTION START ************************************************/

// Charge Types

// Connection charge for DID setup = 1
// Monthly Charge for DID use = 2
// Subscription fee = 3
// Extra charge =  4
if ($invoice_type == 1)
{
	$QUERY = "SELECT t1.id_cc_card, t1.iduser, t1.creationdate, t1.amount, t1.chargetype, t1.id_cc_did, t1.currency, t1.description" .
	" FROM cc_charge t1, cc_card t2 WHERE (t1.chargetype <> 1 AND t1.chargetype <> 2) " .
	" AND t2.username = '$customer' AND t1.id_cc_card = t2.id AND t1.creationdate >= (Select CASE WHEN max(cover_enddate) is NULL " .
	" THEN '0001-01-01 01:00:00' ELSE max(cover_enddate) END from cc_invoices) Order by t1.creationdate";
}
else
{
	$QUERY = "SELECT t1.id_cc_card, t1.iduser, t1.creationdate, t1.amount, t1.chargetype, t1.id_cc_did, t1.currency, t1.description" .
	" FROM cc_charge t1, cc_card t2 WHERE (t1.chargetype <> 2 AND t1.chargetype <> 1)" .
	" AND t2.username = '$customer' AND t1.id_cc_card = t2.id AND " .
	" t1.creationdate >(Select cover_startdate  from cc_invoices where id ='$id') " .
	" AND t1.creationdate <(Select cover_enddate  from cc_invoices where id ='$id')";
}
//echo "<br>".$QUERY."<br>";

if (!$nodisplay)
{
	$res = $DBHandle -> Execute($QUERY);
	if ($res){
		$num = $res -> RecordCount();
		for($i=0;$i<$num;$i++)
		{
			$list_total_charges [] =$res -> fetchRow();
		}
	}
	
	if ($FG_DEBUG >= 1) var_dump ($list_total_charges);
}//end IF nodisplay
/*************************************************CHARGES SECTION END ************************************************/

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

/*************************************************************/
if ((isset($customer)  &&  ($customer>0)) || (isset($entercustomer)  &&  ($entercustomer>0))){

	$FG_TABLE_CLAUSE = "";
	if (isset($customer)  &&  ($customer>0)){		
		$FG_TABLE_CLAUSE =" username='$customer' ";
	}elseif (isset($entercustomer)  &&  ($entercustomer>0)){
		$FG_TABLE_CLAUSE =" username='$entercustomer' ";
	}

	$instance_table_customer = new Table("cc_card", "id,  username, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, activated");
	$info_customer = $instance_table_customer -> Get_list ($DBHandle, $FG_TABLE_CLAUSE, "id", "ASC", null, null, null, null);

}
?>

<?php
if($exporttype == "pdf")
{
	require('pdf-invoices/html2pdf/html2fpdf.php');
   	ob_start();
}

?>
<?php 
$currencies_list = get_currencies();

//For DID DIAL & Fixed + Dial
$totalcost = 0;
if (is_array($list_total_destination) && count($list_total_destination)>0){
	$mmax=0;
	$totalcall=0;
	$totalminutes=0;	
	foreach ($list_total_destination as $data){	
		if ($mmax < $data[1]) $mmax=$data[1];
		$totalcall+=$data[3];
		$totalminutes+=$data[1];
		$totalcost+=$data[2];	
	}
}
?>
<table cellpadding="0"  align="center">
<tr>
<td align="center">
<img src="<?php echo Images_Path;?>/asterisk01.jpg" align="middle">
</td>
</tr>
</table>
<br>
<center><h4><font color="#FF0000"><?php echo $invocie_top_heading; ?>&nbsp;<?php echo $info_customer[0][1] ?> </font></h4></center>
<br>
<br>
	
	<table cellspacing="0" cellpadding="2" align="center" width="80%" >
     
      <tr>
        <td colspan="2" bgcolor="#FFFFCC"><font size="5" color="#FF0000"><?php echo $invoice_heading; ?></font></td>
      </tr>
      <tr>
        <td valign="top" colspan="2"></td>
      </tr>	 
	<tr>
	  <td width="35%">&nbsp; </td>
	  <td >&nbsp; </td>
	</tr>
	<tr>
	  <td width="35%" ><font color="#003399"><?php echo gettext("Name")?>&nbsp; :</font> </td>
	  <td  ><font color="#003399"><?php echo $info_customer[0][3] ." ".$info_customer[0][2] ?></font></td>
	</tr>
	<tr>
	  <td width="35%" ><font color="#003399"><?php echo gettext("Card Number")?>&nbsp; :</font></td>
	  <td  ><font color="#003399"><?php echo $info_customer[0][1] ?></font> </td>
	</tr>           
	<tr>
	  <td width="35%" ><font color="#003399"><?php echo gettext("As of Date")?>&nbsp; :</font></td>
	  <td  ><font color="#003399"><?php echo date('m-d-Y');?> </font></td>
	</tr>
	</table>
	  
	  
	  <table align="center" width="80%">
	   <?php if($exporttype != "pdf"){?>
	  <tr>
	  <td align="right" colspan="4">&nbsp;	  
	  </td>
	  </tr>
	  <?php }?>
	 
   				<tr>
				<td colspan="4" align="center"><font> <b><?php echo gettext("By Destination")?></b></font> </td>
				</tr>

			<tr bgcolor="#CCCCCC">
              <td  width="29%"><font color="#003399"><b><?php echo gettext("Destination")?> </b></font></td>
              <td width="38%" ><font color="#003399"><b><?php echo gettext("Duration")?></b></font> </td>
			 
			  <td width="12%" align="center" ><font color="#003399"><b><?php echo gettext("Calls")?> </b></font></td>
              <td   align="right"><font color="#003399"><b><?php echo gettext("Amount")." (".BASE_CURRENCY.")"; ?> </b></font></td>
            </tr>
			<?php  		
				$i=0;
				if (is_array($list_total_destination) && count($list_total_destination)>0){
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
              <td width="29%" ><font color="#003399"><?php echo $data[0]?></font></td>
              <td width="38%" ><font color="#003399"><?php echo $minutes?> </font></td>
			  
			  <td width="12%" align="right" ><font color="#003399"><?php echo $data[3]?></font> </td>
              <td  align="right" ><font color="#003399"><?php  display_2bill($data[2]) ?></font></td>
            </tr>
			<?php 	 }	 	 	
	 	
			if ((!isset($resulttype)) || ($resulttype=="min")){  
				$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
				$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
			}else{
				$total_tmc = intval($totalminutes/$totalcall);			
			}
			 ?>   
			 <tr >
              <td width="29%" >&nbsp;</td>
              <td width="38%" >&nbsp;</td>              
			  <td width="12%" >&nbsp; </td>
			  <td  >&nbsp; </td>
			  
            </tr>
            <tr bgcolor="#CCCCCC">
              <td width="29%" ><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td width="38%" ><font color="#003399"><?php echo $totalminutes?></font></td>			  
			  <td width="12%"  align="right"><font color="#003399"><?php echo $totalcall?> </font></td>
              <td  align="right" ><font color="#003399"><?php  display_2bill($totalcost - $totalcost_did) ?></font> </td>
            </tr> 
            <?php }else{
            ?>         
            <tr >
              <td width="29%"><?php echo gettext("None!!!")?></td>
              <td width="38%">&nbsp;</td>
              
			  <td width="12%">&nbsp; </td>
			  <td >&nbsp; </td>
			  
            </tr>
            <?php }?>
              
            <tr >
              <td width="29%">&nbsp;</td>
              <td width="38%">&nbsp;</td>
              
			  <td width="12%">&nbsp; </td>
			  <td >&nbsp; </td>
			  
            </tr>			
			</table>
			<table align="center" width="80%">
			<!-- Start Here ****************************************-->
			<?php 
				$mmax=0;
				$totalcall=0;
				$totalminutes=0;
				$totalcost_day=0;
				foreach ($list_total_day as $data){	
					if ($mmax < $data[1]) $mmax=$data[1];
					$totalcall+=$data[3];
					$totalminutes+=$data[1];
					$totalcost_day+=$data[2];
				}
				?>
				
				<tr>
				<td colspan="4" align="center"><b><?php echo gettext("By Date")?></b> </td>
				</tr>
			  <tr bgcolor="#CCCCCC">
              <td  width="29%"><font color="#003399"><b><?php echo gettext("Date")?></b> </font></td>
              <td width="38%" ><font color="#003399"><b><?php echo gettext("Duration")?></b> </font></td>
			  
			  <td width="12%" align="center" ><font color="#003399"><b><?php echo gettext("Calls")?></b> </font></td>
              <td width="21%"  align="right"><font color="#003399"><b><?php echo gettext("Amount")." (".BASE_CURRENCY.")"; ?></b> </font></td>
            </tr>
			<?php  		
				$i=0;
				if (is_array($list_total_day) && count($list_total_day)>0){
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
              <td width="29%" ><font color="#003399"><?php echo $data[0]?></font></td>
              <td width="38%" ><font color="#003399"><?php echo $minutes?> </font></td>
			  
			  <td width="12%"  align="right"><font color="#003399"><?php echo $data[3]?> </font></td>
              <td width="21%" align="right" ><font color="#003399"><?php  display_2bill($data[2]) ?></font></td>
            </tr>
			 <?php 	 }	 	 	
	 	
				if ((!isset($resulttype)) || ($resulttype=="min")){  
					$total_tmc = sprintf("%02d",intval(($totalminutes/$totalcall)/60)).":".sprintf("%02d",intval(($totalminutes/$totalcall)%60));				
					$totalminutes = sprintf("%02d",intval($totalminutes/60)).":".sprintf("%02d",intval($totalminutes%60));
				}else{
					$total_tmc = intval($totalminutes/$totalcall);			
				}
			 
			 ?>               
			 <tr >
              <td width="29%" >&nbsp;</td>
              <td width="38%" >&nbsp;</td>
              
			  <td width="12%" >&nbsp; </td>
			  <td width="21%" >&nbsp; </td>
			  
            </tr>
            <tr bgcolor="#CCCCCC">
              <td width="29%" ><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td width="38%" ><font color="#003399"><?php echo $totalminutes?></font></td>			  
			  <td width="12%" align="right" ><font color="#003399"><?php echo $totalcall?></font> </td>
              <td width="21%" align="right" ><font color="#003399"><?php  display_2bill($totalcost_day) ?> </font></td>
            </tr>        
            <?php }else{
            ?>         
            <tr >
              <td width="29%"><?php echo gettext("None!!!")?></td>
              <td width="38%">&nbsp;</td>
             
			  <td width="12%">&nbsp; </td>
			  <td width="21%">&nbsp; </td>
			  
            </tr>	
            <?php }?>    
            <tr >
              <td width="29%">&nbsp;</td>
              <td width="38%">&nbsp;</td>
             
			  <td width="12%">&nbsp; </td>
			  <td width="21%">&nbsp; </td>
			  
            </tr>				
				
			
			<!-- END HERE ******************************************-->
     
    </table>
	 <table align="center" width="80%">
	 <?php
	 if (is_array($list_total_did) && count($list_total_did)>0)
				{ 
	 ?>
		   <tr>
	  <td>
	  <!------------------------ DID Billing Here Starts ----------------------->
		
		<table width="100%" align="left" cellpadding="0" cellspacing="0">
   				<tr>
				<td colspan="5" align="center"><font><b><?php echo gettext("DID Billing")?></b></font> </td>
				</tr>
			<tr  bgcolor="#CCCCCC">
              <td  width="20%"> <font color="#003399"><b><?php echo gettext("Charge Date")?> </b></font></td>
              <td width="13%" ><font color="#003399"><b><?php echo gettext("DID")?> </b></font></td>
			  <td width="14%" ><font color="#003399"><b><?php echo gettext("Country")?></b></font> </td>
			  <td width="41%" ><font color="#003399"><b><?php echo gettext("Description")?> </b></font></td>  			  
              <td width="12%"  align="right"><font color="#003399"><b><?php echo gettext("Amount")." (".BASE_CURRENCY.")"; ?></b></font> </td>
            </tr>
			<?php  		
				$i=0;
				$totaldidcost = 0;
								
					foreach ($list_total_did as $data)
					{	
						$totaldidcost = $totaldidcost + $data[0];					
			
			?>
			 <tr class="invoice_rows">
              <td width="20%" ><font color="#003399"><?php echo $data[1]?></font></td>
              <td width="13%" ><font color="#003399">&nbsp;<?php  echo $data[4]; ?></font> </td>
  			  <td width="14%" ><font color="#003399">&nbsp;<?php  echo $data[3]; ?> </font></td>
			  <td width="41%" ><font color="#003399"><?php echo $data[2]?></font> </td>			  
              <td width="12%" align="right" ><font color="#003399"><?php  display_2bill($data[0]) ?></font></td>
            </tr>
			 <?php  }	 	
				$totalcost = $totalcost  + $totaldidcost; 
			 ?>   
			 <tr >
              <td width="20%" >&nbsp;</td>
              <td width="13%" >&nbsp;</td>
              <td width="14%" >&nbsp; </td>
			  <td width="41%" >&nbsp; </td>			 
			  <td width="12%" >&nbsp; </td>			  
            </tr>
            <tr bgcolor="#CCCCCC" >
              <td width="20%" ><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td ><font color="#003399">&nbsp;</font></td>			  
			  <td width="14%" ><font color="#003399">&nbsp;</font> </td>
			  <td width="41%" ><font color="#003399">&nbsp;</font> </td>			  
              <td width="12%" align="right" ><font color="#003399"><?php  display_2bill($totaldidcost) ?></font> </td>
            </tr>     
			       
            <tr >
              <td width="20%">&nbsp;</td>
              <td width="13%">&nbsp;</td>
              <td width="14%">&nbsp; </td>
			  <td width="41%">&nbsp; </td>			 
			  <td width="12%">&nbsp; </td>
			  
            </tr>
		
		</table>
		
		<!------------------------DID Billing ENDS Here ----------------------------->
	  </td>
	  </tr>
	  <?php			 
			 }
			 ?>
	   <tr>
		<td>
				
<!-------------------------EXTRA CHARGE START HERE ---------------------------------->
	
	 <?php  		
		$i=0;				
		$extracharge_total = 0;
		if (is_array($list_total_charges) && count($list_total_charges)>0)
		{
					
	  ?>	
		
		<table width="100%" align="left" cellpadding="0" cellspacing="0">
   				<tr>
				<td colspan="4" align="center"><font><b><?php echo gettext("Extra Charges")?></b></font> </td>
				</tr>
			<tr  bgcolor="#CCCCCC">
              <td  width="20%"> <font color="#003399"><b><?php echo gettext("Date")?> </b></font></td>
              <td width="19%" ><font color="#003399"><b><?php echo gettext("Type")?> </b></font></td>
			  <td width="43%" ><font color="#003399"><b><?php echo gettext("Description")?></b></font> </td>			
              <td width="18%"  align="right"><font color="#003399"><b><?php echo gettext("Amount")." (".BASE_CURRENCY.")"; ?></b></font> </td>
            </tr>
			<?php  		
			
			foreach ($list_total_charges as $data)
			{	
			 	$extracharge_total = $extracharge_total + convert_currency($currencies_list,$data[3], $data[6], BASE_CURRENCY) ;
		
			?>
			 <tr class="invoice_rows">
              <td width="20%" ><font color="#003399"><?php echo $data[2]?></font></td>
              <td width="19%" ><font color="#003399">
			  <?php 
			  if($data[4] == 1) //connection setup charges
				{
					echo gettext("Setup Charges");
				}
				if($data[4] == 2) //DID Montly charges
				{
					echo gettext("DID Montly Use");
				}
				if($data[4] == 3) //Subscription fee charges
				{
					echo gettext("Subscription Fee");
				}
				if($data[4] == 4) //Extra Misc charges
				{
					echo gettext("Extra Charges");
				}
			  ?>
			  </font> </td>
  			  <td width="43%" ><font color="#003399"><?php  echo $data[7];?></font></td>			 
              <td width="18%" align="right" ><font color="#003399"><?php echo convert_currency($currencies_list,$data[3], $data[6],BASE_CURRENCY)." ".BASE_CURRENCY ?></font></td>
            </tr>
			 <?php
			  }
			  //for loop end here
			   ?>
			 <tr >
              <td width="20%" >&nbsp;</td>
              <td width="19%" >&nbsp;</td>
              <td width="43%" >&nbsp; </td>			 
			  <td width="18%" >&nbsp; </td>
			  
            </tr>
            <tr bgcolor="#CCCCCC" >
              <td width="20%" ><font color="#003399"><?php echo gettext("TOTAL");?> </font></td>
              <td ><font color="#003399">&nbsp;</font></td>			  
			  <td width="43%" ><font color="#003399">&nbsp;</font> </td>			  
              <td width="18%" align="right" ><font color="#003399"><?php echo display_2bill($extracharge_total) ?></font> </td>
            </tr>    			        
            <tr >
              <td width="20%">&nbsp;</td>
              <td width="19%">&nbsp;</td>
              <td width="43%">&nbsp; </td>			  
			  <td width="18%">&nbsp; </td>			  
            </tr>		
		</table>		
		<?php
	   }
	   //if check end here
	   $totalcost = $totalcost + $extracharge_total;
	   ?><!-----------------------------EXTRA CHARGE END HERE ------------------------------->		
		
		</td>
		</tr>
	  
	 <tr>
	 <td><img src="<?php echo Images_Path;?>/spacer.jpg" align="middle" height="30px"></td>
	 </tr>
	 <tr bgcolor="#CCCCCC" >
	 <td  align="right" width="100%"><font color="#003399"><b><?php echo gettext("Grand Total")?> = <?php 
	 $prvat = ($vat / 100) * $totalcost;
	 display_2bill($prvat);?>&nbsp;</b></font></td>
	 </tr>
	 <tr>
	 <td><img src="<?php echo Images_Path;?>/spacer.jpg" align="middle" height="30px"></td>
	 </tr>
	 <tr bgcolor="#CCCCCC" >
	 <td  align="right" width="100%"><font color="#003399"><b><?php echo gettext("Grand Total")?> = <?php echo display_2bill($totalcost + $prvat);?>&nbsp;</b></font></td>
	 </tr>
	 <tr>
	 <td><img src="<?php echo Images_Path;?>/spacer.jpg" align="middle" height="30px"></td>
	 </tr>
	 </table>
	
<table cellspacing="0" cellpadding="2" width="80%" align="center">
<tr>
			<td colspan="3">&nbsp;</td>
			</tr>           			
			<tr>
              <td  align="left">Status :&nbsp;<?php if($info_customer[0][12] == 't') {?>
			  <img src="<?php echo Images_Path;?>/connected.jpg">
			  <?php }
			  else
			  {
			  ?>
			  <img src="<?php echo Images_Path;?>/terminated.jpg">
			  <?php }?> </td>              
            </tr>      
      <tr>	  
	  <td  align="left">&nbsp; <img src="<?php echo Images_Path;?>/connected.jpg"> &nbsp;<?php echo gettext("Connected")?> 
	  &nbsp;&nbsp;&nbsp;<img src="<?php echo Images_Path;?>/terminated.jpg">&nbsp;<?php echo gettext("Disconnected")?> 
	  
	  </td>
</table>
	


<?php  if($exporttype!="pdf"){ ?>

<?php
//$smarty->display( 'footer.tpl');
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
	$stream = $pdf->Output('UnBilledDetails_'.date("d/m/Y-H:i").'.pdf', 'S');	
	
	//================================Email Template Retrival Code ===================================
	
   	$QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='invoice' ";
   	$res = $DBHandle -> Execute($QUERY);
	$num = 0;	
	if ($res)
		$num = $res -> RecordCount();

	if (!$num)
    {
        echo "<br>Error : No email Template Found";
        exit();
    }

	for($i=0;$i<$num;$i++)
	{
		$listtemplate[] = $res->fetchRow();
	}

	list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
	if ($FG_DEBUG == 1)
    {
        echo "<br><b>mailtype : </b>$mailtype</br><b>from:</b> $from</br><b>fromname :</b> $fromname</br><b>subject</b> : $subject</br><b>ContentTemplate:</b></br><pre>$messagetext</pre></br><hr>";
    }
	
	//================================================================================================
$ok = send_email_attachment($from, $info_customer[0][10], $subject, $messagetext,'UnBilledDetails_'.date("d/m/Y-H:i").'.pdf', $stream );
/*
$email_from = "admin@a2billing.org"; 
$email_subject = "A2Billing Invoice";
$email_message = "Dear Customer<br><br> \n".
				"Attached is the Invoice.<br><br>\n\n".
				" Sincerely <br>\n\n".
				"Administrator A2Billing"; 
$email_to = $info_customer[0][10];
$headers = "From: ".$email_from;

$semi_rand = md5(time()); 
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
   
$headers .= "\nMIME-Version: 1.0\n" . 
            "Content-Type: multipart/mixed;\n" . 
            " boundary=\"{$mime_boundary}\""; 

$email_message .= "This is a multi-part message in MIME format.\n\n" . 
                "--{$mime_boundary}\n" . 
                "Content-Type:text/html; charset=\"iso-8859-1\"\n" . 
                "Content-Transfer-Encoding: 7bit\n\n" . 
$email_message . "\n\n"; 

$fileatt = "";           
$fileatt_type = "application/octet-stream"; 
$fileatt_name = 'UnBilledDetails_'.date("d/m/Y-H:i").'.pdf';  
$data = $stream;
$data = chunk_split(base64_encode($data)); 
$email_message .= "--{$mime_boundary}\n" . 
                  "Content-Type: {$fileatt_type};\n" . 
                  " name=\"{$fileatt_name}\"\n" .                 
                  "Content-Transfer-Encoding: base64\n\n" . 
                 $data . "\n\n" . 
                  "--{$mime_boundary}\n"; 
unset($data);
unset($file);
unset($fileatt);
unset($fileatt_type);
unset($fileatt_name);
$ok = @mail($email_to, $email_subject, $email_message, $headers);
*/

$smarty->display('main.tpl');
if($ok) 
{ 
?>
	<br><br>
	<table align="center" width="415" style="border:1px solid orange;">	
	<tr>
	<td width="407" height="20" bgcolor="#000066"><font color="#FFFFFF" face="Verdana, Arial, Helvetica, sans-serif"><b><?php echo gettext("Message")?></b></font></td>
	</tr>
	<tr>
	<td height="82" align="center" valign="middle"><font color="#000066" face="Verdana, Arial, Helvetica, sans-serif"><?php echo gettext("Congratulations!!! Email sent successfully to")." ".$info_customer[0][10]?>&nbsp;<?php echo $email_to;?>.</font></td>
	</tr>
	<tr>
	<td align="center"><FORM>
<INPUT type="button" value="&nbsp;Back&nbsp;" onClick="history.back()" class="form_input_button">
</FORM></td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	</table><br><br><br>
	
<?php
}
else
{ 
?>
<br><br>
<table align="center" width="415" style="border:1px solid orange;">	
	<tr>
	<td width="407" height="20" bgcolor="#000066"><font color="#FFFFFF" face="Verdana, Arial, Helvetica, sans-serif"><b><?php echo gettext("Message")?></b></font></td>
	</tr>
	<tr>
	<td height="82" align="center" valign="middle"><font color="#000066" face="Verdana, Arial, Helvetica, sans-serif"><?php echo gettext("Sorry!!! Email sending failed to")?>&nbsp;<?php echo $email_to;?>.</font></td>
	</tr>
	<tr>
	<td align="center">
	<FORM>
	<INPUT type="button" value="&nbsp;Back&nbsp;" onClick="history.back()" class="form_input_button">
	</FORM>
</td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	</table><br><br><br>
	
<?php
	
} 
$smarty->display('footer.tpl');
} 

?>
