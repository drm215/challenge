<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	public $components = array(
        'Session',
        'Auth' => array(
				'loginAction' => array(
				'controller' => 'users',
				'action' => 'login'
			),
            'loginRedirect' => array(
                'controller' => 'Userentries',
                'action' => 'index'
            ),
            'logoutRedirect' => array(
                'controller' => 'pages',
                'action' => 'display',
                'home'
            ),
            'authenticate' => array(
                'Form' => array(
                    'passwordHasher' => 'Blowfish',
					'userModel' => 'User',
					'fields' => array('username' => 'email', 'password' => 'password')
                )
            )
        )
    );

    public function beforeFilter() {
        $this->Auth->allow('');
    }
	
		function safe_json_encode($value){
				if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
					$encoded = json_encode($value, JSON_PRETTY_PRINT);
				} else {
					$encoded = json_encode($value);
				}
				switch (json_last_error()) {
					case JSON_ERROR_NONE:
						return $encoded;
					case JSON_ERROR_DEPTH:
						return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
					case JSON_ERROR_STATE_MISMATCH:
						return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
					case JSON_ERROR_CTRL_CHAR:
						return 'Unexpected control character found';
					case JSON_ERROR_SYNTAX:
						return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
					case JSON_ERROR_UTF8:
						$clean = $this->utf8ize($value);
						return $this->safe_json_encode($clean);
					default:
						return 'Unknown error'; // or trigger_error() or throw new Exception()

				}
			}

		private function utf8ize($mixed) {
			if (is_array($mixed)) {
				foreach ($mixed as $key => $value) {
					$mixed[$key] = $this->utf8ize($value);
				}
			} else if (is_string ($mixed)) {
				return utf8_encode($mixed);
			}
			return $mixed;
		}
}
