<?php

/**
 * TableMapper类用于表映射，对表操作进行简单封装，用来实现单表的CRUD，sql子句生成，并且支持主从、父子关系表映射。
 */
class TableMapper {

    static $master_detail_deep = 50;

    /**
     * 创建TableMapper对象
     * @param string $table TableMapper对应主表名
     * @param PDODB $db  TableMapper对象使用数据库访问对象，默认为系统主数据库访问对象
     * @param string $alias			主表别名，默认NULL 即使用系统默认别名。
     */
    function __construct($table, PDODB $db = NULL, $alias = NULL) {
        $this->table($table, $alias);
        if ($db)
            $this->db = $db;
        else
            $this->db = $GLOBALS['context']->db;
    }

    /**
     * @var PDODB
     */
    protected $db;

    function get_db() {
        return $this->db;
    }

    protected function parse_tab_alias(&$txt, &$name, &$alias) {
        if (preg_match('/(\w+)(\s+as\s+|\s+)(\w+)/i', $txt, $m)) {
            $name = $m[1];
            $alias = $m[3];
            return true;
        } else
            return false;
    }

    protected function parse_col_alias(&$txt, &$name, &$alias) {
        if (preg_match('/(\w+)(\s*\)\s+\as\s+|\s*\)\s+|\s+as\s+|\s+)(\w+)/i', $txt, $m)) {
            $name = $m[1];
            if (strncmp(ltrim($m[2]), ')', 1) === 0)
                $name .=')';
            $alias = $m[3];
            return true;
        }
        return false;
    }

    public $table;
    protected $tab_alias = NULL;

    /**
     * 设置主表名和别名，应在对象创建后调用其他子句前立即调用，否则可能无效。
     * @param string $table 主表名
     * @param string $alias 主表别名，默认NULL 即使用系统默认别名。
     */
    function table($table, $alias = NULL) {
        $table = trim($table);
        if ($alias) {
            $this->table = $table;
            $this->tab_alias = $alias;
        } else if (!$this->parse_tab_alias($table, $this->table, $this->tab_alias)) {
            $this->table = $table;
            $this->tab_alias = NULL;
        }
        return $this;
    }

    /* Mapper设置开始，设置映射参数，全部返回$this，仅对查询find_by,find_all_by,count_by,sum_by,avg_by,max_by,min_by有效，
     * 其中仅where子句对delete_by,update_by有效，
     * 所有子句对delete、update、save、insert函数无效 */

    protected $cols_text = NULL;
    protected $cols = array();

    /**
     * 设置需要返回表的列名，调用无参数函数cols()，清除cols子句
     * @param string|array $cols  设置需要返回表的列名，默认全部列，如果为字符串，使用,划分列名,如果为数组是列名列表。在带join sql中如果不需要返回列，可传入''
     */
    function cols($cols = NULL) {
        if ($cols === NULL) { //clear
            $this->cols_text = NULL;
            $this->cols = array();
            return $this;
        }
        $cols_a = array();
        $this->parse_cols($this->table, $cols, $this->cols_text, $cols_a);
        foreach ($cols_a as $key => $value) {  //handle call col first
            if (is_int($key)) {
                if (!isset($this->cols[$value]) && !in_array($value, $this->cols))
                    $this->cols[] = $value;
            } else
                $this->cols[$key] = $value;
        }
        return $this;
    }

    /**
     * 添加需要返回表的列名，可以添加多个
     * @param string $col 添加需要返回表的列名
     * @param string $alias 添加列名的别名
     */
    function col($col, $alias = NULL) {
        if (!$col && !is_string($col))
            return $this;
        $col = trim($col);
        if ($col === '*')
            return $this;     //omit *
        if ($alias)
            $this->cols[$col] = $alias;  //one col alias,like col(col1,alias1)
        else
            $this->cols[] = $col;
        return $this;
    }

    private function parse_cols($table, $cols, &$cols_text, &$cols_alias) {
        if (is_string($cols)) {
            $cols = trim($cols);
            if ($cols === '*') {  //all cols
                $cols_alias = $this->db->query_for_cols($table);
                $cols_text = NULL;
                return true;
            } else if (strpos($cols, '(') !== false) {  //find sql func ,only put it to sql
                if ($cols_text)
                    $cols_text .=',' . $cols;
                else
                    $cols_text = $cols;
                return true;
            } else
                $cols = explode(',', $cols); //simple cols
        }

        if (is_array($cols) && count($cols) > 0) {
            foreach ($cols as $val) {
                $c_alias = NULL;
                if ($this->parse_col_alias($val, $val, $c_alias) && $c_alias)
                    $cols_alias[$val] = $c_alias;
                else
                    $cols_alias[] = $val;
            }
            return true;
        }
        return false;
    }

    /**
     * 设置需要返回表的列名，等同cols方法，调用无参数函数select()，清除select子句
     * @param string|array $cols 设置需要返回表的列名，默认全部列，如果为字符串，使用,划分列名,如果为数组是列名列表。在带join sql中如果不需要返回列，可传入''
     */
    function select($cols = NULL) {
        return $this->cols($cols);
    }

    public $order = NULL;

