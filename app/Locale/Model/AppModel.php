<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
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
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

	protected function getESPNPositionId($position) {
		$id = "";
		switch ($position) {
			case "QB":
				$id = "1";
				break;
			case "RB":
				$id = "3";
				break;
			case "WR":
				$id = "5";
				break;
			case "K":
				$id = "7";
				break;
			case "D":
				$id = "8";
				break;
		}
		return $id;
	}
	
	protected function getESPNWeekId($weekId) {
		return 27+$weekId;
	}
	
	function getCurrentUserId() {
	  // for CakePHP 2.x:
	  App::uses('CakeSession', 'Model/Datasource');
	  $Session = new CakeSession();

	  $user = $Session->read('Auth.User');
	  return $user['id'];
	}
}
