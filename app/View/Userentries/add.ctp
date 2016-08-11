<?php
    echo $this->Html->script('userentry.js');
    $errors = getValidationErrors($this->validationErrors);
?>
<div id="player-wrapper">
    <div id="contentliquid"><div id="contentwrap">
    <div id="player-content">
        <table id="player-data" class="display compact" style="width: 100%">
            <thead>
                <tr>
									<th rowspan="2"></th>
                  <th rowspan="2">ID</th>
                  <th rowspan="2">Pos</th>
                  <th rowspan="2"></th>
									<th rowspan="2">Team</th>
                  <th rowspan="2">Player</th>
                  <th rowspan="2">Opp</th>
                  <th rowspan="2">Points</th>
                  <th colspan="2">Passing</th>
                  <th colspan="2">Rushing</th>
                  <th colspan="2">Receiving</th>
                  <th colspan="2">Return</th>
                  <th colspan="2">Kicking</th>
                  <th colspan="5">Defense</th>
                </tr>
                <tr>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="kicking">FGs</th>
                    <th class="kicking">PATs</th>
                    <th class="defense">PA</th>
                    <th class="defense">Fumbles</th>
                    <th class="defense">INTs</th>
                    <th class="defense">TDs</th>
                    <th class="defense">Safeties</th>
                </tr>
            </thead>
        </table>

    </div>
    </div></div>
    <div id="leftcolumnwrap">
    <div id="leftcolumn">

        <table id="selection-data" style="width: 270px; table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 20%">Pos</th>
                    <th style="width: 60%">Player</th>
                    <th style="width: 20%;">Points</th>
                </tr>
            </thead>
            <tbody>
                <tr id="qb-row" class="highlight"><td><a class="toggle-position" id="QB">QB</a></td><td><span id="qb-player-name"></span><span id="qb-player-name-error" class="player-data-error"></span></td><td id="qb-player-points" style="text-align:right"></td></tr>
                <tr id="rb1-row"><td><a class="toggle-position" id="RB1">RB1</a></td><td id="rb1"><span id="rb1-player-name"></span><span id="rb1-player-name-error" class="player-data-error"></span></td><td id="rb1-player-points" style="text-align:right"></td></tr>
                <tr id="rb2-row"><td><a class="toggle-position" id="RB2">RB2</a></td><td><span id="rb2-player-name"></span><span id="rb2-player-name-error" class="player-data-error"></span></td><td id="rb2-player-points" style="text-align:right"></td></tr>
                <tr id="wr1-row"><td><a class="toggle-position" id="WR1">WR1</a></td><td><span id="wr1-player-name"></span><span id="wr1-player-name-error" class="player-data-error"></span></td><td id="wr1-player-points" style="text-align:right"></td></tr>
                <tr id="wr2-row"><td><a class="toggle-position" id="WR2">WR2</a></td><td><span id="wr2-player-name"></span><span id="wr2-player-name-error" class="player-data-error"></span></td><td id="wr2-player-points" style="text-align:right"></td></tr>
                <tr id="f-row"><td><a class="toggle-position" id="F">F</a></td><td><span id="f-player-name"></span><span id="f-player-name-error" class="player-data-error"></span></td><td id="f-player-points" style="text-align:right"></td></tr>
                <tr id="k-row"><td><a class="toggle-position" id="K">K</a></td><td><span id="k-player-name"></span><span id="k-player-name-error" class="player-data-error"></span></td><td id="k-player-points" style="text-align:right"></td></tr>
                <tr id="d-row"><td><a class="toggle-position" id="D">D</a></td><td><span id="d-player-name"></span><span id="d-player-name-error" class="player-data-error"></span></td><td id="d-player-points" style="text-align:right"></td></tr>
                <tr><td><strong>Total</strong></td><td></td><td id="total-player-points" style="text-align:right"></td></tr>
            </tbody>
        </table>
    </div>
    </div>
</div>

<?php echo $this->Form->create("Userentry"); ?>
<input id="qb-id" name="qb-id" type="hidden"/>
<input id="rb1-id" name="rb1-id" type="hidden"/>
<input id="rb2-id" name="rb2-id" type="hidden"/>
<input id="wr1-id" name="wr1-id" type="hidden"/>
<input id="wr2-id" name="wr2-id" type="hidden"/>
<input id="f-id" name="f-id" type="hidden"/>
<input id="k-id" name="k-id" type="hidden"/>
<input id="d-id" name="d-id" type="hidden"/>

<input id="hidden-position" value="QB" type="hidden"/>

<?php echo $this->Form->end('Submit'); ?>

<script>
    $(document).ready(function() {
        playerData.initialize(
                JSON.parse('<?php echo $userentry; ?>'),
                JSON.parse('<?php echo $playerentries; ?>'),
                '<?php echo $this->params['pass'][0]; ?>',
                '<?php echo AuthComponent::user('id'); ?>',
                JSON.parse('<?php echo json_encode(getValidationErrors($this->validationErrors), JSON_HEX_APOS); ?>')
            );
        $(document).tooltip({
            content: function () {
                return $(this).prop('title');
            },
            tooltipClass: 'tooltip'
        });
    });
</script>

<?php
    function getValidationErrors($validationErrors) {
        $errors = array();
        $errors['qb'] = getValidationError($validationErrors['Userentry'], 'qb_id');
        $errors['rb1'] = getValidationError($validationErrors['Userentry'], 'rb1_id');
        $errors['rb2'] = getValidationError($validationErrors['Userentry'], 'rb2_id');
        $errors['wr1'] = getValidationError($validationErrors['Userentry'], 'wr1_id');
        $errors['wr2'] = getValidationError($validationErrors['Userentry'], 'wr2_id');
        $errors['f'] = getValidationError($validationErrors['Userentry'], 'f_id');
        $errors['k'] = getValidationError($validationErrors['Userentry'], 'k_id');
        $errors['d'] = getValidationError($validationErrors['Userentry'], 'd_id');
        CakeLog::write('debug', json_encode($errors, JSON_HEX_APOS ));
        return $errors;
    }
    function getValidationError($array, $key) {
        $value = "";
        if(isset($array[$key])) {
            $value = $array[$key][0];
        }
        return $value;
    }
?>