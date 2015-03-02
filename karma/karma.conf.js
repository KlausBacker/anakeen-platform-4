// Karma configuration
// Generated on Wed Aug 06 2014 11:53:47 GMT+0200 (CEST)

module.exports = function (config) {
    config.set({

        // base path that will be used to resolve all patterns (eg. files, exclude)
        basePath :      '',

        // frameworks to use
        // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
        frameworks :    ['jasmine', 'requirejs'],

        // list of files / patterns to load in the browser
        files :         [
            'test-main.js',
            {pattern: 'test-css.js', watched: false, served: true, included: false},
            {pattern : '../DOCUMENT/IHM/widgets/attributes/**/test*.js', included : false},
            {pattern : '../DOCUMENT/IHM/test/test*.js', included : false}
          //  {pattern : '../css/dcp/document/bootstrap.css', included : false},
          ///  {pattern : '../css/dcp/document/kendo.css', included : false}
        ],

        // list of files to exclude
        exclude :       [],

        // preprocess matching files before serving them to the browser
        // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
        preprocessors : {},

        // test results reporter to use
        // possible values: 'dots', 'progress'
        // available reporters: https://npmjs.org/browse/keyword/karma-reporter
        reporters :     ['progress'],

        proxies :   {
//            '/dynacase/' : 'http://dynacase.dev:8081/'
            '/dynacase/' : 'http://localhost:8080/dynacase/',
            '/resizeimg.php': 'http://localhost:8080/dynacase/resizeimg.php'
        },


        // web server port
        port :      9876,

        // enable / disable colors in the output (reporters and logs)
        colors :    true,

        // level of logging
        // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
        logLevel :  config.LOG_INFO,

        // enable / disable watching file and executing tests whenever any file changes
        autoWatch : false,

        browserStack :    {
            username :  '',
            accessKey : ''
        },

        // define browsers
        customLaunchers : {
            "bs_chrome_mac" :   {
                base :            'BrowserStack',
                browser :         'chrome',
                browser_version : '36.0',
                os :              'OS X',
                os_version :      'Mountain Lion'
            },
            "bs_ie11_win8" :    {
                "base" :            'BrowserStack',
                "os" :              "Windows",
                "os_version" :      "8.1",
                "browser_version" : "11.0",
                "browser" :         "ie"
            }, "bs_ie8_win7" :  {
                "os" :              "Windows",
                "os_version" :      "7",
                "base" :            'BrowserStack',
                "browser_version" : "8.0",
                "browser" :         "ie"
            }, "bs_ie9_win7" :  {
                "os" :              "Windows",
                "os_version" :      "7",
                "base" :            'BrowserStack',
                "browser_version" : "9.0",
                "browser" :         "ie"
            }, "bs_ie10_win7" : {
                "os" :              "Windows",
                "os_version" :      "7",
                "base" :            'BrowserStack',
                "browser_version" : "10.0",
                "browser" :         "ie"
            }, "bs_ie11_win7" : {
                "os" :              "Windows",
                "os_version" :      "7",
                "base" :            'BrowserStack',
                "browser_version" : "11.0",
                "browser" :         "ie"
            }

        },

        // start these browsers
        // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher

    //   browsers : ['PhantomJS'],
        browsers : ['Chrome'],

        browserDisconnectTimeout :   '100000',
        browserNoActivityTimeout :   '100000',
        browserDisconnectTolerance : 1, // default 0
        captureTimeout : 4 * 60 * 1000, //default 60000

        // Continuous Integration mode
        // if true, Karma captures browsers, runs the tests and exits
        singleRun :                  false
    });
};
