<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Column;

use Symfony\Component\Security\Core\SecurityContextInterface;
use APY\DataGridBundle\Grid\Filter;

abstract class Column
{
    const DEFAULT_VALUE = null;

    /**
     * Filter
     */
    const DATA_CONJUNCTION = 0;
    const DATA_DISJUNCTION = 1;

    const OPERATOR_EQ     = 'eq';
    const OPERATOR_NEQ    = 'neq';
    const OPERATOR_LT     = 'lt';
    const OPERATOR_LTE    = 'lte';
    const OPERATOR_GT     = 'gt';
    const OPERATOR_GTE    = 'gte';
    const OPERATOR_BTW    = 'btw';
    const OPERATOR_NBTW    = 'nbtw';
    const OPERATOR_BTWE   = 'btwe';
    const OPERATOR_NBTWE    = 'nbtwe';
    const OPERATOR_LIKE   = 'like';
    const OPERATOR_NLIKE  = 'nlike';
    const OPERATOR_RLIKE  = 'rlike';
    const OPERATOR_NRLIKE  = 'nrlike';
    const OPERATOR_LLIKE  = 'llike';
    const OPERATOR_NLLIKE  = 'nllike';
    const OPERATOR_ISNULL  = 'isNull';
    const OPERATOR_ISNOTNULL  = 'isNotNull';

    public static $virtualNotOperators = array(
        self::OPERATOR_NLIKE,
        self::OPERATOR_NRLIKE,
        self::OPERATOR_NLLIKE,
        self::OPERATOR_NBTW,
        self::OPERATOR_NBTWE,
    );

    /**
     * Align
     */
    const ALIGN_LEFT = 'left';
    const ALIGN_RIGHT = 'right';
    const ALIGN_CENTER = 'center';

    protected static $aligns = array(
        self::ALIGN_LEFT,
        self::ALIGN_RIGHT,
        self::ALIGN_CENTER,
    );

    /**
     * Internal parameters
     */
    protected $id;
    protected $title;
    protected $sortable;
    protected $filterable;
    protected $visible;
    protected $callback;
    protected $order;
    protected $size;
    protected $visibleForSource;
    protected $primary;
    protected $align;
    protected $inputType;
    protected $field;
    protected $role;
    protected $filterType;
    protected $filter;
    protected $params;
    protected $isSorted = false;
    protected $orderUrl;
    protected $securityContext;
    protected $data;
    protected $operatorsVisible;
    protected $operators;
    protected $defaultOperator;
    protected $values = array();
    protected $selectFrom;
    protected $selectMulti;
    protected $selectExpanded;
    protected $searchOnClick = false;
    protected $separator;
    protected $dataJunction;


    /**
     * Default Column constructor
     *
     * @param array $params
     */
    public function __construct($params = null)
    {
        $this->__initialize((array) $params);
    }

    public function __initialize(array $params)
    {
        $this->params = $params;

        // Basic
        $this->setId($this->getParam('id'));
        $this->setField($this->getParam('field'));
        $this->setVisibleForSource($this->getParam('source', false));
        $this->setPrimary($this->getParam('primary', false));
        $this->setTitle($this->getParam('title', ''));

        // Sort
        $this->setSortable($this->getParam('sortable', true));
        $this->setOrder($this->getParam('order'));

        // Style
        $this->setAlign($this->getParam('align', self::ALIGN_LEFT));
        $this->setSize($this->getParam('size', -1));
        $this->setInputType($this->getParam('inputType', 'text'));
        $this->setVisible($this->getParam('visible', true));

        // Security
        $this->setRole($this->getParam('role'));

        // Filter
        $this->setFilterable($this->getParam('filterable', true));
        $this->setFilterType($this->getParam('filter', 'input'));
        $this->setSelectFrom($this->getParam('selectFrom', 'query'));
        $this->setSelectMulti($this->getParam('selectMulti', false));
        $this->setSelectExpanded($this->getParam('selectExpanded', false));
        $this->setValues($this->getParam('values', array()));

        // Filter operator
        $this->setOperatorsVisible($this->getParam('operatorsVisible', true));
        // Order is important for the display order
        if (isset($this->params['array'])) {
            $this->setOperators($this->getParam('operators', array(
                self::OPERATOR_LIKE,
                self::OPERATOR_NLIKE,
                self::OPERATOR_EQ,
                self::OPERATOR_NEQ,
                self::OPERATOR_ISNULL,
                self::OPERATOR_ISNOTNULL,
            )));
        } else {
            $this->setOperators($this->getParam('operators', array(
                self::OPERATOR_EQ,
                self::OPERATOR_NEQ,
                self::OPERATOR_LT,
                self::OPERATOR_LTE,
                self::OPERATOR_GT,
                self::OPERATOR_GTE,
                self::OPERATOR_BTW,
                self::OPERATOR_NBTW,
                self::OPERATOR_BTWE,
                self::OPERATOR_NBTWE,
                self::OPERATOR_LIKE,
                self::OPERATOR_NLIKE,
                self::OPERATOR_RLIKE,
                self::OPERATOR_NRLIKE,
                self::OPERATOR_LLIKE,
                self::OPERATOR_NLLIKE,
                self::OPERATOR_ISNULL,
                self::OPERATOR_ISNOTNULL,
            )));
        }

        $this->setDefaultOperator($this->getParam('defaultOperator', self::OPERATOR_LIKE));

        // Features
        $this->setSearchOnClick($this->getParam('searchOnClick'), false);
        $this->setSeparator($this->getParam('separator', "<br />"));
    }

