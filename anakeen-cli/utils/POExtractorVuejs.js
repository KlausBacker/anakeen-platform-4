const VueI18NExtract = require("vue-i18n-extract").default;
const PO_LANGS = require("./appConst").po_langs;
const fs = require("fs");

exports.vueJSExtract = ({ globFile, targetName, info }) => {
  return Promise.all(
    PO_LANGS.map(lang => {
      return new Promise(resolve => {
        const localeDir = `${info.buildInfo.buildPath[0]}/locale/${lang}/vuejs/src/`;
        const baseLocale = `${localeDir}/${targetName}.json`;

        if (!fs.existsSync(localeDir)) {
          //dir exists
          fs.mkdirSync(localeDir, { recursive: true });
        }

        if (!fs.existsSync(baseLocale)) {
          fs.writeFileSync(baseLocale, "{}");
        }

        let recordedLocales = JSON.parse(fs.readFileSync(baseLocale));

        globFile.addGlob.forEach(pattern => {
          const report = VueI18NExtract.createI18NReport(pattern, baseLocale);
          report.missingKeys.forEach(function(miss) {
            const key = miss.path;
            if (recordedLocales[key] === undefined) {
              recordedLocales[key] = null;
            }
          });
        });

        fs.writeFileSync(baseLocale, JSON.stringify(recordedLocales, null, 2));

        resolve();
      });
    })
  );
};
