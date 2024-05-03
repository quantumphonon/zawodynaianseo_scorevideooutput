<?php

function get_tour_id($tour_name){
    $tour_id = '';
    $select = "SELECT ToId, ToCode FROM Tournament WHERE ToCode = '$tour_name'";
    $rs=safe_r_sql($select); 
    if($row = $rs->fetch_assoc()){
        $tour_id = $row['ToId'];
    }
    return $tour_id;
}

function get_match_type($tour_id){
    $match_type = 'no-live-match';
    $match_number = 0;

    $select = "SELECT * FROM Finals WHERE FinTournament = '$tour_id' && FinLive = 1 ORDER BY FinMatchNo ASC";
    $rs=safe_r_sql($select);
    if($row = $rs->fetch_assoc()){
        $match_type = 'ind';
        $match_number = $row['FinMatchNo'];
    }else{
        $select_team = "SELECT * FROM TeamFinals WHERE TfTournament = '$tour_id' && TfLive = 1 ORDER BY TfMatchNo ASC";
        $rs_team=safe_r_sql($select_team);
        if($row_team = $rs_team->fetch_assoc()){
            $match_type = 'team';
            $match_number = $row_team['TfMatchNo'];
        }
    }
    $match_type_data = [
        'match_type' => $match_type,
        'match_no' => $match_number,
        ];
    return $match_type_data;
}

