import { createElement } from "react";
import darkAnakeenTheme from "./anakeenTheme";
import { addons, types } from "@storybook/addons";
import { AddonPanel } from "@storybook/components";
import { useParameter, useStorybookState } from "@storybook/api";
import { STORY_RENDERED } from "@storybook/core-events";
import StoryTests from "../src/addons/AnkTestsManager/AnkTestsManager.react";

addons.setConfig({
  isFullscreen: false,
  showNav: true,
  showPanel: true,
  panelPosition: "bottom",
  sidebarAnimations: true,
  enableShortcuts: true,
  isToolshown: true,
  theme: darkAnakeenTheme,
  selectedPanel: "readme",
  initialActive: "sidebar",
  showRoots: false
});

const ADDON_ID = "AnkTests";
const PANEL_ID = `${ADDON_ID}/panel`;

// Add anakeen test addon
addons.register(ADDON_ID, api => {
  addons.add(PANEL_ID, {
    type: types.PANEL,
    title: () => {
      const results = useParameter("automaticTests", []);
      const userTests = useParameter("userTests", []);
      const testCount = results.length + userTests.length;
      const suffix = testCount === 0 ? "" : " (".concat(testCount, ")");
      return "Anakeen Tests".concat(suffix);
    },
    render: ({ active, key }) => {
      const autoTests = useParameter("automaticTests", []);
      const mdReadme = useParameter("anakeenReadme", "");
      const userTests = useParameter("userTests", []);

      const channel = api.getChannel();
      const story = useStorybookState();

      api.on(STORY_RENDERED, () => {
        channel.emit("displayTestResults");
      });

      return createElement(
        AddonPanel,
        { id: "ank-tests-panel-addon", className: "ank-tests-panel", active, key },
        createElement(StoryTests, {
          autoTests: autoTests,
          userTests: userTests,
          readme: mdReadme,
          channel: channel,
          story: story
        })
      );
    }
  });
});
