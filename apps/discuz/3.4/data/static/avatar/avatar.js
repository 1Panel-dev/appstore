(function(){
var jQuery = window.jQuery.noConflict(true);
var dwidth = data[data.indexOf('width')+1];
var dheight = data[data.indexOf('height')+1];
jQuery('#avatardesigner').width(dwidth-20);
jQuery('#avatardesigner').height(dheight-25);
jQuery('#widgetparent').width(dwidth-150);
jQuery('#widgetparent').height(dheight-25);
jQuery('#selector').width(150);
jQuery('#selector').height(150);
jQuery('#avatarfileselector').width(dwidth-20);
jQuery('#avatarfileselector').height(dheight-25);
jQuery('#avatarfile').width(dwidth-20);
jQuery('#avatarfile').height(dheight-25);

jQuery('#avatarcanvas').attr('width', (dwidth-150));
jQuery('#avatarcanvas').attr('height', dheight-25);
jQuery('#avatardisplaycanvas').attr('width', dwidth-20);
jQuery('#avatardisplaycanvas').attr('height', dheight-25);
$('avatarform').target ='uploadframe';
$('avatarfile').onchange = uploadAvatarDone;
$('avatarimage').onload = forceSelectorInsideAvatar;
$('backfileselection').onclick = showAvatarFileSelector;
$('avconfirm').onclick = saveAvatar;

jQuery(document).ready(function () {
    jQuery("#selector")
        .draggable({ containment: "parent", drag: function (event, ui) { refreshAvatarCanvas(ui.position); }, stop: function() { forceSelectorInsideAvatar(); } })
        .resizable({ containment: "parent", resize: function (event, ui) { refreshAvatarCanvas(ui.position); }, stop: function() { forceSelectorInsideAvatar(); }  })
        .hover(
            function () { jQuery(this).css({ "border-color": "red" }); },
            function () { jQuery(this).css({ "border-color": "rgba(255, 0, 0, 0.6)" }); }
        );
    jQuery("#slider").slider({
        min: 0,
        max: 100,
        value: 50,
        slide: function(event, ui) {
            forceSelectorInsideAvatar();
        }
    });
    jQuery("#slider").append("<div style='position: absolute; top: -2px; left: 0px; width: 3px; height: 6px; background-color: black;'>&nbsp;</div>" );
    jQuery("#slider").append("<div style='position: absolute; top: -2px; left: 50px; width: 3px; height: 6px; background-color: black;'>&nbsp;</div>" );
    jQuery("#slider").append("<div style='position: absolute; top: -2px; left: 100px; width: 3px; height: 6px; background-color: black;'>&nbsp;</div>" );
});

window.addEventListener('message', receiveMessage, false);

function receiveMessage(event) {
    var msgdata = event.data;
    if (typeof(msgdata) !== 'string') {
        return;
    }
    rectAvatarDone(msgdata);
}

function uploadAvatarDone() {
    if (this.files && this.files[0]) {
        var fr = new FileReader();
        fr.onload = function(e) {
            jQuery('#avatarfileselector').hide();
            jQuery('#avatardisplayer').hide();
            jQuery('#avataradjuster').show();
            jQuery('#selector').css('left', Math.floor((dwidth-300)/2));
            jQuery('#selector').css('top', Math.floor((dheight-150)/2));
            jQuery('#selector').width(150);
            jQuery('#selector').height(150);
            $('avatarimage').src = e.target.result;
            jQuery("#slider").slider('value', 50);
        };
        fr.readAsDataURL(this.files[0]);
    }
}

function showAvatarFileSelector() {
    $('avatarimage').src = null;
    $('avatarfile').value = null;
    clearAvatar();
    $('avataradjuster').style.display = 'none';
    $('avatarfileselector').style.display = 'block';
}

function getAvatarDimension() {
    var factor = jQuery('#slider').slider('option', 'value');
    var cw = jQuery('#widgetparent').width();
    var ch = jQuery('#widgetparent').height();
    var iw = jQuery('#avatarimage').width();
    var ih = jQuery('#avatarimage').height();
    var minw = 48;
    var minh = 48;
    var midw = Math.min(Math.max(iw, 48), cw);
    var midh = Math.min(Math.max(ih, 48), ch);
    var maxw = Math.max(Math.max(iw, 48), cw);
    var maxh = Math.max(Math.max(ih, 48), ch);
    var minr = Math.max(minw/iw, minh/ih);
    var midr = Math.max(midw/iw, midh/ih);
    var maxr = Math.max(maxw/iw, maxh/ih);
    if (factor<=50) {
        r = (minr * (50-factor) + midr * factor)/50;
    }
    else {
        r = (midr * (100-factor) + maxr * (factor-50))/50;
    }
    var aw = r*iw;
    var ah = r*ih;
    var al = (cw-aw)/2;
    var at = (ch-ah)/2;
    var sd = getSelectorDimention();
    if (aw>cw) al = (cw-aw)/(cw-sd.width)*sd.left;
    if (ah>ch) at = (ch-ah)/(ch-sd.height)*sd.top;
    return { left: Math.floor(al), top: Math.floor(at), width: Math.floor(aw), height: Math.floor(ah) };
}

function clearAvatar() {
    var canvas = $('avatarcanvas');
    var cw = canvas.width;
    var ch = canvas.height;
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, cw, ch);
}

function refreshAvatarCanvas(uiposition) {
    var canvas = $('avatarcanvas');
    var cw = canvas.width;
    var ch = canvas.height;
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, cw, ch);
    var ad = getAvatarDimension();
    var iw = jQuery('#avatarimage').width();
    var ih = jQuery('#avatarimage').height();
    var img = $('avatarimage');
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, 0,0, iw, ih, ad.left, ad.top, ad.width, ad.height);
    var sd = getSelectorDimention();
    if (uiposition) {
        sd.left = uiposition.left;
        sd.top = uiposition.top;
    }
    ctx.fillStyle="rgba(0,0,0,0.6)";
    ctx.fillRect(0, 0, cw, sd.top);
    ctx.fillRect(sd.left+sd.width, sd.top, cw-sd.left-sd.width, ch-sd.top);
    ctx.fillRect(0, sd.top+sd.height, sd.left+sd.width, ch-sd.top-sd.height);
    ctx.fillRect(0, sd.top, sd.left, sd.height);
}

