<?php

	//echo "<b><hr>$this->FG_FILTER_SEARCH_SESSION_NAME<br>".$_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]."</br></b>";	

	if ($this->FG_FILTER_SEARCH_FORM){

?>



<a href="#" target="_self"  onclick="imgidclick('img51000','div51000','kfind.png','viewmag.png');"><img id="img51000" src="../Css/kicons/viewmag.png" onmouseover="this.style.cursor='hand';" WIDTH="16" HEIGHT="16"></a>
<div id="div51000" style="display:visible;">

<!-- ** ** ** ** ** Part for the research - ** ** ** ** ** -->
	<center>
		<b><?php echo $this -> FG_FILTER_SEARCH_TOP_TEXT?></b>
	
		<table class="bar-status" width="85%" border="0" cellspacing="1" cellpadding="2" align="center">
		<FORM METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']?>?s=<?php echo $processed['s']?>&t=<?php echo $processed['t']?>&order=<?php echo $processed['order']?>&sens=<?php echo $processed['sens']?>&current_page=<?php echo $processed['current_page']?>">
	<INPUT TYPE="hidden" NAME="posted_search" value="1">
	<INPUT TYPE="hidden" NAME="current_page" value="0">		
			<tr>
        		<td class="bar-search" align="left" bgcolor="#555577" width="120">

					<input type="radio" name="Period" value="Month" <?php  if (!isset($processed['Period']) || ($processed['Period']=="Month")){ ?>checked="checked" <?php  } ?>> 
					<font face="verdana" size="1" color="#ffffff"><b><?php echo $this-> FG_FILTER_SEARCH_1_TIME_TEXT?></b></font>
				</td>
      			<td class="bar-search" align="left" bgcolor="#cddeff">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#cddeff"><tr><td>
	  				<input type="checkbox" name="frommonth" value="true" <?php  if ($processed['frommonth']){ ?>checked<?php }?>>
					
					From : <select name="fromstatsmonth">
					<?php 
						$year_actual = date("Y");  	
						$monthname = array( gettext("January"), gettext("February"),gettext("March"), gettext("April"), gettext("May"), gettext("June"), gettext("July"), gettext("August"), gettext("September"), gettext("October"), gettext("November"), gettext("December"));
						for ($i=$year_actual;$i >= $year_actual-1;$i--)
						{
							   if ($year_actual==$i){
									$monthnumber = date("n")-1; // Month number without lead 0.
							   }else{
									$monthnumber=11;
							   }
							   for ($j=$monthnumber;$j>=0;$j--){
										$month_formated = sprintf("%02d",$j+1);
							   			if ($processed['fromstatsmonth']=="$i-$month_formated") $selected="selected";
										else $selected="";
										echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
							   }
						}
					?>
					</select>
					</td><td>&nbsp;&nbsp;
					<input type="checkbox" name="tomonth" value="true" <?php  if ($processed['tomonth']){ ?>checked<?php }?>>
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
							   			if ($processed['tostatsmonth']=="$i-$month_formated") $selected="selected";
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
					<input type="radio" name="Period" value="Day" <?php  if ($processed['Period']=="Day"){ ?>checked="checked" <?php  } ?>>
					<font face="verdana" size="1" color="#ffffff"><b><?php echo $this-> FG_FILTER_SEARCH_2_TIME_TEXT?></b></font>
				</td>
      			<td align="left" bgcolor="#acbdee">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#acbdee"><tr><td>
	  				<input type="checkbox" name="fromday" value="true" <?php  if ($processed['fromday']){ ?>checked<?php }?>> <? echo gettext("From :");?>
					<select name="fromstatsday_sday">
						<?php
							for ($i=1;$i<=31;$i++){
								if ($processed['fromstatsday_sday']==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
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
								if ($processed['fromstatsmonth_sday']=="$i-$month_formated") $selected="selected";
								else $selected="";
								echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
							}
						}
					?>
					</select>
					</td><td>&nbsp;&nbsp;
					<input type="checkbox" name="today" value="true" <?php  if ($processed['today']){ ?>checked<?php }?>><?php echo gettext("To :");?>
					<select name="tostatsday_sday">
					<?php
						for ($i=1;$i<=31;$i++){
							if ($processed['tostatsday_sday']==sprintf("%02d",$i)){$selected="selected";}else{$selected="";}
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
								if ($processed['tostatsmonth_sday']=="$i-$month_formated") $selected="selected";
								else $selected="";
								echo "<OPTION value=\"$i-$month_formated\" $selected> $monthname[$j]-$i </option>";
							}
						}
					?>
					</select>
					</td></tr></table>
	  			</td>
    		</tr>

			<!-- compare with a value //-->

			<?php
			foreach ($this->FG_FILTER_SEARCH_FORM_1C as $one_compare){
			?>
			<tr>
				<td class="bar-search" align="left" bgcolor="#555577">
					<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo $one_compare[0]?></b></font>
				</td>
				<td class="bar-search" align="left" bgcolor="#cddeff">
				<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="<?php echo $one_compare[1]?>" value="<?php echo $processed[$one_compare[1]]?>"></td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $one_compare[2]?>" value="1" <?php if((!isset($processed[$one_compare[2]]))||($processed[$one_compare[2]]==1)){?>checked<?php }?>><?php echo gettext("Exact");?> </td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $one_compare[2]?>" value="2" <?php if($processed[$one_compare[2]]==2){?>checked<?php }?>> <?php echo gettext("Begins with");?></td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $one_compare[2]?>" value="3" <?php if($processed[$one_compare[2]]==3){?>checked<?php }?>> <?php echo gettext("Contains");?></td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $one_compare[2]?>" value="4" <?php if($processed[$one_compare[2]]==4){?>checked<?php }?>> <?php echo gettext("Ends with");?></td>
				</tr></table></td>
			</tr>

			<?php
			}
			?>

			<!-- compare between 2 values //-->

			<?php
			foreach ($this->FG_FILTER_SEARCH_FORM_2C as $two_compare){
			?>
			<tr>
				<td class="bar-search" align="left" bgcolor="#555577">
					<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo $two_compare[0]?></b></font>
				</td>
				<td class="bar-search" align="left" bgcolor="#cddeff">
				<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
				<td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="<?php echo $two_compare[1]?>" size="4" value="<?php echo $processed[$two_compare[1]]?>"></td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[2]?>" value="4" <?php if($processed[$two_compare[2]]==4){?>checked<?php }?>>&gt;</td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[2]?>" value="5" <?php if($processed[$two_compare[2]]==5){?>checked<?php }?>>&gt; =</td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[2]?>" value="1" <?php if((!isset($processed[$two_compare[2]]))||($processed[$two_compare[2]]==1)){?>checked<?php }?>> = </td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[2]?>" value="2" <?php if($processed[$two_compare[2]]==2){?>checked<?php }?>>&lt; =</td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[2]?>" value="3" <?php if($processed[$two_compare[2]]==3){?>checked<?php }?>>&lt;</td>
				<td width="5%" class="bar-search" align="center" bgcolor="#cddeff"></td>

				<td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="<?php echo $two_compare[3]?>" size="4" value="<?php echo $processed[$two_compare[3]]?>"></td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[4]?>" value="4" <?php if($processed[$two_compare[4]]==4){?>checked<?php }?>>&gt;</td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[4]?>" value="5" <?php if($processed[$two_compare[4]]==5){?>checked<?php }?>>&gt; =</td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[4]?>" value="2" <?php if($processed[$two_compare[4]]==1){?>checked<?php }?>>&lt; =</td>
				<td class="bar-search" align="center" bgcolor="#cddeff"><input type="radio" NAME="<?php echo $two_compare[4]?>" value="3" <?php if($processed[$two_compare[4]]==3){?>checked<?php }?>>&lt;</td>
				</tr></table>
				</td>
			</tr>

			<?php
			}
			?>

			<!-- select box //-->


			<tr>
				<td class="bar-search" align="left" bgcolor="#555577">
					<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo $this->FG_FILTER_SEARCH_FORM_SELECT_TEXT?></b></font>
				</td>
				<td class="bar-search" align="left" bgcolor="#cddeff">

				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
				<?php
				foreach ($this->FG_FILTER_SEARCH_FORM_SELECT as $selects){
				?>
				<td>
					<select NAME="<?php echo $selects[2]?>" size="1" style="border: 1px outset;">
						<option value=''><?php echo $selects[0]?></option>
				<?php
					 foreach ($selects[1] as $recordset){
				?>
						<option class=input value='<?php echo $recordset[0]?>'  <?php if ($processed[$selects[2]]==$recordset[0]) echo 'selected="selected"'?>><?php echo $recordset[1]?></option>
				<?php 	 }
				?>
					</select>
				</td>
				<?php
				}
				?>
				</tr>
				</table></td>
			</tr>

			<tr>
        		<td class="bar-search" align="left" bgcolor="#000033"> </td>

				<td class="bar-search" align="center" bgcolor="#acbdee">
					<input type="image"  name="image16" align="top" border="0" src="<?php echo Images_Path_Main;?>/button-search.gif" />
					<?php if(isset($_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME]) && strlen($_SESSION[$this->FG_FILTER_SEARCH_SESSION_NAME])>10 ){ ?>
                    - <a href="<?php echo $_SERVER['PHP_SELF']?>?cancelsearch=true"><font color="red"><b><img src="../Css/kicons/button_cancel.png" height="16"> Cancel Search</b></font></a>&nbsp;
                    - <a href="<?php echo $_SERVER['PHP_SELF']?>?deleteselected=true" onclick="return confirm('<?php echo "Are you sure to delete ".$this -> FG_NB_RECORD." selected records?";?>');"><font color="red"><b>Delete All</b></font></a>
                    <?php } ?>



	  			</td>
    		</tr>
		</tbody></table>
	</FORM>
