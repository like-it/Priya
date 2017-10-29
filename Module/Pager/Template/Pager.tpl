{capture append="script"}
    <script type="text/javascript">
        ready(function(){
            var previous = priya.dom('.pager .previous');
            previous.on('previous', function(){
                {if !empty($pager) && !empty($pager.page) && !empty($pager.page.current)}
                    {$pager.page.previous = intval($pager.page.current-1)}
                    {if {$pager.page.previous} < 1}
                        {$pager.page.previous = 1}
                    {/if}
                    this.request("{route name="{$pager.route}" attribute="{$pager.page.previous}"}");
                {/if}

            });
            previous.on('click', function(){
                this.trigger('previous');
            });

            var next = priya.dom('.pager .next');
            next.on('next', function(){
                {if !empty($pager) && !empty($pager.page) && !empty($pager.page.current)}
                    {$pager.page.next = intval($pager.page.current+1)}
                    {if {$pager.page.next} > {$pager.page.amount}}
                        {$pager.page.next = {$pager.page.amount}}
                    {/if}
                    this.request("{route name="{$pager.route}" attribute="{$pager.page.next}"}");
                {/if}
            });
            next.on('click', function(){
                this.trigger('next');
            });

            var current = priya.dom('.pager .page input');
            current.on('change', function(){
                var url = "{route name="{$pager.route}" attribute="__PAGE__"}";
                url = priya.str_replace('__PAGE__', this.value, url);
                this.request(url);
            });
        });
    </script>
{/capture}
{capture append="link"}
    <link rel="stylesheet" href="{$web.root}Priya/Module/Pager/Public/Css/Pager.css?{$priya.revision|default:"{$priya.version}"}">
{/capture}
{content name="html" trim="html-line"}
<ul class="pager">
    <li class="item">
    </li>
    <li class="item previous" data-method="{$pager.method|default:"replace-with"}" data-target="{$pager.target|default:".app-issue-overview"}">
        <span class="option issue-previous">previous page</span>
    </li>

    <li class="item page">
        <span class="option page">
            <input type="text" name="page" value="{$pager.page.current}" data-method="{$pager.method|default:"replace-with"}" data-target="{$pager.target|default:".app-issue-overview"}"><span> / {$pager.page.amount}</span>
        </span>
    </li>

    <li class="item next" data-method="{$pager.method|default:"replace-with"}" data-target="{$pager.target|default:".app-issue-overview"}">
        <span class="option issue-next">next page</span>
    </li>
</ul>
{/content}{$html}