<?php
	class PlayersController extends AppController {
		
		public function index() {
			$this->set('records', $this->Player->find('all'));
		}
		
		public function parser() {
			if(!isset($this->params['pass'][0])) {
				throw new NotFoundException("Cannot find position.");
			}
			
			$this->Player->parser(strtoupper($this->params['pass'][0]));
		}
	}
?>