</center>
</div>
<!-- ** ** ** ** ** End - Part for the research ** ** ** ** ** -->
<?php
}

	if ($this->FG_UPDATE_FORM){
?>

<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
<a href="#" target="_self"  onclick="imgidclick('img61000','div61000','kfind.png','viewmag.png');"><img id="img61000" src="../Css/kicons/viewmag.png" onmouseover="this.style.cursor='hand';" WIDTH="16" HEIGHT="16"></a>
<div id="div61000" style="display:visible;">

<br>
<center>
<b><?php echo gettext("There is");?>&nbsp;<?php echo $nb_record ?>&nbsp;<?php echo gettext("selected, use the option below if you are willing to make a batch updated of the selected cards.");?></b>
	   <table align="center" border="0" width="65%"  cellspacing="1" cellpadding="2">
        <tbody>
		<form name="updateForm" action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
		<INPUT type="hidden" name="batchupdate" value="1">


		<tr>
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_id_trunk]" type="checkbox" <?php if ($check["upd_id_trunk"]=="on") echo "checked"?>>
		  </td>
		  <td align="left"  bgcolor="#cccccc">
				<strong>1) TRUNK : </strong>
				<select NAME="upd_id_trunk" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
					<?php
					 foreach ($list_trunk as $recordset){
					?>
						<option class=input value='<?php echo $recordset[0]?>'  <?php if ($upd_id_trunk==$recordset[0]) echo 'selected="selected"'?>><?php echo $recordset[1].' ('.$recordset[2].')'?></option>
					<?php 	 }
					?>
				</select>
			</td>
		</tr>
		<tr>
          <td align="left"  bgcolor="#cccccc">
		  	<input name="check[upd_idtariffplan]" type="checkbox" <?php if ($check["upd_idtariffplan"]=="on") echo "checked"?> >
		  </td>
		  <td align="left"  bgcolor="#cccccc">

			  	<strong>2) <?php echo gettext("RATECARD");?> :</strong>
				<select NAME="upd_idtariffplan" size="1" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
									
					<?php					 
				  	 foreach ($list_tariffname as $recordset){ 						 
					?>
						<option class=input value='<?php echo $recordset[0]?>'  <?php if ($upd_idtariffplan==$recordset[0]) echo 'selected="selected"'?>><?php echo $recordset[1]?></option>                        
					<?php 	 }
					?>
				</select>
				<br/>
			</td>
		</tr>
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_buyrate]" type="checkbox" <?php if ($check["upd_buyrate"]=="on") echo "checked"?>>
				<input name="mode[upd_buyrate]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
			  	<strong>3)&nbsp;<?php echo gettext("BUYRATE");?>&nbsp;:</strong>
					<input class="form_enter" name="upd_buyrate" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_buyrate)) echo $upd_buyrate; else echo '0';?>">
				<font class="version">
				<input type="radio" NAME="type[upd_buyrate]" value="1" <?php if((!isset($type["upd_buyrate"]))|| ($type["upd_buyrate"]==1) ){?>checked<?php }?>><?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_buyrate]" value="2" <?php if($type["upd_buyrate"]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_buyrate]" value="3" <?php if($type["upd_buyrate"]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_buyrateinitblock]" type="checkbox" <?php if ($check["upd_buyrateinitblock"]=="on") echo "checked"?>>
				<input name="mode[upd_buyrateinitblock]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
			  	<strong>4)&nbsp; <?php echo gettext("BUYRATEINITBLOCK");?>&nbsp;:</strong>
					<input class="form_enter" name="upd_buyrateinitblock" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_buyrateinitblock)) echo $upd_buyrateinitblock; else echo '0';?>">
				<font class="version">
				<input type="radio" NAME="type[upd_buyrateinitblock]" value="1" <?php if((!isset($type["upd_buyrateinitblock"]))|| ($type["upd_buyrateinitblock"]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_buyrateinitblock]" value="2" <?php if($type["upd_buyrateinitblock"]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_buyrateinitblock]" value="3" <?php if($type["upd_buyrateinitblock"]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_buyrateincrement]" type="checkbox" <?php if ($check["upd_buyrateincrement"]=="on") echo "checked"?>>
				<input name="mode[upd_buyrateincrement]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
			  	<strong>5) <?php echo gettext("BUYRATEINCREMENT");?>&nbsp;:</strong>
					<input class="form_enter" name="upd_buyrateincrement" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_buyrateincrement)) echo $upd_buyrateincrement; else echo '0';?>">
				<font class="version">
				<input type="radio" NAME="type[upd_buyrateincrement]" value="1" <?php if((!isset($type["upd_buyrateincrement"]))|| ($type["upd_buyrateincrement"]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_buyrateincrement]" value="2" <?php if($type["upd_buyrateincrement"]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_buyrateincrement]" value="3" <?php if($type["upd_buyrateincrement"]==3){?>checked<?php }?>>  <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_rateinitial]" type="checkbox" <?php if ($check["upd_rateinitial"]=="on") echo "checked"?>>
				<input name="mode[upd_rateinitial]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
				
				<strong>6)&nbsp;<?php echo gettext("RATE INITIAL");?>&nbsp;:</strong>
				 	<input class="form_enter" name="upd_rateinitial" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_rateinitial)) echo $upd_rateinitial; else echo '0';?>" >
				<font class="version">
				<input type="radio" NAME="type[upd_rateinitial]" value="1" <?php if((!isset($type[upd_rateinitial]))|| ($type[upd_rateinitial]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_rateinitial]" value="2" <?php if($type[upd_rateinitial]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_rateinitial]" value="3" <?php if($type[upd_rateinitial]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_initblock]" type="checkbox" <?php if ($check["upd_initblock"]=="on") echo "checked"?>>
				<input name="mode[upd_initblock]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
				
				<strong>7)&nbsp;<?php echo gettext("MIN DURATION");?>&nbsp;:</strong>
				 	<input class="form_enter" name="upd_initblock" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_initblock)) echo $upd_initblock; else echo '0';?>" >
				<font class="version">
				<input type="radio" NAME="type[upd_initblock]" value="1" <?php if((!isset($type[upd_initblock]))|| ($type[upd_initblock]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_initblock]" value="2" <?php if($type[upd_initblock]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_initblock]" value="3" <?php if($type[upd_initblock]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_billingblock]" type="checkbox" <?php if ($check["upd_billingblock"]=="on") echo "checked"?>>
				<input name="mode[upd_billingblock]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
				
				<strong>8)&nbsp;<?php echo gettext("BILLINGBLOCK");?>&nbsp;:</strong>
				 	<input class="form_enter" name="upd_billingblock" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_billingblock)) echo $upd_billingblock; else echo '0';?>" >
				<font class="version">
				<input type="radio" NAME="type[upd_billingblock]" value="1" <?php if((!isset($type[upd_billingblock]))|| ($type[upd_billingblock]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_billingblock]" value="2" <?php if($type[upd_billingblock]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_billingblock]" value="3" <?php if($type[upd_billingblock]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_connectcharge]" type="checkbox" <?php if ($check["upd_connectcharge"]=="on") echo "checked"?>>
				<input name="mode[upd_connectcharge]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
				
				<strong>9)&nbsp;<?php echo gettext("CONNECTCHARGE");?>&nbsp;:</strong>
				 	<input class="form_enter" name="upd_connectcharge" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_connectcharge)) echo $upd_connectcharge; else echo '0';?>" >
				<font class="version">
				<input type="radio" NAME="type[upd_connectcharge]" value="1" <?php if((!isset($type[upd_connectcharge]))|| ($type[upd_connectcharge]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_connectcharge]" value="2" <?php if($type[upd_connectcharge]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_connectcharge]" value="3" <?php if($type[upd_connectcharge]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>		
          <td align="left" bgcolor="#cccccc">
		  		<input name="check[upd_disconnectcharge]" type="checkbox" <?php if ($check["upd_disconnectcharge"]=="on") echo "checked"?>>
				<input name="mode[upd_disconnectcharge]" type="hidden" value="2">
		  </td>
		  <td align="left"  bgcolor="#cccccc">	
				
				<strong>10)&nbsp;<?php echo gettext("DISCONNECTCHARGE");?>&nbsp;:</strong>
				 	<input class="form_enter" name="upd_disconnectcharge" size="10" maxlength="10" style="border: 2px inset rgb(204, 51, 0);" value="<?php if (isset($upd_disconnectcharge)) echo $upd_disconnectcharge; else echo '0';?>" >
				<font class="version">
				<input type="radio" NAME="type[upd_disconnectcharge]" value="1" <?php if((!isset($type[upd_disconnectcharge]))|| ($type[upd_disconnectcharge]==1) ){?>checked<?php }?>> <?php echo gettext("Equal");?>
				<input type="radio" NAME="type[upd_disconnectcharge]" value="2" <?php if($type[upd_disconnectcharge]==2){?>checked<?php }?>> <?php echo gettext("Add");?>
				<input type="radio" NAME="type[upd_disconnectcharge]" value="3" <?php if($type[upd_disconnectcharge]==3){?>checked<?php }?>> <?php echo gettext("Substract");?>
				</font>
			</td>
		</tr>
		<tr>		
			<td align="right" bgcolor="#cccccc">
			</td>
		 	<td align="right"  bgcolor="#cccccc">		
		  
				
				<input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" value="<?php gettext(" BATCH UPDATE RATECARD ");?>" type="submit">


          
        	</td>
		</tr>
		
		</form>        
      </table>
</center>
<!-- ** ** ** ** ** Part for the Update ** ** ** ** ** -->
</div>

<?php
}
?>