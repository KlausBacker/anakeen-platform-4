import { createElement } from "react";
import yourTheme from "./anakeenTheme";
import { addons, types } from "@storybook/addons";
import { AddonPanel } from "@storybook/components";
import { useParameter } from "@storybook/api";
import StoryTests from "../src/addons/AnkTestsManager/AnkTestsManager.react";

addons.setConfig({
  isFullscreen: false,
  showNav: true,
  showPanel: true,
  panelPosition: "bottom",
  sidebarAnimations: true,
  enableShortcuts: true,
  isToolshown: true,
  theme: yourTheme,
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
    title: "Anakeen Tests",
    render: ({ active, key }) => {
      const results = useParameter("AnkTests", []); // story's parameter being retrieved here
      return createElement(
        AddonPanel,
        { id: "root", active, key },
        createElement(StoryTests, { tests: results })
      );
    }
  });
});
