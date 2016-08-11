<?php
	App::uses('AppModel', 'Model');
	App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
	
	class Team extends AppModel {

		public $hasMany = array("Teamentry");
		
		 public $validate = array(
			'email' => array(
				'emailRule-1' => array(
					'rule' => 'email',
					'message' => 'An email is required',
				 ),
				'emailRule-2' => array(
					'rule' => array('isUnique'),
					'message' => 'This email address is already registered'
				),
			),

			'password' => array(
				'required' => array(
					'rule' => array('notEmpty'),
					'message' => 'A password is required'
				),
				'passwordverify' => array( 
					'rule' => array('identicalFieldValues', 'verifypassword' ), 
					'message' => 'Please re-enter your password twice so that the values match' 
                ) 
			),
			'name' => array(
				'nameRule-1' => array(
					'rule' => array('maxLength', 60),
					'message' => 'Team name is required',
				 ),
				'nameRule-2' => array(
					'rule' => array('isUnique'),
					'message' => 'This team name is already registered'
				)
			),
			'owner' => array(
				'ownerRule-1' => array(
					'rule' => array('maxLength', 60),
					'message' => 'Your name is required',
				 ),
				'ownerRule-2' => array(
					'rule' => array('isUnique'),
					'message' => 'Your name is already registered'
				)
			)
		);
		
		public function beforeSave($options = array()) {
			if (isset($this->data[$this->alias]['password'])) {
				$passwordHasher = new BlowfishPasswordHasher();
				$this->data[$this->alias]['password'] = $passwordHasher->hash(
					$this->data[$this->alias]['password']
				);
			}
			return true;
		}
		
		function identicalFieldValues($field = array(), $compare_field = null) { 
			foreach( $field as $key => $value ){ 
				$v1 = $value; 
				$v2 = $this->data[$this->name][ $compare_field ];     
				if($v1 !== $v2) { 
					return FALSE; 
				} else { 
					continue; 
				} 
			} 
			return TRUE; 
		} 
		
		public function updateTeamWins() {
			$this->updateAll(array('wins' => 0));
			
			$wins = array();
			$this->Week = ClassRegistry::init('Week');
			$this->Standing = ClassRegistry::init('Standing');
			$weeks = $this->Week->find('list', array('fields' => array('id')));
			foreach($weeks as $week) {
				$standing = $this->Standing->find('first', array('fields' => array('team_id'), 'conditions' => array('week_id' => $week), 'order' => array('points DESC'), 'recursive' => -1));
				if(isset($standing['Standing'])) {
					$team = $this->find('first', array('fields' => array('id', 'wins'), 'conditions' => array('id' => $standing['Standing']['team_id']), 'recursive' => -1));
					$team['Team']['wins'] = $team['Team']['wins'] + 1;
					$this->save($team);
				}
			}
		}
	}
?>