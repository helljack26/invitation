<?php

namespace Controller;

use Model\GuestModel;
use Model\UserModel;
use Exception;

class GuestController
{
    private GuestModel $guestModel;
    private UserModel $userModel;

    public function __construct(GuestModel $guestModel, UserModel $userModel)
    {
        $this->guestModel = $guestModel;
        $this->userModel = $userModel;
    }

    /**
     * Аутентификация и получение company_id.
     */
    private function authenticateByUserId(): ?array
    {
        $userId = $this->userModel->getUserIdFromToken();
        if (!$userId) {
            http_response_code(403);
            echo json_encode(["error" => "Ви не залогінені!"]);
            return null;
        }
        return ['userId' => $userId];
    }


    /**
     * Create a new guest record.
     */
    public function createGuest(): void
    {
        try {
            // Check authentication:
            $auth = $this->authenticateByUserId();
            if ($auth === null) {
                return; // User not authenticated, response already sent in authenticateByUserId()
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validate the mandatory fields:
            if (!isset($data['first_name']) || empty($data['first_name'])) {
                throw new \InvalidArgumentException("Field 'first_name' is required.");
            }

            // Make them optional
            $plusOneName = $data['first_name_plus_1'] ?? null;
            $gender = $data['gender'] ?? null;

            // Generate unique path using the main guest name + optional plus-one
            $uniquePath = $this->generateUniquePath($data['first_name'], $plusOneName);

            // Prepare final payload
            $guestData = [
                'first_name'           => $data['first_name'],
                'first_name_plus_1'    => $plusOneName,
                'unique_path'          => $uniquePath,
                'gender'               => $gender,
                'rsvp_status'          => $data['rsvp_status']          ?? 'pending',
                'rsvp_status_plus_one' => $data['rsvp_status_plus_one'] ?? 'pending'
            ];

            // Create in DB
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
            echo json_encode([
                "error"   => "Failed to create guest",
                "details" => $e->getMessage()
            ]);
        }
    }


    public function updateGuest(): void
    {
        try {
            // Check authentication:
            $auth = $this->authenticateByUserId();
            if ($auth === null) {
                return; // User not authenticated, response already sent in authenticateByUserId()
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // 1) Ensure we have guest_id
            if (!isset($data['guest_id'])) {
                throw new \InvalidArgumentException("Field 'guest_id' is required for update.");
            }
            $guestId = intval($data['guest_id']);

            // 2) Fetch the existing guest from the DB
            $oldGuest = $this->guestModel->getGuestById($guestId);
            if (!$oldGuest) {
                throw new Exception("Guest not found.");
            }

            // 3) Figure out if user has added (or removed) a plus-one name
            $newPlusOneName = $data['first_name_plus_1'] ?? null;

            // If the user *added* a plus-one name (i.e., old was empty, new is not empty),
            // and there was no existing rsvp_status_plus_one, set it to 'pending'.
            $oldPlusOneName = $oldGuest['first_name_plus_1'] ?? null;
            $oldRsvpStatusPlusOne = $oldGuest['rsvp_status_plus_one'] ?? null;

            if (!empty($newPlusOneName) && empty($oldPlusOneName)) {
                // Did old rsvp_status_plus_one exist? If it's empty, set to 'pending'.
                if (empty($oldRsvpStatusPlusOne)) {
                    // We manually set rsvp_status_plus_one for the update
                    $data['rsvp_status_plus_one'] = 'pending';
                }
            }

            // If user removed the plus-one name, you can decide if you want to reset
            // $data['rsvp_status_plus_one'] to null. For now we do nothing.

            // 4) Possibly regenerate unique_path if `first_name` has changed
            if (isset($data['first_name'])) {
                $data['unique_path'] = $this->generateUniquePath(
                    $data['first_name'],
                    $newPlusOneName,
                    $guestId // exclude this guest from path uniqueness check
                );
            }

            // 5) Build final data for the model
            //    (Admin can only edit names & gender, not RSVP statuses directly)
            $guestData = [
                'first_name'         => $data['first_name'],
                'first_name_plus_1'  => $newPlusOneName,
                'unique_path'        => $data['unique_path']         ?? null,
                'gender'             => $data['gender']              ?? null
            ];

            // If we decided to set rsvp_status_plus_one above, include it in the update
            if (array_key_exists('rsvp_status_plus_one', $data)) {
                $guestData['rsvp_status_plus_one'] = $data['rsvp_status_plus_one'];
            }

            // 6) Perform update via model
            $updatedGuest = $this->guestModel->updateGuest($guestId, $guestData);

            http_response_code(200);
            echo json_encode([
                "message" => "Guest updated successfully",
                "guest"   => $updatedGuest
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error"   => "Failed to update guest",
                "details" => $e->getMessage()
            ]);
        }
    }


    /**
     * List all guests.
     */
    public function listGuests(): void
    {
        try {
            // Check authentication:
            $auth = $this->authenticateByUserId();
            if ($auth === null) {
                return; // User not authenticated, response already sent in authenticateByUserId()
            }

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
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve guest", "details" => $e->getMessage()]);
        }
    }


    /**
     * Delete a guest by ID.
     */
    public function deleteGuest(): void
    {
        try {
            // Check authentication:
            $auth = $this->authenticateByUserId();
            if ($auth === null) {
                return; // User not authenticated, response already sent in authenticateByUserId()
            }

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
    }

    /**
     * Update guest RSVP and alcohol preferences using unique_path.
     * Only the columns provided in the request will be updated.
     */
    public function updateGuestDataByUser(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate unique_path is provided
            if (!isset($data['unique_path']) || empty($data['unique_path'])) {
                throw new \InvalidArgumentException("Parameter 'unique_path' is required.");
            }
            $uniquePath = trim($data['unique_path']);

            // Retrieve guest by unique_path
            $guest = $this->guestModel->getByUniquePath($uniquePath);
            if (!$guest) {
                throw new Exception("Guest not found.");
            }
            $guestId = intval($guest['guest_id']);

            // Define mapping: input key => database column
            $fieldMapping = [
                'rsvp_status'           => 'rsvp_status',
                'rsvp_status_plus_one'  => 'rsvp_status_plus_one',
                'alcohol_preferences'          => 'alcohol_preferences',
                'alcohol_preferences_plus_one' => 'alcohol_preferences_plus_one',
                'wine_type'             => 'wine_type',
                'wine_type_plus_one'    => 'wine_type_plus_one',
                'custom_alcohol'        => 'custom_alcohol',
                'custom_alcohol_plus_one' => 'custom_alcohol_plus_one'
            ];

            // Allowed values for RSVP status
            $allowedStatusValues = ['pending', 'accepted', 'declined'];
            $updateData = [];

            // Iterate over allowed fields and map them to DB columns.
            foreach ($fieldMapping as $inputKey => $dbColumn) {
                if (array_key_exists($inputKey, $data)) {
                    // Validate RSVP status values.
                    if (in_array($inputKey, ['rsvp_status', 'rsvp_status_plus_one'])) {
                        if (!in_array($data[$inputKey], $allowedStatusValues)) {
                            throw new \InvalidArgumentException("Invalid value for $inputKey.");
                        }
                    }
                    $updateData[$dbColumn] = $data[$inputKey];
                }
            }

            if (empty($updateData)) {
                throw new \InvalidArgumentException("No valid fields provided for update.");
            }

            // Use the model method to update only the provided fields.
            $result = $this->guestModel->updateGuestDataByUser($guestId, $updateData);

            http_response_code(200);
            echo json_encode([
                "message" => "Guest RSVP and preferences updated successfully",
                "guest"   => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error"   => "Failed to update guest RSVP and preferences",
                "details" => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper: Generate a unique URL-friendly path.
     * If plusOneName is provided, it concatenates transliterated first names.
     * Then it checks for duplicates in the DB and appends a suffix if needed.
     *
     * @param string      $firstName
     * @param string|null $plusOneName
     * @param int|null    $excludeGuestId  If provided, do not consider that guest's unique_path as a conflict.
     *
     * @return string
     */
    private function generateUniquePath(
        string $firstName,
        ?string $plusOneName = null,
        ?int $excludeGuestId = null
    ): string {
        // 1) Build the base (transliterate + underscores)
        $base = $this->transliterate($firstName);
        if ($plusOneName) {
            $base .= '_' . $this->transliterate($plusOneName);
        }
        // 2) Lowercase and remove any non-(a-z0-9-)
        $base = strtolower($base);
        $base = preg_replace('/[^a-z0-9_\-]/', '', $base);


        // 3) Check uniqueness; if found, append suffix
        $uniquePath = $base;
        $suffix     = 'a';  // or anything you like, e.g. 'guest', etc.

        // Keep adjusting until we find something not taken
        while ($this->guestModel->getByUniquePathExcludingId($uniquePath, $excludeGuestId)) {
            $uniquePath = $base . '-' . $suffix;
            // Advance the suffix
            $suffix = chr(ord($suffix) + 1);
        }

        return $uniquePath;
    }

    /**
     * Helper: Transliterate Ukrainian characters to their Latin equivalent.
     */
    private function transliterate(string $text): string
    {
        $map = [
            'А' => 'A',
            'а' => 'a',
            'Б' => 'B',
            'б' => 'b',
            'В' => 'V',
            'в' => 'v',
            'Г' => 'H',
            'г' => 'h',
            'Ґ' => 'G',
            'ґ' => 'g',
            'Д' => 'D',
            'д' => 'd',
            'Е' => 'E',
            'е' => 'e',
            'Є' => 'Ye',
            'є' => 'ie',
            'Ж' => 'Zh',
            'ж' => 'zh',
            'З' => 'Z',
            'з' => 'z',
            'И' => 'Y',
            'и' => 'y',
            'І' => 'I',
            'і' => 'i',
            'Ї' => 'Yi',
            'ї' => 'i',
            'Й' => 'Y',
            'й' => 'i',
            'К' => 'K',
            'к' => 'k',
            'Л' => 'L',
            'л' => 'l',
            'М' => 'M',
            'м' => 'm',
            'Н' => 'N',
            'н' => 'n',
            'О' => 'O',
            'о' => 'o',
            'П' => 'P',
            'п' => 'p',
            'Р' => 'R',
            'р' => 'r',
            'С' => 'S',
            'с' => 's',
            'Т' => 'T',
            'т' => 't',
            'У' => 'U',
            'у' => 'u',
            'Ф' => 'F',
            'ф' => 'f',
            'Х' => 'Kh',
            'х' => 'kh',
            'Ц' => 'Ts',
            'ц' => 'ts',
            'Ч' => 'Ch',
            'ч' => 'ch',
            'Ш' => 'Sh',
            'ш' => 'sh',
            'Щ' => 'Shch',
            'щ' => 'shch',
            'Ю' => 'Yu',
            'ю' => 'iu',
            'Я' => 'Ya',
            'я' => 'ia',
            "'" => '',
            "’" => ''
        ];

        return strtr($text, $map);
    }
}