function get_match_data($match_type_data, $tour_id){

    switch ($match_type_data['match_type']){
        
        case 'ind':
            
            $select_ind_match_data = "SELECT Fin.*, "
                ."En.EnFirstName, En.EnName, "
                ."Ev.EvFinEnds, Ev.EvFinArrows, Ev.EvFinSO, Ev.EvMatchMode, Ev.EvEventName, "
                ."Co.CoName, Co.CoCode "
                ."FROM Finals as Fin "
                ."JOIN Entries as En ON En.EnId = Fin.FinAthlete "
                ."JOIN Events as Ev ON Ev.EvCode = Fin.FinEvent "
                ."JOIN Countries as Co ON Co.CoId = En.EnCountry "
                ."WHERE Fin.FinTournament = '$tour_id' AND Fin.FinLive = 1 "
                ."AND Ev.EvTeamEvent = 0 AND Ev.EvTournament = '$tour_id' "
                ."ORDER BY Fin.FinMatchNo DESC";
            $rs = safe_r_sql($select_ind_match_data);
            $row = 0;
            $row2 = 0;
            while($data = $rs->fetch_assoc()){
                $row2 = $row;
                $row = $data;
            }
            if(($row['FinStatus'] == 0 && $row2['FinStatus'] == 0 && strlen($row['FinArrowstring']) < 1 && strlen($row['FinArrowstring'] < 1)) || ($row['FinStatus'] == 3 && $row2['FinStatus'] == 3)){
                $match_status = 'score_confirmed';
            }elseif($row['FinStatus'] == 0 || $row2['FinStatus'] == 0 ){
                if(strlen($row['FinTbDecoded'])>0 || strlen($row2['FinTbDecoded'])>0){
                    $match_status = 'tiebreak';
                }else{
                    $match_status = 'scoring';
                }
            }else{
                $match_status = 'error';
            }

            

            $athlete_1_data = [
                'name' => $row['EnFirstName'],
                'given_name' =>$row['EnName'],
                'arrow' => trim($row['FinArrowstring']),
                'score' => $row['FinScore'],
                'set_score' => $row['FinSetScore'],
                'tiebreak' => $row['FinTbDecoded'],
                'tiebreak_arrows' => $row['FinTiebreak'],
                'country' => $row['CoName'],
                'id' => $row['FinAthlete'],
                'winner' => $row['FinWinLose'],
                'country_code' =>$row['CoCode']
            ];

            $athlete_2_data = [
                'name' => $row2['EnFirstName'],
                'given_name' =>$row2['EnName'],
                'arrow' => trim($row2['FinArrowstring']),
                'score' => $row2['FinScore'],
                'set_score' => $row2['FinSetScore'],
                'tiebreak' => $row2['FinTbDecoded'],
                'tiebreak_arrows' => $row2['FinTiebreak'],
                'country' => $row2['CoName'],
                'id' => $row2['FinAthlete'],
                'winner' => $row2['FinWinLose'],
                'country_code' =>$row2['CoCode']
            ];
            $event = $row2['FinEvent'];
            break;
        case 'team':
            $select_team_match_data = "SELECT Tf.*, "
                ."Ev.EvFinEnds, Ev.EvFinArrows, Ev.EvFinSO, Ev.EvMatchMode, Ev.EvEventName, "
                ."Co.CoName, Co.CoCode "
                ."FROM TeamFinals as Tf "
                ."JOIN Events as Ev ON Ev.EvCode = Tf.TfEvent "
                ."JOIN Countries as Co ON Co.CoId = Tf.TfTeam "
                ."WHERE Tf.TfTournament = '$tour_id' AND Tf.TfLive = 1 "
                ."AND Ev.EvTeamEvent = 1 AND Ev.EvTournament = '$tour_id' "
                ."ORDER BY Tf.TfMatchNo DESC";
            
            $rs = safe_r_sql($select_team_match_data);
            $row = 0;
            $row2 = 0;
            while($data = $rs->fetch_assoc()){
                $row2 = $row;
                $row = $data;
            }
            if(($row['TfStatus'] == 0 && $row2['TfStatus'] == 0 && strlen($row['TfArrowstring']) < 1 && strlen($row['TfArrowstring'] < 1)) || ($row['TfStatus'] == 3 && $row2['TfStatus'] == 3)){
                $match_status = 'score_confirmed';
            }elseif($row['TfStatus'] == 0 || $row2['TfStatus'] == 0 ){
                if(strlen($row['TfTbDecoded'])>0 || strlen($row2['TfTbDecoded'])>0){
                    $match_status = 'tiebreak';
                }else{
                    $match_status = 'scoring';
                }
            }else{
                $match_status = 'error';
            }

            $athlete_1_data = [
                'name' => $row['CoName'],
                'given_name' =>"",
                'arrow' => trim($row['TfArrowstring']),
                'score' => $row['TfScore'],
                'set_score' => $row['TfSetScore'],
                'tiebreak' => $row['TfTbDecoded'],
                'tiebreak_arrows' => $row['TfTiebreak'],
                'country' => $row['CoName'],
                'id' => $row['TfTeam'],
                'subteam' => $row['TfSubTeam'],
                'event' => $row['TfEvent'],
                'winner' => $row['TfWinLose'],
                'country_code' =>$row['CoCode']
            ];

            $athlete_2_data = [
                'name' => $row2['CoName'],
                'given_name' =>"",
                'arrow' => trim($row2['TfArrowstring']),
                'score' => $row2['TfScore'],
                'set_score' => $row2['TfSetScore'],
                'tiebreak' => $row2['TfTbDecoded'],
                'tiebreak_arrows' => $row2['TfTiebreak'],
                'country' => $row2['CoName'],
                'id' => $row2['TfTeam'],
                'subteam' => $row2['TfSubTeam'],
                'event' => $row2['TfEvent'],
                'winner' => $row2['TfWinLose'],
                'country_code' =>$row2['CoCode']
            ];
            $event = $row2['TfEvent'];
            break;
    }
    $number_of_arrows = max(strlen($athlete_1_data['arrow']), strlen($athlete_2_data['arrow']));
    $end_number = ceil($number_of_arrows/$row['EvFinArrows']);
    
    $match_data = [
        'athlete_1_data' => $athlete_1_data,
        'athlete_2_data' => $athlete_2_data,
        'event' => $event,
        'event_name' => $row['EvEventName'],
        'match_status' => $match_status,
        'ends' => $row['EvFinEnds'],
        'fin_arrows' => $row['EvFinArrows'],
        'fin_so_arrows' => $row['EvFinSO'],
        'match_mode' => $row['EvMatchMode'],
        'number_of_arrows' => $number_of_arrows,
        'end_number' => $end_number,
        'match_type' => $match_type_data['match_type']
    ];
    
    return $match_data;
}

function output_match_data($match_data){
    ?>
    <table id=arrowvalues>
        <tr>
            <?php 
            output_name($match_data['athlete_1_data']); 
            output_match_score($match_data['athlete_1_data'], $match_data['match_mode']);
            output_arrows_values($match_data['athlete_1_data'], $match_data);
            ?>
        </tr>
        <tr>
            <?php 
            output_name($match_data['athlete_2_data']); 
            output_match_score($match_data['athlete_2_data'], $match_data['match_mode']);
            output_arrows_values($match_data['athlete_2_data'], $match_data);
            ?>
        </tr>
    </table>
    <?php

}

