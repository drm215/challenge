<?php
	class WeeksController extends AppController {
		
		public function index() {
				$this->set('records', $this->Week->find('all'));
		}
	}
?>