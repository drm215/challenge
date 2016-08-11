<?php 
	echo $this->Form->create(); 
?>

<h3><?php echo "Week ".$this->params['pass'][0]; ?></h3>

<table>
	<tr>
		<th colspan="3">Players</th>
		<th colspan="2">Passing</th>
		<th colspan="2">Rushing</th>
		<th colspan="2">Receiving</th>
		<th colspan="2">Returns</th>
		<th colspan="2">Kicking</th>
		<th colspan="7">Defense</th>
		<th/>
	</tr>
	<tr>
		<th>Pos</th>
		<th>Name</th>
		<th>School</th>
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
		<th>Sacks</th>
		<th>Fumbles</th>
		<th>INTs</th>
		<th>TDs</th>
		<th>Safeties</th>
		<th>Points Allowed</th>
		<th>Points</th>
	</tr>

	<?php
	
		printPosition($playerEntries, $this);
	?>
</table>

<?php echo $this->Form->end('Submit'); ?>

<?php 
	function printPosition($playerEntries, $_this) {
		foreach ($playerEntries as $entry) {
			echo "<tr>";
			echo "<td>".$entry['Player']['position']."</td>";
			echo "<td>".$entry['Player']['name']."</td>";
			echo "<td>".$entry['Player']['school']."</td>";
			
			$passYards = 0;
			$passTDs = 0;
			$rushYards = 0;
			$rushTDs = 0;
			$receivingYards = 0;
			$receivingTDs = 0;
			$returnYards = 0;
			$returnTDs = 0;
			$fieldGoals = 0;
			$pats = 0;
			$pointsAllowed = 0;
			$sacks = 0;
			$fumbleRecoveries = 0;
			$defensiveInts = 0;
			$defensiveTDs = 0;
			$safeties = 0;
			$points = 0;
			$playerEntryId = "";
			
			$playerEntry = $entry['Playerentry'];
			if($playerEntry != "") {
				$passYards = $entry['Playerentry']['pass_yards'];
				$passTDs = $entry['Playerentry']['pass_tds'];
				$rushYards = $entry['Playerentry']['rush_yards'];
				$rushTDs = $entry['Playerentry']['rush_tds'];
				$receivingYards = $entry['Playerentry']['receive_yards'];
				$receivingTDs = $entry['Playerentry']['receive_tds'];
				$returnYards = $entry['Playerentry']['return_yards'];
				$returnTDs = $entry['Playerentry']['return_tds'];
				$fieldGoals = $entry['Playerentry']['field_goals'];
				$pats = $entry['Playerentry']['pat'];
				$pointsAllowed = $entry['Playerentry']['points_allowed'];
				$sacks = $entry['Playerentry']['sacks'];
				$fumbleRecoveries = $entry['Playerentry']['fumble_recovery'];
				$defensiveInts = $entry['Playerentry']['def_ints'];
				$defensiveTDs = $entry['Playerentry']['def_tds'];
				$safeties = $entry['Playerentry']['safety'];
				$points = $entry['Playerentry']['points'];
				$playerEntryId = $entry['Playerentry']['id'];
			}
			
			if($entry['Player']['position'] == 'QB' || $entry['Player']['position'] == 'RB' || $entry['Player']['position'] == 'WR' || $entry['Player']['position'] == 'TE') {
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__pass_yards', array('label' => '', 'value' => $passYards, 'size' => 3, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__pass_tds', array('label' => '', 'value' => $passTDs, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__rush_yards', array('label' => '', 'value' => $rushYards, 'size' => 3, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__rush_tds', array('label' => '', 'value' => $rushTDs, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__receive_yards', array('label' => '', 'value' => $receivingYards, 'size' => 3, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__receive_tds', array('label' => '', 'value' => $receivingTDs, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__return_yards', array('label' => '', 'value' => $returnYards, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__return_tds', array('label' => '', 'value' => $returnTDs, 'size' => 2, 'div' => ''))."</td>";
			} else {
				echo "<td/><td/><td/><td/><td/><td/><td/><td/>";
			}
			
			if($entry['Player']['position'] == 'K') {
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__field_goals', array('label' => '', 'value' => $fieldGoals, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__pat', array('label' => '', 'value' => $pats, 'size' => 2, 'div' => ''))."</td>";
			} else {
				echo "<td/><td/>";
			}
			
			if($entry['Player']['position'] == 'D') {
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__sacks', array('label' => '', 'value' => $sacks, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__fumble_recovery', array('label' => '', 'value' => $fumbleRecoveries, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__def_ints', array('label' => '', 'value' => $defensiveInts, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__def_tds', array('label' => '', 'value' => $defensiveTDs, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__safety', array('label' => '', 'value' => $safeties, 'size' => 2, 'div' => ''))."</td>";
				echo "<td>".$_this->Form->input($playerEntryId.'__'.$entry['Player']['id'].'__points_allowed', array('label' => '', 'value' => $pointsAllowed, 'size' => 3, 'div' => ''))."</td>";
			} else {
				echo "<td/><td/><td/><td/><td/><td/>";
			}
			echo "<td>".$points."</td>";
			echo "</tr>";
		}
	}
?>
