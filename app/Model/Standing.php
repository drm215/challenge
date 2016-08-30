<?php
	class Standing extends AppModel {
	
		public $belongsTo = array("User", "Week");
		
		public function calculateStandingsByWeek($weekId = null) {
			echo "Calculating Standings for Week ".$weekId."\n";
			if($weekId != null) {
				
				$this->Userentry = ClassRegistry::init('Userentry');
				$this->Playerentry = ClassRegistry::init('Playerentry');
				echo "Current year: " . Configure::read('current.year') . "\n";
				$userentries = $this->Userentry->find('all', array('conditions' => array('week_id' => $weekId, 'year' => Configure::read('current.year')), 'recursive' => -1));
				echo "Found " . count($userentries) . " to calculate.\n";
				foreach($userentries as $entry) {
					$playersArray = array($entry['Userentry']['qb_id'],$entry['Userentry']['rb1_id'],$entry['Userentry']['rb2_id'],$entry['Userentry']['wr1_id'],$entry['Userentry']['wr2_id'],$entry['Userentry']['f_id'],$entry['Userentry']['k_id'],$entry['Userentry']['d_id']);				
					$points = $this->Playerentry->getTotalPointsByWeek($weekId, $playersArray);
					
					$standing = $this->find('first', array('conditions' => array('week_id' => $weekId, 'user_id' => $entry['Userentry']['user_id'], 'year' => Configure::write('current.year')), 'recursive' => -1));
					if(count($standing) == 0) {
						$standing['Standing']['user_id'] = $entry['Userentry']['user_id'];
						$standing['Standing']['week_id'] = $weekId;
					}
					if(isset($points['points'])) {
						$standing['Standing']['points'] = $points['points'];
					} else {
						$standing['Standing']['points'] = 0;
					}
					
					if(!$this->save($standing)) {
						echo "Error!\n";
						
					} else {
						$this->clear();
					}
				}
			}
		}
		
		public function updateLowestWeek() {
			echo "Updating lowest week\n";
			$this->updateAll(array('lowest' => 0));
		
			$this->User = ClassRegistry::init('User');
			$Users = $this->User->find('list', array('fields' => array('id')));
			foreach($Users as $User) {
				
				$lowest = $this->find('first', array('conditions' => array('user_id' => $User, 'year' => Configure::write('current.year')), 'order' => array('points ASC'), 'recursive' => -1));
				$lowest['Standing']['lowest'] = 1;
				
				if(!$this->save($lowest)) {
					echo "Error saving lowest week\n";
				}
				$this->clear();
			}
		}
	}
?>