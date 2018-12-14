const gulp = require("gulp");
const urlJoin = require("url-join");
const fs = require("fs");
const signale = require("signale");
const opn = require("opn");
const sax = require("sax");

exports.openElement = ({ filePath, lineNumber, columnNumber, contextUrl }) => {
  return gulp.task("openElement", async () => {
    const a = fs.readFileSync(filePath).toString();
    const rules = JSON.parse(
      fs.readFileSync(__dirname + "/rules/openElementRules.json")
    );
    parseXML(a, lineNumber, columnNumber, contextUrl, rules);
  });
};

function parseXML(data, lineNumber, columnNumber, contextUrl, rules) {
  const d = data.toString("utf8");
  let parser = sax.parser(true, { trim: true, position: true, xmlns: true });
  parser.onerror = function(e) {
    signale.error("XML error: ", e.toString());
    return {};
  };
  let tagTab = [];
  let tagName = "";
  let url = "";

  parser.onopentag = function(node) {
    tagTab.push(node);
    if (node.name.split(":")[1] in rules.rules) {
      tagName = node.attributes.name.value;
      url = rules.rules[node.name.split(":")[1]].url;
      url = url.replace(/(<identifier>)+/g, tagName);
    }
    if (parser.line === lineNumber) {
      opn(`${urlJoin(contextUrl, url)}`);
      signale.success(`Element ${tagName} has been open in your browser`);
    }
  };
  parser.onclosetag = function() {
    tagTab.pop();
    if (parser.line === lineNumber) {
      opn(`${urlJoin(contextUrl, url)}`);
      signale.success(`Element ${tagName} has been open in your browser`);
    }
  };
  return parser.write(d).end();
}
