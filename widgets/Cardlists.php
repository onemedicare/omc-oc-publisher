<?php namespace Omc\Publisher\Widgets;

use Db;
use App;
use Html;
use Lang;
use Input;
use Backend;
use DbDongle;
use Carbon\Carbon;
use October\Rain\Html\Helper as HtmlHelper;
use October\Rain\Router\Helper as RouterHelper;
use System\Helpers\DateTime as DateTimeHelper;
use System\Classes\PluginManager;
use Backend\Classes\ListColumn;
use Backend\Classes\WidgetBase;
use October\Rain\Database\Model;
use ApplicationException;
use DateTime;

/**
 * Cardlist Widget
 * Used for building back end cardlists, renders a list cards based on a model objects
 *
 * @package omc/publisher
 * @author Azahari Zaman
 */
class Cardlists extends WidgetBase
{
    
    //
    // Configurable properties
    //

    /**
     * @var array Cardlists column configuration.
     */
    public $columns;

    /**
     * @var Model Cardlist model object.
     */
    public $model;

    /**
     * @var string Link for each record row. Replace :id with the record id.
     */
    public $recordUrl;

    /**
     * @var string Click event for each record row. Replace :id with the record id.
     */
    public $recordOnClick;

    /**
     * @var string Message to display when there are no records in the list.
     */
    public $noRecordsMessage = 'backend::lang.list.no_records';

    /**
     * @var int Maximum cards to display for each page.
     */
    public $recordsPerPage;
    
    /**
     * @var bool|string Display pagination when limiting records per page.
     */
    public $showPagination = 'auto';
    
    protected $defaultAlias = 'cardlist';
    
    /**
     * @var array Model data collection.
     */
    protected $records;

    /**
     * @var int Current page number.
     */
    protected $currentPageNumber;
    
