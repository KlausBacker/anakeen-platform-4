<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.TableLayout.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// $Id: Class.TableLayout.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Layout/Class.TableLayout.php,v $
// ---------------------------------------------------------------
// $Log: Class.TableLayout.php,v $
//
//
// ---------------------------------------------------------------------------
//
include_once('Class.Log.php');

class TableLayout
{
    // ---------------------------------------------------------------------------
    // Public var
    //
    public $table_name;
    public $array; // THE Array (2 dimensionnal) or
    // array of objects
    // Fields attributes
    public $fields; // array of the field names to show
    public $order_by; // the current ordering field
    public $desc = ""; // the ordering =up or down
    public $sort_link; // the URL used to perform a reordering of the table
    // this URL is a format string with a %s where we
    // should give the column name
    // Header attributes
    public $headcontent; // the content of the header
    // if not set, the field string is used
    public $headsortfields; // column with sorting capabilities
    // Footer attributes
    public $footcontent; // content of the footer
    // if not set, the field string is used
    // Hyperlinks
    public $links; // array of links associated with fields, each link
    // is a composed with a dynamic url + an array of
    // value that should replace %s format tag
    // in the url using an sprintff function
    // Paging attributes
    public $start = 0; // the start index
    public $slice = 20; // the slice size, zero means all
    public $page_numbering = 0; // if true a page number is displayed
    public $prev = "prev"; // the text (can be <img...) used to link to the
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
    // page size (number of elements in the page
    public $nb_tot = 0; // Total number of elements
    // ---------------------------------------------------------------------------
    // Private var
    public $row = 0; // index of the current displayed row
    public $out; // the output string
    public $paging_zone;
    public $header_zone;
    public $table_zone;
    public $footer_zone;
    //
    // ---------------------------------------------------------------------------
    // Public methods
    // ---------------------------------------------------------------------------
    //
    public function __construct(&$lay, $table_name = 'TABLE')
    {
        $this->table_name = $table_name;
        $this->log = new Log("", "TableLayout", "");
        $this->lay = & $lay;
    }
    //
    // ---------------------------------------------------------------------------
    // Private methods
    // ---------------------------------------------------------------------------
    //
    public function Set()
    {
        if ($this->start == "") {
            $this->start = 0;
        }
        // check the table
        if (!is_array($this->array)) {
            return;
        }
        // init the fields to display
        $this->SelectColnames();
        // show the table
        $this->GenPaging();
        $this->GenHeader();
        $this->GenTable();
        $this->GenFooter();
    }
    
    public function GenHeader()
    {
        if (!isset($this->headcontent)) {
            return;
        }
        reset($this->headcontent);
        foreach ($this->headcontent as $k => $v) {
            /* link ? */
            if (isset($this->headsortfields[$k])) {
                $value[0] = $this->headsortfields[$k];
                $value[1] = "down";
                $value[2] = 0;
                if ($this->order_by == $this->headsortfields[$k]) {
                    $value[2] = 0;
                    if ($this->desc == "down") {
                        $value[1] = "up";
                    }
                }
                $v = $this->create_link($this->sort_link, $value, $v);
            }
            $this->lay->set("$k", $v);
        }
    }
    // ----------------------------------------------
    public function GenTable()
    {
        $ind = 0;
        reset($this->array);
        $tmparray = "";
        foreach ($this->array as $key => $val) {
            if ($ind > $this->slice) {
                break;
            }

            
            if ((!is_array($val)) && (!is_object($val))) {
                continue;
            }
            
            reset($this->fields);
            foreach ($this->fields as $k => $v) {
                if (is_object($val)) {
                    $curval = $val->$v;
                } else {
                    if (isset($val[$v])) {
                        $curval = $val[$v];
                    } else {
                        $curval = "";
                    }
                }
                if (!isset($this->links[$v])) {
                    $tmparray[$ind][$v] = $curval;
                } else {
                    reset($this->links[$v][1]);
                    foreach ($this->links[$v][1] as $kk => $var) {
                        if (is_object($val)) {
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
                    $tmparray[$ind][$v] = $link;
                }
            }
            $ind++;
        }
        reset($this->fields);
        foreach ($this->fields as $k => $v) {
            $this->lay->SetBlockCorresp($this->table_name . "BODY", $v, $v);
        }
        
        $this->lay->SetBlockData($this->table_name . "BODY", $tmparray);
    }
    // ----------------------------------------------
    public function GenFooter()
    {
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
            
            $this->lay->set($v, $val);
        }
        return;
    }
    // ----------------------------------------------
    public function GenPaging()
    {
        $link_first = "";
        $link_last = "";
        $link_next = "";
        $link_prev = "";
        $page_num = 1;
        $page_tot = 1;
        // Next/Prev pages
        if ($this->slice && ($this->slice < $this->nb_tot) && isset($this->page_link)) {
            $page_tot = (ceil(($this->nb_tot / $this->slice) * $this->slice) == $this->nb_tot) ? ceil($this->nb_tot / $this->slice) : ceil($this->nb_tot / $this->slice + 1);
            $page_num = (int)($this->start / $this->slice) + 1;
            
            $values_first[0] = 0;
            $values_first[1] = $this->slice;
            $values_last[0] = $this->nb_tot - ($this->nb_tot - (($page_tot - 1) * ($this->slice)));
            $values_last[1] = $this->slice;
            if ($this->start - $this->slice >= 0) {
                $value[0] = $this->start - $this->slice;
                $value[1] = $this->slice;
                $link_first = $this->create_link($this->page_link, $values_first, $this->first);
                $link_prev = $this->create_link($this->page_link, $value, $this->prev);
            }
            if ($this->start + $this->slice < $this->nb_tot) {
                $value[0] = $this->start + $this->slice;
                $value[1] = $this->slice;
                $link_next = $this->create_link($this->page_link, $value, $this->next);
                $link_last = $this->create_link($this->page_link, $values_last, $this->last);
            }
        }
        $this->lay->set($this->table_name . "_PREV", $link_prev);
        $this->lay->set($this->table_name . "_NEXT", $link_next);
        $this->lay->set($this->table_name . "_FIRST", $link_first);
        $this->lay->set($this->table_name . "_LAST", $link_last);
        $this->lay->set($this->table_name . "_NUM", $page_num);
        $this->lay->set($this->table_name . "_NB", $page_tot);
    }
    // ----------------------------------------------
    // Used if fields are not provided
    public function SelectColnames()
    {
        if (isset($this->fields)) {
            return;
        }
        reset($this->array);
        list($key, $val) = each($this->array);
        if (is_object($val)) {
            $val = get_object_vars($val);
        }
        reset($val);
        foreach ($val as $k => $v) {
            $this->fields[] = $k;
        }
    }
    ////////////////////////////////////////////////////////////////
    // create_link : should be usefull for other classes
    //  this function is here because we don't know where we should put it
    //  so !!
    //
    public function create_link($template, $values, $text)
    {
        $link = "<a href=\"" . $template . "\">";
        for ($i = 0; $i < 9; $i++) {
            if (!isset($values[$i])) {
                $values[$i] = "";
            }
        }
        $link = sprintf($link, $values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6], $values[7], $values[8]);
        $link = $link . $text . "</a>";
        return ($link);
    }
}