    /**
     * 添加排序的列，可以添加多个，调用无参数函数order()，清除order子句
     * @param string $order_col 用于排序的列名
     * @param boolean $desc	是否为降序，否则是升序，默认true
     * @param boolean $tail	添加到后面还是插入前面，默认true
     */
    function order($order_col = NULL, $desc = true, $tail = true) {
        if ($order_col === NULL) {
            $this->order = NULL;
            return $this;
        }
        $s = $desc ? ' DESC' : ' ASC';
        if ($tail) {
            if ($this->order)
                $this->order .=',';
            $this->order .= $order_col . $s;
        }
        else {
            if ($this->order)
                $this->order = $order_col . $s . ',' . $this->order;
            else
                $this->order = $order_col . $s . $this->order;
        }
        return $this;
    }

    public $limit = NULL;
    public $offset = NULL;
    public $is_page = false;

    /**
     * 记录分页，调用无参数函数limit()，清除limit子句，和page子句互斥，即后面语句将覆盖前面设置，仅对find_all_by有效。
     * @param int $limit	每页记录数
     * @param int $offset	偏移量 ，默认为0，即首条
     */
    function limit($limit = NULL, $offset = NULL) {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->is_page = false;
        return $this;
    }

    /**
     * 记录分页，调用无参数函数page()，清除page子句，和limit子句互斥，即后面语句将覆盖前面设置，仅对find_all_by有效。
     * @param int $size		每页记录数
     * @param int $start	起始页数，第一页为1，默认为1
     */
    function page($size = 20, $start = NULL) {
        $this->limit = $size;
        $this->offset = $start;
        $this->is_page = true;
        return $this;
    }

    public $where = array();

    /**
     * 添加where子句，可以添加多个，调用无参数函数where()，清除where子句，通过property where得到当前where子句设置$var=$obj->where。
     * @param string|array $clause where子句，如果为字符串where子句，使用此子句，如果是数组，根据key，value用$item_and连接生成where子句.  col1 > value
     * @param boolean $item_and 如果$clause是数组，使用此值来连接各项，true用AND连接，false用OR连接，默认为AND
     * @param boolean $not 连接此where子句前是否添加NOT，默认为不添加
     * @param boolean $and 此where子句和原有的where子句连接方式，true用AND连接，false用OR连接，默认为AND
     */
    function where($clause = NULL, $item_and = true, $and = true, $not = false) {
        if ($clause === NULL) {
            $this->where = array();
            return $this;
        }
        $this->where[] = array('clause' => $clause, 'in' => $item_and, 'out' => $and, 'not' => $not);
        return $this;
    }

    /**
     * 添加带OR的where子句，可以添加多个，调用无参数函数where()，清除where子句
     * @param string|array $clause where子句，如果为字符串where子句，使用此子句，如果是数组，根据key，value用$item_and连接生成where子句.
     * @param boolean $item_and 如果$clause是数组，使用此值来连接各项，true用AND连接，false用OR连接，默认为AND
     * @param boolean $not 连接此where子句前是否添加NOT，默认为不添加
     */
    function or_where($clause, $item_and = true, $not = false) {
        return $this->where($clause, $item_and, $not, false);
    }

    /**
     * 添加单个字段的where子句，可以添加多个，调用无参数函数where()，清除where子句
     * @param sring $col	列名
     * @param mixed $value	列对应的值
     * @param string $op	sql操作符，默认为=，如果为in，$value应该为数组，还包括>,<,<>,like,is null,is not null等。
     * @param boolean $not 连接此where子句前是否添加NOT，默认为不添加
     * @param boolean $and 此where子句和原有的where子句连接方式，true用AND连接，false用OR连接，默认为AND
     */
    function where_col($col, $value, $op = '=', $and = true, $not = false) {
        if (strpos($col, '.') === false) {
            if ($this->tab_alias)
                $col = "{$this->tab_alias}.{$col}";
            else
                $col = "{$this->table}.{$col}";
        }

        if ($op == '=')
            return $this->where(array("{$col}" => $value), true, $and, $not);
        else
            return $this->where(array("{$col} {$op}" => $value), true, $and, $not);
    }

    /**
     * 添加带OR的单个字段的where子句，可以添加多个，调用无参数函数where()，清除where子句
     * @param sring $col	列名
     * @param mixed $value	列对应的值
     * @param string $op	sql操作符，默认为=，如果为in，$value应该为数组，还包括>,<,<>,like,is null,is not null等。
     *  @param boolean $not 连接此where子句前是否添加NOT，默认为不添加
     */
    function or_where_col($col, $value, $op = '=', $not = false) {
        return $this->where_col($col, $value, $op, false, $not);
    }

    public $join = array();

    /**
     * 添加从表join子句，可以添加多个，join子句仅对查询by语句，find、count有效，调用无参数函数join()，清除join子句，通过property join得到当前join子句设置$var=$obj->join。。
     * @param string $detail		从表名称
     * @param string|array $on		join条件, 如果为字符串join子句，使用此子句，如果是数组，根据key=value连接生成join子句.
     * @param string|array $cols	设置需要返回从表的列名，默认*，全部列，如果为字符串，使用,划分列名,如果为数组是列名列表。如果不需要返回列，可传入''
     * @param bool $left_join 		是否left连接，否则普通连接。
     * @param string $alias			从表别名，默认NULL 即使用系统默认别名。
     */
    function join($detail, $on = NULL, $cols = '*', $left_join = false, $alias = NULL) {
        if ($detail === NULL) {
            $this->join = array();
            return $this;
        }
        $detail = strtolower(trim($detail));
        $alias = trim($alias);
        if (!$alias)
            $this->parse_tab_alias($detail, $detail, $alias);

        $cols_alias = array();
        $cols_text = '';
        $this->parse_cols($detail, $cols, $cols_text, $cols_alias);

        $this->join[$detail] = array('detail' => $detail, 'on' => $on, 'cols' => $cols_alias, 'cols_text' => $cols_text, 'left' => $left_join, 'alias' => $alias);
        return $this;
    }

