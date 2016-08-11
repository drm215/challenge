<table>

	<tr>
		<th colspan="4">Players</th>
		<th></th>
		<th colspan="2">Passing</th>
		<th colspan="2">Rushing</th>
		<th colspan="2">Receiving</th>
		<th colspan="2">Returns</th>
		<th colspan="2">Kicking</th>
		<th colspan="6">Defense</th>
		<th/>
	</tr>
    <tr>
		<th>ID</th>
		<th>Name</th>
		<th>School</th>
		<th>Pos</th>
		<th>Week</th>
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
		<th>Points</th>
	</tr>

    <?php 
		foreach ($records as $record): 
	?>
    <tr>
        <td><?php echo $record['Playerentry']['id']; ?></td>
		<td><?php echo $record['Player']['name']; ?></td>
		<td><?php echo $record['Player']['school']; ?></td>
		<td><?php echo $record['Player']['position']; ?></td>
		<td><?php echo $record['Week']['name']; ?></td>
		<td><?php echo $record['Playerentry']['pass_yards']; ?></td>
		<td><?php echo $record['Playerentry']['pass_tds']; ?></td>
		<td><?php echo $record['Playerentry']['rush_yards']; ?></td>
		<td><?php echo $record['Playerentry']['rush_tds']; ?></td>
		<td><?php echo $record['Playerentry']['receive_yards']; ?></td>
		<td><?php echo $record['Playerentry']['receive_tds']; ?></td>
		<td><?php echo $record['Playerentry']['return_yards']; ?></td>
		<td><?php echo $record['Playerentry']['return_tds']; ?></td>
		
		<td><?php echo $record['Playerentry']['field_goals']; ?></td>
		<td><?php echo $record['Playerentry']['pat']; ?></td>
		
		<td><?php echo $record['Playerentry']['points_allowed']; ?></td>
		<td><?php echo $record['Playerentry']['sacks']; ?></td>
		<td><?php echo $record['Playerentry']['fumble_recovery']; ?></td>
		<td><?php echo $record['Playerentry']['def_ints']; ?></td>
		<td><?php echo $record['Playerentry']['def_tds']; ?></td>
		<td><?php echo $record['Playerentry']['safety']; ?></td>
		
		<td><b><?php echo $record['Playerentry']['points']; ?></b></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($records); ?>
</table>
