<?php

namespace App\Models;

class SummaryUser extends BaseModel
{
    protected $fillable = [
        'last_time_evaluated_at',
    ];

    // public function course()
    // {
    //     return $this->belongsTo(Course::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function setUserLastTimeEvaluation($user = NULL)
    {
        $user = $user ?? auth()->user;

        return SummaryUser::where('user_id', $user->id)
            ->update([
                'last_time_evaluated_at' => now(),
            ]);
    }

    protected function incrementUserAttempts($course, $user = null)
    {
        $user = $user ?? auth()->user;

        $row = SummaryUser::select('id', 'attempts')
                ->where('user_id', $user->id)
                ->first();

        if ($row) return $row->update(['attempts' => $row->attempts + 1]);

        return SummaryUser::storeData($course, $user);
    }

    protected function storeData($user = null)
    {
        $user = $user ?? auth()->user;
        // $assigneds = $course->topics->where('active', ACTIVE)->count();

        return SummaryUser::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'assigneds' => $assigneds,
            'attempts' => 1,
            // 'libre' => $curso->libre,
            // 'status_id' => 'desarrollo'
        ]);
    }
}
