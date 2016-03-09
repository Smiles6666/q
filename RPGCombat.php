<?php

	class RPGCombat{
	
		private $_arrCombatMessage;
		private $_intCurrPlayerWait;
		private $_intCurrEnemyWait;
		private $_objPlayer;
		private $_objEnemy;
		private $_strFirstTurn; // who gets to attack first
		private $_strCombatState; // in progress, victory, defeat, fled
		private $_strNextTurn; // who attacks next
		private $_strPrevTurn; // who attacked last
		private $_blnEnemyConsecutiveAttacks = false; // to output exhausted message
	
		public function RPGCombat($objPlayer, $objEnemy, $strFirstTurn){
			$this->_objPlayer = $objPlayer;
			$this->_objEnemy = $objEnemy;
			$this->_strFirstTurn = $strFirstTurn;
			$this->_arrCombatMessage = array();
		}
	
		public function initiateCombat(){
			$this->_arrCombatMessage[] = '<b>Attack In Progress: ' . $this->_objEnemy->getNPCName() . '</b><br/>';
			$this->_strCombatState = 'In Progress';
			$this->_intCurrPlayerWait = $this->_objPlayer->getWaitTime('Standard');
			$this->_intCurrEnemyWait = $this->_objEnemy->getWaitTime('Standard');
			if($this->_strFirstTurn == 'Player'){
				$this->_strNextTurn = 'Player';
				$this->_strPrevTurn = 'Player';
			}
			else{
				$this->_strNextTurn = 'Opponent';
				$this->_strPrevTurn = 'Opponent';
			}
		}
		
		public function determineNextTurn(){
		
			// check if anyone died
			if($this->_objEnemy->isDead()){
				$this->_strCombatState = 'Victory';
				
				// exp gain
				$intExpGain = $this->_objEnemy->getExperienceGiven();
				$this->_objPlayer->gainExperience($intExpGain);
				$this->_arrCombatMessage[] = '<br/><br/>You gained <b>' . $intExpGain . ' experience</b> from the battle.';
				
				// receive money
				if($this->_objEnemy->getGoldDropMax() != 0){
					$intMoneyGain = mt_rand($this->_objEnemy->getGoldDropMin(), $this->_objEnemy->getGoldDropMax());
					$this->_objPlayer->receiveGold($intMoneyGain);
					$this->_arrCombatMessage[] = '<br/>You picked up <b>' . $intMoneyGain . ' gold</b> from the enemy\'s remains.';
				}
				
				// roll for loot
				$arrDrops = $this->_objEnemy->getRandomDrops();
				$intCounter = 1;
				foreach($arrDrops as $key => $value){
					$this->_objPlayer->giveItem($key);
					$this->_arrCombatMessage[] = '<br/><br/>Loot received:<br/>' . $intCounter . ') ' . $value . '<br/>';
					$intCounter++;
				}
				
			}
			else if($this->_objPlayer->isDead()){
				$this->_strCombatState = 'Defeat';
			}
			else{
				// determine the next turn using AGI formula
				if($this->_intCurrPlayerWait <= $this->_intCurrEnemyWait){
					$this->_strNextTurn = 'Player';
				}
				else{
					$this->_strNextTurn = 'Opponent';
					// consecutive opponent attacks means you were too exhausted to move
					if($this->_strPrevTurn == 'Opponent' && $this->_strNextTurn == 'Opponent'){
						$this->_arrCombatMessage[] = "<br/><b>Player Turn:</b> You stop to catch your breath, too exhausted to move. ";
						if($this->_objPlayer->getBMI() > 40 && $this->_objPlayer->getBMI() <= 60){
							$this->_arrCombatMessage[] = "Clearly the excess pounds have really done a number on your stamina.";
						}
						else if($this->_objPlayer->getBMI() > 60 && $this->_objPlayer->getBMI() <= 80){
							$this->_arrCombatMessage[] = "Your belly heaves up and down with each deep breath you take. Fighting with this much extra weight puts you at a massive disadvantage on the battlefield.";
						}
						else if($this->_objPlayer->getBMI() > 80){
							$this->_arrCombatMessage[] = "Despite your best efforts, maneuvering your oversized body in combat has proven to be too much for you. As your enemy prepares to charge at you again, you wonder if your sluggishness on the battlefield may lead to your demise.";
						}
					}
				}
			}
			
		}
		
		public function playerAttack(){
			$this->_strPrevTurn = "Player";
			$this->_intCurrPlayerWait += $this->_objPlayer->getWaitTime('Standard');
			
			// block or evade based on shield
			if($this->_objEnemy->getEquippedSecondary()->getItemType() == 'Shield'){
				$blnShield = true;
				$intEnemyBlockRoll = mt_rand(1, 100);
			}
			else{
				$blnShield = false;
				$intEnemyBlockRoll = mt_rand(1, 200);
			}
			
			$intPlayerCritRoll = mt_rand(1, 100);
			
			if($intEnemyBlockRoll <= $this->_objEnemy->getModifiedBlockRate()){
				if($blnShield){
					$intBlockedDamageModifier = $this->_objPlayer->getModifiedBlock();
				}
				else{
					$intBlockedDamageModifier = 0.0;
				}
				$blnBlocked = true;
			}
			else{
				$intBlockedDamageModifier = 1.0;
				$blnBlocked = false;
			}
			
			if($intPlayerCritRoll <= $this->_objPlayer->getModifiedCritRate()){
				$intCritDamageModifier = $this->_objPlayer->getModifiedCritDamage();
				$blnCrit = true;
			}
			else{
				$intCritDamageModifier = 1.0;
				$blnCrit = false;
			}
			
			if($this->_objPlayer->getEquippedWeapon()->getStatDamage() === null || $this->_objPlayer->getEquippedWeapon()->getStatDamage() == 'Strength'){
				$intDamage = $this->_objEnemy->takeDamage(round((($this->_objPlayer->getModifiedDamage() * $intCritDamageModifier) - $this->_objEnemy->getModifiedDefence()) * $intBlockedDamageModifier));
			}
			else if($this->_objPlayer->getEquippedWeapon()->getStatDamage() == 'Intelligence'){
				$intDamage = $this->_objEnemy->takeDamage(round((($this->_objPlayer->getModifiedMagicDamage() * $intCritDamageModifier) - $this->_objEnemy->getModifiedMagicDefence()) * $intBlockedDamageModifier));	
			}
			
			if($blnBlocked && !$blnCrit){
				$this->_arrCombatMessage[] = "<br/><b>Player Turn:</b> You attack " . $this->_objEnemy->getNPCName() . ", but you are " . ($blnShield ? "blocked" : "dodged") . ", dealing " . $intDamage . " damage.";
			}
			else if($blnBlocked && $blnCrit){
				$this->_arrCombatMessage[] = "<br/><b>Player Turn:</b> You land a critical attack against " . $this->_objEnemy->getNPCName() . ", but it is " . ($blnShield ? "blocked" : "dodged") . " dealing " . $intDamage . " damage.";
			}
			else if(!$blnBlocked && $blnCrit){
				$this->_arrCombatMessage[] = "<br/><b>Player Turn:</b> You land a critical attack against " . $this->_objEnemy->getNPCName() . ", inflicting " . $intDamage . " damage.";
			}
			else if(!$blnBlocked && !$blnCrit){
				$this->_arrCombatMessage[] = '<br/><b>Player Turn:</b> You attack ' . $this->_objEnemy->getNPCName() . ', dealing ' . $intDamage . ' damage.';
			}
		}
		
		public function playerDefend(){
			// todo
		}
		
		public function playerSkill(){
			// todo
		}
		
		public function playerWait(){
			// todo
		}
		
		public function playerFlee(){
			$this->_arrCombatMessage[] = '<br/>You fled from the battle.';
			$this->_strCombatState = 'Fled';
		}
		
		public function enemyTurn(){
			// todo: include AI file
			$this->_strPrevTurn = "Opponent";
			$this->_intCurrEnemyWait += $this->_objEnemy->getWaitTime('Standard');
			
			// block or evade based on shield
			if($this->_objPlayer->getEquippedSecondary()->getItemType() == 'Shield'){
				$blnShield = true;
				$intPlayerBlockRoll = mt_rand(1, 100);
			}
			else{
				$blnShield = false;
				$intPlayerBlockRoll = mt_rand(1, 200);
			}
			
			$intEnemyCritRoll = mt_rand(1, 100);
			
			if($intPlayerBlockRoll <= $this->_objPlayer->getModifiedBlockRate()){
				if($blnShield){
					$intBlockedDamageModifier = $this->_objPlayer->getModifiedBlock();
				}
				else{
					$intBlockedDamageModifier = 0.0;
				}
				$blnBlocked = true;
			}
			else{
				$intBlockedDamageModifier = 1.0;
				$blnBlocked = false;
			}
			
			if($intEnemyCritRoll <= $this->_objEnemy->getModifiedCritRate()){
				$intCritDamageModifier = $this->_objEnemy->getModifiedCritDamage();
				$blnCrit = true;
			}
			else{
				$intCritDamageModifier = 1.0;
				$blnCrit = false;
			}
			
			if($this->_objEnemy->getEquippedWeapon()->getStatDamage() === null || $this->_objEnemy->getEquippedWeapon()->getStatDamage() == 'Strength'){
				$intDamage = $this->_objPlayer->takeDamage(round((($this->_objEnemy->getModifiedDamage() * $intCritDamageModifier) - $this->_objPlayer->getModifiedDefence()) * $intBlockedDamageModifier));
			}
			else if($this->_objEnemy->getEquippedWeapon()->getStatDamage() == 'Intelligence'){
				$intDamage = $this->_objPlayer->takeDamage(round((($this->_objEnemy->getModifiedMagicDamage() * $intCritDamageModifier) - $this->_objPlayer->getModifiedMagicDefence()) * $intBlockedDamageModifier));
			}
			
			if($blnBlocked && !$blnCrit){
				$this->_arrCombatMessage[] = "<br/><b>Enemy Turn:</b> " . $this->_objEnemy->getNPCName() . " attacks you, but you manage to " . ($blnShield ? "block" : "dodge") . " it, taking " . $intDamage . " damage.";
			}
			else if($blnBlocked && $blnCrit){
				$this->_arrCombatMessage[] = "<br/><b>Enemy Turn:</b> " . $this->_objEnemy->getNPCName() . " lands a critical attack against you, but you " . ($blnShield ? "block" : "dodge") . " it, taking " . $intDamage . " damage.";
			}
			else if(!$blnBlocked && $blnCrit){
				$this->_arrCombatMessage[] = "<br/><b>Enemy Turn:</b> " . $this->_objEnemy->getNPCName() . " lands a critical attack against you, inflicting " . $intDamage . " damage.";
			}
			else if(!$blnBlocked && !$blnCrit){
				$this->_arrCombatMessage[] = '<br/><b>Enemy Turn:</b> ' . $this->_objEnemy->getNPCName() . ' attacks you, dealing ' . $intDamage . ' damage.';
			}
		}
		
		public function getPlayer(){
			return $this->_objPlayer;
		}
		
		public function getEnemy(){
			return $this->_objEnemy;
		}
		
		public function getCombatMessage(){
			return $this->_arrCombatMessage;
		}
		
		public function getCombatState(){
			return $this->_strCombatState;
		}
		
		public function getFirstTurn(){
			return $this->_strFirstTurn;
		}
		
		public function getNextTurn(){
			return $this->_strNextTurn;
		}
		
	}

?>