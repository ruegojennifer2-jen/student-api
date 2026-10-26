<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        $students = Student::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'count'   => $students->count(),
            'data'    => $students,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $student = Student::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully.',
            'data'    => $student,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $student = Student::find($id);

        if (! $student) {
            return $this->notFound($id);
        }

        return response()->json([
            'success' => true,
            'data'    => $student,
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $student = Student::find($id);

        if (! $student) {
            return $this->notFound($id);
        }

        $validator = Validator::make($request->all(), $this->rules($student->id));

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $student->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'data'    => $student->fresh(),
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $student = Student::find($id);

        if (! $student) {
            return $this->notFound($id);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully.',
        ], 200);
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'student_number' => [
                'required', 'string', 'max:20',
                Rule::unique('students', 'student_number')->ignore($ignoreId),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => [
                'required', 'email', 'max:255',
                Rule::unique('students', 'email')->ignore($ignoreId),
            ],
            'course'     => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'between:1,6'],
        ];
    }

    private function validationError($validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422);
    }

    private function notFound(string $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "Student with ID {$id} not found.",
        ], 404);
    }
}