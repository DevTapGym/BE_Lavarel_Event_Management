<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Exception;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return $this->successResponse(
            200,
            'Fecth all events successfully',
            $events
        );
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'location'    => 'required|string',
                'start_date'  => 'required|date',
                'end_date'    => 'required|date|after_or_equal:start_date',
                'organizer'   => 'required|string',
                'status'      => 'in:upcoming,ongoing,completed,canceled'
            ]);

            $event = Event::create($data);

            return $this->successResponse(
                201,
                'Create event successfully',
                $event
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                $e->getMessage(),
                'Internal Server Error'
            );
        }
    }

    public function show(string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse(
                404,
                'Not Found',
                'Event not found'
            );
        }

        return $this->successResponse(
            200,
            'Fecth event successfully',
            $event
        );
    }

    public function update(Request $request, string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse(
                404,
                'Not Found',
                'Event not found'
            );
        }

        try {
            $event->update($request->all());
            return $this->successResponse(
                200,
                'Update event successfully',
                $event
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                $e->getMessage(),
                'Internal Server Error'
            );
        }
    }

    public function destroy(string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse(
                404,
                null,
                'Event not found'
            );
        }

        try {
            $event->delete();
            return $this->successResponse(
                200,
                'Delete event successfully',
                null
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                $e->getMessage(),
                'Internal Server Error'
            );
        }
    }
}
