<?php
return [
    \lying\service\Hook::APP_READY => [
        function () {},
    ],
    \lying\service\Hook::APP_END => [
        function () {},
    ],
    \lying\service\Hook::APP_ERROR => [
        function ($err) {},
    ],
    \lying\base\Controller::EVENT_BEFORE_ACTION => [
        function ($a) {},
    ],
    \lying\base\Controller::EVENT_AFTER_ACTION => [
        function ($res, $response) {},
    ],
    \lying\db\ActiveRecord::EVENT_BEFORE_INSERT => [
        function () {},
    ],
    \lying\db\ActiveRecord::EVENT_AFTER_INSERT => [
        function ($res) {},
    ],
    \lying\db\ActiveRecord::EVENT_BEFORE_UPDATE => [
        function () {},
    ],
    \lying\db\ActiveRecord::EVENT_AFTER_UPDATE => [
        function ($res) {},
    ],
    \lying\db\ActiveRecord::EVENT_BEFORE_DELETE => [
        function () {},
    ],
    \lying\db\ActiveRecord::EVENT_AFTER_DELETE => [
        function ($res) {},
    ],
];
