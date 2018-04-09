<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.Table.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
//

$CLASS_TABLE_PHP = "";
/**
 * @deprecated not necessary and not used now
 */
class Table
{
    // ---------------------------------------------------------------------------
    // Public var
    //
    public $array; // THE Array (2 dimensionnal)
    public $arrayobj = "FALSE"; // if set to TRUE, Table can manage array of i
    // Objects, it means that fields are considered as
    // Object Attributes
    // Fields attributes
    public $fields; // array of the field names to show
    public $filter = "/[A-Za-z0-9][A-Za-z0-9_]*/"; // Regexp: Field names to show
    public $sortable_fields; // array of the fields you can sort using the
    // sort_link
    public $ordered_by; // the current ordering field
    public $sort_link; // the URL used to perform a reordering of the table
    // this URL is a format string with a %s where we
    // should give the column name
    public $fieldsattr; // array, foreach field gives the align attr
    // Header attributes
    public $heading = 0; // if set, create a <th> section
    public $headcontent; // the content of the header
    // if not set, the field string is used
    public $headattr; // foreach element of the header gives the spanning
    // and align attributes
    public $headcolor; // Background color of header line, if not set,
    // we use the first color of colortab
    public $indexcolor; // Background color of index line
    // Footer attributes
    public $footing = 0; // Do we use a footer ?
    public $footcontent; // content of the footer
    // if not set, the field string is used
    public $footattr; // foreach element of the footer gives the spanning
    // and align attributes
    public $footcolor; // background color of the footer, if not set,
    // we use the color from colortab
    // Hyperlinks
    public $links; // array of links associated with fields, each link
    // is a composed with a dynamic url + an array of
    // value that should replace %s format tag
    // in the url using an sprintff function
    // General Layout
    public $class; // the class (CSS) associated with <table, <tr, <td
    public $index_class; // the class (CSS) associated with the index
    public $head_class; // the class (CSS) associated with the header
    public $width = "100%"; // table width
    public $colortab; // table of alternate colors used for each row
    public $border = 0; // border size
    public $cellspacing = 0; // cellspacing size
    public $defaultcolor = "#FFFFFF"; // too easy
    public $page_bgcolor = "#FFFFFF"; // background color for nav fields
    // Page Layout
    public $start = 0; // the start index
    public $slice = 20; // the slice size, zero means all
    public $alpha_index = 0; // do you want an alphabetical index before the table
    // it's a bitstream :
    // 1 : UpperCase letter
    // 2 : lowerCase letter
    public $page_numbering = 0; // if true a page number is displayed
    public $previous = "prev"; // the text (can be <img...) used to link to the
    // previous page
    public $next = "next"; // the text (can be <img...) used to link to the
    // next page
    public $first = "first"; // the text (can be <img...) used to link to the
    // first page
    public $last = "last"; // the text (can be <img...) used to link to the
    // last page
    public $page_link; // the URL used to turn pages. This URL is a format
    // string with two %s in it the first on gives the
    // index of the page start, the second gives the
    // page size
    // ---------------------------------------------------------------------------
    // Private var
    public $row = 0; // index of the current displayed row
    public $out; // the output string
    //
    // ---------------------------------------------------------------------------
    // Public methods
    // ---------------------------------------------------------------------------
    //
    public function show()
    {
        $this->construct_table();
        print $this->out;
    }
    
