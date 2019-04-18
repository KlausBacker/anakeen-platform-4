<?php

namespace Anakeen\Ui;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\SmartStructure\FieldAccessManager;
use Dcp\Ui\Exception;
use SmartStructure\Fields\Cvdoc as CvdocFields;

class MaskManager
{

    const HiddenVisibility = "H";
    const ReadOnlyVisibility = "R";
    const ReadWriteVisibility = "W";
    const WriteOnlyVisibility = "O";
    const ArrayStaticVisibility = "U";
    const StaticWriteVisibility = "S";

    /**
     * @var SmartElement $smartElement
     */
    protected $smartElement = null;
    protected $mVisibilities = [];

    public function __construct(SmartElement $smartElement = null)
    {
        $this->smartElement = $smartElement;
    }

    /**
     * set visibility mask
     * Apply primary mask first if is set in view control of element
     * @param int $mid Mask identifier
     *
     * @return void
     */
    public function setUiMask($mid = 0)
    {
        $this->mVisibilities = [];
        $this->applyMask($mid);
    }

    /**
     * Apply a mask over current visibilities
     * @param string|int $mid Mask identifier
     * @throws Exception
     */
    public function addUiMask($mid)
    {
        if (!$this->mVisibilities) {
            $this->initVisibilities();
        }
        $this->overrideMask($mid);
    }

    /**
     * @param SmartElement $smartElement
     * @return MaskManager
     */
    public function setSmartElement(SmartElement $smartElement)
    {
        $this->smartElement = $smartElement;
        return $this;
    }

    /**
     * Return the primary mask refrerenced in associated primary control
     * @return int|string the primary mask id (0 if not found)
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function getPrimaryMask()
    {
        if ($this->smartElement->cvid) {
            $cvdoc = SEManager::getDocument($this->smartElement->cvid);
            $primaryMask = $cvdoc->getRawValue(CvdocFields::cv_primarymask);
            if ($primaryMask) {
                return $primaryMask;
            }
        }
        return 0;
    }

    /**
     * Init visibilities with access field control
     * @throws Exception
     */
    protected function initVisibilities()
    {
        $oas = $this->smartElement->getAttributes();
        if (is_array($oas)) {
            foreach ($oas as $k => $v) {
                if ($oas[$k]) {
                    $this->mVisibilities[$v->id] = self::propagateVisibility(
                        $this->getDefaultVisibility($v),
                        (empty($v->fieldSet->id)) ? '' : $this->mVisibilities[$v->fieldSet->id],
                        (!empty($v->fieldSet->fieldSet->id)) ? $this->mVisibilities[$v->fieldSet->fieldSet->id] : ''
                    );
                }
            }
        }
    }

    /**
     * apply visibility mask
     *
     * @param int  $mid   mask ident, if not set it is found from possible workflow
     * @param bool $force set to true to force reapply mask even it is already applied
     *
     * @return void
     */
    protected function applyMask($mid = 0, $force = false)
    {
        // copy default visibilities
        $err = '';
        $this->initVisibilities();

        $argMid = $mid;
        if ((!$force) && (($this->smartElement->doctype == 'C') || (($this->smartElement->doctype == 'T') && ($mid == 0)))) {
            return;
        }
        // modify visibilities if needed
        if ((!is_numeric($mid)) && ($mid != "")) {
            $imid = SEManager::getIdFromName($mid);
            if (!$imid) {
                $err = \ErrorCode::getError('DOC1004', $argMid, $this->smartElement->getTitle());
                throw new Exception($err);
            } else {
                $mid = $imid;
            }
        }

        $cvdoc = null;
        if ($mid == \Anakeen\Core\Internal\SmartElement::USEMASKCVVIEW || $mid == \Anakeen\Core\Internal\SmartElement::USEMASKCVEDIT) {
            if ($this->smartElement->cvid) {
                /**
                 * @var \SmartStructure\CVDoc $cvdoc
                 */
                $cvdoc = SEManager::getDocument($this->smartElement->cvid);
                SEManager::cache()->addDocument($cvdoc);
                if ($cvdoc && $cvdoc->isAlive()) {
                    $cvdoc = clone $cvdoc;
                    $cvdoc->Set($this->smartElement);
                    $vid = self::getDefaultView($this->smartElement, ($mid == \Anakeen\Core\Internal\SmartElement::USEMASKCVEDIT), "id");
                    if ($vid != '') {
                        $tview = $cvdoc->getView($vid);
                        $mid = ($tview !== false) ? $tview["CV_MSKID"] : 0;
                    }
                }
            } else {
                $mid = 0;
            }
        }
        if ($mid == 0) {
            if (($this->smartElement->wid > 0) && ($this->smartElement->wid != $this->smartElement->id)) {
                // search mask from workflow

                /**
                 * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
                 */
                $wdoc = SEManager::getDocument($this->smartElement->wid);
                if ($wdoc && $wdoc->isAlive()) {
                    if ($this->smartElement->id == 0) {
                        $wdoc->set($this->smartElement);
                    }
                    $mid = $wdoc->getStateMask($this->smartElement->state);
                    if ((!is_numeric($mid)) && ($mid != "")) {
                        $mid = SEManager::getIdFromName($mid);
                    }
                }
            }
        }

        $primaryMaskId = $this->getPrimaryMask();
        // Use primary mask if first if is defined
        if ($primaryMaskId) {
            $this->overrideMask($primaryMaskId);
        }
        if ($mid) {
            $this->overrideMask($mid);
        }
        if (!empty($this->smartElement->attributes->attr)) {
            $this->smartElement->attributes->orderAttributes();
        }

        if ($err) {
            throw new Exception($err);
        }
    }

