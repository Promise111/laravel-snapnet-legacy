<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersistEmployeeRequest;
use App\Jobs\SendWelcomeEmail;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * Store a newly created employee in storage.
     *
     * @param  PersistEmployeeRequest  $request
     * @return JsonResponse
     */
    public function store(PersistEmployeeRequest $request): JsonResponse
    {
        try {
            $employee = Employee::create($request->validated());

            // Dispatch background job to send welcome email
            SendWelcomeEmail::dispatch($employee->email);
            // Log::info("Dispatching welcome email", ['email' => $employee->email]);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the employee',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
