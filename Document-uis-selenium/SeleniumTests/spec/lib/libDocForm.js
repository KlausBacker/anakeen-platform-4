var webdriver = require('selenium-webdriver'),
    driver = require("./initDriver.js"),
    fs = require('fs');

require('jasmine2-custom-message');

var docWindow, currentDriver, currentWindow;

var waitAnimationClose = function waitAnimationClose()
{
    'use strict';
    return currentDriver.wait(function waitDatePicker()
    {
        var locatorStr = webdriver.By.css('div.k-animation-container[style*=block]');
        return currentDriver.findElements(locatorStr).then(function waitAnimationDone(elements)
        {
            return elements.length === 0;
        });
    }, 4000);
};

var scrollToAttribute = function scrollToAttribute(attrid, index)
{
    'use strict';
    var aElt;

    if (typeof index === "undefined") {
        aElt = webdriver.By.css('div.dcpAttribute__content[data-attrid=' + attrid + '], div.dcpArray__content[data-attrid=' + attrid + ']');
    } else {
        aElt = webdriver.By.xpath('(//div[contains(@class, "dcpArray__content")][@data-attrid="' + attrid + '"])[' + (index + 1) + ']');
    }

    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(aElt);
    }, 5000);

    return currentDriver.findElements(aElt).then(function x(elements)
    {
        if (elements.length > 0) {
            var lastElement = elements[elements.length - 1];

            currentDriver.executeScript(
                "$(arguments[0]).css('outline', 'none');" +
                "if (($('body').height() > $(window).height()) && " +
                "($(window).height() - $(arguments[0]).offset().top - $(arguments[0]).height() + $(window).scrollTop()) < 600 && " +
                "($(window).height() + $(window).scrollTop()) < $('body').height()" +
                " ){" +
                "$(arguments[0]).css('outline', 'solid 1px blue');" +
                "$(arguments[0]).get(0).scrollIntoView(true);" +

                "}" +
                "if ($(arguments[0]).offset().top - ($(window).scrollTop() ) < 100) { " +
                "$(arguments[0]).css('outline', 'solid 1px green');" +
                "window.scrollBy(0,-100);" +
                "}" +
                "$('.tooltip-inner').hide()", lastElement);

        }
    });
};

/**
 * Switch to a document iframe
 * @param docWindowRef
 */
exports.setDocWindow = function setPageWindow(docWindowRef)
{
    'use strict';

    currentDriver = driver.getDriver();
    currentWindow = currentDriver.getWindowHandle();
    if (docWindowRef) {
        docWindow = currentDriver.findElement(docWindowRef);
        currentDriver.switchTo().frame(docWindow);
        return currentDriver.wait(function waitDocumentIsDisplayed()
        {
            return currentDriver.isElementPresent(webdriver.By.css(".dcpDocument__frames"));
        }, 5000);
    } else {
        docWindow = currentWindow;
    }
};

exports.selectTab = function selectTab(data)
{
    'use strict';

    currentDriver.executeScript('window.scrollTo(0,0);');
    currentDriver.wait(function waitMenuNormal()
    {
        return currentDriver.findElements(
            webdriver.By.css("nav.menu--fixed")).then(function waitMenuFixed(elements)
        {
            return elements.length === 0;
        });
    }, 5000);

    currentDriver.findElement(webdriver.By.css('.dcpTab__label[data-attrid=' + data.attrid + ']')).click();
    currentDriver.wait(function waitSelect()
    {
        return webdriver.until.elementIsVisible(currentDriver.findElement(webdriver.By.css('.dcpTab__content[data-attrid=' + data.attrid + ']')));
    }, 4000);

    return currentDriver.wait(function waitNumericInput()
    {
        return webdriver.until.elementIsVisible(currentDriver.findElement(webdriver.By.css(".dcpDocument__header")));
    }, 5000);

};
exports.setTextValue = function setTextValue(data)
{
    'use strict';
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + ']'));
    }, 5000);
    scrollToAttribute(data.attrid, data.index);

    if (typeof data.index === "undefined") {
        currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] input[type=text]')).sendKeys(data.rawValue);
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@type="text"]')).sendKeys(data.rawValue);
    }
    return currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] input[type=text]").blur()').then(
        function docFormExpect()
        {
            exports.verifyValue(data);
        }
    );
};

