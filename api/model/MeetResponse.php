<?php

class MeetResponse extends Model
{
    protected $_tableName = 'tb_meet_response';

    protected function _format_my(&$row)
    {
        $row['meet'] = Meet::m()->getById($row['meet_id']);
    }
}

?>