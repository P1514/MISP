<?php
$xmlArray = array();
$toEscape = array("&", "<");
$escapeWith = array('&amp;', '&lt;');
foreach ($results as $result) {
	$result['Event']['Attribute'] = $result['Attribute'];
	$result['Event']['ShadowAttribute'] = $result['ShadowAttribute'];
	$result['Event']['RelatedEvent'] = $result['RelatedEvent'];

	//
	// cleanup the array from things we do not want to expose
	//
	unset($result['Event']['user_id']);
	// hide the org field is we are not in showorg mode
	if ('true' != Configure::read('MISP.showorg') && !$isSiteAdmin) {
		unset($result['Event']['org']);
		unset($result['Event']['orgc']);
		unset($result['Event']['from']);
	}
	// remove value1 and value2 from the output and remove invalid utf8 characters for the xml parser
	foreach ($result['Event']['Attribute'] as $key => $value) {
		$result['Event']['Attribute'][$key]['value'] = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $result['Event']['Attribute'][$key]['value']);
		$result['Event']['Attribute'][$key]['value'] = str_replace($toEscape, $escapeWith, $result['Event']['Attribute'][$key]['value']);
		unset($result['Event']['Attribute'][$key]['value1']);
		unset($result['Event']['Attribute'][$key]['value2']);
		unset($result['Event']['Attribute'][$key]['category_order']);
	}
	// remove invalid utf8 characters for the xml parser
	foreach($result['Event']['ShadowAttribute'] as $key => $value) {
		$result['Event']['ShadowAttribute'][$key]['value'] = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $result['Event']['ShadowAttribute'][$key]['value']);
		$result['Event']['ShadowAttribute'][$key]['value'] = str_replace($toEscape, $escapeWith, $result['Event']['ShadowAttribute'][$key]['value']);
	}
	
	if (isset($result['Event']['RelatedEvent'])) {
		foreach ($result['Event']['RelatedEvent'] as $key => $value) {
			$temp = $value['Event'];
			unset($result['Event']['RelatedEvent'][$key]['Event']);
			$result['Event']['RelatedEvent'][$key]['Event'][0] = $temp;
			unset($result['Event']['RelatedEvent'][$key]['Event'][0]['user_id']);
			if ('true' != Configure::read('MISP.showorg') && !$isAdmin) {
				unset($result['Event']['RelatedEvent'][$key]['Event'][0]['org']);
				unset($result['Event']['RelatedEvent'][$key]['Event'][0]['orgc']);
			}
			unset($temp);
		}
	}
	$xmlArray['response']['Event'][] = $result['Event'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<response>';
echo $this->XmlOutput->recursiveEcho($xmlArray['response']);
echo '<xml_version>' . $mispVersion . '</xml_version>';
echo '</response>' . PHP_EOL;
