<?php

namespace App\Http\Controllers;

use App\GenName;
use App\Http\Requests\API\CreateCourseAPIRequest;
use App\Http\Requests\API\UpdateCourseAPIRequest;
use App\Models\Course;
use App\Repositories\CourseRepository;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Response;

/**
 * Class CourseController
 * @package App\Http\Controllers\API
 */
class CourseAPIController extends AppBaseController
{
    /** @var  CourseRepository */
    private $courseRepository;

    public function __construct(CourseRepository $courseRepo)
    {
        $this->middleware('admin', ['except' => ['index', 'show', 'course_enroll', 'my_courses','show_back', 'course_by_title']]);
        $this->middleware('api-auth', ['except' => ['index', 'show', 'show_back', 'course_by_title']]);

        $this->courseRepository = $courseRepo;
    }

    /**
     * Display a listing of the Course.
     * GET|HEAD /courses
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // $courses = Course::where('status', 1)->get();
        // if(auth('api')->check()){
        //     if(auth('api')->user()->role_id == 'admin'){
        //         $courses = $this->courseRepository->all(
        //             $request->except(['skip', 'limit']),
        //             $request->get('skip'),
        //             $request->get('limit')
        //         );
        //     }
        // }
        $courses = $this->courseRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courses->toArray(), 'Courses retrieved successfully');
    }

    /**
     * Store a newly created Course in storage.
     * POST /courses
     *
     * @param CreateCourseAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateCourseAPIRequest $request)
    {
        $input = $request->all();

        $photo = $request->file('photo');
        if ($photo) {
            $gen = new GenName();
            $fileToStore = $gen->generate($photo, $request->title);
            $photo->move('coursephoto', $fileToStore);
            $input['photo'] = '/coursephoto/' . $fileToStore;
        }
        if(!$request->has('url')) {
            $input['url'] = preg_replace('/\s+/', '_', $input['title']);
        }

        $course = $this->courseRepository->create($input);
        if($request->has('status')){
            if($request->status == 1){
                $users = User::all();

                if ($users) {
                    Notification::send($users, new \App\Notifications\NewAction("Новый курс: " . $course->title));   //multiple users
                }
            }
        }


        return $this->sendResponse($course->toArray(), 'Course saved successfully');
    }

    public function course_notification(Request $request, $id)
    {
        $input = $request->all();
        $this->validate($request, [
            'message' => 'required',
        ]);

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }
        $users = $course->users;
        $emails = [];
        foreach ($users as $user){
            if(!empty($user->email) && !is_null($user->email)){
                array_push($emails, $user->email);
            }
        }

        if ($users) {
            Notification::send($users, new \App\Notifications\NewAction($request->message));   //multiple users

//            Mail::send(new NotifyMail($request->message), [], function($message) use ($emails)
//            {
//                $message->to($emails)->subject('Notification');
//            });
//            var_dump( Mail:: failures());

            Mail::to($emails)->queue(new NotifyMail($request->message));

            $course->makeHidden(['users']);
            return $this->sendResponse($course->toArray(), 'Notification for course ' . $course->id . ' sent successfully');
        }
    }

    /**
     * Display the specified Course.
     * GET|HEAD /courses/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function course_by_title(Request $request)
    {
        /** @var Course $course */

        $course = Course::where('title', $request->title)->first();
        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        if(auth('api')->check()) {
            $user = auth('api')->user();
            $enrolled = DB::table('course_user')->where([
                ['course_id', $course->id],
                ['user_id', $user->id]
            ])->first();
        }
        else{
            $enrolled = [];
        }

        if (!$enrolled) {
            $lessons = $course->lesson = $course->lesson()->orderBy('number')->get();
            $course->isUserEnrolled = false;
            foreach ($lessons as $lesson) {
                $lesson->makeHidden('pivot', 'full_description', 'video_link', 'zoom_link', 'telegram_link', 'homework_link');
            }
            $course->lesson = $lessons;
        }
        else{
            $course->lesson = $course->lesson()->orderBy('number')->get();
            $course->isUserEnrolled = true;
        }


