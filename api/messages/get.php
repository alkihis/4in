<?php

if (isAdminLogged()) {
    session_write_close();

    if (isset($_GET['sender'])) {
        $max_id = "";

        if (isset($_GET['max_id']) && is_numeric($_GET['max_id']) && $_GET['max_id'] > 0) {
            $max_id = " AND id_message < {$_GET['max_id']} ";
        }

        global $sql;

        // Get offset of MySQL server
        $offset = mysqli_query($sql, "SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP) o");

        if ($offset) {
            // Construction de l'offset (+0000 / -0100 / etc)
            $offset = mysqli_fetch_assoc($offset)['o']; // Sous la forme 01:00:00

            $o = explode(':', $offset); // Explosion

            $offset = $o[0] . $o[1];
            if ($offset[0] !== '-') { // Si jamais on a pas de signe, on le rajoute
                $offset = "+$offset";
            }
        }
        else {
            http_response_code(400);
            return;
        }

        header('Content-Type: application/json');

        $messages = [];
        $count = (int)$_GET['count'];
        if ($count <= 0) {
            $count = 10;
        }

        $sender = mysqli_real_escape_string($sql, $_GET['sender']);

        $q = mysqli_query($sql, "SELECT id_message, content, seen, send_date
            FROM Messages 
            WHERE sender='$sender' $max_id 
            ORDER BY id_message DESC 
            LIMIT $count;");

        if ($q) {
            foreach ($q as $row) {
                $date = (new DateTime($row['send_date'], new DateTimeZone($offset)))->format(DATE_ATOM);

                $messages[] = [
                    'content' => $row['content'], 
                    'id' => (int)$row['id_message'], 
                    'seen' => (bool)$row['seen'],
                    'date' => $date
                ];
            }

            mysqli_query($sql, "UPDATE Messages SET seen=1 WHERE sender='$sender' ORDER BY id_message DESC LIMIT $count;");
        }

        echo json_encode($messages);
    }
    else {
        http_response_code(400);
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

