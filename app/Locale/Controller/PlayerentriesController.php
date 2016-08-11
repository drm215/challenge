<?php
	class PlayerentriesController extends AppController {
	
		public function index() {
			$this->set('records', $this->Playerentry->find('all', array('recursive' => 1)));
		}
		
		public function parser() {
			if(!isset($this->params['pass'][1])) {
				throw new NotFoundException("Cannot find week.");
			}
			$this->Playerentry->parser(strtoupper($this->params['pass'][0]));
		}
		
		public function espnParser() {
			$this->Playerentry->espnParser($this->params['pass'][0]);
		}
		
		public function edit($weekId) {
			if (!$weekId) { throw new NotFoundException(__('Invalid weekId')); }
			
			if ($this->request->is('post')) {
				$playerEntries = array();
				$data = $this->request->data['Playerentry'];
				foreach ($data as $key => $value) {
					$attributes = explode("__", $key);
					if(!array_key_exists($attributes[0], $playerEntries)) {
						$playerEntries[$attributes[1]]['id'] = $attributes[0];
						$playerEntries[$attributes[1]]['player_id'] = $attributes[1];
						$playerEntries[$attributes[1]]['week_id'] = $weekId;
					}
					$playerEntries[$attributes[1]][$attributes[2]] = $value;
				}

				foreach ($playerEntries as $playerEntry) {
					$this->Playerentry->create($playerEntry);
					if (!$this->Playerentry->save()) {
						throw new NotFoundException(__('Save unsuccessful'));
					}
					$this->Playerentry->clear();
				}
				$this->Standing = ClassRegistry::init('Standing');
				$this->Standing->calculateStandingsByWeek($weekId);
				$this->Standing->updateLowestWeek();
			}
			
			$ids = array();
			
			$this->Userentry = ClassRegistry::init('Userentry');
			$this->Player = ClassRegistry::init('Player');
			
			$Userentries = $this->Userentry->find('all', array('conditions' => array('week_id' => $weekId), 'recursive' => -1));
			$ids = array_unique($this->buildUniquePlayerIds($Userentries));
			
			$playerEntries = $this->Playerentry->find('all', array('conditions' => array('week_id' => $weekId, 'player_id' => $ids), 'recursive' => -1));
			$players = $this->Player->find('all', array('conditions' => array('id' => $ids), 'recursive' => -1, 'order' => array('school')));
			
			$persistedPlayerEntries = array();
			foreach ($playerEntries as $playerEntry) {
				$persistedPlayerEntries[$playerEntry['Playerentry']['player_id']] = $playerEntry['Playerentry'];
			}
			
			$processedPlayerEntries = array();
			foreach ($players as $player) {
				$playerId = $player['Player']['id'];
				$processedPlayerEntries[$playerId]['Player'] = $player['Player'];
				if(array_key_exists($playerId, $persistedPlayerEntries)) {
					$processedPlayerEntries[$playerId]['Playerentry'] = $persistedPlayerEntries[$playerId];
				} else {
					$processedPlayerEntries[$playerId]['Playerentry'] = "";
				}
			}
			$this->set('playerEntries', $processedPlayerEntries);
		}
		
		private function buildUniquePlayerIds($Userentries) {
			$ids = array();
			foreach ($Userentries as $Userentry) {
				array_push($ids, $Userentry['Userentry']['qb_id']);
				array_push($ids, $Userentry['Userentry']['rb1_id']);
				array_push($ids, $Userentry['Userentry']['rb2_id']);
				array_push($ids, $Userentry['Userentry']['wr1_id']);
				array_push($ids, $Userentry['Userentry']['wr2_id']);
				array_push($ids, $Userentry['Userentry']['flex_id']);
				array_push($ids, $Userentry['Userentry']['k_id']);
				array_push($ids, $Userentry['Userentry']['d_id']);
			}
			return $ids;
		}
	}
?>