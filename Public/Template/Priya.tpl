Welcome to PRIYA

{literal}
{Data(attribute, value)}
{Request(attribute, value)}
{Session(attribute, value)}

{/literal}
_______________________________________________________________________________
-------------------------------------------------------------------------------
{$currenttime = null}
{$currenttime | date.format}
{$method = 'awesome'}
{$target = 'body'}
{$time.test = $currenttime | date.format : "Y-m-d H:i:s" : "+1 week"}
{$dont.forget = 'test'}
{$default = $notfound | default2:["me","arrays", "no ob{}jects", true, 1.0, $count] | json}
{if !is.empty($method) && !is.empty($target)}
has method & target
{$method}
{$target}
{$tostring.what.is = {}}to line 11


{$test13 = {
    "test2" : "({(int) !((!! Is.empty(0.0)))})",
    "test" : "{(int) Is.empty(true)}, {(int) Is.empty(null)}, {(int) Is.empty(false)}, {(int) Is.empty(0.0)}, {(int) Is.empty(0)}, {(int) !Is.empty(0.0)}",
    "if" : "{if Is.object($tostring.what.is)}master{else}stupid master{/if}"
}}
cast is working in $test13.test


{else}
godverdomme...
{/if}
mooi...