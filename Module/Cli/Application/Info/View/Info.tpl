{terminal.color('white')}Information about: {terminal.color('light-green-blue')}<{$execute}>{terminal.color('reset')}
Powered by {terminal.color('light-green')}\Priya\framework{terminal.color('reset')} Verion: {$priya.version} (c) 2012 - {date('Y')} By {terminal.color('white')}Priya.Software{terminal.color('reset')} (PS)

{terminal.color('light-green-blue')}<required>  {terminal.color('reset')} {terminal.color('white')} These atrributes are required. {terminal.color('reset')}
{terminal.color('light-green')}<optional>  {terminal.color('reset')} {terminal.color('white')} These atrributes are optional. {terminal.color('reset')}
{terminal.color('light-red')}<install>   {terminal.color('reset')} {terminal.color('white')} Install Application. {terminal.color('reset')}
{terminal.color('light-yellow')}<module>    {terminal.color('reset')} {terminal.color('white')} Install Module. {terminal.color('reset')}
{$rows = terminal.put('rows')}{$columns = terminal.put('columns')}
{str.repeat('_', $columns)}
Commands:

  {terminal.color('white')}{$binary} info{terminal.color('reset')}
  {terminal.color('white')}{$binary} version{terminal.color('reset')}
  {terminal.color('white')}{$binary} clear cache{terminal.color('reset')}
  {terminal.color('white')}{$binary} update{terminal.color('reset')}
  {terminal.color('white')}{$binary} bpmanalyzer{terminal.color('reset')}
  {terminal.color('white')}{$binary} service cron{terminal.color('reset')}

  {terminal.color('white')}{$binary} cap{terminal.color('reset')}
  {terminal.color('white')}{$binary} yob{terminal.color('reset')}

  {terminal.color('white')}{$binary} apache{terminal.color('reset')}
  {terminal.color('white')}{$binary} host{terminal.color('reset')}

  {terminal.color('light-red')}renoise{terminal.color('reset')}
  {terminal.color('light-red')}ffmpeg{terminal.color('reset')}
  {terminal.color('light-red')}id3tag{terminal.color('reset')}
  {terminal.color('light-red')}translate{terminal.color('reset')}
  {terminal.color('light-green')}google-translate {terminal.color('light-green-blue')}<language source> <language destination> <text>{terminal.color('reset')}
  {terminal.color('light-green')}youtube-dl{terminal.color('reset')}
  {terminal.color('light-green')}record youtube.id output|mp3|webmp|mp4 extension{terminal.color('reset')}
  {terminal.color('light-green')}record soundcloud.id output|mp3 extension{terminal.color('reset')}
  {terminal.color('light-yellow')}\Renoise\Module\Beat\Generator{terminal.color('reset')}
  {terminal.color('light-yellow')}\Renoise\Module\Instrument\Tester{terminal.color('reset')}
  {terminal.color('light-yellow')}\Renoise\Module\Output\Wav{terminal.color('reset')}
  {terminal.color('light-yellow')}\Renoise\Module\Convert\Mp3{terminal.color('reset')}
  {terminal.color('light-yellow')}\Renoise\Module\Output\Enhance{terminal.color('reset')}

make {$execute} info default command

add tput up / down with arrow keys for menu's installation menu's options etc...

