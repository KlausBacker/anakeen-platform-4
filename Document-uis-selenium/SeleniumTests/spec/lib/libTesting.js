var webdriver = require('selenium-webdriver'),
    driver = require("./initDriver.js"),
    fs = require('fs');

/**
 * Perform a login from connection page
 * @param loginName
 * @param password
 */
exports.login = function login(loginName, password)
{
    'use strict';
    var currentDriver = driver.getDriver();
    var loginInput, passInput;
    currentDriver.get(driver.rootUrl);
    //currentDriver.manage().window().maximize();
    currentDriver.manage().window().setSize(1280,1024);
    currentDriver.wait(function waitLogin()
    {
        return currentDriver.isElementPresent({id: "auth_user"});
    }, 2000);

    loginInput = currentDriver.findElement({id: "auth_user"});
    passInput = currentDriver.findElement({id: "auth_pass"});

    loginInput.sendKeys(loginName);
    passInput.sendKeys(password);

        exports.saveScreenshot("login");
    return currentDriver.findElement(webdriver.By.xpath('//a[contains(text(),"Se connecter")]')).click();
    //currentDriver.sleep(1000);

};

exports.saveScreenshot=function saveScreenshot(name) {
    'use strict';
    var currentDriver = driver.getDriver();
     currentDriver.takeScreenshot().then(function loginScreenShot(image)
    {
        var outDir=driver.screenshotDirectory+'/'+driver.browser+'/';
        var outFile=outDir+name+'.png';
        fs.access(driver.screenshotDirectory,  fs.W_OK,function mkdirScreenshotDir (err)
        {
            if (err && err.code === "ENOENT") {
                    fs.mkdirSync(driver.screenshotDirectory);
                }
            fs.access(outDir, fs.W_OK, function mkdirBrowserDir(err)
            {
                if (err && err.code === "ENOENT") {
                    fs.mkdirSync(outDir);
                }

                fs.writeFile(outFile, image, 'base64', function writeScreenShot(err)
                {
                    console.log("Record screenshot to " + outFile, err ? err : "");
                });
            });
        });
    });
};
