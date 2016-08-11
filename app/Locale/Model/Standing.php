<?php
	class Standing extends AppModel {
	
		public $belongsTo = array("User", "Week");

		public function calculateStandingsByWeek($weekId = null) {
			if($weekId != null) {
				
				$this->Userentry = ClassRegistry::init('Userentry');
				$this->Playerentry = ClassRegistry::init('Playerentry');
				
				$data = array();
				
				$UserEntries = $this->Userentry->find('all', array('conditions' => array('week_id' => $weekId), 'recursive' => -1));
				foreach($UserEntries as $entry) {
					$playersArray = array($entry['Userentry']['qb_id'],$entry['Userentry']['rb1_id'],$entry['Userentry']['rb2_id'],$entry['Userentry']['wr1_id'],$entry['Userentry']['wr2_id'],$entry['Userentry']['flex_id'],$entry['Userentry']['k_id'],$entry['Userentry']['d_id']);				
					$points = $this->Playerentry->getTotalPointsByWeek($weekId, $playersArray);
					
					$standing = $this->find('first', array('conditions' => array('week_id' => $weekId, 'user_id' => $entry['Userentry']['user_id']), 'recursive' => -1));
					if(count($standing) == 0) {
						$standing['Standing']['user_id'] = $entry['Userentry']['user_id'];
						$standing['Standing']['week_id'] = $weekId;
					}
					$standing['Standing']['points'] = $points['points'];
					
					array_push($data, $standing);
				}
				$this->saveMany($data);
			}
		}
		
		public function updateLowestWeek() {
			$this->updateAll(array('lowest' => 0));
		
			$this->User = ClassRegistry::init('User');
			$Users = $this->User->find('list', array('fields' => array('id')));
			foreach($Users as $User) {
				
				$lowest = $this->find('first', array('conditions' => array('user_id' => $User), 'order' => array('points ASC'), 'recursive' => -1));
				$lowest['Standing']['lowest'] = 1;
				
				$this->save($lowest);
			}
		}
	}
?>