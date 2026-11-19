
<?php

session_start();

include('admin/config/dbcon.php');

header('Content-Type: application/json');


/*
 * Only accept POST requests
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);

    exit;
}


/*
 * Get coordinates
 */

$latitude  = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';


/*
 * Check that coordinates exist
 */

if ($latitude === '' || $longitude === '') {

    echo json_encode([
        "status" => "error",
        "message" => "Latitude and longitude are required"
    ]);

    exit;
}


/*
 * Validate coordinates
 */

if (!is_numeric($latitude) || !is_numeric($longitude)) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid coordinates"
    ]);

    exit;
}


$latitude  = (float) $latitude;
$longitude = (float) $longitude;


/*
 * Check valid latitude range
 */

if ($latitude < -90 || $latitude > 90) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid latitude"
    ]);

    exit;
}


/*
 * Check valid longitude range
 */

if ($longitude < -180 || $longitude > 180) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid longitude"
    ]);

    exit;
}


/*
 * Google Maps location
 */

$location =
    "https://www.google.com/maps?q="
    . $latitude
    . ","
    . $longitude;


/*
 * Insert into track table
 */

$sql = "
    INSERT INTO track
    (
        latitude,
        longitude,
        location
    )
    VALUES
    (?, ?, ?)
";


$stmt = mysqli_prepare($con, $sql);


if (!$stmt) {

    echo json_encode([
        "status" => "error",
        "message" => "Database prepare failed"
    ]);

    exit;
}


/*
 * Bind parameters
 *
 * d = double
 * d = double
 * s = string
 */

mysqli_stmt_bind_param(
    $stmt,
    "dds",
    $latitude,
    $longitude,
    $location
);


/*
 * Execute
 */

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "status" => "success",
        "message" => "Location saved successfully",
        "id" => mysqli_insert_id($con)
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Database insert failed"
    ]);

}


mysqli_stmt_close($stmt);

?>
