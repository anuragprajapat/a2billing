
<script language="JavaScript" src="./javascript/calendar3.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function sendto(action, record, field_inst, instance){
  document.myForm.form_action.value = action;
  document.myForm.sub_action.value = record;
  if (field_inst != null) document.myForm.elements[field_inst].value = instance;
  myForm.submit();
}

function sendtolittle(direction){
  myForm.action=direction;
  myForm.submit();

}

//-->
</script>


<table class="editform_table1" cellspacing="2">
			
	<FORM action=<?= $_SERVER['PHP_SELF']?> method=post name="myForm" id="myForm">
		<?php $this->gen_PostParams(array( form_action => 'edit', sub_action => '',
			$this->FG_TABLE_ID => $this->FG_TABLE_ID)); ?>
<?php
	if (!is_null($this->FG_QUERY_EDITION_HIDDEN_FIELDS) && $this->FG_QUERY_EDITION_HIDDEN_FIELDS!=""){
		$split_hidden_fields = split(",",trim($this->FG_QUERY_EDITION_HIDDEN_FIELDS));
		$split_hidden_fields_value = split(",",trim($this->FG_QUERY_EDITION_HIDDEN_VALUE));
		
		for ($cur_hidden=0;$cur_hidden<count($split_hidden_fields);$cur_hidden++){
			echo "<INPUT type=\"hidden\" name=\"".trim($split_hidden_fields[$cur_hidden])."\" value=\"".trim($split_hidden_fields_value[$cur_hidden])."\">\n";
		}
	}
?> 
            <TBODY>