function output_name($athlete_data){
    ?>
    <th>
        <div id=AthName>
            <?php echo $athlete_data['name']; ?>
        </div>
	</th>
    <?php
}

function output_match_score($athlete_data, $match_mode){
    ?>
    <th>
        <div id=score>
            <?php
                if($match_mode){
                    echo $athlete_data['set_score'];
                }else{
                    echo $athlete_data['score'];
                }
            ?>
        </div>
	</th>
    <?php
}

function get_end_arrows($arrow, $match_data){
    $arrows_in_previous_ends = ($match_data['end_number']-1)*$match_data['fin_arrows'];
    $arrow_in_end = $match_data['fin_arrows'];
    $end_arrow = substr($arrow, $arrows_in_previous_ends, $arrow_in_end);
    return $end_arrow;
}


function decodearrowtext($Ar){
    switch ($Ar){
        case "A":
            return "M";
        case "B":
            return "1";
        case "C":
            return "2";
        case "D":
            return "3";
        case "E":
            return "4";
        case "F":
            return "5";		
        case "G":
            return "6";
        case "H":
            return "7";	
        case "I":
            return "8";					
        case "J":
            return "9";
        case "L":
            return "10";
        case "K":
            return "10";
        case "a":
            return "M*";
        case "b":
            return "1*";
        case "c":
            return "2*";
        case "d":
            return "3*";
        case "e":
            return "4*";
        case "f":
            return "5*";		
        case "g":
            return "6*";
        case "h":
            return "7*";	
        case "i":
            return "8*";					
        case "j":
            return "9*";
        case "l":
            return "10*";						
    }
}

function decodearrowvalue($Ar){
    switch ($Ar){
        case "A":
            return "0";
        case "B":
            return "1";
        case "C":
            return "2";
        case "D":
            return "3";
        case "E":
            return "4";
        case "F":
            return "5";		
        case "G":
            return "6";
        case "H":
            return "7";	
        case "I":
            return "8";					
        case "J":
            return "9";
        case "L":
            return "10";
        case "K":
            return "10";
        case "a":
            return "0";
        case "b":
            return "1";
        case "c":
            return "2";
        case "d":
            return "3";
        case "e":
            return "4";
        case "f":
            return "5";		
        case "g":
            return "6";
        case "h":
            return "7";	
        case "i":
            return "8";					
        case "j":
            return "9";
        case "l":
            return "10";						
    }
}

function output_arrows_values($athlete_data, $match_data){
    switch($match_data['match_status']){
        case 'score_confirmed':
            return 0;
        case 'error':
            return 0;
        case 'scoring':
            $end_arrows = get_end_arrows($athlete_data['arrow'], $match_data);
            $total = 0;
            for($i = 0; $i < $match_data['fin_arrows']; $i++){
                ?>
                <th>
                    <div id=scorearrow>
                        <?php    
                        echo decodearrowtext(substr($end_arrows, $i, 1));
                        $total=$total+decodearrowvalue(substr($end_arrows, $i, 1));
                        ?>
                    </div>
                </th>
                <?php
                } 
            ?>
            <th>
                <div id=score>
                    <?php echo $total; ?>
                </div>
            </th>
            <?php
            return 0;
        case 'tiebreak':
            if($match_data['match_type']=='ind'){
                $ath1_data = $match_data['athlete_1_data'];
                $ath2_data = $match_data['athlete_2_data'];
                $athlete_1_tb_num_of_arrows = count(explode(',', $ath1_data['tiebreak']));
                $athlete_2_tb_num_of_arrows = count(explode(',', $ath2_data['tiebreak']));
                $tiebreak_num_of_arrows = max($athlete_1_tb_num_of_arrows, $athlete_2_tb_num_of_arrows);
                ?>
                <th>
                    <div id=scorearrow>
                        <?php
                        $tiebreak_arrows = explode(',', $athlete_data['tiebreak']);     
                        echo $tiebreak_arrows[$tiebreak_num_of_arrows-1];
                        ?>
                    </div>
                </th>
                <?php
            }
            if($match_data['match_type']=='team'){
                $ath1_data = $match_data['athlete_1_data'];
                $ath2_data = $match_data['athlete_2_data']; 

                $athlete_1_tb_num_of_arrows = count(explode(',', $ath1_data['tiebreak']));
                $athlete_2_tb_num_of_arrows = count(explode(',', $ath2_data['tiebreak']));
                $athlete_tiebreak_result = explode(',', $athlete_data['tiebreak']);
                $tiebreak_num_of_arrows = max($athlete_1_tb_num_of_arrows, $athlete_2_tb_num_of_arrows);
                $end_arrows = $athlete_data['tiebreak_arrows'];
            for($i = 0; $i<($tiebreak_num_of_arrows-1);$i++){
                $end_arrows = substr($end_arrows,$match_data['fin_so_arrows']);
            }
            for($i = 0; $i < $match_data['fin_so_arrows']; $i++){
                ?>
                <th>
                    <div id=scorearrow>
                        <?php    
                        echo decodearrowtext(substr($end_arrows, $i, 1));
                       
                        ?>
                    </div>
                </th>
                <?php
                } 
            ?>
            <th>
                <div id=score>
                    <?php echo $athlete_tiebreak_result[$tiebreak_num_of_arrows-1]; ?>
                </div>
            </th>
            <?php
            return 0;
            }
            
    }
    return 0;
}