    /**
     * 添加从表单个字段的where子句，可以添加多个，必须在对应的join子句后调用
     * @param sring $detail	从表名
     * @param sring $col	列名
     * @param mixed $value	列对应的值
     * @param string $op	sql操作符，默认为=，如果为in，$value应该为数组，还包括>,<,<>,like,is null,is not null等。
     * @param boolean $not 连接此where子句前是否添加NOT，默认为不添加
     * @param boolean $and 此where子句和原有的where子句连接方式，true用AND连接，false用OR连接，默认为AND
     */
    function join_where_col($detail, $col, $value, $op = '=', $and = true, $not = false) {
        $detail = strtolower(trim($detail));
        if (!$detail || !isset($this->join[$detail]) || !$col && !is_string($col))
            return $this;

        if (strpos($col, '.') === false) {
            if ($this->join[$detail]['alias'])
                $col = "{$this->join[$detail]['alias']}.{$col}";
            else
                $col = "{$detail}.{$col}";
        }
        if ($op == '=')
            return $this->where(array("{$col}" => $value), true, $and, $not);
        else
            return $this->where(array("{$col} {$op}" => $value), true, $and, $not);
    }

    /**
     * 添加需要返回表的列名，可以添加多个
     * @param string $detail 添加需要列的明细表
     * @param string $col 添加需要返回表的列名
     * @param string $alias 添加列名的别名
     */
    function join_col($detail, $col = NULL, $alias = NULL) {
        $detail = strtolower(trim($detail));
        if (!$detail || !isset($this->join[$detail]) || !$col && !is_string($col))
            return $this;
        $col = trim($col);
        if ($col === '*')
            return $this;     //omit *

        $row = & $this->join[$detail]['cols'];
        if ($alias)
            $row[$col] = $alias;  //one col alias,like join_col(col1,alias1)
        else
            $row[] = $col;
        return $this;
    }

    /**
     * 添加从表left join子句，可以添加多个，left_join子句仅对查询by语句有效，调用无参数函数join()，清除join子句。
     * @param string $detail		从表名称
     * @param string|array $on		join条件, 如果为字符串join子句，使用此子句，如果是数组，根据key=value连接生成join子句.
     * @param string|array $cols	设置需要返回从表的列名，默认*，全部列，如果为字符串，使用,划分列名,如果为数组是列名列表。如果不需要返回列，可传入''
     * @param string $alias			从表别名，默认NULL 没有。
     */
    function left_join($detail, $on, $cols = '*', $alias = NULL) {
        return $this->join($detail, $on, $cols, true, $alias);
    }

    public $distinct = NULL;

    /**
     * cols是否设置distinct ，调用无参数函数distinct()，清除distinct子句
     * @param booelan $distinct 是否设置distinct，默认为false.
     */
    function distinct($distinct = NULL) {
        $this->distinct = $distinct;
        return $this;
    }

    public $group = NULL;

    /**
     * 设置group by子句，调用无参数函数distinct()，清除group子句
     * @param string $group group by子句，默认为不设.
     */
    function group($group = NULL) {
        $this->group = $group;
        return $this;
    }

    public $having = NULL;

    /**
     * 设置having子句，调用无参数函数having()，清除having子句
     * @param string $having having子句，默认为不设.
     */
    function having($having = NULL) {
        $this->having = $having;
        return $this;
    }

    public $quote_col = true;

    /**
     * where方法中列名是否quote，如mysql用·，，调用无参数函数quote_col()，清除quote_col设置。默认为true，设置true可提高安全性，但要求where方法中列名为普通列名，不能带函数。
     * @param boolean $quote_col 列名是否quote，默认传入false，列名可以带函数。
     */
    function quote_col($quote_col = true) {
        $this->quote_col = $quote_col;
        return $this;
    }

    /**
     * 清除所有sql子句设置
     */
    function clear() {
        $this->limit = NULL;
        $this->offset = NULL;
        $this->is_page = false;
        $this->cols = array();
        $this->cols_text = NULL;
        $this->order = NULL;
        $this->distinct = NULL;
        $this->group = NULL;
        $this->having = NULL;
        $this->quote_col = true;
        $this->join = array();
        $this->where = array();
        $this->tab_alias = NULL;
        return $this;
    }

    /* Mapper设置完成 */

    /**
     * 查找对应条件的单条记录。
     */
    function & find_by($lob = false) {
        $result = &$this->_find_by(10, $lob);
        return $result;
    }

    /**
     * 查找对应条件的全部记录列表。
     */
    function & find_all_by() {
        $result = &$this->_find_by(20);
        return $result;
    }

    /**
     * 返回对应条件的全部记录数
     */
    function row_count_by() {
        $result = &$this->_find_by(25);
        return $result;
    }

    /**
     * 得到对应条件的记录数。
     */
    function count_by() {
        $result = &$this->_find_by(30);
        return $result;
    }

    /**
     * 得到对应条件的字段总和
     */
    function sum_by() {
        $result = &$this->_find_by(31);
        return $result;
    }

    /**
     * 得到对应条件的字段平均值。
     */
    function avg_by() {
        $result = &$this->_find_by(32);
        return $result;
    }

    /**
     * 得到对应条件的字段最大值。
     */
    function max_by() {
        $result = &$this->_find_by(33);
        return $result;
    }