<?php 
	for($i=0;$i<$this->FG_NB_TABLE_EDITION;$i++){ 
		$pos = strpos($this->FG_TABLE_EDITION[$i][14], ":"); // SQL CUSTOM QUERY		
		if (strlen($this->FG_TABLE_EDITION[$i][16])>1){
			echo '<TR><TD width="%25" valign="top" bgcolor="#FEFEEE" colspan="2" class="tableBodyRight" ><i>';
			echo $this->FG_TABLE_EDITION[$i][16];
			echo '</i></TD></TR>';
		}
		
		if (!$pos){			
?>
                    <TR> 		
		<?php if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){ ?>
			<TD width="%25" valign="middle" class="form_head_red"> 		<?= $this->FG_TABLE_EDITION[$i][0]?> 		</TD>  
		  	<TD width="%75" valign="top" class="tableBodyRight" background="../Images/background_cells_red.png" class="text">
        <?php }else{ ?>
			<TD width="%25" valign="middle" class="form_head"> 		<?= $this->FG_TABLE_EDITION[$i][0]?> 		</TD>  
			<TD width="%75" valign="top" class="tableBodyRight" background="../Images/background_cells.png" class="text">
		<?php } ?>
                        <?php 
			if ($this->FG_DEBUG >= 1) print($this->FG_TABLE_EDITION[$i][3]);
				if(($this->FG_DISPLAY_SELECT == true) && (strlen($this->FG_SELECT_FIELDNAME)>0) && (strlen($list[0][$this->FG_SELECT_FIELDNAME])>0) && ($this->FG_CONF_VALUE_FIELDNAME == $this->FG_TABLE_EDITION[$i][1]))
				{
				$valuelist = explode(",", $list[0][$this->FG_SELECT_FIELDNAME]);
				
				?>
					<SELECT name='<?php echo $this->FG_TABLE_EDITION[$i][1]?>' class="form_input_select">
					<?php 
					foreach($valuelist as $listval)
					{
					?>
					<option value="<?php echo $listval;?>" <?php  if($listval == $list[0][$i]) echo " selected";?>><?php echo $listval;?></option>
					<?php }?>
					</select>
				<?
				}
		  		elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("INPUT"))
				{								
					if (isset ($this->FG_TABLE_EDITION[$i][15]) && strlen($this->FG_TABLE_EDITION[$i][15])>1){				
						$list[0][$i] = call_user_func($this->FG_TABLE_EDITION[$i][15], $list[0][$i]);
					}			
			  ?>
                        <INPUT 	
						class="form_input_text" 
						 <?php if(substr_count($this->FG_TABLE_EDITION[$i][4], "readonly") > 0){?>
						 style="background-color: #CCCCCC;" 
						 <?php }?> 
						name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]];  }?>"> 
                        <?php 
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPVALUE")){
			?>
				<INPUT class="form_enter" name=<?= $this->FG_TABLE_EDITION[$i][1]?>  <?= $this->FG_TABLE_EDITION[$i][4]?> value="<?
					if($this->VALID_SQL_REG_EXP){ 
						echo stripslashes($list[0][$i]);
					}else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
                                	<a href="#" onclick="window.open('<?= $this->FG_TABLE_EDITION[$i][12]?>popup_formname=myForm&popup_fieldname=<?= $this->FG_TABLE_EDITION[$i][1]?>' <?= $this->FG_TABLE_EDITION[$i][7]?>);"><img src="../Images/icon_arrow_orange.png"/></a>
			 <?php
				}elseif (strtoupper ($this -> FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPVALUETIME"))
				{
                        ?>
                        <INPUT class="form_enter" name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
                         <a href="#" onclick="window.open('<?= $this->FG_TABLE_EDITION[$i][14]?>formname=myForm&fieldname=<?= $this->FG_TABLE_EDITION[$i][1]?>' <?= $this->FG_TABLE_EDITION[$i][14]?>);"><img src="../Images/icon_arrow_orange.png"/></a>
                        <?php
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("POPUPDATETIME"))
				{
                        ?>
                         <INPUT class="form_enter" name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
                          <a href="javascript:cal<?= $this->FG_TABLE_EDITION[$i][1]?>.popup();"><img src="img/cal.gif" width="16" height="16" border="0" title="Click Here to Pick up the date" alt="Click Here to Pick up the date"></a>
                          <script language="JavaScript">
                         <!-- // create calendar object(s) just after form tag closed
                             // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
                             // note: you can have as many calendar objects as you need for your application
                          var cal<?= $this->FG_TABLE_EDITION[$i][1]?> = new calendar3(document.forms['myForm'].elements['<?= $this->FG_TABLE_EDITION[$i][1]?>']);
                          cal<?= $this->FG_TABLE_EDITION[$i][1]?>.year_scroll = false;
                          cal<?= $this->FG_TABLE_EDITION[$i][1]?>.time_comp = true;
                          cal<?= $this->FG_TABLE_EDITION[$i][1]?>.formatpgsql = true;
                          //-->
                          </script>
			<?php	
		  		}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("TEXTAREA"))
				{
			  ?>
                     <textarea class="form_input_textarea" 
					 <?php if(substr_count($this->FG_TABLE_EDITION[$i][4], "readonly") > 0){?>
						 style="background-color: #CCCCCC;" 
						 <?php }?> 
					 name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?>><?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]];  }?></textarea> 
				<?php	
		  		}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SPAN"))
				{
			  ?>
                     <span class="form_input_span" name=<?php echo $this->FG_TABLE_EDITION[$i][1]?>  <?php echo $this->FG_TABLE_EDITION[$i][4]?>><?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]];  }?></span> 	 
                        <?php 	
				}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SELECT"))
				{
					
					if ($this->FG_DEBUG >= 1)
						echo "<br> TYPE OF SELECT :".$this->FG_TABLE_EDITION[$i][7]."<br>";
					$tmp_value=NULL;
					if (strtoupper ($this->FG_TABLE_EDITION[$i][7])==strtoupper ("SQL")){
						$instance_sub_table = new Table($this->FG_TABLE_EDITION[$i][8], $this->FG_TABLE_EDITION[$i][9]);
						if ($this-> FG_DEBUG >=2) 
							$instance_sub_table->debug_st=1;
						$select_list = $instance_sub_table -> Get_list ($this->DBHandle, $this->FG_TABLE_EDITION[$i][10], null, null, null, null, null, null);
						if ($this->FG_DEBUG >= 3) { 
							echo "<br> sql_select_list:";
							print_r($select_list);
						}
					}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][7])==strtoupper ("LIST"))
					{
						$select_list = $this->FG_TABLE_EDITION[$i][11];
						if ($this->FG_DEBUG >= 3) {
							echo "<br>select-list:"; print_r($select_list);
						}
					}
					$tmp_multiple=false;
					$tmp_value=$list[0][$i];
					if(strpos($this->FG_TABLE_EDITION[$i][4], "label-first")!==false){
						// array is ('label','id') instead of (id,label)
						$tmp2 = array();
						foreach($select_list as $tmp)
							$tmp2[]=array($tmp[1],$tmp[0]);
						$select_list = $tmp2;
					}
					if (isset($this->FG_TABLE_EDITION[$i][15]))
						array_unshift($select_list,$this->FG_TABLE_EDITION[$i][15]);
					
								<OPTION  value='<?php echo $select_recordset[1]?>' <?php 
					
					if(strpos($this->FG_TABLE_EDITION[$i][4], "multiple")!==false){
						$tmp_multiple=true;
						if ($this->FG_DEBUG >= 3)
							echo "Multiple<br>\n";
						if (strpos($this->FG_TABLE_EDITION[$i][4], "bitfield")!==false){
							//decode bitfield into values
							$tmp_int = (integer)$tmp_value;
							$tmp_value= array();
							$tmp_i = 1;
							for($tmp_i=1;($tmp_i!=0) && ($tmp_int!=0);$tmp_i*=2){
								if ($tmp_int & $tmp_i){
									$tmp_value[] = $tmp_i;
									$tmp_int -= $tmp_i;
								}
							}
						}elseif (strpos($this->FG_TABLE_EDITION[$i][4], "sql")!==false) {
							// decode SQL list into values
							$tmp_value=sql_decodeArray($tmp_value);
							
						} // else how to decode this?
					}
					if ($this->FG_TABLE_EDITION[$i][12] != ""){
						// replace expression into Option display
						foreach($select_list as $tmp_disp)
							$tmp_disp[1]=str_params($this->FG_TABLE_EDITION[$i][12],$tmp_disp,1);
					}
					
					if ($this->FG_DEBUG >= 3) {
						echo "list: ";
						print_r ($list);
						echo "<br>\n";
					}
					if ($this->FG_DEBUG >= 2){ ?>
						<br>
						#<?= $i?> <br> 
						SQL-REGEXP: <?= $this->VALID_SQL_REG_EXP ?><br>
						list[0]: <?= $list[0][$i] ?><br>
						fieldname: <?= $this->FG_TABLE_ADITION[$i][1] ?> <br>
						tmp_value: <?php var_dump($tmp_value); ?><br>
					<?php
					}
						//now, build the combo automatically!
					gen_Combo($this->FG_TABLE_EDITION[$i][1],$tmp_value,$select_list,$tmp_multiple);


					}elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("RADIOBUTTON")){
						$radio_table = split(",",trim($this->FG_TABLE_EDITION[$i][10]));
						foreach ($radio_table as $radio_instance){
							$radio_composant = split(":",$radio_instance);
							echo $radio_composant[0];
							echo ' <input class="form_enter" type="radio" name="'.$this->FG_TABLE_EDITION[$i][1].'" value="'.$radio_composant[1].'" ';
							if($this->VALID_SQL_REG_EXP){ 
								$know_is_checked = stripslashes($list[0][$i]); 
							}else{ 
								$know_is_checked = $_POST[$this->FG_TABLE_EDITION[$i][1]];  
							}
													
							if ($know_is_checked==$radio_composant[1]){
								echo "checked";
							}
							echo ">";
													
						}								
						//  Yes <input type="radio" name="digitalized" value="t" checked>
						//  No<input type="radio" name="digitalized" value="f">
						
                               		}//END_IF (RADIOBUTTON)  
							   
		  ?>
                        <span class="liens">
                        <?php 						
					if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){
						echo "<br>".$this->FG_TABLE_EDITION[$i][6]." - ".$this->FG_regular[$this->FG_TABLE_EDITION[$i][5]][1];
					}
							   
			  ?>
                        </span>
			<?php  
					if (strlen($this->FG_TABLE_COMMENT[$i])>0){  ?><?php  echo "<br/>".$this->FG_TABLE_COMMENT[$i];?>  <?php  } ?>
                        &nbsp; </TD>
                    </TR>
                    <?php 					
					}else{
								
						if (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SELECT")){
							$table_split = split(":",$this->FG_TABLE_EDITION[$i][14]);
						
					?>
                    <TR> 
						<!-- ******************** PARTIE EXTERN : SELECT ***************** -->
                      	<TD width="122" class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>
					  	<TD align="center" valign="top" background="../Images/background_cells.png" class="tableBodyRight">
                     		<br>
                         
						 	<!-- Table with list instance already inserted -->
                        	<table width="300" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EDF3FF">
								<TR bgcolor="#ffffff"> 
								<TD height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px" class="form_head"> 
								  <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
									<TBODY>
									  <TR> 
										<TD class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?> <?= gettext("LIST ");?></TD>
									  </TR>
									</TBODY>
								  </TABLE></TD>
							  </TR>
							  <TR> 
								<TD> <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
									<TBODY>
									  <TR> 
										<TD bgColor=#e1e1e1 colSpan=<?= $this->FG_TOTAL_TABLE_COL?> height=1><IMG height=1 src="../Images/clear.png" width=1></TD>
									  </TR>
									  <?php
								$SPLIT_CLAUSE = str_replace("%id", "$id", $table_split[4]);
	
	
								$instance_sub_table = new Table($table_split[2], $table_split[3]);
								if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
								$split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, $SPLIT_CLAUSE, null, null, null, null, null, null);			
				
								if (!is_array($split_select_list)){	
									$num=0;
								}else{	
									$num = count($split_select_list);
								}
		
	if($num>0)
	{	
	for($j=0;$j<$num;$j++)
	  {
			if (is_numeric($table_split[7])){
					
					$instance_sub_sub_table = new Table($table_split[8], $table_split[9]);
					if (FG_DEBUG >=2) $instance_sub_sub_table->debug_st=1;
					
					$SUB_TABLE_SPLIT_CLAUSE = str_replace("%1", $split_select_list[$j][$table_split[7]], $table_split[11] );
					$sub_table_split_select_list = $instance_sub_sub_table -> Get_list ($this->DBHandle, $SUB_TABLE_SPLIT_CLAUSE, null, null, null, null, null, null);
					$split_select_list[$j][$table_split[7]] = $sub_table_split_select_list[0][0];
			}	
			
	?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'"> 
                                    <TD vAlign=top class=tableBody> 
                                      <font face="Verdana" size="2">
                                      <b><?= $split_select_list[$j][$table_split[7]]?></b> : <?= $split_select_list[$j][0]?>
                                      </font> </TD>
                                    <TD align="center" vAlign=top class=tableBodyRight> 
                                      <input onClick="sendto('del-content','<?php echo $i?>','<?php echo $table_split[1]?>_hidden','<?php echo $split_select_list[$j][1]?>');" title="Remove this <?php echo $this->FG_TABLE_EDITION[$i][0]?>" alt="Remove this <?php echo $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=11 hspace=2 id=submit33 name=submit33 src="<?php echo Images_Path_Main;?>/icon-del.gif" type=image width=33 value="add-split">
                                    </TD>
                                  </TR>
                                  <?php 
	  }//end_for
	}else{
			?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'"> 
                                    <TD colspan="2" align="<?= $this->FG_TABLE_COL[$i][3]?>" vAlign=top class=tableBody> 
                                      <div align="center" class="liens"><?= gettext("No");?><?= $this->FG_TABLE_EDITION[$i][0]?></div></TD>
                                  </TR>
                                  <?php 
	}
	?>
                                  <TR> 
                                    <TD class=tableDivider colSpan=<?= $this->FG_TOTAL_TABLE_COL?>><IMG height=1 src="../Images/clear.png" width=1></TD>
                                  </TR>
                                </TBODY>
                              </TABLE></td>
                          </tr>
                          <TR class="bgcolor_016"> 
                            <TD bgcolor="#AAAAAA"  height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
                              <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                  <TR> 
                                    <TD height="4" align="right"></TD>
                                </TR>
                              </TABLE>
			</TD>
                          </TR>
                        </table><br>
			</TD>
                    </TR>
                    <TR>
					  <!-- *******************   Select to ADD new instances  ****************************** -->					  					  
                      <TD class="form_head">&nbsp;</TD>
                      <TD align="center" valign="top" background="<?php echo Images_Path;?>/background_cells.png" class="text"><br>
                        <TABLE width="300" height=50 border=0 align="center" cellPadding=0 cellSpacing=0>
                            <TR> 
                            	<TD bgColor=#7f99cc colSpan=3 height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 5px" class="form_head">
									<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
										<TR> 
											<TD class="form_head"><?php echo gettext("Add a new");?> <?php echo $this->FG_TABLE_EDITION[$i][0]?></TD>
										</TR>
									</TABLE>
								</TD>
                            </TR>
							
                            <TR> 
								<TD class="form_head"> <IMG height=1 src="../Images/clear.png" width=1>
								</TD>
								<TD class="editform_table4_td1"> 
                                
								<TABLE width="97%" border=0 align="center" cellPadding=0 cellSpacing=0>
									<TR> 
										<TD width="122" class="tableBody"><?php echo $this->FG_TABLE_EDITION[$i][0]?></TD>
										<TD width="516"><div align="center"> 	
							 <input name="<?php echo $table_split[1]?>_hidden" type="hidden" value="" />
                                          <SELECT name="<?php echo $table_split[1]?>[]" <?php echo $this->FG_TABLE_EDITION[$i][4]?> class="form_input_select">
                                            <?php
					 $split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, null, null, null, null, null, null, null);

					 if (count($split_select_list)>0)
					 {
						 $select_number=0;
						 foreach ($split_select_list as $select_recordset){
							 $select_number++;
							 if ($table_split[6]!="" && !is_null($table_split[6])){
							 	if (is_numeric($table_split[7])){
									$instance_sub_sub_table = new Table($table_split[8], $table_split[9]);
									if (FG_DEBUG >=2) $instance_sub_sub_table->debug_st=1;
									$SUB_TABLE_SPLIT_CLAUSE = str_replace("%1", $select_recordset[$table_split[7]], $table_split[11] );
									$sub_table_split_select_list = $instance_sub_sub_table -> Get_list ($this->DBHandle, $SUB_TABLE_SPLIT_CLAUSE, null, null, null, null, null, null);
									$select_recordset[$table_split[7]] = $sub_table_split_select_list[0][0];
								}
								 $value_display = $table_split[6];
								 $nb_recor_k = count($select_recordset);
								 for ($k=1;$k<=$nb_recor_k;$k++){
									$value_display  = str_replace("%$k", $select_recordset[$k-1], $value_display );
								 }
							 }else{
							 	$value_display  = $select_recordset[0];
							 }

			  ?>
                                            <OPTION  value='<?php echo $select_recordset[1]?>'>
                                            <?= $value_display?>
                                            </OPTION>
                                            <?php
						 }// END_FOREACH
					  }else{
						echo gettext("No data found !!!");
					  }//END_IF
							  ?>
                                          </SELECT>
                                        </div>
										</TD>
                                    </TR>
									<TR>
                                      <TD colSpan=2 height=4></TD>
                                    </TR>
                                    <TR>
                                      <TD colspan="2" align="center" vAlign="middle">
					<input onClick="sendto('add-content','<?= $i?>');" title="<?= gettext("add new a ");?><?= $this->FG_TABLE_EDITION[$i][0]?>" alt="<?= gettext("add new a ");?><?= $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=20 hspace=2 id=submit32 name=submit3 src="../Images/btn_Add_94x20.png" type=image width=94 value="add-split">
                                      </TD>
                                    </TR>
                                </TABLE>
				</TD>
                            <TD class="form_head"><IMG height=1 src="../Images/clear.png" width=1></TD>
                            </TR>
                            <TR>
                              <TD colSpan=3 class="form_head"><IMG height=1 src="../Images/clear.png" width=1></TD>
                            </TR>

                        </TABLE>

                        </TD>
                    </TR>

					<?php }elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("INSERT")){
							$table_split = split(":",$this->FG_TABLE_EDITION[$i][14]);
					?>
					<TR>
					  <!-- ******************** PARTIE EXTERN : INSERT ***************** -->

                      	<TD width="122" class="form_head"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>

                      	<TD align="center" valign="top" background="../Images/background_cells.png" class="text"><br>


                        <!-- Table with list instance already inserted -->
                        <table width="300" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EDF3FF">
                          <TR bgcolor="#ffffff">
                            <TD height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px" class="form_head">
                            	<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                	<TR>
                                		<TD class="form_head"><?php echo $this->FG_TABLE_EDITION[$i][0]?>&nbsp;<?php echo gettext("LIST");?> </TD>
                                	</TR>
                            	</TABLE>
							</TD>
                          </TR>
                          <TR>
                            <TD>
					<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                <TR>
                                	<TD bgColor=#e1e1e1 colSpan=<?= $this->FG_TOTAL_TABLE_COL?> height=1><IMG height=1 src="../Images/clear.png" width=1></TD>
                                </TR>
                                <?php
			$SPLIT_CLAUSE = str_replace("%id", "$id", $table_split[4]);

			$instance_sub_table = new Table($table_split[2], $table_split[3]);
			if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
			$split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, $SPLIT_CLAUSE, null, null, null, null, null, null);
	if (!is_array($split_select_list)){
		$num=0;
	}else{
		$num = count($split_select_list);
	}



	if($num>0)
	{
	for($j=0;$j<$num;$j++)
	  {

	?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'">
                                    <TD vAlign="top" align="<?= $this->FG_TABLE_COL[$i][3]?>" class="tableBody">
                                      <font face="Verdana" size="2">
                                      <?php if(!empty($split_select_list[$j][$table_split[7]]))
                                      {
                                      ?>
                                      <b><?php echo $split_select_list[$j][$table_split[7]]?></b> : 
                                      <?php }?>
                                      <?php echo $split_select_list[$j][0]?>
                                      </font> </TD>
                                    <TD align="center" vAlign="top2" class="tableBodyRight">
                                      <input onClick="sendto('del-content','<?= $i?>','<?= $table_split[1]?>','<?= $split_select_list[$j][1]?>');" alt="Remove this <?= $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=11 hspace=2 id=submit33 name=submit33 src="../Images/icon-del.png" type=image width=33 value="add-split">
                                    </TD>
                                  </TR>
                                  <?php
	  }//end_for
	}else{
			?>
                                  <TR bgcolor="<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?= $this->FG_TABLE_ALTERNATE_ROW_COLOR[$j%2]?>'">
                                    <TD colspan="2" align="<?= $this->FG_TABLE_COL[$i][3]?>" vAlign="top" class="tableBody">
                                      <div align="center" class="liens">No <?= $this->FG_TABLE_EDITION[$i][0]?></div></TD>
                                  </TR>
                                  <?php
	}
	?>
                                  <TR> 
                                    <TD class="tableDivider" colSpan=<?= $this->FG_TOTAL_TABLE_COL?>><IMG height=1 src="../Images/clear.png" width=1></TD>
                                  </TR>
                              </TABLE></td>
                          </tr>
                          <TR class="bgcolor_016"> 
                            <TD bgcolor="#AAAAAA"  height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
                            	<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                                	<TR><TD height="4" align="right"></TD></TR>
                              	</TABLE>
							</TD>
                          </TR>
                        </table><br>
