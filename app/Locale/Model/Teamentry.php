<?php
	class UserEntry extends AppModel {
	
		public $belongsTo = array(
			"Week", "User", 
			"QB" => array("className" => "Player", "foreignKey" => "qb_id"),
			"RB1" => array("className" => "Player", "foreignKey" => "rb1_id"),
			"RB2" => array("className" => "Player", "foreignKey" => "rb2_id"),
			"WR1" => array("className" => "Player", "foreignKey" => "wr1_id"),
			"WR2" => array("className" => "Player", "foreignKey" => "wr2_id"),
			"FLEX" => array("className" => "Player", "foreignKey" => "flex_id"),
			"K" => array("className" => "Player", "foreignKey" => "k_id"),
			"D" => array("className" => "Player", "foreignKey" => "d_id")
		);
		
		public function getTotalPoints() {
			$this->bindModel(array('hasMany' => array('PlayerEntry' => array('foreignKey' => false, 'conditions' => array('PlayerEntry.week_id = UserEntry.week_id', 'PlayerEntry.qb_id = UserEntry.player_id')))));
			$object = $this->find('all');
			
			return $object;
		}
		
		public $validate = array(
			'qb_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'qb_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				)
			),
			'rb1_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'rb1_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				),
				'rule-unique' => array(
					'rule' => array('validatePlayerUnique', 'rb1_id', 'rb2_id', 'flex_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
			),
			'rb2_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'rb2_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				),
				'rule-unique' => array(
					'rule' => array('validatePlayerUnique', 'rb2_id', 'rb1_id', 'flex_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
			),
			'wr1_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'wr1_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				),
				'rule-unique' => array(
					'rule' => array('validatePlayerUnique', 'wr1_id', 'wr2_id', 'flex_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
			),
			'wr2_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'wr2_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				),
				'rule-unique' => array(
					'rule' => array('validatePlayerUnique', 'wr2_id', 'wr1_id', 'flex_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
			),
			'flex_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'flex_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				),
				'rule-unique-rb' => array(
					'rule' => array('validatePlayerUnique', 'flex_id', 'rb1_id', 'rb2_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
				,
				'rule-unique-wr' => array(
					'rule' => array('validatePlayerUnique', 'flex_id', 'wr1_id', 'wr2_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
			),
			'k_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'k_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				)
			),
			'd_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'd_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				)
			)
		);
		
		public function validatePlayerNotLocked($playerId, $position, $weekId) {
			$this->Player = ClassRegistry::init('Player');
			return !$this->Player->isPlayerLocked($playerId, $position, $weekId);
		}
		
		public function validatePlayerUnique($playerId, $positionOne, $positionTwo, $positionThree) {
			if($this->data['Userentry'][$positionOne] == "") {
				return true;
			}
			if($this->data['Userentry'][$positionOne] == $this->data['Userentry'][$positionTwo] || $this->data['Userentry'][$positionOne] == $this->data['Userentry'][$positionThree]) {
				return false;
			}
			return true;
		}
	}
?>