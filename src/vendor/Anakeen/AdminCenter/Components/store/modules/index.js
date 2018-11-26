// const modulesFiles = require.context('./', true, /\.js$/);
// const resultObj = {};
//
// const rootDir = (path) => {
//     const regex = new RegExp('(?:\\.\\/)?([^\\/]+)\\/?.*');
//     const match = path.match(regex);
//     if (match.length > 1) {
//         return match[1];
//     }
//     return path;
// };
//
// const vuexModuleContent = ['actions', 'getters', 'mutations', 'state'];
//
// modulesFiles.keys().forEach(key => {
//     console.log(key);
//     if (key !== './index.js') {
//         const base = rootDir(key);
//         console.log(base);
//         resultObj[base] = {};
//         vuexModuleContent.forEach(c => {
//             console.log(`${base}/${c}`);
//             if (key.indexOf(`${base}/${c}`) > -1) {
//                 console.log('set', key);
//                 resultObj[base][c] = modulesFiles(key).default;
//             }
//         });
//     }
// });
//
// export default resultObj;

export default {
  application: {
    state: require("./application/state").default,
    getters: require("./application/getters").default,
    mutations: require("./application/mutations").default,
    actions: require("./application/actions").default
  },
  admin: {
    state: require("./admin/state").default,
    getters: require("./admin/getters").default,
    mutations: require("./admin/mutations").default,
    actions: require("./admin/actions").default
  }
};