exports.setLongTextValue = function setLongTextValue(data)
{
    'use strict';
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + ']'));
    }, 5000);
    if (typeof data.index === "undefined") {
        currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] textarea')).sendKeys(data.rawValue);
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//textarea')).sendKeys(data.rawValue);
    }
    return currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] textarea").blur()');

};

exports.setDateValue = function setDateValue(data)
{
    'use strict';
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + ']'));
    }, 5000);

    if (data.today) {
        if (typeof data.index === "undefined") {
            currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] .k-select .k-i-calendar')).click();
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//span[contains(@class,"k-i-calendar")]')).click();

        }
        currentDriver.wait(function waitDatePicker()
        {
            return currentDriver.isElementPresent(webdriver.By.css('div.k-animation-container[style*=block] .k-nav-today'));
        }, 5000);
        currentDriver.wait(function waitDatePicker()
        {
            return webdriver.until.elementIsVisible(currentDriver.findElement(webdriver.By.css(
                'div.k-animation-container[style*=block] .k-nav-today')));
        }, 5000);
        currentDriver.sleep(500); // Wait animation done

        currentDriver.findElement(webdriver.By.css('div.k-animation-container[style*=block] .k-nav-today')).click();

    }
    if (data.date) {
        if (typeof data.index === "undefined") {
            currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-picker-wrap input[type="text"]')).sendKeys(data.date);
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[contains(@class,"dcpAttribute__content")][@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@type="text"]')).sendKeys(data.date);
        }
        currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] input").blur()');

    }

    return waitAnimationClose();
};

exports.setTimeValue = function setTimeValue(data)
{
    'use strict';
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + ']'));
    }, 5000);

    if (typeof data.selectedIndex !== "undefined") {
        if (typeof data.index === "undefined") {
            currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] .k-select .k-i-clock')).click();
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//span[contains(@class,"k-i-clock")]')).click();
        }

        currentDriver.wait(function waitTimePicker()
        {
            return currentDriver.isElementPresent(webdriver.By.xpath(
                "//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]//ul[contains(@id, '" + data.attrid + "')]"));
        }, 5000);
        currentDriver.sleep(500); // Wait animation done
        // set to 01:00

        currentDriver.findElement(webdriver.By.xpath(
            "//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]//ul[contains(@id, '" +
            data.attrid + "')]//li[contains(@class, 'k-item')][" + data.selectedIndex + "]")).click();

    }
    if (data.time) {
        if (typeof data.index === "undefined") {
            currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-picker-wrap input[type="text"]')).sendKeys(data.time);
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[contains(@class,"dcpAttribute__content")][@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@type="text"]')).sendKeys(data.time);
        }
        currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] input").blur()');
    }

    return waitAnimationClose();
};

exports.setNumericValue = function setNumericValue(data)
{
    'use strict';
    var eltRef;
    // click before send key to input
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + ']'));
    }, 5000);
    if (typeof data.index === "undefined") {
        currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] input.k-formatted-value[type=text]')).click();
        eltRef = 'div[data-attrid=' + data.attrid + '] input[type=text][data-role="numerictextbox"]';
        currentDriver.wait(function waitNumericInput()
        {
            return webdriver.until.elementIsVisible(currentDriver.findElement(webdriver.By.css(eltRef)));
        }, 5000);

        currentDriver.findElement(webdriver.By.css(eltRef)).sendKeys(data.number);
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[contains(@class,"k-formatted-value")]')).click();

        eltRef = '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@data-role="numerictextbox"]';
        currentDriver.wait(function waitNumericInput()
        {
            return webdriver.until.elementIsVisible(currentDriver.findElement(webdriver.By.xpath(eltRef)));
        }, 5000);

        currentDriver.findElement(webdriver.By.xpath(eltRef)).sendKeys(data.number);
    }
    return currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] input[type=text][data-role=numerictextbox]").blur()');

};

exports.setPasswordValue = function setPasswordValue(data)
{
    'use strict';
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + ']'));
    }, 5000);

    if (typeof data.index === "undefined") {
        currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] input[type=password]')).sendKeys(data.rawValue);
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@type="password"]')).sendKeys(data.rawValue);
    }
    return currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] input").blur()');
};

