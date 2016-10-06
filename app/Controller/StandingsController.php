<?php

	/**
        @SuppressWarnings(PHPMD.StaticAccess)
    */
	class StandingsController extends AppController {

		public function index() {
			$this->Week = ClassRegistry::init('Week');
			$standings = $this->Standing->find('all', array('conditions' => array('Week.lock_time < NOW()', 'year' => Configure::read('current.year')), 'fields' => array('Standing.week_id', 'Standing.points', 'User.id', 'User.name', 'User.owner', 'User.wins')));

			$totalPointsArray = array();
			$detailsArray = array();
			foreach($standings as $standing) {
				$existingPoints = 0;
				if(isset($totalPointsArray[$standing['User']['id']])) {
					$existingPoints = $totalPointsArray[$standing['User']['id']];
				}
				$totalPointsArray[$standing['User']['id']] = $existingPoints + $standing['Standing']['points'];

				$detail = null;
				if(isset($detailsArray[$standing['User']['id']])) {
					$detail = $detailsArray[$standing['User']['id']];
				} else {
					$detail = array();
					$detail['name'] = $standing['User']['name'];
					$detail['owner'] = $standing['User']['owner'];
					$detail['wins'] = $standing['User']['wins'];
				}

				$lowest = null;
				if(isset($detail['lowest'])) {
					$lowest = $detail['lowest'];
					if($standing['Standing']['points'] < $lowest) {
						$detail['lowest'] = $standing['Standing']['points'];
					}
				} else {
					$detail['lowest'] = $standing['Standing']['points'];
				}
				$detail[$standing['Standing']['week_id']] = $standing['Standing']['points'];

				$detailsArray[$standing['User']['id']] = $detail;
			}

			if(!empty($totalPointsArray)) {
				foreach($totalPointsArray as $key => $val) {
					$detail = $detailsArray[$key];
					$val = $val - $detailsArray[$key]['lowest'];
					$totalPointsArray[$key] = $val;
				}
				arsort($totalPointsArray);

				$keys = array_keys($totalPointsArray);
				$leader = $totalPointsArray[$keys[0]];
				$playoff = $totalPointsArray[$keys[7]];
				foreach($totalPointsArray as $key => $val) {
					$detailsArray[$key]['total_points'] = $val;
					$behindLeader = $leader - $val;
					if($behindLeader == 0) {
						$behindLeader = "-";
					} else {
						 $behindLeader = round($behindLeader, 2);
					}
					$detailsArray[$key]['behind_leader'] = $behindLeader;
					$behindPlayoffs = $playoff - $val;
					if($behindPlayoffs <= 0) {
						$behindPlayoffs = "-";
					} else {
						$behindPlayoffs = round($behindPlayoffs, 2);
					}
					$detailsArray[$key]['behind_playoff'] = $behindPlayoffs;
				}
			}
			$this->set('detailsArray', $detailsArray);
			$this->set('totalPointsArray', $totalPointsArray);
			$this->set('weeks', $this->Week->find('list', array('conditions' => 'lock_time < NOW()')));
		}

		public function weekly($weekId = null) {
			$conditions = array();

			if($weekId == null) {
				array_push($conditions, 'Week.lock_time > NOW()');
			} else {
				$conditions['Week.id'] = $weekId;
			}

			$standings = array();
			$otherWeeks = array();
			
			$this->Week = ClassRegistry::init('Week');
			$week = $this->Week->find('first', array('conditions' => $conditions, 'order' => array('Week.lock_time ASC'), 'recursive' => -1));
			if(!empty($week)) {
				$this->set('week', $week);

				if($weekId == null) {
					$weekId = $week['Week']['id'];
				}
				$standings = $this->Standing->find('all', array('conditions' => array('week_id' => $weekId, 'year' => Configure::read('current.year')), 'fields' => array('SUM(Standing.points) AS points', 'User.name', 'User.owner, User.wins, User.id'), 'group' => array('Standing.user_id'), 'order' => array('points DESC')));
				$otherWeeks = $this->Week->find('all', array('conditions' => array('Week.lock_time < NOW()', 'id !=' => $weekId), 'order' => array('Week.lock_time ASC'), 'recursive' => -1));
				
			}
			$this->set('standings', $standings);
			$this->set('otherWeeks', $otherWeeks);
		}

		public function beforeFilter() {
			$this->Auth->allow('index', 'weekly', 'playoffs');
		}
	}
?>