<style>
	table.debug {font: normal 12px Arial;border-spacing: 0;width: 100%;}
	table.info {font: normal 12px Arial;border-spacing: 0;margin-bottom:50px;}

	table.debug thead th {border-bottom: 1px solid #555;}
	table.debug tfoot th {border-top: 1px solid #555;}

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
			<th>Queries time</th>
			<th>Parse Time</th>
		</tr>
	</thead>

	[[+rows]]

	<tfoot>
		<tr>
			<th colspan="2" class="total">Total:</th>
			<th>[[+total_queries]]</th>
			<th>[[+total_queries_time]]</th>
			<th>[[+total_parse_time]]</th>
		</tr>
	</tfoot>
</table>

<table class="info">
	<tr><th>MODX version</th><td>[[+modx_version]]</td></tr>
	<tr><th>PHP version</th><td>[[+php_version]]</td></tr>
	<tr><th>Database version</th><td>[[+database_type]] [[+database_version]]</td></tr>
	<tr><th>Memory peak usage</th><td>[[+memory_peak]]</td></tr>
	<tr><th>From cache</th><td>[[+from_cache]]</td></tr>
</table>