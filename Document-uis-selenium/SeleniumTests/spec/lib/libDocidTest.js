var webdriver = require('selenium-webdriver'),
    driver = require("./initDriver.js");

require('jasmine2-custom-message');

var currentDriver;
/**
 * Init selenium driver
 */
exports.initDriver = function initDriver()
{
    'use strict';
    currentDriver = driver.getDriver();
};

exports.verifyClick = function verifyClick(data)
{
    'use strict';
    var elt, xpath;
    var prefixMsg = 'Attribute :"' + data.attrid +
        ((typeof data.index === "undefined") ? "" : (" #" + data.index)) + '"';

    if (typeof data.rowIndex !== "undefined") {
        xpath = "(//div[@data-attrid='" + data.attrid + "']//span[contains(@class,'dcpAttribute__value--read')])[" +
            (data.rowIndex + 1) + "]/a";
    }
    else {
        xpath = "//div[@data-attrid='" + data.attrid + "']//span[contains(@class,'dcpAttribute__value--read')]/a";
    }
    if (typeof data.index !== "undefined") {
        xpath += "[" + (data.index + 1) + "]";
    }
    elt = webdriver.By.xpath(xpath);
    // For IE need to move and click after due to tooltip interaction
    currentDriver.actions().mouseMove(currentDriver.findElement(elt)).perform();
    currentDriver.sleep(50);
    currentDriver.findElement(elt).click();
    currentDriver.sleep(1000); // wait animation

    if (data.expected.historicTitle) {
        currentDriver.wait(function waitDocumentIsDisplayed()
        {
            return webdriver.until.elementIsVisible(webdriver.By.css(".document-history .history-level--revision"));
        }, 5000);
        elt = webdriver.By.xpath("//div[contains(@class,'document-history')]/..//span[contains(@class,'k-window-title')]");
        currentDriver.findElement(elt).getText().then(function x(text)
        {
            var msg = "Verify history :" + prefixMsg +
                ', expected "' + data.expected.historicTitle +
                '", got :"' + text + '"';
            since(msg).expect(text).toEqual(data.expected.historicTitle);
        });
    }

    if (data.expected.documentTitle) {
        currentDriver.wait(function waitDocumentIsDisplayed()
        {
            return webdriver.until.elementIsVisible(webdriver.By.css("iframe.k-content-frame"));
        }, 5000);

        elt = webdriver.By.xpath("//iframe[contains(@class,'k-content-frame')]/../..//span[contains(@class,'k-window-title')]");

        exports.getFirstVisibleElement(elt).then(function x(windowTitleElement)
        {
            windowTitleElement.getText().then(function x(text)
            {
                var msg = "Verify document :" + prefixMsg +
                    ', expected "' + data.expected.documentTitle +
                    '", got :"' + text + '"';
                //console.log("verify", msg);
                since(msg).expect(text).toEqual(data.expected.documentTitle);
            });
        });

    }
    if (data.expected.propertiesTitle) {
        elt = webdriver.By.xpath("//div[contains(@class,'document-properties')]/..//span[contains(@class,'k-window-title')]");
        currentDriver.findElement(elt).getText().then(function x(text)
        {
            var msg = "Verify properties :" + prefixMsg +
                ', expected "' + data.expected.propertiesTitle +
                '", got :"' + text + '"';
            since(msg).expect(text).toEqual(data.expected.propertiesTitle);
        });
    }

    exports.getFirstVisibleElement(webdriver.By.css(".k-window-actions .k-i-close")).click();

    currentDriver.wait(function waitDocumentIsDisplayed()
    {
        var locator=webdriver.By.css(".k-window");
        var links = currentDriver.findElements(locator);
        return webdriver.promise.filter(links, function closeDialogWindowFilter(link)
        {
            return link.isDisplayed();
        }).then(function closeDialogWindoGetwFirst(visibleLinks)
        {
            return visibleLinks.length === 0;
        });

    }, 5000);

};

exports.getFirstVisibleElement = function getFirstVisibleElement(locator)
{
    'use strict';
    var ftVisibleLink = function firstVisibleLink(driver)
    {
        var links = driver.findElements(locator);
        return webdriver.promise.filter(links, function closeDialogWindowFilter(link)
        {
            return link.isDisplayed();
        }).then(function closeDialogWindoGetwFirst(visibleLinks)
        {
            return visibleLinks[0];
        });
    };
    return currentDriver.findElement(ftVisibleLink);
};