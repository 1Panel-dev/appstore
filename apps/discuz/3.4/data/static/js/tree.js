/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tree.js 23838 2011-08-11 06:51:58Z monkey $
*/

var icon = new Object();
icon.root		= IMGDIR + '/tree_root.gif';
icon.folder		= IMGDIR + '/tree_folder.gif';
icon.folderOpen		= IMGDIR + '/tree_folderopen.gif';
icon.file		= IMGDIR + '/tree_file.gif';
icon.empty		= IMGDIR + '/tree_empty.gif';
icon.line		= IMGDIR + '/tree_line.gif';
icon.lineMiddle		= IMGDIR + '/tree_linemiddle.gif';
icon.lineBottom		= IMGDIR + '/tree_linebottom.gif';
icon.plus		= IMGDIR + '/tree_plus.gif';
icon.plusMiddle		= IMGDIR + '/tree_plusmiddle.gif';
icon.plusBottom		= IMGDIR + '/tree_plusbottom.gif';
icon.minus		= IMGDIR + '/tree_minus.gif';
icon.minusMiddle	= IMGDIR + '/tree_minusmiddle.gif';
icon.minusBottom	= IMGDIR + '/tree_minusbottom.gif';

function treeNode(id, pid, name, url, target, open) {
	var obj = new Object();
	obj.id = id;
	obj.pid = pid;
	obj.name = name;
	obj.url = url;
	obj.target = target;
	obj.open = open;
	obj._isOpen = open;
	obj._lastChildId = 0;
	obj._pid = 0;
	return obj;
}

function dzTree(treeName) {
	this.nodes = new Array();
	this.openIds = getcookie('leftmenu_openids');
	this.pushNodes = new Array();
	this.addNode = function(id, pid, name, url, target, open) {
		var theNode = new treeNode(id, pid, name, url, target, open);
		this.pushNodes.push(id);
		if(!this.nodes[pid]) {
			this.nodes[pid] = new Array();
		}
		this.nodes[pid]._lastChildId = id;

		for(k in this.nodes) {
			if(this.openIds && this.openIds.indexOf('_' + theNode.id) != -1) {
				theNode._isOpen = true;
			}
			if(this.nodes[k].pid == id) {
				theNode._lastChildId = this.nodes[k].id;
			}
		}
		this.nodes[id] = theNode;
	};

	this.show = function() {
		var s = '<div class="tree">';
		s += this.createTree(this.nodes[0]);
		s += '</div>';
		document.write(s);
	};

	this.createTree = function(node, padding) {
		padding = padding ? padding : '';
		if(node.id == 0){
			var icon1 = '';
		} else {
			var icon1 = '<img src="' + this.getIcon1(node) + '" onclick="' + treeName + '.switchDisplay(\'' + node.id + '\')" id="icon1_' + node.id + '" style="cursor: pointer;">';
		}
		var icon2 = '<img src="' + this.getIcon2(node) + '" onclick="' + treeName + '.switchDisplay(\'' + node.id + '\')" id="icon2_' + node.id + '" style="cursor: pointer;">';
		var s = '<div class="node" id="node_' + node.id + '">' + padding + icon1 + icon2 + this.getName(node) + '</div>';
		s += '<div class="nodes" id="nodes_' + node.id + '" style="display:' + (node._isOpen ? '' : 'none') + '">';
		for(k in this.pushNodes) {
			var id = this.pushNodes[k];
			var theNode = this.nodes[id];
			if(theNode.pid == node.id) {
				if(node.id == 0){
					var thePadding = '';
				} else {
					var thePadding = padding + (node.id == this.nodes[node.pid]._lastChildId  ? '<img src="' + icon.empty + '">' : '<img src="' + icon.line + '">');
				}
				if(!theNode._lastChildId) {
					var icon1 = '<img src="' + this.getIcon1(theNode) + '"' + ' id="icon1_' + theNode.id + '">';
					var icon2 = '<img src="' + this.getIcon2(theNode) + '" id="icon2_' + theNode.id + '">';
					s += '<div class="node" id="node_' + theNode.id + '">' + thePadding + icon1 + icon2 + this.getName(theNode) + '</div>';
				} else {
					s += this.createTree(theNode, thePadding);
				}
			}
		}
		s += '</div>';
		return s;
	};

	this.getIcon1 = function(theNode) {
		var parentNode = this.nodes[theNode.pid];
		var src = '';
		if(theNode._lastChildId) {
			if(theNode._isOpen) {
				if(theNode.id == 0) {
					return icon.minus;
				}
				if(theNode.id == parentNode._lastChildId) {
					src = icon.minusBottom;
				} else {
					src = icon.minusMiddle;
				}
			} else {
				if(theNode.id == 0) {
					return icon.plus;
				}
				if(theNode.id == parentNode._lastChildId) {
					src = icon.plusBottom;
				} else {
					src = icon.plusMiddle;
				}
			}
		} else {
			if(theNode.id == parentNode._lastChildId) {
				src = icon.lineBottom;
			} else {
				src = icon.lineMiddle;
			}
		}
		return src;
	};

	this.getIcon2 = function(theNode) {
		var src = '';
		if(theNode.id == 0 ) {
			return icon.root;
		}
		if(theNode._lastChildId) {
			if(theNode._isOpen) {
				src = icon.folderOpen;
			} else {
				src = icon.folder;
			}
		} else {
			src = icon.file;
		}
		return src;
	};

	this.getName = function(theNode) {
		if(theNode.url) {
			return '<a href="'+theNode.url+'" target="' + theNode.target + '"> '+theNode.name+'</a>';
		} else {
			return theNode.name;
		}
	};

	this.switchDisplay = function(nodeId) {
		eval('var theTree = ' + treeName);
		var theNode = theTree.nodes[nodeId];
		if($('nodes_' + nodeId).style.display == 'none') {
			theTree.openIds = updatestring(theTree.openIds, nodeId);
			setcookie('leftmenu_openids', theTree.openIds, 8640000000);
			theNode._isOpen = true;
			$('nodes_' + nodeId).style.display = '';
			$('icon1_' + nodeId).src = theTree.getIcon1(theNode);
			$('icon2_' + nodeId).src = theTree.getIcon2(theNode);
		} else {
			theTree.openIds = updatestring(theTree.openIds, nodeId, true);
			setcookie('leftmenu_openids', theTree.openIds, 8640000000);
			theNode._isOpen = false;
			$('nodes_' + nodeId).style.display = 'none';
			$('icon1_' + nodeId).src =  theTree.getIcon1(theNode);
			$('icon2_' + nodeId).src = theTree.getIcon2(theNode);

		}
	};
}