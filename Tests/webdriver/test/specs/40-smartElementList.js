const path = require("path");
const scriptName = path.basename(__filename);
const { login, setWindowSize } = require("../config");

const waitForComponentToBeLoaded = async () => {
  return await browser.waitUntil(() => {
    const result = browser.execute(() => {
      return [
        "simple",
        "simple--withoutcollection",
        "simple--label",
        "simple--logo",
        "simple--content-url",
        "simple--slot",
        "simple--slot--bis"
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

describe(`${scriptName} : basic check`, () => {
  //Get a login cookie and resize the browser to the stnndard size
  before(async () => {
    setWindowSize({ browser });
    return login({ browser, login: "zoo.user1", password: "anakeen" });
  });
  //Add a ready event
  it("ready event", async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    expect(true, "Ready event not detected").to.be.true;
  });
  //Analyze dom content
  it(`check dom custom element`, async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Check text label
    expect(
      await browser.getText("#simple .seList__header__label")
    ).to.have.string("TEST DE RENDU DE DOCUMENT", "Label is not good");
    expect(
      await browser.getText("#simple--label .seList__header__label")
    ).to.have.string("TEST", "Custom label is not  good");
    //Check logo seList__header__slot
    expect(
      await browser.getAttribute("#simple .seList__logo__img", "src")
    ).to.have.string(
      "/CORE/Images/anakeen-logo.svg",
      "Default img is not good"
    );
    expect(
      await browser.getAttribute("#simple--logo .seList__logo__img", "src")
    ).to.have.string("/Images/se-tst_render.png", "Custom img is not good");
    //Check slot
    expect(await browser.getText("#mySuperList--bis")).to.have.string(
      "SUPER",
      "Slot bis syntax is not good"
    );
  });
  //Analyze event
  it(`check event : se-selected`, async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document.getElementById("simple").addEventListener("se-selected", () => {
        document.getElementById("result--after").textContent = "selected";
      });
    });
    await browser.click("#simple .seList__listItem:first-of-type");
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "selected";
    });
    expect(true).to.be.true;
  });
  it(`check event : se-list-filter-input`, async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("se-list-filter-input", event => {
          document.getElementById("result--after").textContent =
            event.detail[0].filterInput;
          event.detail[0].filterInput = "this is a test";
        });
    });
    await browser.setValue("#simple .seList__search__keyword", "c");
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "c";
    });
    expect(
      await browser.getValue("#simple .seList__search__keyword")
    ).to.be.string("this is a test", "Prevent event successful");
  });
  it(`check event : *-se-list-page-change`, async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-page-change", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
        });
    });
    await browser.click("#simple .k-i-arrow-60-right");
    await browser.waitUntil(() => {
      return browser.getText("#result--before") === "beforeChangePage";
    });
    expect(true).to.be.true;
    //Prevent change page
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-page-change", event => {
          document.getElementById("result--after").textContent =
            "beforeChangePage";
          event.preventDefault();
        });
    });
    await browser.click("#simple .k-i-arrow-60-right");
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "beforeChangePage";
    });
    expect(
      await browser.getValue("#simple .k-pager-input .k-textbox")
    ).to.be.equal("1");
    //Change page value
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-page-change", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
          event.detail[0].newPage = 3;
        });
    });
    //Register after (for the check)
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("after-se-list-page-change", () => {
          document.getElementById("result--after").textContent =
            "afterPageChange";
        });
    });
    await browser.click("#simple .k-i-arrow-60-right");
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "afterPageChange";
    });
    expect(
      await browser.getValue("#simple .k-pager-input .k-textbox")
    ).to.be.equal("3");
  });
  it(`check event : *-se-list-pagesize-change`, async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-pagesize-change", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
        });
    });
    let activeDescendant = await browser.getAttribute(
      "#simple .k-widget.seList__list__pagerCounter",
      "aria-activedescendant"
    );
    await browser.click("#simple .seList__list__pagerCounter");
    //use id[] notation because id not work
    await browser.waitUntil(() => {
      return browser.isVisible(`li[id="${activeDescendant}"]`);
    });
    await browser.click(`li[id="${activeDescendant}"] + li`);
    await browser.waitUntil(() => {
      return browser.getText("#result--before") === "beforeChangePage";
    });
    expect(true).to.be.true;
    //Prevent
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-pagesize-change", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
          event.preventDefault();
        });
    });
    activeDescendant = await browser.getAttribute(
      "#simple .k-widget.seList__list__pagerCounter",
      "aria-activedescendant"
    );
    await browser.click("#simple .seList__list__pagerCounter");
    await browser.waitUntil(() => {
      return browser.isVisible(`li[id="${activeDescendant}"]`);
    });
    await browser.click(`li[id="${activeDescendant}"] + li`);
    await browser.waitUntil(() => {
      return browser.getText("#result--before") === "beforeChangePage";
    });
    expect(
      await browser.getValue("#simple input.seList__list__pagerCounter")
    ).to.be.equal("25");
    //Change value
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-pagesize-change", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
          event.detail[0].newPageSize = 66;
        });
    });
    //Register after (for the check)
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("after-se-list-pagesize-change", () => {
          document.getElementById("result--after").textContent =
            "afterPageChange";
        });
    });
    activeDescendant = await browser.getAttribute(
      "#simple .k-widget.seList__list__pagerCounter",
      "aria-activedescendant"
    );
    await browser.click("#simple .seList__list__pagerCounter");
    await browser.waitUntil(() => {
      return browser.isVisible(`li[id="${activeDescendant}"]`);
    });
    await browser.click(`li[id="${activeDescendant}"] + li`);
    await browser.waitUntil(() => {
      return browser.getText("#result--after") === "afterPageChange";
    });
    expect(
      (await browser.isVisible("#simple .seList__list .seList__listItem"))
        .length
    ).to.be.equal(66);
  });
  it(`check event : *-se-list-request`, async () => {
    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-request", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
        });
    });
    //Register after (for the check)
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("after-se-list-request", () => {
          document.getElementById("result--after").textContent =
            "afterPageChange";
        });
    });
    //Trigger event
    await browser.execute(() => {
      document.getElementById("simple").publicMethods.refreshList();
    });
    //Wait for event
    await browser.waitUntil(() => {
      return (
        browser.getText("#result--after") === "afterPageChange" &&
        browser.getText("#result--before") === "beforeChangePage"
      );
    });

    await browser.url("/autotest/ankselist");
    await waitForComponentToBeLoaded();
    //Register handler on selected event
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("before-se-list-request", event => {
          document.getElementById("result--before").textContent =
            "beforeChangePage";
        });
    });
    //Register after (for the check)
    await browser.execute(() => {
      document
        .getElementById("simple")
        .addEventListener("after-se-list-request", () => {
          document.getElementById("result--after").textContent =
            "afterPageChange";
        });
    });
    //Trigger event
    await browser.execute(() => {
      document.getElementById("simple").publicMethods.filterList("sdklmqmqkdqmlskdlmqsdkqmlsksdmlqskdml");
    });
    //Wait for event
    await browser.waitUntil(() => {
      return (
        browser.getText("#result--after") === "afterPageChange" &&
        browser.getText("#result--before") === "beforeChangePage"
      );
    });
    expect(
      (await browser.isVisible("#simple .seList__list .seList__listItem"))
    ).to.be.equal(false);

  });
});