function getSelectorDimention() {
    var sl = Math.ceil(jQuery('#selector').position().left);
    var st = Math.ceil(jQuery('#selector').position().top);
    var sw = jQuery('#selector').width();
    var sh = jQuery('#selector').height();
    return { left: sl, top: st, width: sw, height: sh };
}

function forceSelectorInsideAvatar() {
    var sd = getSelectorDimention();
    var ad = getAvatarDimension();
    if (sd.width>ad.width) jQuery('#selector').width(ad.width);
    if (sd.height>ad.height) jQuery('#selector').height(ad.height);
    sd = getSelectorDimention();
    if (sd.left<ad.left) jQuery('#selector').css('left', ad.left);
    if (sd.top<ad.top) jQuery('#selector').css('top', ad.top);
    if (sd.left+sd.width>ad.left+ad.width) jQuery('#selector').css('left', ad.left+ad.width-sd.width);
    if (sd.top+sd.height>ad.top+ad.height) jQuery('#selector').css('top', ad.top+ad.height-sd.height);
    refreshAvatarCanvas();
}

function saveAvatar() {
    var img = $('avatarimage');
    var sd = getSelectorDimention();
    var ad = getAvatarDimension();
    var rl = (sd.left-ad.left)/ad.width;
    var rt = (sd.top-ad.top)/ad.height;
    var rw = sd.width/ad.width;
    var rh = sd.height/ad.height;
    var iw = jQuery('#avatarimage').width();
    var ih = jQuery('#avatarimage').height();
    var sl = rl*iw;
    var st = rt*ih;
    var sw = rw*iw;
    var sh = rh*ih;
    var tw = sw;
    var th = sh;
    if (sw>200 || sh>250) {
        var r = Math.max(sw/200, sh/250);
        tw = Math.floor(sw/r);
        th = Math.floor(sh/r);
    }
    var canvas = document.createElement('canvas');
    canvas.width = tw;
    canvas.height = th;
    var ctx = canvas.getContext("2d");
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, tw, th);
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sl, st, sw, sh, 0, 0, tw, th);
    var dataURL = canvas.toDataURL("image/jpeg", 1.0);
    jQuery('#avatar1').val(dataURL.substr(dataURL.indexOf(",") + 1));

    var tw = sw;
    var th = sh;
    if (sw>120 || sh>120) {
        var r = Math.max(sw/120, sh/120);
        tw = Math.floor(sw/r);
        th = Math.floor(sh/r);
    }
    var canvas = document.createElement('canvas');
    canvas.width = tw;
    canvas.height = th;
    var ctx = canvas.getContext("2d");
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, tw, th);
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sl, st, sw, sh, 0, 0, tw, th);
    var dataURL = canvas.toDataURL("image/jpeg", 1.0);
    jQuery('#avatar2').val(dataURL.substr(dataURL.indexOf(",") + 1));

    var mwh = Math.min(sw, sh);
    if (sw>mwh) {
        sl += Math.floor((sw-mwh)/2);
        sw = mwh;
    }
    if (sh>mwh) {
        st += Math.floor((sh-mwh)/2);
        sh = mwh;
    }
    var tw = 48;
    var th = 48;
    var canvas = document.createElement('canvas');
    canvas.width = tw;
    canvas.height = th;
    var ctx = canvas.getContext("2d");
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, tw, th);
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sl, st, sw, sh, 0, 0, tw, th);
    var dataURL = canvas.toDataURL("image/jpeg", 1.0);
    jQuery('#avatar3').val(dataURL.substr(dataURL.indexOf(",") + 1));

    var src = $('avatarform').action;
    $('avatarform').action = data[data.indexOf('src')+1].replace('images/camera.swf?inajax=1', 'index.php?m=user&a=rectavatar&base64=yes');
    $('avatarform').target='rectframe';
}

