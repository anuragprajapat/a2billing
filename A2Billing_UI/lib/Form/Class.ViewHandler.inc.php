<?php
 
// Hack to allow PHP4 use stripos() that is only supported in PHP5
if (!function_exists("stripos")) {
	function stripos($str,$needle) {
		return strpos(strtolower($str),strtolower($needle));
	}
}



// ******************** END IF $topviewer *******************************

// $stitle = $_GET['stitle'];
// $ratesort = $_GET['ratesort'];
// $current_page = $_GET['current_page'];
// 	if (isset($_GET['order']) && ($_GET['order'] != ''))
// 		$this->FG_ORDER = $_GET['order']; // really need ?!
// 	if (isset($_GET['sens']) && ($_GET['sens'] != ''))
// 		$this->FG_SENS = $_GET['sens']; // really need  ?
// 	$this->Add_FormParams(putpost_arr('stitle','ratesort','current_page','order','sens'));


if ((count($list)>0) && is_array($list)){
	$ligne_number=0;
?>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
	window.open(theURL,winName,features);
}

function openURLFilter(theLINK)
{
	selInd = document.theFormFilter.choose_list.selectedIndex;
	if(selInd==0){return false;}
	goURL = document.theFormFilter.choose_list.options[selInd].value;      
	this.location.href = theLINK + goURL;
}

