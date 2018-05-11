<?php

$options = 'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;';

echo transliterator_transliterate($options, "你好啊");