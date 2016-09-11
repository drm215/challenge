<?php
    class UserentriesController extends AppController {

        public function index() {
            $this->Userentry->unbindModel(array('belongsTo' => array('QB', 'RB1', 'RB2', 'WR1', 'WR2', 'F', 'K', 'D', 'User')));
            $records = $this->Userentry->find('all', array('conditions' => array('Userentry.user_id' => $this->Auth->user('id'), 'year' => Configure::read('current.year')), 'recursive' => 0));
            $data = array();
            $this->Playerentry = ClassRegistry::init('Playerentry');
            foreach ($records as $record) {
                $weekId = $record['Week']['id'];
                $UserId = $this->Auth->user('id');

                $playersArray = array($record['Userentry']['qb_id'],$record['Userentry']['rb1_id'],$record['Userentry']['rb2_id'],$record['Userentry']['wr1_id'],$record['Userentry']['wr2_id'],$record['Userentry']['f_id'],$record['Userentry']['k_id'],$record['Userentry']['d_id']);
                $record['Playerentry'] = $this->Playerentry->getTotalPointsByWeek($weekId, $playersArray);

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
						$userentry['QB']['locked'] = $this->Player->isPlayerLocked($userentry['QB']['id'], $weekId);
						$userentry['RB1']['locked'] = $this->Player->isPlayerLocked($userentry['RB1']['id'], $weekId);	
						$userentry['RB2']['locked'] = $this->Player->isPlayerLocked($userentry['RB2']['id'], $weekId);
						$userentry['WR1']['locked'] = $this->Player->isPlayerLocked($userentry['WR1']['id'], $weekId);
						$userentry['WR2']['locked'] = $this->Player->isPlayerLocked($userentry['WR2']['id'], $weekId);
						$userentry['F']['locked'] = $this->Player->isPlayerLocked($userentry['F']['id'], $weekId);
						$userentry['K']['locked'] = $this->Player->isPlayerLocked($userentry['K']['id'], $weekId);
						$userentry['D']['locked'] = $this->Player->isPlayerLocked($userentry['D']['id'], $weekId);
            return $userentry;
        }

        public function view($UserId) {
            if (!$UserId) {
                throw new NotFoundException(__('Invalid User'));
            }

            $this->Userentry->User->recursive=-1;
            $User = $this->Userentry->User->findById($UserId, array('name', 'owner'));
            $this->set('title', $User['User']['name']." (".$User['User']['owner'].")");

            $this->Standing = ClassRegistry::init('Standing');
            $this->Standing->unbindModel(array('belongsTo' => array('User')));
            $this->set('records', $this->Standing->find('all', array('conditions' => array('user_id' => $UserId/* , 'Week.lock_time < NOW()' */, 'year' => Configure::read('current.year')))));
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
      
   public function getPlayerDataDebug($weekId, $userId, $position) {
    return $this->getPlayerData($weekId, $userId, $position, true);     
   }

    public function getPlayerData($weekId, $userId, $position, $debug = false) {
        //CakeLog::write('debug', "getPlayerData:");
        //CakeLog::write('debug', "weekId: " . $weekId);
        //CakeLog::write('debug', "userId: " . $userId);
        //CakeLog::write('debug', "position: " . $position);
      
        if(!$debug) {
          $layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
          $this->autoRender = false;  // <-- NO RENDER THIS METHOD HAS NO VIEW VERY IMPORTANT!!!!!
        }
      
        $this->Player = ClassRegistry::init('Player');
        $this->Week = ClassRegistry::init('Week');
        $this->School = ClassRegistry::init('School');

        $userentry = $this->getUserentry($weekId);
        $players = $this->Player->getAvailablePlayers();
        $schedule = $this->getGamesSchedule($weekId);
        $schools = $this->School->findAndAdjustIndex();
        $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));
        $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $week['Week']['playoff_fl'], $userId);
        $data = array();
        $buttonId = 0;
        // loop through all the player records and build the json array
        foreach($players[$position] as $player) {
			
            $opponentID = $this->getOpponentID($player, $schedule);
            //$espnId = $player['Player']['School']['espn_id'];
						$espnId = $schools[$player['Player']['school_id']]['espn_id'];
            $opponent = "";
            if($opponentID != "") {
                $opponent = '<img src="../../app/webroot/img/logos/' . $schools[$opponentID]['espn_id'] . '.png" title="' . $schools[$opponentID]['name'] . '">';
            }

            $button = '';
            $playerName = $player['Player']['name'].'<br/>'.$this->getPlayerSchool($player);
			CakeLog::write('debug', 'position locked' . $userentry[$position]['locked']);
			if($userentry[$position]['locked'] != true) {
				if(!isset($userentries[$position][$player['Player']['id']])) {
					$button = $this->getButton($player, $schedule, $buttonId);
				} else {
					$playerName = '<span style="text-decoration:line-through">' . $playerName . '</span>';
				}
			}		
						$teamImage = '<img src="../../app/webroot/img/logos/' . $espnId . '.png" title="' . $schools[$player['Player']['school_id']]['name'] . '">';

            array_push($data,
                array(
                    $player['Player']['id'],
                    $player['Player']['position'],
                    $espnId,
                    $button,
										$teamImage,
                    $playerName,
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
		//CakeLog::write('debug', "json: " . $json);
        return $json;
    }

    private function getButton($player, $schedule, $buttonId) {
        $buttonLabel = $this->getButtonLabel($player, $schedule);
        $disabled = $this->getDisabledAttribute($buttonLabel);
        $button = '<button id="'.$buttonId.'"'.$disabled.' class="select-player">'.$buttonLabel.'</button>';
        return $button;
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
			//CakeLog::write('debug', 'Player = ' . $player['Player']['name']);
			//CakeLog::write('debug', 'Current Time = ' . $currentTime->format('Y-m-d H:i:s'));
			//CakeLog::write('debug', 'Game Time = ' . $lockedTime->format('Y-m-d H:i:s'));
        } else {
            $label = "Inactive";
        }
        return $label;
    }

    private function getDisabledAttribute($buttonLabel) {
        $class = '';
        if('Select' != $buttonLabel) {
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
      
    private function getPlayerSchool($player) {
        if(isset($player['Player']['School']['name'])) {
            return $player['Player']['School']['name'];
        }
        return "";
    }

      public function test() {
        $playerData = $this->getPlayerData(14, 1, 'QB', true);
        pr($playerData);
      }
}
?>