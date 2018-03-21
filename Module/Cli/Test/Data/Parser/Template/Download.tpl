{$module.dir.root = '{$module.dir.root}Data/Parser/'}

{capture.append('loader')}
    {require("{$module.dir.root}Template/Loader.tpl")}
{/capture}

{capture.append('footer')}
    {require("{$module.dir.root}Template/Footer.tpl")}
{/capture}
{capture('missing')}
    {require("{$module.dir.root}Template/Footer.tpl")}
{/capture}

{capture.prepend('loader')}
    {require("{$module.dir.root}Template/Footer.tpl")}
{/capture}

{$head = require("{$module.dir.root}Template/Head.tpl")}

{$head} <a href="{route('priya-software-main')}">Home</a>