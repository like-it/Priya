{terminal.color('white')}Information about: {terminal.color('light-green-blue')}<{$binary}> Apache{terminal.color('reset')}
Powered by {terminal.color('light-green')}\Priya\framework{terminal.color('reset')} Version: {$priya.version} (c) 2012 - {date('Y')} By {terminal.color('white')}Priya.Software{terminal.color('reset')} (PS)

{terminal.color('light-green-blue')}<required>  {terminal.color('reset')} {terminal.color('white')} These atrributes are required. {terminal.color('reset')}
{terminal.color('light-green')}<optional>  {terminal.color('reset')} {terminal.color('white')} These atrributes are optional. {terminal.color('reset')}
{terminal.color('light-red')}<install>   {terminal.color('reset')} {terminal.color('white')} Install Application. {terminal.color('reset')}
{terminal.color('light-yellow')}<module>    {terminal.color('reset')} {terminal.color('white')} Install Module. {terminal.color('reset')}
{$rows = terminal.put('rows')}{$columns = terminal.put('columns')}
{str.repeat('_', $columns)}
Commands:

{terminal.color('white')}{$binary} apache info{terminal.color('reset')}
{terminal.color('white')}{$binary} apache start{terminal.color('reset')}
{terminal.color('white')}{$binary} apache stop{terminal.color('reset')}
{terminal.color('white')}{$binary} apache restart{terminal.color('reset')}
{terminal.color('white')}{$binary} apache create{terminal.color('reset')}
{terminal.color('white')}{$binary} apache delete{terminal.color('reset')}
{terminal.color('white')}{$binary} apache enable {terminal.color('light-green-blue')}<url>{terminal.color('reset')}
{terminal.color('white')}{$binary} apache disable {terminal.color('light-green-blue')}<url>{terminal.color('reset')}
{terminal.color('white')}{$binary} apache backup{terminal.color('reset')}
{terminal.color('white')}{$binary} apache restore {terminal.color('light-green')}list{terminal.color('reset')}
{terminal.color('white')}{$binary} apache redirect{terminal.color('reset')}