    protected function getParam($id, $default = null)
    {
        return isset($this->params[$id]) ? $this->params[$id] : $default;
    }

    /**
     * Draw cell
     *
     * @param string $value
     * @param Row $row
     * @param $router
     * @return string
     */
    public function renderCell($value, $row, $router)
    {
        if (is_callable($this->callback)) {
            $value = call_user_func($this->callback, $value, $row, $router);
        } else {
            $value = $this->getDisplayedValue($value);
        }

        if (array_key_exists((string)$value, $this->values)) {
            $value = $this->values[$value];
        }

        return $value;
    }

    public function getDisplayedValue($value)
    {
        return $value;
    }

    /**
     * Set column callback
     *
     * @param  $callback
     * @return self
     */
    public function manipulateRenderCell($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set column identifier
     *
     * @param $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * get column identifier
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * get column render block identifier
     *
     * @return int|string
     */
    public function getRenderBlockId()
    {
        // For Mapping fields and aggregate dql functions
        return str_replace(array('.', ':'), '_', $this->id);
    }

    /**
     * Set column title
     *
     * @param string $title
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get column title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set column visibility
     *
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Return column visibility
     *
     * @return bool return true when column is visible
     */
    public function isVisible()
    {
        if ($this->visible && $this->securityContext !== null && $this->getRole() != null) {
            return $this->securityContext->isGranted($this->getRole());
        }

        return $this->visible;
    }

    /**
     * Return true if column is sorted
     *
     * @return bool return true when column is sorted
     */
    public function isSorted()
    {
        return $this->isSorted;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * column ability to sort
     *
     * @return bool return true when column can be sorted
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * Return true if column is filtered
     *
     * @return boolean return true when column is filtered
     */
    public function isFiltered()
    {
        return ( (isset($this->data['from']) && $this->isQueryValid($this->data['from']) && $this->data['from'] != static::DEFAULT_VALUE)
              || (isset($this->data['to']) && $this->isQueryValid($this->data['to']) && $this->data['to'] != static::DEFAULT_VALUE)
              || (isset($this->data['operator']) && ($this->data['operator'] === self::OPERATOR_ISNULL || $this->data['operator'] === self::OPERATOR_ISNOTNULL)) );
    }

    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * column ability to filter
     *
     * @return bool return true when column can be filtred
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * set column order
     *
     * @param string $order asc|desc
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setOrder($order)
    {
        if ($order !== null) {
            $this->order = $order;
            $this->isSorted = true;
        }

        return $this;
    }

    /**
     * get column order
     *
     * @return string asc|desc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set column width
     *
     * @param int $size in pixels
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setSize($size)
    {
        if ($size < -1) {
            throw new \InvalidArgumentException(sprintf('Unsupported column size %s, use positive value or -1 for auto resize', $size));
        }

        $this->size = $size;

        return $this;
    }

    /**
     * get column width
     *
     * @return int column width in pixels
     */
    public function getSize()
    {
        return $this->size;
    }

    public function setOrderUrl($orderUrl)
    {
        $this->orderUrl = $orderUrl;

        return $this;
    }

    public function getOrderUrl()
    {
        return $this->orderUrl;
    }

    /**
     * set filter data from session | request
     *
     * @param  $data
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setData($data)
    {
        $this->data = array('operator' => $this->getDefaultOperator(), 'from' => static::DEFAULT_VALUE, 'to' => static::DEFAULT_VALUE);

        $hasValue = false;
        if (isset($data['from']) && $this->isQueryValid($data['from'])) {
            $this->data['from'] = $data['from'];
            $hasValue = true;
        }

        if (isset($data['to']) && $this->isQueryValid($data['to'])) {
            $this->data['to'] = $data['to'];
            $hasValue = true;
        }

        $isNullOperator = (isset($data['operator']) && ($data['operator'] === self::OPERATOR_ISNULL || $data['operator'] === self::OPERATOR_ISNOTNULL) );
        if (($hasValue || $isNullOperator) && isset($data['operator']) && $this->hasOperator($data['operator'])) {
            $this->data['operator'] = $data['operator'];
        }

        return $this;
    }

    /**
     * get filter data from session | request
     *
     * @return array data
     */
    public function getData()
    {
        $result = array();

        $hasValue = false;
        if ($this->data['from'] != $this::DEFAULT_VALUE) {
            $result['from'] = $this->data['from'];
            $hasValue = true;
        }

        if ($this->data['to'] != $this::DEFAULT_VALUE) {
            $result['to'] = $this->data['to'];
            $hasValue = true;
        }

        $isNullOperator = (isset($this->data['operator']) && ($this->data['operator'] === self::OPERATOR_ISNULL || $this->data['operator'] === self::OPERATOR_ISNOTNULL) );
        if ($hasValue || $isNullOperator) {
            $result['operator'] = $this->data['operator'];
        }

        return $result;
    }

    /**
     * Return true if filter value is correct (has to be overridden in each Column class that can be filtered, in order to catch wrong values)
     *
     * @return boolean
     */
    public function isQueryValid($query)
    {
        return true;
    }

    /**
     * Set column visibility for source class
     * @param $value
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setVisibleForSource($visibleForSource)
    {
        $this->visibleForSource = $visibleForSource;

        return $this;
    }

    /**
     * Return true is column in visible for source class
     * @return boolean
     */
    public function isVisibleForSource()
    {
        return $this->visibleForSource;
    }

    /**
     * Set column as primary
     *
     * @param boolean $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * Return true is column in primary
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->primary;
    }

    /**
     * Set column align
     * @param string $align left/right/center
     */
    public function setAlign($align)
    {
        if (!in_array($align, self::$aligns)) {
            throw new \InvalidArgumentException(sprintf('Unsupported align %s, just left, right and center are supported', $align));
        }

        $this->align = $align;

        return $this;
    }

    /**
     * get column align
     * @return bool
     */
    public function getAlign()
    {
        return $this->align;
    }

    public function setInputType($inputType)
    {
        return $this->inputType = $inputType;
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    /**
     * Filter
     */

    public function setFilterType($filterType)
    {
        $this->filterType = strtolower($filterType);

        return  $this;
    }

    public function getFilterType()
    {
        return $this->filterType;
    }

    public function getFilters($source)
    {
        $filters = array();
        $operator = $this->data['operator'];

        if ($this->hasOperator($operator)) {
            $valueFrom = $this->data['from'];

            if (isset($this->params['array'])) {
                $filters = $this->getArrayFilters($operator, (array) $valueFrom, $source);
            } else {
                $valueTo = $this->data['to'];
                $filters = $this->getBasicFilters($operator, $valueFrom, $valueTo, $source);
            }
        }

        if ($this->getSelectMulti() && $this->getDataJunction() !== null) {
            switch ($operator) {
                case self::OPERATOR_LIKE:
                case self::OPERATOR_RLIKE:
                case self::OPERATOR_LLIKE:
                    $this->setDataJunction(self::DATA_DISJUNCTION);
                    break;
                default:
                    $this->setDataJunction(self::DATA_CONJUNCTION);
                    break;
            }
        }

        return $filters;
    }

    protected function getBasicFilters($operator, $valueFrom, $valueTo, $source)
    {
        $filters = array();

        switch ($operator) {
            case self::OPERATOR_BTW:
            case self::OPERATOR_NBTW:
                if ($valueFrom != static::DEFAULT_VALUE) {
                    $filters[] = new Filter(self::OPERATOR_GT, $valueFrom);
                }
                if ($valueTo != static::DEFAULT_VALUE) {
                    $filters[] = new Filter(self::OPERATOR_LT, $valueTo);
                }
                break;
            case self::OPERATOR_BTWE:
            case self::OPERATOR_NBTWE:
                if ($valueFrom != static::DEFAULT_VALUE) {
                    $filters[] = new Filter(self::OPERATOR_GTE, $valueFrom);
                }
                if ($valueTo != static::DEFAULT_VALUE) {
                    $filters[] = new Filter(self::OPERATOR_LTE, $valueTo);
                }
                break;
            case self::OPERATOR_ISNULL:
            case self::OPERATOR_ISNOTNULL:
                $valueFrom = null;
            default:
                $filters[] = new Filter($operator, $valueFrom);
                break;
        }

        return $filters;
    }

    protected function getArrayFilters($operator, array $values, $source)
    {
        $filters = array();

        switch ($operator) {
            case self::OPERATOR_ISNULL:
                $filters[] =  new Filter($operator);
                $filters[] =  new Filter(self::OPERATOR_EQ, 'a:0:{}');
                $this->setDataJunction(self::DATA_DISJUNCTION);
                break;
            case self::OPERATOR_ISNOTNULL:
                $filters[] =  new Filter($operator);
                $filters[] =  new Filter(self::OPERATOR_NEQ, 'a:0:{}');
                break;
            case self::OPERATOR_EQ:
            case self::OPERATOR_NEQ:
                if ($operator == self::OPERATOR_EQ) {
                    $filters[] = new Filter(self::OPERATOR_RLIKE, 'a:'.count($values).':{');
                    $operator = self::OPERATOR_LIKE;
                } else {
                    $filters[] = new Filter(self::OPERATOR_NRLIKE, 'a:'.count($values).':{');
                    $operator = self::OPERATOR_NLIKE;
                }
            case self::OPERATOR_LIKE:
            case self::OPERATOR_NLIKE:
                foreach ($values as $value) {
                    $filters[] =  new Filter($operator, serialize($value));
                }
                break;
            default:
                throw new \Exception($operator . ' operator is not supported for array values.');
        }

        return $filters;
    }

    public function setDataJunction($dataJunction)
    {
        $this->dataJunction = $dataJunction;

        return $this;
    }

    /**
     * get data filter junction (how column filters are connected with column data)
     *
     * @return bool self::DATA_CONJUNCTION | self::DATA_DISJUNCTION
     */
    public function getDataJunction()
    {
        return $this->dataJunction;
    }

    public function setOperators(array $operators)
    {
        $this->operators = $operators;

        return $this;
    }

    /**
     * Return column filter operators
     *
     * @return array $operators
     */
    public function getOperators()
    {
        // Issue with Doctrine (See http://www.doctrine-project.org/jira/browse/DDC-1857 and http://www.doctrine-project.org/jira/browse/DDC-1858)
        if ($this->hasDQLFunction()) {
            return array_intersect($this->operators, array(self::OPERATOR_EQ,
                self::OPERATOR_NEQ,
                self::OPERATOR_LT,
                self::OPERATOR_LTE,
                self::OPERATOR_GT,
                self::OPERATOR_GTE,
                self::OPERATOR_BTW,
                self::OPERATOR_BTWE));
        }

        return $this->operators;
    }

    public function setDefaultOperator($defaultOperator)
    {
        if (!$this->hasOperator($defaultOperator)) {
            throw new \Exception($defaultOperator . ' operator not found in operators list.');
        }

        $this->defaultOperator = $defaultOperator;

        return $this;
    }

    public function getDefaultOperator()
    {
        return $this->defaultOperator;
    }

    /**
     * Return true if $operator is in $operators
     *
     * @param string $operator
     * @return boolean
     */
    public function hasOperator($operator)
    {
        return in_array($operator, $this->operators);
    }

    public function setOperatorsVisible($operatorsVisible)
    {
        $this->operatorsVisible = $operatorsVisible;

        return $this;
    }

    public function getOperatorsVisible()
    {
        return $this->operatorsVisible;
    }

    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setSelectFrom($selectFrom)
    {
        $this->selectFrom = $selectFrom;

        return $this;
    }

    public function getSelectFrom()
    {
        return $this->selectFrom;
    }

    public function getSelectMulti()
    {
        return $this->selectMulti;
    }

    public function setSelectMulti($selectMulti)
    {
        $this->selectMulti = $selectMulti;
    }

    public function getSelectExpanded()
    {
        return $this->selectExpanded;
    }

    public function setSelectExpanded($selectExpanded)
    {
        $this->selectExpanded = $selectExpanded;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function hasDQLFunction(&$matches = null)
    {
        $regex = '/(?P<all>(?P<field>\w+):(?P<function>\w+)(:)?(?P<parameters>\w*))$/';

        return ($matches === null) ? preg_match($regex, $this->field) : preg_match($regex, $this->field, $matches);
    }

    /**
     * Internal function
     *
     * @param $securityContext
     */
    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;

        return $this;
    }

    public function getParentType()
    {
        return '';
    }

    public function getType()
    {
        return '';
    }

    /**
     * By default all filers include a JavaScript onchange=submit block.  This
     * does not make sense in some cases, such as with multi-select filters.
     *
     * @todo Eventaully make this configurable via annotations?
     */
    public function isFilterSubmitOnChange()
    {
        return !$this->getSelectMulti();
    }

    public function setSearchOnClick($searchOnClick)
    {
        $this->searchOnClick = $searchOnClick;

        return $this;
    }

    public function getSearchOnClick()
    {
        return $this->searchOnClick;
    }
}
