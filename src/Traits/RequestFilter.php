<?php
namespace BSProxy\Traits;


class RequestFilter
{
    private $filters = [];
    private $sort = [];
    private $page = [];

    public static function factory(){
        return new self;
    }
    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param  array  $sort
     */
    public function setSort(array $sort): RequestFilter
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return array
     */
    public function getPage(): array
    {
        return $this->page;
    }

    /**
     * @param  array  $page
     */
    public function setPage(array $page): RequestFilter
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param  array  $filters
     *
     * @return RequestFilter
     */
    public function setFilters(array $filters): RequestFilter
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Encode a value as JSON.
     *
     * @param  int  $opt
     *
     * @return string
     * @throws \JsonException
     */
    public function encode($opt = 0): string
    {
        $opt |= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        return \json_encode([
            'filter' => [
                'filters' => $this->filters,
                'page' => $this->page,
                'sort' => $this->sort
            ],
        ], JSON_THROW_ON_ERROR | $opt);
    }
}