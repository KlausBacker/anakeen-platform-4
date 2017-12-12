/* loader.js */

if (process.env.NODE_ENV === "production") {
    window.CKEDITOR_BASEPATH = ($("head base").attr("href") || "")+'uiAssets/anakeen/prod/node_modules/ckeditor/';
} else {
    window.CKEDITOR_BASEPATH = ($("head base").attr("href") || "")+'uiAssets/anakeen/debug/node_modules/ckeditor/';
}

// Load your custom config.js file for CKEditor.
require(`!file-loader?context=${__dirname}&outputPath=node_modules/ckeditor/&name=[path][name].[ext]!./config.js`);

// Load your custom contents.css file in case you use iframe editor.
require(`!file-loader?context=${__dirname}&outputPath=node_modules/ckeditor/&name=[path][name].[ext]!./contents.css`);

// Load your custom styles.js file for CKEditor.
require(`!file-loader?context=${__dirname}&outputPath=node_modules/ckeditor/&name=[path][name].[ext]!./styles.js`);

// Load files from plugins.
require.context(
    `!file-loader?name=[path][name].[ext]!ckeditor/plugins/`,
    true,
    /^\.\/((a11yhelp|about|basicstyles|blockquote|button|clipboard|contextmenu|dialog|dialogui|elementspath|enterkey|entities|fakeobjects|filebrowser|floatingspace|floatpanel|format|horizontalrule|htmlwriter|image|indent|indentlist|link|list|listblock|magicline|maximize|menu|menubutton|notification|panel|pastefromword|pastetext|popup|removeformat|resize|richcombo|scayt|showborders|sourcearea|specialchar|stylescombo|tab|table|tableselection|tabletools|toolbar|undo|wsc|wysiwygarea)(\/(?!lang\/)[^/]+)*)?[^/]*$/
);

// Load lang files from plugins.
// Limit to active plugins with
// Object.keys(CKEDITOR.plugins.registered).sort().toString().replace(/,/g, '|')
require.context(
    '!file-loader?name=[path][name].[ext]!ckeditor/plugins/',
    true,
    /^\.\/(a11yhelp|about|basicstyles|blockquote|button|clipboard|contextmenu|dialog|dialogui|elementspath|enterkey|entities|fakeobjects|filebrowser|floatingspace|floatpanel|format|horizontalrule|htmlwriter|image|indent|indentlist|link|list|listblock|magicline|maximize|menu|menubutton|notification|panel|pastefromword|pastetext|popup|removeformat|resize|richcombo|scayt|showborders|sourcearea|specialchar|stylescombo|tab|table|tableselection|tabletools|toolbar|undo|wsc|wysiwygarea)\/(.*\/)*lang\/(en|fr)\.js$/
)

// Load CKEditor lang files.
require.context(
    `!file-loader?name=[path][name].[ext]!ckeditor/lang`,
    true,
    /(en|fr)\.js/
);

// Load skin.
require.context(
    `!file-loader?name=[path][name].[ext]!ckeditor/skins/moono-lisa`,
    true,
    /.*/
);

require("ckeditor");
require('ckeditor/adapters/jquery');