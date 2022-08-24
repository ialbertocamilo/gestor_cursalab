<?php

namespace App\Models;

class SummaryCourse extends BaseModel
{
    protected $fillable = [
        'last_time_evaluated_at', 'user_id', 'course_id', 'assigneds', 'attempts'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function setUserLastTimeEvaluation($course, $user = NULL)
    {
        $user = $user ?? auth()->user;

        return SummaryCourse::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->update([
                    'last_time_evaluated_at' => now(),
                ]);
    }

    protected function incrementUserAttempts($course, $user = null)
    {
        $user = $user ?? auth()->user;

        $row = SummaryCourse::select('id', 'attempts')
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

        if ($row) return $row->update(['attempts' => $row->attempts + 1]);

        return SummaryCourse::storeData($course, $user);
    }

    protected function storeData($course, $user = null)
    {
        $user = $user ?? auth()->user;
        $assigneds = $course->topics->where('active', ACTIVE)->count();

        return SummaryCourse::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'assigneds' => $assigneds,
            'attempts' => 1,
            // 'libre' => $curso->libre,
            // 'status_id' => 'desarrollo'
        ]);
    }
}
