<?php

// models/User.php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($email, $password) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $stored_password = $stmt->fetchColumn();

        return password_verify($password, $stored_password);
    }
}
