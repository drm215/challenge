<?php if(isset($players)) { ?>

	<table>
		
	
		<?php 
			printPlayers($players['QB']);
			printPlayers($players['RB']);
			printPlayers($players['WR']);
			printPlayers($players['K']);
			printPlayers($players['D']);
			
		?>
	</table>

<? }
	
	function printHeader() {
		echo "<tr><th>Player</th><th>School</th><th>Position</th><th>Points</th><th>Games</th><th>Average</th></tr>";
	}
	
	function printPlayers($rows) {
		printHeader();
		foreach($rows as $row) {
			echo "<tr>";
			echo "<td>";
			echo $row['Player']['name'];
			echo "</td>";
			echo "<td>";
			echo $row['Player']['school'];
			echo "</td>";
			echo "<td>";
			echo $row['Player']['position'];
			echo "</td>";
			echo "<td>";
			echo $row['0']['points'];
			echo "</td>";
			echo "<td>";
			echo $row['0']['count'];
			echo "</td>";
			echo "<td>";
			echo round($row['0']['points'] / $row['0']['count'], 2);
			echo "</td>";
			echo "</tr>";
		}
	}
?>