<?php
    class Playerentry extends AppModel {
    
        public $belongsTo = array("Week", "Player");
        
        public function espnParser($weekId) {
            App::import('Vendor', 'simple_html_dom', array('file'=>'simple_html_dom.php'));
            $this->Player = ClassRegistry::init('Player');
            $this->School = ClassRegistry::init('School');
            $this->Game = ClassRegistry::init('Game');
            
            $this->espnProcessWeek($weekId);
            
            $this->Standing = ClassRegistry::init('Standing');
            $this->Standing->calculateStandingsByWeek($weekId);
            $this->Standing->updateLowestWeek();
        }
        
        private function espnProcessWeek($weekId) {
            $debug = '';
            $gameIdsArray = array();
            $parsedGames = array();
            
            if($debug == '') {
                $url = "https://espn.go.com/ncf/schedule/_/group/80/week/".$weekId;
                echo $url;
                echo "\n";
                $html = file_get_html($url);
                $links = $html->find('a[name=&amp;lpos=ncf:schedule:score],0');
                echo "Processing " . count($links) . " links.\n";
                $parsedGames = $this->Game->find('list', array('conditions' => array('week_id' => $weekId), 'fields' => array('id', 'game_id')));
                for($i = 0; $i < count($links); $i++) {
                    $link = $links[$i]->href;
                    if(strpos($link, "/ncf/game?gameId=") === 0) {
                        $gameId = substr($link, strlen("/ncf/game?gameId="));
                        echo "Pushing game = " . $gameId ."\n";
                        array_push($gameIdsArray, $gameId);
                    }
                }
            } else {
                array_push($gameIdsArray, $debug);
            }

            for($i = 0; $i < count($gameIdsArray); $i++) {
                if(!in_array($gameIdsArray[$i], $parsedGames)) {
                    $playerEntries = array();
                    
                    $final = false;
                    
                    $url = "https://espn.go.com/ncf/boxscore?gameId=".$gameIdsArray[$i];
                    $html = file_get_html($url);
                    
                    if(strpos($html->plaintext, 'No Box Score Available') !== false) {
                        echo "Skipping ".$gameIdsArray[$i]." because the game stats do not exist.\n";
                    } else {
                        $gameTimeSpan = $html->find('span[class=game-time]',0);
                        if(!empty($gameTimeSpan)) {
                            if(strpos($gameTimeSpan->plaintext, 'Final')>=0) {
                                $final = true;
                            }
                        }
                        
                        $playerEntries = $this->espnProcessBoxScore($html, $gameIdsArray[$i], $playerEntries, $weekId);
                        if(isset($playerEntries[0]) && $playerEntries[0] == 'error') {
                            echo "Skipping ".$gameIdsArray[$i]." because the game stats do not exist.\n";
                        }
                        else {
                            $playerEntries = $this->espnProcessDefensiveTurnovers($gameIdsArray[$i], $playerEntries, $weekId);
                            $playerEntries = $this->espnProcessDefensiveScoring($gameIdsArray[$i], $playerEntries, $weekId);
                            
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
                            if(!$errors && $final) {
                                echo "Saving game\n";
                                $game = $this->Game->create();
                                $game['Game']['week_id'] = $weekId;
                                $game['Game']['game_id'] = $gameIdsArray[$i];
                                $this->Game->save($game);
                                $this->Game->clear();
                            }
                        }
                    }
                } else {
                    echo "Skipping ".$gameIdsArray[$i]." because it has already been parsed.\n";
                }
            }
        }
        
        private function espnProcessBoxScore($html, $gameId, $playerEntries, $weekId) {
            echo $gameId."\n";
            $schools = array_unique($this->Player->find('list', array('fields' => array('school'))));

            $playerEntries = $this->espnProcessBoxScoreCategories($html->find('table[class=mod-data]'), $playerEntries, $weekId, $schools);
            $awayUserDiv = $html->find('div[class=team away]',0);
            if($awayUserDiv == null) {
                $awayUserDiv = $html->find('div[class=team away possession]',0);
            }
            $homeUserDiv = $html->find('div[class=team home]',0);
            if($homeUserDiv == null) {
                $homeUserDiv = $html->find('div[class=team home possession]',0);
            }
            $playerEntries = $this->espnProcessDefensivePointsAllowed($gameId, $awayUserDiv, $homeUserDiv, $playerEntries, $weekId);
            return $playerEntries;
        }
        
        private function espnProcessDefensiveTurnovers($gameId, $playerEntries, $weekId) {
            echo $gameId."\n";
            $url = "https://espn.go.com/ncf/matchup?gameId=".$gameId;
            $html = file_get_html($url);
            
            $awayUserDiv = $html->find('div[class=team away]',0);
            if($awayUserDiv == null) {
                $awayUserDiv = $html->find('div[class=team away possession]',0);
            }
            
            $awayUserSpan = $awayUserDiv->find('span[class=long-name]', 0);
            $awayUserName = $awayUserSpan->plaintext;
            
            $homeUserDiv = $html->find('div[class=team home]',0);
            if($homeUserDiv == null) {
                $homeUserDiv = $html->find('div[class=team home possession]',0);
            }
            $homeUserSpan = $homeUserDiv->find('span[class=long-name]', 0);
            $homeUserName = $homeUserSpan->plaintext;
            
            $fumblesTr = $html->find('tr[data-stat-attr=fumblesLost]',0);
            $fumblesTds = $fumblesTr->find('td');
            $awayFumbles = $fumblesTds[1]->plaintext;
            $homeFumbles = $fumblesTds[2]->plaintext;
            
            $interceptionsTr = $html->find('tr[data-stat-attr=interceptions]',0);
            $interceptionsTds = $interceptionsTr->find('td');
            $awayInterceptions = $interceptionsTds[1]->plaintext;
            $homeInterceptions = $interceptionsTds[2]->plaintext;
            
            $awayPlayer = $this->Player->find('first', array('conditions' => array('name' => 'Defense', 'school' => $awayUserName), 'recursive' => -1));
            if(!empty($awayPlayer)) {
                $awayId = $awayPlayer['Player']['id'];
                $awayPlayerentry = $this->getPlayerentry($playerEntries, $awayId, $weekId);
                $awayPlayerentry['Playerentry']['fumble_recovery'] = trim($homeFumbles);
                $awayPlayerentry['Playerentry']['def_ints'] = trim($homeInterceptions);
                $awayPlayerentry['Playerentry']['game_id'] = $gameId;
                $playerEntries[$awayId] = $awayPlayerentry;
            }
            
            $homePlayer = $this->Player->find('first', array('conditions' => array('name' => 'Defense', 'school' => $homeUserName), 'recursive' => -1));
            if(!empty($homePlayer)) {
                $homeId = $homePlayer['Player']['id'];
                $homePlayerentry = $this->getPlayerentry($playerEntries, $homeId, $weekId);
                $homePlayerentry['Playerentry']['fumble_recovery'] = trim($awayFumbles);
                $homePlayerentry['Playerentry']['def_ints'] = trim($awayInterceptions);
                $homePlayerentry['Playerentry']['game_id'] = $gameId;
                $playerEntries[$homeId] = $homePlayerentry;
            }
            return $playerEntries;
        }
        
        private function espnProcessDefensiveScoring($gameId, $playerEntries, $weekId) {
            echo $gameId."\n";
            echo "espnProcessDefensiveScoring\n";
            
            $url = "https://espn.go.com/ncf/playbyplay?gameId=".$gameId;
            $html = file_get_html($url);

            $awayUserDiv = $html->find('div[class=team away]',0);
            if($awayUserDiv == null) {
                $awayUserDiv = $html->find('div[class=team away possession]',0);
            }
            $awayUserSpan = $awayUserDiv->find('span[class=long-name]', 0);
            $awayUserName = $awayUserSpan->plaintext;
            
            $homeUserDiv = $html->find('div[class=team home]',0);
            if($homeUserDiv == null) {
                $homeUserDiv = $html->find('div[class=team home possession]',0);
            }
            $homeUserSpan = $homeUserDiv->find('span[class=long-name]', 0);
            $homeUserName = $homeUserSpan->plaintext;
            
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
                
                $awayPlayer = $this->Player->find('first', array('conditions' => array('name' => 'Defense', 'school' => $awayUserName), 'recursive' => -1));
                if(!empty($awayPlayer)) {
                    $awayId = $awayPlayer['Player']['id'];
                    $awayPlayerentry = $this->getPlayerentry($playerEntries, $awayId, $weekId);
                    $awayPlayerentry['Playerentry']['def_tds'] = trim($awayTds);
                    $awayPlayerentry['Playerentry']['safety'] = trim($awaySafeties);
                    $awayPlayerentry['Playerentry']['game_id'] = $gameId;
                    $playerEntries[$awayId] = $awayPlayerentry;
                }
                
                $homePlayer = $this->Player->find('first', array('conditions' => array('name' => 'Defense', 'school' => $homeUserName), 'recursive' => -1));
                if(!empty($homePlayer)) {
                    $homeId = $homePlayer['Player']['id'];
                    $homePlayerentry = $this->getPlayerentry($playerEntries, $homeId, $weekId);
                    $homePlayerentry['Playerentry']['def_tds'] = trim($homeTds);
                    $homePlayerentry['Playerentry']['safety'] = trim($homeSafeties);
                    $homePlayerentry['Playerentry']['game_id'] = $gameId;
                    $playerEntries[$homeId] = $homePlayerentry;
                }
            }
            
            return $playerEntries;
        }
        
        private function espnProcessDefensivePointsAllowed($gameId, $awayUserDiv, $homeUserDiv, $playerEntries, $weekId) {
            $awayUserSpan = $awayUserDiv->find('span[class=long-name]', 0);
            $awayUserName = $awayUserSpan->plaintext;
            $awayUserScoreDiv = $awayUserDiv->find('div[class=score icon-font-after]', 0);
            $awayUserScore = $awayUserScoreDiv->plaintext;
            
            $homeUserSpan = $homeUserDiv->find('span[class=long-name]', 0);
            $homeUserName = $homeUserSpan->plaintext;
            $homeUserScoreDiv = $homeUserDiv->find('div[class=score icon-font-before]', 0);
            $homeUserScore = $homeUserScoreDiv->plaintext;
            
            $awayPlayer = $this->Player->find('first', array('conditions' => array('name' => 'Defense', 'school' => $awayUserName), 'recursive' => -1));
            if(!empty($awayPlayer)) {
                $awayId = $awayPlayer['Player']['id'];
                $awayPlayerentry = $this->getPlayerentry($playerEntries, $awayId, $weekId);
                $awayPlayerentry['Playerentry']['points_allowed'] = $homeUserScore;
                $awayPlayerentry['Playerentry']['game_id'] = $gameId;
                $playerEntries[$awayId] = $awayPlayerentry;
            }
            
            $homePlayer = $this->Player->find('first', array('conditions' => array('name' => 'Defense', 'school' => $homeUserName), 'recursive' => -1));
            if(!empty($homePlayer)) {
                $homeId = $homePlayer['Player']['id'];
                $homePlayerentry = $this->getPlayerentry($playerEntries, $homeId, $weekId);
                $homePlayerentry['Playerentry']['points_allowed'] = $awayUserScore;
                $homePlayerentry['Playerentry']['game_id'] = $gameId;
                $playerEntries[$homeId] = $homePlayerentry;
            }
            return $playerEntries;
        }
        
        private function espnProcessBoxScoreCategories($tables, $playerEntries, $weekId, $schools) {
            foreach($tables as $table) {
                $caption = $table->find('caption',0);
                if($caption != null) {
                    $captionText = $caption->plaintext;
                    $category = null;
                    $school = null;
                    if(strpos($captionText, ' Passing')) {
                        $category = 'pass';
                        $school = substr($captionText, 0, strpos($captionText, ' Passing'));
                    } else if(strpos($captionText, ' Rushing')) {
                        $category = 'rush';
                        $school = substr($captionText, 0, strpos($captionText, ' Rushing'));
                    } else if(strpos($captionText, ' Receiving')) {
                        $category = 'receive';
                        $school = substr($captionText, 0, strpos($captionText, ' Receiving'));
                    } else if(strpos($captionText, ' Interceptions')) {
                        $category = 'Interceptions';
                        $school = substr($captionText, 0, strpos($captionText, ' Interceptions'));
                    } else if(strpos($captionText, ' Kick Returns')) {
                        $category = 'kreturns';
                        $school = substr($captionText, 0, strpos($captionText, ' Kick Returns'));
                    } else if(strpos($captionText, ' Punt Returns')) {
                        $category = 'preturns';
                        $school = substr($captionText, 0, strpos($captionText, ' Punt Returns'));
                    } else if(strpos($captionText, ' Kicking')) {
                        $category = 'kicking';
                        $school = substr($captionText, 0, strpos($captionText, ' Kicking'));
                    }
                    $school = str_replace('é', '&eacute;', $school);
                                    
                    if($category != null) {
                        $rows = $table->find('tr');
                        foreach($rows as $row) {
                            $nameTd = $row->find('td[class=name]',0);
                            if($nameTd != null) {
                                $name = $nameTd->plaintext;
                                if($name != "User" && $name != "TEAM") {
                                    if(in_array($school, $schools)) {
                                        if($category == 'kicking') {
                                            $name = 'Kickers';
                                        }
                                        $playerArray = $this->Player->find('all', array('conditions' => array('name' => $name, 'school' => $school), 'recursive' => -1));
                                        $countPlayer = count($playerArray);
                                        $player = null;
                                        if($countPlayer == 0) {
                                            echo "Player not found\n";
                                            $player = $this->Player->create();
                                            $player['name'] = $name;
                                            $player['school'] = $school;
                                            $player['school_id'] = "";
                                            //$player = $this->Player->save($player);
                                            //$this->Player->clear();
                                        } else if($countPlayer == 1) {
                                            $player = $playerArray[0];
                                        } else {
                                            // something went really wrong!
                                            echo "bad news!\n";
                                            echo $name."\n";
                                            echo $school."\n";
                                        }
                                            
                                        $kReturnTds = 0;
                                        $kReturnYards = 0;
                                        $pReturnTds = 0;
                                        $pReturnYards = 0;
                                        if($player != null && isset($player['Player'])) {
                                            $id = $player['Player']['id'];
                                            $Playerentry = $this->getPlayerentry($playerEntries, $id, $weekId);

                                            if("kicking" == $category) {
                                                $temp = $row->find('td[class=fg]',0);
                                                if($temp != null) {
                                                    $array = explode("/", $temp->plaintext);
                                                    if(count($array) == 2) {
                                                        $Playerentry['Playerentry']['field_goals'] = $array[0];
                                                    }
                                                }
                                                $temp = $row->find('td[class=xp]',0);
                                                if($temp != null) {
                                                    $array = explode("/", $temp->plaintext);
                                                    if(count($array) == 2) {
                                                        $Playerentry['Playerentry']['pat'] = $array[0];
                                                    }
                                                }
                                            } else if("kreturns" == $category) {
                                                $temp = $row->find('td[class=td]',0);
                                                if($temp != null) {
                                                    $kReturnTds = $temp->plaintext;
                                                    $Playerentry['Playerentry']['return_tds'] = $kReturnTds + $pReturnTds;
                                                }
                                                $temp = $row->find('td[class=yds]',0);
                                                if($temp != null) {
                                                    $kReturnYards = $temp->plaintext;
                                                    $Playerentry['Playerentry']['return_yards'] = $kReturnYards + $pReturnYards;
                                                }
                                            } else if("preturns" == $category) {
                                                $temp = $row->find('td[class=td]',0);
                                                if($temp != null) {
                                                    $pReturnTds = $temp->plaintext;
                                                    $Playerentry['Playerentry']['return_tds'] = $kReturnTds + $pReturnTds;
                                                }
                                                $temp = $row->find('td[class=yds]',0);
                                                if($temp != null) {
                                                    $pReturnYards = $temp->plaintext;
                                                    $Playerentry['Playerentry']['return_yards'] = $kReturnYards + $pReturnYards;
                                                }
                                            } else {
                                                $temp = $row->find('td[class=td]',0);
                                                if($temp != null) {
                                                    $Playerentry['Playerentry'][$category.'_tds'] = $temp->plaintext;
                                                }
                                                $temp = $row->find('td[class=yds]',0);
                                                if($temp != null) {
                                                    $Playerentry['Playerentry'][$category.'_yards'] = $temp->plaintext;
                                                }
                                            }
                                            $playerEntries[$id] = $Playerentry;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $playerEntries;
        }
        
        private function getPlayerentry($playerEntries, $id, $weekId) {
            if(isset($playerEntries[$id])) {
                $Playerentry = $playerEntries[$id];
            } else {
                $Playerentry = $this->find('first', array('conditions' => array('player_id' => $id, 'week_id' => $weekId), 'recursive' => -1));
            }
            if(empty($Playerentry)) {
                $Playerentry = $this->create();
                $Playerentry['Playerentry']['week_id'] = $weekId;
                $Playerentry['Playerentry']['player_id'] = $id;
            }
            return $Playerentry;
        }
        public function getTotalPointsByWeek($weekId, $playerIds) {
            $points = $this->find('first', array('fields' => array('SUM(Playerentry.points) AS points'), 'conditions' => array('week_id' => $weekId, 'player_id' => $playerIds), 'recursive' => -1));
            if(count($points) > 0) {
                return $points[0];
            }
        }
        public function getPlayerentries($userentry) {
            $playerEntries = array();
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['QB'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['qb_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['RB1'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['rb1_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['RB2'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['rb2_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['WR1'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['wr1_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['WR2'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['wr2_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['F'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['f_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['K'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['k_id']), 'recursive' => 0));
            $this->unbindModel(array('belongsTo' => array('Week', 'Player')));
            $playerEntries['D'] = $this->find('first', array('conditions' => array('week_id' => $userentry['week_id'], 'player_id' => $userentry['d_id']), 'recursive' => 0));
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
    }
?>