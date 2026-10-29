<?php

// MITIGASI ALTERNATIF: Strict Input Allowlisting
require_once __DIR__ . '/sqli_integer_guard.php';

if (isset($_POST['Submit'])) {

    // Get input
    $id = $_POST['id'] ?? null;

    // MITIGASI: Periksa input sebelum query SQL
    $safeId = validateUserId($id);

    if ($safeId === null) {

        // Tolak input yang tidak valid
        $html .= "<pre>Input ditolak: User ID tidak valid.</pre>";

    } else {

        // Gunakan ID yang sudah divalidasi
        $id = $safeId;

        // Mekanisme asli DVWA Medium
        $id = mysqli_real_escape_string(
            $GLOBALS["___mysqli_ston"],
            (string) $id
        );

        switch ($_DVWA['SQLI_DB']) {

            case MYSQL:

                $query = "SELECT first_name, last_name FROM users WHERE user_id = $id;";

                $result = mysqli_query(
                    $GLOBALS["___mysqli_ston"],
                    $query
                ) or die(
                    '<pre>' .
                    mysqli_error($GLOBALS["___mysqli_ston"]) .
                    '</pre>'
                );

                // Get results
                while ($row = mysqli_fetch_assoc($result)) {

                    // Display values
                    $first = $row["first_name"];
                    $last  = $row["last_name"];

                    // Feedback for end user
                    $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }

                break;

            case SQLITE:

                global $sqlite_db_connection;

                $query = "SELECT first_name, last_name FROM users WHERE user_id = $id;";

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
                        $last  = $row["last_name"];

                        // Feedback for end user
                        $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                    }

                } else {
                    echo "Error in fetch " . $sqlite_db->lastErrorMsg();
                }

                break;
        }
    }
}

// ============================================
// KODE ASLI DVWA UNTUK DROPDOWN USER ID
// ============================================

$query = "SELECT COUNT(*) FROM users;";

$result = mysqli_query(
    $GLOBALS["___mysqli_ston"],
    $query
) or die(
    '<pre>' .
    (
        (is_object($GLOBALS["___mysqli_ston"]))
            ? mysqli_error($GLOBALS["___mysqli_ston"])
            : (
                ($___mysqli_res = mysqli_connect_error())
                    ? $___mysqli_res
                    : false
            )
    ) .
    '</pre>'
);

$number_of_rows = mysqli_fetch_row($result)[0];

mysqli_close($GLOBALS["___mysqli_ston"]);
