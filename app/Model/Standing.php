<?php

	/**
        @SuppressWarnings(PHPMD.StaticAccess)
    */
	class Standing extends AppModel {
	
		public $belongsTo = array("User", "Week");
		
		public function calculateStandingsByWeek() {
			echo "Calculating Standings for Week\n";
			$this->Userentry = ClassRegistry::init('Userentry');
			$this->Playerentry = ClassRegistry::init('Playerentry');
			$this->Game = ClassRegistry::init('Game');
			$game = $this->Game->find('first', array('fields' => array('MAX(week_id) AS week_id'), 'recursive' => -1));
			$weekId = $game[0]['week_id'];
			$userentries = $this->Userentry->find('all', array('conditions' => array('week_id' => $weekId, 'year' => Configure::read('current.year')), 'recursive' => -1));
				echo "Found " . count($userentries) . " to calculate.\n";
				foreach($userentries as $entry) {
					$playersArray = array($entry['Userentry']['qb_id'],$entry['Userentry']['rb1_id'],$entry['Userentry']['rb2_id'],$entry['Userentry']['wr1_id'],$entry['Userentry']['wr2_id'],$entry['Userentry']['f_id'],$entry['Userentry']['k_id'],$entry['Userentry']['d_id']);				
					$points = $this->Playerentry->getTotalPointsByWeek($weekId, $playersArray);
					
					$standing = $this->find('first', array('conditions' => array('week_id' => $weekId, 'user_id' => $entry['Userentry']['user_id'], 'year' => Configure::read('current.year')), 'recursive' => -1));
					if(count($standing) == 0) {
						$standing['Standing']['user_id'] = $entry['Userentry']['user_id'];
						$standing['Standing']['week_id'] = $weekId;
						$standing['Standing']['year'] = Configure::read('current.year');
					}
					$standing['Standing']['points'] = 0;
					if(isset($points['points'])) {
						$standing['Standing']['points'] = $points['points'];
					}
					if(!$this->save($standing)) {
						echo "Error!\n";
						return;
					}
				}
		}
		
		public function updateLowestWeek() {
			echo "Updating lowest week\n";
			$this->updateAll(array('lowest' => 0));
		
			$this->User = ClassRegistry::init('User');
			$users = $this->User->find('list', array('fields' => array('id')));
			foreach($users as $user) {
				
				$lowest = $this->find('first', array('conditions' => array('user_id' => $user, 'year' => Configure::read('current.year')), 'order' => array('points ASC'), 'recursive' => -1));
				$lowest['Standing']['lowest'] = 1;
				
				if(!$this->save($lowest)) {
					echo "Error saving lowest week\n";
				}
				$this->clear();
			}
		}
	}
?>