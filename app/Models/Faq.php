<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Faq
 * @package App\Models
 * @version August 9, 2020, 1:03 pm +05
 *
 * @property string $question
 * @property string $answer
 * @property integer $course_id
 */
class Faq extends Model
{

    public $table = 'faqs';




    public $fillable = [
        'question',
        'answer',
        'course_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'question' => 'string',
        'answer' => 'string',
        'course_id' => 'integer'
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


}
