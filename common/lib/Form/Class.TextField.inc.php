<?php
require_once("Class.BaseField.inc.php");

class TextField extends BaseField{
	public $def_value;

	function TextField($fldtitle, $fldname, $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname]);
	}
	
	public function DispAddEdit($val,&$form){
	?><input type="text" name="<?= $this->fieldname ?>" value="<?=
		htmlspecialchars($val);?>" />
	<div class="descr"><?= htmlspecialchars($this->editDescr)?></div>
	<?php
	}
	
	public function getDefault() {
		return $this->def_value;
	}


};

/** Text field, which will hyperlink to the Edit page */
class TextFieldEH extends TextField{
	public $message = null;
	
	public function DispList(array &$qrow,&$form){
		if ($this->message)
			$msg=$this->message;
		else
			$msg=str_params(_("Edit this %1"),array($form->model_name_s),1);
			
		echo '<a href="'. $form->askeditURL($qrow) . '" title="'.$msg .'">';
		echo htmlspecialchars($qrow[$this->fieldname]);
		echo '</a>';
	}

};

class TextAreaField extends TextField{
	public $listLimit;

	function TextAreaField($fldtitle, $fldname, $llimit=30, $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->listWidth = $fldwidth;
		$this->listLimit = $llimit;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		if (strlen($qrow[$this->fieldname])>$this->listLimit)
			echo substr(htmlspecialchars($qrow[$this->fieldname]), 1, $this->listLimit). '...';
		else
			echo htmlspecialchars($qrow[$this->fieldname]);
	}
	
	public function DispAddEdit($val,&$form){
	?><textarea name="<?= $this->fieldname ?>" rows=5 cols=40><?=
		htmlspecialchars($val);?></textarea>
	<div class="descr"><?= htmlspecialchars($this->editDescr)?></div>
	<?php
	}

};

/** Text field, allows for null values (if empty */
class TextFieldN extends TextField{
	public function buildInsert(&$ins_arr,&$form){
		if (!$this->does_add)
			return;
		$val = $form->getpost_dirty($this->fieldname);
		if (!strlen($val))
			$val = null;
		$ins_arr[] = array($this->fieldname, $val);
	}

	public function buildUpdate(&$ins_arr,&$form){
		if (!$this->does_edit)
			return;
		$val = $form->getpost_dirty($this->fieldname);
		if (!strlen($val))
			$val = null;
		$ins_arr[] = array($this->fieldname, $val);
	}
};

?>