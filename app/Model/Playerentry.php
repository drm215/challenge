<?php

	/**
        @SuppressWarnings(PHPMD.StaticAccess)
    */
    class Playerentry extends AppModel {
    
        public $belongsTo = array("Week", "Player");
        
        public function espnParser($gameId = null) {
            App::import('Vendor', 'simple_html_dom', array('file'=>'simple_html_dom.php'));
            $this->Player = ClassRegistry::init('Player');
            $this->School = ClassRegistry::init('School');
            $this->Game = ClassRegistry::init('Game');
            
            $this->espnProcessWeek($gameId);
            
            $this->Standing = ClassRegistry::init('Standing');
            $this->Standing->calculateStandingsByWeek();
            $this->Standing->updateLowestWeek();
        }
      
        private function espnProcessWeek($gameId) {
          echo "Begin espnProcessWeek: " . $gameId . "\n";
          $playerEntries = array();
					$conditions = array('parsed' => false, 'time < DATE_SUB(NOW(), INTERVAL 3.5 HOUR)');
					if($gameId != '') {
						$conditions['espn_id'] = $gameId;
					}
          $games = $this->Game->find('all', array('conditions' => $conditions, 'recursive' => -1));
          $parsedGames = array();
          $schools = $this->School->find('list', array('conditions' => array('NOT' => array('conference_id' => 0)), 'fields' => array('id')));
          echo "Found " . count($games) . " to process.\n";
          foreach ($games as $game) {
            echo "Processing game id:" . $game['Game']['id'] . " " . $game['Game']['espn_id']."\n";
						$weekId = $game['Game']['week_id'];
            $espnUrl = "http://www.espn.com/college-football/boxscore?gameId=" . $game['Game']['espn_id'];
            $html = file_get_html($espnUrl);
            // check if the game is final
            $gameTimeSpan = $html->find('span[class=game-time]',0);
            if(!empty($gameTimeSpan)) {
                if(strpos($gameTimeSpan->plaintext, 'Final') === 0) {
                   echo "The game is final. Continue processing.\n";
                   $playerEntries = $this->espnProcessBoxScore($html, $playerEntries, $weekId, $schools, $game);
                   array_push($parsedGames, $game);
                } else {
                  echo "Skipping because the game is not final.\n";
                }
            } else {
              echo "Skipping because something unexpected happened.\n";
            }
            $html->clear(); 
			unset($html);
          }
          $this->savePlayerEntries($playerEntries, $parsedGames);
          unset($html);
        }
      
        private function savePlayerEntries($playerEntries, $games) {
          echo "\n\nBegin savePlayerEntries\n";
          $errors = false;
          foreach($playerEntries as $Playerentry) {
              if(!$this->save($Playerentry)) {
                  pr($Playerentry);
                  debug($this->validationErrors);
                  $errors = true;
              } else {
                  echo "Saving ".$Playerentry['Playerentry']['player_id']." is successful.\n";
              }
              $this->clear();
          }
          if(!$errors) {
            foreach ($games as $game) {
              $game['Game']['parsed'] = TRUE;
              if(!$this->Game->save($game)) {
                  pr($game);
                  debug($this->validationErrors);
              } else {
                  echo "Saving ".$game['Game']['id']." is successful.\n";
              }
              $this->Game->clear();
            }
          }
        }
        
        private function espnProcessBoxScore($html, $playerEntries, $weekId, $schools, $game) {
          $playerEntries = $this->espnProcessBoxScoreCategories($html, $playerEntries, $weekId, $schools, $game);
          $playerEntries = $this->espnProcessDefensivePointsAllowed($html, $playerEntries, $weekId, $schools, $game);
          $playerEntries = $this->espnProcessDefensiveScoring($playerEntries, $weekId, $schools, $game);
          $playerEntries = $this->espnProcessDefensiveTurnovers($playerEntries, $weekId, $schools, $game);
          return $playerEntries;
        }
        
        private function espnProcessDefensiveTurnovers($playerEntries, $weekId, $schools, $game) {
            $url = "http://www.espn.com/college-football/matchup?gameId=".$game['Game']['espn_id'];
            $html = file_get_html($url);
          
            $fumblesTr = $html->find('tr[data-stat-attr=fumblesLost]',0);
            $fumblesTds = $fumblesTr->find('td');
            $awayFumbles = $fumblesTds[1]->plaintext;
            $homeFumbles = $fumblesTds[2]->plaintext;
            
            $interceptionsTr = $html->find('tr[data-stat-attr=interceptions]',0);
            $interceptionsTds = $interceptionsTr->find('td');
            $awayInterceptions = $interceptionsTds[1]->plaintext;
            $homeInterceptions = $interceptionsTds[2]->plaintext;
            
            if(in_array($game['Game']['away_school_id'], $schools)) {
              $player = $this->Player->find('first', array('conditions' => array('position' => 'D', 'school_id' => $game['Game']['away_school_id']), 'recursive' => -1));
              if(!empty($player)) {
                  $playerEntry = $this->getPlayerentry($playerEntries, $player['Player']['id'], $weekId);
                  $playerEntry['Playerentry']['fumble_recovery'] = trim($homeFumbles);
                  $playerEntry['Playerentry']['def_ints'] = trim($homeInterceptions);
                  $playerEntries[$player['Player']['id']] = $playerEntry;
              }
            }
            if(in_array($game['Game']['home_school_id'], $schools)) {
              $player = $this->Player->find('first', array('conditions' => array('position' => 'D', 'school_id' => $game['Game']['home_school_id']), 'recursive' => -1));
              if(!empty($player)) {
                  $playerEntry = $this->getPlayerentry($playerEntries, $player['Player']['id'], $weekId);
                  $playerEntry['Playerentry']['fumble_recovery'] = trim($awayFumbles);
                  $playerEntry['Playerentry']['def_ints'] = trim($awayInterceptions);
                  $playerEntries[$player['Player']['id']] = $playerEntry;
              }
            }
            return $playerEntries;
        }
        
        private function espnProcessDefensiveScoring($playerEntries, $weekId, $schools, $game) {
          echo "espnProcessDefensiveScoring\n";
            
          $url = "http://www.espn.com/college-football/playbyplay?gameId=".$game['Game']['espn_id'];
          $html = file_get_html($url);

          $scoringSummaryDiv = $html->find('div[class=scoring-summary]', 0);
          if($scoringSummaryDiv != null) {
            $rows = $scoringSummaryDiv->find('tr');
                
            $previousAwayScore = 0;
            $previousHomeScore = 0;
                
            $awayTds = 0;
            $homeTds = 0;
            $awaySafeties = 0;
            $homeSafeties = 0;
                
            foreach($rows as $row) {
                $tds = $row->find('td');
                if(count($tds) == 5) {
                    $gameDetailsTd = $tds[1];
                    $headline = strtoupper($gameDetailsTd->find('div[class=headline]',0)->plaintext);
                    $awayScore = $tds[2]->plaintext;
                    $homeScore = $tds[3]->plaintext;
                    if(strpos($headline, "INTERCEPTION")) {
                        if($awayScore > $previousAwayScore) {
                            $awayTds++;
                        } else if($homeScore > $previousHomeScore) {
                            $homeTds++;
                        }    
                    } else if(strpos($headline, "SAFETY")) {
                        if($awayScore > $previousAwayScore) {
                            $awaySafeties++;
                        } else if($homeScore > $previousHomeScore) {
                            $homeSafeties++;
                        }
                    } else if(strpos($headline, "FUMBLE")) {
                        if($awayScore > $previousAwayScore) {
                            $awayTds++;
                        } else if($homeScore > $previousHomeScore) {
                            $homeTds++;
                        }
                    }
                    $previousAwayScore = $awayScore;
                    $previousHomeScore = $homeScore;
                  }
            }
            
          if(in_array($game['Game']['away_school_id'], $schools)) {
            $player = $this->Player->find('first', array('conditions' => array('position' => 'D', 'school_id' => $game['Game']['away_school_id']), 'recursive' => -1));
            if(!empty($player)) {
                $playerEntry = $this->getPlayerentry($playerEntries, $player['Player']['id'], $weekId);
                $playerEntry['Playerentry']['def_tds'] = trim($awayTds);
                $playerEntry['Playerentry']['safety'] = trim($awaySafeties);
                $playerEntries[$player['Player']['id']] = $playerEntry;
            }
          }
                               
          if(in_array($game['Game']['home_school_id'], $schools)) {
            $player = $this->Player->find('first', array('conditions' => array('position' => 'D', 'school_id' => $game['Game']['home_school_id']), 'recursive' => -1));
            if(!empty($player)) {
              $playerEntry = $this->getPlayerentry($playerEntries, $player['Player']['id'], $weekId);
              $playerEntry['Playerentry']['def_tds'] = trim($homeTds);
              $playerEntry['Playerentry']['safety'] = trim($homeSafeties);
              $playerEntries[$player['Player']['id']] = $playerEntry;
            }
          }   
        }
        return $playerEntries;
      }
        
        private function espnProcessDefensivePointsAllowed($html, $playerEntries, $weekId, $schools, $game) {
          echo "espnProcessDefensivePointsAllowed\n";
          if(in_array($game['Game']['away_school_id'], $schools)) {
            $player = $this->Player->find('first', array('conditions' => array('position' => 'D', 'school_id' => $game['Game']['away_school_id']), 'recursive' => -1));
            $score = $html->find('div[class=team home]',0)->find('div[class=score]',0)->plaintext;
            $playerEntry = $this->getPlayerentry($playerEntries, $player['Player']['id'], $weekId);
            $playerEntry['Playerentry']['points_allowed'] = $score;
            $playerEntries[$player['Player']['id']] = $playerEntry;
          }
          
          if(in_array($game['Game']['home_school_id'], $schools)) {
            $player = $this->Player->find('first', array('conditions' => array('position' => 'D', 'school_id' => $game['Game']['home_school_id']), 'recursive' => -1));
            $score = $html->find('div[class=team away]',0)->find('div[class=score]',0)->plaintext;
            $playerEntry = $this->getPlayerentry($playerEntries, $player['Player']['id'], $weekId);
            $playerEntry['Playerentry']['points_allowed'] = $score;
            $playerEntries[$player['Player']['id']] = $playerEntry;
          }
          return $playerEntries;
        }
      
        private function espnProcessBoxScoreContainer($container, $processAway, $processHome, $category, $playerEntries, $weekId) {
		  echo "espnProcessBoxScoreContainer - " . $category . "\n";
          if($processAway) {
            $div = $container->find('div[class=column-one]', 0);
            $table = $div->find('table[class=mod-data]', 0);
            $playerEntries = $this->espnProcessBoxScoreTable($table, $category, $playerEntries, $weekId);
          }
          if($processHome) {
            $div = $container->find('div[class=column-two]', 0);
            $table = $div->find('table[class=mod-data]', 0);
            $playerEntries = $this->espnProcessBoxScoreTable($table, $category, $playerEntries, $weekId);
          }
          return $playerEntries;
        }
      
        private function espnProcessBoxScoreKickingContainer($container, $processAway, $processHome, $playerEntries, $weekId, $game) {
          if($processAway) {
            $div = $container->find('div[class=column-one]', 0);
            $table = $div->find('table[class=mod-data]', 0);
            $playerEntries = $this->espnProcessBoxScoreKickingTable($table, $playerEntries, $weekId, $game['Game']['away_school_id']);
          }
          if($processHome) {
            $div = $container->find('div[class=column-two]', 0);
            $table = $div->find('table[class=mod-data]', 0);
            $playerEntries = $this->espnProcessBoxScoreKickingTable($table, $playerEntries, $weekId, $game['Game']['home_school_id']);
          }
          return $playerEntries;
        }
      
        private function espnProcessBoxScoreKickingTable($table, $playerEntries, $weekId, $schoolId) {
          $tbody = $table->find('tbody',0);
          $row = $tbody->find('tr[class=highlight]', 0);
					if(count($row) == 1) {
						$player = $this->Player->find('first', array('conditions' => array('position' => 'K', 'school_id' => $schoolId), 'recursive' => -1));
						$id = $player['Player']['id'];
						$playerentry = $this->getPlayerentry($playerEntries, $id, $weekId);
						$temp = $row->find('td[class=fg]',0);
						if($temp != null) {
								$array = explode("/", $temp->plaintext);
								if(count($array) == 2) {
										$playerentry['Playerentry']['field_goals'] = $array[0];
								}
						}
						$temp = $row->find('td[class=xp]',0);
						if($temp != null) {
								$array = explode("/", $temp->plaintext);
								if(count($array) == 2) {
										$playerentry['Playerentry']['pat'] = $array[0];
								}
						}
						$playerEntries[$id] = $playerentry;
					}
          return $playerEntries;
        }
      
        private function espnProcessBoxScoreTable($table, $category, $playerEntries, $weekId) {
			echo "espnProcessBoxScoreTable - " . $category . "\n";
          $tbody = $table->find('tbody',0);
          $rows = $tbody->find('tr');
          
		  echo "espnProcessBoxScoreTable found " . count($rows) . "\n";
          foreach($rows as $row) {
            $nameTd = $row->find('td[class=name]',0);
            if($nameTd != null) {
              $nameLink = $nameTd->find('a', 0);
              if(empty($nameLink)) {
                //echo "Link could not be found.  Dumping contents of td: " . $nameTd->plaintext;
              } else {
				  $espnId = substr($nameLink->href, strlen("http://www.espn.com/college-football/player/_/id/"), strrpos($nameLink->href, "/") - strlen("http://www.espn.com/college-football/player/_/id/"));
                $player = $this->Player->find('first', array('conditions' => array('espn_id' => $espnId), 'recursive' => -1));
                if(empty($player)) {
                  echo "Player " . $nameTd->plaintext . " could not be found in the database. \n";
                } else {
                  $kReturnTds = 0;
                  $kReturnYards = 0;
                  $pReturnTds = 0;
                  $pReturnYards = 0;
                  if($player != null && isset($player['Player'])) {
                      $id = $player['Player']['id'];
                      $playerentry = $this->getPlayerentry($playerEntries, $id, $weekId);

                      if("kickReturns" == $category) {
                          $temp = $row->find('td[class=td]',0);
                          if($temp != null) {
                              $kReturnTds = $temp->plaintext;
                              $playerentry['Playerentry']['return_tds'] = $kReturnTds + $pReturnTds;
                          }
                          $temp = $row->find('td[class=yds]',0);
                          if($temp != null) {
                              $kReturnYards = $temp->plaintext;
                              $playerentry['Playerentry']['return_yards'] = $kReturnYards + $pReturnYards;
                          }
                      } else if("puntReturns" == $category) {
                          $temp = $row->find('td[class=td]',0);
                          if($temp != null) {
                              $pReturnTds = $temp->plaintext;
                              $playerentry['Playerentry']['return_tds'] = $kReturnTds + $pReturnTds;
                          }
                          $temp = $row->find('td[class=yds]',0);
                          if($temp != null) {
                              $pReturnYards = $temp->plaintext;
                              $playerentry['Playerentry']['return_yards'] = $kReturnYards + $pReturnYards;
                          }
                      } else {
                          $temp = $row->find('td[class=td]',0);
                          if($temp != null) {
                              $playerentry['Playerentry'][$category.'_tds'] = $temp->plaintext;
                          }
                          $temp = $row->find('td[class=yds]',0);
                          if($temp != null) {
                              $playerentry['Playerentry'][$category.'_yards'] = $temp->plaintext;
                          }
                      }
                      $playerEntries[$id] = $playerentry;
                  }
                }
              }
            }
          }
          return $playerEntries;
        }
        
        private function espnProcessBoxScoreCategories($html, $playerEntries, $weekId, $schools, $game) {
          $processAway = false;
          $processHome = false;
          if(in_array($game['Game']['away_school_id'], $schools)) {
            // away team is an fbs school.  continue processing
            echo "Processing away school: " . $game['Game']['away_school_id']."\n";
            $processAway = true;
          }
          
          if(in_array($game['Game']['home_school_id'], $schools)) {
            // home team is an fbs school.  continue processing
            echo "Processing home school: " . $game['Game']['home_school_id']."\n";
            $processHome = true;
          }
          
          $playerEntries = $this->espnProcessBoxScoreContainer($html->find("div[id=gamepackage-passing]", 0), $processAway, $processHome, 'pass', $playerEntries, $weekId);
          $playerEntries = $this->espnProcessBoxScoreContainer($html->find("div[id=gamepackage-rushing]", 0), $processAway, $processHome, 'rush', $playerEntries, $weekId);
          $playerEntries = $this->espnProcessBoxScoreContainer($html->find("div[id=gamepackage-receiving]", 0), $processAway, $processHome, 'receive', $playerEntries, $weekId);
          $playerEntries = $this->espnProcessBoxScoreContainer($html->find("div[id=gamepackage-interceptions]", 0), $processAway, $processHome, 'interceptions', $playerEntries, $weekId);
          $playerEntries = $this->espnProcessBoxScoreContainer($html->find("div[id=gamepackage-kickReturns]", 0), $processAway, $processHome, 'kickReturns', $playerEntries, $weekId);
          $playerEntries = $this->espnProcessBoxScoreContainer($html->find("div[id=gamepackage-puntReturns]", 0), $processAway, $processHome, 'puntReturns', $playerEntries, $weekId);
          $playerEntries = $this->espnProcessBoxScoreKickingContainer($html->find("div[id=gamepackage-kicking]", 0), $processAway, $processHome, $playerEntries, $weekId, $game);
          return $playerEntries;
        }
        
        private function getPlayerentry($playerEntries, $id, $weekId) {
            if(isset($playerEntries[$id])) {
                $playerentry = $playerEntries[$id];
            } else {
                $playerentry = $this->find('first', array('conditions' => array('player_id' => $id, 'week_id' => $weekId), 'recursive' => -1));
            }
            if(empty($playerentry)) {
                $playerentry = $this->create();
                $playerentry['Playerentry']['week_id'] = $weekId;
                $playerentry['Playerentry']['player_id'] = $id;
            }
            return $playerentry;
        }
        public function getTotalPointsByWeek($weekId, $playerIds) {
            $points = $this->find('first', array('fields' => array('SUM(Playerentry.points) AS points'), 'conditions' => array('week_id' => $weekId, 'player_id' => $playerIds), 'recursive' => -1));
            if(count($points) > 0) {
                return $points[0];
            }
        }
        public function getPlayerEntries($userentry, $unbindPlayer = true, $recursive = 0) {
            $playerEntries = array();
            if($unbindPlayer) {
              $this->unbindModel(array('belongsTo' => array('Player')));
            }
            $this->unbindModel(array('belongsTo' => array('Week')));
            $playerEntries['QB'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['qb_id']), 'recursive' => $recursive));
            $playerEntries['RB1'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['rb1_id']), 'recursive' => $recursive));
            $playerEntries['RB2'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['rb2_id']), 'recursive' => $recursive));
            $playerEntries['WR1'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['wr1_id']), 'recursive' => $recursive));
            $playerEntries['WR2'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['wr2_id']), 'recursive' => $recursive));
            $playerEntries['F'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['f_id']), 'recursive' => $recursive));
            $playerEntries['K'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['k_id']), 'recursive' => $recursive));
            $playerEntries['D'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['d_id']), 'recursive' => $recursive));
            return $playerEntries;
        }
			
        public function beforeSave($options = array()) {
            if(isset($this->data['Playerentry']['player_id'])) {
                $points = 0;
            
                $this->Weight = ClassRegistry::init('Weight');
                $weights = $this->Weight->find('first');
                            
                $this->Player = ClassRegistry::init('Player');
            
                $player = $this->Player->find('first', array('conditions' => array('id' => $this->data['Playerentry']['player_id']), 'recursive' => -1));
                $position = $player['Player']['position'];
                if($position == 'QB' || $position == 'RB' || $position == 'WR' || $position == 'TE') {
                    $points += $this->data['Playerentry']['pass_yards'] / $weights['Weight']['pass_yards'];
                    $points += $this->data['Playerentry']['pass_tds'] * $weights['Weight']['pass_tds'];
                    $points += $this->data['Playerentry']['rush_yards'] / $weights['Weight']['rush_yards'];
                    $points += $this->data['Playerentry']['rush_tds'] * $weights['Weight']['rush_tds'];
                    $points += $this->data['Playerentry']['receive_yards'] / $weights['Weight']['receive_yards'];
                    $points += $this->data['Playerentry']['receive_tds'] * $weights['Weight']['receive_tds'];
                    $points += $this->data['Playerentry']['return_tds'] * $weights['Weight']['return_tds'];
                } else if($position == 'K') {
                    $points += $this->data['Playerentry']['field_goals'] * $weights['Weight']['field_goals'];
                    $points += $this->data['Playerentry']['pat'] * $weights['Weight']['pat'];
                } else if($position == 'D') {
                    $points += $this->data['Playerentry']['sacks'] * $weights['Weight']['sacks'];
                    $points += $this->data['Playerentry']['fumble_recovery'] * $weights['Weight']['fumble_recovery'];
                    $points += $this->data['Playerentry']['def_ints'] * $weights['Weight']['def_ints'];
                    $points += $this->data['Playerentry']['def_tds'] * $weights['Weight']['def_tds'];
                    $points += $this->data['Playerentry']['safety'] * $weights['Weight']['safety'];
                    
                    $pointsAllowedString = $weights['Weight']['points_allowed'];
                    $pointsAllowedTempArray = explode(';',$pointsAllowedString);
                    $pointsAllowedArray = array();
                    
                    foreach($pointsAllowedTempArray as $row) {
                        $temp = explode(':', $row);
                        $pointsAllowedArray[$temp[0]] = $temp[1];
                    }
                    
                    $pointsAllowedValue = $this->data['Playerentry']['points_allowed'];
                    while($row = current($pointsAllowedArray)) {
                        if($pointsAllowedValue <= key($pointsAllowedArray)) {
                            $points += $row;
                            break;
                        }
                        next($pointsAllowedArray);                
                    }
                }
                $this->data['Playerentry']['points'] = $points;
            }
        }
			
			public function getPlayerEntriesKeyed($position) {
				$this->unbindModel(array('belongsTo' => array('Week')));
				$data = array();
				$playerentries = $this->find('all', array('conditions' => array('points >' => '0', 'position' => $position), 'order' => array('week_id'), 'recursive' => 0));
				foreach($playerentries as $playerentry) {
					if(isset($data[$playerentry['Playerentry']['player_id']])) {

					} else {
						$key = $playerentry['Playerentry']['player_id'] . '-' . $playerentry['Playerentry']['week_id'];
						$data[$key] = $playerentry;
					}
				}
				return $data;
			}
    }
?>