exports.setColorValue = function setColorValue(data)
{
    'use strict';

    scrollToAttribute(data.attrid, data.index);
    if (typeof data.index === "undefined") {
        currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] .k-select')).click();
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//span[contains(@class,"k-select")]')).click();
    }
    currentDriver.wait(function waitColorPicker()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.k-animation-container[style*=block] .k-flatcolorpicker'));
    }, 5000);
    currentDriver.sleep(500); // Wait animation done

    currentDriver.findElement(webdriver.By.css('div.k-animation-container[style*=block] .k-flatcolorpicker .k-hsv-gradient')).click();

    // Choose hue
    currentDriver.actions().mouseMove(currentDriver.findElement(webdriver.By.css('div.k-animation-container[style*=block] .k-flatcolorpicker .k-slider-track')), {
        x: Math.round(data.hue * 223 / 360) + 1,
        y: 3
    }).click().perform();
    // Choose saturation
    currentDriver.actions().mouseMove(currentDriver.findElement(webdriver.By.css('div.k-animation-container[style*=block] .k-flatcolorpicker .k-hsv-gradient')), {
        x: Math.floor(data.saturation * 2.5),
        y: Math.floor(180 - data.value * 1.8)
    }).click().perform();

    // Close color selector
    currentDriver.findElement(webdriver.By.xpath(
        '//*[contains(@class,"dcpLabel")][@data-attrid="' + data.attrid + '"]')).click();
    return waitAnimationClose();
};

exports.setFileValue = function setFileValue(data)
{
    'use strict';
    scrollToAttribute(data.attrid, data.index);
    var refWaitCss = "div[data-attrid=" + data.attrid + "] .dcpAttribute__value--transferring, div[data-attrid=" + data.attrid + "] .dcpAttribute__value--recording";

    // Need to wait because DOM will be destroyed after setValue
    currentDriver.wait(function waitNumericInput()
    {
        return currentDriver.findElements(
            webdriver.By.css(refWaitCss)).then(function waitAnimationDone(elements)
        {
            return elements.length === 0;
        });
    }, 5000);

    if (typeof data.index === "undefined") {
        if (driver.browser === "firefox") {
            //currentDriver.executeScript('$("div[data-attrid=' + data.attrid + '] input[type=file]").show()'); // only for firefox
            currentDriver.executeScript('$(arguments[0]).show()', currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] input[type=file]'))); // only for firefox
        }

        return currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] input[type=file]')).sendKeys(data.filePath);
    } else {
        if (driver.browser === "firefox") {
            currentDriver.executeScript('$(arguments[0]).show()', currentDriver.findElement(
                webdriver.By.xpath('(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@type="file"]'))); // only for firefox
        }

        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[@type="file"]')).sendKeys(data.filePath);
    }

    return currentDriver.sleep(10);

};

exports.setDocidValue = function setDocidValue(data)
{
    'use strict';

    scrollToAttribute(data.attrid, data.index);

    if (typeof data.index === "undefined") {

        currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] .k-input')).sendKeys(data.filterText);
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[contains(@class,"k-input")]')).sendKeys(data.filterText);
    }
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]"));
    }, 5000);

    currentDriver.sleep(500); // Wait animation done
    currentDriver.findElement(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]//span[contains(text(), '" + data.selectedText + "')]")).click();
    return waitAnimationClose();
};

exports.addAccountMultipleValue = function addAccountMultipleValue(data)
{
    'use strict';

    return exports.setDocidValue(data);
};

exports.setHtmlTextValue = function setTextValue(data)
{
    'use strict';

    if (typeof data.index === "undefined") {
        currentDriver.switchTo().frame(currentDriver.findElement(webdriver.By.css('div[data-attrid=' + data.attrid + '] .cke_wysiwyg_frame')));
    } else {

        currentDriver.switchTo().frame(currentDriver.findElement(webdriver.By.xpath('(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//*[contains(@class,"cke_wysiwyg_frame")]')));

    }
    currentDriver.findElement(webdriver.By.css('body')).click();
    currentDriver.findElement(webdriver.By.css('body')).sendKeys(data.textValue);

    currentDriver.switchTo().defaultContent();
    return currentDriver.switchTo().frame(docWindow);
};

