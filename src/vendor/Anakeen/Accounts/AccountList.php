<?php

namespace Anakeen\Accounts;

class AccountList implements \Iterator, \Countable
{
    /**
     * @var null|\Anakeen\Accounts\SearchAccounts
     */
    private $accountsData = null;
    /**
     * @var null|\Anakeen\Core\Account
     */
    private $currentAccount = null;
    private $currentIndex = 0;

    public $length = 0;
    
    public function __construct(array $data)
    {
        $this->accountsData = $data;
        $this->length = count($this->accountsData);
        $this->initSearch();
    }
    /**
     * get number of returned documents
     * can be upper of real length due to callback map
     * @return int
     */
    public function count()
    {
        return $this->length;
    }
    private function initSearch()
    {
        $this->currentIndex = 0;
        $this->currentAccount = new \Anakeen\Core\Account();
    }
    
    private function getCurrentAccount()
    {
        if (empty($this->accountsData[$this->currentIndex])) {
            return null;
        }
        $this->currentAccount->affect($this->accountsData[$this->currentIndex]);
        return $this->currentAccount;
    }
    
    public function rewind()
    {
        $this->initSearch();
    }
    /**
     * @return void
     */
    public function next()
    {
        $this->currentIndex++;
    }
    
    public function key()
    {
        return $this->currentAccount->id;
    }
    /**
     * @return \Anakeen\Core\Account|null
     */
    public function current()
    {
        return $this->getCurrentAccount();
    }
    /**
     * @return bool
     */
    public function valid()
    {
        return (!empty($this->accountsData[$this->currentIndex]));
    }
}
