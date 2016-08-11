<table id="standings-table">
    <thead>
        <tr>
            <th>Place</th>
            <th>Owner</th>
            <th>Team</th>
            <th>Wins</th>
            <th>Total Points</th>
            <th>Points Behind</th>
            <th>Playoff Points</th>
            <th>Week 1</th>
            <th>Week 2</th>
            <th>Week 3</th>
            <th>Week 4</th>
            <th>Week 5</th>
            <th>Week 6</th>
            <th>Week 7</th>
            <th>Week 8</th>
            <th>Week 9</th>
            <th>Week 10</th>
            <th>Week 11</th>
            <th>Week 12</th>
            <th>Week 13</th>
            <th>Week 14</th>
        </tr>
    </thead>
</table>

<table>
    <tr>
        <th>Place</th>
        <th>Owner</th>
        <th>Team</th>
        <th>Wins</th>
        <th>Total Points</th>
        <th>Points Behind</th>
        <th>Playoff Points</th>
        <?php
            foreach($weeks as $week) {
                echo "<th>";
                echo "Week ".$week;
                echo "</th>";
            }
        ?>
    </tr>

    <?php
        $count = 1;
        foreach ($totalPointsArray as $key => $value) {
    ?>
    <tr>
        <td><?php echo $count; $count++; ?></td>
        <td><?php echo $detailsArray[$key]['owner'] ?></td>
        <td><?php echo $detailsArray[$key]['name'] ?></td>
        <td><?php echo $detailsArray[$key]['wins'] ?></td>
        <td><?php echo $detailsArray[$key]['total_points'] ?></td>
        <td><?php echo $detailsArray[$key]['behind_leader'] ?></td>
        <td><?php echo $detailsArray[$key]['behind_playoff'] ?></td>
        <?php
            foreach($weeks as $week) {
                echo "<td>";
                if(isset($detailsArray[$key][$week])) {
                    echo $this->Html->link($detailsArray[$key][$week], '/userentries/detail/'.$key.'/'.$week);
                } else {
                    echo "0";
                }
                echo "</td>";
            }
        ?>
    </tr>
    <?php } ?>
</table>

<script>
$(document).ready(function() {
    $('#standings-table').DataTable({
    	searching: false,
    	paging: false
    });
});
</script>