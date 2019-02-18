# Default theme for Anakeen Platform Ui

## Using theme
Use the NPM package which contains a built `dist/prod/all.css` CSS file for the theme.

 ```bash
 npm install @anakeen/anakeen-theme
 ```
 
 ```javascript
import "@anakeen/anakeen-theme";

// or

import "@anakeen/anakeen-theme/src/public/css/ank/theme/all.min.css";
```

## Customizing the Themes

### With NPM
To customize the Anakeen Sass-based theme, create a .scss file and consume the theme package in the following way:

Obtain the theme source through the NPM package.

```bash
npm install @anakeen/anakeen-theme
```

Create a .scss file that will consume the theme. 

To build the theme files, import them into the file.

```scss
@import "@anakeen/anakeen-theme/src/scss/gen/all";
```

To customize the variables that are used in the theme, change the theme before you import the theme files.

```scss
$ank-ui-color-client: #E82C0C; // brand color

@import "@anakeen/anakeen-theme/src/scss/gen/all";
```

Build your scss file through a Sass compiler. For example, use node-sass.

```bash
node-sass anakeen-theme-override.scss styles.css
```

Then import the `styles.css` file instead of the anakeen theme provided one.


### Cloning the repo
You can also clone the repo. 

```bash
git clone git@gitlab.anakeen.com:Anakeen/Platform-4/npm/anakeen-theme.git
```

Fill the [custom theme file](scss/_custom.scss) to overload the theme

```scss
// Theme overrides
//
// Copy variables from `variables/corporate.scss` to this file to override default values
// without modifying source files.

$ank-ui-color-client: #E82C0C; // brand color
```

Build the theme with the command :

```bash
yarn build
```

### Add a css to build