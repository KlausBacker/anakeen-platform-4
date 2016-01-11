var webdriver = require('selenium-webdriver'),
    config = require('config.json')('./driver.json');

var driver, buildedDriver = null;

if (!config.driver) {
    throw new Error("You need to specify the driver");
}

driver = new webdriver.Builder().forBrowser(config.driver);

if (config.server) {
    driver.usingServer(config.server);
}

if (config.capabilities) {
    driver.getCapabilities().merge(config.capabilities);
}

exports.getDriver = function buildDriver()
{
    'use strict';
    if (buildedDriver === null) {
        buildedDriver = driver.build();
        /*
        buildedDriver.getSession().then(function x(session) {
            console.log("session", session);
        });*/
    }

    return buildedDriver;
};

exports.quit = function quitDriver() {
    'use strict';
    return buildedDriver.quit().then (function initDriverquit() {
        buildedDriver=null;
    });
};

exports.rootUrl = config.rootUrl;
exports.data = config.data;
exports.screenshotDirectory=config.screenshotDirectory;
exports.browser=config.driver;