        $course->course_feature;
        $course->teachers;
        $course->partners;
        $course->faq;
        $course->review;


        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    public function course_by_url(Request $request)
    {
        /** @var Course $course */

        $course = Course::where('url', $request->url)->first();
        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        if(auth('api')->check()) {
            $user = auth('api')->user();
            $enrolled = DB::table('course_user')->where([
                ['course_id', $course->id],
                ['user_id', $user->id]
            ])->first();
        }
        else{
            $enrolled = [];
        }

        if (!$enrolled) {
            $lessons = $course->lesson = $course->lesson()->orderBy('number')->get();
            $course->isUserEnrolled = false;
            foreach ($lessons as $lesson) {
                $lesson->makeHidden('pivot', 'full_description', 'video_link', 'zoom_link', 'telegram_link', 'homework_link');
            }
            $course->lesson = $lessons;
        }
        else{
            $course->lesson = $course->lesson()->orderBy('number')->get();
            $course->isUserEnrolled = true;
        }


        $course->course_feature;
        $course->teachers;
        $course->partners;
        $course->faq;
        $course->review;


        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    public function show($id)
    {
        /** @var Course $course */

        $course = $this->courseRepository->find($id);
        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        if(auth('api')->check()) {
            $user = auth('api')->user();
            $enrolled = DB::table('course_user')->where([
                ['course_id', $course->id],
                ['user_id', $user->id]
            ])->first();
            if(auth('api')->user()->role_id == 'admin'){
                $course->users;
                $course->total_enrolled_users = $course->users->count();
            }
        }
        else{
            $enrolled = [];
        }

        if (!$enrolled) {
            $lessons = $course->lesson = $course->lesson()->orderBy('number')->get();
            $course->isUserEnrolled = false;
            foreach ($lessons as $lesson) {
                $lesson->makeHidden('pivot', 'full_description', 'video_link', 'zoom_link', 'telegram_link', 'homework_link');
            }
            $course->lesson = $lessons;
        }
        else{
            $course->lesson = $course->lesson()->orderBy('number')->get();
            $course->isUserEnrolled = true;
        }


        $course->course_feature;
        $course->teachers;
        $course->partners;
        $course->faq;
        $course->review;


        return $this->sendResponse($course->toArray(), 'Course retrieved successfully');
    }

    public function show_back($id)
    {
        /** @var Course $course */

        $course = $this->courseRepository->find($id);
        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $course->users;
        $course->total_enrolled_users = $course->users->count();


        return view('courseuser')->with('course', $course);
    }

    public function attach_teacher($id, Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'teachers' => 'required',
        ]);
        $course = Course::find($id);
        if (empty($course)) {
            return $this->sendError('Course not found');
        }
        $course->teachers()->syncWithoutDetaching($request->teachers);
        return response([
            'success' => true,
            'data' => $course,
            'message' => 'Given teachers are attached'
        ],
            200);

    }

    public function detach_teacher($id, Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'teachers' => 'required',
        ]);
        $course = Course::find($id);
        if (empty($course)) {
            return $this->sendError('Course not found');
        }
        if($request->filled('teachers')){
            $course->teachers()->detach($request->teachers);
        }
        else{
            $course->teachers()->detach();
        }

        return response([
            'success' => true,
            'data' => [],
            'message' => 'Teachers are detached'
        ],
            200);
    }

    public function attach_partner($id, Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'partners' => 'required',
        ]);
        $course = Course::find($id);
        if (empty($course)) {
            return $this->sendError('Course not found');
        }
        $course->partners()->syncWithoutDetaching($request->partners);
        return response([
            'success' => true,
            'data' => $course,
            'message' => 'Given partners are attached'
        ],
            200);

    }

    public function detach_partner($id, Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'partners' => 'required',
        ]);
        $course = Course::find($id);
        if (empty($course)) {
            return $this->sendError('Course not found');
        }
        if($request->filled('partners')){
            $course->partners()->detach($request->partners);
        }
        else{
            $course->partners()->detach();
        }
        return response([
            'success' => true,
            'data' => [],
            'message' => 'Partners are detached'
        ],
            200);
    }


    public function my_courses()
    {

        $user = auth('api')->user();
        $courses = $user->course;
        foreach ($courses as $course) {
            $course->finished_lesson_num = $course->lesson_finished->where('user_id', auth('api')->user()->id)->count();
            $course->total_lesson_num = $course->lesson->count();
            $course->makeHidden('pivot', 'lesson_finished', 'lesson');
        }

        return $this->sendResponse($courses->toArray(), 'User courses retrieved successfully');
    }

    public function course_enroll($id)
    {

//        $input = $request->all();

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $course->users()->syncWithoutDetaching(auth('api')->user()->id);
        $course->user = auth('api')->user();

//        $course = $this->courseRepository->update($input, $id);

        return $this->sendResponse($course->toArray(), 'User enrolled successfully');
    }

    /**
     * Update the specified Course in storage.
     * PUT/PATCH /courses/{id}
     *
     * @param int $id
     * @param UpdateCourseAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCourseAPIRequest $request)
    {
        $input = $request->all();

        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $photo = $request->file('photo');

        if ($photo) {
            if (!is_null($course->photo) || !empty($course->photo)) {
                $path = public_path() . $course->photo;
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $gen = new GenName();
            $fileToStore = $gen->generate($photo, $course->title);
            $photo->move('coursephoto', $fileToStore);
            $input['photo'] = '/coursephoto/' . $fileToStore;
        }

        $course = $this->courseRepository->update($input, $id);

        if($request->has('status')){
            if($request->status == 1){
                $users = User::all();

                if ($users) {
                    Notification::send($users, new \App\Notifications\NewAction("Новый курс: " . $course->title));   //multiple users
                }
            }
        }

        return $this->sendResponse($course->toArray(), 'Course updated successfully');
    }

    /**
     * Remove the specified Course from storage.
     * DELETE /courses/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */


    public function destroy($id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }
        $course->teachers()->detach();

        $course->delete();

        return $this->sendSuccess('Course deleted successfully');
    }
}
