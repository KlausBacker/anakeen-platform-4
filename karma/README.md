# Karma launcher for dynacase-document-uis

This directory contains conf for karma with dynacase document-uis

## Install deps

* Install nodejs v0.10.30
* Use `npm install`

## Run test

You can run test with the command :

`./node_modules/karma/bin/karma start`

launched is the directory karma

## Configuration

The configuration is in the file `karma.conf.js` and in the `test-main.js`.

You should modify the proxy options to indicate where is the dynacase URL (root url).

## Links

See [karma](https://karma-runner.github.io/0.12/index.html)

## List of the browser

You can have the list of the browserstack browser with the following command :

`curl -u "my_user:my_api_password" https://www.browserstack.com/automate/browsers.json`