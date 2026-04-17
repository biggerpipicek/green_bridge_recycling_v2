<?php

    function logActivity($conn, $user_id, $action, $entity_type, $entity_id, $description) {
        $stmt = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issis", $user_id, $action, $entity_type, $entity_id, $description);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }