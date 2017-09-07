{$welcome =  hello } {$true = '2'}
{$two = '2'}
{$test =  ($true+1) == $two ?  $welcome2  :   'default'}
{$test =  $welcome == 'hello' ?  'nice'  :   $two}
{$test =  $welcome2  ?  $welcome2  :   'default'}
{$begin =   $start > $two ?: 1}
{$begin =   $start2 ?: '$start3 > $two'}
{$end = 2}
{$highest = $start > $end ? $start : $end}
{literal}
{$welcome} should not be hello
{/literal}
/**
 * {$welcome} should be hello but in comment
 *
 *
 */

ternary tests, boolean logical operators

$start > $end ? $start : $end //highest number





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