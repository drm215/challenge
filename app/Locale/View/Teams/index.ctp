<table>

    <tr>
		<th>Name</th>
		<th>Owner</th>
	</tr>

    <?php 
		$class = 'Team';
		foreach ($users as $record): 
	?>
    <tr>
		<td><?php echo $record[$class]['name']; ?></td>
		<td><?php echo $record[$class]['owner']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($records); ?>
</table>