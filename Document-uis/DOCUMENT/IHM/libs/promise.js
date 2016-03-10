if (window.Promise) {
    define([], function nativePromise() {
        'use strict';
        return window.Promise;
    });
} else {
    define(["es6-promise"], function polifyllPromise(Promise) {
        'use strict';
        return Promise.Promise;
    });
}

