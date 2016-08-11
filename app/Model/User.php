<?php
	App::uses('AppModel', 'Model');
	App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
	
	class User extends AppModel {
		
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
					'message' => 'This user name is already registered'
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
	}
?>