<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Stripe\Collection;
use Stripe\Price as StripePrice;

class Price implements Arrayable, Jsonable, JsonSerializable
{
    public function __construct(public StripePrice $price) {}

    /**
     * Retrieve all Stripe prices.
     *
     * @param  array|null  $params  Optional parameters for filtering the prices.
     * @param  array|null  $opts  Optional request options.
     */
    public static function all(?array $params = null, ?array $opts = null): Collection
    {
        return Redbird::stripe()->prices->all($params ?? [], $opts ?? []);
    }

    /**
     * Creates a new Price instance using the provided attributes.
     *
     * This method interacts with the Stripe API to create a new price object
     * based on the given attributes, then returns a new instance of this class
     * wrapping the created price.
     *
     * @param  array  $attributes  The attributes to be used for creating the price.
     * @return self A new instance of the class containing the created price.
     */
    public static function create(array $attributes): self
    {
        $price = Redbird::stripe()->prices->create($attributes);

        return new self($price);
    }

    /**
     * Retrieves the product ID associated with the price.
     *
     * @return string The product ID.
     */
    public function productId(): string
    {
        return $this->price->product;
    }

    /**
     * Get the price amount as a float.
     *
     * This method checks if the 'unit_amount' property exists and is numeric on the associated price object.
     * If so, it returns the value divided by 100 (to convert from cents to dollars, for example).
     * If not, it returns 0.0.
     *
     * @return float The price amount as a float.
     */
    public function amount(): float
    {
        return isset($this->price->unit_amount) && is_numeric($this->price->unit_amount)
            ? $this->price->unit_amount / 100
            : 0.0;
    }

    /**
     * Returns the currency code of the price in uppercase.
     *
     * @return string The uppercase currency code.
     */
    public function currency(): string
    {
        return strtoupper($this->price->currency);
    }

    /**
     * Determine if the price is currently active.
     *
     * @return bool Returns true if the price is active, false otherwise.
     */
    public function isActive(): bool
    {
        return $this->price->active;
    }

    /**
     * Get the unique identifier of the price.
     *
     * @return string The ID of the price.
     */
    public function id(): string
    {
        return $this->price->id;
    }

    /**
     * Retrieves the nickname associated with the price.
     *
     * @return string|null The nickname of the price, or null if not set.
     */
    public function nickname(): ?string
    {
        return $this->price->nickname;
    }

    /**
     * Determines if the price is recurring.
     *
     * Checks whether the 'recurring' property exists and is not empty
     * on the associated price object.
     *
     * @return bool True if the price is recurring, false otherwise.
     */
    public function isRecurring(): bool
    {
        return isset($this->price->recurring) && ! empty($this->price->recurring);
    }

    /**
     * Converts the Price object to an associative array.
     *
     * @return array{
     *     id: mixed,
     *     amount: mixed,
     *     nickname: mixed,
     *     currency: mixed,
     *     recurring: string|null,
     *     type: mixed,
     *     active: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'amount' => $this->amount(),
            'nickname' => $this->nickname(),
            'currency' => $this->currency(),
            'recurring' => $this->price->recurring?->interval ?? null,
            'type' => $this->price->type,
            'active' => $this->isActive(),
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * This method is called when an instance of this class is encoded with json_encode().
     * It returns the object's data as an array, typically by delegating to the toArray() method.
     *
     * @return array The data to be serialized by json_encode().
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options  Bitmask options for json_encode, such as JSON_PRETTY_PRINT.
     * @return string JSON encoded string.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
