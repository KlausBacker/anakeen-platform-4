import { addDecorator} from '@storybook/vue';
import { addReadme } from 'storybook-readme/vue';

import { themes } from '@storybook/theming';

// import '@storybook/addon-console';

addDecorator(addReadme);

export const parameters = {
  actions: { argTypesRegex: "^on[A-Z].*" },
  backgrounds: {
    default: 'light'
  },
  readme: {
    theme: {
      bodyColor: "white"
    }
  }
}

