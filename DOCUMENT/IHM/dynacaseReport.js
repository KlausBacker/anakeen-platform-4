!(function (TraceKit) {

    "use strict";

    var request = function requestFunction(url, content, success, fail) {
        var request, getRequest, stateChange, postBody;

        stateChange = function () {
            if (success && request.readyState == 4 && request.status == 200) {
                success(request.responseText);
            } else {
                if (fail) {
                    fail(request);
                }
            }
        };

        getRequest = function getRequest() {
            if (window.ActiveXObject) {
                return new ActiveXObject('Microsoft.XMLHTTP');
            }
            else if (window.XMLHttpRequest) {
                return new XMLHttpRequest();
            }

            return false;
        };

        postBody = (content || false);

        request = getRequest();

        if (request) {
            request.onreadystatechange = stateChange;

            if (postBody !== false) {
                request.open("POST", url);
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                request.setRequestHeader('Content-type', 'application/json');
            } else {
                request.open("GET", url);
            }

            request.send(postBody);
        }
    };

    TraceKit.report.subscribe(function dcpLogger(errorReport) {
        try {
            if (!errorReport.stack) {
                errorReport.stack = (new Error('make stack')).stack;
                if (errorReport.stack) {
                    errorReport.stack = errorReport.stack.toString();
                }
            }
        } catch (e) {
        }
        if (typeof errorReport !== 'string') {
            errorReport = JSON.stringify(errorReport);
        }
        request("?app=DOCUMENT&action=COLLECT_ERROR", errorReport);
    });

    window.dcp = window.dcp || {};

    window.dcp.logger = function dcpLogger(error) {
        try {
            TraceKit.report(error);
        } catch(e) {

        }
        console.error(error);
    };

}(TraceKit));