//-->
</script>
  
	  
	  
      <table width="<?php echo $this->FG_VIEW_TABLE_WITDH; ?>" border="0" align="center" cellpadding="0" cellspacing="0">
	  <?php  IF ($this -> CV_DISPLAY_LINE_TITLE_ABOVE_TABLE){ ?>
		<TR> 
		  <TD height="20" bgcolor="#52BEDE" style="padding-left: 5px; padding-right: 3px;"><span class="text_blanconegrita">
          	  <b><?php echo $this -> CV_TEXT_TITLE_ABOVE_TABLE?></b></span>
		  </TD>
        </TR>
	   <?php  } //END IF ?>
	   <?php  IF ($this -> CV_DISPLAY_FILTER_ABOVE_TABLE){ ?>
	   <TR><FORM NAME="theFormFilter">
	   		<input type="hidden" name="popup_select" value="<?php echo $_GET['popup_select']?>">
			<input type="hidden" name="popup_formname" value="<?php echo $_GET['popup_formname']?>">
			<input type="hidden" name="popup_fieldname" value="<?php echo $_GET['popup_fieldname']?>">
            <TD height="25" bgcolor="#52BEDE" style="padding-left: 5px; padding-right: 3px;"><span class="text_blanconegrita">			   		
				<SELECT name="choose_list" size="1" class="form_input_select" style="width: 185px;" onchange="openURLFilter('<?php echo $_SERVER['PHP_SELF'].$this->CV_FILTER_ABOVE_TABLE_PARAM?>')">
				
					<OPTION><?php echo gettext("Sort");?></OPTION>
				
					<?php	
						// TODO not sure for what should be used that, because exist already a filter.	
						if (!isset($list_site)) $list_site = $list;
						foreach ($list_site as $recordset){ 						 
					?>
					<OPTION class=input value='<?php echo $recordset[0]?>'  <?php if ($recordset[0]==$site_id) echo "selected";?>><?php echo $recordset[1]?></OPTION>
					<?php 	 }
					?>
				</SELECT>
			  </SPAN></TD></FORM>	
        </TR>
		<?php  } //END IF ?>
		
		<tr> 
          <td style="padding-left: 5px; padding-right: 3px;" height="16"> 
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr> 
                  <td><span style="color: rgb(120, 120, 120); font-size: 9px;"><b> - <?php echo strtoupper($this->FG_INSTANCE_NAME) ?> LIST - </b></span></td>
                  <td align="right"> <span class="viewhandler_span1"> <?php echo $this -> FG_NB_RECORD.' '.gettext("Records"); ?></span></td>
                </tr>
            </table></td>
        </tr>
		
		<?php
		// Add filter  FG_FILTER_APPLY , FG_FILTERFIELD and FG_FILTER_FORM_ACTION
		if ($this -> FG_FILTER_APPLY || $this -> FG_FILTER_APPLY2){
		?>
		<tr><FORM NAME="theFormFilter" action="<?php echo $_SERVER['PHP_SELF']?>">	
			<input type="hidden" name="atmenu" value="<?php echo $_GET['atmenu']?>">
			<input type="hidden" name="popup_select" value="<?php echo $_GET['popup_select']?>">
			<input type="hidden" name="popup_formname" value="<?php echo $_GET['popup_formname']?>">
			<input type="hidden" name="popup_fieldname" value="<?php echo $_GET['popup_fieldname']?>">
			
			<INPUT type="hidden" name="form_action"	value="<?php echo $this->FG_FILTER_FORM_ACTION ?>">
            <td height="31" bgcolor="#8888CC" style="padding-left: 5px; padding-right: 3px;">
			<span class="text_blanconegrita">		
			<?php if ($this -> FG_FILTER_APPLY){ ?>
				   		
				<font color="white"><b><?php echo gettext("FILTER ON ");?> <?php echo strtoupper($this->FG_FILTERFIELDNAME)?> :</b></font>
				<INPUT type="text" name="filterprefix" value="">
				
				<INPUT type="hidden" name="filterfield"	value="<?php echo $this->FG_FILTERFIELD?>">
				<?php
				if ($this -> FG_FILTERTYPE == 'INPUT'){
					// IT S OK 
				}elseif ($this -> FG_FILTERTYPE == 'POPUPVALUE'){
				?>
					<a href="#" onclick="window.open('<?php echo $this->FG_FILTERPOPUP[0]?>popup_formname=theFormFilter&popup_fieldname=filterprefix' <?php echo $this->FG_FILTERPOPUP[1]?>);"><img src="../Images/icon_arrow_orange.png"/></a>
				<?php
				}

			}
			
			if ($this -> FG_FILTER_APPLY2){ ?>
				&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;&nbsp; 
				<font color="white"><b><?php echo gettext("FILTER ON");?><?php echo strtoupper($this->FG_FILTERFIELDNAME2)?> :</b></font>
				<INPUT type="text" name="filterprefix2" value="">				
				<INPUT type="hidden" name="filterfield2"	value="<?php echo $this->FG_FILTERFIELD2?>">
				<?php
				if ($this -> FG_FILTERTYPE2 == 'INPUT'){
					// IT S OK 
				}elseif ($this -> FG_FILTERTYPE2 == 'POPUPVALUE'){
				?>
					<a href="#" onclick="window.open('<?php echo $this->FG_FILTERPOPUP2[0]?>popup_formname=theFormFilter&popup_fieldname=filterprefix2' <?php echo $this->FG_FILTERPOPUP2[1]?>);"><img src="../Images/icon_arrow_orange.png"/></a>
				<?php
				}
			} 
			?>
				<input type="SUBMIT" value="<?php echo gettext("APPLY FILTER ");?>" style="border: 2px outset rgb(204, 51, 0);" class="form_enter"/>
			</span>
			</td></FORM>
        </tr>
		<?php } ?>
		
        <TR> 
          <TD> 

			<TABLE border="0" cellPadding="2" cellSpacing="2" width="100%">
				<TR class="form_head"> 
				<?php 
					  for($i=0;$i<$this->FG_NB_TABLE_COL;$i++){ 
				?>				
				 <td class="tableBody" style="padding: 2px;" align="center" width="<?php echo $this->FG_TABLE_COL[$i][2]?>" >
						<strong> 
						<?php  if (strtoupper($this->FG_TABLE_COL[$i][4])=="SORT"){
							if ($this->FG_ORDER == $this->FG_TABLE_COL[$i][1]) {
								if ($this->FG_SENS=="ASC") $next_sens= "DESC"; else $next_sens = "ASC";
							}else $next_sens = $this->FG_SENS;
						?>
						<a href="<?= $_SERVER['PHP_SELF']. $this->gen_GetParams(
							array(order => $this->FG_TABLE_COL[$i][1], sens => $next_sens)) ?>" >
						<font color="#FFFFFF"><?php  } ?>
						<?php echo $this->FG_TABLE_COL[$i][0]?> 
						<?php if ($this->FG_ORDER==$this->FG_TABLE_COL[$i][1] && $this->FG_SENS=="ASC"){?>
						&nbsp;<img src="../Images/icon_up_12x12.png" border="0">
						<?php }elseif ($this->FG_ORDER==$this->FG_TABLE_COL[$i][1] && $this->FG_SENS=="DESC"){?>
						&nbsp;<img src="../Images/icon_down_12x12.png" border="0">
						<?php }?>
						<?php  if (strtoupper($this->FG_TABLE_COL[$i][4])=="SORT"){?>
						</font></a> 
						<?php }?>
						</strong></TD>
			   <?php } 		
				 if ($this->FG_DELETION || $this->FG_EDITION || $this -> FG_OTHER_BUTTON1 || $this -> FG_OTHER_BUTTON2){ ?>
					 <td width="<?php echo $this->FG_ACTION_SIZE_COLUMN?>" align="center" class="tableBody" ><strong> <?php echo gettext("ACTION");?></strong> </td>
			   <?php } ?>		
                </TR>
		<?php			
			 /**********************   START BUILDING THE TABLE WITH BROWSING VALUES ************************/
			 for ($ligne_number=0;$ligne_number<count($list);$ligne_number++){

		?>

               	 <TR bgcolor="<?php echo $this->FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>"  onmouseover="bgColor='#FFDEA6'" onMouseOut="bgColor='<?php echo $this->FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>'"> 
		  		<?php 
				$k=0;
				for($i=0;$i<$this->FG_NB_TABLE_COL;$i++){ 
					/**********************   select the mode to browse define the column value : lie, list, value, eval.... ************************/
					if ($this->FG_TABLE_COL[$i][6]=="lie"){
						$instance_sub_table = new Table($this->FG_TABLE_COL[$i][7], $this->FG_TABLE_COL[$i][8]);
						if ($this->FG_DEBUG>3) {
							 $instance_sub_table->debug_st=1;
							 //echo "i=" . $i . ", k=". $k ."<br>";
							 }
						$sub_clause = str_replace("%id", $list[$ligne_number][$i-$k], $this->FG_TABLE_COL[$i][9]);																																	
						$select_list = $instance_sub_table -> Get_list ($this->DBHandle, $sub_clause, null, null, null, null, null, null);
						$field_list_sun = split(',',$this->FG_TABLE_COL[$i][8]);
						$record_display = $this->FG_TABLE_COL[$i][10];
								
						$record_display=str_params($record_display,$select_list[0],1);
						/*for ($l=1;$l<=count($field_list_sun);$l++){													
							$record_display = str_replace("%$l", $select_list[0][$l-1], $record_display);
						}*/
						
					}elseif ($this->FG_TABLE_COL[$i][6]=="eval"){
						$string_to_eval = $this->FG_TABLE_COL[$i][7]; // %4-%3
						$string_to_eval = str_params($string_to_eval,$list[$ligne_number]);
						eval("\$eval_res = $string_to_eval;");
						$record_display = $eval_res;
						//$record_display = "\$eval_res = $string_to_eval";
						
						
					}elseif ($this->FG_TABLE_COL[$i][6]=="list"){
						$select_list = $this->FG_TABLE_COL[$i][7];
						$record_display = $select_list[$list[$ligne_number][$i-$k]][0];
					}elseif ($this->FG_TABLE_COL[$i][6]=="value"){
						$record_display = $this->FG_TABLE_COL[$i][7];
						$k++;
					}elseif ($this->FG_TABLE_COL[$i][6]=="object"){
						$record_display = $this->FG_TABLE_COL[$i][7]->disp($list[$ligne_number],$i);
					}else{
						$record_display = $list[$ligne_number][$i-$k];
					}
					
					/**********************   IF LENGHT OF THE VALUE IS TOO LONG IT MIGHT BE CUT ************************/
					if ( is_numeric($this->FG_TABLE_COL[$i][5]) && (strlen($record_display) > $this->FG_TABLE_COL[$i][5])  ){
						$record_display = substr($record_display, 0, $this->FG_TABLE_COL[$i][5])."";
					}
					/*

					if (isset ($this -> FG_TABLE_COL[$i][10]) && strlen($this -> FG_TABLE_COL[$i][10])>1){
						call_user_func($this -> FG_TABLE_COL[$i][10], $record_display);
					}else{
						echo stripslashes($record_display);
					}
					*/
	 ?>

					<TD vAlign="top" align="<?php echo $this->FG_TABLE_COL[$i][3]?>" class="tableBody"><?php
						$origlist[$ligne_number][$i-$k] = $list[$ligne_number][$i-$k];
						$list[$ligne_number][$i-$k] = $record_display;

						if (isset ($this->FG_TABLE_COL[$i][11]) && strlen($this->FG_TABLE_COL[$i][11])>1){
							call_user_func($this->FG_TABLE_COL[$i][11], $record_display);
						}else{
							echo stripslashes($record_display);
						}
						?>
					</TD>

		 		 <?php  } ?>

				  	<?php if($this->FG_EDITION || $this->FG_DELETION || $this -> FG_OTHER_BUTTON1 || $this -> FG_OTHER_BUTTON2){?>
					  <TD align="center" vAlign=top class=tableBodyRight><?php
					   if($this->FG_EDITION){
						?>&nbsp; <a href="<?php echo $this->FG_EDITION_LINK?><?php echo $list[$ligne_number][$this->FG_NB_TABLE_COL]?>&stitle=<?php echo $stitle?>&site_id=<?php echo $list[$ligne_number][0]?>"><img src="../Images/icon-edit.png" border="0" title="<?php echo $this->FG_EDIT_ALT?>" alt="<?php echo $this->FG_EDIT_ALT?>"></a><?php } ?>
					<?php if($this->FG_DELETION){
						?>&nbsp; <a href="<?php echo $this->FG_DELETION_LINK?><?php echo $list[$ligne_number][$this->FG_NB_TABLE_COL]?>&stitle=<?php echo $stitle?>"><img src="../Images/icon-del.png" border="0" title="<?php echo $this->FG_DELETE_ALT?>" alt="<?php echo $this->FG_DELETE_ALT?>"></a><?php } ?>
					<?php if($this->FG_OTHER_BUTTON1){ 
					?> <a href="<?php
								$new_FG_OTHER_BUTTON1_LINK = $this -> FG_OTHER_BUTTON1_LINK;
								// we should depreciate |param| and only use |col|
								if (strpos($this -> FG_OTHER_BUTTON1_LINK,"|param|")){
									$new_FG_OTHER_BUTTON1_LINK = str_replace("|param|",$list[$ligne_number][$this->FG_NB_TABLE_COL],$this -> FG_OTHER_BUTTON1_LINK);
									// SHOULD DO SMTH BETTER WITH paramx and get the x number to find the value to use
								}
								if (strpos($this -> FG_OTHER_BUTTON1_LINK,"|param1|")){
									$new_FG_OTHER_BUTTON1_LINK = str_replace("|param1|",$list[$ligne_number][$this->FG_NB_TABLE_COL-1],$this -> FG_OTHER_BUTTON1_LINK);
								}
								
								// REPLACE |colX|  where is a numero of the column by the column value
								if (eregi ('col[0-9]', $new_FG_OTHER_BUTTON1_LINK)) {
									for ($h=0;$h<=$this->FG_NB_TABLE_COL;$h++){
										$findme = "|col$h|";
										$pos = stripos($new_FG_OTHER_BUTTON1_LINK, $findme);
										if ($pos !== false) {
											$new_FG_OTHER_BUTTON1_LINK = str_replace($findme,$list[$ligne_number][$h],$new_FG_OTHER_BUTTON1_LINK);
										}
									}
								}
								
								// REPLACE |col_origX|  where is a numero of the column by the column value
								if (eregi ('col_orig[0-9]', $new_FG_OTHER_BUTTON1_LINK)) {
									for ($h=0;$h<=$this->FG_NB_TABLE_COL;$h++){
										$findme = "|col_orig$h|";
										$pos = stripos($new_FG_OTHER_BUTTON1_LINK, $findme);
										if ($pos !== false) {
											$new_FG_OTHER_BUTTON1_LINK = str_replace($findme,$origlist[$ligne_number][$h],$new_FG_OTHER_BUTTON1_LINK);
										}
									}
								}
								
								echo $new_FG_OTHER_BUTTON1_LINK;
								if (substr($new_FG_OTHER_BUTTON1_LINK,-1)=='=') echo $list[$ligne_number][$this->FG_NB_TABLE_COL];
								if (strlen($this -> FG_OTHER_BUTTON1_IMG)==0){
									echo '"> '.'<span class="cssbutton">'.$this->FG_OTHER_BUTTON1_ALT.'</span>';
									// onclick="location.href='http://www.google.com'"
								}else{
									?>"><img src="<?php echo $this -> FG_OTHER_BUTTON1_IMG?>" border="0" title="<?php echo $this->FG_OTHER_BUTTON1_ALT?>" alt="<?php echo $this->FG_OTHER_BUTTON1_ALT?>"><?
								}
								?></a>
						<?php } ?>
						<?php if($this->FG_OTHER_BUTTON2){ ?>
							<a href="<?php 
								$new_FG_OTHER_BUTTON2_LINK = $this -> FG_OTHER_BUTTON2_LINK;
								if (strpos($this -> FG_OTHER_BUTTON2_LINK,"|param|")){
									$new_FG_OTHER_BUTTON2_LINK = str_replace("|param|",$list[$ligne_number][$this->FG_NB_TABLE_COL],$this -> FG_OTHER_BUTTON2_LINK);
									// SHOULD DO SMTH BETTER WITH paramx and get the x number to find the value to use
								}
								if (strpos($this -> FG_OTHER_BUTTON2_LINK,"|param1|")){
									$new_FG_OTHER_BUTTON2_LINK = str_replace("|param1|",$list[$ligne_number][$this->FG_NB_TABLE_COL-1],$this -> FG_OTHER_BUTTON2_LINK);
								}
								
								// REPLACE |colX|  where is a numero of the column by the column value
								if (eregi ('col[0-9]', $new_FG_OTHER_BUTTON2_LINK)) {
									for ($h=0;$h<=$this->FG_NB_TABLE_COL;$h++){
										$findme = "|col$h|";
										$pos = stripos($new_FG_OTHER_BUTTON2_LINK, $findme);
										if ($pos !== false) {
											$new_FG_OTHER_BUTTON2_LINK = str_replace($findme,$list[$ligne_number][$h],$new_FG_OTHER_BUTTON2_LINK);
										}
									}
								}
								
								// REPLACE |col_origX|  where is a numero of the column by the column value
								if (eregi ('col_orig[0-9]', $new_FG_OTHER_BUTTON2_LINK)) {
									for ($h=0;$h<=$this->FG_NB_TABLE_COL;$h++){
										$findme = "|col_orig$h|";
										$pos = stripos($new_FG_OTHER_BUTTON2_LINK, $findme);
										if ($pos !== false) {
											$new_FG_OTHER_BUTTON2_LINK = str_replace($findme,$origlist[$ligne_number][$h],$new_FG_OTHER_BUTTON2_LINK);
										}
									}
								}
								echo $new_FG_OTHER_BUTTON2_LINK;
								if (substr($new_FG_OTHER_BUTTON2_LINK,-1)=='=') echo $list[$ligne_number][$this->FG_NB_TABLE_COL];
								if (strlen($this -> FG_OTHER_BUTTON2_IMG)==0){
									echo '"> '.'<span class="cssbutton">'.$this->FG_OTHER_BUTTON2_ALT.'</span>';
								}else{
									?>"><img src="<?php echo $this -> FG_OTHER_BUTTON2_IMG?>" border="0" title="<?php echo $this->FG_OTHER_BUTTON2_ALT?>" alt="<?php echo $this->FG_OTHER_BUTTON2_ALT?>"><?

								}
								?></a>
						<?php } ?>

					  </TD>
					<?php  } ?>

					</TR>
				<?php
					} //  for (ligne_number=0;ligne_number<count($list);$ligne_number++)
					while ($ligne_number < 7){
				?>
					<TR bgcolor="<?php echo $this->FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]?>">
				  		<?php
							$REMOVE_COL = ($this->FG_OTHER_BUTTON1 || $this->FG_OTHER_BUTTON2 || $this->FG_EDITION || $this->FG_DELETION )? 0 : 1;
							for($i=0;$i<$this->FG_NB_TABLE_COL-$REMOVE_COL;$i++){
				 		 ?>
                 		 <TD vAlign=top class=tableBody>&nbsp;</TD>
				 		 <?php  } ?>
                 		 <TD align="center" vAlign=top class=tableBodyRight>&nbsp;</TD>
					</TR>

				<?php
						$ligne_number++;
					} //END_WHILE
				 ?>
                <TR>
                  <TD class=tableDivider colSpan=<?php echo $this->FG_TOTAL_TABLE_COL?>><IMG height=1 src="../Images/clear.png" width=1></TD>
                </TR>
            </TABLE>

		  </TD>
        </TR>
         <TR >
          <TD height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px">
			<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                <TR>
                  <TD align="right" valign="bottom"><span class="text"><font color="#cc0000">
			<?php
			$this -> printPages($this -> CV_CURRENT_PAGE+1, $this -> FG_NB_RECORD_MAX, $_SERVER['PHP_SELF']) ;
			?>
			</span>
                  </TD>
            </TABLE></TD>
        </TR>
		
		<FORM name="otherForm2" action="<?php echo $_SERVER['PHP_SELF']?>">
		<tr><td>
			<?php echo gettext("DISPLAY");?>
			<?= $this->gen_PostParams(array(current_page => 0)); ?>
			
			<select name="mydisplaylimit" size="1" class="form_input_select">
				<option value="10" selected>10</option>
				<option value="50">50</option>
				<option value="100">100</option>
				<option value="ALL">All</option>
			</select>
			<input class="form_input_button"  value=" <?php echo gettext("GO");?> " type="SUBMIT">
			&nbsp; &nbsp; &nbsp;
			
		<?php if ($this->FG_EXPORT_CSV){ ?>
		 - &nbsp; &nbsp; <a href="export_csv.php?var_export=<?php echo $this->FG_EXPORT_SESSION_VAR ?>&var_export_type=type_csv" target="_blank" ><img src="../Images/spread.png" border="0" height="30"/><?php echo gettext("Export CSV");?></a>

		<?php  	} 	?>
        <?php if ($this->FG_EXPORT_XML){ ?>
		 - &nbsp; &nbsp; <a href="export_csv.php?var_export=<?php echo $this->FG_EXPORT_SESSION_VAR ?>&var_export_type=type_xml" target="_blank" ><img src="../Images/icons_xml.png" border="0" height="32"/><?php echo gettext("Export XML");?></a>

		<?php  	}?>

		</td></tr>
		</FORM>
      </table>
<?php
	}else{
?>
	<br><br>
	<table width="50%" border="0" align="center">
		<tr>
			<td align="center">
				<?php echo $this -> CV_NO_FIELDS;?><br>
			</td>
		</tr>
	</table>
	<br><br>
<?php
	}//end_if
?>
