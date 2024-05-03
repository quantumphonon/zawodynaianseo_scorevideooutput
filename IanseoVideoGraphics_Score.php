<?php 

$match_type_data = get_match_type($tour_id);

$match_data = get_match_data($match_type_data, $tour_id);

//print_r($match_data);

output_match_data($match_data);


