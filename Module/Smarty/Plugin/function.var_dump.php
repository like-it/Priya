<?php

/**
 * @author         Remco van der Velde
 * @since         19-01-2016
 * @version        1.0
 * @changeLog
 *  -    all
 */

function smarty_function_var_dump($params, $template)
{
    $vars = $template->getTemplateVars();
    var_dump($vars);
}