exports.setEnumListValue = function setEnumListValue(data)
{
    'use strict';

    scrollToAttribute(data.attrid, data.index);
    if (typeof data.index === "undefined") {
        currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-input')).click();
    } else {
        currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//span[contains(@class,"k-input")]')).click();
    }

    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.k-animation-container'));
    }, 5000);

    currentDriver.sleep(500); // Wait animation done
    currentDriver.findElement(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]//li[contains(text(), '" + data.selectedText + "')]")).click().then(
        function docFormExpect()
        {
            exports.verifyValue(data);
        }
    );
    return waitAnimationClose();
};

exports.setEnumAutoValue = function setEnumAutoValue(data)
{
    'use strict';

    scrollToAttribute(data.attrid, data.index);
    if (typeof data.index === "undefined") {
        if (data.filterText) {
            currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-input[type=text]')).sendKeys(data.filterText);
        } else {
            currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-select')).click();
        }
    } else {
        if (data.filterText) {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[contains(@class,"k-input")][@type="text"]')).sendKeys(data.filterText);
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[contains(@class,"dcpAttribute__content")][@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//span[contains(@class,"k-select")]')).click();
        }
    }

    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]"));
    }, 5000);

    currentDriver.sleep(500); // Wait animation done
    currentDriver.findElement(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]//li[contains(text(), '" + data.selectedText + "')]")).click();

    return waitAnimationClose().then(
        function docFormExpect()
        {
            exports.verifyValue(data);
        }
    ); // Wait animation close
};

exports.setEnumRadioValue = function setEnumRadioValue(data)
{
    'use strict';

    var localPromise;
    scrollToAttribute(data.attrid, data.index);

    if (typeof data.index === "undefined") {
        localPromise = currentDriver.findElement(webdriver.By.xpath("//div[@data-attrid='" + data.attrid + "']//span[@class='dcpAttribute__value--enumlabel--text'][contains(text(), '" + data.label + "')]")).click();
    } else {
        localPromise = currentDriver.findElement(webdriver.By.xpath(
            '(//div[@data-attrid="' + data.attrid + '"])[' +
            (data.index + 1) +
            "]//span[@class='dcpAttribute__value--enumlabel--text'][contains(text(), '" + data.label + "')]")).click();
    }

    return localPromise.then(
        function docFormExpect()
        {
            exports.verifyValue(data);
        }
    );
};

exports.addEnumAutoValue = function addEnumAutoValue(data)
{
    'use strict';

    scrollToAttribute(data.attrid, data.index);

    if (data.filterText) {
        if (typeof data.index === "undefined") {
            currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-input')).sendKeys(data.filterText);
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[contains(@class,"dcpAttribute__content")][@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[contains(@class,"k-input")]')).sendKeys(data.filterText);
        }
    } else {
        if (typeof data.index === "undefined") {
            currentDriver.findElement(webdriver.By.css('div.dcpAttribute__content[data-attrid=' + data.attrid + '] .k-input')).click();
        } else {
            currentDriver.findElement(webdriver.By.xpath(
                '(//div[contains(@class,"dcpAttribute__content")][@data-attrid="' + data.attrid + '"])[' + (data.index + 1) + ']//input[contains(@class,"k-input")]')).click();
        }
    }

    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]"));
    }, 5000);

    currentDriver.sleep(500); // Wait animation done
    currentDriver.findElement(webdriver.By.xpath("//div[contains(@class, 'k-animation-container')][contains(@style, 'block')]//li[contains(text(), '" + data.selectedText + "')]")).click();

    return waitAnimationClose().then(
        function docFormExpect()
        {
            exports.verifyValue(data);
        }); // Wait animation close
};
exports.selectEnumCheckboxValue = function addEnumCheckboxValue(data)
{
    'use strict';
    var localPromise;

    scrollToAttribute(data.attrid, data.index);

    if (typeof data.index === "undefined") {
        localPromise = currentDriver.findElement(webdriver.By.xpath("//div[@data-attrid='" + data.attrid + "']//span[@class='dcpAttribute__value--enumlabel--text'][contains(text(), '" + data.label + "')]")).click();
    } else {
        localPromise = currentDriver.findElement(webdriver.By.xpath("(//div[@data-attrid='" + data.attrid + "'])[" + (data.index + 1) + "]//span[@class='dcpAttribute__value--enumlabel--text'][contains(text(), '" + data.label + "')]")).click();
    }
    return localPromise.then(
        function docFormExpect()
        {
            exports.verifyValue(data);
        }); // Wait animation close
};

