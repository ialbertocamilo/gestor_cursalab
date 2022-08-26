<?php

namespace App\Models;

class SummaryCourse extends Summary
{
    protected $table = 'summary_courses';

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

    // protected function setUserLastTimeEvaluation($course, $user = NULL)
    // {
    //     $user = $user ?? auth()->user;

    //     return SummaryCourse::where('user_id', $user->id)
    //             ->where('course_id', $course->id)
    //             ->update(['last_time_evaluated_at' => now()]);
    // }

    // protected function incrementUserAttempts($course, $setLastTimeEvaluation, $user = null)
    // {
    //     $user = $user ?? auth()->user;

    //     $row = SummaryCourse::select('id', 'attempts')
    //             ->where('user_id', $user->id)
    //             ->where('course_id', $course->id)
    //             ->first();

    //     $data = ['attempts' => $row->attempts + 1];

    //     if ($setLastTimeEvaluation)
    //         $data['last_time_evaluated_at'] = now();

    //     if ($row) return $row->update($data);

    //     return SummaryCourse::storeData($course, $user);
    // }

    // protected function storeData($course, $user = null)
    // {
    //     $user = $user ?? auth()->user;
    //     $assigneds = $course->topics->where('active', ACTIVE)->count();

    //     return SummaryCourse::create([
    //         'user_id' => $user->id,
    //         'course_id' => $course->id,
    //         'assigneds' => $assigneds,
    //         'attempts' => 1,
    //         'last_time_evaluated_at' => now(),
    //         // 'libre' => $curso->libre,
    //         // 'status_id' => 'desarrollo'
    //     ]);
    // }

    public static function resetMasiveAttempts($coursesIds, $userId)
    {
        self::whereIn('course_id', $coursesIds)
            ->where('user_id', $userId)
            ->update([
                'attempts' => 0,
                //'fuente' => 'resetm'
            ]);
    }
}
