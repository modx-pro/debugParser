<?php

class debugParser extends modParser {
	public $tags = array();
	public $from_cache = false;
	/** @var modParser $parser */
	protected $parser = null;


	function __construct(xPDO &$modx) {
		/** @var modX $modx */
		parent::__construct($modx);
		if (property_exists($modx, 'parser')) {
			$this->parser = $modx->parser;
		}
	}


	/** {inheritDoc} */
	public function processTag($tag, $processUncacheable = true) {
		$hash = sha1($tag[0]);

		$parse_time_start = microtime(true);
		$query_time_start = $this->modx->queryTime;
		$queries_start = $this->modx->executedQueries;

		// Call processTag method from real parser
		$result = $this->parser && $this->parser instanceof modParser
			? $this->parser->processTag($tag, $processUncacheable)
			: parent::processTag($tag, $processUncacheable);

		$parse_time = number_format(round(microtime(true) - $parse_time_start, 7), 7);
		$query_time = number_format(round($this->modx->queryTime - $query_time_start, 7), 7);
		$queries = $this->modx->executedQueries - $queries_start;

		if (isset($this->tags[$hash])) {
			$this->tags[$hash]['attempts'] ++;
			$this->tags[$hash]['queries'] += $queries;
			$this->tags[$hash]['queries_time'] += $query_time;
			$this->tags[$hash]['parse_time'] += $parse_time;
		}
		else {
			$this->tags[$hash] = array(
				'tag' => htmlentities(trim($tag[0]), ENT_QUOTES, 'UTF-8'),
				'attempts' => 1,
				'queries' => $queries,
				'queries_time' => $query_time,
				'parse_time' => $parse_time,
			);
		}

		return $result;
	}


	/**
	 * Generates table with report
	 */
	public function generateReport() {
		// Total values
		$data = array(
			'rows' => '',
			'total_queries' => $this->modx->executedQueries,
			'total_queries_time' => number_format(round($this->modx->queryTime, 7), 7),
			'total_parse_time' => number_format(round(microtime(true) - $this->modx->startTime, 7), 7),
		);

		$time = array();
		// Sort tags by time
		foreach ($this->tags as $hash => $tag) {
			$time[$hash] = $tag['parse_time'];
		}
		arsort($time);

		// Get templates
		$tplOuter = file_get_contents(MODX_CORE_PATH . 'components/debugparser/elements/templates/template.report.outer.tpl');
		$tpl = file_get_contents(MODX_CORE_PATH . 'components/debugparser/elements/templates/template.report.row.tpl');


		$idx = 1;
		foreach ($time as $k => $v) {
			$row = $this->tags[$k];
			if ($row['queries'] === 0) {
				$row['queries_time'] = 0;
			}
			$row['idx'] = $idx++;

			$pls = $this->makePlaceholders($row);
			$data['rows'] .= str_replace($pls['pl'], $pls['vl'], $tpl);

			if (!empty($_REQUEST['top']) && $idx > (int) $_REQUEST['top']) {
				break;
			}
		}

		/** @var modProcessorResponse $response */
		$response = $this->modx->runProcessor('system/info');
		if (!$response->isError()) {
			$data = array_merge($data, $response->response['object']);
		}

		$data['memory_peak'] = memory_get_peak_usage(true) / 1048576;
		$data['php_version'] = PHP_VERSION;
		$data['from_cache'] = $this->from_cache ? 'true' : 'false';

		$pls = $this->makePlaceholders($data);
		$output = str_replace($pls['pl'], $pls['vl'], $tplOuter);

		if (!empty($_REQUEST['add']) && strpos($this->modx->resource->_output, '</body>') !== false) {
			$this->modx->resource->_output = str_replace('</body>', $output.'</body>', $this->modx->resource->_output);
		}
		else {
			$tplPage = file_get_contents(MODX_CORE_PATH . 'components/debugparser/elements/templates/template.report.page.tpl');
			$data = array(
				'site_name' => $this->modx->getOption('site_name'),
				'modx_charset' => $this->modx->getOption('modx_charset'),
				'pagetitle' => $this->modx->resource->pagetitle,
				'content' => $output,
			);
			$pls = $this->makePlaceholders($data);
			$this->modx->resource->_output = str_replace($pls['pl'], $pls['vl'], $tplPage);;
		}
	}


	/**
	 * Transform array to placeholders
	 *
	 * @param array $array
	 * @param string $prefix
	 *
	 * @return array
	 */
	public function makePlaceholders(array $array = array(), $prefix = '') {
		$result = array(
			'pl' => array(),
			'vl' => array()
		);
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$result = array_merge_recursive($result, $this->makePlaceholders($v, $k.'.'));
			}
			else {
				$result['pl'][$prefix.$k] = '[[+'.$prefix.$k.']]';
				$result['vl'][$prefix.$k] = $v;
			}
		}

		return $result;
	}


	/**
	 * Clearing cache of the resource
	 * @param string $context Key of context for clearing
	 * @return void
	 */
	public function clearCache($context = null) {
		if (empty($context)) {
			$context = $this->modx->context->key;
		}
		$this->modx->_contextKey = $context;

		/** @var xPDOFileCache $cache */
		$cache = $this->modx->cacheManager->getCacheProvider($this->modx->getOption('cache_resource_key', null, 'resource'));
		/** @var modResource $resource */
		if ($resource = $this->modx->getObject('modResource', $this->modx->resourceIdentifier)) {
			$key = $resource->getCacheKey();
			$cache->delete($key, array('deleteTop' => true));
			$cache->delete($key);
		}
	}

	/**
	 * /manager/controllers/default/system/info.class.php
	 *
	 * @param $type
	 * @unused
	 *
	 * @return array|mixed
	 */
	/*
	public function getPHPInfo($type = -1) {
		ob_start();
		phpinfo($type);

		$pi = preg_replace(
			array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
				'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
				"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
				'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
				.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
				'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
				'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
				"# +#", '#<tr>#', '#</tr>#'),
			array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
				'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
				"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
				'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
				'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
				'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
			ob_get_clean());

		$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
		unset($sections[0]);

		$pi = array();
		foreach($sections as $section){
			$n = substr($section, 0, strpos($section, '</h2>'));
			preg_match_all(
				'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
				$section, $askapache, PREG_SET_ORDER);
			foreach($askapache as $m)
				$pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
		}

		return $pi;
	}
	*/

}