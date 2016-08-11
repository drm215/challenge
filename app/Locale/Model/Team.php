<?php
	App::uses('AppModel', 'Model');
	App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
	
	class User extends AppModel {

		public $hasMany = array("Userentry");
		
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
					'message' => 'User name is required',
				 ),
				'nameRule-2' => array(
					'rule' => array('isUnique'),
					'message' => 'This User name is already registered'
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
		
		public function updateUserWins() {
			$this->updateAll(array('wins' => 0));
			
			$wins = array();
			$this->Week = ClassRegistry::init('Week');
			$this->Standing = ClassRegistry::init('Standing');
			$weeks = $this->Week->find('list', array('fields' => array('id')));
			foreach($weeks as $week) {
				$standing = $this->Standing->find('first', array('fields' => array('user_id'), 'conditions' => array('week_id' => $week), 'order' => array('points DESC'), 'recursive' => -1));
				if(isset($standing['Standing'])) {
					$User = $this->find('first', array('fields' => array('id', 'wins'), 'conditions' => array('id' => $standing['Standing']['user_id']), 'recursive' => -1));
					$User['User']['wins'] = $User['User']['wins'] + 1;
					$this->save($User);
				}
			}
		}
	}
?>