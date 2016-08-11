<h3><?php echo $week['Week']['name']; ?></h3>

<table>

	<tr>
		<th>Place</th>
		<th>Owner</th>
		<th>Team</th>
		<th>Points Points</th>
	</tr>

    <?php 
		$count = 1;
		foreach ($standings as $standing): 
	?>
    <tr>
		<td><?php echo $count; $count++; ?></td>
		<td><?php echo $standing['Team']['owner']; ?></td>
		<td><?php echo $this->Html->link($standing['Team']['name'], '/userentries/detail/'.$standing['Team']['id'].'/'.$week['Week']['id']); ?></td>
		<td><?php echo $standing['0']['points']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($standings); ?>
	
</table>

<h3>Past Weeks</h3>
<table>
	<tr><th>Week</th></tr>
	<?php
		foreach ($otherWeeks as $otherWeek): 
	?>
	<tr><td><?php echo $this->Html->link($otherWeek['Week']['name'], '/standings/weekly/'.$otherWeek['Week']['id']); ?></td></tr>
	
	<?php endforeach; ?>
</table>