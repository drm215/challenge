<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>ID</th>
			<th>School</th>
			<th>School ID</th>
			<th>Position</th>
			<th>Year</th>
			<th>ESPN ID</th>
		</tr>
	</thead>

	<tbody>
		<?php
			foreach($duplicates as $duplicate) {
				echo "<tr>";
				echo "<td colspan=\"7\">";
				echo "<b>";
				echo $duplicate['Player']['name']." (".$duplicate[0]['ct'].")";
				echo "</b>";
				echo "</td>";
				echo "</tr>";
			}
		?>
	</tbody>
</table>