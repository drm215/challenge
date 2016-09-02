<h3><?php echo $title; ?></h3>
<table>
	<tr>
		<th colspan="3">Players</th>
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
		<th>School</th>
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
	printColumns('QB', $playerentries, $record, $schools);
	printColumns('RB1', $playerentries, $record, $schools);
	printColumns('RB2', $playerentries, $record, $schools);
	printColumns('WR1', $playerentries, $record, $schools);
	printColumns('WR2', $playerentries, $record, $schools);
	printColumns('F', $playerentries, $record, $schools);
	printColumns('K', $playerentries, $record, $schools);
	printColumns('D', $playerentries, $record, $schools);
?>
	<tr><td>Total</td><td colspan="16"/><td><td><?php echo $totalPoints; ?></td></tr>
</table>
<?php 
	
	function printColumns($position, $playerentries, $player, $schools) {
		if(isset($player[$position]['locked']) && $player[$position]['locked'] == 1) {
			echo "<tr><td>".$player[strtoupper($position)]['position']."</td>";
			echo "<td>".$player[strtoupper($position)]['name']."</td>";
			echo "<td>".$schools[$playerentries[$position]['Player']['school_id']]['name']."</td>";
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
			printColumn($position, 'fumble_recovery', $playerentries);
			printColumn($position, 'def_ints', $playerentries);
			printColumn($position, 'def_tds', $playerentries);
			printColumn($position, 'safety', $playerentries);
			printColumn($position, 'points', $playerentries);
			echo "</tr>";
		} else {
			echo "<tr><td colspan=\"19\"><i>HIDDEN</i></td></tr>";
		}
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
			$gameTime = strtotime($locks[$data[$position]['school']]);
			if(time() - $gameTime > 0) {
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