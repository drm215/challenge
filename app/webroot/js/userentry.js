"use strict";
var playerData = {
    data: [],
    weekId: null,
    userId: null,
    playerentries: null,
		userentries: null,
    initialize: function(userentry, playerentries, weekId, userId, errors) {
        playerData.weekId = weekId;
        playerData.userId = userId;
        playerData.userentry = userentry;
        playerData.playerentries = playerentries;
        playerData.errors = errors;
        $('#player-data').DataTable({
            "ajax": {
                url: playerData.getAjaxUrl('QB'),
                complete: function(data) {
                    playerData.data[playerData.getBasePosition($('#hidden-position').val())] = data.responseJSON;
                }
             },
             "dataType": "json",
             "order": [7, 'desc'],
             "columnDefs": [{
                 "targets": [0,1,2],
                 "visible": false
             }],
             "processing": true
        });

        $('#selection-data').DataTable({
            bFilter: false,
            paging: false,
            bInfo: false,
            bSort: false,
            autoWidth: false
        });

        playerData.updateColumnVisibility();
        playerData.setSelectedPlayers();
        playerData.triggerErrors();

        $('.toggle-position').on('click', function() {
            playerData.updatePlayerDataTable($(this).attr('id'));
        });
        $(document).on('click', '.select-player', function() {
            playerData.selectPlayer($(this).attr('id'));
        });
    },
    updateColumnVisibility: function() {
        var $position = $('#hidden-position').val();
        if($position === 'D') {
            $('#player-data').DataTable().columns('.offense').visible(false);
            $('#player-data').DataTable().columns('.kicking').visible(false);
            $('#player-data').DataTable().columns('.defense').visible(true);
        } else if($position === 'K') {
            $('#player-data').DataTable().columns('.offense').visible(false);
            $('#player-data').DataTable().columns('.kicking').visible(true);
            $('#player-data').DataTable().columns('.defense').visible(false);
        } else {
            $('#player-data').DataTable().columns('.offense').visible(true);
            $('#player-data').DataTable().columns('.kicking').visible(false);
            $('#player-data').DataTable().columns('.defense').visible(false);
        }
    },
    updatePlayerDataTable: function(position) {
        $('#hidden-position').val(playerData.getBasePosition(position));
        $('#player-data').DataTable().ajax.url(playerData.getAjaxUrl(position)).load();

        $('.highlight').removeClass('highlight');
        $('#' + position.toLowerCase() + '-row').addClass('highlight');
        playerData.updateColumnVisibility();
    },
    getAjaxUrl: function(position) {
        return "/userentries/getPlayerData/" + playerData.weekId + "/" + playerData.userId + "/" + $('#hidden-position').val() + "/" + position;
    },
    getBasePosition: function(position) {
        var basePosition = position;
        if(position === 'RB1' || position === 'RB2') {
            basePosition = 'RB';
        } else if(position === 'WR1' || position === 'WR2') {
            basePosition = 'WR';
        }
        return basePosition;
    },
    setSelectedPlayers: function() {
        var totalPoints = 0;
        var positions = ["QB", "RB1", "RB2", "WR1", "WR2", "F", "K", "D"];
        for (var i = 0; i < positions.length; i++) {
			if(!$.isEmptyObject(playerData.userentry)) {
				if(playerData.userentry[positions[i]]["id"]) {
					playerData.setSelectedPlayer(positions[i], playerData.userentry[positions[i]]["id"], playerData.userentry[positions[i]]["name"], playerData.getTooltip(positions[i]));
					if(playerData.playerentries[positions[i]]['Playerentry']) {
						$('#' + positions[i].toLowerCase() + '-player-points').text(playerData.playerentries[positions[i]]['Playerentry']['points']);
						totalPoints += parseFloat(playerData.playerentries[positions[i]]['Playerentry']['points']);
					}
				}
			}
            $('#total-player-points').text(totalPoints);
        }
    },
    setSelectedPlayer: function(position, id, name, title) {
        $('#' + position.toLowerCase() + '-id').val(id);
        $('#' + position.toLowerCase() + '-player-name').text(name);// + '\n' + userentry[positions[i]]["School"]["name"]);
        $('#' + position.toLowerCase() + '-player-name').attr('title', title);
    },
    getTooltip: function(position, id) {
        var player = null;
        var name = null;
        var school = null;
        var pposition = null;
        var espn_id = null;
        if(id) {
            player = playerData.data[position].data[id];
            var nameSchool = player[4].split('<br/>');
            name = nameSchool[0];
            school = nameSchool[1];
            pposition = player[1];
            espn_id = player[2];
        } else {
            player = playerData.userentry[position];
            name = player.name;
            school = player.School.name;
            pposition = player.position;
            espn_id = player.School.espn_id;
        }
        var playerEntry = playerData.playerentries[position];
        var html = '<div id="tooltip-player-info" class="tooltip-player-info">';
        html += '<h3><img src="../../app/webroot/img/logos/' + espn_id + '.png" title="' + school +'"> ' + name + '</h3>';
        html += pposition + ' | ' + school;
        html += '</div>';
        if(playerEntry && playerEntry['Playerentry']) {
            html += '<table>';
            html += '<thead><tr>';
            switch(pposition) {
                case 'K':
                    html += '<th colspan="2">Kick</th>';
                    html += '</tr><tr>';
                    html += '<td>FGs</td><td>PATs</td>';
                    break;
                case 'D':
                    html += '<th colspan="5">Defense</th>';
                    html += '</tr><tr>';
                    html += '<td>PA</td><td>Fum</td><td>INTs</td><td>TDs</td><td>Safe</td>';
                    break;
                default:
                    html += '<th colspan="2">Pass</th>';
                    html += '<th colspan="2">Rush</th>';
                    html += '<th colspan="2">Rec</th>';
                    html += '<th colspan="2">Return</th>';
                    html += '</tr><tr>';
                    html += '<td>Yards</td><td>TDs</td>';
                    html += '<td>Yards</td><td>TDs</td>';
                    html += '<td>Yards</td><td>TDs</td>';
                    html += '<td>Yards</td><td>TDs</td>';
            }

            html += '</tr>';
            html += '</tr></thead>';
            html += '<tbody><tr>';

            switch(pposition) {
                case 'K':
                    html += '<td>' + playerEntry['Playerentry']['field_goals'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['pat'] + '</td>';
                    break;
                case 'D':
                    html += '<td>' + playerEntry['Playerentry']['points_allowed'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['fumble_recovery'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['def_ints'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['def_tds'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['safety'] + '</td>';
                    break;
                default:
                    html += '<td>' + playerEntry['Playerentry']['pass_yards'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['pass_tds'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['rush_yards'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['rush_tds'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['receive_yards'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['receive_tds'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['return_yards'] + '</td>';
                    html += '<td>' + playerEntry['Playerentry']['return_tds'] + '</td>';
            }
            html += '</tr></tbody>';
            html += '</table>';
        }
        return html;
    },
    selectPlayer: function(id) {
        var data = playerData.data[playerData.getBasePosition($('#hidden-position').val())].data[id];
	      var name = data[5].replace(/<(?:.|\n)*?>/gm, '');
        playerData.setSelectedPlayer($('.highlight').attr('id').split('-')[0], data[0], name.substr(name.indexOf('">') + 2), playerData.getTooltip($('#hidden-position').val(), id));
        playerData.validatePlayersUnique();
    },
    triggerErrors: function() {
        console.log(playerData.errors);
        $.each(playerData.errors, function(key, value) {
            if(!$.isEmptyObject(value)) {
                $('#' + key + '-player-name-error').html('<br/>' + value);
            }
        });
    },
    validatePlayersUnique: function() {
        var rb1 = false;
        var rb2 = false;
        var wr1 = false;
        var wr2 = false;
        var f = false;
        var error = false;
        
        if(!$.isEmptyObject($('#rb1-player-name').text()) && $('#rb1-player-name').text() === $('#rb2-player-name').text()) {
            rb1 = true;
            rb2 = true;
            error = true;
        }
        if(!$.isEmptyObject($('#rb1-player-name').text()) && $('#rb1-player-name').text() === $('#f-player-name').text()) {
            rb1 = true;
            f = true;
            error = true;
        }
        if(!$.isEmptyObject($('#rb2-player-name').text()) && $('#rb2-player-name').text() === $('#f-player-name').text()) {
            rb2 = true;
            f = true;
            error = true;
        }
        
        if(!$.isEmptyObject($('#wr1-player-name').text()) && $('#wr1-player-name').text() === $('#wr2-player-name').text()) {
            wr1 = true;
            wr2 = true;
            error = true;
        }
        if(!$.isEmptyObject($('#wr1-player-name').text()) && $('#wr1-player-name').text() === $('#f-player-name').text()) {
            wr1 = true;
            f = true;
            error = true;
        }
        if(!$.isEmptyObject($('#wr2-player-name').text()) && $('#wr2-player-name').text() === $('#f-player-name').text()) {
            wr2 = true;
            f = true;
            error = true;
        }
        
        $('.player-data-error').html('');
        if(rb1) {
            $('#rb1-player-name-error').html('<br/>This player cannot be used more than once.');
        }
        if(rb2) {
            $('#rb2-player-name-error').html('<br/>This player cannot be used more than once.');
        }
        if(wr1) {
            $('#wr1-player-name-error').html('<br/>This player cannot be used more than once.');
        }
        if(wr2) {
            $('#wr2-player-name-error').html('<br/>This player cannot be used more than once.');
        }
        if(f) {
            $('#f-player-name-error').html('<br/>This player cannot be used more than once.');
        }
        playerData.toggleSubmitButton(error);
    },
    toggleSubmitButton: function(error) {
      $('.submit').find(':submit').prop('disabled', error);
      var opacity = '1';
      if(error) {
        opacity = '.5';
      }
      $('.submit').find(':submit').css('opacity', opacity);
    }
};