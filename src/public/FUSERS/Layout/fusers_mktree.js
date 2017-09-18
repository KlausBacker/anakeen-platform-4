var FUSERS = FUSERS || {};

/**
 * FUSERS.mktree implements mktree.js as an object.
 *
 * It also adds the use of FUSERS.mktreeState for tracking the states of the
 * tree's nodes.
 */

/**
 * Copyright (c)2005-2007 Matt Kruse (javascripttoolbox.com)
 *
 * Dual licensed under the MIT and GPL licenses.
 * This basically means you can use this code however you want for
 * free, but don't claim to have written it yourself!
 * Donations always accepted: http://www.JavascriptToolbox.com/donate/
 *
 * Please do not link to the .js files on javascripttoolbox.com from
 * your site. Copy the files locally to your server instead.
 *
 */
/*
 This code is inspired by and extended from Stuart Langridge's aqlist code:
 http://www.kryogenix.org/code/browser/aqlists/
 Stuart Langridge, November 2002
 sil@kryogenix.org
 Inspired by Aaron's labels.js (http://youngpup.net/demos/labels/)
 and Dave Lindquist's menuDropDown.js (http://www.gazingus.org/dhtml/?id=109)
 */
FUSERS.mktree = (function () {
    var __CLASS__ = function (treeId, stateStr, paramStore) {
        this.debug = false;
        this.states = new FUSERS.mktreeState();
        if (typeof paramStore == 'object') {
            this.states.setParamStore(paramStore);
        }
        this.tree = document.getElementById(treeId);
        this._default = {
            treeClass: null,
            nodeClosedClass: null,
            nodeOpenClass: null,
            nodeBulletClass: null,
            nodeLinkClass: null,
            preProcessTrees: null
        };
        this.processList(this.tree);
        this.resetState(stateStr);
    };

    __CLASS__.prototype = {
        'debug': false,
        'states': null,
        'tree': null,
        '_default': {},

        'log': function (msg) {
            if (this.debug && typeof console == 'object') {
                console.log(msg);
            }
        },

        'resetState': function (stateStr) {
            if (typeof stateStr != 'undefined' && stateStr !== null) {
                this.setState(stateStr);
            } else if (this.tree.hasAttribute('data-init-state')) {
                this.setState(this.tree.getAttribute('data-init-state'))
            }
        },

        // utility function to set a global variable if it is not already set
        'setDefault': function (name, val) {
            if (typeof(this._default[name]) == "undefined" || this._default[name] == null) {
                this._default[name] = val;
            }
        },

        // Full expands a tree with a given ID
        'expandTree': function () {
            this.log("expandTree()");
            var ul = this.tree;
            if (ul == null) {
                return false;
            }
            this.expandCollapseList(ul, this._default.nodeOpenClass);
            this.states.expand('*');
        },

        // Fully collapses a tree with a given ID
        'collapseTree': function () {
            this.log("collapseTree()");
            var ul = this.tree;
            if (ul == null) {
                return false;
            }
            this.expandCollapseList(ul, this._default.nodeClosedClass);
            this.states.collapse('*');
        },

        // Expands enough nodes to expose an LI with a given ID
        'expandToItem': function (itemId) {
            var ul = this.tree;
            if (ul == null) {
                return false;
            }
            var ret = this.expandCollapseList(ul, this._default.nodeOpenClass, itemId);
            if (ret) {
                var o = document.getElementById(itemId);
                if (o.scrollIntoView) {
                    o.scrollIntoView(false);
                }
            }
        },

        'expandItemId': function (itemId) {
            this.expandItem(document.getElementById(itemId));
        },

        'expandItem': function (item) {
            if (item === null) {
                return;
            }
            if (item.className == this._default.nodeClosedClass) {
                this.log("Expand item " + item.id);
                this.states.expand(item.id);
            }
            item.className = (item.className == this._default.nodeOpenClass) ? this._default.nodeClosedClass : this._default.nodeOpenClass;
        },

        'collapseItemId': function (itemId) {
            this.collapseItem(document.getElementById(itemId));
        },

        'collapseItem': function (item) {
            if (item === null) {
                return;
            }
            if (item.className == this._default.nodeOpenClass) {
                this.log("Collapse item " + item.id);
                this.states.collapse(item.id);
            }
            item.className = (item.className == this._default.nodeOpenClass) ? this._default.nodeClosedClass : this._default.nodeOpenClass;
        },

        // Performs 3 functions:
        // a) Expand all nodes
        // b) Collapse all nodes
        // c) Expand all nodes to reach a certain ID
        'expandCollapseList': function (ul, cName, itemId) {
            if (!ul.childNodes || ul.childNodes.length == 0) {
                return false;
            }
            // Iterate LIs
            for (var itemi = 0; itemi < ul.childNodes.length; itemi++) {
                var item = ul.childNodes[itemi];
                if (itemId != null && item.id == itemId) {
                    return true;
                }
                if (item.nodeName == "LI") {
                    // Iterate things in this LI
                    var subLists = false;
                    for (var sitemi = 0; sitemi < item.childNodes.length; sitemi++) {
                        var sitem = item.childNodes[sitemi];
                        if (sitem.nodeName == "UL") {
                            subLists = true;
                            var ret = this.expandCollapseList(sitem, cName, itemId);
                            if (itemId != null && ret) {
                                item.className = cName;
                                return true;
                            }
                        }
                    }
                    if (subLists && itemId == null) {
                        item.className = cName;
                    }
                }
            }
        },

        'initDefaultTree': function () {
            this.setDefault("treeClass", "mktree");
            this.setDefault("nodeClosedClass", "liClosed");
            this.setDefault("nodeOpenClass", "liOpen");
            this.setDefault("nodeBulletClass", "liBullet");
            this.setDefault("nodeLinkClass", "bullet");
            this.setDefault("preProcessTrees", true);
        },

        // Search the document for UL elements with the correct CLASS name, then process them
        'convertTrees': function () {
            this.initDefaultTree();
            if (this._default.preProcessTrees) {
                if (!document.createElement) {
                    return;
                } // Without createElement, we can't do anything
                var uls = document.getElementsByTagName("ul");
                if (uls == null) {
                    return;
                }
                var uls_length = uls.length;
                for (var uli = 0; uli < uls_length; uli++) {
                    var ul = uls[uli];
                    if (ul.nodeName == "UL" && ul.className == this._default.treeClass) {
                        this.processList(ul);
                    }
                }
            }
        },

        'treeNodeOnclick': function (elmt) {
            if (elmt.parentNode != null) {
                if (elmt.parentNode.className == this._default.nodeOpenClass) {
                    this.collapseItem(elmt.parentNode);
                } else {
                    this.expandItem(elmt.parentNode);
                }
            }
            return false;
        },

        'retFalse': function () {
            return false;
        },

        // Process a UL tag and all its children, to convert to a tree
        'processList': function (ul) {
            this.initDefaultTree();
            if (!ul.childNodes || ul.childNodes.length == 0) {
                return;
            }
            // Iterate LIs
            var childNodesLength = ul.childNodes.length;
            for (var itemi = 0; itemi < childNodesLength; itemi++) {
                var item = ul.childNodes[itemi];
                if (item.nodeName == "LI") {
                    // Iterate things in this LI
                    var subLists = false;
                    var itemChildNodesLength = item.childNodes.length;
                    for (var sitemi = 0; sitemi < itemChildNodesLength; sitemi++) {
                        var sitem = item.childNodes[sitemi];
                        if (sitem.nodeName == "UL") {
                            subLists = true;
                            this.processList(sitem);
                        }
                    }
                    var s = document.createElement("SPAN");
                    var t = '\u00A0'; // &nbsp;
                    s.className = this._default.nodeLinkClass;
                    if (subLists) {
                        // This LI has UL's in it, so it's a +/- node
                        if (item.className == null || item.className == "") {
                            item.className = this._default.nodeClosedClass;
                        }
                        // If it's just text, make the text work as the link also
                        if (item.firstChild.nodeName == "#text") {
                            t = t + item.firstChild.nodeValue;
                            item.removeChild(item.firstChild);
                        }
                        s.onclick = (function (mktreeObj, elmt) {
                            return function () {
                                mktreeObj.treeNodeOnclick(elmt);
                            };
                        })(this, s);
                    }
                    else {
                        // No sublists, so it's just a bullet node
                        item.className = this._default.nodeBulletClass;
                        s.onclick = (function (mktreeObj, elmt) {
                            return function () {
                                mktreeObj.retFalse();
                            };
                        })(this, s)
                    }
                    s.appendChild(document.createTextNode(t));
                    item.insertBefore(s, item.firstChild);
                }
            }
        },

        'setState': function (str) {
            this.log("setState('" + str + "')");
            if (typeof str == 'undefined' || str === null) {
                return;
            }
            if (typeof str === 'string' && str == '') {
                str = '-*';
            }
            /* Deserialize states */
            var _states = this.states.unserializeStates(str);
            /* Inhibit autostore during replay */
            var autostore = this.states.setAutoStore(false);
            /* Replay states */
            this.states.reset();
            for (var i = 0; i < _states.length; i++) {
                var state = _states[i];
                if (state.id == '*') {
                    if (state.sign == '-') {
                        this.collapseTree();
                    } else {
                        this.expandTree();
                    }
                } else {
                    var item = document.getElementById(state.id);
                    if (item !== null) {
                        if (state.sign == '-') {
                            this.collapseItemId(state.id);
                        } else {
                            this.expandItemId(state.id);
                        }
                    }
                }
            }
            /* Reset autostore */
            this.states.setAutoStore(autostore);
        }
    };

    return __CLASS__;
})();
