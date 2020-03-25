<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Anakeen\Components\Grid\Filters;

use Anakeen\Components\Grid\Exceptions\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Search\Filters\ElementSearchFilter;
use Anakeen\Search\Filters\StandardAttributeFilter;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

class StateLabel extends StandardAttributeFilter implements ElementSearchFilter
{
    protected $compatibleType = array(
        'text',
    );

    protected $compatibleProperties = [
        "state"
    ];
    const NOT = 1;
    const REGEXP = 2;
    const NOCASE = 4;
    const NODIACRITIC = 8;
    private $NOT = false;
    private $REGEXP = false;
    private $NOCASE = false;
    private $NODIACRITIC = false;
    protected $value = null;

    public function __construct($attrId, $value, $options = 0)
    {
        parent::__construct($attrId);
        $this->value = $value;
        if (isset($options)) {
            $this->NOT = ($options & self::NOT);
            $this->REGEXP = ($options & self::REGEXP);
            $this->NOCASE = ($options & self::NOCASE);
            $this->NODIACRITIC = ($options & self::NODIACRITIC);
        }
    }

    public function verifyCompatibility(\Anakeen\Search\Internal\SearchSmartData &$search)
    {
        $attr = parent::verifyCompatibility($search);
        if (!in_array($attr->id, $this->compatibleProperties)) {
            throw new Exception("GRID0016", $this->attributeId, $attr->type);
        }
        return $attr;
    }

    /**
     * @inheritDoc
     */
    public function addFilter(\Anakeen\Search\Internal\SearchSmartData $search)
    {
        $this->verifyCompatibility($search);
        // Get family to retrieve workflow
        $fam = $search->getFamily();
        if (!empty($fam) && !empty($fam->wid)) {
            /**
             * @var WDocHooks $wdoc
             */
            $wdoc = SEManager::getDocument($fam->wid);
            // List states that match the filter value
            $states = $this->getMatchedStates($this->value, $wdoc);
            if (!empty($states)) {
                // Filter the search
                $search->addFilter(sprintf("state IN (%s)", implode(array_map(function ($state) {
                    return pg_escape_literal($state);
                }, $states), ", ")));
            }
        }
        return $this;
    }

    protected function getMatchedStates($stateLabel, WDocHooks $wdoc)
    {
        $matchedStates = [];
        if (!empty($wdoc)) {
            // Walk over workflow states
            foreach ($wdoc->getStates() as $state) {
                $stateLabel = $wdoc->getStateLabel($state);
                $left = $stateLabel;
                $right = $this->value;
                if ($this->NOCASE) {
                    // no case sensitive
                    $left = strtolower($left);
                    $right = strtolower($right);
                }
                //  Compare state label and searched string
                if (strpos($left, $right) !== false) {
                    if (!$this->NOT) {
                        // Contains case
                        $matchedStates[] = $state;
                    }
                } else {
                    if ($this->NOT) {
                        // Not contain case
                        $matchedStates[] = $state;
                    }
                }
            }
        }
        return $matchedStates;
    }
}
