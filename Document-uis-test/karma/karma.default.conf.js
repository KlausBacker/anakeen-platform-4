var config = {

    // base path that will be used to resolve all patterns (eg. files, exclude)
    basePath: '',

    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['jasmine', 'requirejs'],

    // list of files / patterns to load in the browser
    files: [
        'test-main.js',
        {pattern: 'test-css.js', watched: false, served: true, included: false},
        {pattern: '../../DOCUMENT/IHM/widgets/attributes/**/test*.js', included: false},
        {pattern: '../../DOCUMENT/IHM/test/test*.js', included: false}
    ],

    // list of files to exclude
    exclude: [],

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {},

    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['progress'],

    proxies: {
        '/dynacase//api/v1/' :  'http://localhost/TEST_DOCUMENT/FALSE_API/',
        '/dynacase/': 'http://localhost/',
        '/resizeimg.php': 'http://localhost/resizeimg.php',
        '/lib/': 'http://localhost/lib/',
        '/css/': 'http://localhost/css/',
        '/FDL/': 'http://localhost/FDL/',
        '/file/' : 'http://localhost/TEST_DOCUMENT/FALSE_API/'
    },

    // web server port
    port: 9876,

    // enable / disable colors in the output (reporters and logs)
    colors: true,

    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: false,

    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['PhantomJS'],
    //browsers : ['Chrome'],

    browserDisconnectTimeout: '100000',
    browserNoActivityTimeout: '100000',
    browserDisconnectTolerance: 1, // default 0
    captureTimeout: 4 * 60 * 1000, //default 60000

    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: true
};

module.exports = config;