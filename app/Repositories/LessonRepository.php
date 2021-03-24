<?php

namespace App\Repositories;

use App\Models\Lesson;
use App\Repositories\BaseRepository;

/**
 * Class LessonRepository
 * @package App\Repositories
 * @version June 6, 2020, 1:36 pm UTC
*/

class LessonRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'video_link',
        'zoom_link',
        'telegram_link',
        'homework_link',
        'course_id'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Lesson::class;
    }
}
