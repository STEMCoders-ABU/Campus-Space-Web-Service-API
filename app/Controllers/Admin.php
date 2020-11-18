<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Admin extends BaseController
{
    use ResponseTrait;

    public function __construct() {
        $this->model = \model('App\Models\AdminModel', true);
        $this->admin_session_name = 'admin_session_data';
    }

    public function addFaculty()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                $data = $this->request->getJSON(true);
                if (!$data)
                    return $this->failNotFound('No valid data was provided!');

                // initial validation rules
                $validationRules = [
                    'faculty' => 'required|trim|alpha_numeric_space|max_length[60]|is_unique[faculties.faculty]',
                ];

                $this->validation->setRules($validationRules);
                if ($this->validation->run($data)) {
                    $entries = [
                        'faculty' => $data['faculty'],
                    ];

                    // Add the faculty to the database
                    $id = $this->model->addFaculty($entries);
                    if ($id) 
                        return $this->respondNoContent('Faculty added.');
                    else 
                        return $this->fail('An internal error occured');
                }
                else {
                    return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
                }
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function updateFaculty($id)
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                $data = $this->request->getJSON(true);
                if (!$data)
                    return $this->failNotFound('No valid data was provided!');

                // get the faculty
                $faculty = $this->model->getFaculty($id);
                if (!$faculty)
                    return $this->failNotFound('Faculty not found!');

                // initialize entries
                $entries = [];

                // initial validation rules
                $validationRules = [];

                if (isset($data['faculty'])) {
                    $validationRules['faculty'] = "required|trim|alpha_numeric_space|max_length[60]|is_unique[faculties.faculty,faculty,{$faculty['faculty']}]";
                    $entries['faculty'] = $data['faculty'];
                }

                if (\count($validationRules) == 0)
                    return $this->failNotFound('No data was provided!');

                $this->validation->setRules($validationRules);
                if ($this->validation->run($data)) {
                    if (\count($entries) > 0) {
                        if ($this->model->updateFaculty($entries, $id))
                            return $this->respondNoContent('Faculty updated');
                        else 
                            return $this->fail('An unknown error occured');
                    }

                    return $this->fail('An unknown error occured');
                }
                else {
                    return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
                }
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function removeFaculty($id)
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                // get the faculty
                $faculty = $this->model->getFaculty($id);
                if (!$faculty)
                    return $this->failNotFound('Faculty not found!');

                if ($this->model->removeFaculty($id)) {
                    $this->respondNoContent('Faculty removed.');
                }
                else
                    return $this->fail('Failed to remove faculty');
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function addDepartment()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                $data = $this->request->getJSON(true);
                if (!$data)
                    return $this->failNotFound('No valid data was provided!');

                // initial validation rules
                $validationRules = [
                    'department' => 'required|trim|alpha_numeric_space|max_length[60]|is_unique[departments.department]',
                    'faculty_id' => 'required|trim|is_numeric|is_not_unique[faculties.id]',
                ];

                $this->validation->setRules($validationRules);
                if ($this->validation->run($data)) {
                    $entries = [
                        'department' => $data['department'],
                        'faculty_id' => $data['faculty_id'],
                    ];

                    // Add the department to the database
                    $id = $this->model->addDepartment($entries);
                    if ($id) 
                        return $this->respondNoContent('Department added.');
                    else 
                        return $this->fail('An internal error occured');
                }
                else {
                    return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
                }
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function updateDepartment($id)
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                $data = $this->request->getJSON(true);
                if (!$data)
                    return $this->failNotFound('No valid data was provided!');

                // get the department
                $department = $this->model->getDepartment($id);
                if (!$department)
                    return $this->failNotFound('Department not found!');

                // initialize entries
                $entries = [];

                // initial validation rules
                $validationRules = [];

                if (isset($data['department'])) {
                    $validationRules['department'] = "required|trim|alpha_numeric_space|max_length[60]|is_unique[departments.department,department,{$department['department']}]";
                    $entries['department'] = $data['department'];
                }

                if (isset($data['faculty_id'])) {
                    $validationRules['faculty_id'] = "required|trim|is_numeric|is_not_unique[faculties.id]";
                    $entries['faculty_id'] = $data['faculty_id'];
                }

                if (\count($validationRules) == 0)
                    return $this->failNotFound('No data was provided!');

                $this->validation->setRules($validationRules);
                if ($this->validation->run($data)) {
                    if (\count($entries) > 0) {
                        if ($this->model->updateDepartment($entries, $id))
                            return $this->respondNoContent('Department updated');
                        else 
                            return $this->fail('An unknown error occured');
                    }

                    return $this->fail('An unknown error occured');
                }
                else {
                    return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
                }
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function removeDepartment($id)
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                // get the department
                $department = $this->model->getDepartment($id);
                if (!$department)
                    return $this->failNotFound('Department not found!');

                if ($this->model->removeDepartment($id)) {
                    $this->respondNoContent('Department removed.');
                }
                else
                    return $this->fail('Failed to remove department');
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }

    public function createSession() 
	{
		try {
            // Set the headers
            $this->setDefaultHeaders();

            // Authenticate
            $admin = $this->authenticate();

            if ($admin) {
				$this->session->set($this->admin_session_name, $admin);
				return $this->respondNoContent();
            }
            else {
                // Authentication failed
                return $this->failUnauthorized('Authentication failed!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

	public function verifySession() 
	{
        try {
            // Set the headers
            $this->setDefaultHeaders();

            if ($this->session->has($this->admin_session_name)) {
                // A valid session exists
                return $this->respondNoContent('Session verified');
            }
            else {
                // Invalid or expired session
                // Clear any session data that exists
                $this->session->remove($this->admin_session_name);

                return $this->failUnauthorized('Session verification failed!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

	public function clearSession() 
	{
        try {
            // Set the headers
            $this->setDefaultHeaders();

            // Clear the current session
            $this->session->destroy();
            return $this->respondDeleted('Session cleared');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
	}

    private function authenticate() 
	{
		if (! $this->request->hasHeader('Authorization'))
			return FALSE;

		$credentials = $this->request->getHeader('Authorization')->getValue();
		$credentials = \explode(' ', $credentials);

		if (\count($credentials) < 2)
			return FALSE;

		if ($credentials[0] !== 'Basic')
			return FALSE;
		
		$credentials = $credentials[1];
		$credentials = \base64_decode($credentials, TRUE);

		if (! $credentials)
			return FALSE;

		$credentials = \explode(':', $credentials);

		if (\count($credentials) < 2)
			return FALSE;

		$username = $credentials[0];
		$password = $credentials[1];

		$data = $this->model->getAdminData($username);

		if (! $data)
			return FALSE;

		if (password_verify($password, $data['password']))
			return $data;
		else
			return FALSE;
	}

	protected function authenticateSession() 
	{
        // Check if a valid session exists
        if ($this->session->has($this->admin_session_name)) {
            $data = $this->session->get($this->admin_session_name);
            return $data;
		}
        else {
			// No session data found. Let's try other authentication method(s)
			// We can authenticate if a BASIC AUTH header was provided.
			return $this->authenticate();
		}
    }
    
    public function addModerator()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $admin = $this->authenticateSession();

            if ($admin) {
                $data = $this->request->getJSON(true);
                if (!$data)
                    return $this->failNotFound('No valid data was provided!');

                // initial validation rules
                $validationRules = [
                    'full_name' => 'required|trim|alpha_numeric_space|max_length[60]',
                    'username' => 'required|trim|alpha_numeric_space|max_length[12]|is_unique[moderators.username]',
                    'email' => 'required|trim|valid_email|max_length[50]|is_unique[moderators.email]',
                    'phone' => 'required|trim|alpha_numeric|max_length[20]|is_unique[moderators.phone]',
                    'gender' => 'required|trim|in_list[Male, Female]',
                    'faculty_id' => 'required|trim|is_numeric|is_not_unique[faculties.id]',
                    'department_id' => 'required|trim|is_numeric|is_not_unique[departments.id]',
                    'level_id' => 'required|trim|is_numeric|is_not_unique[levels.id]',
                    'password' => 'required',
                ];

                $this->validation->setRules($validationRules);
                if ($this->validation->run($data)) {
                    // Make sure the moderator is new
                    if ($this->model->getModerator([
                        'faculty_id' => $data['faculty_id'],
                        'department_id' => $data['department_id'],
                        'level_id' => $data['level_id'],
                    ]))
                        return $this->failResourceExists('A moderator for this combination exists!');

                    $entries = [
                        'full_name' => $data['full_name'],
                        'username' => $data['username'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'gender' => $data['gender'],
                        'faculty_id' => $data['faculty_id'],
                        'department_id' => $data['department_id'],
                        'level_id' => $data['level_id'],
                        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    ];

                    // Add the faculty to the database
                    $id = $this->model->addModerator($entries);
                    if ($id) 
                        return $this->respondNoContent('Moderator added.');
                    else 
                        return $this->fail('An internal error occured');
                }
                else {
                    return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
                }
            }
            else // failed authentication
                return $this->failUnauthorized('Authentication failed!');
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }
}