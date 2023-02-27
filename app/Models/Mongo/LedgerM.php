<?php

namespace App\Models\Mongo;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class LedgerM extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ledgers';
    protected $casts = ['created_at' => 'datetime'];
    public static function store(){
        DB::table('ledgers')->chunkById(10, function ($ledgers_chunk){
            foreach ($ledgers_chunk as $ledger) {
                $ledger->properties = json_decode($ledger->properties);
                $ledger->modified = json_decode($ledger->modified);
            }
            dd($ledgers_chunk);
            self::insert((array) $ledgers_chunk);
            dd('entro');
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