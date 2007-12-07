<?php
require_once("Class.BaseField.inc.php");

class RefField extends BaseField{
	public $field_values; ///< Array with the field values

	function RefField($fldtitle, $fldname,$fldvals, $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->field_values = $fldvals;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		$val = $qrow[$this->fieldname];
		foreach ($this->field_values as $fval)
			if($fval[0] == $val){
			echo htmlspecialchars($fval[1]);
			return;
		}
		
		if ($form->FG_DEBUG>0)
			echo "Unknown val: " .$val ;
	}
	
	public function DispAddEdit($val,&$form){
		gen_Combo($this->fieldname,$val,$this->field_values);
	}

};

?>