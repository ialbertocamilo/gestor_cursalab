<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;

class MongoLedger extends Model implements \Altek\Accountant\Contracts\Ledger
{
    use \Altek\Accountant\Ledger;

    /**
     * {@inheritdoc}
     */

    protected $connection = 'mongodb';

    protected $table = 'ledgers';

    /**
     * {@inheritdoc}
     */

    /**
     * {@inheritdoc}
     */
    public function recordable(): ?MorphTo
    {
        return $this->morphTo();
    }

    /**
     * {@inheritdoc}
     */
    public function user(): ?MorphTo
    {   
        return $this->morphTo();
    }

    protected function getDecipheredProperties(bool $strict = false) 
    {
        $properties = $this->getAttributeValue('properties');
        $properties = is_string($properties) ? json_decode($properties) : $properties;

        foreach (
            $this->getRecordableInstance()->getCiphers()
            as $key => $implementation
        ) {
            if (!\array_key_exists($key, $properties)) {
                throw new AccountantException(
                    \sprintf('Invalid property: "%s"', $key)
                );
            }

            if (!\is_subclass_of($implementation, Cipher::class)) {
                throw new AccountantException(
                    \sprintf(
                        'Invalid Cipher implementation: "%s"',
                        $implementation
                    )
                );
            }

            // If strict mode is on, an exception is thrown when there's an attempt to decipher
            // one way ciphered data, otherwise we just skip to the next property value
            if (!$strict && \call_user_func([$implementation, 'isOneWay'])) {
                continue;
            }

            $properties[$key] = \call_user_func(
                [$implementation, 'decipher'],
                $properties[$key]
            );
        }

        return $properties ?? [];
    }

    // public function user(): ?MorphTo
    // {
    //     $userPrefix = Config::get('accountant.user.prefix');

    //     $relation = $this->morphTo(
    //         $userPrefix,
    //         $userPrefix . '_type',
    //         $userPrefix . '_id'
    //     );

    //     return $this->resolveTrashedRelation($relation);
    // }
}