function output_archer_phase_data($archer_phase_data, $match_data){
    ?>
    <td id=bracket_blank></td>
    <?php if($archer_phase_data['status'] < 1){
        ?>
        <td id = bracket_name_loser>
        <?php
    }elseif($archer_phase_data['status'] < 2){
        ?>
        <td id = bracket_name>
        <?php
    }else{
        ?>
        <td id = bracket_name_current>
        <?php
    }
    ?>
        <?php echo $archer_phase_data['name'];?>
    </td>
    <?php if($archer_phase_data['status'] < 1){
        ?>
        <td id = bracket_score_loser>
        <?php
    }elseif($archer_phase_data['status'] < 2){
        ?>
        <td id = bracket_score>
        <?php
    }else{
        ?>
        <td id = bracket_score_current>
        <?php
    }
    ?>
        <?php
        if($match_data['match_mode']){
            echo $archer_phase_data['set_score'];
        }else{
            echo $archer_phase_data['score'];
        }
         ?>
    </td>
    <?php
    return 0;
}

function  get_tour_name($tour_id){
    $select_tour_name = "SELECT ToName FROM Tournament WHERE ToId ='$tour_id'";
    $rs_tour_name = safe_r_sql($select_tour_name);
    if($data_tour_name = $rs_tour_name->fetch_assoc()){
        return $data_tour_name['ToName'];
    }else{
        return 0;
        }
}

function get_match_data_for_bracket($match_number_start, $match_number_end, $match_data, $tour_id){
    //wymaga dodania wersji dla zespołów
    if($match_data['match_type'] == 'ind'){
        $match_event = $match_data['event'];
        $select_data = "SELECT Fin.*, En.EnFirstName "
            ."FROM Finals as Fin "
            ."LEFT JOIN Entries as En on Fin.FinAthlete = En.EnId "
            ."WHERE Fin.FinTournament = '$tour_id' AND Fin.FinEvent = '$match_event' "
            ."AND Fin.FinMatchNo > '$match_number_start' AND Fin.FinMatchNo < '$match_number_end' "
            ."ORDER BY Fin.FinMatchNo ASC";
        $rs_data = safe_r_sql($select_data);
    
        $data_array = array();
    
        while($data = $rs_data->fetch_assoc()){
            $ath_data = [
                'name' => $data['EnFirstName'],
                'score' => $data['FinScore'],
                'set_score' => $data['FinSetScore'],
                'winner' => $data['FinWinLose'],
                'status' => 0,
                'id' => $data['FinAthlete'],
            ];
            $data_array[] = $ath_data;
        }
        }
        if($match_data['match_type'] == 'team'){
            $match_event = $match_data['event'];
            echo $match_event;
            $select_data = "SELECT Tf.*, Co.CoName "
                ."FROM TeamFinals as Tf "
                ."LEFT JOIN Countries as Co ON Co.CoId = Tf.TfTeam "
                ."WHERE Tf.TfTournament = '$tour_id' AND Tf.TfEvent = '$match_event' "
                ."AND Tf.TfMatchNo > '$match_number_start' AND Tf.TfMatchNo < '$match_number_end' "
                ."ORDER BY Tf.TfMatchNo ASC";

            $rs_data = safe_r_sql($select_data);
            $data_array = array();

            while($data = $rs_data->fetch_assoc()){
                echo "bla";
                if($data['TfSubTeam']>0){
                    $team_name = $data['CoName']." ".strval($data['TfSubTeam']+1);
                }else{
                    $team_name = $data['CoName'];
                }
                $ath_data = [
                    'name' => $team_name,
                    'score' => $data['TfScore'],
                    'set_score' => $data['TfSetScore'],
                    'winner' => $data['TfWinLose'],
                    'status' => 0,
                    'id' => $data['TfTeam'],
                    'subteam' => $data['TfSubTeam'],
                ];
                $data_array[] = $ath_data; 
                }
    

    }
    return $data_array;
}
function modify_bracket_phase_status($bracket_phase_data, $match_data){
    $id_1 = $match_data['athlete_1_data']['id'];
    $id_2 = $match_data['athlete_2_data']['id'];
    for($i=0; $i<count($bracket_phase_data); $i++){
        if($i%2 > 0){
            $bracket_phase_data[$i-1]['status'] = 1 - $bracket_phase_data[$i]['winner'];
        }else{
            $bracket_phase_data[$i+1]['status'] = 1 - $bracket_phase_data[$i]['winner'];
        }
    }   
    for($i=0; $i<count($bracket_phase_data); $i++){
        if($bracket_phase_data[$i]['id'] == $id_1 || $bracket_phase_data[$i]['id'] == $id_2){
            //$bracket_phase_data[$i]['status'] = 2; 
        }
    }
    return $bracket_phase_data;
}

