Welcome to PRIYA

{literal}
{Data(attribute, value)}
{Request(attribute, value)}
{Session(attribute, value)}

{/literal}
_______________________________________________________________________________
-------------------------------------------------------------------------------
{Capture.append('script')}{literal}<script type="text/javascript">
    console.log('test');
    console.log('{/literal}{$priya.version}{literal}');
    if(1 == 1){
        console.log('yeah');
    }
</script>{/literal}{/Capture}
{Capture.append('script')}{literal}<script type="text/javascript">
    console.log('test2');
    console.log('{/literal}{$priya.built}{literal}');
    if(1 == 1){
        console.log('whoohoo');
    }
</script>{/literal}{/Capture}
{$tostring = {
    "what" :
      {
      "is" : { yes:no},
      boolean : "{is.object($tostring.what.is)}",
      "test": "{Is.object($tostring.what.is)}",
      __tostring : yes
      },
      testing : "{
        is.set(
           $tostring.what.is2
        )
      }",
      __tostring : "{$tostring.what.test} nice\r\nwow {$start} {$tostring.what}"
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