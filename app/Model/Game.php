<?php

	/**
        @SuppressWarnings(PHPMD.StaticAccess)
    */
    class Game extends AppModel {

        public $hasMany = array('Week');
		
		public function updateEspnGameIdParser($weekId = null) {
			App::import('Vendor', 'simple_html_dom', array('file'=>'simple_html_dom.php'));
			$this->School = ClassRegistry::init('School');
			 if($weekId !== null) {
				$url = "http://www.espn.com/college-football/schedule/_/week/" . $weekId;
				$html = file_get_html($url);
            	$tables = $html->find('table[class=schedule has-team-logos align-left]');
            	echo "Found " . count($tables) . " tables to process.\n";
            	foreach($tables as $table) {
                	$games = $table->find('tr');
            		echo "Found " . count($games) . " games to process.\n";
            		for($i = 1; $i < count($games); $i++) {
						$tds = $games[$i]->find('td');
						$awaySchoolId = $this->getSchoolId($tds[0]);
						$homeSchoolId = $this->getSchoolId($tds[1]);
						$game = $this->find('first', array('recursive' => -1, 'conditions' => array('away_school_id' => $awaySchoolId, 'home_school_id' => $homeSchoolId, 'week_id' => $weekId)));
						$game['Game']['espn_id'] = $this->getEspnId($tds[2]->find('a', 0)->href, "/college-football/game?gameId=");
						pr($game);
						$this->espnSaveGame($game);
            		}
            	}
				$html->clear(); 
				unset($html);
			 }
			
		}

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
			$html = file_get_html($url);
            $this->espnProcessWeekByDays($html, $weekId);
			$html->clear(); 
			unset($html);
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
			if($date !== FALSE) {

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
			$sEnd = strpos($espnLink, '/', $sPos + 1);
			if($sEnd === FALSE) {
				return substr($espnLink, $sPos);	
			}
            return substr($espnLink, $sPos, $sEnd - $sPos);
        }

        private function processEspnDate($wordyDate) {
			$pos = strpos($wordyDate, 'Postponed');
			if($pos !== FALSE) {
				pr('Game is postponed');
				return FALSE;
			}
			$pos = strpos($wordyDate, 'LIVE');
			if($pos !== FALSE) {
				pr('Game is Live');
				return FALSE;
			}
			
            $start = strpos($wordyDate, 'data-date="') + strlen('data-date="');
			$end = strpos($wordyDate, '"', strpos($wordyDate, 'data-date="') + strlen('data-date="') + 1);
			$date = (new DateTime(substr($wordyDate, $start, $end - $start), new DateTimeZone("America/New_York")))->modify('-4 hours');
			echo $date->format(DATE_RSS);
			return $date->format('Y-m-d H:i:s');
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