<?php
/* Volatile fields:
 These fields perform a differential update,
 so that intermediate values don't get reset.
*/

class IntVolField extends IntField{

	public function DispAddEdit($val,&$form){
	?><input type="hidden" name="<?= $this->fieldname .'_old' ?>" value="<?=
		htmlspecialchars($val);?>" />
	<input type="text" name="<?= $this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}

	public function buildUpdate(&$upd_arr,&$form){
		if (!$this->does_edit)
			return;
		$val = $form->getpost_dirty($this->fieldname);
		$val_old = $form->getpost_dirty($this->fieldname.'_old');
		if ($val != $val_old)
			$upd_arr[] = str_dbparams($form->a2billing->DBHandle(),
			    "$this->fieldname = %#1 + ($this->fieldname - %#2)",
			     array($val,$val_old));
	}

};

class FloatVolField extends FloatField{

	public function DispAddEdit($val,&$form){
	?><input type="hidden" name="<?= $this->fieldname .'_old' ?>" value="<?=
		htmlspecialchars($val);?>" />
	<input type="text" name="<?= $this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= $this->editDescr?></div>
	<?php
	}

	public function buildUpdate(&$upd_arr,&$form){
		if (!$this->does_edit)
			return;
		$val = $form->getpost_dirty($this->fieldname);
		$val_old = $form->getpost_dirty($this->fieldname.'_old');
		if ($val != $val_old)
			$upd_arr[] = str_dbparams($form->a2billing->DBHandle(),
			    "$this->fieldname = %#1 + ($this->fieldname - %#2)",
			     array($val,$val_old));
	}

};


?>