    /**
     * 得到对应条件的字段最小值。
     */
    function min_by() {
        $result = &$this->_find_by(34);
        return $result;
    }

    protected function get_cols_clause($cols, $table, & $sql, & $cols_exist, $alias, $cols_text = NULL) {

        if (count($cols) == 0) {
            if ($cols_text && $cols_text !== '*') {
                if (strpos($cols_text, '(') === false) {
                    $cols = explode(',', $cols_text);
                    $cols_text = NULL;
                }
            } else {
                $cols = $this->db->query_for_cols($table);
                $cols_text = NULL;
            }
        }

        $t = $alias ? $alias : $table;

        if ($cols_text) { //contain sql func
            $t_cols = explode(',', $cols_text);
            if (count($t_cols) > 0) {
                foreach ($t_cols as $t_col) {
                    if (strpos($t_col, '(') === false)
                        $sql = $sql === NULL ? "{$t}.{$this->db->quote($t_col)}" : "{$sql},{$t}.{$this->db->quote($t_col)}";
                    else
                        $sql = $sql === NULL ? $t_col : $sql . ',' . $t_col;
                }
            } else
                $sql = $sql === NULL ? $cols_text : $sql . ',' . $cols_text;
        }

        if (count($cols) > 0)
            foreach ($cols as $key => $val) {
                if (is_int($key)) {
                    $col = $val;
                    $c_alias = NULL;
                } else {
                    $col = $key;
                    $c_alias = $val;
                }
                if (isset($cols_exist[strtolower($col)]) || strlen($col) == 0)
                    continue;
                if (strpos($col, '(') !== false)
                    $sql = $sql === NULL ? $col : $sql . ',' . $col;  //find sql func
                else {
                    $sql = $sql === NULL ? "{$t}.{$this->db->quote($col)}" : $sql . ",{$t}.{$this->db->quote($col)}";
                    $cols_exist[] = strtolower($col);
                }
                if ($c_alias)
                    $sql .= $this->db->alias($c_alias);
            }
    }

