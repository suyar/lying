<?php
/**
 * 全局钩子
 */
return [
    'APP_END' => [
        function () {},
    ],
    'APP_READY' => [
        function () {},
    ],
    'APP_ERR' => [
        function ($err) {},
    ],
    \lying\base\Ctrl::EVENT_BEFORE_ACTION => [
        function ($a) {},
    ],
    \lying\base\Ctrl::EVENT_AFTER_ACTION => [
        function ($res, $response) {},
    ],
    \lying\db\AR::EVENT_BEFORE_INSERT => [
        function () {},
    ],
    \lying\db\AR::EVENT_AFTER_INSERT => [
        function ($res) {},
    ],
    \lying\db\AR::EVENT_BEFORE_UPDATE => [
        function () {},
    ],
    \lying\db\AR::EVENT_AFTER_UPDATE => [
        function ($res) {},
    ],
    \lying\db\AR::EVENT_BEFORE_DELETE => [
        function () {},
    ],
    \lying\db\AR::EVENT_AFTER_DELETE => [
        function ($res) {},
    ],
];
