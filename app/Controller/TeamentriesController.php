<?php
    class TeamentriesController extends AppController {
        
        public function index() {
            $this->Teamentry->unbindModel(array('belongsTo' => array('QB', 'RB1', 'RB2', 'WR1', 'WR2', 'F', 'K', 'D', 'Team')));
            $records = $this->Teamentry->find('all', array('conditions' => array('Teamentry.team_id' => $this->Auth->user('id')), 'recursive' => 0));
            $data = array();
            $this->Playerentry = ClassRegistry::init('Playerentry');
            foreach ($records as $record) {
                $weekId = $record['Week']['id'];
                $teamId = $this->Auth->user('id');
                
                $playersArray = array($record['Teamentry']['qb_id'],$record['Teamentry']['rb1_id'],$record['Teamentry']['rb2_id'],$record['Teamentry']['wr1_id'],$record['Teamentry']['wr2_id'],$record['Teamentry']['f_id'],$record['Teamentry']['k_id'],$record['Teamentry']['d_id']);                
                $record['Playerentry'] = $this->Playerentry->getTotalPointsByWeek($weekId, $playersArray);
                
                array_push($data, $record);
            }
            $this->set('records', $data);
            
            $this->Week = ClassRegistry::init('Week');
            $this->set('weeks', $this->Week->find('all', array('fields' => array('id', 'name'), 'recursive' => 0)));
        }
        
        public function edit($id) {
            if (!$id) {
                throw new NotFoundException(__('Invalid entry'));
            }
            
            $this->Teamentry->unbindModel(array('belongsTo' => array('Week', 'Team')));
            $entry = $this->Teamentry->find('first', array('conditions' => array('Teamentry.id' => $id), 'recursive' => 2));
            if (!$entry) {
                throw new NotFoundException(__('Invalid entry'));
            }
            
            if ($this->request->is(array('post', 'put'))) {
                $this->Teamentry->id = $id;
                if ($this->Teamentry->save($this->request->data)) {
                    $this->Session->setFlash(__('Your entry has been updated.'));
                    return $this->redirect(array('action' => 'index'));
                }
                $this->Session->setFlash(__('Unable to update your entry.'));
            }
            
            if (!$this->request->data) {
                $this->request->data = $entry;
            }
    
            $this->set('title', $this->Auth->user('name'). " (".$this->Auth->user('owner').") - Week ".$entry['Teamentry']['week_id']);
            
            $this->Player = ClassRegistry::init('Player');
            $this->set('players', $this->Player->getPlayers($this->Auth->user('id'), $entry['Teamentry']['week_id']));
            
            $this->Week = ClassRegistry::init('Week');
            $this->set('week', $this->Week->find('first', array('conditions' => array('id' => $entry['Teamentry']['week_id']), 'recursive' => -1)));
            
            $this->Playerentry = ClassRegistry::init('Playerentry');
            $this->set('playerentries', $this->Playerentry->getplayerentries($entry['Teamentry']));
            
            $playersArray = array($entry['Teamentry']['qb_id'],$entry['Teamentry']['rb1_id'],$entry['Teamentry']['rb2_id'],$entry['Teamentry']['wr1_id'],$entry['Teamentry']['wr2_id'],$entry['Teamentry']['f_id'],$entry['Teamentry']['k_id'],$entry['Teamentry']['d_id']);                
            $points = $this->Playerentry->getTotalPointsByWeek($entry['Teamentry']['week_id'], $playersArray);
            $calculatedPoints = $points['points'] == "" ? "0" : $points['points'];
            $this->set('totalPoints', $calculatedPoints);
        }
        
        public function add($weekId) {
            if (!$weekId) {
                throw new NotFoundException(__('Invalid week'));
            }
            if ($this->request->is('post')) {
                $this->Teamentry->create($this->request->data['TeamEntry']);
                $this->Teamentry->set('week_id', $weekId);
                $this->Teamentry->set('team_id', $this->Auth->user('id'));
                if ($this->Teamentry->save()) {
                    $this->Session->setFlash(__('Your entry has been saved.'));
                    return $this->redirect(array('action' => 'index'));
                }
                $this->Session->setFlash(__('Unable to add your entry.'));
            }

            $this->Player = ClassRegistry::init('Player');
            $this->set('players', $this->Player->getPlayers($this->Auth->user('id'), $weekId));
        }
        
        public function standings() {
            $object = $this->Teamentry->getTotalPoints();
            var_dump($object);
        }
        
        public function view($teamId) {
            if (!$teamId) {
                throw new NotFoundException(__('Invalid team'));
            }
            
            $this->Teamentry->Team->recursive=-1;            
            $team = $this->Teamentry->Team->findById($teamId, array('name', 'owner'));
            $this->set('title', $team['Team']['name']." (".$team['Team']['owner'].")");
            
            $this->Standing = ClassRegistry::init('Standing');
            $this->Standing->unbindModel(array('belongsTo' => array('Team')));
            $this->set('records', $this->Standing->find('all', array('conditions' => array('team_id' => $teamId/* , 'Week.lock_time < NOW()' */))));
        }
        
        public function detail($teamId, $weekId) {
            if (!$teamId) {
                throw new NotFoundException(__('Invalid team'));
            }
            if (!$weekId) {
                throw new NotFoundException(__('Invalid weekId'));
            }
            
            $record = $this->Teamentry->find('first', array('conditions' => array('team_id' => $teamId, 'week_id' => $weekId)));
            
            if($this->Auth->user('id') != $teamId) {
                
                $this->Week = ClassRegistry::init('Week');
                $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));
                
                $lockedTime = strtotime($week['Week']['lock_time']);
                if(time() - $lockedTime < 0) {
                    
                    foreach ($record as $key => $val) {
                        if($key != 'Teamentry' && $key != 'Week' && $key != 'Team') {
                            if(!in_array($val['school'], array_keys($locks))) {
                                $record[$key] = "";
                                
                                switch($key) {
                                    case "QB":
                                        $translatedKey = "qb_id";
                                        break;
                                    case "RB1":
                                        $translatedKey = "rb1_id";
                                        break;
                                    case "RB2":
                                        $translatedKey = "rb2_id";
                                        break;
                                    case "WR1":
                                        $translatedKey = "wr1_id";
                                        break;
                                    case "WR2":
                                        $translatedKey = "wr2_id";
                                        break;
                                    case "F":
                                        $translatedKey = "f_id";
                                        break;
                                    case "K":
                                        $translatedKey = "k_id";
                                        break;
                                    case "D":
                                        $translatedKey = "d_id";
                                        break;
                                }
                                $record['Teamentry'][$translatedKey] = "";
                            }
                        }    
                    }
                }
            }
    
            $this->set('title', $record['Team']['name']." (".$record['Team']['owner'].") - Week ".$record['Week']['name']);
            $this->set('record', $record);
            
            $this->Playerentry = ClassRegistry::init('Playerentry');
            $playerEntries = $this->Playerentry->getplayerentries($record['Teamentry']);
            $this->set('playerentries', $playerEntries);
            
            $playersArray = array($record['Teamentry']['qb_id'],$record['Teamentry']['rb1_id'],$record['Teamentry']['rb2_id'],$record['Teamentry']['wr1_id'],$record['Teamentry']['wr2_id'],$record['Teamentry']['f_id'],$record['Teamentry']['k_id'],$record['Teamentry']['d_id']);
            $points = $this->Playerentry->getTotalPointsByWeek($record['Teamentry']['week_id'], $playersArray);
            $calculatedPoints = $points['points'] == "" ? "0" : $points['points'];
            $this->set('totalPoints', $calculatedPoints);
        }
        
        public function beforeFilter() {
            $this->Auth->allow('view','detail');
        }
    }
?>