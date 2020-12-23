import { addons, types } from '@storybook/addons';
import { AddonPanel } from '@storybook/components';
import { useParameter } from '@storybook/api';
import StoryTests from "../src/addons/AnkTestsManager";

const ADDON_ID = 'AnkTests';
const PANEL_ID = `${ADDON_ID}/panel`;

// give a unique name for the panel
export const MyPanel = () => {
  const results = useParameter('AnkTests', []); // story's parameter being retrieved here
  // console.log("results => ");
  // console.log(results);
  return (
    <StoryTests tests={results}/>
  );
};

addons.register(ADDON_ID, (api) => {
  addons.add(PANEL_ID, {
    type: types.PANEL,
    title: 'Salut Adrien=)',
    render: ({ active, key }) => (
      <AddonPanel id='root' active={active} key={key}>
        <MyPanel />
      </AddonPanel>
    ),
  });
});