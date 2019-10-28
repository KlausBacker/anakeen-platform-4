<?php


namespace Anakeen\Dev;

use Anakeen\Core\AccountManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\Utils\Strings;
use Anakeen\Search\SearchElements;
use Anakeen\SmartElementManager;
use SmartStructure\Fields\Iuser as IUserFields;
use SmartStructure\Fields\Devperson as DevPersonFields;

class AccountsData
{

    public function __invoke(int $cAccounts)
    {
        if (!empty($cAccounts)) {
            $searchPerson = new SearchElements("DEVPERSON");
            $searchPerson->setSlice($cAccounts);
            $persons = $searchPerson->search();
            $count = 0;
            foreach ($persons->getResults() as $person) {
                $login = $this->getLogin($person);
                $account = AccountManager::getAccount($login);
                if (!empty($account)) {
                    $account->delete();
                }
                $account = SmartElementManager::createDocument("IUSER");
                $account->setValue(IUserFields::us_login, $this->getLogin($person));
                $mail = $person->getRawValue(DevPersonFields::dev_email);
                if (empty($mail)) {
                    $mail = $login."@example.foo";
                }
                $account->setValue(IUserFields::us_extmail, $mail);
                $account->setValue(IUserFields::us_fname, $person->getRawValue(DevPersonFields::dev_firstname));
                $account->setValue(IUserFields::us_lname, $person->getRawValue(DevPersonFields::dev_lastname));
                $account->store();
                printf("\r%05d/%05d", ++$count, $cAccounts);
            }
        }
    }

    protected static function getLogin(SmartElement $person)
    {
        $firstname = Strings::unaccent($person->getRawValue(DevPersonFields::dev_firstname));
        $lastname =  Strings::unaccent($person->getRawValue(DevPersonFields::dev_lastname));
        return mb_strtolower($firstname[0].$lastname);
    }
}
