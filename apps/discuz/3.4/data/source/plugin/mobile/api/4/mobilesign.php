<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
include_once 'misc.php';

class mobile_api {

    function common() {
        global $_G;
        if(submitcheck('hash', true) && $_G['uid']){
            $r = updatecreditbyaction('mobilesign', $_G['uid']);
            if($r['updatecredit']) {
              $_G['messageparam'][0] = 'mobilesign_success';
            } else {
              $_G['messageparam'][0] = 'mobilesign_failed';
            }
        } else {
            $_G['messageparam'][0] = 'mobilesign_formhash_failed';
        }
        mobile_core::result(mobile_core::variable(array()));
    }

    function output() {
    }

}

?>