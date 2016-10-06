<?php
	App::uses('AppController', 'Controller');
	
	/**
        @SuppressWarnings(PHPMD.StaticAccess)
    */
	class UsersController extends AppController {

		public function add() {
			if ($this->request->is('post')) {
				$this->User->create();
				if ($this->User->save($this->request->data)) {
					$this->Session->setFlash(__('The User has been saved'));
					return $this->redirect(array('controller' => 'standings', 'action' => 'index'));
				}
				$this->Session->setFlash(
					__('The User could not be saved. Please, try again.')
				);
			}
		}
		
		public function beforeFilter() {
			parent::beforeFilter();
			// Allow users to register and logout.
			$this->Auth->allow('add', 'logout');
		}

		public function login() {
			if ($this->request->is('post')) {
				if ($this->Auth->login()) {
					return $this->redirect($this->Auth->redirect('/userentries'));
				}
				$this->Session->setFlash(__('Invalid username or password, try again'));
			}
		}

		public function logout() {
			return $this->redirect($this->Auth->logout());
		}
	}
?>