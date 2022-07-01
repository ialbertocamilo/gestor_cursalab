<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use DB;

class ExternalDatabase extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect($db_data)
    {
        return new OTFConnection($db_data);
    }

    protected function insertMigrationData($db_config, $data)
    {
        $db = self::connect($db_config);

        $this->insertUsersData($db, $data);

        $this->insertSchoolsData($db, $data);

        $this->insertCoursesData($db, $data);

        $this->insertCourseSchoolData($db, $data);

        $this->insertTopicsData($db, $data);

        $this->insertQuestionData($db, $data);
    }

    public function insertChunkedData($db, $data, $table_name)
    {
        foreach ($data as $chunk)
            $db->getTable($table_name)->insert($chunk);
    }

    public function insertUsersData($db, $data)
    {
        $this->insertChunkedData($db, $data['users'], 'users');
    }

    public function insertSchoolsData($db, $data)
    {
        $this->insertChunkedData($db, $data['schools'], 'schools');
    }

    public function insertCoursesData($db, $data)
    {
        $this->insertChunkedData($db, $data['courses'], 'courses');
    }

    public function insertTopicsData($db, $data)
    {
        $courses = $db->getTable('courses')->get();

        $temp = [];

        foreach ($data['topics'] as $topic) {
            $course = $courses->where('external_id', $topic['curso_id'])->first();
            unset($topic['curso_id']);

            $temp[] = array_merge($topic, ['course_id' => $course->id ?? null]);
        }

        $chunk = array_chunk($temp, self::CHUNK_LENGTH, true);

        $this->insertChunkedData($db, $chunk, 'topics');
    }

    public function insertCourseSchoolData($db, $data)
    {
        $courses = $db->getTable('courses')->get();
        $schools = $db->getTable('schools')->get();

        $temp = [];
        foreach ($data['course_school'] as $course_school) {
            $course = $courses->where('external_id', $course_school['curso_id'])->first();
            $school = $schools->where('external_id', $course_school['categoria_id'])->first();
            if ($course && $school) {
                $temp[] = [
                    'course_id' => $course->id,
                    'school_id' => $school->id
                ];
            }
        }

        $chunk = array_chunk($temp, self::CHUNK_LENGTH, true);

        $this->insertChunkedData($db, $chunk, 'course_school');
    }

    public function insertQuestionData($db, $data)
    {
        $topics = $db->getTable('topics')->get();

        foreach ($data['questions'] as $question) {
            $topic = $topics->where('external_id', $question['post_id'])->first();
            $options = $question['rptas_json'];
            unset($question['post_id'], $question['rptas_json']);

            if ($topic) {
                $temp_question = array_merge($question, ['model_id' => $topic->id]);

                $db->getTable('questions')->insert($temp_question);

                $this->insertOptionsData($db, array_merge($question, ['rptas_json' => $options]));
            }
        }
    }

    public function insertOptionsData($db, $data)
    {
        $now = now()->format('Y-m-d H:i:s');

        $options = json_decode($data['rptas_json'] ?? [], true);

        $position = 1;
        $temp = [];
        foreach ($options as $option) {
            $temp[] = [
                'question_id' => $data['external_id'],

                'statement' => $option['opc'],
                'position' => $position,

                'is_correct' => $option['correcta'],

                'active' => ACTIVE,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $position++;
        }

        $db->getTable('options')->insert($temp);
    }


//    // CHANNEL
//
//    public function setExternalDatabaseConnection()
//    {
//        return new OTFConnection($this->settings['database']);
//    }

}
