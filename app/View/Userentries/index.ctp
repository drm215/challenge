<table>
    <tr>
        <th>Week</th>
        <th>Points</th>
    </tr>
    <?php 
        $existingWeeks = array();
        foreach ($records as $record) {
            $existingWeeks[$record['Week']['id']] = $record['Playerentry']['points'];
        }
        foreach ($weeks as $week) {
            echo "<tr>";
            echo "<td>";
            echo $this->Html->link($week['Week']['name'], '/userentries/add/'.$week['Week']['id']);
            echo "</td>";
            echo "<td>";
            echo isset($existingWeeks[$week['Week']['id']]) ? $existingWeeks[$week['Week']['id']] : "";
            echo "</td>";
            echo "</tr>";  
        }
    ?>
</table>