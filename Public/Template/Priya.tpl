Welcome to PRIYA
_______________________________________________________________________________
-------------------------------------------------------------------------------

{$math.int.water = 5.6}

{if $math.int.water == 5.6 &&
    (
        newline == 2ewline ||
        water === water
    )
}
    depth = 1
    {if 4.4 == 4.4} depth = 2
        {if 3.3 == 3.3} depth = 3
            html
        {else}
            master
        {/if}
        depth = 2
    {else}
            this one
    {/if}
    depth = 1
    {$if1 = 3}
{else}
    depth = 1.1;
{/if}


{$boolean_1 = (int) ($math.int.water << 1)       }

{$string_1 =

'
$math
test\'
\''

}

test $math.int.water in equation
parseble tags should be removed from the documents
\'\' => '' \' => ' \\' => \' \\\' => \\' \\\\' => \\\'


{$test5 = [
    "omg",
    "test5"

]}

{$test4 = {}}

{$multiply6 =
   (int) $math.int.water * 3
}

{$multiply = ((float)
    12 * 3
)}

{$multiply2 = (
    $start * ((int)  3 + 2)
)  * 4 + 5}





{$multiply =
    $var * 3
}

{$test6 =  (array) {
    "testw;" : {
        "object" : true
    },
    "previous" : 1.0,
    "omg": "{$allow}{$entity_decode}",
    "task" : "{if $omg == $start}{$testing}{else}todo{/if}"
} }

how to detect if it is  multiply, are we going to use

{function is_equation(multiply)}
    {$this ?}
    /**
     * first replace variables
     * then check for is_number & operator::arithmetic
     */
{/function}

{is_equation(multiply)}

{literal}{test{}{}}{/literal}

{test({literal}{}{/literal})}

{$omg3}
{$testing = (string) "t\"\''"\"est\\""}tes2t
{$quote = 'quo\\\'te'}
{$test6 =  (array) {
    "testw;" : {
        "object" : true
    },
    "previous" : 1.0,
    "omg": "{$allow}{$entity_decode}",
    "task" : "{if $omg == $start}{$testing}{else}todo{/if}"
} }
{$test7 = (object) {
    "test" : {
        "object" : true
    },
    "previous" : 1.0,
    "omg": "{$allow}{$entity_decode}",
    "task" : "{if $omg == $start}{$testing}{else}todo{/if}"
}}
{$test8 = (array) {
    "test" : {
        "object" : true
    },
    "previous" : 1.0,
    "omg": "{$start}{$testing}",
    "omg2": "{$omg3}",
    "task" : "{if $omg == $start}{$testing}{else}todo{/if}"
}}
shit    {$test7 = (array) {}}

{$test9 = [
    "omg"

]}

literal, remove from the data and replace with [literal:1] and replace [literal:1] with the literal data at the end

{$test += 1.1}
{$test2 = 3.1}
{$test2 += 1.1}
{$test3 = $allow}
{$test != 1.1} != boolean value so 0 = false & <> 0 = true "" = false '' = false "test" = true



{$float = (float) $var}
{$welcome5 = (int) ($var - ($float -12) * 3)}
{$welcome6 = ($var - ($float -12) * 3)}

{$welcome3 = (int) $var}
{$welcome4 =  (boolean)      $var}
{$welcome1 =      (bool)    ($var < 4)}
{$welcome =      (int)     hello (int) 45n} pakt de eerste cast
{$welcome2                 =         'hello 'a'} {$true =       '   2   '   }
{$two = 2}

(this bug at line 273... ) move if count right_parse == 2 first right_parse becomes left_parse