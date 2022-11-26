<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Requirement extends BaseModel
{
    protected $table = 'requirements';

    protected $fillable = [
        'model_type', 'model_id', 'requirement_type', 'requirement_id'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function requirement()
    {
        return $this->morphTo();
    }

    public function summaries_course()
    {
        return $this->hasMany(SummaryCourse::class, 'course_id', 'requirement_id');
    }

    public function summaries_topics()
    {
        return $this->hasMany(SummaryTopic::class, 'topic_id', 'requirement_id');
    }

    protected function storeRequest($data, $requirement = null)
    {
        try {

            DB::beginTransaction();
            $response = null;
            if ($requirement) :
                Requirement::where('id', $requirement->id)->update($data);
            else :
                $response = self::create($data);
                $response->save();
            endif;

            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
}
