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
                    first_name, last_name, unique_path, rsvp_status, rsvp_status_plus_one, alcohol_preferences, first_name_plus_1, last_name_plus_1
                  ) VALUES (
                    :first_name, :last_name, :unique_path, :rsvp_status, :rsvp_status_plus_one, :alcohol_preferences, :first_name_plus_1, :last_name_plus_1
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':first_name_plus_1', $data['first_name_plus_1']);
        $stmt->bindParam(':last_name_plus_1', $data['last_name_plus_1']);
        $stmt->bindParam(':unique_path', $data['unique_path']);
        $stmt->bindParam(':rsvp_status', $data['rsvp_status']);
        $stmt->bindParam(':rsvp_status_plus_one', $data['rsvp_status_plus_one']);
        $stmt->bindParam(':alcohol_preferences', $data['alcohol_preferences']);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert guest record.");
        }

        $guestId = (int)$this->conn->lastInsertId();
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
     * Retrieve all guest records.
     */
    public function listGuests(): array
    {
        $query = "SELECT * FROM guests ORDER BY created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update an existing guest record.
     */
    public function updateGuest(int $guestId, array $data): array
    {
        $query = "UPDATE guests SET
                    first_name = :first_name,
                    last_name = :last_name,
                    first_name_plus_1 = :first_name_plus_1,
                    last_name_plus_1 = :last_name_plus_1,
                    unique_path = :unique_path,
                    rsvp_status = :rsvp_status,
                    rsvp_status_plus_one = :rsvp_status_plus_one,
                    alcohol_preferences = :alcohol_preferences,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE guest_id = :guest_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':first_name_plus_1', $data['first_name_plus_1']);
        $stmt->bindParam(':last_name_plus_1', $data['last_name_plus_1']);
        $stmt->bindParam(':unique_path', $data['unique_path']);
        $stmt->bindParam(':rsvp_status', $data['rsvp_status']);
        $stmt->bindParam(':rsvp_status_plus_one', $data['rsvp_status_plus_one']);
        $stmt->bindParam(':alcohol_preferences', $data['alcohol_preferences']);
        $stmt->bindParam(':guest_id', $guestId, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update guest record.");
        }

        return $this->getGuestById($guestId);
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
}
