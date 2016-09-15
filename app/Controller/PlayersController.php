<?php
    class PlayersController extends AppController {

        public $components = array('RequestHandler', 'Paginator');

        public $paginate = array(
            'limit' => 25,
            'order' => array(
                'Player.name' => 'asc'
            )
        );

        public function duplicates() {
            $duplicates = $this->Player->find('all', array('fields' => array('name', 'COUNT(*) as ct'), 'group' => array('name HAVING COUNT(*) > 1'), 'order' => array('COUNT(*) DESC'), 'conditions' => array('NOT' => array('name' => array('Kickers', 'Defense'))), 'recursive' => -1));
            $this->set('duplicates', $duplicates);
        }

        public function getPlayers($start, $increment, $userId, $weekId, $playoffFlag) {
            $this->set('data', $this->Player->getPlayers($start, $increment, $userId, $weekId, $playoffFlag));
        }

        public function beforeFilter() {
            $this->Auth->allow('view','detail');
        }

        public function index() {
            
        }
    }
?>