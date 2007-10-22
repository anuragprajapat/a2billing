<?php

/** Reverse reference Edit: control a table which references the edited entry
    We are editing table1 and have some table2 where table2.ref1 = table1.id
    So, we present here the list of table2.name..
    
*/
class RevRef {
	var $reftable;
	var $refname = 'name';
	var $refid = 'rid';
	var $refkey = 'id'; /// The (primary) key for $reftable. 
	
	var $debug_st = 1;


	/** Produce the necessary html code at the body.
	    Useful for styles, scripts etc.
	    @param $action	The form action. Sometimes, the header is only needed 
	    		for edits etc.
	   */
	public function html_body($action = null){
		if ($action == 'list')
			return;
	?>
<style>
table.FormRRt1 {
	border: thin solid black;
	color: blue;
	width: 300;
	font: Arial, Verdana;
}

table.FormRRt1 thead td{
	background: gray;
	color: white;
	font-weight: bold;
}
</style>

<script language="JavaScript" type="text/JavaScript">
<!--
function formRRdelete(rid,raction,rname, instance){
  document.myForm.form_action.value = "object-edit";
  document.myForm.sub_action.value = rid;
  document.myForm.elements[raction].value='delete';
  if (rname != null) document.myForm.elements[rname].value = instance;
  myForm.submit();
}

function formRRadd(rid,raction){
  document.myForm.form_action.value = "object-edit";
  document.myForm.sub_action.value = rid;
  document.myForm.elements[raction].value='add';
  myForm.submit();
}
//-->
</script>
	
	<?php
	}

	/** params : 5= form field name 
	*/
	public function DispEdit($scol, $sparams, $svalue, $DBHandle = null){
		$refname = $this->refname ;
		$refid = $this->refid ;
		$refkey = $this->refkey ;
		?><input type="hidden" name="<?= $sparams[5] . '_action' ?>" value="">
		<?php
		$QUERY = str_dbparams($DBHandle, "SELECT $refkey, $refname FROM $this->reftable ".
			"WHERE $refid = %1 ; ",array($svalue));
			
		$res = $DBHandle->Execute ($QUERY);
		if (! $res){
			if ($this->debug_st) {
				?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
				Error: <?= $DBHanlde->ErrorMsg() ?><br>
				<?php
			}
			echo _("No data found!");
		}else{
		?> <table class="FormRRt1">
		<thead>
		<tr><td><?= $sparams[0] ?></td><td><?= _("Action") ?></td></tr>
		</thead>
		<tbody>
		<?php while ($row = $res->fetchRow()){ ?>
			<tr><td><?= htmlspecialchars($row[1]) ?></td>
			    <td><a onClick="formRRdelete('<?= $scol ?>','<?=$sparams[5]. '_action' ?>','<?= $sparams[5] .'_del' ?>','<?= $row[0] ?>')" > <img src="../Images/icon-del.png" alt="<?= _("Remove this") ?>" /></a></td>
			</tr>
		<?php } ?>
		</tbody>
		</table>
		<input type="hidden" name="<?= $sparams[5] . '_del' ?>" value="">
		<?php
		}
		
		$this->dispAddBox($scol, $sparams, $svalue, $DBHandle);
	}
	

	/** Called by the framework when we have requested an 'object-edit'
	*/
	public function PerformObjEdit($scol, $sparams, $DBHandle = null){
		if ($this->debug_st)
			echo "PerformObjEdit stub!!\n";
	}
	
	/** By default, no addition method is defined */
	public function dispAddbox($scol, $sparams, $svalue, $DBHandle){
		if ($this->debug_st)
			echo "dispAddbox stub!!\n";
	
	}
};


/** Rev Ref, where the add field is a combo with not-refed entries.
 
 	However, we need $refoid, the (primary) key for $reftable.
 	
 	Adding an item means UPDATE $reftable SET $refid = %id ;
 	Deleting means UPDATE $reftable SET $refid = NULL;
 */
class RevRefcmb extends RevRef {
	