    protected function overrideMask($mid)
    {
        /**
         * @var \SmartStructure\MASK $mdoc
         */
        $mdoc = SEManager::getDocument($mid);
        $err = "";
        if ($mdoc && $mdoc->isAlive()) {
            if (is_a($mdoc, \SmartStructure\Mask::class)) {
                $oas = $this->smartElement->getAttributes();
                $maskFam = $mdoc->getRawValue("msk_famid");
                if (!in_array($maskFam, $this->smartElement->getFromDoc())) {
                    $err = \ErrorCode::getError(
                        'DOC1002',
                        $mdoc->name ?? $mdoc->id,
                        $this->smartElement->getTitle(),
                        SEManager::getNameFromId($maskFam)
                    );
                } else {
                    $tvis = $mdoc->getVisibilities();
                    foreach ($tvis as $k => $v) {
                        if (isset($oas[$k])) {
                            if ($v != "-") {
                                $this->mVisibilities[$oas[$k]->id] = $v;
                            }
                        }
                    }
                    $tdiff = array_diff(array_keys($oas), array_keys($tvis));
                    // compute frame before because has no order
                    foreach ($tdiff as $k) {
                        $v = $oas[$k];
                        if ($v->type === "frame") {
                            $fid = $oas[$k]->id;

                            $this->mVisibilities[$fid] = self::propagateVisibility(
                                $this->mVisibilities[$fid] ?: $this->getDefaultVisibility($v),
                                isset($v->fieldSet) ? $this->mVisibilities[$v->fieldSet->id] : '',
                                ''
                            );
                        }
                    }
                    foreach ($tdiff as $k) {
                        $v = $oas[$k];
                        if ($v->type === "array") {
                            $fid = $oas[$k]->id;

                            $this->mVisibilities[$oas[$k]->id] = self::propagateVisibility(
                                $this->mVisibilities[$fid] ?: $this->getDefaultVisibility($v),
                                isset($v->fieldSet) ? $this->mVisibilities[$v->fieldSet->id] : '',
                                isset($v->fieldSet->fieldSet) ? $this->mVisibilities[$v->fieldSet->fieldSet->id] : ''
                            );
                        }
                    }
                    // recompute loosed attributes
                    foreach ($tdiff as $k) {
                        $v = $oas[$k];
                        if ($v->type !== "frame") {
                            $fid = $oas[$k]->id;
                            $this->mVisibilities[$oas[$k]->id] = self::propagateVisibility(
                                $this->mVisibilities[$fid] ?: $this->getDefaultVisibility($v),
                                isset($v->fieldSet) ? $this->mVisibilities[$v->fieldSet->id] : '',
                                isset($v->fieldSet->fieldSet) ? $this->mVisibilities[$v->fieldSet->fieldSet->id] : ''
                            );
                        }
                    }

                    // modify needed attribute also
                    $tneed = $mdoc->getNeedeeds();
                    foreach ($tneed as $k => $v) {
                        if (isset($oas[$k])) {
                            if ($v === "Y") {
                                $oas[$k]->needed = true;
                            } elseif ($v === "N") {
                                $oas[$k]->needed = false;
                            }
                        }
                    }
                }
            } else {
                $err = \ErrorCode::getError('DOC1001', $mdoc->name ?? $mdoc->id, $mdoc->fromname, $this->smartElement->getTitle());
            }
        } else {
            $err = \ErrorCode::getError('DOC1000', $mid, $this->smartElement->getTitle());
        }
        if ($err) {
            throw new Exception($err);
        }
    }

