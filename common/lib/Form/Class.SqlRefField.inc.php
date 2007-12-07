<?php
require_once("Class.BaseField.inc.php");

class SqlRefField extends BaseField{
	public $field_values; ///< Array with the cached field values
	public $def_value;
	public $reftable;
	public $refname;
	public $refid ;

	function SqlRefField($fldtitle, $fldname,$reftbl, $refid = 'id', $refname = 'name', $flddescr=null, $fldwidth = null){
		$this->fieldname = $fldname;
		$this->fieldtitle = $fldtitle;
		$this->reftable = $reftbl;
		$this->refname = $refname;
		$this->refid = $refid;
		$this->listWidth = $fldwidth;
		$this->editDescr = $flddescr;
	}

	public function DispList(array &$qrow,&$form){
		echo htmlspecialchars($qrow[$this->fieldname.'_'.$this->refname]);
		if ($form->FG_DEBUG>3)
			echo " (Ref:" .htmlspecialchars($qrow[$this->fieldname]) .")";
	}
	
	public function DispAddEdit($val,&$form){
		if (!$this->field_values)
			$this->prepare($form->a2billing->DBHandle());
		gen_Combo($this->fieldname,$val,$this->field_values);
	}

	public function getDefault() {
		return $this->def_value;
	}
	public function listQueryField(&$dbhandle){
		if (!$this->does_list)
			return;
		return array($this->fieldname, $this->fieldname.'_'.$this->refname);
	}

	public function listQueryTable(&$table,&$form){
		echo "List query table!";
		$table .= ' LEFT OUTER JOIN ' .
			str_params("( SELECT %1 AS %0_%1, %2 AS %0_%2 FROM %3) AS %0_table ".
				"ON %0_%1 = %0",
			    array($this->fieldname,$this->refid,$this->refname, $this->reftable));
	}
	
	protected function prepare(&$dbhandle){
		//echo "Prepare!";
		$debug = $GLOBALS['FG_DEBUG'];
		$qry = "SELECT $this->refid,$this->refname FROM $this->reftable;";
		if ($debug>3)
			echo "Query: $qry\n";
		$res = $dbhandle->Execute($qry);
		if (!$res ){
			if ($debug>1)
				echo "Cannot fetch ref values: ". $dbhandle->ErrorMsg();
		}else
			while($row = $res->fetchRow())
				$this->field_values[] = 
					array ($row[$this->refid],$row[$this->refname]);
		if ($debug>3)
			print_r($this->field_values);
	}

};

?>