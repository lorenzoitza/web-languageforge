<?php
namespace models;

class UnreadMessageModel extends UserUnreadModel
{
    public function __construct($userId, $projectId)
    {
        parent::__construct('broadcast_message', $userId, $projectId);
    }
}
