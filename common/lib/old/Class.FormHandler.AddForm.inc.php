<script language="JavaScript" src="./javascript/calendar3.js"></script>

	<FORM action=<?php echo $_SERVER['PHP_SELF']?> id="myForm" method="post" name="myForm">
	
	<table width="95%" border="0" cellpadding="2" cellspacing="2" bgcolor="#E2E2D3" align="center">
                  <INPUT type="hidden" name="form_action" value="add">
		  <INPUT type="hidden" name="wh" value="<?php echo $wh?>">
	<?php
	if (!is_null($this->FG_QUERY_ADITION_HIDDEN_FIELDS) && $this->FG_QUERY_ADITION_HIDDEN_FIELDS!=""){
		$split_hidden_fields = split(",",trim($this->FG_QUERY_ADITION_HIDDEN_FIELDS));
		$split_hidden_fields_value = split(",",trim($this->FG_QUERY_ADITION_HIDDEN_VALUE));
		for ($cur_hidden=0;$cur_hidden<count($split_hidden_fields);$cur_hidden++){
			echo "<INPUT type=\"hidden\" name=\"".trim($split_hidden_fields[$cur_hidden])."\" value=\"".trim($split_hidden_fields_value[$cur_hidden])."\">\n";
		}
	}
	?>
	 	 <INPUT type="hidden" name="atmenu" value="<?php echo $atmenu?>">
		 <TBODY>
	<?php
		for($i=0;$i<$this->FG_NB_TABLE_ADITION;$i++){ 
			$pos = strpos($this->FG_TABLE_ADITION[$i][14], ":");
			
			if (strlen($this->FG_TABLE_EDITION[$i][16])>1){
				echo '<TR><TD width="%25" valign="top" bgcolor="#FEFEEE" colspan="2" class="tableBodyRight" ><i>';				
				echo $this->FG_TABLE_EDITION[$i][16];
				echo '</i></TD></TR>';
			}
			
			if (!$pos){
	?>
               <TR>
			   <?php if (!$this-> FG_fit_expression[$i]  &&  isset($this-> FG_fit_expression[$i]) ){ ?>
			<TD width="%25" valign="middle" class="form_head_red"> 		<?php echo $this->FG_TABLE_ADITION[$i][0]?> 		</TD>  
		  	<TD width="%75" valign="top" class="tableBodyRight" background="<?php echo Images_Path;?>/background_cells_red.gif" class="text">
        <?php }else{ ?>
			<TD width="%25" valign="middle" class="form_head"> 		<?php echo $this->FG_TABLE_ADITION[$i][0]?> 		</TD>  
			<TD width="%75" valign="top" class="tableBodyRight" background="<?php echo Images_Path;?>/background_cells.gif" class="text">
		<?php } ?>
		
	<?php 
		if ($this->FG_DEBUG == 1) print($this->FG_TABLE_ADITION[$i][3]);
  		if (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("INPUT")){
	?>
                 <INPUT class="form_input_text" name=<?php echo $this->FG_TABLE_ADITION[$i][1]?>  <?php echo $this->FG_TABLE_ADITION[$i][4]?> value="<?php echo $_POST[$this->FG_TABLE_ADITION[$i][1]];?>">
	<?php
		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("POPUPVALUE")){
	?>
		<INPUT class="form_enter" name=<?php echo $this->FG_TABLE_ADITION[$i][1]?>  <?php echo $this->FG_TABLE_ADITION[$i][4]?> value="<?		
		
			if($this->VALID_SQL_REG_EXP){
				echo stripslashes($list[0][$i]);
			}else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
		<a href="#" onclick="window.open('<?php echo $this->FG_TABLE_ADITION[$i][12]?>popup_formname=myForm&popup_fieldname=<?php echo $this->FG_TABLE_ADITION[$i][1]?>' <?php echo $this->FG_TABLE_ADITION[$i][13]?>);"><img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/></a>
<!--CAPTCHA IMAGE CODE START HERE-->
	<?php
		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper("CAPTCHAIMAGE"))
		{
	?>
		<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>			
				<td> <img src="../signup/captcha/captcha.php" ></td>
			</tr>			
			<tr>
			<td><INPUT class="form_input_text" name=<?php echo $this->FG_TABLE_ADITION[$i][1]?>  <?php echo $this->FG_TABLE_ADITION[$i][4]?> value="<?php echo $_POST[$this->FG_TABLE_ADITION[$i][1]];?>"> Enter code from above picture here.
			</td>
			</tr>
			</table>
		
		
<!--CAPTCHA IMAGE CODE END HERE-->		
			
	<?php
		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("POPUPVALUETIME"))
		{
	?>
		<INPUT class="form_enter" name=<?php echo $this->FG_TABLE_ADITION[$i][1]?>  <?php echo $this->FG_TABLE_ADITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
		<a href="#" onclick="window.open('<?php echo $this->FG_TABLE_ADITION[$i][14]?>formname=myForm&fieldname=<?php echo $this->FG_TABLE_ADITION[$i][1]?>' <?php echo $this->FG_TABLE_ADITION[$i][14]?>);"><img src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif"/></a>
	<?php
		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("POPUPDATETIME"))
		{
	?>
		<INPUT class="form_enter" name=<?php echo $this->FG_TABLE_ADITION[$i][1]?>  <?php echo $this->FG_TABLE_ADITION[$i][4]?> value="<?php if($this->VALID_SQL_REG_EXP){ echo stripslashes($list[0][$i]); }else{ echo $_POST[$this->FG_TABLE_ADITION[$i][1]]; }?>">
		<a href="javascript:cal<?php echo $this->FG_TABLE_ADITION[$i][1]?>.popup();"><img src="img/cal.gif" width="16" height="16" border="0" title="Click Here to Pick up the date" alt="Click Here to Pick up the date"></a>
		<script language="JavaScript">
		<!-- // create calendar object(s) just after form tag closed
		// specify form element as the only parameter (document.forms['formname'].elements['inputname']);
		// note: you can have as many calendar objects as you need for your application
		var cal<?php echo $this->FG_TABLE_ADITION[$i][1]?> = new calendar3(document.forms['myForm'].elements['<?php echo $this->FG_TABLE_ADITION[$i][1]?>']);
		cal<?php echo $this->FG_TABLE_ADITION[$i][1]?>.year_scroll = false;
		cal<?php echo $this->FG_TABLE_ADITION[$i][1]?>.time_comp = true;
		cal<?php echo $this->FG_TABLE_ADITION[$i][1]?>.formatpgsql = true;
		//-->
		</script>
	<?php 
		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("TEXTAREA")){
	?>
            <TEXTAREA class="form_enter" name=<?php echo $this->FG_TABLE_ADITION[$i][1]?> <?php echo $this->FG_TABLE_ADITION[$i][4]?>><?php echo $_POST[$this->FG_TABLE_ADITION[$i][1]];?></TEXTAREA> 
	<?php 	
		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("SELECT")){
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
	<OPTION  value='<?php echo $select_recordset[1]?>' 
			}
			if (isset($this->FG_TABLE_EDITION[$i][15]))
				array_unshift($select_list,$this->FG_TABLE_EDITION[$i][15]);
			
			
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

		}elseif (strtoupper ($this->FG_TABLE_ADITION[$i][3])==strtoupper ("RADIOBUTTON")){
				$radio_table = split(",",trim($this->FG_TABLE_ADITION[$i][10]));
				foreach ($radio_table as $radio_instance){
					$radio_composant = split(":",$radio_instance);
					echo $radio_composant[0];
					echo ' <input type="radio" name="'.$this->FG_TABLE_ADITION[$i][1].'" value="'.$radio_composant[1].'" ';
					// TODO just a temporary and quick hack please review $VALID_SQL_REG_EXP
					if ($_POST[$this->FG_TABLE_ADITION[$i][1]]==$radio_composant[1]){
						echo "checked";
					}
					else if($VALID_SQL_REG_EXP){
						$know_is_checked = stripslashes($list[0][$i]);
					}else{
						$know_is_checked = $this -> FG_TABLE_ADITION[$i][2];
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
				echo "<br>".$this->FG_TABLE_ADITION[$i][6]." ".$this->FG_regular[$this->FG_TABLE_ADITION[$i][5]][1];	
			}
	 ?>
                        </span> 
	<?php  
			if (strlen($this->FG_TABLE_COMMENT[$i])>0){  ?><?php  echo "<br/>".$this->FG_TABLE_COMMENT[$i];?>  <?php  } ?>
       </TD>
	</TR>
					
	<?php   	}
					
		}//END_FOR 		
		?>
	
        </TBODY>
      </TABLE>
	  <TABLE cellspacing="0" class="editform_table8">
		<tr>
			<td width="50%" class="text_azul"><span class="tableBodyRight"><?php echo $this->FG_BUTTON_ADITION_BOTTOM_TEXT?></span></td>
			<td width="50%" align="right" class="text">
				<a href="#" onClick="javascript:document.myForm.submit();" class="cssbutton_big"><IMG src="<?php echo Images_Path_Main;?>/icon_arrow_orange.gif">
				<?php echo $this->FG_ADD_PAGE_CONFIRM_BUTTON; ?> </a>
				<!--
				<INPUT title="<?php echo gettext("Create a new ");?><?php echo $this->FG_INSTANCE_NAME?>" alt="<?php echo gettext("Create a new ");?> <?php echo $this->FG_INSTANCE_NAME?>" border=0 hspace=0 id=submit4 name=submit2 src="<?php echo $this->FG_BUTTON_ADITION_SRC?>" type=image>
				-->
			</td>
		</tr>
	  </TABLE>
	</FORM>