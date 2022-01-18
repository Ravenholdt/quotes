<?php

namespace Loek;

use JsonSerializable;

class ReturnInfo implements JsonSerializable
{

    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $quote;

    /**
     * @var string
     */
    public string $context;

    /**
     * @var int
     */
    public int $rating;

    /**
     * @var int
     */
    public int $matches;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'quote' => $this->quote,
            'context' => $this->context,
            'rating' => $this->rating,
            'matches' => $this->matches
        ];
    }
}