<?php
	echo $this->Form->create("TeamEntry");
	
	echo $this->Form->input("id", array('type' => 'hidden'));
	echo $this->Form->input("week_id", array('type' => 'hidden'));
	echo $this->Form->input("user_id", array('type' => 'hidden'));
	
	echo $this->Form->input("qb_id", array('label' => 'QB', 'type' => 'select', 'options' => $players['QB'], 'empty' => ''));
	echo $this->Form->input("rb1_id", array('label' => 'RB', 'type' => 'select', 'options' => $players['RB'], 'empty' => ''));
	echo $this->Form->input("rb2_id", array('label' => 'RB', 'type' => 'select', 'options' => $players['RB'], 'empty' => ''));
	echo $this->Form->input("wr1_id", array('label' => 'WR', 'type' => 'select', 'options' => $players['WR'], 'empty' => ''));
	echo $this->Form->input("wr2_id", array('label' => 'WR', 'type' => 'select', 'options' => $players['WR'], 'empty' => ''));
	echo $this->Form->input("flex_id", array('label' => 'RB/WR/TE', 'type' => 'select', 'options' => $players['F'], 'empty' => ''));
	echo $this->Form->input("k_id", array('label' => 'K', 'type' => 'select', 'options' => $players['K'], 'empty' => ''));
	echo $this->Form->input("d_id", array('label' => 'D', 'type' => 'select', 'options' => $players['D'], 'empty' => ''));
	echo $this->Form->end('Submit');
?>