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

    function logChanges($conn, $user_id, $entity_type, $entity_id, $old_data, $new_data) {
        $changes = [];

        foreach ($new_data as $key => $new_value) {
            // ONLY COMPATE KEY THAT EXIST IN THE OLD DATA
            if (array_key_exists($key, $old_data)) {
                if($old_data[$key] != $new_value) {
                    $changes[] = "$key changed from {$old_data[$key]} to {$new_value}";
                }
            }
        }

        if(!empty($changes)) {
            $description = "Updated $entity_type: " . implode(", ", $changes);    
            return logActivity($conn, $user_id, "update", $entity_type, $entity_id, $description);    
        }

        return false; // IF NOTHING FOUND, NOTHING LOGGED
    }

    function getChangedFields($old_data, $new_data) {
        $changes = [];

        foreach ($new_data as $key => $new_value) {
            if (array_key_exists($key, $old_data)) {
                if ($old_data[$key] != $new_value) {
                    $changes[$key] = [
                        'from' => $old_data[$key],
                        'to' => $new_value
                    ];
                }
            }
        }

        return $changes;
    }

    function generateTrackId($length = 12) {
        return strtoupper(bin2hex(random_bytes($length / 2)));
    }