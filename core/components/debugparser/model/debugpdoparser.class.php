<?php

class debugPdoParser extends pdoParser {
	public $tags = array();
	public $from_cache = false;
	/** @var debugParser $parser */
	protected $parser = null;


	function __construct(xPDO &$modx) {
		parent::__construct($modx);
		if (!class_exists('debugParser')) {
			require dirname(__FILE__) . '/debugparser.class.php';
		}
		$this->parser = new debugParser($modx);
	}


	/** {inheritDoc} */
	public function processElementTags($parentTag, & $content, $processUncacheable = false, $removeUnprocessed = false, $prefix = "[[", $suffix = "]]", $tokens = array(), $depth = 0) {
		$work = is_string($content) && empty($parentTag) && $processUncacheable && preg_match('#\{(\$|\/|\w+\s)#', $content)
			&& !empty($this->pdoTools->config['useFenomParser']) && !empty($this->pdoTools->config['useFenom']);
		$tag = htmlentities(trim($content), ENT_QUOTES, 'UTF-8');
		$hash = sha1($tag);

		$parse_time_start = microtime(true);
		$query_time_start = $this->modx->queryTime;
		$queries_start = $this->modx->executedQueries;

		$result = parent::processElementTags($parentTag, $content, $processUncacheable, $removeUnprocessed, $prefix, $suffix, $tokens, $depth);
		if ($work) {
			$parse_time = number_format(round(microtime(true) - $parse_time_start, 7), 7);
			$query_time = number_format(round($this->modx->queryTime - $query_time_start, 7), 7);
			$queries = $this->modx->executedQueries - $queries_start;

			if (isset($this->tags[$hash])) {
				$this->tags[$hash]['attempts']++;
				$this->tags[$hash]['queries'] += $queries;
				$this->tags[$hash]['queries_time'] += $query_time;
				$this->tags[$hash]['parse_time'] += $parse_time;
			}
			else {
				$this->tags[$hash] = array(
					'tag' => $tag,
					'attempts' => 1,
					'queries' => $queries,
					'queries_time' => $query_time,
					'parse_time' => $parse_time,
				);
			}
		}

		return $result;
	}


	/** {inheritDoc} */
	public function processTag($tag, $processUncacheable = true) {
		return $this->parser->processTag($tag, $processUncacheable);
	}


	/**
	 * Generates table with report
	 */
	public function generateReport() {
		$this->parser->tags = array_merge($this->parser->tags, $this->tags);
		$this->parser->from_cache = $this->from_cache;

		return $this->parser->generateReport();
	}


	/**
	 * Clearing cache of the resource
	 *
	 * @param string $context Key of context for clearing
	 *
	 * @return void
	 */
	public function clearCache($context = null) {
		$this->parser->clearCache($context);
	}

}