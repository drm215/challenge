<?php
    class Game extends AppModel {

        public $hasMany = array('Week');

        public function parser($weekId = null) {
            App::import('Vendor', 'simple_html_dom', array('file'=>'simple_html_dom.php'));
						$this->School = ClassRegistry::init('School');

            if($weekId == null) {
                $weeks = $this->Week->find('list');
            } else {
                $weeks[1] = $weekId;
            }

            foreach($weeks as $week) {
                $this->espnProcessWeek($week);
            }
        }

        private function espnProcessWeek($weekId) {
            echo "Processing Week " . $weekId . "\n";
            $url = "http://www.espn.com/college-football/schedule/_/week/" . $weekId;
            echo "URL = " . $url . "\n";
            //$url = "file:///C:/Users/damagee/Desktop/espn.html";
            $this->espnProcessWeekByDays(file_get_html($url), $weekId);
        }

        private function espnProcessWeekByDays($html, $weekId) {
            $tables = $html->find('table[class=schedule has-team-logos align-left]');
            echo "Found " . count($tables) . " tables to process.\n";
            foreach($tables as $table) {
                $this->espnProcessWeekGames($table, $weekId);
            }
        }

        private function espnProcessWeekGames($table, $weekId) {
            $games = $table->find('tr');
            echo "Found " . count($games) . " games to process.\n";
            for($i = 1; $i < count($games); $i++) {
                $this->espnProcessWeekGame($games[$i], $weekId);
            }
        }

        private function espnProcessWeekGame($gameTr, $weekId) {
						pr('begin espnProcessWeekGame');
            $tds = $gameTr->find('td');
            $awaySchoolId = $this->getSchoolId($tds[0]);
            $homeSchoolId = $this->getSchoolId($tds[1]);
					
						$date = $this->processEspnDate($tds[2]->outertext);

            $existingGame = $this->find('first', array('recursive' => -1, 'conditions' => array('away_school_id' => $awaySchoolId, 'home_school_id' => $homeSchoolId, 'week_id' => $weekId)));
            if(empty($existingGame)) {
                $game = $this->create();
                $game['Game']['away_school_id'] = $awaySchoolId;
                $game['Game']['home_school_id'] = $homeSchoolId;
                $game['Game']['week_id'] = $weekId;
            } else {
                $game = $existingGame;
            }
            $game['Game']['espn_id'] = $this->getEspnId($tds[2]->find('a', 0)->href, "/college-football/game?gameId=");
            $game['Game']['time'] =  $date;

						pr($game);
            $this->espnSaveGame($game);
        }
		
		private function getSchoolId($td) {
			pr('begin getSchoolId');
			$schoolLink = $td->find('a', 0);
			if($schoolLink == null) {
				pr('School does not have a link.  Need to retrieve the id from the database.');
				$schoolName = $td->find('span[class=team-name]',0)->find('span',0)->plaintext;
				$school = $this->School->find('first', array('recursive' => -1, 'fields' => array('id'), 'conditions' => array('name' => $schoolName)));
				if($school == null || empty($school)) {
					pr('School does not exist yet. Creating '. $schoolName. ' now.');
					$school = $this->School->create();
					$school['School']['name'] = $schoolName;
					$school = $this->School->save($school);
				}
				$schoolId = $school['School']['id'];
			} else {
				$schoolId = $this->getSchoolIdByEspnId($this->getEspnId($schoolLink->href, "/college-football/team/_/id/"));
			}
			return $schoolId;
		}

        private function getSchoolIdByEspnId($espnId) {
			pr('begin getSchoolIdByEspnId');
			pr('$espnId = '.$espnId);
            $school = $this->School->find('first', array('recursive' => -1, 'fields' => array('id'), 'conditions' => array('espn_id' => $espnId)));
            return $school['School']['id'];
        }

        private function espnSaveGame($game) {
            if($this->save($game)) {
                echo "Game saved successfully.\n";
            } else {
                echo "Game not saved successfully.\n";
                pr($game);
            }
        }

        private function getEspnId($espnLink, $prefix) {
            $sPos = strlen($prefix);
            return substr($espnLink, $sPos, strlen($espnLink) - $sPos);
        }

        private function processEspnDate($wordyDate) {
            $start = strpos($wordyDate, 'data-date="') + strlen('data-date="');
						$end = strpos($wordyDate, '"', strpos($wordyDate, 'data-date="') + strlen('data-date="') + 1);
						$date = (new DateTime(substr($wordyDate, $start, $end - $start)))->modify('-4 hours');
						return $date->format('Y-m-d H:i:s');
        }

        private function switchMonth($text) {
            $date = date_parse($text);
            return str_pad($date['month'], 2, "0", STR_PAD_LEFT);
        }

        public function getGamesByWeek($weekId) {
            $values = array();
            $games = $this->find('all', array('recursive' => -1, 'conditions' => array('week_id' => $weekId)));
            foreach($games as $game) {
                $values[$game['Game']['away_school_id']] = $game;
                $values[$game['Game']['home_school_id']] = $game;
            }
            return $values;
        }
    }
?>