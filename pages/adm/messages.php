<?php

function messagesController() : array {
    $data = ['active_page' => 'messages', 'container' => false, 'gradient' => false];

    global $sql;

    $data['conversations'] = [];

    $q = mysqli_query($sql, "SELECT DISTINCT sender FROM Messages");

    while ($row = mysqli_fetch_assoc($q)) {
        $data['conversations'][] = $row['sender'];
    }

    return $data;
}

function messagesView(array $data) : void { ?>
    <style>
        main {
            position: relative;
        }
    </style>
    <div class="row" style="width: 100%; position: absolute; height: 100%;">
        <div class="col s3 side-messages">
            <?php foreach ($data['conversations'] as $sender) { ?>
                <div class="conversation">
                    <span class="sender"><?= htmlspecialchars($sender) ?></span>
                </div>
            <?php } ?>
        </div>
        <div class="col s9 messages-placeholder">this will host messages</div>
    </div>
    <?php
}