    protected function & _find_by($type, $lob = false) {
        if (!$this->table || !is_string($this->table)) {
            $result = false;
            return $result;
        }

        if ($type >= 30 && $type <= 34) {
            $sql = 'SELECT ';
            $col = '*';
            if ($this->cols)
                $col = $this->cols;
            if ($type === 30) {
                if (is_array($col))
                    $col = implode(',', $col);
            } else {
                if (is_string($col))
                    $col = explode(',', $col);
                if (is_array($col) && count($col) > 0)
                    $col = $this->quote($col[0]);
            }

            switch ($type) {
                case 31: $op = 'SUM';
                    break;
                case 32: $op = 'AVG';
                    break;
                case 33: $op = 'MAX';
                    break;
                case 34: $op = 'MIN';
                    break;
                default: $op = 'COUNT';
            }
            if ($this->distinct)
                $sql .=$op . "(DISTINCT {$col})";
            else
                $sql .=$op . "({$col})";

            if ($this->join) {
                $sql .=" FROM {$this->db->table($this->table)} ";
                if ($this->tab_alias) {
                    $sql .= $this->db->alias($this->tab_alias);
                    $t = $this->tab_alias;
                } else
                    $t = $this->table;


                foreach ($this->join as $k => $join) {
                    $detail = $join['detail'];
                    $on = $join['on'];
                    if ($join['left'])
                        $jsql = " LEFT JOIN ";
                    else
                        $jsql = " JOIN ";
                    $alias = $join['alias'];
                    $jsql .=$this->db->table($detail);

                    if ($alias) {
                        $d = $alias;
                        $jsql .= $this->db->alias($d);
                    } else
                        $d = $detail;

                    $jsql .=' ON ';
                    if (is_array($on)) {
                        $first = true;
                        foreach ($on as $item) {
                            if ($first) {
                                $jsql .= "{$t}.{$this->db->quote($item)}={$d}.{$this->db->quote($item)}";
                                $first = false;
                            } else
                                $jsql .= " and {$t}.{$this->db->quote($item)}={$d}.{$this->db->quote($item)}";
                        }
                    } else
                        $jsql .= $on;
                    $sql .=$jsql;
                }
            }else {
                $sql .=" FROM {$this->db->table($this->table)} "; //count_by
                if ($this->tab_alias)
                    $sql .= $this->db->alias($this->tab_alias);
            }
        }else {
            if ($this->distinct)
                $sql = 'SELECT DISTINCT ';
            else
                $sql = 'SELECT ';
            if ($this->join) {
                $t_sql = NULL;
                $cols_exist = array();
                $this->get_cols_clause($this->cols, $this->table, $t_sql, $cols_exist, $this->tab_alias, $this->cols_text);
                foreach ($this->join as $join) {
                    $join['cols'] = array_diff_assoc($join['cols'], $this->cols); //add by zxd,2011/1/9
                    $this->get_cols_clause($join['cols'], $join['detail'], $t_sql, $cols_exist, $join['alias'], $join['cols_text']);
                }

                unset($cols_exist);
                if ($type == 25 && $this->group == NULL) { //row_count_by and not group by
                    $sql .= 'COUNT(*)';
                } else if ($t_sql)
                    $sql .=$t_sql;
                else
                    $sql .=' * ';
                $sql .=" FROM {$this->db->table($this->table)} ";
                if ($this->tab_alias) {
                    $sql .= $this->db->alias($this->tab_alias);
                    $t = $this->tab_alias;
                } else
                    $t = $this->table;


                foreach ($this->join as $k => $join) {
                    $detail = $join['detail'];
                    $on = $join['on'];
                    if ($join['left'])
                        $jsql = " LEFT JOIN ";
                    else
                        $jsql = " JOIN ";
                    $alias = $join['alias'];
                    $jsql .=$this->db->table($detail);

                    if ($alias) {
                        $d = $alias;
                        $jsql .= $this->db->alias($d);
                    } else
                        $d = $detail;

                    $jsql .=' ON ';
                    if (is_array($on)) {
                        $first = true;
                        foreach ($on as $item) {
                            if ($first) {
                                $jsql .= "{$t}.{$this->db->quote($item)}={$d}.{$this->db->quote($item)}";
                                $first = false;
                            } else
                                $jsql .= " and {$t}.{$this->db->quote($item)}={$d}.{$this->db->quote($item)}";
                        }
                    } else
                        $jsql .= $on;
                    $sql .=$jsql;
                }
            }else {
                if ((!$this->cols || count($this->cols) <= 0) && $this->cols_text === NULL) {
                    if ($type == 25 && $this->group == NULL)
                        $sql .=' COUNT(*) '; //row_count_by and not group by
                    else
                        $sql .= ' * ';
                    $sql .="  FROM {$this->db->table($this->table)} ";
                    if ($this->tab_alias)
                        $sql .= $this->db->alias($this->tab_alias);
                }
                else {
                    if ($type == 25 && $this->group == NULL)
                        $sql .=' COUNT(*) '; //row_count_by and not group by
                    else if (is_array($this->cols) && count($this->cols) > 0) {
                        $t_sql = NULL;
                        $cols_exist = array();
                        $this->get_cols_clause($this->cols, $this->table, $t_sql, $cols_exist, $this->tab_alias, $this->cols_text);
                        $sql .= $t_sql;
                    } else
                        $sql .= $this->cols_text;

                    $sql .=" FROM {$this->db->table($this->table)} ";
                    if ($this->tab_alias)
                        $sql .= $this->db->alias($this->tab_alias);
                }
            }
        }

        $values = array();


        $all_out = '';
        $where_all = $this->parse_where_clause($values, $all_out);   //where func clause
        if ($where_all)
            $sql .=' WHERE ' . $where_all;

        if ($this->group !== NULL) {
            $sql .="  GROUP BY {$this->group}";
            if ($this->having !== NULL)
                $sql .="  HAVING {$this->having}";
        }

        if ($this->order !== NULL)
            $sql .=" ORDER BY {$this->order}";

        if ($type == 10)              //find_by
            $result = $lob ? $this->db->get_lob($sql, $values, true) : $this->db->get_row($sql, $values, true);
        else if ($type >= 30 && $type <= 34) {             //count_by
            $result = $this->db->get_value($sql, $values, true);
            if ($result !== false)
                $result = intval($result);
            else
                $result = false;
        }else if ($type == 20) { //find_all_by
            if ($this->limit === NULL)
                $result = $this->db->get_all($sql, $values, true);
            else {

                if ($this->is_page) {
                    $offset = $this->offset === NULL ? 1 : $this->offset;
                    $offset = ($offset - 1) * $this->limit;
                    if ($offset < 0)
                        $offset = 0;
                }else {
                    $offset = $this->offset === NULL ? 0 : $this->offset;
                }
                $result = $this->db->get_limit($sql, $values, $this->limit, $offset, true);
            }
        } else if ($type == 25) { //row_count_by
            if ($this->group !== NULL)
                $sql = 'SELECT COUNT(*) FROM (' . $sql . ') l9z1t_';
            $result = $this->db->get_value($sql, $values, true);
        } else
            $result = false;
        return $result;
    }

    protected function & parse_where_clause(array & $values, &$out_rel) {
        $where_all = '';
        foreach ($this->where as $row) {
            $where = $row['clause'];
            $out = $row['out'];
            $in = $row['in'];
            $not = $row['not'];
            $single = false;
            if (is_array($where))
                $this->get_where_by_hash($where, $values, $in, $single);

            if ($where) {
                if ($where_all) {
                    $prefix = $out ? ' AND ' : ' OR ';
                } else {
                    $prefix = NULL;
                    $out_rel = $out ? ' AND ' : ' OR ';
                }
                if ($not)
                    $prefix = $prefix ? ' NOT ' : $prefix . ' NOT ';
                $where = $single ? $where : "({$where})";
                if ($where_all)
                    $where_all .=($prefix ? "{$prefix} {$where}" : $where);
                else
                    $where_all = ($prefix ? "{$prefix} {$where}" : $where);
            }
        }
        return $where_all;
    }

    protected function quote($col) {
        if ($this->quote_col)
            return $this->db->quote($col);
        else
            return $col;
    }

    protected function contain_op_1($string) {
        return preg_match('/(IS\sNULL|IS\sNOT\sNULL)/i', $string);
    }

    protected function contain_op_2($string, &$matches) {
        return preg_match('/(.*)(!=|>|<|=|\sNOT\sIN|\sNOT|\sLIKE|\sIN)/iU', $string, $matches);
    }

    protected function contain_func($string, &$matches) {
        return preg_match('/\+|=|\*|\/|\(.*\)|NULL/i', $string, $matches);
    }

