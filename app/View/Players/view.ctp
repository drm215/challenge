<?php

	if(isset($player)) {
		echo "<h2>";
		echo $player['Player']['name'];
		echo "</h2>";
		echo "<h3>";
		echo $player['Player']['position'].' | '.$player['Player']['school'];
		echo "</h3>";
		
		echo "<table>";
		echo "<tr>";
		echo "<th></th>";
		echo "<th colspan=\"2\">Passing</th>";
		echo "<th colspan=\"2\">Rushing</th>";
		echo "<th colspan=\"2\">Receiving</th>";
		echo "<th colspan=\"2\">Returns</th>";
		echo "<th colspan=\"2\">Kicking</th>";
		echo "<th colspan=\"6\">Defense</th>";
		echo "<th/>";
		echo "</tr>";
		echo "<tr>";
		echo "<th>Week</th>";
		echo "<th>Yards</th>";
		echo "<th>TDs</th>";
		echo "<th>Yards</th>";
		echo "<th>TDs</th>";
		echo "<th>Yards</th>";
		echo "<th>TDs</th>";
		echo "<th>Yards</th>";
		echo "<th>TDs</th>";
		echo "<th>FGs</th>";
		echo "<th>PATs</th>";
		echo "<th>PA</th>";
		echo "<th>Sacks</th>";
		echo "<th>Fumbles</th>";
		echo "<th>INTs</th>";
		echo "<th>TDs</th>";
		echo "<th>Safeties</th>";
		echo "<th>Points</th>";
		echo "</tr>";
		
		$points = 0;
		foreach($playerentries as $entry) {
			echo "<tr>";
			echo "<td>".$entry['Playerentry']['week_id']."</td>";
			echo "<td>".$entry['Playerentry']['pass_yards']."</td>";
			echo "<td>".$entry['Playerentry']['pass_tds']."</td>";
			echo "<td>".$entry['Playerentry']['rush_yards']."</td>";
			echo "<td>".$entry['Playerentry']['rush_tds']."</td>";
			echo "<td>".$entry['Playerentry']['receive_yards']."</td>";
			echo "<td>".$entry['Playerentry']['receive_tds']."</td>";
			echo "<td>".$entry['Playerentry']['return_yards']."</td>";
			echo "<td>".$entry['Playerentry']['return_tds']."</td>";
			echo "<td>".$entry['Playerentry']['field_goals']."</td>";
			echo "<td>".$entry['Playerentry']['pat']."</td>";
			echo "<td>".$entry['Playerentry']['points_allowed']."</td>";
			echo "<td>".$entry['Playerentry']['sacks']."</td>";
			echo "<td>".$entry['Playerentry']['fumble_recovery']."</td>";
			echo "<td>".$entry['Playerentry']['def_ints']."</td>";
			echo "<td>".$entry['Playerentry']['def_tds']."</td>";
			echo "<td>".$entry['Playerentry']['safety']."</td>";
			echo "<td>".$entry['Playerentry']['points']."</td>";
			echo "</tr>";
			$points = $points + $entry['Playerentry']['points'];
		}
		echo "<tr><td>Total</td><td colspan=\"16\"></td><td>".$points."</td></tr>";
		
		echo "</table>";
	}
?>