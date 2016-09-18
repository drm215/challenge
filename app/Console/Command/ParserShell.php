<?php
/**
 * AppShell file
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
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Shell', 'Console');

/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 
 * @package       app.Console.Command
 */
class ParserShell extends AppShell {

    public $uses = array('Playerentry', 'Player', 'School', 'Game', 'User');
    
    public function test() {
        echo "This is a test.";
    }
    
    public function espnParser() {
      echo "Beginning ESPN Parser\n";
			$gameId = '';
			if(isset($this->args[0])) {
				$gameId = $this->args[0];
			}
			echo "Game ID = ". $gameId . "\n";
			$this->Playerentry->espnParser($this->args[0]);
      echo "Ending ESPN Parser\n";
    }
    
    public function playerParser() {
        echo "Beginning Player Parser\n";
        $this->Player->parser();
        echo "Ending Player Parser\n";
    }
    
    public function schoolParser() {
        echo "Beginning School Parser\n";
        $this->School->parser();
        echo "Ending School Parser\n";
    }
    
    public function gamesParser() {
        echo "Beginning Player gamesParser\n";
        $weekId = null;
        if(isset($this->args[0])) {
            $weekId = $this->args[0];
        }
        $this->Game->parser($weekId);
        echo "Ending Player gamesParser\n";
    }
  
    public function updateUserWins() {
		  $this->User->updateUserWins();
	  }
}
