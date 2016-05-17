<?php


namespace Mikelmi\SmartTable\Engines;


use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mikelmi\SmartTable\Contracts\SmartTableEngine;
use Mikelmi\SmartTable\Request;

class CollectionEngine extends BaseEngine implements SmartTableEngine
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * CollectionEngine constructor.
     * @param Collection $collection
     * @param Request $request
     */
    public function __construct(Collection $collection, Request $request)
    {
        $this->collection = $collection;
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function results()
    {
        return $this->collection->all();
    }

    /**
     * Get rows count
     *
     * @return int
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * Perform global search
     *
     * @return mixed
     */
    public function search()
    {
        $search = $this->request->getSearch();

        if ($search && $this->searchColumns) {
            $this->filterColumns(array_fill_keys($this->searchColumns, $search));
        }
    }

    protected function filterColumns($filters)
    {
        foreach($filters as $key=>$filter) {
            $this->collection = $this->collection->filter(
                function ($row) use ($key, $filter) {
                    return strpos(Str::lower($row[$key]), Str::lower($filter)) !== false;
                }
            );
        }
    }

    /**
     * Perform column filtering
     *
     * @return mixed
     */
    public function filtering()
    {
        $this->filterColumns($this->request->getFilter());
    }

    /**
     * Perform ordering
     *
     * @return mixed
     */
    public function ordering()
    {
        foreach ($this->request->getOrder() as $key=>$reverse) {
            $this->collection = $this->collection->sortBy(
                function ($row) use ($key) {
                    return $row[$key];
                }
                , SORT_NATURAL, $reverse);
        }
    }

    /**
     * Perform pagination
     *
     * @return mixed
     */
    public function paging()
    {
        $this->collection = $this->collection->slice(
            $this->request->getStart(),
            $this->request->getCount()
        );
    }
}