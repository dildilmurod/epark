<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;

/**
 * Class Partner
 * @package App\Models
 * @version August 9, 2020, 1:00 pm +05
 *
 * @property string $title
 * @property string $logo
 * @property integer $course_id
 */
class Partner extends Model
{

    public $table = 'partners';




    public $fillable = [
        'title',
        'logo',
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
        'logo' => 'string',
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
        return $this->belongsToMany('App\Models\Course', 'course_partner', 'partner_id', 'course_id' );
    }


}
