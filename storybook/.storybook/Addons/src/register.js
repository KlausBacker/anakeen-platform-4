// /my-addon/src/register.js

import React, { Fragment } from 'react';
import { addons, types } from '@storybook/addons';
import { AddonPanel } from '@storybook/components';
import { useParameter } from '@storybook/api';
import Checkbox from './Checkbox';
import ReactDOM from 'react-dom';
import StoryTests from "./AnkTestsManager"

// const Content = () => {
//   const results = useParameter('AnkTests', []); // story's parameter being retrieved here
//   return (
//     <Fragment>
//       {results.length ? (
//         <ol>
//           <Checkbox
//               label="Check all tests"
//               // handleCheckboxChange={selectAll}
//               key="AllTests"
//           />
//           {results.map(i => (
//               <Checkbox
//                 label={i}
//                 isSelected={true}
//                   // handleCheckboxChange={this.toggleCheckbox}
//                 key={i}
//             />
//           ))}
//         </ol>
//       ) : null}
//     </Fragment>
//   );
// };


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
    title: 'Ank-test',
    render: ({ active, key }) => (
      <AddonPanel id='root' active={active} key={key}>
        <MyPanel />
      </AddonPanel>
    ),
  });
});