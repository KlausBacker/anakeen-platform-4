(function mainBoostraper(root, factory) {
    var customDefine = typeof define == 'function' && define.amd ? define : function customDefine(a1, a2, a3) {(a3 || a2)();};
     factory(customDefine);
}(window, function mainFactory(define) {

"use strict";
