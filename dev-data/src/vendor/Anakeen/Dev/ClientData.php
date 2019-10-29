<?php

namespace Anakeen\Dev;

use SmartStructure\Fields\Devclient as DevClientFields;

class ClientData extends PersonData
{
    public function __invoke(int $number)
    {
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devclient::familyName)));
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devbill::familyName)));

        $data = [];

        for ($i = 0; $i < $number; $i++) {
            $firstName = \Anakeen\Dev\Data::getRandomFirstName();
            $lastName = \Anakeen\Dev\Data::getRandomLastName();
            $data[] = [
                DevclientFields::client_firstname => $firstName,
                DevclientFields::client_lastname => $lastName,
                DevclientFields::client_email => \Anakeen\Core\Utils\Strings::unaccent(sprintf("%s.%s@example.net", mb_strtolower($firstName), mb_strtolower($lastName))),
                DevclientFields::client_location => \Anakeen\Dev\Data::getRandomTown(),
                DevclientFields::client_society => \Anakeen\Dev\Data::getRandomSociety()
            ];
        }

        self::storeData(\SmartStructure\Devclient::familyName, $data);
    }
}
