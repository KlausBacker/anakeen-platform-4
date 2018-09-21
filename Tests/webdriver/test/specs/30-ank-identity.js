const path = require("path");
const scriptName = path.basename(__filename);
const { login, setWindowSize } = require("../config");

const waitForComponentToBeLoaded = async () => {
  return await browser.waitUntil(() => {
    const result = browser.execute(() => {
      return [
        "identity",
        "identity-large",
        "identity-email",
        "identity-password"
      ].reduce((acc, key) => {
        if (acc !== false) {
          return document.getElementById(key).publicMethods.isReady();
        }
        return acc;
      }, undefined);
    });
    return result.value;
  });
};

describe(`${scriptName} : component : basic check`, () => {
  //Get a login cookie and resize the browser to the standard size
  before(async () => {
    return login({ browser, login: "zoo.user1", password: "anakeen" });
  });
  beforeEach(async () => {
    return setWindowSize({ browser });
  });
  it("ank-identity : Check dom", async () => {
    await browser.url("/autotest/ankidentity");
    //Wait for all elements to be ready
    await waitForComponentToBeLoaded();
    expect(
      await browser.isExisting("#identity .identity-badge-initials"),
      "Identity has no initials"
    ).to.be.true;
    expect(
      await browser.getText("#identity .identity-badge-initials")
    ).to.have.string("JG", "The initial are not good");

    expect(
      await browser.isExisting("#identity-large .identity-badge-initials"),
      "Identity has no initials"
    ).to.be.true;
    expect(
      await browser.getText("#identity-large .identity-badge-initials")
    ).to.have.string("JG", "The initial are not good");
    expect(
      await browser.getText("#identity-large .identity-badge-extension-name")
    ).to.have.string("Jean Grand", "The name is not good");
    expect(
      await browser.getText("#identity-large .identity-badge-extension-email")
    ).to.have.string("gj@example.net", "The mail is not good");

    expect(
      await browser.isExisting("#identity-email .identity-badge-initials"),
      "Identity has no initials"
    ).to.be.true;
    expect(
      await browser.getText("#identity-email .identity-badge-initials")
    ).to.have.string("JG", "The initial are not good");
    expect(
      await browser.isExisting("#identity-email .identity-email-modifier"),
      "mail modifier is not here"
    ).to.be.true;

    expect(
      await browser.isExisting("#identity-password .identity-badge-initials"),
      "Identity has no initials"
    ).to.be.true;
    expect(
      await browser.getText("#identity-password .identity-badge-initials")
    ).to.have.string("JG", "The initial are not good");
    expect(
      await browser.isExisting("#identity-password .identity-password-modifier"),
      "password modifier is not here"
    ).to.be.true;
  });
  //Check if the modal open and close
  it("ank-identity : check open modif", async () => {
    await browser.url("/autotest/ankidentity");
    //Wait for all elements to be ready
    await waitForComponentToBeLoaded();
    //Open change email popup
    await browser.click("#identity-email .identity-badge");
    expect(
      await browser.isVisible('.identity-modification-popup[aria-hidden="false"]'),
      "check if menu is good"
    ).to.be.true;
    //Click on menu
    await browser.click('.identity-modification-popup[aria-hidden="false"]');
    //Wait until k-window has finished css animation
    await browser.waitUntil(() => {
      return browser.getCssProperty(".identity-email-window", "opacity").value === 1;
    });
    expect(
      await browser.isVisible(".identity-email-modifier"),
      "check if email modifier is visible"
    ).to.be.true;
    //Click on cancel button
    //await browser.debug();
    await browser.click(".identity-email-modifier .identity-emailModifier--cancel");
    //Wait until k window ended css anim
    await browser.waitUntil(() => {
      return browser.isVisible(".identity-email-window") === false;
    });
    expect(
      await browser.isVisible(".identity-email-modifier"),
      "check if email modifier is hidden"
    ).to.be.false;
    await browser.url("/autotest/ankidentity");
    //Wait for all elements to be ready
    await waitForComponentToBeLoaded();
    //Open change password popup
    await browser.click("#identity-password .identity-badge");
    expect(
      await browser.isVisible('.identity-modification-popup[aria-hidden="false"]'),
      "check if menu is good"
    ).to.be.true;
    //Click on menu
    await browser.click('.identity-modification-popup[aria-hidden="false"]');
    //Wait until k-window has finished css animation
    await browser.waitUntil(() => {
      return browser.getCssProperty(".identity-password-window", "opacity").value === 1;
    });
    expect(
      await browser.isVisible(".identity-password-modifier"),
      "check if password modifier is visible"
    ).to.be.true;
    //Click on cancel button
    await browser.click(".identity-password-modifier .identity-passwordModifier--cancel");
    //Wait until k window ended css anim
    await browser.waitUntil(() => {
      return browser.isVisible(".identity-password-window") === false;
    });
    expect(
      await browser.isVisible(".identity-email-modifier"),
      "check if password modifier is hidden"
    ).to.be.false;
  });
  it("ank-identity : check userLoad event standard", async () => {
    await browser.url("/autotest/ankidentity");
    //Wait for all elements to be ready
    await waitForComponentToBeLoaded();
    //Register beforeUserLoaded Event
    await browser.execute(() => {
      document
        .getElementById("identity")
        .addEventListener("beforeUserLoaded", event => {
          document.getElementById("result--before").textContent = "before";
        });
    });
    //Register afterUserLoaded event
    await browser.execute(() => {
      document
        .getElementById("identity")
        .addEventListener("afterUserLoaded", event => {
          document.getElementById("result--after").textContent = "after";
        });
    });
    //Trigger the change
    await browser.execute(() => {
      document.getElementById("identity").publicMethods.fetchUser();
    });
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "after";
    });
    expect(await (browser.getText("#result--after"))).to.have.string("after", "afterUserLoaded is not executed");
    expect(await (browser.getText("#result--before"))).to.have.string("before", "beforeUserLoaded is not executed");
  });
  it("ank-identity : check userLoad event modify", async () => {
    await browser.url("/autotest/ankidentity");
    //Wait for all elements to be ready
    await waitForComponentToBeLoaded();
    //Register beforeUserLoaded Event
    await browser.execute(() => {
      document
        .getElementById("identity")
        .addEventListener("beforeUserLoaded", event => {
          document.getElementById("result--before").textContent = "before";
          event.detail[0].initials = "CB";
        });
    });
    //Register afterUserLoaded event
    await browser.execute(() => {
      document
        .getElementById("identity")
        .addEventListener("afterUserLoaded", event => {
          document.getElementById("result--after").textContent = "after";
        });
    });
    //Trigger the change
    await browser.execute(() => {
      document.getElementById("identity").publicMethods.fetchUser();
    });
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "after";
    });
    expect(
      await browser.getText("#identity .identity-badge-initials")
    ).to.have.string("CB", "The initial are not good");
  });
  it("ank-identify : check change email", async () => {
    await browser.url("/autotest/ankidentity");
    //Register event
    await browser.execute(() => {
      document
        .getElementById("identity-email")
        .addEventListener("beforeMailAddressChange", event => {
          document.getElementById("result--before").textContent = "before";
        });
    });
    //Register afterMailAddressChange event
    await browser.execute(() => {
      document
        .getElementById("identity-email")
        .addEventListener("afterMailAddressChange", event => {
          document.getElementById("result--after").textContent = "after";
        });
    });
    //Open change email popup
    await browser.click("#identity-email .identity-badge");
    //Click on menu
    await browser.click('.identity-modification-popup[aria-hidden="false"]');
    //Wait until k-window has finished css animation
    await browser.waitUntil(() => {
      return browser.getCssProperty(".identity-email-window", "opacity").value === 1;
    });
    //Complete
    await browser.setValue(
      '.identity-email-modifier[data-role="window"] .identity-email-input',
      "test@test.test"
    );
    await browser.setValue(
      '.identity-email-modifier[data-role="window"] input[type="password"]',
      "anakeen"
    );
    //emailModifier--validate
    await browser.click(".identity-emailModifier--validate");
    //Wait event
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "after";
    });
    //check event
    expect(await (browser.getText("#result--after"))).to.have.string("after", "afterUserLoaded is not executed");
    expect(await (browser.getText("#result--before"))).to.have.string("before", "beforeUserLoaded is not executed");
    //Check result
    expect(
      await browser.getText("#identity-email .identity-badge-extension-email")
    ).to.have.string("test@test.test", "The mail is not good");
    //////RESTART PAGE////
    await browser.url("/autotest/ankidentity");
    //Wait for all elements to be ready
    await waitForComponentToBeLoaded();
    //Restore email with hook
    //gj@example.net
    await browser.execute(() => {
      document
        .getElementById("identity-email")
        .addEventListener("beforeMailAddressChange", event => {
          event.detail[0].newEmail = "gj@example.net";
        });
    });
    //Register afterMailAddressChange event
    await browser.execute(() => {
      document
        .getElementById("identity-email")
        .addEventListener("afterMailAddressChange", event => {
          document.getElementById("result--after").textContent = "after";
        });
    });
    //Open change email popup
    await browser.click("#identity-email .identity-badge");
    //Click on menu
    await browser.click('.identity-modification-popup[aria-hidden="false"]');
    //Wait until k-window has finished css animation
    await browser.waitUntil(() => {
      return browser.getCssProperty(".identity-email-window", "opacity").value === 1;
    });
    await browser.setValue(
      '.identity-email-modifier[data-role="window"] .identity-email-input',
      "test@test.test"
    );
    await browser.setValue(
      '.identity-email-modifier[data-role="window"] input[type="password"]',
      "anakeen"
    );
    //emailModifier--validate
    await browser.click(".identity-emailModifier--validate");
    //Wait after event
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "after";
    });
    //Check result
    expect(
      await browser.getText("#identity-email .identity-badge-extension-email")
    ).to.have.string("gj@example.net", "The mail is not good");
  });
});
