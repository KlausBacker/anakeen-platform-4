const fs = require('fs');
const path = require('path');
let exportModule = {};
fs.readdirSync(__dirname).forEach((file) => {
    if (file !== 'index.js') {
        Object.assign(exports, require(path.resolve(__dirname, file)));
    }
});