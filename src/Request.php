<?php


namespace Mikelmi\SmartTable;


class Request extends \Illuminate\Http\Request
{
    /**
     * Get columns ordering
     *
     * @return array
     */
    public function getOrder()
    {
        $predicate = $this->input('sort.predicate');

        if (!$predicate) {
            return [];
        }

        $reverse = $this->input('sort.reverse', false);

        if ($reverse === 'false' || $reverse === "0") {
            $reverse = false;
        }

        return [$predicate => $reverse];
    }

    /**
     * Get start pagination
     *
     * @return int
     */
    public function getStart()
    {
        return (int)$this->input('start', 0);
    }

    /**
     * Get count per page
     *
     * @return int
     */
    public function getCount()
    {
        $count = (int)$this->input('number', 10);

        return $count > 0 ? $count : 10;
    }

    /**
     * Get columns filter
     *
     * @return array
     */
    public function getFilter()
    {
        return array_except((array) $this->input('search', []), '$');
    }

    /**
     * Get global search
     *
     * @return mixed
     */
    public function getSearch()
    {
        return $this->input('search.$');
    }
}