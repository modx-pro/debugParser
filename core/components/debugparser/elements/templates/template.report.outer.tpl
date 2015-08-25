<style>
	table.debug {font: normal 12px Arial;border-spacing: 0;width: 100%;}
	table.info {font: normal 12px Arial;border-spacing: 0;margin: 50px 0 50px 20px;}

	table.debug thead th,
	table.debug tr:last-child td {
		border-bottom: 1px solid #555 !important;
	}

	table.debug th {
		margin: 0;
		padding: 15px;
		font-size: 14px;
	}
	table.info th {text-align: left;padding-right: 20px;}
	table.debug th.total {text-align: right;}

	table.debug td {
		text-align: center;
		margin: 0;
		padding: 10px;
		border: 0;
		border-bottom: 1px solid #AAA;
	}
	table.debug tr:last-child td {
		border: none;
	}
	table.debug tr:hover td {
		background: #efefef;
	}
	table.debug td.tag {
		width: 50%;
		text-align: left;
	}
</style>

<table class="debug">
	<thead>
		<tr>
			<th>#</th>
			<th>Tag</th>
			<th>Queries</th>
			<th>Queries time, s</th>
			<th>Parse Time, s</th>
		</tr>
	</thead>

	[[+rows]]
</table>

<table class="info">
	<tr><th>Total parse time</th><th>[[+total_parse_time]] s</th></tr>
	<tr><th>Total queries</th><td>[[+total_queries]]</td></tr>
	<tr><th>Total queries time</th><td>[[+total_queries_time]] s</td></tr>
</table>
<table class="info">
	<tr><th>Memory peak usage</th><td>[[+memory_peak]] Mb</td></tr>
	<tr><th>MODX version</th><td>[[+modx_version]]</td></tr>
	<tr><th>PHP version</th><td>[[+php_version]]</td></tr>
	<tr><th>Database version</th><td>[[+database_type]] [[+database_version]]</td></tr>
	<tr><th>From cache</th><td>[[+from_cache]]</td></tr>
</table>