function refreshAvatarCanvasForDisplay() {
    var img = $('avatarimage');
    var canvas = $('avatardisplaycanvas');
    var ctx = canvas.getContext("2d");
    var sd = getSelectorDimention();
    var ad = getAvatarDimension();
    var rl = (sd.left-ad.left)/ad.width;
    var rt = (sd.top-ad.top)/ad.height;
    var rw = sd.width/ad.width;
    var rh = sd.height/ad.height;
    var iw = jQuery('#avatarimage').width();
    var ih = jQuery('#avatarimage').height();
    var sl = rl*iw;
    var st = rt*ih;
    var sw = rw*iw;
    var sh = rh*ih;
    var tw = sw;
    var th = sh;
    if (sw>200 || sh>250) {
        var r = Math.max(sw/200, sh/250);
        tw = Math.floor(sw/r);
        th = Math.floor(sh/r);
    }
    var ctl = 10;
    var ctt = 10;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sl, st, sw, sh, ctl, ctt, tw, th);
    ctl += 20 + tw;

    var tw = sw;
    var th = sh;
    if (sw>120 || sh>120) {
        var r = Math.max(sw/120, sh/120);
        tw = Math.floor(sw/r);
        th = Math.floor(sh/r);
    }
    ctx.drawImage(img, sl, st, sw, sh, ctl, ctt, tw, th);
    ctl += 20 + tw;

    var tw = 48;
    var th = 48;
    var mwh = Math.min(sw, sh);
    if (sw>mwh) {
        sl += Math.floor((sw-mwh)/2);
        sw = mwh;
    }
    if (sh>mwh) {
        st += Math.floor((sh-mwh)/2);
        sh = mwh;
    }
    ctx.drawImage(img, sl, st, sw, sh, ctl, ctt, tw, th);

    ctx.fillStyle = "black";
    ctx.font = "bold 16px Arial";
    ctx.fillText('上传成功!', dwidth - 160,155);
    ctx.fillStyle = "grey";
    ctx.font = "bold 12px Arial";
    ctx.fillText('以上是您头像的三种尺寸', dwidth - 200, 180);
}

function rectAvatarDone(res) {
    if (!res) return;
    if (res == 'success') {
        jQuery('#avatardisplayer').show();
        refreshAvatarCanvasForDisplay();
        jQuery('#avataradjuster').hide();
        jQuery('#avatarfileselector').hide();
    } else if (res == 'failure') {
        alert('上传失败');
    }
}
})();