<?php
    class Userentry extends AppModel {

        public $belongsTo = array(
            "Week", "User",
            "QB" => array("className" => "Player", "foreignKey" => "qb_id"),
            "RB1" => array("className" => "Player", "foreignKey" => "rb1_id"),
            "RB2" => array("className" => "Player", "foreignKey" => "rb2_id"),
            "WR1" => array("className" => "Player", "foreignKey" => "wr1_id"),
            "WR2" => array("className" => "Player", "foreignKey" => "wr2_id"),
            "F" => array("className" => "Player", "foreignKey" => "f_id"),
            "K" => array("className" => "Player", "foreignKey" => "k_id"),
            "D" => array("className" => "Player", "foreignKey" => "d_id")
        );
        public $validate = array(
            'qb_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'qb_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'qb_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'rb1_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'rb1_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-unique' => array(
                    'rule' => array('validatePlayerUnique', 'rb1_id', 'rb2_id', 'f_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player cannot be used more than once.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'rb1_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'rb2_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'rb2_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-unique' => array(
                    'rule' => array('validatePlayerUnique', 'rb2_id', 'rb1_id', 'f_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player cannot be used more than once.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'rb2_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'wr1_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'wr1_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-unique' => array(
                    'rule' => array('validatePlayerUnique', 'wr1_id', 'wr2_id', 'f_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player cannot be used more than once.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'wr1_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'wr2_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'wr2_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-unique' => array(
                    'rule' => array('validatePlayerUnique', 'wr2_id', 'wr1_id', 'f_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player cannot be used more than once.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'wr2_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'f_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'f_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-unique-rb' => array(
                    'rule' => array('validatePlayerUnique', 'f_id', 'rb1_id', 'rb2_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player cannot be used more than once.'
                )
                ,
                'rule-unique-wr' => array(
                    'rule' => array('validatePlayerUnique', 'f_id', 'wr1_id', 'wr2_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player cannot be used more than once.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'f_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'k_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'k_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'k_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            ),
            'd_id' => array(
                'rule-validatePlayerNotLocked' => array(
                    'rule' => array('validatePlayerNotLocked', 'd_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player is locked.'
                ),
                'rule-validatePlayerNotAlreadyPlayed' => array(
                    'rule' => array('validatePlayerNotAlreadyPlayed', 'd_id'),
                    'allowEmpty' => 'false',
                    'message' => 'This player has already been played.'
                )
            )
        );

        public function validatePlayerNotLocked($playerId, $position) {
            $this->Player = ClassRegistry::init('Player');
            $previous = $this->find('first', array('conditions' => array('id' =>  $this->data['Userentry']['id']), 'recursive' => -1));
            if($previous['Userentry'][$position] ==  $this->data['Userentry'][$position]) {
              return true;
            } else {
              return !$this->Player->isPlayerLocked($playerId[$position], $this->data['Userentry']['week_id']);
            }
        }

        public function validatePlayerUnique($playerId, $positionOne, $positionTwo, $positionThree) {
            if($this->data['Userentry'][$positionOne] == "") {
                return true;
            }
            if($this->data['Userentry'][$positionOne] == $this->data['Userentry'][$positionTwo] || $this->data['Userentry'][$positionOne] == $this->data['Userentry'][$positionThree]) {
                return false;
            }
            return true;
        }

        public function validatePlayerNotAlreadyPlayed($playerId, $position) {
            if(!empty($this->data['Userentry'][$position])) {
                $previous = $this->find('all', array(
                    'recursive' => -1,
                    'conditions' => array('OR' => array('qb_id' => $playerId,'rb1_id' => $playerId,'rb2_id' => $playerId,'wr1_id' => $playerId,'wr2_id' => $playerId,'f_id' => $playerId,'k_id' => $playerId,'d_id' => $playerId), 'week_id < ' => $this->data['Userentry']['week_id'], 'user_id' => $this->getCurrentUserId())
                ));
                if(!empty($previous)) {
                    return false;
                }
            }
            return true;
        }

        public function calculatePreviousUserEntries($weekId, $playoffFlag, $userId) {
            $userEntries = $this->find('all', array('conditions' => array('week_id' < $weekId, 'Userentry.playoff_fl' => $playoffFlag, 'user_id' => $userId)));
            $calculatedUserEntries = array();
            foreach($userEntries as $temp) {
                if(isset($temp['QB']['id'])) {
                    $calculatedUserEntries['QB'][$temp['QB']['id']] = $temp['QB'];
                }
                if(isset($temp['RB1']['id'])) {
                    $calculatedUserEntries['RB'][$temp['RB1']['id']] = $temp['RB1'];
                    $calculatedUserEntries['F'][$temp['RB1']['id']] = $temp['RB1'];
                }
                if(isset($temp['RB2']['id'])) {
                    $calculatedUserEntries['RB'][$temp['RB2']['id']] = $temp['RB2'];
                    $calculatedUserEntries['F'][$temp['RB2']['id']] = $temp['RB2'];
                }
                if(isset($temp['WR1']['id'])) {
                    $calculatedUserEntries['WR'][$temp['WR1']['id']] = $temp['WR1'];
                    $calculatedUserEntries['F'][$temp['WR1']['id']] = $temp['WR1'];
                }
                if(isset($temp['WR2']['id'])) {
                    $calculatedUserEntries['WR'][$temp['WR2']['id']] = $temp['WR2'];
                    $calculatedUserEntries['F'][$temp['WR2']['id']] = $temp['WR2'];
                }
                if(isset($temp['F']['id'])) {
                    $calculatedUserEntries['RB'][$temp['F']['id']] = $temp['F'];
                    $calculatedUserEntries['WR'][$temp['F']['id']] = $temp['F'];
                    $calculatedUserEntries['F'][$temp['F']['id']] = $temp['F'];
                }
                if(isset($temp['K']['id'])) {
                    $calculatedUserEntries['K'][$temp['K']['id']] = $temp['K'];
                }
                if(isset($temp['D']['id'])) {
                    $calculatedUserEntries['D'][$temp['D']['id']] = $temp['D'];
                }
            }
            return $calculatedUserEntries;
        }
    }
?>