<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Teacher
 * @package App\Models
 * @version August 9, 2020, 12:56 pm +05
 *
 * @property string $name
 * @property string $position
 * @property string $avatar
 */
class Teacher extends Model
{

    public $table = 'teachers';




    public $fillable = [
        'name',
        'position',
        'avatar',
        'course_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'position' => 'string',
        'avatar' => 'string',
        'course_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required'
    ];

    public function course(){
        return $this->belongsToMany('App\Models\Course', 'course_teacher', 'teacher_id', 'course_id' );
    }


}
