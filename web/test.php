<?php

function t()
{
    var_dump(http_get_request_headers());
    return [1,2,3,4,5,6,7,8,9,0];
}

foreach (t() as $t) {
    var_dump($t);
}