    protected function get_where_by_hash(&$where, &$values, $and, &$single) {
        $clause = array();
        foreach ($where as $key => $val) {
            if ($this->contain_op_1($key))
                $clause[] = " {$key}";
            else {
                $matches = array();
                if ($this->contain_op_2($key, $matches)) {
                    $op = trim(substr($key, strlen($matches[1])));
                    $key = trim($matches[1]);
                    if (strpos($key, '.') !== false) {
                        $key_a = explode('.', $key);
                        $key_b = array();
                        foreach ($key_a as $k_row)
                            $key_b[] = $this->quote($k_row);
                        $key = implode('.', $key_b);
                    } else
                        $key = $this->quote($key);


                    $op = strtoupper($op);
                    if (strpos($op, 'IN') !== false && ( strcmp($op, 'IN') == 0 || strcmp($op, 'NOT IN') == 0 || preg_match('/NOT\sIN/', $op))) {
                        $isstr = is_string($val);
                        if ($isstr)
                            $val = trim($val);
                        if ($isstr && $val[0] === '(' && $val[strlen($val) - 1] === ')') { //is sub select,omit,MUST escape select clause's value
                            $clause[] = " {$key} {$op} {$val}";
                        } else {
                            if ($isstr && strpos($val, ',') !== false)
                                $val = explode(',', $val);
                            if (is_array($val) && count($val) > 0) {
                                $opts = array();
                                foreach ($val as $item) {
                                    $opts [] = '?';
                                    $values[] = $item;
                                }
                                $clause[] = " {$key} {$op} (" . implode(',', $opts) . ')';
                            } else {       //in but val is not array,rewrite equal
                                $clause[] = " {$key} = ?";
                                $values[] = $val;
                            }
                        }
                    } else {
                        $clause[] = " {$key} {$op}  ?";
                        $values[] = $val;
                    }
                } else {
                    if (strpos($key, '.') !== false) {
                        $key_a = explode('.', $key);
                        $key_b = array();
                        foreach ($key_a as $k_row)
                            $key_b[] = $this->quote($k_row);
                        $key = implode('.', $key_b);
                    } else
                        $key = $this->quote($key);
                    $clause[] = " {$key} = ?";
                    $values[] = $val;
                }
            }
        }
        $single = false;
        if ($clause) {
            $single = count($clause) == 1;
            $where = implode(($and ? ' AND ' : ' OR '), $clause);
        } else
            $where = NULL;
    }

    /**
     * 返回由limit,page子句设置当前分页信息，page_no：当前页码，page_size:每页行数，total_page：符合条件总页数，total_row：符合条件总行数。
     * <br>如果没有调用limit,page子句，返回NULL。
     * @return array|NULL 返回值，array->返回当前分页信息，page_no：当前页码，page_size:每页行数，total_page：符合条件总页数，total_row：符合条件总行数；NULL：无效
     */
    function get_page_info() {
        if ($this->limit === NULL)
            return NULL;
        else if ($this->limit > 100)
            $this->limit = 100;

        $row_num = $this->row_count_by();
        $page_num = ceil($row_num / $this->limit);
        if ($this->offset === NULL || $this->offset < 1)
            $page_no = 1;
        else if ($this->offset > $page_num)
            $page_no = $page_num;
        else {
            if ($this->is_page)
                $page_no = $this->offset;
            else
                $page_no = ceil($this->offset / $this->limit);
        }
        return array('page_no' => $page_no, 'page_size' => $this->limit, 'total_page' => $page_num, 'total_row' => $row_num);
    }

    /**
     * 删除记录。
     * @param string|array $where where子句，如果为字符串where子句，使用此子句，如果是数组，根据key，value用AND连接生成where子句.
     * @return bool 执行结果
     */
    function delete($where) {
        if (!$this->table)
            return false; //omit delete from [table]
        $values = array();
        $single = false;
        if (is_array($where))
            $this->get_where_by_hash($where, $values, true, $single);
        $sql = "DELETE FROM {$this->db->table($this->table)} ";
        if ($this->tab_alias)
            $sql .= $this->db->alias($this->tab_alias);
        $sql .= ($where ? "  WHERE  {$where}" : '');
        return $this->db->query($sql, $values, false);
    }

    /**
     * 删除对应条件的记录,条件见where子句
     */
    function delete_by() {
        if (!$this->table || !is_string($this->table) || count($this->where) == 0) { // omit delete from [table]
            $result = false;
            return $result;
        }
        $values = array();


        $all_out = $where = '';
        $where_all = $this->parse_where_clause($values, $all_out);
        if ($where_all)
            $where .=$where_all;

        if ($where) {
            $sql = "DELETE  FROM {$this->db->table($this->table)}  ";
            if ($this->tab_alias)
                $sql .= $this->db->alias($this->tab_alias);
            $sql .="  WHERE " . $where;
            $result = $this->db->query($sql, $values, false);
        } else
            $result = false;
        return $result;
    }

    //得到当前cols字段列表数组
    private function & get_cols() {
        $a = array();
        foreach ($this->cols as $k => $v)
            if (is_int($k))
                $a[] = $v;
            else
                $a[] = $k;
        return $a;
    }

