<?php

namespace App\Http\Controllers;

use App\GenName;
use App\Http\Requests\API\CreateCourseFeatureAPIRequest;
use App\Http\Requests\API\UpdateCourseFeatureAPIRequest;
use App\Models\CourseFeature;
use App\Repositories\CourseFeatureRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\File;
use Response;

/**
 * Class CourseFeatureController
 * @package App\Http\Controllers\API
 */

class CourseFeatureAPIController extends AppBaseController
{
    /** @var  CourseFeatureRepository */
    private $courseFeatureRepository;

    public function __construct(CourseFeatureRepository $courseFeatureRepo)
    {
        $this->middleware('admin', ['except' => ['index', 'show']]);
        $this->middleware('api-auth', ['except' => []]);

        $this->courseFeatureRepository = $courseFeatureRepo;
    }

    /**
     * Display a listing of the CourseFeature.
     * GET|HEAD /courseFeatures
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $courseFeatures = $this->courseFeatureRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courseFeatures->toArray(), 'Course Features retrieved successfully');
    }

    /**
     * Store a newly created CourseFeature in storage.
     * POST /courseFeatures
     *
     * @param CreateCourseFeatureAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateCourseFeatureAPIRequest $request)
    {
        $input = $request->all();
        $icon = $request->file('icon');
        if ($icon) {
            $gen = new GenName();
            $fileToStore = $gen->generate($icon, $request->title);
            $icon->move('icon', $fileToStore);
            $input['icon'] = '/icon/' . $fileToStore;
        }

        $courseFeature = $this->courseFeatureRepository->create($input);

        return $this->sendResponse($courseFeature->toArray(), 'Course Feature saved successfully');
    }

    /**
     * Display the specified CourseFeature.
     * GET|HEAD /courseFeatures/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var CourseFeature $courseFeature */
        $courseFeature = $this->courseFeatureRepository->find($id);

        if (empty($courseFeature)) {
            return $this->sendError('Course Feature not found');
        }

        $courseFeature->course;

        return $this->sendResponse($courseFeature->toArray(), 'Course Feature retrieved successfully');
    }

    /**
     * Update the specified CourseFeature in storage.
     * PUT/PATCH /courseFeatures/{id}
     *
     * @param int $id
     * @param UpdateCourseFeatureAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCourseFeatureAPIRequest $request)
    {
        $input = $request->all();

        /** @var CourseFeature $courseFeature */
        $courseFeature = $this->courseFeatureRepository->find($id);

        if (empty($courseFeature)) {
            return $this->sendError('Course Feature not found');
        }

        $icon = $request->file('icon');
        if ($icon) {
            if (!is_null($courseFeature->icon) || !empty($courseFeature->icon)) {
                $path = public_path() . $courseFeature->icon;
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $gen = new GenName();
            $fileToStore = $gen->generate($icon, $courseFeature->title);
            $icon->move('icon', $fileToStore);
            $input['icon'] = '/icon/' . $fileToStore;
        }

        $courseFeature = $this->courseFeatureRepository->update($input, $id);

        return $this->sendResponse($courseFeature->toArray(), 'CourseFeature updated successfully');
    }

    /**
     * Remove the specified CourseFeature from storage.
     * DELETE /courseFeatures/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var CourseFeature $courseFeature */
        $courseFeature = $this->courseFeatureRepository->find($id);

        if (empty($courseFeature)) {
            return $this->sendError('Course Feature not found');
        }

        if (!is_null($courseFeature->icon) || !empty($courseFeature->icon)) {
            $path = public_path() . $courseFeature->icon;
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $courseFeature->delete();

        return $this->sendSuccess('Course Feature deleted successfully');
    }
}
