<?php

namespace Controller;

use Model\GuestModel;
use Exception;

class GuestController
{
    private GuestModel $guestModel;

    public function __construct(GuestModel $guestModel)
    {
        $this->guestModel = $guestModel;
    }

    /**
     * Create a new guest record.
     */
    public function createGuest(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $this->validateGuestData($data);

            $guestData = [
                'first_name'            => $data['first_name'],
                'last_name'             => $data['last_name'],
                'first_name_plus_1'             => $data['first_name_plus_1'],
                'last_name_plus_1'             => $data['last_name_plus_1'],
                'unique_path'           => $data['unique_path'],
                'rsvp_status'           => $data['rsvp_status'] ?? 'pending',
                'rsvp_status_plus_one'  => $data['rsvp_status_plus_one'] ?? 'pending',
                'special_requests'      => $data['special_requests'] ?? ''
            ];

            $result = $this->guestModel->createGuest($guestData);

            http_response_code(201);
            echo json_encode([
                "message"  => "Guest created successfully",
                "guest_id" => $result['guest_id']
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create guest", "details" => $e->getMessage()]);
        }
    }

    /**
     * Update an existing guest record.
     */
    public function updateGuest(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $this->validateGuestUpdateData($data);

            $guestId = intval($data['guest_id']);

            $guestData = [
                'first_name'            => $data['first_name'],
                'last_name'             => $data['last_name'],
                'first_name_plus_1'             => $data['first_name_plus_1'],
                'last_name_plus_1'             => $data['last_name_plus_1'],
                'unique_path'           => $data['unique_path'],
                'rsvp_status'           => $data['rsvp_status'] ?? 'pending',
                'rsvp_status_plus_one'  => $data['rsvp_status_plus_one'] ?? 'pending',
                'special_requests'      => $data['special_requests'] ?? ''
            ];

            $result = $this->guestModel->updateGuest($guestId, $guestData);

            http_response_code(200);
            echo json_encode([
                "message" => "Guest updated successfully",
                "guest"   => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update guest", "details" => $e->getMessage()]);
        }
    }

    /**
     * List all guests.
     */
    public function listGuests(): void
    {
        try {
            $guests = $this->guestModel->listGuests();
            http_response_code(200);
            echo json_encode(["guests" => $guests]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to list guests", "details" => $e->getMessage()]);
        }
    }

    /**
     * Retrieve a guest by ID.
     */
    public function getGuestById(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['guest_id'])) {
                throw new \InvalidArgumentException("Parameter 'guest_id' is required.");
            }

            $guestId = intval($data['guest_id']);
            if ($guestId <= 0) {
                throw new \InvalidArgumentException("Invalid guest_id.");
            }

            $guest = $this->guestModel->getGuestById($guestId);
            if (!$guest) {
                throw new \Exception("Guest not found.");
            }

            http_response_code(200);
            echo json_encode(["guest" => $guest]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve guest", "details" => $e->getMessage()]);
        }
    }

    /**
     * Retrieve a guest record by unique_path.
     */
    public function getGuestByUniquePath(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['unique_path']) || empty($data['unique_path'])) {
                throw new \InvalidArgumentException("Parameter 'unique_path' is required.");
            }

            $uniquePath = trim($data['unique_path']);

            $guest = $this->guestModel->getByUniquePath($uniquePath);
            if (!$guest) {
                throw new \Exception("Guest not found.");
            }

            http_response_code(200);
            echo json_encode(["guest" => $guest]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(600);
            echo json_encode(["error" => "Failed to retrieve guest", "details" => $e->getMessage()]);
        }
    }


    /**
     * Delete a guest by ID.
     */
    public function deleteGuest(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['guest_id'])) {
                throw new \InvalidArgumentException("Parameter 'guest_id' is required.");
            }

            $guestId = intval($data['guest_id']);
            if ($guestId <= 0) {
                throw new \InvalidArgumentException("Invalid guest_id.");
            }

            $this->guestModel->deleteGuest($guestId);
            http_response_code(200);
            echo json_encode(["message" => "Guest deleted successfully"]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete guest", "details" => $e->getMessage()]);
        }
    }

    /**
     * Validate data for guest creation.
     */
    private function validateGuestData(array $data): void
    {
        if (empty($data['first_name'])) {
            throw new \InvalidArgumentException("First name is required.");
        }
        if (empty($data['last_name'])) {
            throw new \InvalidArgumentException("Last name is required.");
        }
        if (empty($data['unique_path'])) {
            throw new \InvalidArgumentException("Unique path is required.");
        }
        if (isset($data['rsvp_status']) && !in_array($data['rsvp_status'], ['pending', 'accepted', 'declined'])) {
            throw new \InvalidArgumentException("Invalid rsvp_status value.");
        }
        if (isset($data['rsvp_status_plus_one']) && !in_array($data['rsvp_status_plus_one'], ['pending', 'accepted', 'declined'])) {
            throw new \InvalidArgumentException("Invalid rsvp_status_plus_one value.");
        }
    }

    /**
     * Validate data for guest update.
     */
    private function validateGuestUpdateData(array $data): void
    {
        if (empty($data['guest_id'])) {
            throw new \InvalidArgumentException("Guest ID is required.");
        }
        // Reuse creation validation for the rest of the fields.
        $this->validateGuestData($data);
    }
}
