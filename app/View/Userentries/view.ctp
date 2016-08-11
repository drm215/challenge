<h3><?php echo $title; ?></h3>

<table>
    <tr>
		<th>Week</th>
		<th>Points</th>
	</tr>
    <?php foreach ($records as $record): ?>
    <tr>
		<td><?php echo $this->Html->link($record['Week']['name'], '/userentries/detail/'.$record['Standing']['user_id'].'/'.$record['Week']['id']); ?></td>
		<td><?php echo $record['Standing']['points']; ?>
    </tr>
    <?php endforeach; ?>
	
</table>

<?php unset($records); ?>
<?php unset($weeks); ?>