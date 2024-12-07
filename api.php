<?php
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "osrs_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Define the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_student_results') {
    // Retrieve student results by student ID
    $student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
    
    if ($student_id > 0) {
        // Fetch results
        $sql = "SELECT r.id, r.marks_percentage, c.level, c.section, r.date_created
                FROM results r
                JOIN classes c ON r.class_id = c.id
                WHERE r.student_id = $student_id";
        $result = $conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $result_id = $row['id'];
                $row['subjects'] = [];

                // Fetch subjects and marks for each result
                $subjects_sql = "SELECT s.subject, ri.mark
                                 FROM result_items ri
                                 JOIN subjects s ON ri.subject_id = s.id
                                 WHERE ri.result_id = $result_id";
                $subjects_result = $conn->query($subjects_sql);

                if ($subjects_result->num_rows > 0) {
                    while ($subject_row = $subjects_result->fetch_assoc()) {
                        $row['subjects'][] = $subject_row;
                    }
                }

                $data[] = $row;
            }
        }

        echo json_encode($data);
    } else {
        echo json_encode(["error" => "Invalid student ID."]);
    }
} else {
    echo json_encode(["error" => "Invalid action."]);
}

$conn->close();
?>
