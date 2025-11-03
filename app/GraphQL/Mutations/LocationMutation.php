<?php

namespace App\GraphQL\Mutations;

use App\Models\Location;
use App\Models\Event;
use Exception;

class LocationMutation
{
    public function create($_, array $args)
    {
        try {
            return Location::create([
                'name' => $args['name'],
                'building' => $args['building'] ?? null,
                'address' => $args['address'] ?? null,
                'capacity' => $args['capacity'] ?? null,
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to create location: ' . $e->getMessage());
        }
    }

    public function update($_, array $args)
    {
        try {
            $location = Location::findOrFail($args['id']);

            $location->update(array_filter([
                'name' => $args['name'] ?? null,
                'building' => $args['building'] ?? null,
                'address' => $args['address'] ?? null,
                'capacity' => $args['capacity'] ?? null,
            ], fn($value) => $value !== null));

            return $location->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update location: ' . $e->getMessage());
        }
    }

    public function delete($_, array $args)
    {
        $locationId = $args['id'];

        // Kiểm tra location có tồn tại không
        $location = Location::find($locationId);
        if (!$location) {
            throw new Exception("Không tìm thấy địa điểm.");
        }

        // Kiểm tra có event nào đang dùng location này không
        $eventCount = Event::where('location_id', $locationId)->count();

        if ($eventCount > 0) {
            throw new Exception("Không thể xóa vì còn {$eventCount} sự kiện đang sử dụng địa điểm này.");
        }

        // Nếu không có event nào, thì xóa
        $location->delete();

        return $location;
    }
}
