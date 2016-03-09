<?php

	include_once "Database.php";

	class RPGStats{
	
		private $_arrBaseStats;
		private $_arrSkillStats;
		private $_arrStatusEffectStats;
		private $_arrAbilityStats;
		private $_intRPGCharacterID;

		public function RPGStats($intRPGCharacterID){
			$this->_intRPGCharacterID = $intRPGCharacterID;
		}
		
		public function loadBaseStats(){
			$this->_arrBaseStats = array();
			$objDB = new Database();
			$strSQL = "SELECT intMaxHP, intStrength, intIntelligence, intAgility, intVitality, intWillpower, intDexterity, intEvasion, intCritDamage, intPierce, intBlockRate, intBlockReduction
						FROM tblcharacterstats
							WHERE intRPGCharacterID = " . $objDB->quote($this->_intRPGCharacterID);
			$rsResult = $objDB->query($strSQL);
			while($arrRow = $rsResult->fetch(PDO::FETCH_ASSOC)){
				$this->_arrBaseStats['intMaxHP'] = $arrRow['intMaxHP'];
				$this->_arrBaseStats['intStrength'] = $arrRow['intStrength'];
				$this->_arrBaseStats['intIntelligence'] = $arrRow['intIntelligence'];
				$this->_arrBaseStats['intAgility'] = $arrRow['intAgility'];
				$this->_arrBaseStats['intVitality'] = $arrRow['intVitality'];
				$this->_arrBaseStats['intWillpower'] = $arrRow['intWillpower'];
				$this->_arrBaseStats['intDexterity'] = $arrRow['intDexterity'];
				$this->_arrBaseStats['intEvasion'] = $arrRow['intEvasion'];
				$this->_arrBaseStats['intCritDamage'] = $arrRow['intCritDamage'];
				$this->_arrBaseStats['intPierce'] = $arrRow['intPierce'];
				$this->_arrBaseStats['intBlockRate'] = $arrRow['intBlockRate'];
				$this->_arrBaseStats['intBlockReduction'] = $arrRow['intBlockReduction'];
			}
		}
		
		public function saveBaseStats(){
			$objDB = new Database();
			$strSQL = "UPDATE tblcharacterstats
						SET intMaxHP = " . $objDB->quote($this->_arrBaseStats['intMaxHP']) . ",
							intStrength = " . $objDB->quote($this->_arrBaseStats['intStrength']) . ",
							intIntelligence = " . $objDB->quote($this->_arrBaseStats['intIntelligence']) . ",
							intAgility = " . $objDB->quote($this->_arrBaseStats['intAgility']) . ",
							intVitality = " . $objDB->quote($this->_arrBaseStats['intVitality']) . ",
							intWillpower = " . $objDB->quote($this->_arrBaseStats['intWillpower']) . ",
							intDexterity = " . $objDB->quote($this->_arrBaseStats['intDexterity']) . ",
							intEvasion = " . $objDB->quote($this->_arrBaseStats['intEvasion']) . ",
							intCritDamage = " . $objDB->quote($this->_arrBaseStats['intCritDamage']) . ",
							intPierce = " . $objDB->quote($this->_arrBaseStats['intPierce']) . ",
							intBlockRate = " . $objDB->quote($this->_arrBaseStats['intBlockRate']) . ",
							intBlockReduction = " . $objDB->quote($this->_arrBaseStats['intBlockReduction']) . "
							WHERE intRPGCharacterID = " . $objDB->quote($this->_intRPGCharacterID);
			$objDB->query($strSQL);
		}
		
		public function saveAbilityStats(){
			$objDB = new Database();
			$strSQL = "UPDATE tblcharacterabilitystats
						SET intStrength = " . $objDB->quote($this->_arrAbilityStats['intStrength']) . ",
							intIntelligence = " . $objDB->quote($this->_arrAbilityStats['intIntelligence']) . ",
							intAgility = " . $objDB->quote($this->_arrAbilityStats['intAgility']) . ",
							intVitality = " . $objDB->quote($this->_arrAbilityStats['intVitality']) . ",
							intWillpower = " . $objDB->quote($this->_arrAbilityStats['intWillpower']) . ",
							intDexterity = " . $objDB->quote($this->_arrAbilityStats['intDexterity']) . "
							WHERE intRPGCharacterID = " . $objDB->quote($this->_intRPGCharacterID);
			$objDB->query($strSQL);
		}
		
		public function saveAll(){
			$this->saveBaseStats();
			$this->saveAbilityStats();
		}
		
		public function loadSkillStats(){
			
		}
		
		public function loadStatusEffectStats(){
			$this->_arrStatusEffectStats = array();
		}
		
		public function loadAbilityStats(){
			$this->_arrAbilityStats = array();
			$objDB = new Database();
			$strSQL = "SELECT intStrength, intIntelligence, intAgility, intVitality, intWillpower, intDexterity
						FROM tblcharacterabilitystats
							WHERE intRPGCharacterID = " . $objDB->quote($this->_intRPGCharacterID);
			$rsResult = $objDB->query($strSQL);
			while($arrRow = $rsResult->fetch(PDO::FETCH_ASSOC)){
				$this->_arrAbilityStats['intStrength'] = $arrRow['intStrength'];
				$this->_arrAbilityStats['intIntelligence'] = $arrRow['intIntelligence'];
				$this->_arrAbilityStats['intAgility'] = $arrRow['intAgility'];
				$this->_arrAbilityStats['intVitality'] = $arrRow['intVitality'];
				$this->_arrAbilityStats['intWillpower'] = $arrRow['intWillpower'];
				$this->_arrAbilityStats['intDexterity'] = $arrRow['intDexterity'];
			}
		}
		
		public function createNewEntry(){
			$objDB = new Database();
			$strSQL = "INSERT INTO tblcharacterstats
						(intRPGCharacterID, intMaxHP, intStrength, intIntelligence, intAgility, intVitality, intWillpower, intDexterity, intEvasion, intCritDamage, intPierce, intBlockRate, intBlockReduction)
						VALUES
						(" . $objDB->quote($this->_intRPGCharacterID) . ", 10, 5, 5, 5, 5, 5, 5, 0, 150, 0, 0, 10)";
			$objDB->query($strSQL);
			
			$strSQL = "INSERT INTO tblcharacterabilitystats
						(intRPGCharacterID)
						VALUES
						(" . $objDB->quote($this->_intRPGCharacterID) . ")";
			$objDB->query($strSQL);
		}
		
		public function addToStats($strStatArrayName, $strStatName, $intStatAmount){
			if($strStatArrayName == 'Base'){
				$this->_arrBaseStats[$strStatName] += $intStatAmount;
			}
			else if($strStatArrayName == 'Skill'){
				$this->_arrSkillStats[$strStatName] += $intStatAmount;
			}
			else if($strStatArrayName == 'Status Effect'){
				$this->_arrStatusEffectStats[$strStatName] += $intStatAmount;
			}
			else if($strStatArrayName == 'Ability'){
				$this->_arrAbilityStats[$strStatName] += $intStatAmount;
			}
		}
		
		public function removeFromStats($strStatArrayName, $strStatName, $intStatAmount){
			if($strStatArrayName == 'Base'){
				$_arrBaseStats[$strStatName] -= $intStatAmount;
			}
			else if($strStatArrayName == 'Skill'){
				$_arrSkillStats[$strStatName] -= $intStatAmount;
			}
			else if($strStatArrayName == 'Status Effect'){
				$_arrStatusEffectStats[$strStatName] -= $intStatAmount;
			}
			else if($strStatArrayName == 'Ability'){
				$_arrAbilityStats[$strStatName] -= $intStatAmount;
			}
		}
		
		public function getBaseStats(){
			return $this->_arrBaseStats;
		}
		
		public function setBaseStats($strIndex, $intValue){
			$this->_arrBaseStats[$strIndex] = $intValue;
		}
		
		public function getAbilityStats(){
			return $this->_arrAbilityStats;
		}
		
		public function setAbilityStats($strIndex, $intValue){
			$this->_arrAbilityStats[$strIndex] = $intValue;
		}
		
		public function getCombinedStats($strIndex){
			return $this->_arrBaseStats[$strIndex] + $this->_arrAbilityStats[$strIndex];
		}
	}

?>