	public function dispAddbox($scol, $sparams, $svalue, $DBHandle ){
			// Now, find those refs NOT already in the list!
		$QUERY = "SELECT $refkey, $refname FROM $this->reftable ".
			"WHERE $refid IS NULL;";
		$res = $DBHandle->Execute ($QUERY);
		if (! $res){
			if ($this->debug_st) {
				?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
				Error: <?= $DBHanlde->ErrorMsg() ?><br>
				<?php
			}
			echo _("No additional data found!");
		}else{
			$add_combos = array(array('', _("Select one to add..")));
			while ($row = $res->fetchRow()){
				$add_combos[] = $row;
			}
			gen_Combo($sparams[5]. '_add','',$add_combos);
			 ?>
			 <a onClick="formRRadd('<?= $scol ?>','<?=$sparams[5]. '_action' ?>')"><img src="../Images/btn_Add_94x20.png" alt="<?= _("Add this") ?>" /></a>
		<?php
		}
	}
	
	
	public function PerformObjEdit($scol, $sparams, $DBHandle = null){
		$oeaction = getpost_single($sparams[5].'_action');
		if ($this->debug_st)
			echo "Object edit! Action: $oeaction <br>\n";
		$oeid = getpost_single($sparams[1]);
		switch($oeaction){
		case 'add':
			$QUERY = str_dbparams($DBHandle,"UPDATE $this->reftable SET $this->refid = %1 ".
				"WHERE $this->refkey = %2;", array($oeid, getpost_single($sparams[5].'_add')));
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				if ($this->debug_st) {
					?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
					Error: <?= $DBHanlde->ErrorMsg() ?><br>
					<?php
				}
				echo _("Could not add!");
			}else{
				if ($this->debug_st)
					echo _("Item added!");
			}
			break;
		case 'delete':
			$QUERY = str_dbparams($DBHandle,"UPDATE $this->reftable SET $this->refid = NULL ".
				"WHERE $this->refkey = %1;", array(getpost_single($sparams[5].'_del')));
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				if ($this->debug_st) {
					?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
					Error: <?= $DBHanlde->ErrorMsg() ?><br>
					<?php
				}
				echo _("Could not delete!");
			}else{
				if ($this->debug_st)
					echo _("Item deleted!");
			}
			break;
		default:
			echo "Unknown action $oeaction";
		}
	}

};

/** Rev ref, where the add field is free text.
 	
 	Adding an item means INSERT INTO $reftable ($refid , $refname) VALUES (id, txt) ;
 	Deleting means DELETE FROM $reftable WHERE $refkey = key;
 */

class RevReftxt extends RevRef {

	var $addprops="size=40 maxlength=100";
	var $addval="";
	
	public function dispAddbox($scol, $sparams, $svalue, $DBHandle){
			// Now, find those refs NOT already in the list!
		?>
		<input class="form_enter" type="INPUT" name="<?=$sparams[5]. '_new'. $this->refname ?>" value="<?= $this->addval ?>" <?= $this->addprops ?> />
		<a onClick="formRRadd('<?= $scol ?>','<?=$sparams[5]. '_action' ?>')"><img src="../Images/btn_Add_94x20.png" alt="<?= _("Add this") ?>" /></a>
		<?php
		
	}

	/** Called by the framework when we have requested an 'object-edit'
	*/
	public function PerformObjEdit($scol, $sparams, $DBHandle = NULL){
		$oeaction = getpost_single($sparams[5].'_action');
		if ($this->debug_st)
			echo "Object edit! Action: $oeaction <br>\n";
		$oeid = getpost_single($sparams[1]);
		switch($oeaction){
		case 'add':
			$QUERY = str_dbparams($DBHandle,"INSERT INTO $this->reftable ($this->refid, $this->refname) VALUES(%1, %2);",
				array($oeid, getpost_single($sparams[5].'_new' . $this->refname)));
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				if ($this->debug_st) {
					?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
					Error: <?= $DBHanlde->ErrorMsg() ?><br>
					<?php
				}
				echo _("Could not add!");
			}else{
				if ($this->debug_st)
					echo _("Item added!");
			}
			break;
		case 'delete':
			$QUERY = str_dbparams($DBHandle,"DELETE FROM $this->reftable WHERE $this->refkey = %1 ;",
				array(getpost_single($sparams[5].'_del')));
			$res = $DBHandle->Execute ($QUERY);
			if (! $res){
				if ($this->debug_st) {
					?> Query failed: <?= htmlspecialchars($QUERY) ?><br>
					Error: <?= $DBHanlde->ErrorMsg() ?><br>
					<?php
				}
				echo _("Could not delete!");
			}else{
				if ($this->debug_st)
					echo _("Item deleted!");
			}
			break;
		default:
			echo "Unknown action $oeaction";
		}
	}
};
?>