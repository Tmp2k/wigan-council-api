<?php

function sendDebug($data, $email = '') {
    if(empty($email)) $email = $GLOBALS['environment']->debugEmail;

    mail($email,'BinDay Debug',print_r($data,true), 'From: BinDay.uk <no-reply@binday.uk>');


}
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
        if($dateEl) {
            if($bin->find('.todayNotification')) {
                //next collection is today
                $nextCollection = date('Y-m-dT00:00:00+00:00');
            } else {
                $nextCollection = $dateEl->find('.bin-date-number', 0)->innertext . ' ' . $dateEl->find('.bin-date-month', 0)->innertext . ' ' . $dateEl->find('.bin-date-year', 0)->innertext;

            }
            $lastCollection = $bin->find('.bin-lastcollection', 0)->innertext;

            $vars['collections'][$bin->find('div', 0)->class] = array(
                'nextCollection' => date('c',strtotime($nextCollection)),
                'lastCollection' => date('c',strtotime($lastCollection)),
            );
        }

    }

    $vars['binCalendar'] = $dom->find('#ContentPlaceHolder1_BinActions a', 1)->href;

    if(!empty($vars['binCalendar'])) {
        $nextDate = time() + (60 * 60 * 24 * 365);  // 1 year from now
        //find next bin to be collected
        foreach ($vars['collections'] as $bin => $collection) {
            $thisDate = strtotime($collection['nextCollection']);
            if ($thisDate < $nextDate) {
                $nextDate = $thisDate;
                $nextBin = $bin;
            }
        }

        //friendly collection day
        $days = (strtotime(date('Y-m-d', $nextDate)) - strtotime(date('Y-m-d'))) / 60 / 60 / 24;
        if ($days == 0) {
            $vars['nextCollection']['day'] = 'Today';
        } else if ($days == 1) {
            $vars['nextCollection']['day'] = 'Tomorrow';
        } else {
            $vars['nextCollection']['day'] = date('l', $nextDate);
        }
        $vars['nextCollection']['date'] = date('c', $nextDate);

        //find all bins on that day
        foreach ($vars['collections'] as $bin => $collection) {
            if (strtotime($collection['nextCollection']) == $nextDate) $vars['nextCollection']['bins'][] = $bin;
        }
    }


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

function validateUprn($uprn) {

    return preg_match($GLOBALS['environment']->uprnMatch, $uprn);

}