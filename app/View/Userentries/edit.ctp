<?php 
	echo $this->Form->create("Userentry"); 
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
		<th colspan="5">Defense</th>
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
		<th>Fumbles</th>
		<th>INTs</th>
		<th>TDs</th>
		<th>Safeties</th>
		<th>Points</th>
	</tr>



<?php	
	printPosition($this->Form, $Userentry, 'QB','QB', $players);
	printPosition($this->Form, $Userentry, 'RB1','RB',$players);
	printPosition($this->Form, $Userentry, 'RB2','RB',$players);
	printPosition($this->Form, $Userentry, 'WR1','WR',$players);
	printPosition($this->Form, $Userentry, 'WR2','WR',$players);
	printPosition($this->Form, $Userentry, 'F','F',$players);
	printPosition($this->Form, $Userentry, 'K','K',$players);
	printPosition($this->Form, $Userentry, 'D','D',$players);
	
	function printPosition($form, $Userentry, $position, $position2, $players) {
		echo "<tr>";
		echo "<td>".$position."</td>";
		echo "<td>";
		$playerLocked = false;
		if(isset($Userentry['Userentry']['week_locked']) && $Userentry['Userentry']['week_locked'] == true) { $playerLocked = true; }
		if(isset($Userentry[$position]['school_locked']) && $Userentry[$position]['school_locked'] == true) { $playerLocked = true; }
		if(isset($Userentry[$position]['Playerentry']) && $Userentry[$position]['Playerentry'] == true) { $playerLocked = true; }
		
		if($playerLocked) {
			$name = $Userentry[$position]['name'];
			if($name != "") { $name = $name . ', '. $Userentry[$position]['school']; }
			echo $name;
		} else {
			$selected = $Userentry[$position]['id'];
			echo $form->input(strtolower($position)."_id", array('label' => '', 'type' => 'select', 'options' => $players[$position2], 'empty' => '', 'selected' => $Userentry[$position]['id']));
		}
		echo "</td>";
		printColumns($Userentry[$position]['Playerentry']);
		echo "</tr>";
	}
?>
	<tr><td>Total</td><td colspan="15"/><td><td><?php echo $totalPoints; ?></td></tr>
</table>
<?php 
	if(!isWeekLocked($Userentry) == true) {
		echo $this->Form->end('Submit');
	}
	
	function printColumns($Playerentry) {
		printColumn('pass_yards', $Playerentry);
		printColumn('pass_tds', $Playerentry);
		printColumn('rush_yards', $Playerentry);
		printColumn('rush_tds', $Playerentry);
		printColumn('receive_yards', $Playerentry);
		printColumn('receive_tds', $Playerentry);
		printColumn('return_yards', $Playerentry);
		printColumn('return_tds', $Playerentry);
		printColumn('field_goals', $Playerentry);
		printColumn('pat', $Playerentry);
		printColumn('points_allowed', $Playerentry);
		printColumn('fumble_recovery', $Playerentry);
		printColumn('def_ints', $Playerentry);
		printColumn('def_tds', $Playerentry);
		printColumn('safety', $Playerentry);
		printColumn('points', $Playerentry);
	}
	
	function printColumn($column, $Playerentry) {
		if(isset($Playerentry[$column])) {
			$value = $Playerentry[$column];
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
	
	function isWeekLocked($Userentry) {
		$locked = false;
		if(isset($Userentry['Userentry']['week_locked']) && $Userentry['Userentry']['week_locked'] == true) {
			$locked = true;
		}
		return $locked;
	}
?>
