var fs = require('fs');
var fusionConf = require("./karma.default.conf");


var extend = function extend(target)
{
    "use strict";
    var sources = [].slice.call(arguments, 1);
    sources.forEach(function (source)
    {
        for (var prop in source) {
            if (target[prop] === undefined) {
                target[prop] = source[prop];
            }
        }
    });
    return target;
};

if (fs.existsSync('karma.specific.conf.json')) {
    var customConf = JSON.parse(fs.readFileSync('karma.specific.conf.json', 'utf8'));
    fusionConf = extend(customConf, fusionConf);
}

module.exports = function (config) {
    "use strict";
    config.set(fusionConf);
};
