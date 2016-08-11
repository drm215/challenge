<?php
	class StandingsController extends AppController {
		
		public function index() {
			
			$count = 1;
			$playoffPositions = 8;
			$standings = array();
			$leaderPoints = 0;
			$records = $this->Standing->find('all', array('conditions' => array('lowest' => 0), 'fields' => array('SUM(Standing.points) AS points', 'User.name', 'User.owner, User.wins, User.id'), 'group' => array('Standing.user_id'), 'order' => array('points DESC')));
			
			if(count($records) > 0) {
				
				$playoffPoints = $records[$playoffPositions-1][0]['points'];
				foreach($records as $record) {
					
					if($count == 1) {
						$record[0]['points_behind'] = '-';
						$leaderPoints = $record[0]['points'];
					} else {
						$record[0]['points_behind'] = $leaderPoints - $record[0]['points'];
					}
					if($count == $playoffPositions) {
						$record[0]['playoff_points'] = '-';
					} else {
						$record[0]['playoff_points'] = $record[0]['points'] - $playoffPoints;
					}
					
					array_push($standings, $record);
					
					$count++;
				}
			}
			$this->set('standings', $standings);
		}
		
		public function weekly($weekId = null) {
			$conditions = array();
			
			if($weekId == null) {
				array_push($conditions, 'Week.lock_time > NOW()');
			} else {
				$conditions['Week.id'] = $weekId;
			}
		
			$this->Week = ClassRegistry::init('Week');
			$week = $this->Week->find('first', array('conditions' => $conditions, 'order' => array('Week.lock_time ASC'), 'recursive' => -1));
			$this->set('week', $week);
			
			if($weekId == null) {
				$weekId = $week['Week']['id'];
			}
			
			$standings = $this->Standing->find('all', array('conditions' => array('week_id' => $weekId), 'fields' => array('SUM(Standing.points) AS points', 'User.name', 'User.owner, User.wins, User.id'), 'group' => array('Standing.user_id'), 'order' => array('points DESC')));
			$this->set('standings', $standings);
			
			$otherWeeks = $this->Week->find('all', array('conditions' => array('Week.lock_time < NOW()', 'id !=' => $weekId), 'order' => array('Week.lock_time ASC'), 'recursive' => -1));
			$this->set('otherWeeks', $otherWeeks);
		}
		
		public function beforeFilter() {
			$this->Auth->allow('index', 'weekly');
		}
	}
?>