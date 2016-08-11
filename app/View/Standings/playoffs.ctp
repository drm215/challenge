<table>
	<thead>
		<tr>
			<th>Place</th>
			<th>Owner</th>
			<th>Team</th>
			<th>Bonus Points</th>
			<th>Total Points</th>
			<th>Elimination Points</th>
			<th>Week 11</th>
			<th>Week 12</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$counter = 1;
			foreach($playoffPointsArray as $key => $val) {
				echo "<tr>";
				echo "<td>";
				echo $counter;
				echo "</td>";
				echo "<td>";
				echo $detailsArray[$key]['owner'];
				echo "</td>";
				echo "<td>";
				echo $detailsArray[$key]['name'];
				echo "</td>";
				echo "<td>";
				echo $detailsArray[$key]['bonus_points'];
				echo "</td>";
				echo "<td>";
				echo $detailsArray[$key]['playoff_points'];
				echo "</td>";
				echo "<td>";
				echo $detailsArray[$key]['points_behind'];
				echo "</td>";
				echo "<td>";
				$value = "";
				if(isset($detailsArray[$key]['11'])) {
					$value = $this->Html->link($detailsArray[$key]['11'], '/userentries/detail/'.$key.'/11');
				}
				echo $value;
				echo "</td>";
				echo "<td>";
				$value = "";
				if(isset($detailsArray[$key]['12'])) {
					$value = $this->Html->link($detailsArray[$key]['12'], '/userentries/detail/'.$key.'/12');
				}
				echo $value;
				echo "</td>";
				echo "</tr>";
				$counter++;
			}
	
		?>
	</tbody>
</table>