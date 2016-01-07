# Unit test with selenium

## Installation

### Dependencies

`npm install`

## Run

`./start.js`

## Config

The config of the current driver is in driver.json, you can use another conf file. For that, you should create a file :

driver.<env>.json

`java -Dwebdriver.chrome.driver=/home/eric/bin/chromedriver -jar ~/bin/selenium-server-standalone-2.48.2.jar`

For windows : 

    java -Dwebdriver.ie.driver=C:\Users\IEUser\Downloads\IEDriverServer.exe 
        -Dwebdriver.chrome.driver=C:\Users\IEUser\Downloads\chromedriver.exe 
        -jar C:\Users\IEUser\Downloads\selenium-server-standalone-2.48.2.jar



And launch after :

`NODE_ENV=chrome ./start.js **/enum_spec.js`


## Add a test

Create a file in the `test` dir, the file must end by : `spec.js`

