<?php

namespace App\Repositories;

use App\Models\CourseFeature;
use App\Repositories\BaseRepository;

/**
 * Class CourseFeatureRepository
 * @package App\Repositories
 * @version August 9, 2020, 12:52 pm +05
*/

class CourseFeatureRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'description',
        'icon',
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
        return CourseFeature::class;
    }
}
