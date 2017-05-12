<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>{$title|default:""}</title>
<meta name="author" content="{$author|default:""}"/>
<meta name="revisit-after" content="{$revisit|default:"1 Week"}" />
<meta name="rating" content="{$rating|default:"General"}" />
<meta name="distribution" content="{$distribution|default:"Global"}" />
{if !empty($description)}
<meta name="description" content="{$description}">
{/if}
{if !empty($keywords)}
<meta name="keywords" content="{$keywords}">
{/if}

<link rel="shortcut icon" href="{$icon|default:"/favicon.ico"}" />
{if !empty($link)}<!-- <priya-link class="{$class}"> //-->
{foreach $link as $link_last}
{$link_last}
{/foreach}<!-- </priya-link> //-->{/if}

{if !empty($script)}<!-- <priya-script class="{$class}"> //-->
{foreach $script as $script_last}
{$script_last}
{/foreach}<!-- </priya-script> //-->{/if}

</head>