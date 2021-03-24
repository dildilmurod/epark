<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class CourseFeature
 * @package App\Models
 * @version August 9, 2020, 12:52 pm +05
 *
 * @property string $title
 * @property string $description
 * @property string $icon
 * @property integer $course_id
 */
class CourseFeature extends Model
{

    public $table = 'course_features';




    public $fillable = [
        'title',
        'description',
        'icon',
        'course_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'icon' => 'string',
        'course_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required'
    ];

    public function course(){
        return $this->belongsTo('App\Models\Course');
    }


}
