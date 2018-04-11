<?php

function luc(string $name, ...$extra) {
    $category = strstr($name, '.', true) ?: $name;
    $instance = app("luclin.$name");
    if ($extra) {
        switch ($category) {
            case 'path':
                $instance .= '/'.implode('/', $extra);
                break;
        }
    }
    return $instance;
}