<?php


namespace Mikelmi\SmartTable;



use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Mikelmi\SmartTable\Engines\CollectionEngine;
use Mikelmi\SmartTable\Engines\EloquentEngine;
use Mikelmi\SmartTable\Engines\QueryBuilderEngine;

class SmartTable
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * SmartTable constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request->request->count() ? $request : Request::capture();
    }

    /**
     * @param QueryBuilder|EloquentEngine|Collection $builder
     * @return CollectionEngine|EloquentEngine|QueryBuilderEngine
     */
    public static function make($builder)
    {
        /** @var SmartTable $self */
        $self = app(self::class);

        if ($builder instanceof QueryBuilder) {
            return new QueryBuilderEngine($builder, $self->request);
        }

        if ($builder instanceof EloquentBuilder) {
            return new EloquentEngine($builder, $self->request);
        }

        if ($builder instanceof Collection) {
            return new CollectionEngine($builder, $self->request);
        }

        throw new \InvalidArgumentException("'{$builder}' must be instance of Collection or QueryBuilder or EloquentBuilder");
    }
}