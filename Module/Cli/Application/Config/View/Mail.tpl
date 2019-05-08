{ literal }

{ /literal }
{ literal }

test 3

{ /literal }	

{ $priya.is.debug = 'huh' + "\n" + 'yes'}
{ $priya.parse.for.count = 20000 }
what is {$time.start} 321
this is {$test.empty = 'is.empty' }original


nice	
{$i = 0}
{for.each($priya.dir as $attribute.test.mooi.man => $value.test)}
{$attribute.test.mooi.man}
{/for.each}

{for($a.b = 1, echo('test'); $a.b < 500; $a.b++)}	
{$s.e = $a.b  + math.random(1000, 9999) }	
{$s.e2 = $a.b  + math.random(1000, 9999) }	
{$s.e3 = $a.b  + math.random(1000, 9999) }	
{$s.e3 }
{/for}

{$i = 0}
/*
{for($a = 1, echo('test'))}
	{$i++}
	{if($i  > 5000  - 2 + 1)}
		{break()}
	{/if}		
{/for}
{for($a = 1, echo('test'))}
	{$i++}
	{if($i  > 5000  - 2 + 1)}
		{break()}
	{/if}		
{/for}
*/
{$current = time(true)}
{$time.start} {$test.empty | string.uppercase.nth : 1 | string.uppercase.nth : 4 }

{$current}

{$duration = math.round($current - $time.start, 2) * 1000 + ' ms' + "\n" + ' nice'}
{$duration}
{$priya.is.debug}