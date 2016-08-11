<table>

    <tr>
		<th>ID</th>
		<th>Name</th>
	</tr>

    <?php 
		$class = 'Player';
		foreach ($records as $record): 
	?>
    <tr>
        <td><?php echo $record[$class]['id']; ?></td>
		<td><?php echo $record[$class]['name']; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php unset($records); ?>
</table>