<table>

	<tr>
		<th>Place</th>
		<th>Owner</th>
		<th>Team</th>
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
		<td><?php echo $record['Team']['owner']; ?></td>
		<td><?php echo $this->Html->link($record['Team']['name'], '/userentries/view/'.$record['Team']['id']); ?></td>
		<td><?php echo $record['Team']['wins']; ?></td>
		<td><?php echo $record['0']['points']; ?></td>
		<td><?php echo $record['0']['points_behind']; ?></td>
		<td><?php echo $record['0']['playoff_points']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($standings); ?>
</table>