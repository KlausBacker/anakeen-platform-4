<?php

namespace Anakeen\Dev;

use SmartStructure\Fields\Devperson as DevPersonFields;

class PersonData {

    public function __invoke(int $number)
    {
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devperson::familyName)));
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devbill::familyName)));
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devnote::familyName)));

        $data = [];

        for ($i = 0; $i < $number; $i++ ) {
            $firstName = \Anakeen\Dev\Data::getRandomFirstName();
            $lastName = \Anakeen\Dev\Data::getRandomLastName();
            $data[] = [
                DevPersonFields::dev_firstname => $firstName,
                DevPersonFields::dev_lastname => $lastName,
                DevPersonFields::dev_email => \Anakeen\Core\Utils\Strings::unaccent(sprintf("%s.%s@example.net", mb_strtolower($firstName), mb_strtolower($lastName))),
                DevPersonFields::dev_birthdate => \Anakeen\Dev\Data::getRandomDate(),
            ];
        }

        self::storeData(\SmartStructure\Devperson::familyName, $data);

    }

    protected function storeData(string $structName, array $data) {
        $c = 1;
        $count = count($data);
        foreach ($data as $datum) {
            $se = \Anakeen\Core\SEManager::createDocument($structName);
            foreach ($datum as $aid => $value) {
                $se->setValue($aid, $value);
            }
            $se->store();
            printf("\r%05d/%05d", $c++, $count);
        }
        print "\n";
}
}