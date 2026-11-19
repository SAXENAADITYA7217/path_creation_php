<?php
header("Content-Type: application/json; charset=UTF-8");
include('../admin/config/dbcon.php');

$response = array();

if (isset($_GET['title'])) {
    $slug = mysqli_real_escape_string($con, $_GET['title']);

    // Prepare and execute the SQL query
    $query = $con->prepare("SELECT * FROM writenotes WHERE slug = ?");
    $query->bind_param("s", $slug);
    $query->execute();
    $result = $query->get_result();

    // Check if any rows are returned
    if ($result->num_rows > 0) {
        $postItems = $result->fetch_assoc();

        $response['status'] = 'success';
        $response['data'] = array(
            'name' => $postItems['name'],
            'class' => $postItems['class'],
            'subject' => $postItems['subject'],
            'posted_on' => date('d-M-Y', strtotime($postItems['created_at'])),
            'image_url' => $postItems['image'] ? 'uploads/writenotes/' . $postItems['image'] : null,
            'description' => $postItems['description']
        );
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No such post found.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Title parameter is missing.';
}

echo json_encode($response);
$con->close();
?>
