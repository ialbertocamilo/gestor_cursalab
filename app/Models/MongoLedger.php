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
    protected $casts = [
        'properties' => 'json',
        'modified' => 'json',
        'pivot' => 'json',
        'extra' => 'json',
    ];

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
