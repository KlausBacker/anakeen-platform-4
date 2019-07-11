<?php

namespace Anakeen\Dev;

use Anakeen\Search\Internal\SearchSmartData;
use SmartStructure\Fields\Devbill as DevbillFields;

class BillData
{
    public function __invoke($billMin=0, $billMax=3)
    {
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devbill::familyName)));

        $s = new SearchSmartData("", \SmartStructure\Devperson::familyName);
        $s->returnsOnly(["initid"]);
        $allPersons = $s->search();
        $count = count($allPersons);
        $c = 1;
        $allPersons = array_values($allPersons);

        $cbill=1;
        $s = new SearchSmartData("", \SmartStructure\Devclient::familyName);
        $s->returnsOnly(["initid"]);
        $allClients = $s->search();
        $countClients = count($allClients);
        $allClients = array_values($allClients);

        $bc=1;
        foreach ($allPersons as $person) {
            $max = rand($billMin, $billMax);
            for ($i = 0; $i < $max; $i++) {
                $bill = \Anakeen\Core\SEManager::createDocument(\SmartStructure\Devbill::familyName);
                $bill->setValue(DevbillFields::bill_author, $person["initid"]);
                $bill->setValue(DevbillFields::bill_title, sprintf("Bill %04d", $bc++));
                $bill->setValue(DevbillFields::bill_content, sprintf("Content for the bill number %04d\nWrited by \"%s\".", $c, \DocTitle::getTitle($person["initid"])));
                $bill->setValue(DevbillFields::bill_location, \Anakeen\Dev\Data::getRandomTown());

                $bill->setValue(DevbillFields::bill_cost, rand(30, 2000));

                // Attach to client
                $maxco = rand(1, 3);
                if ($maxco) {
                    $clients=[];
                    for ($coidx = 0; $coidx < $maxco; $coidx++) {
                        $clients[] = $allClients[rand(0, $countClients-1)]["initid"];
                    }
                    $bill->setValue(DevbillFields::bill_clients, $clients);
                }

                $maxco = rand(0, 3);
                if ($maxco) {
                    $otherClients = $otherSocities = [];
                    for ($coidx = 0; $coidx < $maxco; $coidx++) {
                        $otherClients[] = \Anakeen\Dev\Data::getRandomName();
                        $otherSocities[] = \Anakeen\Dev\Data::getRandomSociety();
                    }
                    $bill->setValue(DevbillFields::bill_clientname, $otherClients);
                    $bill->setValue(DevbillFields::bill_society,  $otherSocities);
                }
                $bill->store();
                printf("\r%05d/%05d - %05d", $c, $count, $cbill++);
            }
            $c++;
        }
        print "\n";
    }
}