<?php

namespace App\Http\Controllers;

use App\GenName;
use App\Http\Requests\API\CreateTeacherAPIRequest;
use App\Http\Requests\API\UpdateTeacherAPIRequest;
use App\Models\Course;
use App\Models\Teacher;
use App\Repositories\TeacherRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\File;
use Response;

/**
 * Class TeacherController
 * @package App\Http\Controllers\API
 */
class TeacherAPIController extends AppBaseController
{
    /** @var  TeacherRepository */
    private $teacherRepository;

    public function __construct(TeacherRepository $teacherRepo)
    {
        $this->teacherRepository = $teacherRepo;
    }

    /**
     * Display a listing of the Teacher.
     * GET|HEAD /teachers
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $teachers = $this->teacherRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($teachers->toArray(), 'Teachers retrieved successfully');
    }

    /**
     * Store a newly created Teacher in storage.
     * POST /teachers
     *
     * @param CreateTeacherAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateTeacherAPIRequest $request)
    {
        $input = $request->all();
        $avatar = $request->file('avatar');
        if ($avatar) {
            $gen = new GenName();
            $fileToStore = $gen->generate($avatar, $request->name);
            $avatar->move('avatars', $fileToStore);
            $input['avatar'] = '/avatars/' . $fileToStore;
        }

        $teacher = $this->teacherRepository->create($input);
        $message = '';
        if ($request->has('course_id')) {
            $course = Course::find($request->course_id);
            if (empty($course)) {
                $message = ', but Course not found so teacher is not attached';
            }
            else{
                $message = ', teacher is attached to course';
            }
            $course->teachers()->syncWithoutDetaching($teacher->id);
        }

        return $this->sendResponse($teacher->toArray(), 'Teacher saved successfully'.$message);
    }

    /**
     * Display the specified Teacher.
     * GET|HEAD /teachers/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Teacher $teacher */
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            return $this->sendError('Teacher not found');
        }
        $teacher->course;

        return $this->sendResponse($teacher->toArray(), 'Teacher retrieved successfully');
    }

    /**
     * Update the specified Teacher in storage.
     * PUT/PATCH /teachers/{id}
     *
     * @param int $id
     * @param UpdateTeacherAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateTeacherAPIRequest $request)
    {
        $input = $request->all();

        /** @var Teacher $teacher */
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            return $this->sendError('Teacher not found');
        }

        $avatar = $request->file('avatar');

        if ($avatar) {
            if (!is_null($teacher->avatar) || !empty($teacher->avatar)) {
                $path = public_path() . $teacher->avatar;
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $gen = new GenName();
            $fileToStore = $gen->generate($avatar, $teacher->name);
            $avatar->move('avatars', $fileToStore);
            $input['avatar'] = '/avatars/' . $fileToStore;
        }

        $teacher = $this->teacherRepository->update($input, $id);

        return $this->sendResponse($teacher->toArray(), 'Teacher updated successfully');
    }

    /**
     * Remove the specified Teacher from storage.
     * DELETE /teachers/{id}
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var Teacher $teacher */
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            return $this->sendError('Teacher not found');
        }

        if (!is_null($teacher->avatar) || !empty($teacher->avatar)) {
            $path = public_path() . $teacher->avatar;
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $teacher->delete();

        return $this->sendSuccess('Teacher deleted successfully');
    }
}
