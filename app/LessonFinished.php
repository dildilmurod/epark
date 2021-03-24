<?php

namespace App;

use Illuminate\Database\Eloquent\Model as Model;

class LessonFinished extends Model
{

    protected $fillable = [
        'user_id', 'course_id', 'lesson_id',
    ];

    protected $hidden = [];

    public function course(){
        return $this->belongsTo('App\Models\Course');
    }

    public function lesson(){
        return $this->belongsTo('App\Models\Lesson');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }


}
