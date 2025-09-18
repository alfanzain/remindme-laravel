<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Responses\{ClientError, ServerError, Success};
use App\Services\{SessionService, ReminderService};

class ReminderController extends Controller
{
    private const REMINDER_LIST_LIMIT = 10;
    private SessionService $sessionService;
    private ReminderService $reminderService;

    public function __construct(
        SessionService $sessionService, 
        ReminderService $reminderService
    ) {
        $this->middleware('auth:api');
        $this->sessionService = $sessionService;
        $this->reminderService = $reminderService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->reminderService->list(
                $request->query('limit', self::REMINDER_LIST_LIMIT)
            );

            return Success::response($data);
        } catch (\Exception $e) {
            return ServerError::internalError($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $this->sessionService->getUserFromToken($request->bearerToken());
            $request->validate([
                'title'      => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'remind_at'  => 'required|integer',
                'event_at'   => 'required|integer',
            ]);
            $request->merge(['created_by' => $user ? $user->id : null]);

            $data = $this->reminderService->create($request->all());

            return Success::response($data);
        } catch (ValidationException $e) {
            return ClientError::badRequest([
                'message' => 'invalid request payload',
                'errors'  => $e->errors(),
            ]);
        } catch (\Exception $e) {
            return ServerError::internalError($e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $data = $this->reminderService->find($id);
            if (!$data) {
                return ClientError::notFound();
            }

            return Success::response($data);
        } catch (\Exception $e) {
            return ServerError::internalError($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->reminderService->update($id, $request->all());
            $request->validate([
                'title'      => 'string|max:255',
                'description' => 'string|max:1000',
                'remind_at'  => 'integer',
                'event_at'   => 'integer',
            ]);

            if (!$data) {
                return ClientError::notFound();
            }

            return Success::response($data);
        } catch (ValidationException $e) {
            return ClientError::badRequest([
                'message' => 'invalid request payload',
                'errors'  => $e->errors(),
            ]);
        } catch (\Exception $e) {
            return ServerError::internalError($e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $deleted = $this->reminderService->delete($id);
            if (!$deleted) {
                return ClientError::notFound();
            }

            return Success::ok();
        } catch (\Exception $e) {
            return ServerError::internalError($e->getMessage());
        }
    }
}
