<?php
use Priya\Module\Handler;

/**
 * @author         Remco van der Velde
 * @since         19-01-2016
 * @version        1.0
 * @changeLog
 *  -    all
 */

function smarty_function_var_export($params, $template)
{
    $vars = $template->getTemplateVars();
    echo '<pre data-lang="php">';
    var_export($vars);
    echo '</pre>';
}
