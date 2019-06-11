<?php


namespace Anakeen\Workflow;

use Anakeen\SmartStructures\Wdoc\WDocHooks;

class DotGraph
{
    protected $useLabel = 'state';
    protected $nodeShape = "circle";
    /**
     * @var array
     */
    private $lines = array();
    /**
     * @var WDocHooks
     */
    private $wdoc;
    private $ratio = 'auto';
    private $orient = 'LR';
    private $size;
    private $type = 'simple';
    private $statefontsize;
    private $conditionfontsize;
    private $labelfontsize;
    private $fontsize;
    public $style = array(
        'autonext-color' => '#006400', // darkgreen
        'arrow-label-font-color' => '#555555', // dark grey
        'arrow-color' => '#00008b', // darkblue
        'ask-color' => '#00008b', // darkblue
        'condition-color0' => '#6df8ab', // green
        'condition-color1' => '#ffff00', // yellow
        'action-color2' => '#ffa500', // orange
        'action-color3' => '#74c0ec', // light blue
        'mail-color' => '#a264d2', // light violet
        'timer-color' => '#64a2d2', // light blue
        'start-color' => '#00ff00', // green
        'end-color' => '#ff0000', // red

    );

    /**
     * Generate dot text
     * @return string dot text
     */
    public function generate()
    {
        if (!$this->wdoc) {
            throw new \Anakeen\Exception('need workflow');
        }
        $ft = $this->wdoc->firstState;


        $this->setStates();
        $this->setTransitionLines();
        if ($this->wdoc->firstState && $this->nodeShape === "circle") {
            $this->lines[] = sprintf("%s [shape=doublecircle]", $this->wdoc->firstState);
        }


        //if ($this->ratio=="auto") $this->size='';
        $dot = "digraph \"" . $this->wdoc->getHtmlTitle() . "\" {
        ratio=\"{$this->ratio}\";
	    rankdir={$this->orient};
        {$this->size}
        bgcolor=\"white\";
        splines=true; fontsize={$this->conditionfontsize}; fontname=sans;
	node [shape={$this->nodeShape}, style=filled, fixedsize=true,fontsize={$this->fontsize},fontname=sans];
	edge [shape={$this->nodeShape}, style=filled, fixedsize=true,fontsize={$this->conditionfontsize},fontname=sans];\n";
        if ($ft) {
            $dot .= "\t{rank=1; \"$ft\";}\n";
        }

        $dot .= implode($this->lines, "\n");
        $dot .= "\n}";
        return $dot;
    }

    private function setTransitionLines()
    {
        foreach ($this->wdoc->cycle as $k => $v) {
            $this->setSimpleTransitionLine($k, $v);
        }
    }


    private function setSimpleTransitionLine($index, $tr)
    {
        $this->lines[] = sprintf('# simple %d %s %s->%s', $index, $tr["t"], $tr["e1"], $tr["e2"]);
        $e1 = $tr["e1"];
        $e2 = $tr["e2"];

        $tmain = '';
        if (isset($this->wdoc->autonext[$tr["e1"]]) && ($this->wdoc->autonext[$tr["e1"]] == $tr["e2"])) {
            $tmain = sprintf('color="%s",style="setlinewidth(3)",arrowsize=1.0', $this->style['autonext-color']);
        }
        $this->lines[] = sprintf(
            '"%s" -> "%s" [labelfontsize=6,color="%s" %s,labelfontname=sans, label="%s"];',
            $e1,
            $e2,
            $this->style['arrow-color'],
            $tmain,
            $this->_t($tr["t"])
        );
    }

    private function getActivityLabel($e)
    {
        $act = $this->wdoc->getActivity($e);
        if (!$act) {
            $act = sprintf(_("activity for %s"), $this->wdoc->getStateLabel($e));
        }
        return str_replace('_', ' ', $act);
    }


    private function setStates()
    {
        $states = $this->wdoc->getStates();
        foreach ($states as $k => $v) {
            $color = $this->wdoc->getColor($v);
            $saction = $this->getActivityLabel($v);
            $tt = sprintf('label="%s"', $this->_n($v));
            $tt .= " ,shape = {$this->nodeShape}, style=filled, fixedsize=true,width=1.0,   fontname=sans";
            if ($saction) {
                $tt .= ', tooltip="' . $v . '"';
            }

            if ($color) {
                $tt .= ',fillcolor="' . $color . '"';
            }

            $this->lines[] = '"' . $v . '" [' . $tt . '];';
        }
    }


    public function _t($s)
    {
        if ($s) {
            if ($this->useLabel !== "raw") {
                $s = $this->wdoc->getTransitionLabel($s);
            } else {
                return $s;
            }
            return str_replace(array(
                " ",
                '"',
                "_"
            ), array(
                "\\n",
                "&quot;",
                "\\n"
            ), $s);
        }
        return '';
    }

    public function _n($s)
    {
        if ($s) {
            if ($this->useLabel === "state") {
                $s = $this->wdoc->getStateLabel($s);
            } elseif ($this->useLabel === "activity") {
                $s = $this->wdoc->getActivity($s, $this->wdoc->getStateLabel($s));
            }

            return str_replace(array(
                " ",
                '"',
                "_"
            ), array(
                "\\n",
                "&quot;",
                "\\n"
            ), $s);
        }
        return '';
    }

    public function setWorkflow(WDocHooks & $doc)
    {
        $this->wdoc = $doc;
    }

    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function useLabel($use)
    {
        $this->useLabel = $use;
        if ($this->useLabel === "state") {
            $this->nodeShape = "box";
        } elseif ($this->useLabel === "raw") {
            $this->nodeShape = "hexagon";
        }
    }

    public function setOrient($orient)
    {
        $this->orient = $orient;
    }

    public function setSize($isize)
    {
        $this->fontsize = 13;
        if ($isize == "auto") {
            $this->size = "";
        } else {
            if ($isize == "A4") {
                $this->fontsize = 20;
                $this->size = "size=\"7.6,11!\";"; // A4 whith 1.5cm margin
            } else {
                if (preg_match("/([0-9\.]+),([0-9\.]+)/", $isize, $reg)) {
                    $this->fontsize = intval(min($reg[1], $reg[2]) / 1);
                    $this->fontsize = 12;
                    $this->size = sprintf("size=\"%.2f,%.2f!\";", floatval($reg[1]) / 2.55, floatval($reg[2]) / 2.55);
                } else {
                    $isize = sprintf("%.2f", floatval($isize) / 2.55);
                    $this->size = "size=\"$isize,$isize!\";";
                }
            }
        }

        $this->statefontsize = $this->fontsize;
        $this->conditionfontsize = intval($this->fontsize * 10 / 13);
        $this->labelfontsize = intval($this->fontsize * 11 / 13);
    }
}
