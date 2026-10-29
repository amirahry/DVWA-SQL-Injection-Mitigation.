<?php

// MITIGASI ALTERNATIF: Strict Input Allowlisting
require_once __DIR__ . '/sqli_integer_guard.php';

if( isset( $_REQUEST[ 'Submit' ] ) ) {
    // Get input
    $id = $_REQUEST[ 'id' ];

    // MITIGASI: Validasi User ID sebelum query SQL
    $safeId = validateUserId($id);

    if ($safeId === null) {
        $html .= "<pre>Input ditolak: User ID tidak valid.</pre>";

        // Hentikan pemrosesan file agar query tidak dijalankan
        return;
    }

    // Gunakan ID yang telah divalidasi
    $id = $safeId;

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            // Check database
            $query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
            $result = mysqli_query($GLOBALS["___mysqli_ston"], $query) or die(
                '<pre>' .
                ((is_object($GLOBALS["___mysqli_ston"]))
                    ? mysqli_error($GLOBALS["___mysqli_ston"])
                    : (($___mysqli_res = mysqli_connect_error())
                        ? $___mysqli_res
                        : false)) .
                '</pre>'
            );

            // Get results
            while ($row = mysqli_fetch_assoc($result)) {
                // Get values
                $first = $row["first_name"];
                $last = $row["last_name"];

                // Feedback for end user
                $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
            }

            mysqli_close($GLOBALS["___mysqli_ston"]);
            break;

        case SQLITE:
            global $sqlite_db_connection;

            #$sqlite_db_connection = new SQLite3($_DVWA['SQLITE_DB']);
            #$sqlite_db_connection->enableExceptions(true);

            $query = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
            #print $query;

            try {
                $results = $sqlite_db_connection->query($query);
            } catch (Exception $e) {
                echo 'Caught exception: ' . $e->getMessage();
                exit();
            }

            if ($results) {
                while ($row = $results->fetchArray()) {
                    // Get values
                    $first = $row["first_name"];
                    $last = $row["last_name"];

                    // Feedback for end user
                    $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }
            } else {
                echo "Error in fetch " . $sqlite_db->lastErrorMsg();
            }
            break;
    }
}

?>