function output_bracket_data($tour_name, $match_data, $quaterfinal_data, $semifinal_data, $final_data){

    ?>
<table>
    <tr>
        <td id=bracket_blank></td>
        
        <td colspan=14; id=tour_name border-style-bottom=solid>
            <?php echo($tour_name); ?>
        </td>
    </tr>
    <tr>
        <td id=bracket_blank></td>
        
        <td colspan=14 id=tour_name>
            <?php echo($match_data['event_name']); ?>
        </td>
    </tr>
    <tr>
        <td></td><td></td>
        <td colspan=2 id=tour_name><?php echo('ĆWIERĆFINAŁ'); ?></td>
        <td></td><td></td>
        <td colspan=2 id=tour_name><?php echo('PÓŁFINAŁ'); ?></td>
        <td></td><td></td><td></td>
        <td colspan=2 id=tour_name><?php echo('FINAŁ'); ?></td>
    </tr>
    <tr><td id=bracket_blank></td><tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[0], $match_data); ?> </td><td></td><td id=bracket_blank></td></tr>
    <tr> <td></td><?php output_archer_phase_data($quaterfinal_data[1], $match_data); ?> <td id=bracket_blank></td></tr>
    <tr><td></td><td></td><td></td><td></td>
    <td> <?php output_archer_phase_data($semifinal_data[0], $match_data); ?> </td>
    <td id=bracket_blank></td>
    <td> <?php output_archer_phase_data($final_data[0], $match_data); ?> </td>
    <td id=bracket_blank></td>
    </tr>
    <tr><td></td><td></td><td></td><td></td>
    <td> <?php output_archer_phase_data($semifinal_data[1], $match_data); ?> </td>
    <td></td>
    <td> <?php output_archer_phase_data($final_data[1], $match_data); ?> </td>
    </tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[2], $match_data); ?> </td></tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[3], $match_data); ?> </td></tr>
    <tr><td id=bracket_blank></td></tr>
    <tr><td id=bracket_blank></td></tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[4], $match_data); ?> </td></tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[5], $match_data); ?> </td></tr>
    <tr><td></td><td></td><td></td><td></td>
    <td> <?php output_archer_phase_data($semifinal_data[2], $match_data); ?> </td>
    <td></td>
    <td> <?php output_archer_phase_data($final_data[2], $match_data); ?> </td>
    </tr>
    <tr><td></td><td></td><td></td><td></td>
    <td> <?php output_archer_phase_data($semifinal_data[3], $match_data); ?> </td>
    <td></td>
    <td> <?php output_archer_phase_data($final_data[3], $match_data); ?> </td>
    </tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[6], $match_data); ?> </td></tr>
    <tr><td> <?php output_archer_phase_data($quaterfinal_data[7], $match_data); ?> </td></tr>
</table>
<?php
}

