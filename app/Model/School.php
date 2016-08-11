<?php
	class School extends AppModel {

		public $hasMany = array('Player');

		public function parser() {
			App::import('Vendor', 'simple_html_dom', array('file'=>'simple_html_dom.php'));
			$this->Conference = ClassRegistry::init('Conference', 'Player');

			$this->espnProcessSchools();
			//$this->espnDownloadImage();
		}

		private function espnDownloadImage() {
		    $schools = $this->find('list', array('fields' => ('espn_id')));
		    foreach($schools as $school) {
		        $url = "a.espncdn.com/combiner/i?img=/i/teamlogos/ncaa/500/" . $school . ".png&h=25&w=25";
		        pr($url);
                file_put_contents("webroot/img/logos/" . $school . ".png", file_get_contents($url));
		    }
		}

		private function espnProcessSchools() {
			$this->espnProcessConferenceDivs(file_get_html("http://www.espn.com/college-football/teams"));
		}

		private function espnProcessConferenceDivs($html) {
			$fbsConferenceDivs = $html->find('div[class=span-2]',0);
			$conferenceDivs = $fbsConferenceDivs->find('div[class=mod-container mod-open-list mod-teams-list-medium mod-no-footer]');
			foreach($conferenceDivs as $conferenceDiv) {
				$this->espnProcessConferenceDiv($conferenceDiv);
			}
		}

		private function espnProcessConferenceDiv($conferenceDiv) {
			$headerDiv = $conferenceDiv->find('div[class=mod-header colhead]',0);
			$conference = $headerDiv->plaintext;
			$this->espnProcessSchoolsDiv($conferenceDiv->find('div[class=mod-content]',0), $this->getConferenceId($conference));
		}

		private function espnProcessSchoolsDiv($schoolsDiv, $conferenceId) {
			$schools = $schoolsDiv->find('li');
			foreach($schools as $school) {
				$schoolLink = $school->find('a[class=bi]',0);
				$espnLink = $schoolLink->href;
				$bPos = strlen("http://www.espn.com/college-football/team/_/id/");
				$espnId = substr($espnLink, $bPos, strpos($espnLink, "/", $bPos + 1) - $bPos);

				$school = $this->create();
				$school['School']['conference_id'] = $conferenceId['Conference']['id'];
				$school['School']['espn_id'] = $espnId;

				$this->espnProcessSchoolAndRoster($school);
			}
		}

		private function espnProcessSchoolAndRoster($school) {
			pr('begin espnProcessSchoolAndRoster');
			pr($school);
			$html = file_get_html("http://www.espn.com/college-football/team/roster/_/id/".$school['School']['espn_id']);
			if($html === FALSE) { 
				pr("error!");
			} else {
				$school['School']['name'] = $html->find('a[class=sub-brand-title]',0)->plaintext;
				$school = $this->espnSaveSchool($school, $html);
			}
			
			
			pr('end espnProcessSchoolAndRoster');
		}

		private function espnProcessSchoolRoster($school, $html) {
			$playerTable = $html->find('table[class=tablehead]',0);
			$playerRows = $playerTable->find('tr');
			for($i = 2; $i < count($playerRows); $i++) {
				$this->Player->parser($playerRows[$i], $school['School']['id']);
			}
		}

		private function espnSaveSchool($school, $html) {
			pr('begin espnSaveSchool');
			pr($school);
			$tempSchool = $this->find('first', array('conditions' => array('name' => $school['School']['name']), 'fields' => array('id'), 'recursive' => -1));
			if(empty($tempSchool)) {
				if($this->save($school)) {
					echo $school['School']['name']." saved successfully.\n";
					$school['School']['id'] = $this->id;
					
					$this->espnProcessSchoolRoster($school, $html);
				} else {
					echo $school['School']['name']." not saved successfully.\n";
				}
			} else {
				echo $school['School']['name']." is being skipped because it already exists.\n";
				$school['School']['id'] = $tempSchool['School']['id'];
			}
			pr('end espnSaveSchool');
			return $school;
		}

		private function getConferenceId($conference) {
			pr('begin getConferenceId: ' . $conference);
			return $this->Conference->find('first', array('fields' => array('id'), 'conditions' => array('name' => $conference), 'recursive' => -1));
		}

		public function findAndAdjustIndex() {
		    $schools = $this->find('all', array('recursive' => -1));
		    $temp = array();
		    foreach($schools as $school) {
		        $temp[$school['School']['id']] = $school['School'];
		    }
		    return $temp;
		}
	}
?>