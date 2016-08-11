<?php
	App::uses('AppController', 'Controller');
	
	class UsersController extends AppController {

		public function index() {
			$this->User->recursive = 0;
			$this->set('Users', $this->paginate());
		}
		
		public function view($id = null) {
			$this->User->id = $id;
			if (!$this->User->exists()) {
				throw new NotFoundException(__('Invalid User'));
			}
			$this->set('User', $this->User->read(null, $id));
		}
		
		public function add() {
			if ($this->request->is('post')) {
				$this->User->create();
				if ($this->User->save($this->request->data)) {
					$this->Session->setFlash(__('The User has been saved'));
					return $this->redirect(array('action' => 'index'));
				}
				$this->Session->setFlash(
					__('The User could not be saved. Please, try again.')
				);
			}
		}

		public function edit($id = null) {
			$this->User->id = $id;
			if (!$this->User->exists()) {
				throw new NotFoundException(__('Invalid User'));
			}
			if ($this->request->is('post') || $this->request->is('put')) {
				if ($this->User->save($this->request->data)) {
					$this->Session->setFlash(__('The User has been saved'));
					return $this->redirect(array('action' => 'index'));
				}
				$this->Session->setFlash(
					__('The User could not be saved. Please, try again.')
				);
			} else {
				$this->request->data = $this->User->read(null, $id);
				unset($this->request->data['User']['password']);
			}
		}

		public function delete($id = null) {
			$this->request->onlyAllow('post');

			$this->User->id = $id;
			if (!$this->User->exists()) {
				throw new NotFoundException(__('Invalid User'));
			}
			if ($this->User->delete()) {
				$this->Session->setFlash(__('User deleted'));
				return $this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash(__('User was not deleted'));
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
					return $this->redirect($this->Auth->redirect('/Userentries'));
				}
				$this->Session->setFlash(__('Invalid username or password, try again'));
			}
		}

		public function logout() {
			return $this->redirect($this->Auth->logout());
		}
		
		public function test() {
			$this->User->updateUserWins();
		}
	}
?>