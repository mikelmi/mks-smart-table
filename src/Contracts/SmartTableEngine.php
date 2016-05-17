<?php


namespace Mikelmi\SmartTable\Contracts;


use Illuminate\Http\JsonResponse;

interface SmartTableEngine
{

    /**
     * @return mixed
     */
    public function results();

    /**
     * Get rows count
     *
     * @return int
     */
    public function count();

    /**
     * Perform global search
     *
     * @return mixed
     */
    public function search();

    /**
     * Perform column filtering
     *
     * @return mixed
     */
    public function filtering();

    /**
     * Perform ordering
     *
     * @return mixed
     */
    public function ordering();

    /**
     * Perform pagination
     *
     * @return mixed
     */
    public function paging();

    /**
     * Set columns for global search;
     *
     * @param array $columns
     * @return SmartTableEngine
     */
    public function setSearchColumns(array $columns);

    public function setHavingColumns(array $columns);

    /**
     * Apply filters, orders
     * @return SmartTableEngine
     */
    public function apply();

    /**
     * Return json response of filtered and ordered results
     *
     * @return JsonResponse
     */
    public function response();
}