function output_presentation_data_reverse($match_data){
    if($match_data['match_type']=="ind"){
    ?>
    <table id=arrowvalues>
        <tr>
            <td><?php output_presentation_name($match_data['athlete_1_data']); ?></td>
            <td><?php output_presentation_name($match_data['athlete_2_data']); ?></td>
        </tr>
        <tr>
            <td><?php output_presentation_club($match_data['athlete_1_data']); ?></td>
            <td><?php output_presentation_club($match_data['athlete_2_data']); ?></td>
        </tr>
    </table>
    <?php
    }
    if($match_data['match_type']=="team"){
        $team_1_data = $match_data['athlete_1_data'];
        $team_2_data = $match_data['athlete_2_data'];
        
        $team_1_component = get_team_component($team_1_data);
        $team_2_component = get_team_component($team_2_data);

        $max_member = max(count($team_1_component), count($team_2_component));

        ?>
        <table id=arrowvalues>
            <tr>
                <td><?php output_presentation_name($match_data['athlete_1_data']); ?></td>
                <td><?php output_presentation_name($match_data['athlete_2_data']); ?></td>
            </tr>
                <?php
                for($i=0;$i<$max_member;$i++){
                    ?>
                    <tr>
                        <td><?php output_presentation_team_component($team_1_component[$i]); ?></td>
                        <td><?php output_presentation_team_component($team_2_component[$i]); ?></td>
                    </tr>
                    <?php
                }
                ?>
        </table>
        <?php
        }

}

function output_presentation_data($match_data){
    if($match_data['match_type']=="ind"){
    ?>
    <table id=arrowvalues>
        <tr>
            <td><?php output_presentation_name($match_data['athlete_2_data']); ?></td>
            <td><?php output_presentation_name($match_data['athlete_1_data']); ?></td>
        </tr>
        <tr>
            <td><?php output_presentation_club($match_data['athlete_2_data']); ?></td>
            <td><?php output_presentation_club($match_data['athlete_1_data']); ?></td>
        </tr>
    </table>
    <?php
    }
    if($match_data['match_type']=="team"){
        $team_1_data = $match_data['athlete_1_data'];
        $team_2_data = $match_data['athlete_2_data'];
        
        $team_1_component = get_team_component($team_1_data);
        $team_2_component = get_team_component($team_2_data);

        $max_member = max(count($team_1_component), count($team_2_component));

        ?>
        <table id=arrowvalues>
            <tr>
                <td><?php output_presentation_name($match_data['athlete_2_data']); ?></td>
                <td><?php output_presentation_name($match_data['athlete_1_data']); ?></td>
            </tr>
                <?php
                for($i=0;$i<$max_member;$i++){
                    ?>
                    <tr>
                        <td><?php output_presentation_team_component($team_2_component[$i]); ?></td>
                        <td><?php output_presentation_team_component($team_1_component[$i]); ?></td>
                    </tr>
                    <?php
                }
                ?>
        </table>
        <?php
        }

}

function get_team_component($team_data){
    $team_component = array();

    $team_id = $team_data['id'];
    $subteam = $team_data['subteam'];
    $event = $team_data['event'];

    $select_team_component = "SELECT En.EnName, En.EnFirstName "
    ."FROM Entries as En "
    ."JOIN TeamFinComponent AS Tfc ON Tfc.TfcId = En.EnId "
    ."WHERE Tfc.TfcCoId = '$team_id' AND Tfc.TfcSubTeam = '$subteam' AND Tfc.TfcEvent = '$event' "
    ."ORDER BY En.EnFirstName ASC, En.EnName ASC";

    $rs = safe_r_sql($select_team_component);
    while($data = $rs->fetch_assoc()){
        array_push($team_component, $data['EnFirstName']." ".$data['EnName']);
    }
    return $team_component;
}

function output_presentation_name($athlete_data){
    ?>
    <th>
        <div id=pan>
            <?php echo $athlete_data['name'].' '.$athlete_data['given_name']; ?>
        </div>
	</th>
    <?php
}

function output_presentation_team_component($athlete_name){
    ?>
    <th>
        <div id=pan_ath>
            <?php echo $athlete_name; ?>
        </div>
	</th>
    <?php
}

function output_presentation_club($athlete_data){
    ?>
    <th>
        <div id=pan>
            <?php echo $athlete_data['country']; ?>
        </div>
	</th>
    <?php
}

