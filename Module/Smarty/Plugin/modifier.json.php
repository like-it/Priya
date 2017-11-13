<?php
function smarty_modifier_json($string='')
{
    return json_encode($string, JSON_PRETTY_PRINT);
}
