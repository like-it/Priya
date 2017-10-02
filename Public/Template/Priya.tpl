Welcome to PRIYA
_______________________________________________________________________________
-------------------------------------------------------------------------------
{$tostring = {
    "what" :
      {
      "is" : {no:yes},
      "test": "{Is.object($tostring.what.is)}"
      },
    __tostring : "{$tostring.what.test} wow {$start}"
}}
{$tostring}
{is.object($entity)}

{$qwer =  $start}
{$qwe =  !! $start}
{$test13 = {
    "test2" : "{(int) !!! Is.empty(0.0)}",
    "test" : "{(int) Is.empty(true)}, {(int) Is.empty(null)}, {(int) Is.empty(false)}, {(int) Is.empty(0.0)}, {(int) Is.empty(0)}, {(int) !Is.empty(0.0)}",
    "if" : "{if Is.object($tosting.what.is) === true}master{else}stupid master{/if}"
}}