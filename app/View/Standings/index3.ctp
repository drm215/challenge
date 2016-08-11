<table>

	<tr>
		<th>Place</th>
		<th>Owner</th>
		<th>User</th>
		<th>Wins</th>
		<th>Total Points</th>
		<th>Points Behind</th>
		<th>Playoff Points</th>
	</tr>

    <?php 
		$count = 1;
		foreach ($standings as $record): 
	?>
    <tr>
		<td><?php echo $count; $count++; ?></td>
		<td><?php echo $record['User']['owner']; ?></td>
		<td><?php echo $this->Html->link($record['User']['name'], '/userentries/view/'.$record['User']['id']); ?></td>
		<td><?php echo $record['User']['wins']; ?></td>
		<td><?php echo round($record['0']['points'], 2); ?></td>
		<td><?php 
		$points = $record['0']['points_behind'];
		if($points != '-') {
			$points = round($points, 2);
		}
		echo $points;
		?></td>
		<td><?php 
		$points = $record['0']['playoff_points'];
		if($points != '-') {
			$points = round($points, 2);
		}
		echo $points;
		?></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($standings); ?>
</table>