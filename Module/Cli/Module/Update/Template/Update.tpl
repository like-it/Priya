CLI:
{foreach $request as $name => $value}
{if is_string($value)}
{$name} : {$value}
{/if}
{/foreach}