exports.addRow = function addRow(data)
{
    'use strict';
    var currentCount = 0;
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.isElementPresent(webdriver.By.css('div.dcpArray__content[data-attrid=' + data.attrid + '] .dcpArray__button--add'));
    }, 5000);
    scrollToAttribute(data.attrid);
    currentDriver.wait(function waitSelect()
    {
        return webdriver.until.elementIsVisible(currentDriver.findElement(webdriver.By.css('div.dcpArray__content[data-attrid=' + data.attrid + '] button.dcpArray__add')));
    }, 5000);

    currentDriver.executeScript('return $("tr.dcpArray__content__line[data-attrid=' + data.attrid + ']").length;').then(function countTr(trCount)
    {
        currentCount = trCount;
    });
    currentDriver.findElement(webdriver.By.css('div.dcpArray__content[data-attrid=' + data.attrid + '] button.dcpArray__add')).click();
    currentDriver.wait(function waitSelect()
    {
        return currentDriver.executeScript('return $("tr.dcpArray__content__line[data-attrid=' + data.attrid + ']").length;').then(function verifyCountTr(trCount)
        {
            return trCount === (currentCount + 1);
        });
    }, 5000);
    return scrollToAttribute(data.attrid);
};

exports.createAndClose = function createAndClose()
{
    'use strict';
    currentDriver.findElement(webdriver.By.xpath('//nav[contains(@class,"dcpDocument__menu")]//a/*[contains(text(),"Créer et fermer")]')).click();
};
exports.saveAndClose = function saveAndClose()
{
    'use strict';
    currentDriver.findElement(webdriver.By.xpath('//nav[contains(@class,"dcpDocument__menu")]//a/*[contains(text(),"Enregistrer et fermer")]')).click();
};

exports.openMenu = function openMenu(config)
{
    'use strict';

    var menuPath = webdriver.By.xpath('//nav[contains(@class,"dcpDocument__menu")]//li//*[contains(text(),"' + config.listMenu + '")]');
    currentDriver.wait(function waitMenuList()
    {
        return currentDriver.isElementPresent(menuPath);
    }, 5000);
    currentDriver.findElement(menuPath).click();

    menuPath = webdriver.By.xpath('//nav[contains(@class,"dcpDocument__menu")]//a/*[contains(text(),"' + config.itemMenu + '")]');
    currentDriver.wait(function waitMenuList()
    {
        return webdriver.until.elementIsVisible(menuPath);
    }, 5000);
    currentDriver.sleep(500); // Wait animation done
    currentDriver.findElement(menuPath).click();

};

exports.getValue = function getValue(attrid)
{
    'use strict';
    return currentDriver.executeScript("return window.dcp.document.documentController('getValue', '" + attrid + "');");
};

exports.verifyValue = function verifyValue(verification)
{
    'use strict';

    if (typeof verification.expectedValue !== "undefined") {
        exports.getValue(verification.attrid).then(function docForm_check_value(value)
        {
            var rawValue, msg;
            if (typeof verification.index === "undefined") {
                if (Array.isArray(value)) {
                    rawValue=value.map(function docFormVerifyValueMap(x) {
                        return x.value;
                    });
                } else {
                    rawValue=value.value;
                }

            } else {
                if (Array.isArray(value[verification.index])) {
                    rawValue=value[verification.index].map(function docFormVerifyValueMapIndex(x) {
                        return x.value;
                    });
                } else {
                    rawValue=value[verification.index].value;
                }

                //console.log("Verify ", verification.attrid, verification.expectedValue, rawValue);
               // console.log("jcm",jcm, jcm.since);


            }
            msg='Attribute :"'+verification.attrid+
                ((typeof verification.index === "undefined")?"":(" #"+verification.index))+
            '", expected "'+verification.expectedValue+
            '", got :"'+rawValue+'"';

            since(msg).expect(rawValue).toEqual(verification.expectedValue);
        });
    }

};
