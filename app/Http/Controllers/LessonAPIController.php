<?php

namespace App\Http\Controllers;

use App\Http\Requests\API\CreateLessonAPIRequest;
use App\Http\Requests\API\UpdateLessonAPIRequest;
use App\LessonFinished;
use App\Models\Course;
use App\Models\Lesson;
use App\Repositories\LessonRepository;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Notification;
use Response;


# EPARK
/**
 * Class LessonController
 * @package App\Http\Controllers\API
 */
class LessonAPIController extends AppBaseController
{
    /** @var  LessonRepository */
    private $lessonRepository;

    public function __construct(LessonRepository $lessonRepo)
    {
        $this->middleware('admin', ['except' => ['index', 'show', 'lesson_finished']]);
        $this->middleware('api-auth', ['except' => []]);
        $this->lessonRepository = $lessonRepo;
    }

# EPARK

    /**
     * Display a listing of the Lesson.
     * GET|HEAD /lessons
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $lessons = $this->lessonRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($lessons->toArray(), 'Lessons retrieved successfully');
    }

    /**
     * Store a newly created Lesson in storage.
     * POST /lessons
     *
     * @param CreateLessonAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateLessonAPIRequest $request)
    {
        $input = $request->all();

        $lesson = $this->lessonRepository->create($input);

        if ($lesson) {
            $course = $lesson->course;;
            if ($course) {
                $users = $course->users;
                if ($users) {
                    Notification::send($users, new \App\Notifications\NewAction("Новый урок в курсе " . $course->title));   //multiple users
                }

            }

        }

        return $this->sendResponse($lesson->toArray(), 'Lesson saved successfully');
    }

# EPARK

    public function lesson_finished(Request $request, $id)
    {
        $input = $request->all();

        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response([
                'success' => false,
                'data' => [],
                'message' => 'Lesson is not found'
            ],
                404);
        }
        $finished = LessonFinished::where([
            ['user_id', auth('api')->user()->id],
            ['lesson_id', $lesson->id]
        ])->first();
        if ($finished) {
            return response([
                'success' => false,
                'data' => [],
                'message' => 'Lesson is already marked as finished '
            ],
                200);
        }
        $lesson_finished = new LessonFinished();
        $lesson_finished->lesson_id = $lesson->id;
        $lesson_finished->course_id = $lesson->course_id;
        $lesson_finished->user_id = auth('api')->user()->id;
        $lesson_finished->save();

        return response([
            'success' => true,
            'data' => [],
            'message' => 'Lesson is marked as finished'
        ],
            201);


    }

    /**
     * Display the specified Lesson.
     * GET|HEAD /lessons/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        return $this->sendResponse($lesson->toArray(), 'Lesson retrieved successfully');
    }

    /**
     * Update the specified Lesson in storage.
     * PUT/PATCH /lessons/{id}
     *
     * @param int $id
     * @param UpdateLessonAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateLessonAPIRequest $request)
    {
        $input = $request->all();

        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }

        $lesson = $this->lessonRepository->update($input, $id);

        return $this->sendResponse($lesson->toArray(), 'Lesson updated successfully');
    }

    /**
     * Remove the specified Lesson from storage.
     * DELETE /lessons/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var Lesson $lesson */
        $lesson = $this->lessonRepository->find($id);

        if (empty($lesson)) {
            return $this->sendError('Lesson not found');
        }
        $finished = LessonFinished::where('lesson_id', $lesson->id)->get();
        foreach ($finished as $finish) {
            $finish->delete();
        }

        $lesson->delete();

        return $this->sendSuccess('Lesson deleted successfully');
    }
}
