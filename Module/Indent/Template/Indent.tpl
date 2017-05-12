.indent *{
    padding-left: 10%;
}
{for $i=2; $i <= 99; $i++}
.indent-{$i} *{
    padding-left: {$i}0%;
}
{/for}