<?php
/*
 * @author Anakeen
 * @package FDL
 */
/**
 * Parameter Class
 * @author Anakeen
 */

class Parameter
{
    
    public $name;
    public $label;
    public $default;
    public $type;
    public $needed;
    public $volatile;
    public $oninstall;
    public $onupgrade;
    public $onedit;
    public $values; // Used for enum type parameters.
    public $value;
    
    public function getVisibility($operation)
    {
        $visibility = '';
        switch ($operation) {
            case 'install':
                $visibility = ($this->oninstall != '') ? $this->oninstall : 'W';
                break;

            case 'upgrade':
                $visibility = ($this->onupgrade != '') ? $this->onupgrade : 'H';
                if ($this->needed == 'Y' && $this->value == '') {
                    $visibility = 'W';
                }
                break;

            case 'parameter':
                $visibility = ($this->onedit != '') ? $this->onedit : 'R';
                break;
        }
        return $visibility;
    }
}
