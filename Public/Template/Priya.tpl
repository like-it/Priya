Welcome to PRIYA

{literal}
{Data(attribute, value)}
{Request(attribute, value)}
{Session(attribute, value)}

{/literal}
_______________________________________________________________________________
-------------------------------------------------------------------------------
{$test13 = {
    "test2" : "{(int) !!! Is.empty(0.0)}",
    "test" : "{(int) Is.empty(true)}, {(int) Is.empty(null)}, {(int) Is.empty(false)}, {(int) Is.empty(0.0)}, {(int) Is.empty(0)}, {(int) !Is.empty(0.0)}",
    "if" : "{if Is.object($tosting.what.is) === true}master{else}stupid master{/if}"
}}
godverdomme...