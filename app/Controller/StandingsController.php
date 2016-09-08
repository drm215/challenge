<?php
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

		public function playoffs() {
			$standings = $this->Standing->find('all', array('conditions' => array('Week.playoff_fl = 0', 'year' => Configure::read('current.year')), 'fields' => array('Standing.week_id', 'Standing.points', 'User.id', 'User.name', 'User.owner', 'User.wins')));
			$playoffStandings = $this->Standing->find('all', array('conditions' => array('Week.playoff_fl = 1', 'year' => Configure::read('current.year')), 'fields' => array('Standing.week_id', 'Standing.points', 'User.id', 'User.name', 'User.owner', 'User.wins')));
			$detailsArray = array();
			$regularPointsArray = array();
			foreach($standings as $standing) {
				$existingPoints = 0;
				if(isset($regularPointsArray[$standing['User']['id']])) {
					$existingPoints = $regularPointsArray[$standing['User']['id']];
				}
				$regularPointsArray[$standing['User']['id']] = $existingPoints + $standing['Standing']['points'];

				$detail = null;
				if(isset($detailsArray[$standing['User']['id']])) {
					$detail = $detailsArray[$standing['User']['id']];
				} else {
					$detail = array();
					$detail['name'] = $standing['User']['name'];
					$detail['owner'] = $standing['User']['owner'];
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

			foreach($regularPointsArray as $key => $val) {
				$detail = $detailsArray[$key];
				$val = $val - $detailsArray[$key]['lowest'];
				$regularPointsArray[$key] = $val;
			}
			arsort($regularPointsArray);

			foreach($playoffStandings as $row) {
				$detail = $detailsArray[$row['User']['id']];
				$detail[$row['Standing']['week_id']] = $row['Standing']['points'];
				$detailsArray[$row['User']['id']] = $detail;
			}

			$playoffPositions = 8;
			$counter = 0;
			$bonusPointsIncrement = 5;
			$playoffPointsArray = array();

			foreach($regularPointsArray as $key => $val) {
				if($counter >= $playoffPositions) {
					unset($regularPointsArray[$key]);
					unset($detailsArray[$key]);
				} else {
					$detail = $detailsArray[$key];

					$detail['bonus_points'] = ($playoffPositions - $counter - 1) * $bonusPointsIncrement;
					$week11 = 0;
					$week12 = 0;
					$week13 = 0;
					$week14 = 0;

					if(isset($detail[11])) {
						$week11 = $detail[11];
					}
					if(isset($detail[12])) {
						$week12 = $detail[12];
					}
					if(isset($detail[13])) {
						$week13 = $detail[13];
					}
					if(isset($detail[14])) {
						$week14 = $detail[14];
					}

					$detail['playoff_points'] = $week11 + $week12 + $detail['bonus_points'];
					$playoffPointsArray[$key] = $detail['playoff_points'];
				}
				$detailsArray[$key] = $detail;
				$counter++;
			}
			arsort($playoffPointsArray);

			$positions = 2;
			$counter = 0;
			$keys = array_keys($playoffPointsArray);
			$leader = $playoffPointsArray[$keys[$positions - 1]];
			foreach($playoffPointsArray as $key => $val) {
				$detailsArray[$key]['points_behind'] = $detailsArray[$key]['playoff_points'] - $leader;
				$counter++;
			}

			$this->set('detailsArray', $detailsArray);
			$this->set('playoffPointsArray', $playoffPointsArray);
		}

		public function beforeFilter() {
			$this->Auth->allow('index', 'weekly', 'playoffs');
		}
	}
?>