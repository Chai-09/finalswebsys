<?php

namespace App\Controllers;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function store() // Save Data in Register.php
    {
        helper(['form']);
        
        $rules = [
            'name'            => 'required|min_length[5]|max_length[50]',
            'email'           => 'required|min_length[12]|max_length[100]|valid_email|is_unique[users.email]',
            'password'        => 'required|min_length[5]|max_length[50]',
            'confirmpassword' => 'matches[password]'
        ];

        if ($this->validate($rules)) {
            $account = new UserModel();
            
            $data = [
                'name'      => $this->request->getVar('name'),
                'email'     => $this->request->getVar('email'),
                'password'  => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'user_role' => $this->request->getVar('user_role'),
            ];
            
            $account->save($data);
            
            return redirect()->to('/signin');
        } else {
            $data['validation'] = $this->validator;
            return view('register', $data);
        }
    }

    public function signin() // Load the sign-in view
    {
        helper(['form']);
        $data = [];
        return view('sign', $data);
    }

    public function login() // Login Logic
    {
        helper(['form']);
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first(); 

        if ($user && password_verify($password, $user['password'])) { 
            
            $sessionData = [
                'id'        => $user['id'],
                'name'      => $user['name'],
                'email'     => $user['email'],
                'user_role' => $user['user_role'],
                'isLoggedIn'=> true,
            ];
            
            session()->set($sessionData);

            
            switch ($user['user_role']) {
                case 'head_admin':
                    return redirect()->to('/head_admin');
                case 'worker':
                    return redirect()->to('/workers');
                case 'user':
                    return redirect()->to('/user');
                default:
                    return redirect()->to('/dashboard');
            }
        } else {
            $data['validation'] = 'Invalid email or password.';
            return view('sign', $data); 
        }
    }

    public function register() // form of register
    {
        helper(['form']);
        $data = [];
        return view('register', $data);
    }

    public function logout()
    {
        session()->destroy(); // Destroy all session data
        return redirect()->to('/signin'); // Redirect to sign in page
    }

    /*public function dashboard()
    {
        // Check if the user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/signin'); // Redirect to sign-in page
        }
    
        // Load the dashboard view
        return view('dashboard');
    }*/

    //user_role's to their respective dashboards. here
    public function headAdminDashboard()
    {
        if (session()->get('user_role') !== 'head_admin') {
            return redirect()->to('/signin');
        }
        return view('roleDashboard/head_admin');
    }

    public function workerDashboard()
    {
        if (session()->get('user_role') !== 'worker') {
            return redirect()->to('/signin');
        }
        return view('roleDashboard/workers');
    }

    public function userDashboard()
    {
        if (session()->get('user_role') !== 'user') {
            return redirect()->to('/signin');
        }
        return view('roleDashboard/user');
    }
    // hanggang here :>

}
