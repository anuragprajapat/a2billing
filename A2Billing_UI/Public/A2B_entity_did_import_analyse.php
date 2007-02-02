<?php
// Common includes
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/smarty.php");
//include ("../lib/Class.Table.php");

set_time_limit(0);

if (! has_rights (ACX_RATECARD)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

getpost_ifset(array('didgroup', 'search_sources', 'task', 'status','countryID'));

//print_r ($_POST);
//print_r ($HTTP_POST_FILES);


$didgroupval= split('-:-', $didgroup);
if (!is_numeric($didgroupval[0])){
	echo "No DIDGroup defined !";
	exit();
}
$countryIDval= split('-:-', $countryID);
if (!is_numeric($countryIDval[0])){
	echo "No Country defined !";
	exit();
}

if ($search_sources!='nochange'){

	//echo "<br>---$search_sources";
	$fieldtoimport= split("\t", $search_sources);
	$fieldtoimport_sql = str_replace("\t", ", ", $search_sources);
	$fieldtoimport_sql = trim ($fieldtoimport_sql);
	if (strlen($fieldtoimport_sql)>0) $fieldtoimport_sql = ', '.$fieldtoimport_sql;
}

//echo "<br>---$fieldtoimport_sql<br>";
//print_r($fieldtoimport);


     $fixfield[0]="DIDGroup (KEY)";
	 $fixfield[1]="Country";

	 $field[0]="did";
	 $field[1]="fixrate";

//RECEIVE buyrate buyrateinitblock
// rateinitial, buyrate, buyrateinitblock, buyrateincrement, initblock, billingblock, connectcharge, disconnectcharge, stepchargea, chargea, timechargea, billingblocka, stepchargeb, chargeb, timechargeb, billingblockb, stepchargec, chargec, timechargec, billingblockc, startdate, stopdate, starttime, endtime
$FG_DEBUG = 0;

if (DB_TYPE == "mysql"){
	$sp = "`";
}

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";


$Temps1 = time();
//echo $Temps1;



//----------------------------------------------
//			Fonction pour l'upload file
//----------------------------------------------

	$registered_types = array(
                                        "application/x-gzip-compressed"         => ".tar.gz, .tgz",
                                        "application/x-zip-compressed"          => ".zip",
                                        "application/x-tar"                     => ".tar",
                                        "text/plain"                            => ".html, .php, .txt, .inc (etc)",
                                        "image/bmp"                             => ".bmp, .ico",
                                        "image/gif"                             => ".gif",
                                        "image/pjpeg"                           => ".jpg, .jpeg",
                                        "image/jpeg"                            => ".jpg, .jpeg",
                                        "image/png"                             => ".png",
                                        "application/x-shockwave-flash"         => ".swf",
                                        "application/msword"                    => ".doc",
                                        "application/vnd.ms-excel"              => ".xls",
                                        "application/octet-stream"              => ".exe, .fla (etc)"
                                        ); # these are only a few examples, you can find many more!

	$allowed_types = array("text/plain");


if ($FG_DEBUG == 1) echo "::::>> ".$the_file;

function validate_upload($the_file, $the_file_type) {

	global $allowed_types;

	$start_error = "\n<b>ERROR:</b>\n<ul>";

        if ($the_file == "none") {
                $error .= "\n<li>You did not upload anything!</li>";
        } else {
			//echo $the_file_type."<br>";
                if (!in_array($the_file_type,$allowed_types)) {
                        $error .= "\n<li>"."file type is not allowed"."\n<ul>";
                        while ($type = current($allowed_types)) {
                                $error .= "\n<li>" . $registered_types[$type] . " (" . $type . ")</li>";
                                next($allowed_types);
                        }
                        $error .= "\n</ul>";
                }
                if ($error) {
                        $error = $start_error . $error . "\n</ul>";
                        return $error;
                } else {
                        return false;
                }
        }
} # END validate_upload

//INUTILE
$my_max_file_size = (int) MY_MAX_FILE_SIZE_IMPORT;


if ($FG_DEBUG == 1) echo "<br> Task :: $task";

if ($task=='upload'){

	//---------------------------------------------------------
	//		 Effacer tout les fichiers du repertoire cache.
	//---------------------------------------------------------

	$the_file_name = $_FILES['the_file']['name'];
	$the_file_type = $_FILES['the_file']['type'];
	$the_file = $_FILES['the_file']['tmp_name'];
	
	
	if ($FG_DEBUG == 1) echo "<br> FILE  ::> ".$the_file_name;
	if ($FG_DEBUG == 1) echo "<br> THE_FILE:$the_file <br>THE_FILE_TYPE:$the_file_type";


	validate_upload($the_file,$the_file_type);				
	
        
	 $fp = fopen($the_file,  "r");  
	 if (!$fp){  /* THE FILE DOESN'T EXIST */ 
		 echo  'THE FILE DOESNOT EXIST';
		 exit();
	 }

	 $chaine1 = '"\'';

 	$nb_imported=0;
	$nb_to_import=0;
	$DBHandle  = DbConnect();

	while (!feof($fp)){

			 //if ($nb_imported==1000) break;
             $ligneoriginal = fgets($fp,4096);  /* On se déplace d'une ligne */
			 $ligneoriginal = trim ($ligneoriginal);
			 $ligneoriginal = strtolower($ligneoriginal);
             if($ligneoriginal == "")
             {
                 break;
             }

			 for ($i = 0; $i < strlen($chaine1); $i++)
					$ligne = str_replace($chaine1[$i], ' ', $ligneoriginal);

			 $ligne = str_replace(',', '.', $ligne);
			 $val= split(';', $ligne);
			 $val[0]=str_replace('"', '', $val[0]); //DH
			 $val[1]=str_replace('"', '', $val[1]); //DH
             $val[2]=str_replace('"', '', $val[2]); //DH
			 $val[0]=str_replace("'", '', $val[0]); //DH
			 $val[1]=str_replace("'", '', $val[1]); //DH
             $val[2]=str_replace("'", '', $val[2]); //DH

			 if ($status!="ok") break;
			 //if ($val[2]!='' && strlen($val[2])>0){
			 if (substr($ligne,0,1)!='#' && substr($ligne,0,2)!='"#'){

				 $FG_ADITION_SECOND_ADD_TABLE  = 'cc_did';
                 if (DB_TYPE == "postgres")
                 {
                     $FG_ADITION_SECOND_ADD_FIELDS = 'id_cc_didgroup, id_cc_country, did, fixrate'; //$fieldtoimport_sql
				     $FG_ADITION_SECOND_ADD_VALUE  = "'".$didgroupval[0]."', '".$countryIDval[0]."', '".$val[0]."', '".$val[1]."'";
                 }
                 else
                 {
                     $FG_ADITION_SECOND_ADD_FIELDS = 'id_cc_didgroup, id_cc_country, did, fixrate, creationdate'; //$fieldtoimport_sql
				     $FG_ADITION_SECOND_ADD_VALUE  = "'".$didgroupval[0]."', '".$countryIDval[0]."', '".$val[0]."', '".$val[1]."', now()";
                 }
				 for ($k=0;$k<count($fieldtoimport);$k++)
                 {

					if (!empty($val[$k+2]) || $val[$k+2]=='0')
					{
						$val[$k+2]=str_replace('"', '', $val[$k+2]); //DH
						$val[$k+2]=str_replace("'", '', $val[$k+2]); //DH

						if ($fieldtoimport[$k]=="startdate" && ($val[$k+2]=='0' || $val[$k+2]=='')) continue;
						if ($fieldtoimport[$k]=="stopdate" && ($val[$k+2]=='0' || $val[$k+2]=='')) continue;

						$FG_ADITION_SECOND_ADD_FIELDS .= ', '.$fieldtoimport[$k];

						if (is_numeric($val[$k+2])) {
							$FG_ADITION_SECOND_ADD_VALUE .= ", ".$val[$k+2]."";
						}else{
							$FG_ADITION_SECOND_ADD_VALUE .= ", '".$val[$k+2]."'";
						}

						if ($fieldtoimport[$k] == "startingdate") $find_startdate = 1;
                        if ($fieldtoimport[$k] == "expirationdate")  $find_expiredate = 1;

					}
				 }

                 $begin_date = date("Y");
                 $begin_date_plus = date("Y") + 25;
	             $end_date = date("-m-d H:i:s");
	             $comp_date = "'".$begin_date.$end_date."'";
                 $comp_date_plus = "'".$begin_date_plus.$end_date."'";


				 if ( $find_startdate !=1 ){
					$FG_ADITION_SECOND_ADD_FIELDS .= ', startingdate';
			 		$FG_ADITION_SECOND_ADD_VALUE .= ", '".$begin_date.$end_date."'";
				 }
                 if ( $find_expiredate !=1 ){
					$FG_ADITION_SECOND_ADD_FIELDS .= ', expirationdate';
			 		$FG_ADITION_SECOND_ADD_VALUE .= ", ".$comp_date_plus;
				 }
				 $TT_QUERY .= "INSERT INTO $sp".$FG_ADITION_SECOND_ADD_TABLE."$sp (".$FG_ADITION_SECOND_ADD_FIELDS.") values (".trim ($FG_ADITION_SECOND_ADD_VALUE).") ";

				 $nb_to_import++;
			}

			if ($TT_QUERY!='' && strlen($TT_QUERY)>0 && ($nb_to_import==1) ){

				$nb_to_import=0;
				$result_query =  $DBHandle -> query($TT_QUERY);

				if ($result_query){ $nb_imported = $nb_imported + 1;
				}else{$buffer_error.= $ligneoriginal.'<br/>';}
				$TT_QUERY='';
				
			}
			
			             
		} // END WHILE EOF
		
		
		if ($TT_QUERY!='' && strlen($TT_QUERY)>0 && ($nb_to_import>0) ){
				
				$result_query = @ $DBHandle -> query($TT_QUERY);								
				if ($result_query) $nb_imported = $nb_imported + $nb_to_import;				
		}		
	
	
}

$Temps2 = time();
$Temps = $Temps2 - $Temps1;
//echo "<br>".$Temps2;
//echo "<br>Script Time :".$Temps."<br>";



	 

?>

<?php
		$smarty->display('main.tpl');
?>
<style type="text/css">
<!--
div.myscroll {
	align: left;
	height: 100px;
	width: 600px;
	overflow: auto;
	border: 1px solid #ddd;
	background-color: #FFFFFF;
	padding: 5px;
}
-->
</style>

<script type="text/javascript">
<!--

function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function sendtoupload(form){
		
	
	if (form.the_file.value.length < 2){
		alert ('Please, you must first select a file !');
		form.the_file.focus ();
		return (false);
	}
	
    document.forms["myform"].elements["task"].value = "upload";	
	document.forms[0].submit();
}

//-->
</script>
     
	  <?php
	  if ($status=="ok"){
	  		echo $CC_help_import_did_confirm;
	  }else{
			echo $CC_help_import_did_analyse;
	  }
	  ?>
          
		<?php  if ($status!="ok"){?> 
		
		<center>As a preview for the import, we have made a quick analyze of the first line of your csv file.<br/>
		Please check out if everything look correct!</center>
		
		<table align=center border="0" cellpadding="2" cellspacing="2" width="300">
			<tbody>
                <tr class="form_head">                  					
                  <td class="tableBody" style="padding: 2px;" align="center" width="50%"> 
                    <strong> <span class="white_link">FIELD </span> </strong>
				  </td>
				  <td class="tableBody" style="padding: 2px;" align="center" width="50%"> 
                    <strong> <span class="white_link">VALUE </span> </strong>
				  </td>
                </tr>
				<tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[1]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[1]?>'">
				 <td class="tableBody" align="left" valign="top"><font class="fontstyle_005" ><?php echo strtoupper($fixfield[0])?></font></td>
				 <td class="tableBody" align="center" valign="top"><font class="fontstyle_005" ><?php echo $didgroupval[1]?> (<?php echo $didgroupval[0]?>)</font></td>
				</tr>
                <tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[2]?>'">
				 <td class="tableBody" align="left" valign="top"><font class="fontstyle_005" ><?php echo strtoupper($fixfield[1])?></b></font></td>
				 <td class="tableBody" align="center" valign="top"><font class="fontstyle_005" ><?php echo $countryIDval[1]?> (<?php echo $countryIDval[0]?>)</font></td>
				</tr>
				<?php  for ($i=0;$i<count($field);$i++){ ?>
               	<tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i+1)%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i+1)%2]?>'">
				 <td class="tableBody" align="left" valign="top"><b><?php echo strtoupper($field[$i])?></b></td>
				 <td class="tableBody" align="center" valign="top"><?php echo $val[$i]?></td>
				</tr>
				<?php  } ?>
				<?php  for ($i=0;$i<count($fieldtoimport);$i++){ ?>
               	<tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i)%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i)%2]?>'">  
				 <td class="tableBody" align="left" valign="top"><b><?php echo strtoupper($fieldtoimport[$i])?></b></td>
				 <td class="tableBody" align="center" valign="top"><?php echo $val[$i+2]?></td>
				</tr>
				<?php  } ?>
				
			</tbody>
		</table>
						                  		 

			
			
