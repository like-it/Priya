{$welcome =  hello } {$true = 'true'}
{literal}
{$welcome} should not be hello
{/literal}
/**
 * {$welcome} should be hello but in comment
 *
 *
 */

{if $welcome == 'hello'}
    {$welcome = 'welcome'}
    {$welcome}
{/if}

{if $welcome == 'hello'}
    {$welcome == 'welcome'}
{elseif $welcome == 'welcome'}
    {$welcome = 'finish'}
{else}
    {$welcome = 'else'}
{/if}

{$html = file.read('http://google.com')}



<section name="{$name}">
    no debug
</section>