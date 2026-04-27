<?php

    function logActivity($conn, $user_id, $action, $entity_type, $entity_id = null, $description = null, $data = []) {

        // TO NOT BREAK THE SYSTEM
        if (!$conn) return false;

        // IF THERE IS NO DESCRIPTION, AD ONE
        if(!$description) {
            $description = ucfirst($action) . " " . $entity_type;
            if($entity_id !== null) {
                $description .= " #" . $entity_id;
            }
        }

        // REPLACE PLACEHOLDERS { SOMETHING }
        if(!empty($data)) {
            foreach ($data as $key => $value) {
                $description = str_replace('{'. $key . '}', $value, $description);
            }
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description) VALUES (?, ?, ?, ?, ?)");

        if(!$stmt) {
            error_log("LogActivity Prepare Failed: ". mysqli_error($conn));
            return false;
        }

        mysqli_stmt_bind_param($stmt, "issis", $user_id, $action, $entity_type, $entity_id, $description);
        $execute = mysqli_stmt_execute($stmt);

        if(!$execute) {
            error_log("LogActivity Execute Failed: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
        return $execute;
    }

    function generateTrackId($length = 12) {
        return strtoupper(bin2hex(random_bytes($length / 2)));
    }