    /**
     * Initialize the widget, called by the constructor and free from its parameters.
     */
    public function init()
    {
        $this->fillFromConfig([
            'columns',
            'model',
            'recordUrl',
            'recordOnClick',
            'noRecordsMessage',
            'recordsPerPage',
            'showPagination',
        ]);

        /*
         * Configure the cardlist widget
         */
        $this->recordsPerPage = $this->getSession('per_page', $this->recordsPerPage);

        if ($this->showPagination == 'auto') {
            $this->showPagination = $this->recordsPerPage && $this->recordsPerPage > 0;
        }
        
        $this->validateModel();
    }
    
    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->addJs('js/october.list.js', 'core');
    }

    /**
     * Renders the widget.
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('cardlist-container');
    }
    
    /**
     * Prepares the cardlist data
     */
    public function prepareVars()
    {
        $this->vars['columns'] = $this->getVisibleColumns();
        $this->vars['records'] = $this->getRecords();
        $this->vars['noRecordsMessage'] = trans($this->noRecordsMessage);
        $this->vars['showPagination'] = $this->showPagination;

        if ($this->showPagination) {
            $this->vars['recordTotal'] = $this->records->total();
            $this->vars['pageCurrent'] = $this->records->currentPage();
            $this->vars['pageLast'] = $this->records->lastPage();
            $this->vars['pageFrom'] = $this->records->firstItem();
            $this->vars['pageTo'] = $this->records->lastItem();
        }
        else {
            $this->vars['recordTotal'] = $this->records->count();
            $this->vars['pageCurrent'] = 1;
        }
    }
    
    /**
     * Event handler for refreshing the list.
     */
    public function onRefresh()
    {
        $this->prepareVars();
        return ['#'.$this->getId() => $this->makePartial('cardlist')];
    }

    /**
     * Event handler for switching the page number.
     */
    public function onPaginate()
    {
        $this->currentPageNumber = post('page');
        return $this->onRefresh();
    }
    
    /**
     * Validate the supplied form model.
     * @return void
     */
    protected function validateModel()
    {
        if (!$this->model) {
            throw new ApplicationException(Lang::get(
                'backend::lang.list.missing_model',
                ['class'=>get_class($this->controller)]
            ));
        }

        if (!$this->model instanceof Model) {
            throw new ApplicationException(Lang::get(
                'backend::lang.model.invalid_class',
                ['model'=>get_class($this->model), 'class'=>get_class($this->controller)]
            ));
        }

        return $this->model;
    }
    
    /**
     * Replaces the @ symbol with a table name in a model
     * @param  string $sql
     * @param  string $table
     * @return string
     */
    protected function parseTableName($sql, $table)
    {
        return str_replace('@', $table.'.', $sql);
    }
    
    /**
     * Applies any filters to the model.
     */
    public function prepareModel()
    {
        $query = $this->model->newQuery();
        $primaryTable = $this->model->getTable();
        $selects = [$primaryTable.'.*'];
        $joins = [];
        $withs = [];

        /*
         * Extensibility
         */
        $this->fireSystemEvent('omc.publisher.cardlist.extendQueryBefore', [$query]);

        /*
         * Prepare searchable column names
         */
        $primarySearchable = [];
        $relationSearchable = [];

        $columnsToSearch = [];
        

        /*
         * Prepare related eager loads (withs) and custom selects (joins)
         */
        foreach ($this->getVisibleColumns() as $column) {

            if (!$this->isColumnRelated($column) || (!isset($column->sqlSelect) && !isset($column->valueFrom))) {
                continue;
            }

            if (isset($column->valueFrom)) {
                $withs[] = $column->relation;
            }

            $joins[] = $column->relation;
        }

        /*
         * Add eager loads to the query
         */
        if ($withs) {
            $query->with(array_unique($withs));
        }

        /*
         * Custom select queries
         */
        foreach ($this->getVisibleColumns() as $column) {
            if (!isset($column->sqlSelect)) {
                continue;
            }

            $alias = $query->getQuery()->getGrammar()->wrap($column->columnName);

            /*
             * Relation column
             */
            if (isset($column->relation)) {

                // @todo Find a way...
                $relationType = $this->model->getRelationType($column->relation);
                if ($relationType == 'morphTo') {
                    throw new ApplicationException('The relationship morphTo is not supported for list columns.');
                }

                $table =  $this->model->makeRelation($column->relation)->getTable();
                $sqlSelect = $this->parseTableName($column->sqlSelect, $table);

                /*
                 * Manipulate a count query for the sub query
                 */
                $relationObj = $this->model->{$column->relation}();
                $countQuery = $relationObj->getRelationExistenceQuery($relationObj->getRelated()->newQueryWithoutScopes(), $query);

                $joinSql = $this->isColumnRelated($column, true)
                    ? DbDongle::raw("group_concat(" . $sqlSelect . " separator ', ')")
                    : DbDongle::raw($sqlSelect);

                $joinSql = $countQuery->select($joinSql)->toSql();

                $selects[] = Db::raw("(".$joinSql.") as ".$alias);
            }
            /*
             * Primary column
             */
            else {
                $sqlSelect = $this->parseTableName($column->sqlSelect, $primaryTable);
                $selects[] = DbDongle::raw($sqlSelect . ' as '. $alias);
            }
        }

        
        

        /*
         * Add custom selects
         */
        $query->select($selects);

        /*
         * Extensibility
         */
        if ($event = $this->fireSystemEvent('omc.publisher.cardlist.extendQuery', [$query])) {
            return $event;
        }

        return $query;
    }
    
    /**
     * Returns all the records from the supplied model, after filtering.
     * @return Collection
     */
    protected function getRecords()
    {
        $model = $this->prepareModel();

        if ($this->showPagination) {
            $records = $model->paginate($this->recordsPerPage, $this->currentPageNumber);
        }
        else {
            $records = $model->get();
        }

        /*
         * Extensibility
         */
        if ($event = $this->fireSystemEvent('omc.publisher.cardlist.extendRecords', [&$records])) {
            $records = $event;
        }

        return $this->records = $records;
    }
    
    /**
     * Returns the record URL address for a card.
     * @param  Model $record
     * @return string
     */
    public function getRecordUrl($record)
    {
        if (isset($this->recordOnClick)) {
            return 'javascript:;';
        }

        if (!isset($this->recordUrl)) {
            return null;
        }

        $data = $record->toArray();
        $data += [$record->getKeyName() => $record->getKey()];

        $columns = array_keys($data);

        $url = RouterHelper::parseValues($data, $columns, $this->recordUrl);
        return Backend::url($url);
    }
    
    /**
     * Returns the onclick event for a list row.
     * @param  Model $record
     * @return string
     */
    public function getRecordOnClick($record)
    {
        if (!isset($this->recordOnClick)) {
            return null;
        }

        $columns = array_keys($record->getAttributes());
        $recordOnClick = RouterHelper::parseValues($record, $columns, $this->recordOnClick);
        return Html::attributes(['onclick' => $recordOnClick]);
    }
    
    /**
     * Get all the registered columns for the instance.
     * @return array
     */
    public function getColumns()
    {
        return $this->allColumns ?: $this->defineCardlistColumns();
    }

    /**
     * Get a specified column object
     * @param  string $column
     * @return mixed
     */
    public function getColumn($column)
    {
        return $this->allColumns[$column];
    }
    
    /**
     * Returns the cardlist columns that are visible by list settings or default
     */
    public function getVisibleColumns()
    {
        $definitions = $this->defineListColumns();
        $columns = [];

        /*
         * Supplied column list
         */
        if ($this->columnOverride === null) {
            $this->columnOverride = $this->getSession('visible', null);
        }

        if ($this->columnOverride && is_array($this->columnOverride)) {

            $invalidColumns = array_diff($this->columnOverride, array_keys($definitions));
            if (!count($definitions)) {
                throw new ApplicationException(Lang::get(
                    'backend::lang.list.missing_column',
                    ['columns'=>implode(',', $invalidColumns)]
                ));
            }

            $availableColumns = array_intersect($this->columnOverride, array_keys($definitions));
            foreach ($availableColumns as $columnName) {
                $definitions[$columnName]->invisible = false;
                $columns[$columnName] = $definitions[$columnName];
            }
        }
        /*
         * Use default column list
         */
        else {
            foreach ($definitions as $columnName => $column) {
                if ($column->invisible) {
                    continue;
                }

                $columns[$columnName] = $definitions[$columnName];
            }
        }

        return $this->visibleColumns = $columns;
    }
    
    /**
     * Builds an array of list columns with keys as the column name and values as a ListColumn object.
     */
    protected function defineListColumns()
    {
        if (!isset($this->columns) || !is_array($this->columns) || !count($this->columns)) {
            $class = get_class($this->model instanceof Model ? $this->model : $this->controller);
            throw new ApplicationException(Lang::get('backend::lang.list.missing_columns', compact('class')));
        }

        $this->addColumns($this->columns);

        /*
         * Extensibility
         */
        $this->fireSystemEvent('backend.list.extendColumns');

        /*
         * Use a supplied column order
         */
        if ($columnOrder = $this->getSession('order', null)) {
            $orderedDefinitions = [];
            foreach ($columnOrder as $column) {
                if (isset($this->allColumns[$column])) {
                    $orderedDefinitions[$column] = $this->allColumns[$column];
                }
            }

            $this->allColumns = array_merge($orderedDefinitions, $this->allColumns);
        }

        return $this->allColumns;
    }

    /**
     * Programatically add columns, used internally and for extensibility.
     * @param array $columns Column definitions
     */
    public function addColumns(array $columns)
    {
        /*
         * Build a final collection of list column objects
         */
        foreach ($columns as $columnName => $config) {
            $this->allColumns[$columnName] = $this->makeListColumn($columnName, $config);
        }
    }

    /**
     * Programatically remove a column, used for extensibility.
     * @param string $column Column name
     */
    public function removeColumn($columnName)
    {
        if (isset($this->allColumns[$columnName])) {
            unset($this->allColumns[$columnName]);
        }
    }
    
}