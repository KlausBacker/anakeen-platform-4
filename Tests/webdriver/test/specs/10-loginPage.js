const path = require('path');
const scriptName = path.basename(__filename);
const {setWindowSize} = require("../config");

describe(`${scriptName} : check login page`, () => {
  beforeEach(() => {
    setWindowSize({browser});
  });
  //Check if dom is generated and take a screenshot
  it("Login page : analyze DOM", () => {
    browser.url("/login/");
    //Check basic element and dom element
    expect('ank-authent').to.be.there("Tag not found");
    expect('.authent-form').to.be.there("Inside form not found");
    expect('.authent-password').to.be.there("Inside password not found");
    expect('.authent-buttons').to.be.there("Inside authent button not found");
    expect('.authent-bottom').to.be.there("Inside bottom part not found");
    expect(".authent-locale").to.be.there("Inside locale not found");
    expect(".authent-help-button").to.be.there("Inside help not found");
    expect(".authent-forget-button").to.be.there("Inside password forgot button not found");
  });
  //Analyze some behaviours
  it("Login page : analyze interaction",async () => {
    await browser.url("/login/");
    //Check click on display password
    expect(await browser.getAttribute(".authent-password input", "type")).to.have.string("password");
    await browser.click(".authent-password button");
    expect(await browser.getAttribute(".authent-password input", "type")).to.have.string("text");
    //Check help button
    await browser.url("/login/");
    await browser.click(".authent-help-button");
    expect((await browser.isVisible(".authent-help")), "authent help is not visible").to.be.true;
    //Check password forget
    await browser.url("/login/");
    await browser.click(".authent-forget-button");
    expect((await browser.isVisible(".authent-form--forget")), "authent forget is not visible").to.be.true;
  });
  it("Login page : do a login", async() => {
    await browser.url("/autotest/welcome");
    //Do a login
    await browser.setValue("#login", "admin");
    await browser.setValue(".authent-password input", "anakeen");
    await browser.click(".authent-login-button");
    //Check welcome page
    await browser.waitForExist('#welcome',5000);
    expect((await browser.getText('#welcome')).toLowerCase()).to.have.string("welcome", "We didn't find the welcome page");
  });
});