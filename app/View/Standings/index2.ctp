<table>
	<tr>
		<th>Place</th>
		<th>Owner</th>
		<th>Team</th>
		<th>Wins</th>
		<th>Total Points</th>
		<th>Points Behind</th>
		<th>Playoff Points</th>
		<?php
			foreach($weeks as $week) {
				echo "<th>";
				echo "Week ".$week;
				echo "</th>";
			}
		?>
	</tr>

    <?php 
		$count = 1;
		foreach ($totalPointsArray as $key => $value) {
	?>
    <tr>
		<td><?php echo $count; $count++; ?></td>
		<td><?php echo $detailsArray[$key]['owner'] ?></td>
		<td><?php echo $detailsArray[$key]['name'] ?></td>
		<td><?php echo $detailsArray[$key]['wins'] ?></td>
		<td><?php echo $detailsArray[$key]['total_points'] ?></td>
		<td><?php echo $detailsArray[$key]['behind_leader'] ?></td>
		<td><?php echo $detailsArray[$key]['behind_playoff'] ?></td>
		<?php
			foreach($weeks as $week) {
				echo "<td>";
				if(isset($detailsArray[$key][$week])) {
					echo $detailsArray[$key][$week];
				} else {
					echo "0";
				}
				echo "</td>";
			}
		?>
    </tr>
    <?php } ?>
    
</table>