    /**
     * 更新记录数组或简单对象到表中。
     * @param array|object $data 记录数组或对象，记录必须为key=>vale对，不能是数字索引。
     * @param string|array $where where子句，如果为字符串where子句，使用此子句，如果是数组，根据key，value用AND连接生成where子句.
     * @param array  $is_func_key  set子句是否为函数或表达式，如果是$is_func_key包含其key。
     * @return bool 执行结果
     */
    function update($data, $where, array $is_func_key = NULL) {
        if (!$data || !$this->table || (!is_object($data) && !is_array($data)))
            return false;
        if (is_object($data))
            $data = get_object_vars($data);

        if (is_array($data)) {
            if (empty($data))
                return false;

            $cols = & $this->get_cols();
            if (!$cols)
                $cols = $this->db->query_for_cols($this->table);
            $fields = $values = array();

            //转小写
            foreach ($cols as $key => $val) {
                $cols[$key] = strtolower($val);
            }
            foreach ($data as $key => $val) {
                if ($cols && !isset($cols[$key]) && !in_array($key, $cols))
                    continue;
                if (is_string($val) && $is_func_key !== NULL && in_array($key, $is_func_key)) {  //|| ($is_func_key===NULL && $this->contain_func($val,$m) )
                    $fields[] = $this->quote($key) . "=" . $val;
                } else {
                    $fields[] = $this->quote($key) . '=?';
                    $values[] = $val;
                }
            }
        } else if (!$data || !is_string($data))
            return false;

        $single = false;
        if (is_array($where))
            $this->get_where_by_hash($where, $values, true, $single);

        $sql = "UPDATE {$this->db->table($this->table)} SET ";
        if (is_string($data))
            $sql .= $data;
        else
            $sql .= implode(', ', $fields);

        $sql .= $where ? " WHERE   {$where}" : '';

        return $this->db->query($sql, $values, false);
    }

    /**
     * 更新对应条件的记录，第1个参数必须是更新的数据data,条件见where子句
     */
    protected function & update_by($data) {
        if (!$this->table || !is_string($this->table) || count($this->where) == 0) {//omit update [table] set
            $result = false;
            return $result;
        }
        if (is_object($data))
            $data = get_object_vars($data);

        if (is_array($data) && count($data) > 0) {
            $cols = & $this->get_cols();
            if (!$cols)
                $cols = $this->db->query_for_cols($this->table);
            $fields = $values = array();
            foreach ($data as $key => $val) {
                if ($cols !== false && $cols && !isset($cols[$key]) && !in_array($key, $cols))
                    continue;
                $m = array();
                if (is_string($val) && $this->contain_func($val, $m)) {  //expression or sql func,no handle ,*BUG may error while string contain func char
                    $fields[] = $this->quote($key) . '=' . $val;
                } else {
                    $fields[] = $this->quote($key) . '=?';
                    $values[] = $val;
                }
            }
        } else if (!$data || !is_string($data))
            return false;


        $all_out = $where = '';
        $where_all = $this->parse_where_clause($values, $all_out);

        if ($where_all)
            $where .=$where_all;
        if ($where) {
            $sql = "UPDATE {$this->db->table($this->table)} SET ";
            if (is_string($data))
                $sql .= $data;
            else
                $sql .= implode(', ', $fields);
            $sql .= $where ? " WHERE   {$where}" : '';
            $result = $this->db->query($sql, $values, false);
        } else
            $result = false;

        return $result;
    }

    /**
     * 保存记录数组或简单对象到表中，如果为多行或多个对象，依次保存，如果没有此记录，插入数据，否则，更新对应数据。
     * @param array|object $data 记录数组或对象，记录必须为key=>vale对，不能是数字索引。
     * @param booelan $mutil_ingore_err 多行数据时是否忽略错误，执行多行数据时忽略错误，继续进行，否则终止。
     * @param array   $no_up_col   如果为列表，不需要更新的字段数组，默认为空数组，即全部更新。
     * @param array   $pks      update条件列，非主键的唯一索引列，主键列不需要录入
     * @param array   $up		update set子句由$up的col对应的value SET 等于后的子句，$up优先级高于$no_up_col，注意防止sql注入。
     * @return boolean|array 执行结果，如果成功返回true，否则，返回出错数据行。
     */
    function save($data, $mutil_ingore_err = false, array $no_up_col = array(), array $pks = array(), array $up = array()) {
        if (!$data || !$this->table || (!is_object($data) && !is_array($data)))
            return false;

        if (is_object($data))
            $data = get_object_vars($data);
        else if (is_array($data) && isset($data[0]) && is_object($data[0])) {
            $d = $data;
            $data = array();
            foreach ($d as $row) {
                if (is_object($row))
                    $data[] = get_object_vars($row);
            }

            unset($d);
        }

        $pcols = array();
        $this->db->query_for_key_cols($this->table, $pcols, $pks);
        $cols = & $this->get_cols();
        if (!$cols)
            $cols = $pcols;
        else
            $cols = array_merge($cols, $pks);

        $is_mutil = isset($data[0]) && is_array($data[0]);
        if ($is_mutil)
            $alldata = & $data;
        else
            $alldata = array($data);
        $result = array();
        foreach ($alldata as $row) {
            $fields = $values = $params = $update = $pk = array();
            foreach ($cols as $col) {
                if (!isset($row[$col]))
                    continue;
                $fields[] = $col;
                $params[] = $col_i = ":{$col}";
                if (in_array($col, $pks))
                    $pk[] = "{$col}=:{$col}";
                else {
                    if ($up && isset($up[$col])) {
                        $update[] = "{$col}={$up[$col]}";   //not safe,use set clause.
                    } else {
                        if (!in_array($col, $no_up_col))
                            $update[] = "{$col}=:{$col}"; //not is pk,not in none update
                    }
                }
                $values[$col_i] = $row[$col];
            }
            $value_list = array($values);
            if ($mutil_ingore_err) {
                try {
                    $dbresult = $this->db->adapter->save($this->table, $fields, $params, $update, $pk, $value_list);
                    if ($dbresult === false)
                        $result[] = $row;
                } catch (Exception $e) {
                    $result[] = $row;
                }
            } else {
                $dbresult = $this->db->adapter->save($this->table, $fields, $params, $update, $pk, $value_list);
                if ($dbresult === false) {
                    $result[] = $row;
                    break;
                }
            }
        }
        return $dbresult ? true : $result;
    }

