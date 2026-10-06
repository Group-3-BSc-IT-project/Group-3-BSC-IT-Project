<?php
class AuthService
{
    public function __construct(private UserRepository $users) {}
    public function login(string $email,string $password): bool { $u=$this->users->findByEmail($email); if(!$u || !password_verify($password,$u['password'])) return false; Session::login($u); return true; }
    public function register(string $name,string $email,string $phone,string $address,string $password): array {
        if($this->users->emailExists($email)) return [false,'Email already registered.'];
        $ok=$this->users->create($name,$email,$phone,$address,password_hash($password,PASSWORD_DEFAULT),'customer');
        return[$ok,$ok?'Registration successful. You can now login.':'Registration failed.'];
    }
    public function createMechanic(string $name,string $email,string $phone,string $address,string $password): array {
        if($this->users->emailExists($email)) return[false,'That email address is already registered.'];
        $ok=$this->users->create($name,$email,$phone,$address,password_hash($password,PASSWORD_DEFAULT),'mechanic');
        return[$ok,$ok?'Mechanic account created successfully.':'Failed to create mechanic.'];
    }
}
