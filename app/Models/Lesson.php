<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Lesson
 * @package App\Models
 * @version June 6, 2020, 1:36 pm UTC
 *
 * @property string $title
 * @property string $video_link
 * @property string $zoom_link
 * @property string $telegram_link
 * @property string $homework_link
 * @property integer $course_id
 */
class Lesson extends Model
{

    public $table = 'lessons';




    public $fillable = [
        'title',
        'description',
        'full_description',
        'video_link',
        'zoom_link',
        'telegram_link',
        'homework_link',
        'course_id',
        'number'
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
        'full_description' => 'string',
        'video_link' => 'string',
        'zoom_link' => 'string',
        'telegram_link' => 'string',
        'homework_link' => 'string',
        'course_id' => 'integer',
        'number' => 'integer'

    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function course(){
        return $this->belongsTo('App\Models\Course');
    }

    public function lesson_finished()
    {
        return $this->hasMany('App\LessonFinished');
    }


}
