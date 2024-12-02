<?php

class Subscription
{
    private $pdo;

    public function __construct($db)
    {
        $this->pdo = $db->getConnection();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO subscriptions (ad_url, email, confirmation_token, status) VALUES (:ad_url, :email, :token, :status)');
        $stmt->execute([
            ':ad_url' => $data['ad_url'],
            ':email' => $data['email'],
            ':token' => $data['confirmation_token'],
            ':status' => $data['status'],
        ]);

        return $this->pdo->lastInsertId();
    }

    public function findByToken($token)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM subscriptions WHERE confirmation_token = :token');
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function activate($id)
    {
        $stmt = $this->pdo->prepare('UPDATE subscriptions SET status = "active", confirmation_token = NULL WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
