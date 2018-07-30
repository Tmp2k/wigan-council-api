<?php

require_once '../loader.php';

$output = array();

if(!empty($_GET['postcode']) && validatePostcode($_GET['postcode'])) {

    $postcode = $_GET['postcode'];

	// we have a valid postcode so lets go...
	$url = $GLOBALS['environment']->wiganUrl;
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
        if(next($addresses) === false) reset($addresses);

        //do {
            // get current UPRN
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
        //} while (empty($output['binCalendar']) && next($addresses) !== false);

	}

	

	$resultPage->clear();
} else {
	$output['errMsg'] = 'Invalid postcode, must be full postcode covered by Wigan council.';
}

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
echo json_encode($output);


