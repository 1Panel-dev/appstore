<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT . './source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

$setting = C::t('common_setting')->fetch_all(array('mobilewechat'));
$setting = (array)unserialize($setting['mobilewechat']);
if(!$setting['wsq_sitetoken']) {
    cpmsg(lang('plugin/wechat', 'wsq_api_register_close'), '', 'error');
}

showtips(lang('plugin/wechat', 'discuzqr_tips'));

$data = wsq::stat();
$d = date('Ymd', TIMESTAMP);
if(!$data || $data->code != 0) {
    cpmsg_error('wechat:stat_failed');
}

$xa = $uv = $pv = $newthread = $reply = $share = $reflow = array();
foreach($data->res as $d) {
    $xa[$d->ftime] = substr($d->ftime, 4, 2).'<br/>'.substr($d->ftime, 6, 2);
    $uv[$d->ftime] = intval($uv[$d->ftime]) + intval($d->uv);
    $pv[$d->ftime] = intval($pv[$d->ftime]) + intval($d->pv);
    $newthread[$d->ftime] = intval($newthread[$d->ftime]) + intval($d->newthread_num);
    $reply[$d->ftime] = intval($reply[$d->ftime]) + intval($d->reply_num);
    $share[$d->ftime] = intval($share[$d->ftime]) + intval($d->share_num);
    $reflow[$d->ftime] = intval($reflow[$d->ftime]) +  intval($d->reflow_num);
}

$xas = "'".implode('\',\'', $xa)."'";
$uvs = implode(',', $uv);
$pvs = implode(',', $pv);
$newthreads = implode(',', $newthread);
$replys = implode(',', $reply);
$shares = implode(',', $share);
$reflows = implode(',', $reflow);

$langarray = array('stat_newthread', 'stat_reply', 'stat_share', 'stat_reflow');
$lang = array();
foreach($langarray as $l) {
    $lang[$l] = lang('plugin/wechat', $l);
}

echo <<<EOF
<script src="./source/plugin/wechat/js/jquery.min.js"></script>
<script type="text/javascript" src="./source/plugin/wechat/js/highcharts.js"></script>
<script type="text/javascript">
var jq=$.noConflict();
jq(function () {
    jq('#chart-container').highcharts({
        chart: {type: 'line'},
        title: {text: ''},
        xAxis: {categories: [$xas]},
        yAxis: {min:0, title: {text: ''}, plotLines: [{value: 0, width: 1, color: '#808080'}]},
        plotOptions: {line: {dataLabels: { enabled: false},enableMouseTracking: true}},
        series: [{name: 'UV', data: [$uvs]}, {name: 'PV', data: [$pvs]}, {name: '$lang[stat_newthread]',data: [$newthreads]}, {name: '$lang[stat_reply]',data: [$replys]}, {name: '$lang[stat_share]',data: [$shares]}, {name: '$lang[stat_reflow]',data: [$reflows]}]
    });
});
</script>
<div id="chart-container" style="min-width:800px;height:400px"></div>
EOF;
$xa = array_reverse($xa, true);
showtableheader('');
showsubtitle(array('', 'UV', 'PV', $lang['stat_newthread'], $lang['stat_reply'], $lang['stat_share'], $lang['stat_reflow']));
foreach($xa as $key=>$value) {
    showtablerow('', array(), array(
        $key,
        $uv[$key],
        $pv[$key],
        $newthread[$key],
        $reply[$key],
        $share[$key],
        $reflow[$key]
    ));
}
showtablefooter();