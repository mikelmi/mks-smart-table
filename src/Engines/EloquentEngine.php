<?php


namespace Mikelmi\SmartTable\Engines;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mikelmi\SmartTable\Contracts\SmartTableEngine;
use Mikelmi\SmartTable\Request;

class EloquentEngine extends QueryBuilderEngine implements SmartTableEngine
{
    /**
     * @var Model
     */
    protected $model;

    public function __construct($model, Request $request)
    {
        $this->request = $request;
        $this->query = $model instanceof Builder ? $model->getQuery() : $model;
        $this->model = $model;
        $this->columns = $this->query->columns;
        $this->prefix = $this->query->getGrammar()->getTablePrefix();
    }

    /**
     * @return mixed
     */
    public function results()
    {
        return $this->model->get()->toArray();
    }
}