function output_winner_data($match_data){
    ?>
    <table id=arrowvalues>
        <tr>
            <?php output_winner_name($match_data['athlete_1_data']); ?>
            <?php output_winner_name($match_data['athlete_2_data']); ?>
        </tr>
        <tr>
            <th>
            <div id=winner>
                <?php echo 'ZWYCIĘZCA'; ?>
            </div>
        </th>
        </tr>
    </table>
    <?php

}

function output_winner_name($athlete_data){
    if($athlete_data['winner']=='1'){
    ?>
    <th>
        <div id=winner>
            <?php echo $athlete_data['name'].' '.$athlete_data['given_name']; ?>
        </div>
	</th>
    <?php
    }
}

function output_arrows_values_tv($athlete_data, $match_data){
    switch($match_data['match_status']){
        case 'score_confirmed':
            return 0;
        case 'error':
            return 0;
        case 'scoring':
            $end_arrows = get_end_arrows($athlete_data['arrow'], $match_data);
            $total = 0;
            ?>
            <table id=table_arrow_values>
                <tr>
                    <?php
                        for($i = 0; $i < $match_data['fin_arrows']; $i++){
                            if($match_data['match_type']=='team' && $i==($match_data['fin_arrows']/2)){
                                ?></tr><tr><?php
                            }
                            ?>

                                <td><?php echo decodearrowtext(substr($end_arrows, $i, 1));?></td>
                            <?php
                            $total=$total+decodearrowvalue(substr($end_arrows, $i, 1));
                        } 
                    ?>
                </tr>
            </table>
            <?php
            return 0;
        case 'tiebreak':
            if($match_data['match_type']=='ind'){
                $ath1_data = $match_data['athlete_1_data'];
                $ath2_data = $match_data['athlete_2_data'];
                $athlete_1_tb_num_of_arrows = count(explode(',', $ath1_data['tiebreak']));
                $athlete_2_tb_num_of_arrows = count(explode(',', $ath2_data['tiebreak']));
                $tiebreak_num_of_arrows = max($athlete_1_tb_num_of_arrows, $athlete_2_tb_num_of_arrows);
                ?>
                        <?php 
                            $tiebreak_arrows = explode(',', $athlete_data['tiebreak']);     
                            echo $tiebreak_arrows[$tiebreak_num_of_arrows-1];
                        ?>
                <?php
            }elseif($match_data['match_type']=='team'){
                $end_arrows = $athlete_data['tiebreak_arrows'];
                $total = 0;
                ?>
                <table id=table_arrow_values>
                    <tr>
                        <?php
                            for($i = 0; $i < $match_data['fin_so_arrows']; $i++){
                                
                                ?>
    
                                    <td><?php echo decodearrowtext(substr($end_arrows, $i, 1));?></td>
                                <?php
                                $total=$total+decodearrowvalue(substr($end_arrows, $i, 1));
                            } 
                        ?>
                    </tr>
                </table>
                <?php
            }
    }
    return 0;
}

function output_presentation_name_tv($athlete_data){
    echo $athlete_data['name'];
}

function output_end_score($athlete_data, $match_data){
    switch($match_data['match_status']){
        case 'score_confirmed':
            return 0;
        case 'error':
            return 0;
        case 'scoring':
            $end_arrows = get_end_arrows($athlete_data['arrow'], $match_data);
            $total = 0;
            for($i = 0; $i < $match_data['fin_arrows']; $i++){
                $total=$total+decodearrowvalue(substr($end_arrows, $i, 1));
                } 
                            echo "Set: ";    
                            echo($total);
            return 0;
        case 'tiebreak':
            if($match_data['match_type']=='ind'){
            }elseif($match_data['match_type']=='team'){
                echo "Set:";
                $ath1_data = $match_data['athlete_1_data'];
                $ath2_data = $match_data['athlete_2_data'];
                $athlete_1_tb_num_of_arrows = count(explode(',', $ath1_data['tiebreak']));
                $athlete_2_tb_num_of_arrows = count(explode(',', $ath2_data['tiebreak']));
                $tiebreak_num_of_arrows = max($athlete_1_tb_num_of_arrows, $athlete_2_tb_num_of_arrows);
                ?>
                        <?php 
                            $tiebreak_arrows = explode(',', $athlete_data['tiebreak']);     
                            echo $tiebreak_arrows[$tiebreak_num_of_arrows-1];
                        ?>
                <?php
            }
            return 0;
    }
    return 0;
}