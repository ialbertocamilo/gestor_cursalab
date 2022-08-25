<?php

namespace App\Models;

class SummaryUser extends Summary
{
    protected $table = 'summary_users';

    protected $fillable = [
        'last_time_evaluated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    // protected function setUserLastTimeEvaluation($user = NULL)
    // {
    //     $user = $user ?? auth()->user;

    //     return SummaryUser::where('user_id', $user->id)
    //         ->update(['last_time_evaluated_at' => now()]);
    // }

    // protected function incrementUserAttempts($user = null)
    // {
    //     $user = $user ?? auth()->user;

    //     $row = SummaryUser::select('id', 'attempts')
    //             ->where('user_id', $user->id)
    //             ->first();

    //     if ($row) return $row->update(['attempts' => $row->attempts + 1]);

    //     return SummaryUser::storeData($course, $user);
    // }

    // protected function storeData($user = null)
    // {
    //     $user = $user ?? auth()->user;
    //     // $assigneds = $course->topics->where('active', ACTIVE)->count();

    //     return SummaryUser::create([
    //         'user_id' => $user->id,
    //         'assigneds' => $assigneds,
    //         'attempts' => 1,
    //         'last_time_evaluated_at' => now(),
    //         // 'libre' => $curso->libre,
    //         // 'status_id' => 'desarrollo'
    //     ]);
    // }
}
