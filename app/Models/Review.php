<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Review
 * @package App\Models
 * @version August 9, 2020, 1:12 pm +05
 *
 * @property string $name
 * @property string $body
 * @property string $avatar
 * @property integer $course_id
 */
class Review extends Model
{

    public $table = 'reviews';




    public $fillable = [
        'name',
        'body',
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
        'body' => 'string',
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
        return $this->belongsTo('App\Models\Course');
    }


}
