<?php

namespace Model;

use PDO;
use Exception;

class GuestModel
{
    protected PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Create a new guest record.
     */
    public function createGuest(array $data): array
    {
        $query = "INSERT INTO guests (
                    first_name,
                    first_name_plus_1,
                    unique_path,
                    gender,
                    rsvp_status,
                    rsvp_status_plus_one
                  ) VALUES (
                    :first_name,
                    :first_name_plus_1,
                    :unique_path,
                    :gender,
                    :rsvp_status,
                    :rsvp_status_plus_one
                  )";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':first_name',           $data['first_name']);
        $stmt->bindParam(':first_name_plus_1',    $data['first_name_plus_1']);
        $stmt->bindParam(':unique_path',          $data['unique_path']);
        $stmt->bindParam(':gender',               $data['gender']);
        $stmt->bindParam(':rsvp_status',          $data['rsvp_status']);
        $stmt->bindParam(':rsvp_status_plus_one', $data['rsvp_status_plus_one']);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert guest record.");
        }

        $guestId = (int) $this->conn->lastInsertId();
        return $this->getGuestById($guestId);
    }

    /**
     * Update an existing guest record.
     */
    public function updateGuest(int $guestId, array $data): array
    {
        // Build the base update query with all columns you might update
        // (matching the columns you insert in createGuest).
        // Note: This includes rsvp_status_plus_one in case the controller wants to set it.
        $query = "UPDATE guests
                  SET
                    first_name = :first_name,
                    first_name_plus_1 = :first_name_plus_1,
                    unique_path = :unique_path,
                    gender = :gender,
                    rsvp_status_plus_one = :rsvp_status_plus_one,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE guest_id = :guest_id";

        // Prepare the statement
        $stmt = $this->conn->prepare($query);

        // Bind each parameter explicitly, just like in createGuest().
        // The controller decides whether or not to provide rsvp_status_plus_one.
        $stmt->bindParam(':first_name',           $data['first_name']);
        $stmt->bindParam(':first_name_plus_1',    $data['first_name_plus_1']);
        $stmt->bindParam(':unique_path',          $data['unique_path']);
        $stmt->bindParam(':gender',               $data['gender']);
        $stmt->bindParam(':rsvp_status_plus_one', $data['rsvp_status_plus_one']);
        $stmt->bindParam(':guest_id',             $guestId, PDO::PARAM_INT);

        // Execute the update
        if (!$stmt->execute()) {
            throw new Exception("Failed to update guest record.");
        }

        // Return the updated record
        return $this->getGuestById($guestId);
    }



    /**
     * Retrieve a guest record by ID.
     */
    public function getGuestById(int $guestId): ?array
    {
        $query = "SELECT * FROM guests WHERE guest_id = :guest_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':guest_id', $guestId, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);
        return $guest ? $guest : null;
    }

    /**
     * Retrieve a guest record by unique_path.
     */
    public function getByUniquePath(string $uniquePath): ?array
    {
        $query = "SELECT * FROM guests WHERE unique_path = :unique_path LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':unique_path', $uniquePath, PDO::PARAM_STR);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);
        return $guest ? $guest : null;
    }

    /**
     * Retrieve a guest record by unique_path, excluding a specific guest_id (if provided).
     */
    public function getByUniquePathExcludingId(string $uniquePath, ?int $excludeGuestId): ?array
    {
        // If $excludeGuestId is null, fallback to normal getByUniquePath check
        if ($excludeGuestId === null) {
            return $this->getByUniquePath($uniquePath);
        }

        $query = "SELECT *
              FROM guests
              WHERE unique_path = :unique_path
                AND guest_id != :exclude_id
              LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':unique_path',  $uniquePath);
        $stmt->bindParam(':exclude_id',   $excludeGuestId, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        return $guest ? $guest : null;
    }


    /**
     * Retrieve all guest records.
     */
    public function listGuests(): array
    {
        $query = "SELECT * FROM guests ORDER BY created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a guest record.
     */
    public function deleteGuest(int $guestId): void
    {
        $query = "DELETE FROM guests WHERE guest_id = :guest_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':guest_id', $guestId, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete guest record.");
        }
    }

    public function updateGuestDataByUser(int $guestId, array $data): array
    {
        // Allowed database columns for update.
        $allowedColumns = [
            'rsvp_status',
            'rsvp_status_plus_one',
            'alcohol_preferences',
            'alcohol_preferences_plus_one',
            'wine_type',
            'wine_type_plus_one',
            'custom_alcohol',
            'custom_alcohol_plus_one'
        ];
        $updateData = [];

        // Filter data to include only allowed columns.
        foreach ($allowedColumns as $column) {
            if (array_key_exists($column, $data)) {
                $updateData[$column] = $data[$column];
            }
        }

        if (empty($updateData)) {
            throw new Exception("No valid fields provided for update.");
        }

        // Build the SQL update statement dynamically.
        $fieldsToUpdate = [];
        $params = [];
        foreach ($updateData as $column => $value) {
            $fieldsToUpdate[] = "$column = :$column";
            $params[":$column"] = $value;
        }

        // Always update the updated_at timestamp.
        $fieldsToUpdate[] = "updated_at = CURRENT_TIMESTAMP";
        $params[':guest_id'] = $guestId;

        $query = "UPDATE guests SET " . implode(", ", $fieldsToUpdate) . " WHERE guest_id = :guest_id";

        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute($params)) {
            throw new Exception("Failed to update guest record.");
        }

        // Return the updated guest record.
        return $this->getGuestById($guestId);
    }
}
