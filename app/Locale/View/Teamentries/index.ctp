<table>
    <tr>
		<th>Week</th>
		<th>Points</th>
	</tr>
	<?php $existingWeeks = array(); ?>
    <?php foreach ($records as $record): ?>
    <tr>
		<td><?php echo $this->Html->link($record['Week']['name'], '/userentries/edit/'.$record['Teamentry']['id']); ?></td>
		<td><?php echo $record['Playerentry']['points']; ?></td>
		<?php array_push($existingWeeks, $record['Week']['id']); ?>
    </tr>
    <?php endforeach; ?>
	
	<?php foreach ($weeks as $week): ?>
	<tr>
		<?php
			if(!in_array($week['Week']['id'], $existingWeeks)) {
				?>
				<tr><td><?php echo $this->Html->link($week['Week']['name'], '/userentries/add/'.$week['Week']['id']); ?></td><td></td></tr>
		<?php
			}
		?>
		
    </tr>
	<?php endforeach; ?>
</table>

<?php unset($records); ?>
<?php unset($weeks); ?>