</TD>
                    </TR>
                    <TR>
					  <!-- *******************   Select to ADD new instances  ****************************** -->					  
                      <TD class="form_head">&nbsp;</TD>
                      <TD align="center" valign="top" background="../Images/background_cells.png" class="text"><br>
                        <TABLE width="300" height=50 border=0 align="center" cellPadding=0 cellSpacing=0>
                            <TR> 
                            	<TD bgColor=#7f99cc colSpan=3 height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 5px" class="form_head">
									<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
										<TR> 
											<TD class="form_head"><?= gettext("Add a new");?> <?= $this->FG_TABLE_EDITION[$i][0]?></TD>
										</TR>
									</TABLE>
								</TD>
                            </TR>
							
                            <TR> 
								<TD class="form_head"> <IMG height=1 src="../Images/clear.png" width=1>
								</TD>
								<TD bgColor=#F3F3F3 style="PADDING-BOTTOM: 7px; PADDING-LEFT: 5px; PADDING-RIGHT: 5px; PADDING-TOP: 5px"> 
                                
								<TABLE width="97%" border=0 align="center" cellPadding=0 cellSpacing=0>
									<TR> 
										<TD width="122" class="tableBody"><?= $this->FG_TABLE_EDITION[$i][0]?></TD>
										<TD width="516"><div align="left"> 	
										<?php if($this->FG_TABLE_EDITION[$i][4] == "multiline"){?>
							  				<textarea name=<?php echo $table_split[1]?> class="form_input_text"  cols="20" rows="5"></textarea>
										<?php }else{?>
											<INPUT TYPE="TEXT" name=<?php echo $table_split[1]?> class="form_input_text"  size="20" maxlength="20">
										<?php }?>
										</TD>
                                    </TR>
                                    <TR> 
										<TD colspan="2" align="center">									  	
											<input onClick="sendto('add-content','<?=$i?>');" alt="add new a <?php echo $this->FG_TABLE_EDITION[$i][0]?>" border=0 height=20 hspace=2 id=submit32 name=submit3 src="<?php echo Images_Path_Main;?>/btn_Add_94x20.gif" type=image width=94 value="add-split">
										</TD>
                                    </TR>
                                    <TR> 
                                      <TD colSpan=2 height=4></TD>
                                    </TR>
                                    <TR> 
                                      <TD colSpan=2> <div align="right"></div></TD>
                                    </TR>
                                </TABLE>
								</TD>
								<TD class="form_head"><IMG height=1 src="../Images/clear.png" width=1>
								</TD>
                            </TR>
                            <TR> 
                              <TD colSpan=3 class="form_head"><IMG height=1 src="../Images/clear.png" width=1></TD>
                            </TR>
                        </TABLE>
                        <br></TD>
                    </TR>					
					<?php  }elseif (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("CHECKBOX")){
							
							$table_split = split(":",$this->FG_TABLE_EDITION[$i][14]);
					?>
					<TR> 
					 <!-- ******************** PARTIE EXTERN : CHECKBOX ***************** -->
                     
 					 <td width="206" height="42" valign="top" bgcolor="#e2e2d3">
					 	<table width="100%" border="0" cellpadding="2" cellspacing="0" class="form_text">
                   		<tr>
                        	<td width="122"><?= $this->FG_TABLE_EDITION[$i][0]?></td>
                        </tr>
						</table>
					</td>
					<td width="400" valign="top" background="../Images/background_cells.png" class="text">
					    
	<?php 
	$SPLIT_CLAUSE = str_replace("%id", "$id", $table_split[4]);
	


	$instance_sub_table = new Table($table_split[2], $table_split[3]);
	if (FG_DEBUG >=2) $instance_sub_table->debug_st=1;
	$split_select_list = $instance_sub_table -> Get_list ($this->DBHandle, $SPLIT_CLAUSE, null, null, null, null, null, null);			
	if (!is_array($split_select_list)){	
		$num=0;
	}else{	
		$num = count($split_select_list);
	}
	
	 ////////////////////////////////////////////////////////////////////////////////////////////////////////

	 $table_split[12] = str_replace("%id", "$id", $table_split[12]);
	 $split_select_list_tariff = $instance_sub_table -> Get_list ($this->DBHandle, $table_split[12], null, null, null, null, null, null);
	 if (count($split_select_list_tariff)>0)
	 {
			 $select_number=0;
			  ?>				
			  <TABLE width="400" height=50 border=0 align="center" cellPadding=0 cellSpacing=0>
				<TR> 
                	<TD colSpan=3 height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 5px">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td bgcolor="#e2e2d3" class="textnegrita"><font color="#000000"> <?= $this->FG_TABLE_COMMENT[$i]?></font></td>
							</tr>
                        </table>
					</TD>
				</TR>
                <TR> 
                	<TD class="form_head"> <IMG height=1 src="../Images/clear.png" width=1>
                    </TD>
                    <TD bgColor=#F3F3F3 style="PADDING-BOTTOM: 7px; PADDING-LEFT: 5px; PADDING-RIGHT: 5px; PADDING-TOP: 5px"> 
						<TABLE width="97%" border=0 align="center" cellPadding=0 cellSpacing=0>
                        
 <?php 
 	foreach ($split_select_list_tariff as $select_recordset){ 
				 $select_number++;
				 
				 if ($table_split[6]!="" && !is_null($table_split[6])){
				 
						if (is_numeric($table_split[7])){
							$instance_sub_sub_table = new Table($table_split[8], $table_split[9]);
							$SUB_TABLE_SPLIT_CLAUSE = str_replace("%1", $select_recordset[$table_split[7]], $table_split[11] );
							$sub_table_split_select_list_tariff = $instance_sub_sub_table -> Get_list ($this->DBHandle, $SUB_TABLE_SPLIT_CLAUSE, null, null, null, null, null, null);
							$select_recordset[$table_split[7]] = $sub_table_split_select_list_tariff[0][0];
						}													 
						 $value_display = $table_split[6];
						 $nb_recor_k = count($select_recordset);
						 for ($k=1;$k<=$nb_recor_k;$k++){
							$value_display  = str_replace("%$k", $select_recordset[$k-1], $value_display );
						 }
				 }else{													 	
					$value_display  = $select_recordset[0];
				 }
				 
				 
				 $checked_tariff=false;
				 if($num>0)
				 {
					for($j=0;$j<$num;$j++)
					{
						if ($select_recordset[1]==$split_select_list[$j][1]) $checked_tariff=true;
					}
				 }

?>
			<TR>
				<TD class="tableBody"><input type="checkbox" name="<?= $table_split[0]?>[]" value="<?= $select_recordset[1]?>" <?php if ($checked_tariff) echo"checked";?>></TD>
				<TD class="text_azul">&nbsp; <?= $value_display?></TD>
			</TR>
<?php }// END_FOREACH?>
                         <TR><TD colSpan=2 height=4>
				<span class="liens">
					<?php
				if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){
					echo "<br>".$this->FG_TABLE_EDITION[$i][6];
				}
		  ?>
					</span>
				</TD></TR>
                                </TABLE></TD>
                              <TD class="form_head"><IMG height=1 src="../Images/clear.png" width=1>
                              </TD>
                            </TR>
                            <TR>
                              <TD colSpan=3 class="form_head"><IMG height=1 src="../Images/clear.png" width=1></TD>
                            </TR>
                        </TABLE>

			  <?php
	  		}else{
				echo gettext("No data found !!!");
	  }?>

					 </TD>
                    </TR>
                    <?php   	  }// end if if (strtoupper ($this->FG_TABLE_EDITION[$i][3])==strtoupper ("SELECT"))
							}// end if pos
			}//END_FOR ?>
                </FORM>
              </TABLE>
	  <TABLE cellspacing="0" class="editform_table8">
		<tr>
			<td width="50%"><span class="tableBodyRight"><?php echo $this->FG_BUTTON_EDITION_BOTTOM_TEXT?></span></td>
			<td width="50%" align="right" class="text">
			
				<a href="#" onClick="sendto('edit');" class="cssbutton_big"><IMG src="../Images/icon_arrow_orange.png">
				<?php echo $this->FG_EDIT_PAGE_CONFIRM_BUTTON; ?> </a>
				
				<!-- 
				<input onClick="sendto('edit');" border=0 hspace=0 id=submit3 name=submit32 src="<?php echo $this->FG_BUTTON_EDITION_SRC?>" 
				type=image value="add-split">		
				-->
			</td>
		</tr>
	  </TABLE>
