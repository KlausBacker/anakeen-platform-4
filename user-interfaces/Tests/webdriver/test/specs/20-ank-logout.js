const path = require('path');
const scriptName = path.basename(__filename);
const { login, setWindowSize } = require("../config");

describe(`${scriptName} : component : basic check`, () => {
  //Get a login cookie and resize the browser to the standard size
  beforeEach(async () => {
    setWindowSize({ browser });
    return login({ browser, login: "zoo.user1", password: "anakeen" });
  });
  //Go to the logout test page and check dom
  it("ank-login : Check dom", () => {
    browser.url("/autotest/anklogout");
    //Check if the logout button is present
    expect("#logout button").to.be.there("logout has no button");
    //Check if the logout button is present
    expect("#logout-slot button").to.be.there("logout slot has no button");
    //Check if the slot content is present
    expect("#logout-slot .slot-content").to.be.there("logout slot has no slot content");
    //Check if the content of the slot is good
    expect("#logout-slot .slot-content").to.have.text("Click here to logout");
  });
  //Execute the logout and test it
  it("ank-logout : Check logout", async () => {
    await browser.url("/autotest/anklogout");
    //Click on the logout button
    await browser.click("#logout");
    await browser.waitUntil(() => {
      //Wait to be disconnected, check if the url of the page has changed
      const currentUrl = browser.getUrl();
      return !(/.*\/autotest\/anklogout.*/.test(currentUrl));
    });
    //Go to the welcome page
    await browser.url("/autotest/welcome");
    //If we are logout the welcome text is not here
    const title = await browser.getTitle();
    expect(title === "Welcome", "User is not disconnected").to.be.false;
  });

  it("ank-logout : Check logout with slot",async () => {
    await browser.url("/autotest/anklogout");
    //Click on the logout button
    await browser.click("#logout-slot");
    await browser.waitUntil(() => {
      //Wait to be disconnected, check if the url of the page has changed
      const currentUrl = browser.getUrl();
      return !(/.*\/autotest\/anklogout.*/.test(currentUrl));
    });
    //Go to the welcome page
    await browser.url("/autotest/welcome");
    //If we are logout the welcome text is not here
    const title = await browser.getTitle();
    expect(title === "Welcome", "User is not disconnected").to.be.false;
  });

  it("ank-logout : Check event", async () => {
    let prevented = false;
    await browser.url("/autotest/anklogout");
    await browser.execute(() => {
      document.getElementById("logout").addEventListener("beforeLogout", (event) => {
        event.preventDefault();
        document.getElementById("result").textContent = "beforePrevented";
      });
    });
    await browser.click("#logout");
    await browser.waitUntil(() => {
      if (browser.getText("#result") === "beforePrevented") {
        expect("#result").to.have.text("beforePrevented");
        return true;
      }
    })
  });
});