<br></br>
		<table width="95%" border="0" cellspacing="2" align="center" class="records">
			
              <form name="myform" enctype="multipart/form-data" action="A2B_entity_did_import_analyse.php" method="post" >
                <INPUT type="hidden" name="didgroup" value="<?php echo $didgroup?>">
                <INPUT type="hidden" name="countryID" value="<?php echo $countryID?>">
				<INPUT type="hidden" name="search_sources" value="<?php echo $search_sources?>">

                <tr> 
                  <td colspan="2"> 
                    <div align="center"><span class="textcomment"> 
                       Please check if the datas above are correct. <br><b>If Yes</b>, you can continue the import.
					  Otherwise you must fix your csv file!
                      </span></div>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <p align="center">
                      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $my_max_file_size?>">
                      <input type="hidden" name="task" value="upload">
					  <input type="hidden" name="status" value="ok">
                      <input name="the_file" type="file" size="50" onFocus=this.select() class="saisie1">
                      <input type="button"    value="Continue to Import the DID's" onFocus=this.select() class="form_input_button" name="submit1" onClick="sendtoupload(this.form);">
                      <br>
                      &nbsp; </p>
                  </td>
                </tr>

                <tr>
                  <td class="bgcolor_014" colspan="2"><b>
                    <?php echo $translate[P34_9]?>
                    </b></td>
                </tr>

              </form>
            </table>
			
			<?php }else{ ?>
			
			</br>
			<table width="75%" border="0" cellspacing="2" align="center" class="records">
			
				<TR> 
				  	<TD style="border-bottom: medium dotted #ED2525" align="center">&nbsp;</TD>
				</TR>
                <tr> 
				  <td colspan="2" style="padding-left: 5px; padding-right: 3px;" align=center class="bgcolor_015">
                    <div align="center"><span class="textcomment"> 
                       
					  <br>
					  The import of the new DID's have been realized with success!<br>
					  <?php echo $nb_imported?> new DID's have been imported into your Database.
                      </span></div>
					  <br><br>
					  

					  <?php  if (!empty($buffer_error)){ ?>
					  <center>
					  	 <b><i>Line that has not been inserted!</i></b>
						 <div class="myscroll">
							  <span class="fontstyle_005">
							  <?php echo $buffer_error?> 
							  </span>  
						 </div>
						</center>
						<br>
					 <?php  } ?>
					 
                  </td>
                </tr>
			</table>
			
			<?php }?>
			<br>
<?php
	$smarty->display('footer.tpl');

?>
