<?php

function main(Action $action) {

    $action->lay->set("WS", $action->getParam(("WVERSION")));
}