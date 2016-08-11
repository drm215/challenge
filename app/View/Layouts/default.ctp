<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CFB Challenge');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
    <?php echo $this->Html->charset(); ?>
    <title>
        <?php echo $cakeDescription ?>:
        <?php echo $this->fetch('title'); ?>
    </title>
    <?php
        echo $this->Html->meta('icon');

        echo $this->Html->css('cake.generic');

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');

        App::uses('CakeSession', 'Model/Datasource');
        $Session = new CakeSession();
        $user = $Session->read('Auth.User');

        $header = $cakeDescription;
        if($user == null) {
            $header .= " | ".$this->Html->link('Register', '/users/add');
            $header .= " | ".$this->Html->link('Login', '/users/login');
        } else {
            $header .= " | ".$user['name']." (".$user['owner'].") | ".$this->Html->link('Logout', '/users/logout');
        }
        $header .= " | ".$this->Html->link('Standings', '/standings');
        //$header .= " | ".$this->Html->link('Playoffs', '/standings/playoffs');
        $header .= " | ".$this->Html->link('Weekly Results', '/standings/weekly');
        $header .= " | ".$this->Html->link('My Picks', '/userentries');
    ?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css"/>
    <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script src="http://cdn.datatables.net/1.10.11/js/jquery.dataTables.js"></script>
    <script src="http://cdn.datatables.net/plug-ins/1.10.11/api/processing().js"></script>

    <?php echo $this->Html->css('jquery.dataTables.min_override'); ?>
</head>
<body>
    <div id="container">
        <div id="header">
            <h1><?php echo $header; ?></h1>
        </div>
        <div id="content">
            <?php echo $this->Session->flash(); ?>
            <?php echo $this->fetch('content'); ?>
        </div>
        <div id="footer">
            <?php echo $this->Html->link(
                    $this->Html->image('cake.power.gif', array('alt' => $cakeDescription, 'border' => '0')),
                    'http://www.cakephp.org/',
                    array('target' => '_blank', 'escape' => false, 'id' => 'cake-powered')
                );
            ?>
            <p>
                <?php echo $cakeVersion; ?>
            </p>
        </div>
    </div>
    <?php echo $this->element('sql_dump'); ?>
</body>
</html>