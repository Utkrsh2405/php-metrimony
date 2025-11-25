<?php
require_once("includes/dbconn.php");

// Fetch States
$states_query = mysqli_query($conn, "SELECT id, state_name FROM states WHERE status = 1 ORDER BY state_name");
$states = [];
$states_map = [];
while($state = mysqli_fetch_assoc($states_query)) {
    $states[] = $state['state_name'];
    $states_map[$state['state_name']] = $state['id'];
}

// Fetch Cities
$cities_query = mysqli_query($conn, "SELECT city_name, state_id FROM cities WHERE status = 1 ORDER BY city_name");
$cities_list = [];
while($city = mysqli_fetch_assoc($cities_query)) {
    $cities_list[] = $city;
}

// Fetch Religions
$religions_query = mysqli_query($conn, "SELECT DISTINCT religion FROM castes WHERE status = 1 AND religion IS NOT NULL ORDER BY religion");
$religions = [];
while($rel = mysqli_fetch_assoc($religions_query)) {
    if(!in_array($rel['religion'], $religions)) {
        $religions[] = $rel['religion'];
    }
}

// Fetch Castes
$castes_query = mysqli_query($conn, "SELECT DISTINCT caste_name, religion FROM castes WHERE status = 1 ORDER BY caste_name");
$castes_list = [];
while($caste = mysqli_fetch_assoc($castes_query)) {
    $castes_list[] = $caste;
}
?>