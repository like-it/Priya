{capture append="script"}
<script type="text/javascript">
	ready(function(){
		var form = priya.dom('form[name="route-detail"]');		
		if(priya.is_nodeList(form)){
			var index;
			for(index=0; index < form.length; index++){
				node = form[index];
				node.on('submit', function(event){							
					event.preventDefault();
					var data = this.data('serialize');
					this.request(data);		
				});				
			}		 
		} else {	
			form.on('submit', function(event){							
				event.preventDefault();
				var data = this.data('serialize');
				this.request(data);		
			});
		}						
	});										
</script>
{/capture}

<h1>{$module}.tpl</h1>
{if !empty($error) && !empty($error.route)}
<h3 style="color:red">Error: Route not found.</h3>
{/if}
<h2>Add edit Route</h2>
<div class="route-detail">
	<form name="route-detail" data-request="{$web.root}Route/Process/">
		<label for="data.path">Path:</label><input type="text" name="data.path" value="{$request.path|default:""}" />
		<label for="data.default.controller">Controller:</label><input type="text" name="data.default.controller" value="{$request.default.controller|default:""}" />
		<label for="data.default.format">Format:</label>
		<select name="data.default.format">
			<option>Please select one...</option>
			<option value="html">html</option>
			<option value="json">json</option>
			<option value="html, json">html, json</option>
			<option value="cli">cli</option>		
		</select>			
		<label for="data.default.language">Language:</label><input type="text" name="data.default.language" value="{$request.default.language|default:"en"}" />
		<label for="data.method">Method:</label>
		<select name="data.method">
			<option>Please select one...</option>
			<option value="GET">GET</option>
			<option value="POST">POST</option>
			<option value="GET, POST">GET, POST</option>
			<option value="CLI">CLI</option>		
		</select>		
		<label for="data.translate">Translate:</label><input type="text" name="data.translate" value="{$request.translate|default:"false"}" readonly="readonly" />
		<label for="data.resource">Resource:</label>
		<select name="data.resource">
			<option>Please select one...</option>
		{foreach $resource as $file}
			<option value="{$file.url}">{$file.url}</option>		
		{/foreach}
		</select>		
		<input type="submit" value="Save"> 
	</form>
</div>