    public function get()
    {
        $this->construct_table();
        return ($this->out);
    }
    //
    // ---------------------------------------------------------------------------
    // Private methods
    // ---------------------------------------------------------------------------
    //
    public function construct_table()
    {
        $this->out = "";
        // check the table
        if (!is_array($this->array)) {
            return;
        }
        if (($this->arrayobj == "FALSE") && !is_array($this->array[$this->start])) {
            return;
        }
        if (($this->arrayobj == "TRUE") && !is_object($this->array[$this->start])) {
            return;
        }
        // init the fields to display
        $this->select_colnames();
        // show the table
        $this->show_alpha();
        
        $this->table_open();
        $this->show_header();
        $this->show_table();
        $this->show_footer();
        $this->table_close();
        
        $this->show_navbar();
        
        return;
    }
    // ----------------------------------------------
    public function show_alpha()
    {
        if (!isset($this->alpha_index)) {
            return;
        }
        if (!isset($this->ordered_by)) {
            return;
        }
        $ind = 0;
        $lettre = array();
        reset($this->array);
        foreach ($this->array as $k => $v) {
            if ($this->arrayobj == "TRUE") {
                $initiale = substr($v->$this->ordered_by, 0, 1);
            } else {
                $initiale = substr($v[$this->ordered_by], 0, 1);
            }
            
            if ($initiale != '') {
                if (!isset($lettre[$initiale])) {
                    $lettre[$initiale] = $this->slice * (int)($ind / $this->slice);
                }
            }
            $ind++;
        }
        
        $alpha_up = array(
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z"
        );
        $alpha_low = array(
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g",
            "h",
            "i",
            "j",
            "k",
            "l",
            "m",
            "n",
            "o",
            "p",
            "q",
            "r",
            "s",
            "t",
            "u",
            "v",
            "w",
            "x",
            "y",
            "z"
        );
        
        if (($this->alpha_index & 1)) {
            $alpha_list = $alpha_up;
        }
        if (($this->alpha_index & 2)) {
            $alpha_list = $alpha_low;
        }
        $this->table_open();
        $this->table_index_row_open();
        $idx = "[";
        $prev = 0;
        foreach ($alpha_list as $k => $car) {
            if ($prev) {
                $idx = $idx . "|";
            }
            if (!isset($lettre[$car])) {
                $idx = $idx . "&nbsp;$car&nbsp;";
                $prev = 1;
            } else {
                $value[0] = $lettre[$car];
                $value[1] = $this->slice;
                $link = $this->create_link($this->page_link, $value, $car);
                $idx = $idx . "&nbsp;$link&nbsp;";
                $prev = 1;
            }
        }
        $idx = $idx . "]";
        #################
        $this->table_cell($idx, 1, "center", "", isset($this->index_class) ? $this->index_class : '', "100%");
        $this->table_row_close();
        $this->table_row_open();
        $this->table_cell("&nbsp;", 1, "", "", "", "100%");
        $this->table_row_close();
        $this->table_close();
    }
    // ----------------------------------------------
    public function show_header()
    {
        if (!$this->heading) {
            return;
        }
        $this->row = 0;
        
        $this->table_heading_row_open();
        
        reset($this->fields);
        foreach ($this->fields as $k => $v) {
            if (isset($this->headcontent)) {
                if (isset($this->headcontent[$v])) {
                    $val = $this->headcontent[$v];
                } else {
                    continue;
                }
            } else {
                $val = $v;
            }
            /* link ? */
            if (isset($this->sortable_fields[$v])) {
                $value[0] = $v;
                $val = $this->create_link($this->sort_link, $value, $val);
            }
            
            $this->table_heading_cell($val, isset($this->headattr[$v]["span"]) ? $this->headattr[$v]["span"] : '', isset($this->headattr[$v]["class"]) ? $this->headattr[$v]["class"] : '', isset($this->headattr[$v]["align"]) ? $this->headattr[$v]["align"] : '');
        }
        $this->table_row_close();
    }
    // ----------------------------------------------
    public function show_table()
    {
        $ind = 0;
        reset($this->array);
        foreach ($this->array as $key => $val) {
            if ($ind++ < $this->start) {
                continue;
            }
            if (($this->slice > 0) && ($ind > ($this->start + $this->slice))) {
                break;
            }

            
            if ((!is_array($val)) && (!is_object($val))) {
                continue;
            }
            
            $this->table_row_open();
            
            reset($this->fields);
            foreach ($this->fields as $k => $v) {
                if ($this->arrayobj == "TRUE") {
                    $curval = $val->$v;
                    ######## BUG ########### echo $val->Parents->nomp;
                } else {
                    if (isset($val[$v])) {
                        $curval = $val[$v];
                    } else {
                        $curval = "";
                    }
                }
                if (!isset($this->links[$v])) {
                    $this->table_cell($curval, 1, isset($this->fieldsattr[$v]["align"]) ? $this->fieldsattr[$v]["align"] : '', isset($this->fieldsattr[$v]["wrap"]) ? $this->fieldsattr[$v]["wrap"] : '', isset($this->fieldsattr[$v]["class"]) ? $this->fieldsattr[$v]["class"] : '', isset($this->fieldsattr[$v]["width"]) ? $this->fieldsattr[$v]["width"] : '');
                } else {
                    reset($this->links[$v][1]);
                    foreach ($this->links[$v][1] as $kk => $var) {
                        if ($this->arrayobj == "TRUE") {
                            $value[$kk] = $val->$var;
                        } else {
                            if (isset($val[$var])) {
                                $value[$kk] = $val[$var];
                            } else {
                                $value[$kk] = "";
                            }
                        }
                    }
                    $link = $this->create_link($this->links[$v][0], $value, $curval);
                    $this->table_cell($link, 1, isset($this->fieldsattr[$v]["align"]) ? $this->fieldsattr[$v]["align"] : '', isset($this->fieldsattr[$v]["wrap"]) ? $this->fieldsattr[$v]["wrap"] : '', isset($this->fieldsattr[$v]["class"]) ? $this->fieldsattr[$v]["class"] : '', isset($this->fieldsattr[$v]["width"]) ? $this->fieldsattr[$v]["width"] : '');
                }
            }
            $this->table_row_close();
        }
    }
    // ----------------------------------------------
    public function show_footer()
    {
        if ($this->footing) {
            $this->table_row_open("", $this->footcolor);
            reset($this->fields);
            foreach ($this->fields as $k => $v) {
                if (isset($this->footcontent)) {
                    if (isset($this->footcontent[$v])) {
                        $val = $this->footcontent[$v];
                    } else {
                        continue;
                    }
                } else {
                    $val = $v;
                }
                
                $this->table_cell($val, isset($this->footattr[$v]["span"]) ? $this->footattr[$v]["span"] : '', isset($this->footattr[$v]["align"]) ? $this->footattr[$v]["align"] : '', isset($this->footattr[$v]["wrap"]) ? $this->footattr[$v]["wrap"] : '', isset($this->footattr[$v]["class"]) ? $this->footattr[$v]["class"] : '', isset($this->footattr[$v]["width"]) ? $this->footattr[$v]["width"] : '');
            }
            $this->table_row_close();
        }
        return;
    }
    // ----------------------------------------------
    public function show_navbar()
    {
        // Next/Prev pages
        if (!$this->slice || ($this->slice >= sizeof($this->array))) {
            return;
        }
        $this->table_open();
        $this->table_row_open("", $this->page_bgcolor);
        $nbcol = 5;
        $link_prev = "&nbsp;";
        $link_next = "&nbsp;";
        $link_first = "&nbsp;";
        $link_last = "&nbsp;";
        $this->table_cell($link_prev, 5, "", "", "", "");
        $this->table_row_close();
        
        $page_tot = (((int)(sizeof($this->array) / $this->slice)) * $this->slice) == sizeof($this->array) ? (int)(sizeof($this->array) / $this->slice) : (int)(sizeof($this->array) / $this->slice) + 1;
        $page_num = (int)($this->start / $this->slice) + 1;
        
        $this->table_row_open("", $this->page_bgcolor);
        
        $values_first[0] = 0;
        $values_first[1] = $this->slice;
        $values_last[0] = sizeof($this->array) - (sizeof($this->array) - (($page_tot - 1) * ($this->slice)));
        $values_last[1] = $this->slice;
        if ($this->start - $this->slice >= 0) {
            $value[0] = $this->start - $this->slice;
            $value[1] = $this->slice;
            $link_first = $this->create_link($this->page_link, $values_first, $this->first);
            $link_prev = $this->create_link($this->page_link, $value, $this->previous);
        }
        if ($this->start + $this->slice < sizeof($this->array)) {
            $value[0] = $this->start + $this->slice;
            $value[1] = $this->slice;
            $link_next = $this->create_link($this->page_link, $value, $this->next);
            $link_last = $this->create_link($this->page_link, $values_last, $this->last);
        }
        
        $this->table_cell($link_first, 1, "left", "", "", "10%");
        $this->table_cell($link_prev, 1, "left", "", "", "20%");
        $this->table_cell("$page_num/$page_tot", 1, "center", "", "", "20%");
        $this->table_cell($link_next, 1, "right", "", "", "20%");
        $this->table_cell($link_last, 1, "right", "", "", "10%");
        $this->table_row_close();
        $this->table_close();
    }
    // ----------------------------------------------
    public function select_colnames()
    {
        if (isset($this->fields) || ($this->arrayobj == "TRUE")) {
            return;
        }
        reset($this->array);
        list($key, $val) = each($this->array);
        reset($val);
        foreach ($val as $k => $v) {
            if (preg_match($this->filter, $k)) {
                $this->fields[] = $k;
            }
        }
    }
    // ----------------------------------------------
    public function table_open()
    {
        $this->out = $this->out . sprintf("<table%s %s cellpadding=\"0\" %s %s >\n", isset($this->class) ? " class=$this->class" : "", isset($this->border) ? " border=$this->border" : "", isset($this->cellspacing) ? " cellspacing=$this->cellspacing" : "", isset($this->width) ? " width=$this->width" : "");
    }
    // ----------------------------------------------
    public function table_close()
    {
        $this->out = $this->out . "</table>\n";
    }
    // ----------------------------------------------
    public function table_row_open($nbcol = "", $color = "")
    {
        if (!$color && (isset($this->colortab))) {
            $color = $this->colortab[$this->row % sizeof($this->colortab) ];
        }
        $this->out = $this->out . sprintf(" <tr%s%s%s>\n", $color ? " bgcolor=$color" : "", isset($this->class) ? " class=$this->class" : "", $nbcol ? " cols=$nbcol" : "");
    }
    // ----------------------------------------------
    public function table_row_close()
    {
        $this->out = $this->out . " </tr>\n";
        $this->row++;
    }
    // ----------------------------------------------
    public function table_index_row_open()
    {
        $this->out = $this->out . sprintf(" <tr%s%s>\n", isset($this->index_class) ? " class=\"$this->index_class\"" : "", isset($this->indexcolor) ? " bgcolor=$this->indexcolor" : "");
    }
    // ----------------------------------------------
    public function table_heading_row_open()
    {
        $this->out = $this->out . sprintf(" <tr%s%s>\n", isset($this->head_class) ? " class=\"$this->head_class\"" : "", isset($this->headcolor) ? " bgcolor=$this->headcolor" : "");
    }
    // ----------------------------------------------
    public function table_heading_cell($val, $colspan = 1, $class = "", $align = "left")
    {
        $w_class = "";
        if (isset($class)) {
            $w_class = " class=$class";
        } else {
            if (isset($this->class)) {
                $w_class = " class=$this->class";
            }
        }
        $this->out = $this->out . sprintf("  <th%s%s%s><p>%s</p></th>\n", $w_class, $colspan ? " colspan=$colspan" : "", $align ? " align=$align" : "", $val);
    }
    // ----------------------------------------------
    public function table_cell($val, $colspan = 1, $align = "left", $wrap = "", $class = "", $width = "")
    {
        $w_class = "";
        if ($class != "") {
            $w_class = " class=$class";
        } else {
            if (isset($this->class)) {
                $w_class = " class=$this->class";
            }
        }
        $this->out = $this->out . sprintf("  <td%s%s%s%s%s><p>%s&nbsp;</p></td>\n", $w_class, $colspan ? " colspan=$colspan" : "", $align ? " align=$align" : "", $wrap ? " $wrap" : "", $width ? " width=$width" : "", $val);
    }
    ////////////////////////////////////////////////////////////////
    // create_link : should be usefull for other classes
    //  this function is here because we don't know where we should put it
    //  so !!
    //
    public function create_link($template, $values, $text)
    {
        $link = "\$link = sprintf (\"\n<a href=\\\"" . $template . "\\\">\"";
        reset($values);
        foreach ($values as $key => $val) {
            $link = $link . ",\"" . $val . "\"";
        }
        $link = $link . ");";
        eval($link);
        $link = $link . $text . "</a>";
        return ($link);
    }
}
