<?php

namespace App\Http\Controllers;

use App\GenName;
use App\Http\Requests\API\CreatePartnerAPIRequest;
use App\Http\Requests\API\UpdatePartnerAPIRequest;
use App\Models\Course;
use App\Models\Partner;
use App\Repositories\PartnerRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\File;
use Response;

/**
 * Class PartnerController
 * @package App\Http\Controllers\API
 */

class PartnerAPIController extends AppBaseController
{
    /** @var  PartnerRepository */
    private $partnerRepository;

    public function __construct(PartnerRepository $partnerRepo)
    {
        $this->partnerRepository = $partnerRepo;
    }

    /**
     * Display a listing of the Partner.
     * GET|HEAD /partners
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $partners = $this->partnerRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($partners->toArray(), 'Partners retrieved successfully');
    }

    /**
     * Store a newly created Partner in storage.
     * POST /partners
     *
     * @param CreatePartnerAPIRequest $request
     *
     * @return Response
     */
    public function store(CreatePartnerAPIRequest $request)
    {
        $input = $request->all();

        $logo = $request->file('logo');
        if ($logo) {
            $gen = new GenName();
            $fileToStore = $gen->generate($logo, $request->title);
            $logo->move('logo', $fileToStore);
            $input['logo'] = '/logo/' . $fileToStore;
        }

        $partner = $this->partnerRepository->create($input);

        $message = '';
        if ($request->has('course_id')) {
            $course = Course::find($request->course_id);
            if (empty($course)) {
                $message = ', but Course not found, so partner is not attached';
            }
            else{
                $message = ', partner is attached to course';
            }
            $course->partners()->syncWithoutDetaching($partner->id);
        }

        return $this->sendResponse($partner->toArray(), 'Partner saved successfully'.$message);
    }

    /**
     * Display the specified Partner.
     * GET|HEAD /partners/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Partner $partner */
        $partner = $this->partnerRepository->find($id);

        if (empty($partner)) {
            return $this->sendError('Partner not found');
        }
        $partner->course;

        return $this->sendResponse($partner->toArray(), 'Partner retrieved successfully');
    }

    /**
     * Update the specified Partner in storage.
     * PUT/PATCH /partners/{id}
     *
     * @param int $id
     * @param UpdatePartnerAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdatePartnerAPIRequest $request)
    {
        $input = $request->all();

        /** @var Partner $partner */
        $partner = $this->partnerRepository->find($id);

        if (empty($partner)) {
            return $this->sendError('Partner not found');
        }

        $logo = $request->file('logo');
        if ($logo) {
            if (!is_null($partner->logo) || !empty($partner->logo)) {
                $path = public_path() . $partner->logo;
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $gen = new GenName();
            $fileToStore = $gen->generate($logo, $partner->title);
            $logo->move('logo', $fileToStore);
            $input['logo'] = '/logo/' . $fileToStore;
        }

        $partner = $this->partnerRepository->update($input, $id);

        return $this->sendResponse($partner->toArray(), 'Partner updated successfully');
    }

    /**
     * Remove the specified Partner from storage.
     * DELETE /partners/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Partner $partner */
        $partner = $this->partnerRepository->find($id);

        if (empty($partner)) {
            return $this->sendError('Partner not found');
        }

        if (!is_null($partner->logo) || !empty($partner->logo)) {
            $path = public_path() . 'logo' . $partner->logo;
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $partner->delete();

        return $this->sendSuccess('Partner deleted successfully');
    }
}
