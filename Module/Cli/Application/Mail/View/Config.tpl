{terminal.color('white')}Information about: {terminal.color('light-green-blue')}<{$binary}> Mail{terminal.color('reset')}
Powered by {terminal.color('light-green')}\Priya\framework{terminal.color('reset')} Version: {$priya.version} (c) 2012 - {date('Y')} By {terminal.color('white')}Priya.Software{terminal.color('reset')} (PS)

{terminal.color('light-green-blue')}<required>  {terminal.color('reset')} {terminal.color('white')} These atrributes are required. {terminal.color('reset')}
{terminal.color('light-green')}<optional>  {terminal.color('reset')} {terminal.color('white')} These atrributes are optional. {terminal.color('reset')}
{terminal.color('light-red')}<install>   {terminal.color('reset')} {terminal.color('white')} Install Application. {terminal.color('reset')}
{terminal.color('light-yellow')}<module>    {terminal.color('reset')} {terminal.color('white')} Install Module. {terminal.color('reset')}
{$rows = terminal.put('rows')}{$columns = terminal.put('columns')}
{str.repeat('_', $columns)}
Configuration:

{terminal.color('light-green-blue')}Name:{terminal.color('reset')} {terminal.readline('light-green-blue')}


{terminal.color('white')}{$binary} mail info{terminal.color('reset')}
{terminal.color('white')}{$binary} mail config{terminal.color('reset')}
{terminal.color('white')}{$binary} mail to{terminal.color('reset')}