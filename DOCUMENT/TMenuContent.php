<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

namespace Dcp\Ui;
/**
 * Class TMenuContent
 * @package Dcp\Ui
 */
trait TMenuContent
{
    /**
     * @var ElementMenu[] Element menu list
     */
    protected $content = array();

    /**
     * Append an new Element at the end of the list
     * @param ElementMenu $elMenu new Element Menu to add
     * @return $this
     */
    public function appendElement(ElementMenu $elMenu)
    {
        $this->content[$elMenu->getId()] = $elMenu;
        return $this;
    }

    /**
     * Insert Element before another Element
     * @param string $index menu identifier to insert before; if empty insert at first place
     * @param ElementMenu $elMenu new Element Menu to insert
     * @return $this
     * @throws Exception
     */
    public function insertBefore($index, ElementMenu $elMenu)
    {
        if (empty($index)) {
            $this->content = array($elMenu->getId() => $elMenu) + $this->content;
        } else {
            $element = $this->getElement($index, $parent);
            if ($element === null) {
                throw new Exception("UI0100", $index);
            }
            if ($parent === null) {
                // local insert
                $new = array();
                foreach ($this->content as $k => $value) {
                    if ($k === $index) {
                        $new[$elMenu->getId()] = $elMenu;
                    }
                    $new[$k] = $value;
                }
                $this->content = $new;

            } else {
                /**
                 * @var ListMenu $parent
                 */
                $parent->insertBefore($index, $elMenu);
            }
        }
        return $this;
    }

    /**
     * Retrieve element content at any recusrion level
     * @param string $index
     * @param ListMenu $parentElement (ListMenu contains element, null if top level)
     * @return ElementMenu|ItemMenu|ListMenu
     */
    public function &getElement($index, &$parentElement = null)
    {
        $null = null;
        if (isset($this->content[$index])) {
            $parentElement = null;
            return $this->content[$index];
        } else {
            foreach ($this->content as $element) {
                if (is_a($element, "Dcp\\Ui\\ListMenu")) {
                    /**
                     * @var ListMenu $element
                     */
                    $searchElement = $element->getElement($index);
                    if ($searchElement !== null) {
                        $parentElement = $element;
                        return $searchElement;
                    }
                }
            }
        }
        return $null;
    }

    /**
     * Insert Element before another Element
     * @param string $index menu identifier to insert before
     * @param ElementMenu $elMenu new Element Menu to insert
     * @return $this
     * @throws Exception
     */
    public function insertAfter($index, ElementMenu $elMenu)
    {
        $element = $this->getElement($index, $parent);
        if ($element === null) {
            throw new Exception("UI0101", $index);
        }
        if ($parent === null) {
            // local insert
            $new = array();
            foreach ($this->content as $k => $value) {
                $new[$k] = $value;
                if ($k === $index) {
                    $new[$elMenu->getId()] = $elMenu;
                }
            }
            $this->content = $new;
        } else {
            /**
             * @var ListMenu $parent
             */
            $parent->insertAfter($index, $elMenu);
        }
        return $this;
    }

    /**
     * Remove en element menu content
     * @param string $index ELement index to remove from list
     * @return $this
     */
    public function removeElement($index)
    {
        $element = $this->getElement($index, $parent);
        if ($element !== null) {

            if ($parent === null) {
                // local delete
                unset($this->content[$index]);
            } else {
                /**
                 * @var ListMenu $parent
                 */
                $parent->removeElement($index);
            }
        }
        return $this;
    }

    protected function getContent()
    {
        return $this->content;
    }
}
