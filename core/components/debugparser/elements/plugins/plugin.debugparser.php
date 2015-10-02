<?php
if (empty($_REQUEST['debug']) || !$modx->user->hasSessionContext('mgr') || $modx->context->key == 'mgr') {
	return;
}

switch ($modx->event->name) {

	case 'OnHandleRequest':
		$corePath = $modx->getOption('debugparser.core_path', null, $modx->getOption('core_path') . 'components/debugparser/');
		if ($modx->parser instanceof pdoParser && $modx->loadClass('debugPdoParser', $corePath . 'model/', false, true)) {
			$modx->parser = new debugPdoParser($modx);
		}
		elseif ($modx->loadClass('debugParser', $corePath . 'model/', false, true)) {
			$modx->parser = new debugParser($modx);
		}
		break;

	case 'OnWebPageInit':
		if (method_exists($modx->parser, 'clearCache') && empty($_REQUEST['cache'])) {
			$modx->parser->clearCache();
		}
		break;

	case 'OnLoadWebPageCache':
		if (property_exists($modx->parser, 'from_cache')) {
			$modx->parser->from_cache = true;
		}
		break;

	case 'OnWebPagePrerender':
		if (method_exists($modx->parser, 'generateReport')) {
			$modx->parser->generateReport();
		}
		break;
}