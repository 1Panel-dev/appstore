/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_slide.js 23838 2011-08-11 06:51:58Z monkey $
*/


if(isUndefined(sliderun)) {
	var sliderun = 1;

	function slide() {

		var s = new Object();
		s.slideId = Math.random();
		s.slideSpeed = slideSpeed;
		s.size = slideImgsize;
		s.imgs = slideImgs;
		s.imgLoad = new Array();
		s.imgnum = slideImgs.length;
		s.imgLinks = slideImgLinks;
		s.imgTexts = slideImgTexts;
		s.slideBorderColor = slideBorderColor;
		s.slideBgColor = slideBgColor;
		s.slideSwitchColor = slideSwitchColor;
		s.slideSwitchbgColor = slideSwitchbgColor;
		s.slideSwitchHiColor = slideSwitchHiColor;
		s.currentImg = 0;
		s.prevImg = 0;
		s.imgLoaded = 0;
		s.st = null;

		s.loadImage = function () {
			if(!s.imgnum) {
				return;
			}
			s.size[0] = parseInt(s.size[0]);
			s.size[1] = parseInt(s.size[1]);
			document.write('<div class="slideouter" id="outer_'+s.slideId+'" style="cursor:pointer;position:absolute;width:'+(s.size[0]-2)+'px;height:'+(s.size[1]-2)+'px;border:1px solid '+s.slideBorderColor+'"></div>');
			document.write('<table cellspacing="0" cellpadding="0" style="cursor:pointer;width:'+s.size[0]+'px;height:'+s.size[1]+'px;table-layout:fixed;overflow:hidden;background:'+s.slideBgColor+';text-align:center"><tr><td valign="middle" style="padding:0" id="slide_'+s.slideId+'">');
			document.write((typeof IMGDIR == 'undefined' ? '' : '<img src="'+IMGDIR+'/loading.gif" />') + '<br /><span id="percent_'+s.slideId+'">0%</span></td></tr></table>');
			document.write('<div id="switch_'+s.slideId+'" style="position:absolute;margin-left:1px;margin-top:-18px"></div>');
			$('outer_' + s.slideId).onclick = s.imageLink;
			for(i = 1;i < s.imgnum;i++) {
				switchdiv = document.createElement('div');
				switchdiv.id = 'switch_' + i + '_' + s.slideId;
				switchdiv.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=70)';
				switchdiv.style.opacity = 0.7;
				switchdiv.style.styleFloat = 'left';
				switchdiv.style.cssFloat = 'left';
				switchdiv.style.cursor = 'pointer';
				switchdiv.style.width = '17px';
				switchdiv.style.height = '17px';
				switchdiv.style.overflow = 'hidden';
				switchdiv.style.fontWeight = 'bold';
				switchdiv.style.textAlign = 'center';
				switchdiv.style.fontSize = '9px';
				switchdiv.style.color = s.slideSwitchColor;
				switchdiv.style.borderRight = '1px solid ' + s.slideBorderColor;
				switchdiv.style.borderTop = '1px solid ' + s.slideBorderColor;
				switchdiv.style.backgroundColor = s.slideSwitchbgColor;
				switchdiv.className = 'slideswitch';
				switchdiv.i = i;
				switchdiv.onclick = function () {
					s.switchImage(this);
				};
				switchdiv.innerHTML = i;
				$('switch_'+s.slideId).appendChild(switchdiv);
				s.imgLoad[i] = new Image();
				s.imgLoad[i].src = s.imgs[i];
				s.imgLoad[i].onerror = function () { s.imgLoaded++; };
			}
			s.loadCheck();
		};

		s.imageLink = function () {
			window.open(s.imgLinks[s.currentImg]);
		};

		s.switchImage = function (obj) {
			s.showImage(obj.i);
			s.interval();
		};

		s.loadCheck = function () {
			for(i = 1;i < s.imgnum;i++) {
				if(s.imgLoad[i].complete && !s.imgLoad[i].status) {
					s.imgLoaded++;
					s.imgLoad[i].status = 1;
					if(s.imgLoad[i].width > s.size[0] || s.imgLoad[i].height > s.size[1]) {
						zr = s.imgLoad[i].width / s.imgLoad[i].height;
						if(zr > 1) {
							s.imgLoad[i].height = s.size[1];
							s.imgLoad[i].width = s.size[1] * zr;
						} else {
							s.imgLoad[i].width = s.size[0];
							s.imgLoad[i].height = s.size[0] / zr;
							if(s.imgLoad[i].height > s.size[1]) {
								s.imgLoad[i].height = s.size[1];
								s.imgLoad[i].width = s.size[1] * zr;
							}
						}
					}
				}
			}
			if(s.imgLoaded < s.imgnum - 1) {
				$('percent_' + s.slideId).innerHTML = (parseInt(s.imgLoad.length / s.imgnum * 100)) + '%';
				setTimeout(function () { s.loadCheck(); }, 100);
			} else {
				for(i = 1;i < s.imgnum;i++) {
					s.imgLoad[i].onclick = s.imageLink;
					s.imgLoad[i].title = s.imgTexts[i];
				}
				s.showImage();
				s.interval();
			}
		};

		s.interval = function () {
			clearInterval(s.st);
			s.st = setInterval(function () { s.showImage(); }, s.slideSpeed);
		};

		s.showImage = function (i) {
			if(!i) {
				s.currentImg++;
				s.currentImg = s.currentImg < s.imgnum ? s.currentImg : 1;
			} else {
				s.currentImg = i;
			}
			if(s.prevImg) {
				$('switch_' + s.prevImg + '_' + s.slideId).style.backgroundColor = s.slideSwitchbgColor;
			}
			$('switch_' + s.currentImg + '_' + s.slideId).style.backgroundColor = s.slideSwitchHiColor;
			$('slide_' + s.slideId).innerHTML = '';
			$('slide_' + s.slideId).appendChild(s.imgLoad[s.currentImg]);
			s.prevImg = s.currentImg;
		};

		s.loadImage();

	}
}

slide();