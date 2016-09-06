<?php
	class TeamEntry extends AppModel {
	
		public $belongsTo = array(
			"Week", "Team", 
			"QB" => array("className" => "Player", "foreignKey" => "qb_id"),
			"RB1" => array("className" => "Player", "foreignKey" => "rb1_id"),
			"RB2" => array("className" => "Player", "foreignKey" => "rb2_id"),
			"WR1" => array("className" => "Player", "foreignKey" => "wr1_id"),
			"WR2" => array("className" => "Player", "foreignKey" => "wr2_id"),
			"F" => array("className" => "Player", "foreignKey" => "f_id"),
			"K" => array("className" => "Player", "foreignKey" => "k_id"),
			"D" => array("className" => "Player", "foreignKey" => "d_id")
		);
		
		public function getTotalPoints() {
			$this->bindModel(array('hasMany' => array('Playerentry' => array('foreignKey' => false, 'conditions' => array('Playerentry.week_id = TeamEntry.week_id', 'Playerentry.qb_id = TeamEntry.player_id')))));
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
					'rule' => array('validatePlayerUnique', 'rb1_id', 'rb2_id', 'f_id'), 
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
					'rule' => array('validatePlayerUnique', 'rb2_id', 'rb1_id', 'f_id'), 
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
					'rule' => array('validatePlayerUnique', 'wr1_id', 'wr2_id', 'f_id'), 
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
					'rule' => array('validatePlayerUnique', 'wr2_id', 'wr1_id', 'f_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
			),
			'f_id' => array(
				'rule-validatePlayerNotLocked' => array(
					'rule' => array('validatePlayerNotLocked', 'f_id', 'week_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player is locked.'
				),
				'rule-unique-rb' => array(
					'rule' => array('validatePlayerUnique', 'f_id', 'rb1_id', 'rb2_id'), 
					'allowEmpty' => 'false',
					'message' => 'This player cannot be used more than once.'
				)
				,
				'rule-unique-wr' => array(
					'rule' => array('validatePlayerUnique', 'f_id', 'wr1_id', 'wr2_id'), 
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
			return !$this->Player->isPlayerLocked($playerId, $weekId);
		}
		
		public function validatePlayerUnique($playerId, $positionOne, $positionTwo, $positionThree) {
			if($this->data['Teamentry'][$positionOne] == "") {
				return true;
			}
			if($this->data['Teamentry'][$positionOne] == $this->data['Teamentry'][$positionTwo] || $this->data['Teamentry'][$positionOne] == $this->data['Teamentry'][$positionThree]) {
				return false;
			}
			return true;
		}
	}
?>