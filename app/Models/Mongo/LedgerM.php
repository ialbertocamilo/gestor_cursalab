<?php

namespace App\Models\Mongo;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
class LedgerM extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ledgers';
    protected $casts = ['created_at' => 'datetime'];
    public static function store(){
        DB::table('ledgers')->chunkById(10000, function ($ledgers_chunk){
            $i = 0;
            foreach ($ledgers_chunk as $ledger) {
                $i++;
                $ledger->_id = new ObjectId();
                $ledger->properties = json_decode($ledger->properties);
                $ledger->modified = json_decode($ledger->modified);
                self::insert((array) $ledger);
            }
        });
    }
    public static function countByTopic($date)
    {
        return self::
        raw(function($collection) use($date)
        {
            return $collection->aggregate([
                [
                    '$match' => [
                        'modified'=>[
                            '$in'=>['views']
                        ],
                        'created_at' => [
                            '$regex' => '^'.$date
                        ]
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$properties.topic_id',
                        'count' => ['$sum' => 1],
                    ],
                ]
            ]);
        });
    }
}