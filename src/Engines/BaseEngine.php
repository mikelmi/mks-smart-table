<?php


namespace Mikelmi\SmartTable\Engines;


use Illuminate\Http\JsonResponse;
use Mikelmi\SmartTable\Contracts\SmartTableEngine;
use Mikelmi\SmartTable\Request;

abstract class BaseEngine implements SmartTableEngine
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $applied = false;

    /**
     * @var array
     */
    protected $searchColumns = [];

    /**
     * @var array
     */
    protected $havingColumns = [];

    /**
     * Set columns for global search;
     *
     * @param array $columns
     * @return SmartTableEngine
     */
    public function setSearchColumns(array $columns)
    {
        $this->searchColumns = $columns;

        return $this;
    }

    public function setHavingColumns(array $columns)
    {
        $this->havingColumns = $columns;

        return $this;
    }

    /**
     * Apply filters, orders
     * @return SmartTableEngine
     */
    public function apply()
    {
        if (!$this->applied) {
            $this->search();
            $this->filtering();
            $this->ordering();

            $this->applied = true;
        }

        return $this;
    }

    /**
     * Return json response of filtered and ordered results
     *
     * @return JsonResponse
     */
    public function response()
    {
        $this->apply();

        $count = $this->count();
        $pages = ceil($count / $this->request->getCount());
        $this->paging();

        $result = [
            'data' => $this->results(),
            'pages' => $pages,
            'total' => $count
        ];

        return response()->json($result);
    }
}