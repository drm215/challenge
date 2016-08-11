<?php 
	echo $this->Form->create("Teamentry"); 
	echo $this->Form->input("id", array('type' => 'hidden'));
	echo $this->Form->input("week_id", array('type' => 'hidden'));
	echo $this->Form->input("user_id", array('type' => 'hidden'));
?>

<h3><?php echo $title; ?></h3>

<table>
	<tr>
		<th colspan="2">Players</th>
		<th colspan="2">Passing</th>
		<th colspan="2">Rushing</th>
		<th colspan="2">Receiving</th>
		<th colspan="2">Returns</th>
		<th colspan="2">Kicking</th>
		<th colspan="7">Defense</th>
		<th/>
	</tr>
	<tr>
		<th>Pos</th>
		<th>Name</th>
		<th>Yards</th>
		<th>TDs</th>
		<th>Yards</th>
		<th>TDs</th>
		<th>Yards</th>
		<th>TDs</th>
		<th>Yards</th>
		<th>TDs</th>
		<th>FGs</th>
		<th>PATs</th>
		<th>PA</th>
		<th>Sacks</th>
		<th>Fumbles</th>
		<th>INTs</th>
		<th>TDs</th>
		<th>Safeties</th>
		<th>Points Allowed</th>
		<th>Points</th>
	</tr>



<?php
	$qbDisabled = false;
	$rb1Disabled = false;
	$rb2Disabled = false;
	$wr1Disabled = false;
	$wr2Disabled = false;
	$flexDisabled = false;
	$kDisabled = false;
	$dDisabled = false;
	
	if(isWeekLocked($week['Week']['lock_time']) == true) {
		$qbDisabled = true;
		$rb1Disabled = true;
		$rb2Disabled = true;
		$wr1Disabled = true;
		$wr2Disabled = true;
		$flexDisabled = true;
		$kDisabled = true;
		$dDisabled = true;
	} else {
		$qbDisabled = isPositionLocked($locks, $this->request->data, 'QB');
		$rb1Disabled = isPositionLocked($locks, $this->request->data, 'RB1');
		$rb2Disabled = isPositionLocked($locks, $this->request->data, 'RB2');
		$wr1Disabled = isPositionLocked($locks, $this->request->data, 'WR1');
		$wr2Disabled = isPositionLocked($locks, $this->request->data, 'WR2');
		$flexDisabled = isPositionLocked($locks, $this->request->data, 'FLEX');
		$kDisabled = isPositionLocked($locks, $this->request->data, 'K');
		$dDisabled = isPositionLocked($locks, $this->request->data, 'D'); 
	}
	
	echo "<tr>";
	echo "<td>QB</td>";
	echo "<td>".$this->Form->input("qb_id", array('label' => '', 'type' => 'select', 'options' => $players['QB'], 'empty' => '', 'disabled' => $qbDisabled))."</td>";
	printColumns('qb', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>RB</td>";
	echo "<td>".$this->Form->input("rb1_id", array('label' => '', 'type' => 'select', 'options' => $players['RB'], 'empty' => '', 'disabled' => $rb1Disabled))."</td>";
	printColumns('rb1', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>RB</td>";
	echo "<td>".$this->Form->input("rb2_id", array('label' => '', 'type' => 'select', 'options' => $players['RB'], 'empty' => '', 'disabled' => $rb2Disabled))."</td>";
	printColumns('rb2', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>WR</td>";
	echo "<td>".$this->Form->input("wr1_id", array('label' => '', 'type' => 'select', 'options' => $players['WR'], 'empty' => '', 'disabled' => $wr1Disabled))."</td>";
	printColumns('wr1', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>WR</td>";
	echo "<td>".$this->Form->input("wr2_id", array('label' => '', 'type' => 'select', 'options' => $players['WR'], 'empty' => '', 'disabled' => $wr2Disabled))."</td>";
	printColumns('wr2', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>RB/WR/TE</td>";
	echo "<td>".$this->Form->input("flex_id", array('label' => '', 'type' => 'select', 'options' => $players['F'], 'empty' => '', 'disabled' => $flexDisabled))."</td>";
	printColumns('flex', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>K</td>";
	echo "<td>".$this->Form->input("k_id", array('label' => '', 'type' => 'select', 'options' => $players['K'], 'empty' => '', 'disabled' => $kDisabled))."</td>";
	printColumns('k', $playerentries);
	echo "</tr>";
	
	echo "<tr>";
	echo "<td>D</td>";
	echo "<td>".$this->Form->input("d_id", array('label' => '', 'type' => 'select', 'options' => $players['D'], 'empty' => '', 'disabled' => $dDisabled))."</td>";
	printColumns('d', $playerentries);
	echo "</tr>";

?>
	<tr><td>Total</td><td colspan="17"/><td><td><?php echo $totalPoints; ?></td></tr>
</table>
<?php 


	if(!isWeekLocked($week['Week']['lock_time']) == true) {
		echo $this->Form->end('Submit');
	}
	
	function printColumns($position, $playerentries) {
		printColumn($position, 'pass_yards', $playerentries);
		printColumn($position, 'pass_tds', $playerentries);
		printColumn($position, 'rush_yards', $playerentries);
		printColumn($position, 'rush_tds', $playerentries);
		printColumn($position, 'receive_yards', $playerentries);
		printColumn($position, 'receive_tds', $playerentries);
		printColumn($position, 'return_yards', $playerentries);
		printColumn($position, 'return_tds', $playerentries);
		printColumn($position, 'field_goals', $playerentries);
		printColumn($position, 'pat', $playerentries);
		printColumn($position, 'points_allowed', $playerentries);
		printColumn($position, 'sacks', $playerentries);
		printColumn($position, 'fumble_recovery', $playerentries);
		printColumn($position, 'def_ints', $playerentries);
		printColumn($position, 'def_tds', $playerentries);
		printColumn($position, 'safety', $playerentries);
		printColumn($position, 'points_allowed', $playerentries);
		printColumn($position, 'points', $playerentries);
	}
	
	function printColumn($position, $column, $playerentries) {
		if(isset($playerentries[$position]['Playerentry'])) {
			$value = $playerentries[$position]['Playerentry'][$column];
		} else {
			$value = "-";
		}
		echo "<td>".$value."</td>";
	}
	
	function isPositionLocked($locks, $data, $position) {
		if(isset($data[$position]) && isset($locks[$data[$position]['school']])) {
			if(strtotime(date(DateTime::ATOM)) > strtotime($locks[$data[$position]['school']])) {
				return true;
			}
		}
		return false;
	}
	
	function isWeekLocked($weekLock) {
		$gameTime = strtotime($weekLock);
		if(time() - $gameTime > 0) {
			return true;
		}
		return false;
	}
?>
