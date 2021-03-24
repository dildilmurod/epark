<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Course
 * @package App\Models
 * @version June 6, 2020, 1:29 pm UTC
 *
 * @property string $title
 * @property string $description
 */
class Course extends Model
{

    public $table = 'courses';




    public $fillable = [
        'title',
        'description',
        'month',
        'theme_color',
        'photo',
        'status',
        'show_landing'
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
        'month' => 'integer',
        'url' => 'integer',
        'theme_color' => 'string',
        'photo' => 'string',
        'status' => 'integer',
        'show_landing' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function lesson()
    {
        return $this->hasMany('App\Models\Lesson');//->orderBy('number', 'asc');
    }

    public function course_feature()
    {
        return $this->hasMany('App\Models\CourseFeature');
    }

    public function faq()
    {
        return $this->hasMany('App\Models\Faq');
    }

    public function review()
    {
        return $this->hasMany('App\Models\Review');
    }

    public function lesson_finished()
    {
        return $this->hasMany('App\LessonFinished');
    }

    public function users(){
        return $this->belongsToMany('App\User', 'course_user', 'course_id', 'user_id');
    }

    public function teachers(){
        return $this->belongsToMany('App\Models\Teacher', 'course_teacher', 'course_id', 'teacher_id');
    }

    public function partners(){
        return $this->belongsToMany('App\Models\Partner', 'course_partner', 'course_id', 'partner_id');
    }








}
