<?php

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


function validatePostcode($postcode) {

    return preg_match($GLOBALS['environment']->postcodeMatch, $postcode);

}