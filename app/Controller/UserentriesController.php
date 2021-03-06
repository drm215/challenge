<?php

	/**
        @SuppressWarnings(PHPMD.StaticAccess)
    */
    class UserentriesController extends AppController {

        public function index() {
            $this->Userentry->unbindModel(array('belongsTo' => array('QB', 'RB1', 'RB2', 'WR1', 'WR2', 'F', 'K', 'D', 'User')));
            $records = $this->Userentry->find('all', array('conditions' => array('Userentry.user_id' => $this->Auth->user('id'), 'year' => Configure::read('current.year')), 'recursive' => 0));
            $data = array();
            $this->Playerentry = ClassRegistry::init('Playerentry');
            foreach ($records as $record) {

                $playersArray = array($record['Userentry']['qb_id'],$record['Userentry']['rb1_id'],$record['Userentry']['rb2_id'],$record['Userentry']['wr1_id'],$record['Userentry']['wr2_id'],$record['Userentry']['f_id'],$record['Userentry']['k_id'],$record['Userentry']['d_id']);
                $record['Playerentry'] = $this->Playerentry->getTotalPointsByWeek($record['Week']['id'], $playersArray);

                array_push($data, $record);
            }
            $this->set('records', $data);

            $this->Week = ClassRegistry::init('Week');
            $this->set('weeks', $this->Week->find('all', array('fields' => array('id', 'name'), 'recursive' => 0)));
        }

        private function getGamesSchedule($weekId) {
            $this->Game = ClassRegistry::init('Game');
            return $this->Game->getGamesByWeek($weekId);
        }

        private function copyPlayerDataFromPlayers($players, $userentry, $schools) {
            $userentry = $this->copyPlayerData('qb', '', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('rb', '1', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('rb', '2', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('wr', '1', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('wr', '2', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('f', '', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('k', '', $players, $userentry, $schools);
            $userentry = $this->copyPlayerData('d', '', $players, $userentry, $schools);
            return $userentry;
        }

        private function copyPlayerData($position, $secondaryPosition, $players, $userentry, $schools) {
            if($userentry['Userentry'][$position.$secondaryPosition.'_id'] != null && $userentry['Userentry'][$position.$secondaryPosition.'_id'] != "") {
                $userentry[strtoupper($position).$secondaryPosition] = $players[strtoupper($position)][$userentry['Userentry'][$position.$secondaryPosition.'_id']]['Player'];
                $userentry[strtoupper($position).$secondaryPosition]['School'] = $schools[$players[strtoupper($position)][$userentry['Userentry'][$position.$secondaryPosition.'_id']]['Player']['school_id']];
            }
            return $userentry;
        }

        private function getUserentry($weekId) {
            $this->Userentry->unbindModel(array('belongsTo' => array('User', 'Week')));
            $this->Userentry->QB->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->RB1->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->RB2->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->WR1->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->WR2->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->F->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->K->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->D->unbindModel(array('hasMany' => array('Playerentry')));

            $userentry = $this->Userentry->find('first', array('conditions' => array('week_id' => $weekId, 'user_id' => $this->Auth->user('id'), 'Userentry.year' => Configure::read('current.year')), 'recursive' => 2));
						if(isset($userentry['QB'])) { $userentry['QB']['locked'] = $this->Player->isPlayerLocked($userentry['QB']['id'], $weekId); }
						if(isset($userentry['RB1'])) { $userentry['RB1']['locked'] = $this->Player->isPlayerLocked($userentry['RB1']['id'], $weekId); }
						if(isset($userentry['RB2'])) { $userentry['RB2']['locked'] = $this->Player->isPlayerLocked($userentry['RB2']['id'], $weekId); }
						if(isset($userentry['WR1'])) { $userentry['WR1']['locked'] = $this->Player->isPlayerLocked($userentry['WR1']['id'], $weekId); }
						if(isset($userentry['WR2'])) { $userentry['WR2']['locked'] = $this->Player->isPlayerLocked($userentry['WR2']['id'], $weekId); }
						if(isset($userentry['F'])) { $userentry['F']['locked'] = $this->Player->isPlayerLocked($userentry['F']['id'], $weekId); }
						if(isset($userentry['K'])) { $userentry['K']['locked'] = $this->Player->isPlayerLocked($userentry['K']['id'], $weekId); }
						if(isset($userentry['D'])) { $userentry['D']['locked'] = $this->Player->isPlayerLocked($userentry['D']['id'], $weekId); }
            return $userentry;
        }

    public function detail($UserId, $weekId) {
        if (!$UserId) {
            throw new NotFoundException(__('Invalid User'));
        }
        if (!$weekId) {
            throw new NotFoundException(__('Invalid weekId'));
        }

        $this->Week = ClassRegistry::init('Week');
        $this->Playerentry = ClassRegistry::init('Playerentry');
		$this->School = ClassRegistry::init('School');
		$this->Player = ClassRegistry::init('Player');

        $record = $this->Userentry->find('first', array('conditions' => array('user_id' => $UserId, 'week_id' => $weekId, 'Userentry.year' => Configure::read('current.year'))));
		if($this->Auth->user('id') == $UserId) {
			$record['QB']['locked'] = 1;
			$record['RB1']['locked'] = 1;
			$record['RB2']['locked'] = 1;
			$record['WR1']['locked'] = 1;
			$record['WR2']['locked'] = 1;
			$record['F']['locked'] = 1;
			$record['K']['locked'] = 1;
			$record['D']['locked'] = 1;
		} else {
			$record['QB']['locked'] = $this->Player->isPlayerLocked($record['QB']['id'], $weekId);
			$record['RB1']['locked'] = $this->Player->isPlayerLocked($record['RB1']['id'], $weekId);	
			$record['RB2']['locked'] = $this->Player->isPlayerLocked($record['RB2']['id'], $weekId);
			$record['WR1']['locked'] = $this->Player->isPlayerLocked($record['WR1']['id'], $weekId);
			$record['WR2']['locked'] = $this->Player->isPlayerLocked($record['WR2']['id'], $weekId);
			$record['F']['locked'] = $this->Player->isPlayerLocked($record['F']['id'], $weekId);
			$record['K']['locked'] = $this->Player->isPlayerLocked($record['K']['id'], $weekId);
			$record['D']['locked'] = $this->Player->isPlayerLocked($record['D']['id'], $weekId);
		}
				

        $this->set('title', $record['User']['name']." (".$record['User']['owner'].") - Week ".$record['Week']['name']);
        $this->set('record', $record);

        $playerEntries = $this->Playerentry->getplayerentries($record['Userentry'], false, 1);
        $this->set('playerentries', $playerEntries);

        $playersArray = array($record['Userentry']['qb_id'],$record['Userentry']['rb1_id'],$record['Userentry']['rb2_id'],$record['Userentry']['wr1_id'],$record['Userentry']['wr2_id'],$record['Userentry']['f_id'],$record['Userentry']['k_id'],$record['Userentry']['d_id']);
        $points = $this->Playerentry->getTotalPointsByWeek($record['Userentry']['week_id'], $playersArray);
        $calculatedPoints = $points['points'] == "" ? "0" : $points['points'];
        $this->set('totalPoints', $calculatedPoints);
		$this->set('schools', $this->School->findAndAdjustIndex());
    }

    public function beforeFilter() {
        $this->Auth->allow('view','detail');
    }

    public function add($weekId) {
        $this->Player = ClassRegistry::init('Player');
        $this->Week = ClassRegistry::init('Week');
        $this->School = ClassRegistry::init('School');
				$this->Playerentry = ClassRegistry::init('Playerentry');

        $userentry = $this->getUserentry($weekId);
        $players = $this->Player->getAvailablePlayers();

        if($this->request->is('post')) {
            if(empty($userentry)) {
                $userentry = $this->Userentry->create();
                
                //$userentry['Userentry']['playoff_fl'] = $week['Week']['playoff_fl'];
            }
						$userentry['Userentry']['week_id'] = $weekId;
            $userentry['Userentry']['user_id'] = $this->Auth->user('id');
            $userentry['Userentry']['qb_id'] = $this->request->data['qb-id'];
            $userentry['Userentry']['rb1_id'] = $this->request->data['rb1-id'];
            $userentry['Userentry']['rb2_id'] = $this->request->data['rb2-id'];
            $userentry['Userentry']['wr1_id'] = $this->request->data['wr1-id'];
            $userentry['Userentry']['wr2_id'] = $this->request->data['wr2-id'];
            $userentry['Userentry']['f_id'] = $this->request->data['f-id'];
            $userentry['Userentry']['k_id'] = $this->request->data['k-id'];
            $userentry['Userentry']['d_id'] = $this->request->data['d-id'];
						$userentry['Userentry']['year'] = Configure::read('current.year');

            if ($this->Userentry->save($userentry)) {
                $this->Session->setFlash(__('Your picks has been saved.'));
                // reselect to re-fetch the associations
                $userentry = $this->getUserentry($weekId);
            } else {
                $userentry = $this->copyPlayerDataFromPlayers($players, $userentry, $this->School->findAndAdjustIndex());
               //debug($this->Userentry->validationErrors);
            }
        }

        $playerentries = array();
        if(isset($userentry['Userentry'])) {
            $playerentries = $this->Playerentry->getPlayerentries($userentry['Userentry']);
        }

        $this->set('userentry', $userentry);
        $this->set('players', json_encode($players, JSON_HEX_APOS));
        $this->set('playerentries', json_encode($playerentries, JSON_HEX_APOS));
    }
      
    public function getPlayerData($weekId, $userId, $basePosition, $position) {
		CakeLog::write('debug', '$position = ' . $position);
        $layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
        $this->autoRender = false;  // <-- NO RENDER THIS METHOD HAS NO VIEW VERY IMPORTANT!!!!!
      
        $this->Player = ClassRegistry::init('Player');
        $this->Week = ClassRegistry::init('Week');
        $this->School = ClassRegistry::init('School');
		$this->Playerentry = ClassRegistry::init('Playerentry');

        $userentry = $this->getUserentry($weekId);
        $players = $this->Player->getAvailablePlayers();
        $schedule = $this->getGamesSchedule($weekId);
        $schools = $this->School->findAndAdjustIndex();
        $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));
        $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $week['Week']['playoff_fl'], $userId);
		$playerentries = $this->Playerentry->getPlayerEntriesKeyed($position);
			
        $data = array();
        $buttonId = 0;
        // loop through all the player records and build the json array
        foreach($players[$basePosition] as $player) {
			$playerName = $player['Player']['name'];
			$previouslyPlayed = isset($userentries[$basePosition][$player['Player']['id']]);
			if($previouslyPlayed) {
				$playerName = '<span style="text-decoration:line-through">' . $playerName . '</span>';
			}
            $opponentID = $this->getOpponentID($player, $schedule);
						$espnId = $schools[$player['Player']['school_id']]['espn_id'];
            $opponent = "";
            if($opponentID != "") {
                $opponent = '<img src="../../app/webroot/img/logos/' . $schools[$opponentID]['espn_id'] . '.png" title="' . $schools[$opponentID]['name'] . '">';
            }
						
			$positionLocked = false;
			if(isset($userentry[$position]) && $userentry[$position]['locked'] == true) {
				$positionLocked = true;
			}
			/*
			switch($basePosition) {
				case 'QB';
					if(isset($userentry['QB']) && $userentry['QB']['locked'] == true) {
						$positionLocked = true;
					}
				break;
				case 'RB';
					if((isset($userentry['RB1']) && $userentry['RB1']['locked'] == true) || (isset($userentry['RB2']) && $userentry['RB2']['locked'] == true)) {
						$positionLocked = true;
					}
				break;
				case 'WR';
					if((isset($userentry['WR1']) && $userentry['WR1']['locked'] == true) || (isset($userentry['WR2']) && $userentry['WR2']['locked'] == true)) {
						$positionLocked = true;
					}
				break;
				case 'F';
					if((isset($userentry['RB1']) && $userentry['RB1']['locked'] == true) || (isset($userentry['RB2']) && $userentry['RB2']['locked'] == true) || (isset($userentry['WR1']) && $userentry['WR1']['locked'] == true) || (isset($userentry['WR2']) && $userentry['WR2']['locked'] == true)) {
						$positionLocked = true;
					}
				break;
				case 'K';
					if(isset($userentry['K']) && $userentry['K']['locked'] == true) {
						$positionLocked = true;
					}
				break;
				case 'D';
					if(isset($userentry['D']) && $userentry['D']['locked'] == true) {
						$positionLocked = true;
					}
				break;

			}*/
				$button = $this->getButton($player, $schedule, $buttonId, $positionLocked, $previouslyPlayed);
				$teamImage = '<img src="../../app/webroot/img/logos/' . $espnId . '.png" title="' . $schools[$player['Player']['school_id']]['name'] . '">';
            
					array_push($data,
                array(
                    $player['Player']['id'],
                    $player['Player']['position'],
                    $espnId,
                    $button,
										$teamImage,
										$this->getPlayerEntryTooltip($playerName, $player, $weekId, $playerentries, $schools),
                    $opponent,
                    $player[0]['SUM(points)'],
                    $player[0]['SUM(pass_yards)'],
                    $player[0]['SUM(pass_tds)'],
                    $player[0]['SUM(rush_yards)'],
                    $player[0]['SUM(rush_tds)'],
                    $player[0]['SUM(receive_yards)'],
                    $player[0]['SUM(receive_tds)'],
                    $player[0]['SUM(return_yards)'],
                    $player[0]['SUM(return_tds)'],
                    $player[0]['SUM(field_goals)'],
                    $player[0]['SUM(pat)'],
                    $player[0]['SUM(points_allowed)'],
                    $player[0]['SUM(fumble_recovery)'],
                    $player[0]['SUM(def_ints)'],
                    $player[0]['SUM(def_tds)'],
                    $player[0]['SUM(safety)']
                    )
                );
            $buttonId++;
        }
        $json = '{"data":'.$this->safe_json_encode($data).'}';
				CakeLog::write('debug', 'End getPlayerData');
        return $json;
    }
			
		private function getPlayerEntryTooltip($playerName, $player, $weekId, $playerentries, $schools) {
			$tooltip = "<div id='tooltip-playerentry-info' class='tooltip-player-info'>";
			$tooltip .= "<h3><img src='../../app/webroot/img/logos/" . $schools[$player['Player']['school_id']]['espn_id'] . ".png' /> " . $player['Player']['name'] . "</h3>";
			$tooltip .= $player['Player']['position'] . " | " . $schools[$player['Player']['school_id']]['name'];
			$tooltip .= "</div>";
			
			$table = "<table>";
			$table .= "<thead><tr><th></th>";
			switch($player['Player']['position']) {
				case "K":
					$table .= "<th colspan='2'>Kick</th><th></th></tr><tr><td>Week</td><td>FGs</td><td>PATs</td>";
					break;
				case "D":
					$table .= "<th colspan='5'>Defense</th><th></th></tr><tr><td>Week</td><td>PA</td><td>Fum</td><td>INTs</td><td>TDs</td><td>Safe</td>";
					break;
				default:
					$table .= "<th colspan='2'>Pass</th><th colspan='2'>Rush</th><th colspan='2'>Rec</th><th colspan='2'>Return</th><th></th></tr>";
					$table .= "<td>Week</td><td>Yards</td><td>TDs</td><td>Yards</td><td>TDs</td><td>Yards</td><td>TDs</td><td>Yards</td><td>TDs</td>";
					break;
			}
			$table .= "<td>Points</td></tr></thead>";
			$table .= "<tbody>";
			for($i = 1; $i < $weekId; $i++)	{
				$table .= "<tr><td>" . $i . "</td>";
				$key = $player['Player']['id'] . "-" . $i;
				if(isset($playerentries[$key])) {
					switch($player['Player']['position']) {
						case "K":
							$table .= "<td>" . $playerentries[$key]['Playerentry']['field_goals'] . "</td><td>" . $playerentries[$key]['Playerentry']['pat'] . "</td>";
							break;
						case "D":
							$table .= "<td>" . $playerentries[$key]['Playerentry']['points_allowed'] . "</td><td>" . $playerentries[$key]['Playerentry']['fumble_recovery'] . "</td><td>" . $playerentries[$key]['Playerentry']['def_ints'] . "</td><td>" . $playerentries[$key]['Playerentry']['def_tds'] . "</td><td>" . $playerentries[$key]['Playerentry']['safety'] . "</td>";
							break;
						default:
							$table .= "<td>" . $playerentries[$key]['Playerentry']['pass_yards'] . "</td><td>" . $playerentries[$key]['Playerentry']['pass_tds'] . "</td><td>" . $playerentries[$key]['Playerentry']['rush_yards'] . "</td><td>" . $playerentries[$key]['Playerentry']['rush_tds'] . "</td><td>" . $playerentries[$key]['Playerentry']['receive_yards'] . "</td><td>" . $playerentries[$key]['Playerentry']['receive_tds'] . "</td><td>" . $playerentries[$key]['Playerentry']['return_tds'] . "</td><td>" . $playerentries[$key]['Playerentry']['return_yards'] . "</td>";
							break;
					}
					$table .= "<td>" . $playerentries[$key]['Playerentry']['points'] . "</td>";
				}
				
				$table .= "</tr>";
			}		
			$table .= "</tbody>";
			$table .= "</table>";
			$tooltip .= $table;
			return '<div title="' . $tooltip . '">' . $playerName . '</div>';
		}

    private function getButton($player, $schedule, $buttonId, $positionLocked, $previouslyPlayed) {
				$element = '';
				if(!$previouslyPlayed  || $positionLocked) {
					$buttonLabel = $this->getButtonLabel($player, $schedule);
        	$disabled = $this->getDisabledAttribute($positionLocked, $buttonLabel);
					$element = '<button id="'.$buttonId.'"'.$disabled.' class="select-player">'.$buttonLabel.'</button>';
				}
				return $element;
    }

    private function getButtonLabel($player, $schedule) {
			$this->Player = ClassRegistry::init('Player');
      $label = "Locked";
      if(empty($schedule)) {
      	$label = "Inactive";
      } else if(isset($schedule[$player['Player']['school_id']])) {
				$game = $schedule[$player['Player']['school_id']]['Game'];
				$currentTime = new DateTime();
				$lockedTime = (new DateTime($game['time']))->modify('-10 minutes');
				if($currentTime < $lockedTime) {
					$label = "Select";
				}
      } else {
				$label = "Inactive";
			}
      return $label;
    }

    private function getDisabledAttribute($positionLocked, $buttonLabel) {
        $class = '';
        if($positionLocked || $buttonLabel == 'Inactive' || $buttonLabel == 'Locked') {
            $class = " disabled='disabled'";
        }
        return $class;
    }
    private function getOpponentID($player, $schedule) {
        if(isset($schedule[$player['Player']['school_id']])) {
            $awaySchoolId = $schedule[$player['Player']['school_id']]['Game']['away_school_id'];
            $homeSchoolId = $schedule[$player['Player']['school_id']]['Game']['home_school_id'];
            if($player['Player']['school_id'] == $awaySchoolId) {
                $schoolId = $homeSchoolId;
            } else {
                $schoolId = $awaySchoolId;
            }
            return $schoolId;
        }
        return "";
    }
}
?>