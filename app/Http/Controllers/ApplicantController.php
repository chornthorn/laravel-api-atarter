<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnprocessableEntityException;
use App\Models\Applicant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicantController extends Controller
{

    /**
     * Retrieves a paginated list of applicants based on the provided filters.
     *
     * @param Request $request The HTTP request object.
     * @return JsonResponse The JSON response containing the paginated list of applicants.
     */
    public function index(Request $request)
    {
        $firstName = $request->query('first_name') ?? '';
        $lastName = $request->query('last_name') ?? '';
        $email = $request->query('email') ?? '';
        $phoneNumber = $request->query('phone_number') ?? '';
        $perPage = $request->input('per_page');

        $applicants = Applicant::query()
            ->when($firstName, function ($query) use ($firstName) {
                $query->where('first_name', 'like', '%' . $firstName . '%');
            })
            ->when($lastName, function ($query) use ($lastName) {
                $query->where('last_name', 'like', '%' . $lastName . '%');
            })
            ->when($email, function ($query) use ($email) {
                $query->where('email', 'like', '%' . $email . '%');
            })
            ->when($phoneNumber, function ($query) use ($phoneNumber) {
                $query->where('phone_number', 'like', '%' . $phoneNumber . '%');
            })
            ->paginate($perPage);

        // transform to custom resume url
        $applicants->getCollection()->transform(function ($applicant) {

            // check if resume url exists
            if ($applicant->resume_url) {
                $disk = Storage::disk('gcs');

                // add expired url to resume_url for 60 minutes
                $applicant->resume_url = $disk->temporaryUrl(
                    $applicant->resume_url,
                    now()->addMinutes(60),
                );

            }

            return $applicant;

        });

        return response()->json($applicants);
    }

    //show
    public function show(Request $request, $id)
    {
        $applicant = Applicant::query()->findOrFail($id);

        if (!$applicant) {
            throw new NotFoundException('Applicant not found');
        }

        // custom pivot table
        $applicant->applicantStatuses->map(function ($applicantStatus) {

            // tranform to remove pivot table
            unset($applicantStatus->pivot);
        });

        // check if resume url exists
        if ($applicant->resume_url) {
            $disk = Storage::disk('gcs');

            $applicant->resume_url = $disk->temporaryUrl(
                $applicant->resume_url,
                now()->addMinutes(60),
            );
        }

        return response()->json($applicant);
    }

    //store
    public function store(Request $request)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'notes',
            'resume_file',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required',
            'notes' => 'nullable|string',
            'resume_file' => 'nullable|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors()->first());
        }

        // check if applicant already exists
        $applicant = Applicant::where('email', $payload['email'])->first();

        if ($applicant) {
            throw new BadRequestException('Applicant already exists');
        }

        // upload resume if exists in payload
        if (isset($payload['resume_file'])) {
            $resume_pdf = $request->file('resume_file');

            $disk = Storage::disk('gcs');

            $folder = 'applicants_resume';

            // format file path (phone_number_first_name_last_name.pdf)

            $fileNamed = $payload['phone_number'] . '_' . $payload['first_name'] . '_' . $payload['last_name'] . '.pdf';

            $path = $disk->putFileAs($folder, $resume_pdf, $fileNamed, 'private');

            if (!$path) {
                throw new BadRequestException('Resume file could not be uploaded');
            }

            $payload['resume_url'] = $path;
        }

        $applicant = Applicant::create($payload);

        if (!$applicant) {
            // delete new resume if exists
            if ($payload['resume_url']) {
                Storage::disk('gcs')->delete($payload['resume_url']);
            }

            throw new BadRequestException('Applicant could not be created');
        }

        return response()->json($applicant);
    }

    //update
    public function update(Request $request, $id)
    {
        $payload = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'notes',
            'resume_file',
        ]);

        $validator = Validator::make($payload, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable',
            'notes' => 'nullable|string',
            'resume_file' => 'nullable|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException($validator->errors()->first());
        }

        $applicant = Applicant::query()->findOrFail($id);

        if (!$applicant) {
            throw new NotFoundException('Applicant not found');
        }

        // check if email already exists with different id
        $emailExists = Applicant::where('email', $payload['email'])->where('id', '!=', $id)->first();

        if ($emailExists) {
            throw new BadRequestException('Email already in use');
        }

        // upload resume if exists in payload
        if (isset($payload['resume_file'])) {
            $resume_pdf = $request->file('resume_file');

            $disk = Storage::disk('gcs');

            $folder = 'applicants_resume';

            // format file path (phone_number_first_name_last_name.pdf)
            $fileNamed = $payload['phone_number'] . '_' . $payload['first_name'] . '_' . $payload['last_name'] . '.pdf';

            $path = $disk->putFileAs($folder, $resume_pdf, $fileNamed, 'private');

            if (!$path) {
                throw new BadRequestException('Resume file could not be uploaded');
            }

            $payload['resume_url'] = $path;

            // delete old resume if exists
            if ($applicant->resume_url) {
                Storage::disk('gcs')->delete($applicant->resume_url);
            }

        }

        if (!$applicant->update($payload)) {
            // delete new resume if exists
            if ($payload['resume_url']) {
                Storage::disk('gcs')->delete($payload['resume_url']);
            }

            throw new BadRequestException('Applicant could not be updated');
        }

        return response()->json(['message' => 'Applicant updated']);
    }

    //destroy
    public function destroy($id)
    {
        $applicant = Applicant::query()->findOrFail($id);

        if (!$applicant) {
            throw new NotFoundException('Applicant not found');
        }

        if (!$applicant->delete()) {
            throw new BadRequestException('Applicant could not be deleted');
        }

        // delete resume if exists
        if ($applicant->resume_url) {
            Storage::disk('gcs')->delete($applicant->resume_url);
        }

        return response()->json(['message' => 'Applicant deleted']);
    }

}
