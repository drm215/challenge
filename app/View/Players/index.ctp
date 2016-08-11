<?php
    $this->Paginator->options(array(
        'update' => '#content',
        'evalScripts' => true
    ));
    foreach($players as $player) {
        echo $player['Player']['name']."<br>";
    }
?>