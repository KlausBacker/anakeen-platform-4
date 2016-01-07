# Unit test with selenium

## Installation

### Dependencies

`npm install`

    npm install selenium-webdriver
    npm install config.json

### Jasmine-node

`npm install jasmine-node -g` (as root)

## Run

`jasmine-node test`

## Config

The config of the current driver is in driver.json, you can use another conf file. For that, you should create a file :

driver.<env>.json

`java -Dwebdriver.chrome.driver=/home/eric/bin/chromedriver -jar ~/bin/selenium-server-standalone-2.48.2.jar`

For windows : 

    java -Dwebdriver.ie.driver=C:\Users\IEUser\Downloads\IEDriverServer.exe 
        -Dwebdriver.chrome.driver=C:\Users\IEUser\Downloads\chromedriver.exe 
        -jar C:\Users\IEUser\Downloads\selenium-server-standalone-2.48.2.jar



And launch after :

`jasmine-node --config NODE_ENV <env> test`

For chrome :

`jasmine-node --config NODE_ENV chrome test`

#### Junit

To have junit report add `--junitreport`

## Add a test

Create a file in the `test` dir, the file must end by : `spec.js`

