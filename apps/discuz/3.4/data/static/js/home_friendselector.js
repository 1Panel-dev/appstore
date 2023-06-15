/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home_friendselector.js 26733 2011-12-21 07:18:01Z zhengqingpeng $
*/

(function() {
	friendSelector = function(parameter) {
		this.dataSource = {};
		this.selectUser = {};
		this.prompterUser = [];
		this.showObj = $(isUndefined(parameter['showId']) ? 'selectorBox' : parameter['showId']);
		if(!this.showObj) return;
		this.handleObj = $(isUndefined(parameter['searchId']) ? 'valueId' : parameter['searchId']);
		this.showType = isUndefined(parameter['showType']) ? 0 : parameter['showType'];
		this.searchStr = null;
		this.selectNumber = 0;
		this.maxSelectNumber = isUndefined(parameter['maxSelectNumber']) ? 0 : parseInt(parameter['maxSelectNumber']);
		this.allNumber = 0;
		this.notInDataSourceNumber = 0;
		this.handleKey = isUndefined(parameter['handleKey']) ? 'this' : parameter['handleKey'];
		this.selectTabId = isUndefined(parameter['selectTabId']) ? 'selectTabId' : parameter['selectTabId'];
		this.unSelectTabId = isUndefined(parameter['unSelectTabId']) ? 'unSelectTabId' : parameter['unSelectTabId'];
		this.maxSelectTabId = isUndefined(parameter['maxSelectTabId']) ? 'maxSelectTabId' : parameter['maxSelectTabId'];
		this.formId = isUndefined(parameter['formId']) ? '' : parameter['formId'];
		this.filterUser = isUndefined(parameter['filterUser']) ? {} : parameter['filterUser'];
		this.showAll = true;
		this.newPMUser = {};
		this.interlaced = true;
		this.handover = true;
		this.parentKeyCode = 0;
		this.pmSelBoxState = 0;
		this.selBoxObj = isUndefined(parameter['selBox']) ? null : $(parameter['selBox']);
		this.containerBoxObj = isUndefined(parameter['selBoxMenu']) ? null : $(parameter['selBoxMenu']);
		this.imgBtn = null;
		this.initialize();
		return this;
	};

	friendSelector.prototype = {
		addDataSource : function(data, clear) {
			if(typeof data == 'object') {
				var userData = data['userdata'];
				clear = isUndefined(clear) ? 0: clear;
				if(clear) {
					this.showObj.innerHTML = "";
					if(this.showType == 3) {
						this.selBoxObj.innerHTML = '';
					}
					this.allNumber = 0;
					this.dataSource = {};
				}
				for(var i in userData) {
					if(typeof this.filterUser[i] != 'undefined') {
						continue;
					}
					var append = clear ? true : false;
					if(typeof this.dataSource[i] == 'undefined') {
						this.dataSource[i] = userData[i];
						append = true;
						this.allNumber++;
					}
					if(append) {
						this.interlaced = !this.interlaced;
						if(this.showType == 3) {
							this.append(i, 0, 1);
						} else {
							this.append(i);
						}
					}
				}
				if(this.showType == 1) {
					this.showSelectNumber();
				} else if(this.showType == 2) {
					if(this.newPMUser) {
						window.setInterval(this.handleKey+".handoverCSS()", 400);
					}
				}
			}
		},
		addFilterUser : function(data) {
			var filterData = {};
			if(typeof data != 'object') {
				filterData[data] = data;
			} else if(typeof data == 'object') {
				filterData = data;
			} else {
				return false;
			}
			for(var id in filterData) {
				this.filterUser[filterData[id]] = filterData[id];
			}
			return true;
		},
		handoverCSS : function() {
			for(var uid in this.newPMUser) {
				$('avt_'+uid).className = this.handover ? 'avt newpm' : 'avt';
			}
			this.handover = !this.handover;
		},
		handleEvent : function(key, event) {
			var username = '';
			this.searchStr = '';
			if(key != '') {
				if(event.keyCode == 188 || event.keyCode == 13 || event.keyCode == 59) {
					if(this.showType == 3) {
						if(event.keyCode == 13) {
							var currentnum = this.getCurrentPrompterUser();
							if(currentnum != -1) {
								key = this.dataSource[this.prompterUser[currentnum]]['username'];
							}
						}
						if(this.parentKeyCode != 229) {
							this.selectUserName(this.trim(key));
							this.showObj.style.display = 'none';
							$(this.handleObj.id+'_menu').style.display = 'none';
							this.showObj.innerHTML = "";
						}
					}
				} else if(event.keyCode == 38 || event.keyCode == 40) {
				} else {
					if(this.showType == 3) {
						this.showObj.innerHTML = "";
						var result = false;
						var reg = new RegExp(key, "ig");
						this.searchStr = key;
						this.prompterUser = [];
						for(var uid in this.dataSource) {
							username = this.dataSource[uid]['username'];
							if(username.match(reg)) {
								this.prompterUser.push(uid);
								this.append(uid, 1);
								result = true;
							}
						}
						if(!result) {
							$(this.handleObj.id+'_menu').style.display = 'none';
						} else {
							showMenu({'showid':this.showObj.id, 'duration':3, 'pos':'43'});
							showMenu({'showid':this.handleObj.id, 'duration':3, 'pos':'43'});
						}
					}
				}
			} else if(this.showType != 3) {
				this.showObj.innerHTML = "";
				for(var uid in this.dataSource) {
					this.append(uid);
				}
			} else {
				$(this.handleObj.id+'_menu').style.display = 'none';
				this.showObj.innerHTML = "";
			}
		},
		selectUserName:function(userName) {
			this.handleObj.value = '';
			if(userName != '') {
				var uid = this.isFriend(userName);
				if(uid && typeof this.selectUser[uid] == 'undefined' || uid === 0 && typeof this.selectUser[userName] == 'undefined') {
					var spanObj = document.createElement("span");
					if(uid) {
						this.selectUser[uid] = this.dataSource[uid];
						spanObj.id = 'uid' + uid;
						if($('chk'+uid) != null) {
							$('chk'+uid).checked = true;
						}
					} else {
						var id = 'str' + Math.floor(Math.random() * 10000);
						spanObj.id = id;
						this.selectUser[userName] = userName;
					}
					this.selectNumber++;
					spanObj.innerHTML= '<a href="javascript:;" class="x" onclick="'+this.handleKey+'.delSelUser(\''+(spanObj.id)+'\');">删除</a><em class="z" title="' + userName + '">' + userName + '</em><input type="hidden" name="users[]" value="'+userName+'" uid="uid'+uid+'" />';
					this.handleObj.parentNode.insertBefore(spanObj, this.handleObj);
					this.showObj.style.display = 'none';
				} else {
					alert('已经存在'+userName);
				}
			}
		},

		delSelUser:function(id) {
			id = isUndefined(id) ? 0 : id;
			var uid = id.substring(0, 3) == 'uid' ? parseInt(id.substring(3)) : 0;
			var spanObj;
			if(uid) {
				spanObj = $(id);
				delete this.selectUser[uid];
				if($('chk'+uid) != null) {
					$('chk'+uid).checked = false;
				}
			} else if(id.substring(0, 3) == 'str') {
				spanObj = $(id);
				delete this.selectUser[spanObj.getElementsByTagName('input')[0].value];
			}
			if(spanObj != null) {
				this.selectNumber--;
				spanObj.parentNode.removeChild(spanObj);
			}
		},
		trim:function(str) {
			return str.replace(/\s|,|;/g, '');
		},
		isFriend:function(userName) {
			var id = 0;
			for(var uid in this.dataSource) {
				if(this.dataSource[uid]['username'] === userName) {
					id = uid;
					break;
				}
			}
			return id;
		},
		directionKeyDown : function(event) {},
		clearDataSource : function() {
			this.dataSource = {};
			this.selectUser = {};
		},
		showUser : function(type) {
			this.showObj.innerHTML = '';
			type = isUndefined(type) ? 0 : parseInt(type);
			this.showAll = true;
			if(type == 1) {
				for(var uid in this.selectUser) {
					this.append(uid);
				}
				this.showAll = false;
			} else {
				for(var uid in this.dataSource) {
					if(type == 2) {
						if(typeof this.selectUser[uid] != 'undefined') {
							continue;
						}
						this.showAll = false;
					}
					this.append(uid);
				}
			}
			if(this.showType == 1) {
				for(var i = 0; i < 3; i++) {
					$('showUser_'+i).className = '';
				}
				$('showUser_'+type).className = 'a brs';
			}
		},
		append : function(uid, filtrate, form) {
			filtrate = isUndefined(filtrate) ? 0 : filtrate;
			form = isUndefined(form) ? 0 : form;
			var liObj = document.createElement("li");
			var appendUserData = this.dataSource[uid] || this.selectUser[uid];
			var username = appendUserData['username'];
			liObj.userid = appendUserData['uid'];
			if(typeof this.selectUser[uid] != 'undefined') {
				liObj.className = "a";
			}
			if(filtrate) {
				var reg  = new RegExp("(" + this.searchStr + ")","ig");
				username = username.replace(reg , "<strong>$1</strong>");
			}
			if(this.showType == 1) {
				liObj.innerHTML = '<a href="javascript:;" id="' + liObj.userid + '" onclick="' + this.handleKey + '.select(this.id)" class="cl"><span class="avt brs" style="background-image: url(' + appendUserData['avatar'] + ');"><span></span></span><span class="d">' + username + '</span></a>';
			} else if(this.showType == 2) {
				if(appendUserData['new'] && typeof this.newPMUser[uid] == 'undefined') {
					this.newPMUser[uid] = uid;
				}
				liObj.className = this.interlaced ? 'alt' : '';
				liObj.innerHTML = '<div id="avt_' + liObj.userid + '" class="avt"><a href="home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' + liObj.userid + '&touid=' + liObj.userid + '&pmid='+appendUserData['pmid']+'&daterange='+appendUserData['daterange']+'" title="'+username+'" id="avatarmsg_' + liObj.userid + '" onclick="'+this.handleKey+'.delNewFlag(' + liObj.userid + ');showWindow(\'showMsgBox\', this.href, \'get\', 0);"><img src="' + appendUserData['avatar'] + '" alt="'+username+'" /></a></div><p><a class="xg1" href="home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' + liObj.userid + '&touid=' + liObj.userid + '&pmid='+appendUserData['pmid']+'&daterange='+appendUserData['daterange']+'" title="'+username+'" id="usernamemsg_' + liObj.userid + '" onclick="'+this.handleKey+'.delNewFlag(' + liObj.userid + ');showWindow(\'showMsgBox\', this.href, \'get\', 0);">'+username+'</a></p>';
			} else {
				if(form) {
					var checkstate = typeof this.selectUser[uid] == 'undefined' ? '' : ' checked="checked" ';
					liObj.innerHTML = '<label><input type="checkbox" name="selUsers[]" id="chk'+uid+'" value="'+ appendUserData['username'] +'" onclick="if(this.checked) {' + this.handleKey + '.selectUserName(this.value);} else {' + this.handleKey + '.delSelUser(\'uid'+uid+'\');}" '+checkstate+' class="pc" /> <span class="xi2">' + username + '</span></label>';
					this.selBoxObj.appendChild(liObj);
					return true;
				} else {
					liObj.innerHTML = '<a href="javascript:;" username="' + this.dataSource[uid]['username'] + '" onmouseover="' + this.handleKey + '.mouseOverPrompter(this);" onclick="' + this.handleKey + '.selectUserName(this.getAttribute(\'username\'));$(\'username\').focus();" class="cl" id="prompter_' + uid + '">' + username + '</a>';
				}

			}
			this.showObj.appendChild(liObj);
		},
		select : function(uid) {
			uid = parseInt(uid);
			if(uid){
				var select = false;
				if(typeof this.selectUser[uid] == 'undefined') {
					if(this.maxSelectNumber && this.selectNumber >= this.maxSelectNumber) {
			            alert('最多只允许选择'+this.maxSelectNumber+'个用户');
			            return false;
			        }
					this.selectUser[uid] = this.dataSource[uid];
					this.selectNumber++;
					if(this.showType == '1') {
						$(uid).parentNode.className = 'a';
					}
					select = true;
				} else {
					delete this.selectUser[uid];
					this.selectNumber--;
					$(uid).parentNode.className = '';

				}
				if(this.formId != '') {
					var formObj = $(this.formId);
					var opId = 'selUids_' + uid;
					if(select) {
						var inputObj = document.createElement("input");
						inputObj.type = 'hidden';
						inputObj.id = opId;
						inputObj.name = 'uids[]';
						inputObj.value = uid;
						formObj.appendChild(inputObj);
					} else {
						formObj.removeChild($(opId));
					}
				}
				if(this.showType == 1) {
					this.showSelectNumber();
				}
			}
		},
		delNewFlag : function(uid) {
			delete this.newPMUser[uid];
		},
		showSelectNumber:function() {
			if($(this.selectTabId) != null && typeof $(this.selectTabId) != 'undefined') {
				$(this.selectTabId).innerHTML = this.selectNumber;
			}
			if($(this.unSelectTabId) != null && typeof $(this.unSelectTabId) != 'undefined') {
				this.notInDataSourceNumber = 0;
				for(var i in this.selectUser) {
					if(typeof this.dataSource[i] == 'undefined') {
						this.notInDataSourceNumber++;
					}
				}
				$(this.unSelectTabId).innerHTML = this.allNumber + this.notInDataSourceNumber - this.selectNumber;
			}
			if($(this.maxSelectTabId) != null && this.maxSelectNumber && typeof $(this.maxSelectTabId) != 'undefined') {
				$(this.maxSelectTabId).innerHTML = this.maxSelectNumber - this.selectNumber;
			}

		},
		getCurrentPrompterUser:function() {
			var len = this.prompterUser.length;
			var selectnum = -1;
			if(len) {
				for(var i = 0; i < len; i++) {
					var obj = $('prompter_' + this.prompterUser[i]);
					if(obj != null && obj.className == 'a') {
						selectnum = i;
					}
				}
			}
			return selectnum;
		},
		mouseOverPrompter:function(obj) {
			var len = this.prompterUser.length;
			if(len) {
				for(var i = 0; i < len; i++) {
					$('prompter_' + this.prompterUser[i]).className = 'cl';
				}
				obj.className = 'a';
			}
		},
		initialize:function() {
			var instance = this;
			this.handleObj.onkeyup = function(event) {
				event = event ? event : window.event;
				instance.handleEvent(this.value, event);
			};
			if(this.showType == 3) {
				this.handleObj.onkeydown = function(event) {
					event = event ? event : window.event;
					instance.parentKeyCode = event.keyCode;
					instance.showObj.style.display = '';
					if(event.keyCode == 8 && this.value == '') {
						var preNode = this.previousSibling;
						if(preNode.tagName == 'SPAN') {
							var uid = preNode.getElementsByTagName('input')[0].getAttribute('uid');
							if(parseInt(uid.substring(3))) {
								instance.delSelUser(uid);
							} else {
								delete instance.selectUser[preNode.getElementsByTagName('input')[0].value];
								instance.selectNumber--;
								this.parentNode.removeChild(preNode);
							}
						}
					} else if(event.keyCode == 38) {
						if(!instance.prompterUser.length) {
							doane(event);
						}
						var currentnum = instance.getCurrentPrompterUser();
						if(currentnum != -1) {
							var nextnum = (currentnum == 0) ? (instance.prompterUser.length-1) : currentnum - 1;
							$('prompter_' + instance.prompterUser[currentnum]).className = "cl";
							$('prompter_' + instance.prompterUser[nextnum]).className = "a";
						} else {
							$('prompter_' + instance.prompterUser[0]).className = "a";
						}
					} else if(event.keyCode == 40) {
						if(!instance.prompterUser.length) {
							doane(event);
						}
						var currentnum = instance.getCurrentPrompterUser();
						if(currentnum != -1) {
							var nextnum = (currentnum == (instance.prompterUser.length - 1)) ? 0 : currentnum + 1;
							$('prompter_' + instance.prompterUser[currentnum]).className = "cl";
							$('prompter_' + instance.prompterUser[nextnum]).className = "a";
						} else {
							$('prompter_' + instance.prompterUser[0]).className = "a";
						}
					} else if(event.keyCode == 13) {
						doane(event);
					}
					if(typeof instance != "undefined" && instance.pmSelBoxState) {
						instance.pmSelBoxState = 0;
						instance.changePMBoxImg(instance.imgBtn);
						instance.containerBoxObj.style.display = 'none';
					}
				};
			}
		},
		changePMBoxImg:function(obj) {
			var btnImg = new Image();
			btnImg.src = IMGDIR + '/' + (this.pmSelBoxState ? 'icon_top.gif' : 'icon_down.gif');
			if(obj != null) {
				obj.src = btnImg.src;
			}
		},
		showPMFriend:function(boxId, listId, obj) {
			this.pmSelBoxState = !this.pmSelBoxState;
			this.imgBtn = obj;
			this.changePMBoxImg(obj);

			if(this.pmSelBoxState) {
				this.selBoxObj.innerHTML = '';
				for(var uid in this.dataSource) {
					this.append(uid, 0, 1);
				}
			}
			this.containerBoxObj.style.display = this.pmSelBoxState ? '' : 'none';
			this.showObj.innerHTML = "";
		},
		showPMBoxUser:function() {
			this.selBoxObj.innerHTML = '';
			for(var uid in this.dataSource) {
				this.append(uid, 0, 1);
			}
		},
		extend:function (obj) {
			for (var i in obj) {
				this[i] = obj[i];
			}
		}
	};

})();