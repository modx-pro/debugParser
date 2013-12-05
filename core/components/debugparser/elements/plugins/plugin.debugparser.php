<?php
if (empty($_REQUEST['debug']) || !$modx->user->hasSessionContext('mgr') || $modx->context->key == 'mgr') {return;}

switch ($modx->event->name) {

	case 'OnHandleRequest':
		if ($modx->loadClass('debugParser', MODX_CORE_PATH.'components/debugparser/model/', false, true)) {
			$modx->parser = new debugParser($modx);
		}
		break;

	case 'OnWebPageInit':
		if ($modx->parser instanceof debugParser && empty($_REQUEST['cache'])) {
			$modx->parser->clearCache();
		}
		break;

	case 'OnLoadWebPageCache':
		if ($modx->parser instanceof debugParser) {
			$modx->parser->from_cache = true;
		}
		break;

	case 'OnWebPagePrerender':
		if ($modx->parser instanceof debugParser) {
			$modx->parser->generateReport();
		}
		break;
}