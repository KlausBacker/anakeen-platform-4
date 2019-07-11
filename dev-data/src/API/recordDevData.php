<?php

use SmartStructure\Fields\Devperson as DevPersonFields;

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Record Many Data");
$cPerson = $usage->addOptionalParameter("person", "number of person to create");
$cClient = $usage->addOptionalParameter("client", "number of client to create");
$cAccounts = $usage->addOptionalParameter("accounts", "number of accounts to create", null, "0");
$note = $usage->addOptionalParameter("note", "MIN-MAX  note associated to person", null, "0-3");
$bill = $usage->addOptionalParameter("bill", "MIN-MAX  bill associated to person and clients", null, "0-3");
$usage->verify();

if ($cPerson) {
    $persn = new \Anakeen\Dev\PersonData();
    $persn($cPerson);
}

if ($cClient) {
    $client = new \Anakeen\Dev\ClientData();
    $client($cClient);
}

if ($cAccounts) {
    $accounts = new \Anakeen\Dev\AccountsData();
    $accounts($cAccounts);
}

if ($note) {
    list($min, $max)=explode("-", $note);
    $notes = new \Anakeen\Dev\NoteData();
    $notes($min, $max);
}
if ($bill) {
    list($min, $max)=explode("-", $bill);
    $bills = new \Anakeen\Dev\BillData();
    $bills($min, $max);
}