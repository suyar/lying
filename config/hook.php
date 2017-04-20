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
];
