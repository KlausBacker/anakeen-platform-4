import { addons } from '@storybook/addons';
import yourTheme from './anakeenTheme';


addons.setConfig({
  isFullscreen: false,
  showNav: true,
  showPanel: true,
  panelPosition: 'bottom',
  sidebarAnimations: true,
  enableShortcuts: true,
  isToolshown: true,
  theme: yourTheme,
  selectedPanel: 'readme',
  initialActive: 'sidebar',
  showRoots: false,
});

