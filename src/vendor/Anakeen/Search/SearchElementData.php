<?php


namespace Anakeen\Search;


class SearchElementData extends SearchElements
{
    protected $data = [];

    public function __construct($familyName = 0)
    {
        parent::__construct($familyName);
        $this->searchDoc->setObjectReturn(false);
    }

    public function search()
    {
        $this->data = $this->searchDoc->search();
        return $this;
    }

    /**
     * Return raw data of elements directly from database
     * @return array
     */
    public function getResults()
    {
        return $this->data;
    }

    public static function getRawData($data, $fieldid)
    {
        $rawValue = $data[$fieldid] ?? null;
        if ($rawValue !== null) {
            return $rawValue;
        }

        if (empty($data["fieldvalues"]) === false) {
            $values = json_decode($data["fieldvalues"], true);
            return $values[$fieldid] ?? null;
        }
        return null;
    }
}