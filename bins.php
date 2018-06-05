<?php
include_once __DIR__.'/simple_html_dom.php';
include_once __DIR__.'/ASPBrowser.php';
                                       

/**Remove spaces from string
 * @param $s
 * @return string
 */
function removeSpaces($s) {
    return trim(preg_replace('!\s+!', ' ', $s));
}



function getAddresses(simple_html_dom $dom) {

	foreach($dom->find('option') as $option) {
        if($option->value) $vars[$option->value] = $option->innertext;
      
    }
	return $vars;
}


function getBins(simple_html_dom $dom) {

	foreach($dom->find('.BinsRecycling') as $bin) {
        
        $dateEl = $bin->find('.dateWrapper-next',0);
		$nextCollection = $dateEl->find('.bin-date-number', 0)->innertext .' '. $dateEl->find('.bin-date-month', 0)->innertext .' '. $dateEl->find('.bin-date-year', 0)->innertext;
		$lastCollection = $bin->find('.bin-lastcollection',0)->innertext;

        $vars['collections'][$bin->find('div',0)->class] = array(
        	'nextCollection' => $nextCollection,
        	'lastCollection' => $lastCollection,
        );
      
    }

    $vars['binCalendar'] = $dom->find('#ContentPlaceHolder1_BinActions a', 1)->href;

	return $vars;
}





// TODO - add postcode validation and error handling.



$output = array();

if(!empty($_GET['postcode']) && preg_match('/^(WN,M,WA)[0-9] ?[0-9]{1,2}[A-Z]{1,2}$/i', $_GET['postcode'])) {

	// we have a valid postcode so lets go...
	$url = 'https://apps.wigan.gov.uk/MyNeighbourhood/';
	$browser = new ASPBrowser();
	$browser->doGetRequest($url); 
	$resultPage = $browser->doPostRequest($url, array('ctl00$ContentPlaceHolder1$txtPostcode' => $_GET['postcode'])); 

	// we only have a postcode so lets return some addresses...
	$addresses = getAddresses($resultPage);

	if($addresses['No Address records found.']) {
		$output['errMsg'] = 'No addresses found for that postcode.';
	} else {
		
		// set poiner to start of array
		reset($addresses);
		// get first UPRN
		$uprn = key($addresses);

		if(preg_match('/^UPRN[0-9]+$/i', $uprn)) {
			
			$resultPage = $browser->doPostBack($url, 'ctl00$ContentPlaceHolder1$lstAddresses',$uprn); 

			if($resultPage->find('#ContentPlaceHolder1_pnlAreaDetails',0)) {
				// found data for this UPRN...
				$output = getBins($resultPage);

			} else {
				// the UPRN was not found...
				$output['errMsg'] = 'No data for this UPRN.';
			}

			
		} else {
			// the UPRN is invalid...
			$output['errMsg'] = 'UPRN is invalid.';
		}

	}

	

	$resultPage->clear();
} else {
	$output['errMsg'] = 'Invalid postcode, must be full postcode covered by Wigan council.';
}


header('Content-Type: application/json');
echo json_encode($output);


