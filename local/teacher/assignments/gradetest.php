<?php
    // // PHP code to handle the AJAX request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $studentId = $_POST['student_id'];
        $actId = $_POST['activity_id'];
        $grade = $_POST['grade'];
        $currentTime = time();
      $id = "19";
      $grader ="5";
      var_dump($grade);
        $checkQuery = "SELECT * FROM mdl_assign_grades WHERE userid = '$studentId' AND assignment = '$actId'";
    $result = $DB->execute($checkQuery);

    if ($result->num_rows > 0) {
        // If the grade exists, update it
        $updateQuery = "UPDATE mdl_assign_grades SET grade = '$grade' WHERE userid = '$studentId' AND assignment = '$actId'";
        $DB->execute($updateQuery);
    } else {
        // If the grade does not exist, insert a new one
        $insertQuery = "INSERT INTO mdl_assign_grades (userid, assignment, grade,timecreated,timemodified,id,grader) VALUES ('$studentId', '$actId', '$grade','$currentTime','$currentTime','$id','$grader')";
        $DB->execute($insertQuery);
    }
    echo "Grade submitted successfully.";
        exit;
    }
    ?>