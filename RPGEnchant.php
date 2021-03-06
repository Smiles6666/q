<?php

require_once "Database.php";
include_once "RPGStatChange.php";

class RPGEnchant{

	private $_intEnchantID;
	private $_strEnchantName;
	private $_strEnchantType;
	private $_arrStatChanges;
	private $_arrFixedStatChanges;
	private $_dtmCreatedOn;
	private $_strCreatedBy;
	private $_dtmModifiedOn;
	private $_strModifiedBy;
	
	public function RPGEnchant($intEnchantID = null){
		if($intEnchantID != null){
			$this->loadEnchantInfo($intEnchantID);
		}
	}
	
	private function populateVarFromRow($arrEnchantInfo){
		$this->setEnchantID($arrEnchantInfo['intEnchantID']);
		$this->setEnchantName($arrEnchantInfo['strEnchantName']);
		$this->setEnchantType($arrEnchantInfo['strEnchantType']);
		$this->setCreatedOn($arrEnchantInfo['dtmCreatedOn']);
		$this->setCreatedBy($arrEnchantInfo['strCreatedBy']);
		$this->setModifiedOn($arrEnchantInfo['dtmModifiedOn']);
		$this->setModifiedBy($arrEnchantInfo['strModifiedBy']);
		$this->setStatChanges(array());
	}
	
	private function loadEnchantInfo($intEnchantID){
		$objDB = new Database();
		$arrEnchantInfo = array();
			$strSQL = "SELECT *
						FROM tblenchant
							WHERE intEnchantID = " . $objDB->quote($intEnchantID);
			$rsResult = $objDB->query($strSQL);
			while ($arrRow = $rsResult->fetch(PDO::FETCH_ASSOC)){
				$arrEnchantInfo['intEnchantID'] = $arrRow['intEnchantID'];
				$arrEnchantInfo['strEnchantName'] = $arrRow['strEnchantName'];
				$arrEnchantInfo['strEnchantType'] = $arrRow['strEnchantType'];
				$arrEnchantInfo['dtmCreatedOn'] = $arrRow['dtmCreatedOn'];
				$arrEnchantInfo['strCreatedBy'] = $arrRow['strCreatedBy'];
				$arrEnchantInfo['dtmModifiedOn'] = $arrRow['dtmModifiedOn'];
				$arrEnchantInfo['strModifiedBy'] = $arrRow['strModifiedBy'];
			}
		$this->populateVarFromRow($arrEnchantInfo);
		$this->loadStatChanges();
		$this->loadFixedStatChanges();
	}
	
	private function loadStatChanges(){
		$objDB = new Database();
		$strSQL = "SELECT * FROM tblenchantstatchanges
					WHERE intEnchantID = " . $this->_intEnchantID . "
						AND intStatusEffectID IS NOT NULL";
		$rsResult = $objDB->query($strSQL);
		while($arrRow = $rsResult->fetch(PDO::FETCH_ASSOC)){
			$this->_arrStatChanges[$arrRow['intEnchantStatChangeID']] = new RPGStatChange($arrRow['intEnchantStatChangeID']);
		}
	}
	
	private function loadFixedStatChanges(){
		$objDB = new Database();
		$strSQL = "SELECT * FROM tblenchantstatchanges
					WHERE intEnchantID = " . $this->_intEnchantID . "
						AND intStatusEffectID IS NULL";
		$rsResult = $objDB->query($strSQL);
		while($arrRow = $rsResult->fetch(PDO::FETCH_ASSOC)){
			if($arrRow['strStatName'] != NULL){
				$this->_arrFixedStatChanges[$arrRow['strStatName']] = $arrRow['intStatChangeMax'];
			}
		}
	}
	
	public function getStatChanges(){
		return $this->_arrStatChanges;
	}
	
	public function setStatChanges($arrStatChanges){
		$this->_arrStatChanges = $arrStatChanges;
	}
	
	public function getFixedStatChanges($strIndex){
		if(isset($this->_arrFixedStatChanges[$strIndex])){
			return $this->_arrFixedStatChanges[$strIndex];
		}
		else{
			return 0;
		}
	}
	
	public function getEnchantID(){
		return $this->_intEnchantID;
	}
	
	public function setEnchantID($intEnchantID){
		$this->_intEnchantID = $intEnchantID;
	}
	
	public function getEnchantName(){
		return $this->_strEnchantName;
	}
	
	public function setEnchantName($strEnchantName){
		$this->_strEnchantName = $strEnchantName;
	}
	
	public function getEnchantType(){
		return $this->_strEnchantType;
	}
	
	public function setEnchantType($strEnchantType){
		$this->_strEnchantType = $strEnchantType;
	}
	
	public function getCreatedOn(){
		return $this->_dtmCreatedOn;
	}
	
	public function setCreatedOn($dtmCreatedOn){
		$this->_dtmCreatedOn = $dtmCreatedOn;
	}
	
	public function getCreatedBy(){
		return $this->_strCreatedBy;
	}
	
	public function setCreatedBy($strCreatedBy){
		$this->_strCreatedBy = $strCreatedBy;
	}
	
	public function getModifiedOn(){
		return $this->_dtmModifiedOn;
	}
	
	public function setModifiedOn($dtmModifiedOn){
		$this->_dtmModifiedOn = $dtmModifiedOn;
	}
	
	public function getModifiedBy(){
		return $this->_strModifiedBy;
	}
	
	public function setModifiedBy($strModifiedBy){
		$this->_strModifiedBy = $strModifiedBy;
	}
}

?>