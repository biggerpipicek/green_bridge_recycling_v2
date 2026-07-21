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


    /**
 * Compresses an uploaded image (JPEG/PNG/WEBP) in place before saving.
 * Resizes to a max dimension and re-encodes at lower quality.
 * Returns true on success, false if the type isn't a compressible image
 * (caller should then just move the file normally, e.g. PDFs).
 */
function compressUploadedImage(string $tmp_path, string $target_path, string $ext, int $max_dimension = 1600, int $jpeg_quality = 70): bool {
    $ext = strtolower($ext);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        return false; // not an image GD can handle here — e.g. pdf
    }

    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $src = @imagecreatefromjpeg($tmp_path);
            break;
        case 'png':
            $src = @imagecreatefrompng($tmp_path);
            break;
        case 'webp':
            $src = @imagecreatefromwebp($tmp_path);
            break;
        default:
            return false;
    }

    if (!$src) {
        return false; // corrupt/unreadable image — let caller fall back to plain move
    }

    $orig_w = imagesx($src);
    $orig_h = imagesy($src);

    // Only shrink if it's actually larger than max_dimension
    if ($orig_w > $max_dimension || $orig_h > $max_dimension) {
        $ratio = min($max_dimension / $orig_w, $max_dimension / $orig_h);
        $new_w = (int)round($orig_w * $ratio);
        $new_h = (int)round($orig_h * $ratio);

        $resized = imagecreatetruecolor($new_w, $new_h);

        // preserve transparency for png
        if ($ext === 'png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        imagedestroy($src);
        $src = $resized;
    }

    // Always save as JPEG regardless of input type, since it compresses far better
    // than PNG for photos. Skip this if you need to preserve PNG transparency.
    $saved = imagejpeg($src, $target_path, $jpeg_quality);
    imagedestroy($src);

    return $saved;
}