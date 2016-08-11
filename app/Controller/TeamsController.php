<?php
	App::uses('AppController', 'Controller');
	
	class TeamsController extends AppController {

		public function index() {
			$this->Team->recursive = 0;
			$this->set('teams', $this->paginate());
		}
		
		public function view($id = null) {
			$this->Team->id = $id;
			if (!$this->Team->exists()) {
				throw new NotFoundException(__('Invalid team'));
			}
			$this->set('team', $this->Team->read(null, $id));
		}
		
		public function add() {
			if ($this->request->is('post')) {
				$this->Team->create();
				if ($this->Team->save($this->request->data)) {
					$this->Session->setFlash(__('The team has been saved'));
					return $this->redirect(array('action' => 'index'));
				}
				$this->Session->setFlash(
					__('The team could not be saved. Please, try again.')
				);
			}
		}

		public function edit($id = null) {
			$this->Team->id = $id;
			if (!$this->Team->exists()) {
				throw new NotFoundException(__('Invalid team'));
			}
			if ($this->request->is('post') || $this->request->is('put')) {
				if ($this->Team->save($this->request->data)) {
					$this->Session->setFlash(__('The team has been saved'));
					return $this->redirect(array('action' => 'index'));
				}
				$this->Session->setFlash(
					__('The team could not be saved. Please, try again.')
				);
			} else {
				$this->request->data = $this->Team->read(null, $id);
				unset($this->request->data['Team']['password']);
			}
		}

		public function delete($id = null) {
			$this->request->onlyAllow('post');

			$this->Team->id = $id;
			if (!$this->Team->exists()) {
				throw new NotFoundException(__('Invalid team'));
			}
			if ($this->Team->delete()) {
				$this->Session->setFlash(__('Team deleted'));
				return $this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash(__('Team was not deleted'));
			return $this->redirect(array('action' => 'index'));
		}
		
		public function beforeFilter() {
			parent::beforeFilter();
			// Allow users to register and logout.
			$this->Auth->allow('add', 'logout', 'index');
		}

		public function login() {
			if ($this->request->is('post')) {
				if ($this->Auth->login()) {
					return $this->redirect($this->Auth->redirect('/teamentries'));
				}
				$this->Session->setFlash(__('Invalid username or password, try again'));
			}
		}

		public function logout() {
			return $this->redirect($this->Auth->logout());
		}
		
		public function test() {
			$this->Team->updateTeamWins();
		}
	}
?>