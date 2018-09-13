const path = require('path');
const scriptName = path.basename(__filename);
const {login, setWindowSize} = require("../config");

describe(`${scriptName} : basic check`, () => {
  //Get a login cookie and resize the browser to the stnndard size
  before(async () => {
    setWindowSize({browser});
    return login({browser, login: "admin", password : "anakeen"});
  });
  //Go to the welcome page (accessoble
  it(`check login procedure`, async () => {
    await browser.url("/autotest/welcome");
    expect((await browser.getText('#welcome')).toLowerCase()).to.have.string("welcome", "We didn't find the welcome page");
  });
});
