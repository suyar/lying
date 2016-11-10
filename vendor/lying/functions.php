<?php
function get($name)
{
    Lying::createObject('lying\core\Request')->get($name);
}