    /**
     * 插入记录数组或简单对象到表中，如果为多行或多个对象，依次插入
     * @param array|object $data 记录数组，记录必须为key=>vale对，不能是数字索引。
     * @param booelan $mutil_ingore_err 多行数据时是否忽略错误，执行多行数据时忽略错误，继续进行，否则终止。
     * @return boolean|array 执行结果，如果成功返回true，否则，返回出错数据行。
     */
    function insert($data, $mutil_ingore_err = false) {
        if (!$data || !$this->table || (!is_object($data) && !is_array($data)))
            return false;


        if (is_object($data))
            $data = get_object_vars($data);
        else if (is_array($data) && isset($data[0]) && is_object($data[0])) {
            $d = $data;
            $data = array();
            foreach ($d as $row) {
                if (is_object($row))
                    $data[] = get_object_vars($row);
            }

            unset($d);
        }
        $cols = & $this->get_cols();
        if (!$cols)
            $cols = $this->db->query_for_cols($this->table);

        $is_mutil = isset($data[0]) && is_array($data[0]);
        if ($is_mutil)
            $row = & $data[0];
        else
            $row = & $data;

        $result = array();
        if ($is_mutil) {
            foreach ($data as $row) {
                $fields = $params = $values = array();
                foreach ($row as $key => $val) {
                    if ($cols !== false && !isset($cols[$key]) && !in_array($key, $cols))
                        continue;
                    $fields[] = $key;
                    $params[] = $key_a = ":{$key}";
                    $values[$key_a] = $val;
                }
                $sql = "INSERT INTO {$this->db->table($this->table)} (`" . implode('`,`', $fields) . "`) VALUES (" . implode(', ', $params) . ")";
                $this->db->prepare($sql, false);
                if ($mutil_ingore_err) {
                    $e0 = error_reporting(0);
                    try {
                        $dbresult = @$this->db->execute($values);
                        if ($dbresult === false)
                            $result[] = $row;
                    } catch (Exception $e) {
                        $result[] = $row;
                        if (isset($GLOBALS['context']))
                            $GLOBALS['context']->log_error('TableMapper Mutil Insert Error Ignore:' . $e->getMessage());
                    }
                    error_reporting($e0);
                }else {
                    $dbresult = $this->db->execute($values);
                    if ($dbresult === false) {
                        $result[] = $row;
                        break;
                    }
                }
            }
            return $dbresult ? true : $result;
        } else {
            $fields = $params = $values = array();
            foreach ($row as $key => $val) {
                if ($cols !== false && !isset($cols[$key]) && !in_array($key, $cols))
                    continue;
                $fields[] = $key;
                $params[] = $key_a = ":{$key}";
                $values[$key_a] = $val;
            }
            $sql = "INSERT INTO {$this->db->table($this->table)} (`" . implode('`,`', $fields) . "`) VALUES (" . implode(', ', $params) . ")";
            $this->db->prepare($sql, false);

            if ($this->db->execute($values))
                return true;
            else {
                $result[] = $row;
                return $result;
            }
        }
    }

  /* 插入时，如果是唯一索引一样可以更新指定字段或忽略插入
  * $mode= IGNORE | UPDATE  $dup_update_fld 如果是唯一索引一样,要更新的字段,以逗号分开
  */
	public function insert_dup($data, $mode='IGNORE',$dup_update_fld='') {
		$fields = array();
		$values = array();
    $ins_data = array();
    if(empty($data)){
      return false;
    }
    if(isset($data[0])){
      $ins_data = $data;
    }else{
      $ins_data[0] = $data;
    }
    foreach($ins_data[0] as $k=>$v){
     $fields[] = $k;
    }
    foreach($ins_data as $sub_ins){
      $row = array();
      foreach($sub_ins as $key => $val) {
        $row[] =  strip_tags(addslashes($val));
      }
      $values[] = "('".join("','",$row)."')";
    }
		$table_name = $this->table;

    $sql = "INSERT INTO ";
    if(strtoupper($mode) == 'IGNORE'){
      $sql = "INSERT IGNORE INTO ";
    }

    $sql .= $table_name." (`".implode('`,`', $fields)."`) VALUES ".implode(', ', $values);
    if(strtoupper($mode) == 'UPDATE'){
      $dup_update_arr = explode(',',$dup_update_fld);
      $sql_dup = '';
      foreach($dup_update_arr as $sub_dup){
        $v = $sub_dup;
        $sql_dup .= "{$v}=VALUES({$v}),";
      }
      $sql .= " ON DUPLICATE KEY UPDATE ".substr($sql_dup,0,-1);
    }
    //echo $sql;die;
		return $this->db->query($sql);
	}

    /**
     * 从时间戳得到标准时间字符串
     * @param intger $timestamp 时间戳
     * @return string 标准时间字符串
     */
    static function to_date($timestamp) {
        return date('Y-m-d H:i:s', $timestamp);
    }

}
