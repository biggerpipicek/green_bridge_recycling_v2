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

    // Compares an order's stored materials against the submitted form values.
    // $existing: rows from order_materials, each with 'material_id' and 'weight'.
    // $submitted_ids / $submitted_weights: raw $_POST['materials'] / $_POST['weights'] arrays (same key order).
    // Order-independent — only the set of (material_id, weight) pairs is compared.
    function materialsChanged(array $existing, array $submitted_ids, array $submitted_weights) {
        $normalize = fn($id, $weight) => (int)$id . ':' . number_format((float)$weight, 2, '.', '');

        $existing_set = [];
        foreach ($existing as $row) {
            $existing_set[] = $normalize($row['material_id'], $row['weight']);
        }
        sort($existing_set);

        $submitted_set = [];
        foreach ($submitted_ids as $key => $m_id) {
            $submitted_set[] = $normalize($m_id, $submitted_weights[$key] ?? 0);
        }
        sort($submitted_set);

        return $existing_set !== $submitted_set;
    }

    function generateTrackId($length = 12) {
        return strtoupper(bin2hex(random_bytes($length / 2)));
    }

    // --- LOW STOCK CHECK ---
    // Returns 'out' (0 or negative), 'low' (positive but under threshold), or 'ok'.
    // Default threshold is 50kg — adjust per-call if a material needs a different cutoff.
    function stockLevel($weight, $threshold = 50) {
        $weight = (float)$weight;
        if ($weight <= 0) return 'out';
        if ($weight <= $threshold) return 'low';
        return 'ok';
    }

    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    // --- ROLE-BASED ACCESS CONTROL ---

    // Role hierarchy: viewer < staff < admin
    function roleLevel($role) {
        $levels = ['viewer' => 1, 'staff' => 2, 'admin' => 3];
        return $levels[$role] ?? 0;
    }

    // Returns true/false — use for showing/hiding buttons, conditional UI, etc.
    function hasRole($min_role) {
        $current_role = $_SESSION['role'] ?? 'viewer';
        return roleLevel($current_role) >= roleLevel($min_role);
    }

    // Hard stop — kills the page if the user doesn't meet the minimum role.
    // Use at the top of pages/actions that should be restricted (e.g. delete partner).
    // Optional $message lets specific pages show a more tailored explanation.
    function requireRole($min_role, $message = null) {
        if (!hasRole($min_role)) {
            http_response_code(403);
            die($message ?? "Access denied — you don't have permission to perform this action.");
        }
    }