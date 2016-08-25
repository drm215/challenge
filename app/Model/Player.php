<?php
    class Player extends AppModel {

        public $belongsTo = array('School');
        public $hasMany = array('Playerentry');
		
		/**
        Server side validation that the player is locked.
        **/
        public function isPlayerLocked($id, $position, $weekId) {			
            $this->Game = ClassRegistry::init('Game');

            $this->unbindModel(array('hasMany' => array('Playerentry')));
            $this->School->unbindModel(array('hasMany' => array('Player')));
            $player = $this->find('first', array('conditions' => array('Player.id' => $id), 'recursive' => 0));
            if(!empty($player)) {
                $school = $player['School'];

                $game = $this->Game->find('first', array('recursive' => -1, 'conditions' => array('week_id' => $weekId, 'OR' => array('away_school_id' => $player['School']['id'], 'home_school_id' => $player['School']['id']))));
                if(!empty($game)) {
                    $lockedTime = strtotime($game['Game']['time']) - 10 * 60;
                    if(time() > $lockedTime) {
                        return true;
                    }
                }
            }
            return false;
        }

        public function getPlayers($position, $userId, $weekId, $playoffFlag) {
            $this->Game = ClassRegistry::init('Game');
            $this->Userentry = ClassRegistry::init('Userentry');
            $this->School = ClassRegistry::init('School');

            $players = $this->getAvailablePlayers();
            $schools = $this->School->find('list', array('recursive' => -1));
            $schedule = $this->Game->getGamesByWeek($weekId);
            $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $playoffFlag, $userId);
            $playerData = $this->printPlayersData($players, $userentries, $schedule, $schools);
            return $playerData;
        }
      
      public function getAvailablePlayers() {
            $players = array();
            $players['QB'] = $this->getAvailablePlayersByPosition(array('QB'));
            $players['RB'] = $this->getAvailablePlayersByPosition(array('RB'));
            $players['WR'] = $this->getAvailablePlayersByPosition(array('WR'));
            $players['F'] = $this->getAvailablePlayersByPosition(array('RB','WR','TE'));
            $players['K'] = $this->getAvailablePlayersByPosition(array('K'));
            $players['D'] = $this->getAvailablePlayersByPosition(array('D'));

            return $players;
        }
      
      /**
        Gets the list of players available by the given position.
        Restricts based on previous picks and schools who have already played in that week
        **/
        private function getAvailablePlayersByPosition($position) {
            $this->Playerentry->unbindModel(array('belongsTo' => array('Week')));
            $this->unbindModel(array('hasMany' => array('Playerentry')));
			$this->unbindModel(array('belongsTo' => array('School')));
            $tempPlayerEntries = $this->Playerentry->find('all',
                array(
                    'conditions' => array('position' => $position, 'Player.year' => Configure::read('current.year')),
                    'fields' => array('SUM(points)',
                        'SUM(pass_yards)','SUM(pass_tds)',
                        'SUM(rush_yards)','SUM(rush_tds)',
                        'SUM(receive_yards)','SUM(receive_tds)',
                        'SUM(return_yards)','SUM(return_tds)',
                        'SUM(field_goals)','SUM(pat)',
                        'SUM(points_allowed)','SUM(fumble_recovery)',
                        'SUM(def_ints)','SUM(def_tds)','SUM(safety)',
                        'Player.name, Player.school_id, Player.id, Player.position'),
                    'group' => array('Player.id'),
                    'recursive' => 2,
                    'order' => array('SUM(points) DESC')
                )
            );

            $playerEntries = array();
            foreach($tempPlayerEntries as $temp) {
                $playerEntries[$temp['Player']['id']] = $temp;
            }
            return $playerEntries;
        }
        

        public function parser($playerRow, $schoolId) {
            $columns = $playerRow->find('td');

            $espnId = $this->getEspnId($columns[1]->find('a',0)->href);
            $name = $columns[1]->plaintext;
            $position = $columns[2]->plaintext;

            $player = null;
            $players = $this->find('all', array('conditions' => array('name' => $name, 'school_id' => $schoolId, 'position' => $position), 'recursive' => -1));

            if(count($players) == 0) {
                $player = $this->create();
                $player['Player']['name'] = $name;
            } else if(count($players == 1)) {
                $player = $players[0];
            } else {
                echo $name . " returned more than one row.";
            }

            if($player != null) {
                $player['Player']['school_id'] = $schoolId;
                $player['Player']['year'] = Configure::read('current.year');
                $player['Player']['position'] = $position;
                $player['Player']['espn_id'] = $espnId;
            }

            $this->savePlayer($player);
        }

        private function savePlayer($player) {
            if($this->save($player)) {
                echo $player['Player']['name']." saved successfully.\n";
            } else {
                echo $player['Player']['name']." not saved successfully.\n";
            }
        }

        public function getEspnId($espnLink) {
            $bPos = strlen("https://espn.go.com/college-football/player/_/id/");
            $ePos = strpos($espnLink, "/", $bPos);
            $espnId = substr($espnLink, $bPos, $ePos - $bPos);
            return $espnId;
        }
}
?>