    /**
     * Get default visibility from access
     * @param BasicAttribute $v
     * @return string
     * @throws Exception
     */
    public function getDefaultVisibility(BasicAttribute $v)
    {
        switch (FieldAccessManager::getAccess($this->smartElement, $v)) {
            case BasicAttribute::READ_ACCESS:
                return self::ReadOnlyVisibility;
            case BasicAttribute::WRITE_ACCESS:
                return self::WriteOnlyVisibility;
            case BasicAttribute::READWRITE_ACCESS:
                return self::ReadWriteVisibility;
            case BasicAttribute::NONE_ACCESS:
                return self::HiddenVisibility;
            default:
                throw new Exception(sprintf("Wrong attribute access \"%s\" for\"%s\"", $v->access, $v->id));
        }
    }


    public static function propagateVisibility($vis, $fvis, $ffvis = '')
    {
        if ($fvis === "H") {
            return $fvis;
        }
        if (($fvis === "R") && (($vis === "W") || ($vis === "U") || ($vis === "S"))) {
            return $fvis;
        }
        if (($fvis === "R") && ($vis === "O")) {
            return "H";
        }
        if (($fvis === "O") && ($vis === "W")) {
            return $fvis;
        }
        if (($fvis === "S") && (($vis === "W") || ($vis === "O"))) {
            return $fvis;
        }
        if ($fvis == 'U') {
            if ($ffvis && ($vis === 'W' || $vis === 'O' || $vis === 'S')) {
                if ($ffvis === 'S') {
                    return 'S';
                }
                if ($ffvis === 'R') {
                    return 'R';
                }
            }
        }

        return $vis;
    }

    public function getVisibility($fieldId)
    {
        $fieldId = strtolower($fieldId);
        if (count($this->mVisibilities) === 0) {
            $this->applyMask();
        }

        if (isset($this->mVisibilities[$fieldId])) {
            return $this->mVisibilities[$fieldId];
        }
        return null;
    }

    public function getVisibilities()
    {
        if (count($this->mVisibilities) === 0) {
            $this->applyMask();
        }
        return $this->mVisibilities;
    }

    /**
     * retrieve first compatible view from default view control of a smart element
     *
     * @param SmartElement $doc     smart element
     * @param bool         $edition if true edition view else consultation view
     * @param string       $extract [id|mask|all]
     *
     * @return array|int view definition "cv_idview", "cv_mskid"
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public static function getDefaultView(SmartElement $doc, $edition = false, $extract = "all")
    {
        if ($doc->cvid > 0) {
            // special controlled view

            /**
             * @var \SmartStructure\CVDoc $cvdoc
             */
            $cvdoc = SEManager::getDocument($doc->cvid);
            $cvdoc = clone $cvdoc;
            $cvdoc->set($doc);

            $view = $cvdoc->getPrimaryView($edition);

            if ($view) {
                switch ($extract) {
                    case 'id':
                        return $view["cv_idview"];
                    case 'mask':
                        return $view["cv_mskid"];
                    default:
                        return $view;
                }
            }
        }
        return 0;
    }
    public static function getVisibilitiesConformToAccess(array $visibilities, SmartElement $elt)
    {
        foreach ($visibilities as $fieldId => &$visibility) {
            $oa = $elt->getAttribute($fieldId);
            if ($oa) {
                $visibility = self::getVisibilityConformToAccess($visibility, FieldAccessManager::getAccess($elt, $oa));
            }
        }

        return $visibilities;
    }

    protected static function getVisibilityConformToAccess($visibility, $access)
    {
        if ($access === BasicAttribute::NONE_ACCESS || ($access === BasicAttribute::WRITE_ACCESS && $visibility === "R")) {
            return "H";
        }

        if ($access === BasicAttribute::READ_ACCESS && ($visibility === "W" || $visibility === "O")) {
            return "S";
        }
        return $visibility;
    }
}
