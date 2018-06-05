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


function getTax(simple_html_dom $dom) {

	$vars['band'] = $dom->find('.CTBand span', 0)->innertext;
	$vars['amount'] = $dom->find('.CTAmount', 0)->innertext;
	$vars['year'] = $dom->find('.CTYear span', 0)->innertext;
	

	return $vars;
}

function getCouncil(simple_html_dom $dom) {

	$vars['ward'] = $dom->find('#Ward p', 0)->innertext;
	
	// note - the classes on the webstie are mixed up!  .CouncillorList is actually the list of MPs and visa-versa

	foreach($dom->find('.CouncillorList li a') as $el) {
		$vars['mps'][] = array(
			'name' => $el->find('.MPName',0)->innertext,
			'link' => $el->href,
			'image' => substr($el->find('.MPImage',0)->style,16,-2),
			'party' => $el->find('.MPParty',0)->innertext,
		);
	}

	foreach($dom->find('.MPList li a') as $el) {

		$vars['councillors'][] = array(
				'name' => $el->find('.CllrName',0)->innertext,
				'link' => $el->href,
				'image' => substr($el->find('.CllrImage',0)->style,16,-2),
				'party' => $el->find('.CllrParty',0)->innertext,
		);
	}

	return $vars;
}



// TODO - add postcode validation and error handling.



$output = array();

if(!empty($_GET['postcode']) && preg_match('/^WN[0-9] ?[0-9]{1,2}[A-Z]{1,2}$/i', $_GET['postcode'])) {

	// we have a valid postcode so lets go...
	$url = 'https://apps.wigan.gov.uk/MyNeighbourhood/';
	$browser = new ASPBrowser();
	$browser->doGetRequest($url); 
	$resultPage = $browser->doPostRequest($url, array('ctl00$ContentPlaceHolder1$txtPostcode' => $_GET['postcode'])); 

	if(!empty($_GET['uprn'])) {

		// we also have a UPRN so lets check it...
		$uprn = strtoupper(trim($_GET['uprn']));

		if(preg_match('/^UPRN[0-9]+$/i', $uprn)) {
			
			$resultPage = $browser->doPostBack($url, 'ctl00$ContentPlaceHolder1$lstAddresses',$uprn); 

			if($resultPage->find('#ContentPlaceHolder1_pnlAreaDetails',0)) {
				// found data for this UPRN...
				$output = array(
					'bins' => getBins($resultPage),
					'tax' => getTax($resultPage),
					'contacts' => getCouncil($resultPage)
				);

			} else {
				// the UPRN was not found...
				$output['errMsg'] = 'No data for this UPRN.';
			}

			
		} else {
			// the UPRN is invalid...
			$output['errMsg'] = 'UPRN is invalid.';
		}
		

	} else {

		// we only have a postcode so lets return some addresses...
		$addresses = getAddresses($resultPage);

		if($addresses['No Address records found.']) {
			$output['errMsg'] = 'No addresses found for that postcode.';
		} else {
			$output = $addresses;
		}
		
		

	}
	


	$resultPage->clear();
} else {
	$output['errMsg'] = 'Invalid postcode, must be full postcode beginning WN.';
}


header('Content-Type: application/json');
echo json_encode($output);

.
