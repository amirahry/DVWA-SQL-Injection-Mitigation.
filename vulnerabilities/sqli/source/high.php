<?php

// ==========================================
// MITIGASI ALTERNATIF: STRICT INPUT ALLOWLISTING
// LEVEL HIGH
// ==========================================

require_once __DIR__ . '/sqli_integer_guard.php';

if (isset($_SESSION['id'])) {

    // Get input dari session
    $id = $_SESSION['id'];

    // MITIGASI: Validasi User ID sebelum query
    $safeId = validateUserId($id);

    if ($safeId === null) {

        // Tolak input yang tidak sesuai format
        $html .= "<pre>Input ditolak: User ID tidak valid.</pre>";

    } else {

        // Gunakan ID yang telah divalidasi
        $id = $safeId;

        switch ($_DVWA['SQLI_DB']) {

            case MYSQL:

                // Query asli DVWA High tetap dipertahankan
                $query = "SELECT first_name, last_name
                          FROM users
                          WHERE user_id = '$id' LIMIT 1;";

                $result = mysqli_query(
                    $GLOBALS["___mysqli_ston"],
                    $query
                ) or die(
                    '<pre>Something went wrong.</pre>'
                );

                // Get results
                while ($row = mysqli_fetch_assoc($result)) {

                    $first = $row["first_name"];
                    $last  = $row["last_name"];

                    // Feedback for end user
                    $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }

                // Pertahankan mekanisme penutupan koneksi asli
                ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"])))
                    ? false
                    : $___mysqli_res);

                break;

            case SQLITE:

                global $sqlite_db_connection;

                $query = "SELECT first_name, last_name
                          FROM users
                          WHERE user_id = '$id' LIMIT 1;";

                try {
                    $results = $sqlite_db_connection->query($query);
                } catch (Exception $e) {
                    echo 'Caught exception: ' . $e->getMessage();
                    exit();
                }

                if ($results) {

                    while ($row = $results->fetchArray()) {

                        $first = $row["first_name"];
                        $last  = $row["last_name"];

                        // Feedback for end user
                        $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                    }

                } else {
                    echo "Error in fetch " .
                         $sqlite_db_connection->lastErrorMsg();
                }

                break;
        }
    }
}

// Tidak menggunakan tag penutup PHP untuk
// mencegah output whitespace yang tidak sengaja.