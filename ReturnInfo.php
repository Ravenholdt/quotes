<?php

namespace Loek;
use StdClass;

class ReturnInfo
{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $quote;

    /**
     * @var string
     */
    public $context;

    /**
     * @var int
     */
    public $rating;

    /**
     * @var int
     */
    public $matches;

    public function __construct(StdClass $data)
    {
        $this->id = (int)$data->id;
        $this->quote = $data->quote;
        $this->context = $data->context;
        $this->rating = (int)$data->rating;
        $this->matches = (int)$data->matches;
    }
}