<?php


namespace Mikelmi\SmartTable\Engines;


use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Mikelmi\SmartTable\Contracts\SmartTableEngine;
use Mikelmi\SmartTable\Request;

class QueryBuilderEngine extends BaseEngine implements SmartTableEngine
{
    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var array
     */
    protected $mappedColumns;

    /**
     * @var string
     */
    protected $prefix;

    public function __construct(Builder $builder, Request $request)
    {
        $this->query = $builder;
        $this->request = $request;
        $this->columns = $this->query->columns;
        $this->prefix = $this->query->getGrammar()->getTablePrefix();
    }

    /**
     * @return mixed
     */
    public function results()
    {
        return $this->query->get();
    }

    /**
     * Get rows count
     *
     * @return int
     */
    public function count()
    {
        $connection = $this->query->getConnection();

        $myQuery = clone $this->query;

        // if its a normal query ( no union, having and distinct word )
        // replace the select with static text to improve performance
        if (! Str::contains(Str::lower($myQuery->toSql()), ['union', 'having', 'distinct', 'order by', 'group by'])) {
            $row_count = $connection->getQueryGrammar()->wrap('row_count');
            $myQuery->select($connection->raw("'1' as {$row_count}"));
        }

        return $connection->table($connection->raw('(' . $myQuery->toSql() . ') count_row_table'))
            ->setBindings($myQuery->getBindings())->count();
    }

    /**
     * Perform global search
     *
     * @return mixed
     */
    public function search()
    {
        $search = $this->request->getSearch();

        if (!$search || !$this->searchColumns) {
            return;
        }

        $columns = $this->searchColumns;
        $this->columnMapping();

        $this->query->where(
            function ($query) use($search, $columns, &$havings) {
                $keyword = '%'.$search.'%';
                foreach ($columns as $key) {
                    $column = $this->getColumnName($key);
                    if (in_array($key, $this->havingColumns)) {
                        //$query->orWhere($key, 'like', $keyword);
                        continue;
                    }
                    $query->orWhere($column, 'like', $keyword);
                }
            }
        );
    }

    /**
     * Perform column filtering
     *
     * @return mixed
     */
    public function filtering()
    {
        $filters = $this->request->getFilter();

        if ($filters) {
            $this->columnMapping();

            foreach ($filters as $key=>$keyword) {
                $keyword = '%'.$keyword.'%';
                $column = $this->getColumnName($key);
                if (in_array($key, $this->havingColumns)) {
                    $this->query->having($key, 'like', $keyword);
                    continue;
                }
                $this->query->where($column, 'like', $keyword);
            }
        }
    }

    /**
     * Perform ordering
     *
     * @return mixed
     */
    public function ordering()
    {
        foreach ($this->request->getOrder() as $key=>$reverse) {
            $this->query->orderBy($key, $reverse ? 'desc':'asc');
        }
    }

    /**
     * Perform pagination
     *
     * @return mixed
     */
    public function paging()
    {
        $this->query->skip($this->request->getStart())
            ->take($this->request->getCount());
    }

    public function __call($name, $arguments)
    {
        call_user_func_array([$this->query, $name], $arguments);

        return $this;
    }

    protected function columnMapping() {
        if (!$this->mappedColumns) {
            $res = [];
            foreach ($this->columns as $column) {
                $name = (string)$column;
                preg_match('#^(.+)\s+as\s+(.+)$#si', $name, $matches);
                if (!empty($matches)) {
                    $res[$matches[2]] = $matches[1];
                } elseif (strpos($name, '.')) {
                    $array = explode('.', $name);
                    $res[array_pop($array)] = $name;
                } else {
                    $res[$name] = $name;
                }
            }

            $this->mappedColumns = $res;
        }

        return $this->mappedColumns;
    }

    protected function getColumnName($name)
    {
        return array_get($this->mappedColumns, $name, $name);
    }

    /**
     * Will prefix column if needed.
     *
     * @param string $column
     * @return string
     */
    protected function prefixColumn($column)
    {
        $table_names = $this->tableNames();
        if (count(
            array_filter($table_names, function ($value) use (&$column) {
                return strpos($column, $value . '.') === 0;
            })
        )) {
            // the column starts with one of the table names
            $column = $this->prefix . $column;
        }
        return $column;
    }

    /**
     * Will look through the query and all it's joins to determine the table names.
     *
     * @return array
     */
    public function tableNames()
    {
        $names          = [];
        $query          = $this->query;
        $names[]        = $query->from;
        $joins          = $query->joins ?: [];
        $databasePrefix = $this->prefix;
        foreach ($joins as $join) {
            $table   = preg_split('/ as /i', $join->table);
            $names[] = $table[0];
            if (isset($table[1]) && ! empty($databasePrefix) && strpos($table[1], $databasePrefix) == 0) {
                $names[] = preg_replace('/^' . $databasePrefix . '/', '', $table[1]);
            }
        }
        return $names;
    }
}