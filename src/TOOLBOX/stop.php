<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display a message to advert that Dynacase Platform being to be upgraded
 */
header("HTTP/1.0 503 Service Unavailable");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Anakeen Platform Maintenance</title>
    <meta charset='utf-8'/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <style type="text/css">

        html {
            padding:0;
            margin:0;
            height: 100vh;
            font-size:13px;
        }
        body {
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            background-color:#f5f5f5;

            padding: 3rem 0;
            margin:0;
            min-height: 100vh;

            display:flex;
            flex-direction: column;
        }
        body > div{
            text-align: center;
        }
        .bottom {
            flex-grow:1;
            padding-top: 1rem;
            text-align: center;
            width:100%;
        }


        .product {
            color: #9ecd63;
            font-style: italic;
            font-size: 2rem;
        }
        .summary {
            font-size: 1.1rem;
            font-style: italic;
            color: #484848;
        }
        .description {
            font-size: 1.3rem;
            color: #b5b5b5;
            font-weight: bold;
        }
        .maintenance {
            flex-grow:0;
            font-size: 2rem;
            font-weight: bold;
            background-color:#e9f394;
        }
         p {
            margin: 0.8rem 0;
        }
        .logo img {
            height: 5rem;
        }
        .info {
            font-size:1.4rem;
            line-height: 1rem;
        }
        hr {
            width:4rem;
        }
    </style>
</head>

<body>


    <div>
        <img src="CORE/Images/maintenance-gearing.png"/>
    </div>
    <div class="maintenance">
        <p>UNDER MAINTENANCE</p>
    </div>
    <div class="info">
        <p>The system is currently unavailable due to maintenance works.</p>
        <p>Please come back later.</p>
    </div>
    <hr/>
    <div class="bottom">
        <p class="product">ANAKEEN PLATFORM</p>

        <p class="summary">LOW-CODE PLATFORM FOR HTML5 BUSINESS APPS</p>

        <p class="description">Build process-based applications<br/> to solve everyday business challenges</p>

    </div>
    <div class="logo">
        <img class="logo" src="CORE/Images/anakeen-logo.svg"/>
    </div>


</body>
</html>
