<!DOCTYPE html>
<html>
<!-- <priya-application> //-->{foreach $template_list as $fetch_last}{include file="{$fetch_last}" assign="{$fetch_last}" scope="parent"}{/foreach}
{include file="Head.tpl" assign="head" scope="parent"}
{$head}

<body>
{foreach $template_list as $fetch_last}<!-- <{$class} class="{$class}"> //-->
{${$fetch_last}}{/foreach}
</body>
</html>