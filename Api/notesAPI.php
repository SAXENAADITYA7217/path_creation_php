<?php
header("Content-Type: application/json; charset=UTF-8");
include('../admin/config/dbcon.php');

// Check if 'class' parameter is set
if (isset($_GET['class'])) {
    $class = intval($_GET['class']);
    $response = array();

    // Prepare and execute the SQL query
    $query = "SELECT subject, name, slug FROM writenotes WHERE class = ? AND status = 0 ORDER BY subject";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $class);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned
    if ($result->num_rows > 0) {
        $current_subject = '';
        while ($row = $result->fetch_assoc()) {
            if ($row['subject'] != $current_subject) {
                if ($current_subject != '') {
                    $response['data'][$current_subject] = $current_subject_data;
                }
                $current_subject = $row['subject'];
                $current_subject_data = array();
            }
            $current_subject_data[] = $row;
        }
        // Add the last subject data
        $response['data'][$current_subject] = $current_subject_data;
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No tests found for this class.';
    }

    echo json_encode($response);
} else {
    $response['status'] = 'error';
    $response['message'] = 'Class parameter is missing.';
    echo json_encode($response);
}

$con->close();
?>
