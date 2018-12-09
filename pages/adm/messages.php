<?php

function messagesController() : array {
    $data = ['active_page' => 'messages', 'container' => false, 'gradient' => false];

    global $sql;

    $data['conversations'] = [];

    $q = mysqli_query($sql, "SELECT DISTINCT sender, 
        (SELECT COUNT(*) FROM Messages WHERE sender=m.sender AND seen=0) as new 
        FROM Messages m");

    while ($row = mysqli_fetch_assoc($q)) {
        $data['conversations'][] = ['sender' => $row['sender'], 'new' => $row['new']];
    }

    return $data;
}

function messagesView(array $data) : void { ?>
    <style>
        main {
            position: relative;
            min-height: 500px;
        }
    </style>
    <div class="row messages-container">
        <div class="col s3 side-messages">
            <?php foreach ($data['conversations'] as $sender) { ?>
                <div class="conversation" data-sender="<?= htmlspecialchars($sender['sender']) ?>" 
                    onclick="loadConversation(this, '<?= htmlspecialchars($sender['sender']) ?>')">

                    <span class="remove-conversation"><i class="material-icons left red-text">delete_sweep</i></span>
                    <span class="sender"><?= htmlspecialchars($sender['sender']) ?></span>
                    <?php if ($sender['new'] > 0) { ?>
                        <span class="new badge yellow darken-4"><?= $sender['new'] ?></span>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="center show-if-only-child message-none" style="margin-top: 215px;">
                No conversation
            </div>
        </div>
        <div class="col s9 messages-placeholder"><div class="center show-if-only-child message-none">
            It's empty here.
        </div></div>
    </div>

    <script>
        $(function() {
            $('.remove-conversation').on('click', function(evt) {
                removeConversation(this.parentElement);
            })
        });
    </script>

    <script src="/js/jquery.scrollfire.min.js"></script>
    <?php
}
