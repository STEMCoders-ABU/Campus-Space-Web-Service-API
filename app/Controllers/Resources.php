<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Resources extends BaseController
{
    use ResponseTrait;

    public function __construct()
	{
		$this->model = \model('App\Models\ResourcesModel', true);
    }
    
    public function show()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $constraints = [];

            $course_id = $this->request->getGet('course_id');
            $category_id = $this->request->getGet('category_id');
            $level_id = $this->request->getGet('level_id');
            $department_id = $this->request->getGet('department_id');
            $faculty_id = $this->request->getGet('faculty_id');
            
            if ($level_id)
                $constraints['resources.level_id'] = $level_id;
    
            if ($department_id)
                $constraints['resources.department_id'] = $department_id;
    
            if ($faculty_id)
                $constraints['resources.faculty_id'] = $faculty_id;
    
            if ($category_id)
                $constraints['resources.category_id'] = $category_id;
    
            if ($course_id)
                $constraints['resources.course_id'] = $course_id;
    
            if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
                $join = TRUE;
            else
                $join = FALSE;
    
            if ($this->request->getGet('order_by_downloads') && $this->request->getGet('order_by_downloads') == 'true')
                $by_downloads = TRUE;
            else
                $by_downloads = FALSE;
    
            if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
                $size = $this->request->getGet('size');
            else {
                if ($by_downloads)
                    $size = 10;
                else
                    $size = 0;
            }
    
            if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
                $offset = $this->request->getGet('offset');
            else
                $offset = 0;
    
            $resourcesModel = \model('App\Models\ResourcesModel', true);
    
            if ($by_downloads)
                $data = $resourcesModel->getResourcesByDownloads($constraints, $size, $offset, $join);
            else
                $data = $resourcesModel->getResources($constraints, $size, $offset, $join);
    
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('No resource matching the provided combination was found');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_resource()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $resource_id = $this->request->getGet('resource_id');
            if ($resource_id == null)
                return $this->failNotFound('No resource_id was provided!');
    
            if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
                $join = TRUE;
            else
                $join = FALSE;
    
            $resourcesModel = \model('App\Models\ResourcesModel', true);
            $data = $resourcesModel->get_resource_item(['resources.id' => $resource_id], $join);
            if ($data) {
                $data['file_url'] = \site_url(RESOURCES_PATH . $data['file']);
                return $this->respond($data, 200);
            }
            else {
                return $this->failNotFound('The requested resource does not exist!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function search()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $constraints = [];

            $search = $this->request->getPost('search');
            
            if (!$search)
                $search = $this->request->getJSON(true)['search'];

            if (! $search)
                return $this->failNotFound('No input data was provided!');
    
            $course_id = $this->request->getGet('course_id');
            $category_id = $this->request->getGet('category_id');
            $level_id = $this->request->getGet('level_id');
            $department_id = $this->request->getGet('department_id');
            $faculty_id = $this->request->getGet('faculty_id');
            
            if ($level_id)
                $constraints['resources.level_id'] = $level_id;
    
            if ($department_id)
                $constraints['resources.department_id'] = $department_id;
    
            if ($faculty_id)
                $constraints['resources.faculty_id'] = $faculty_id;
    
            if ($category_id)
                $constraints['resources.category_id'] = $category_id;
            log_message('error', $category_id);
            if ($course_id)
                $constraints['resources.course_id'] = $course_id;
    
            if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
                $join = TRUE;
            else
                $join = FALSE;
    
            if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
                $size = $this->request->getGet('size');
            else
                $size = 0;
    
            if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
                $offset = $this->request->getGet('offset');
            else
                $offset = 0;
    
            $resourcesModel = \model('App\Models\ResourcesModel', true);
            $data = $resourcesModel->getSearchedResources($constraints, $search, $size, $offset, $join);
    
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('No resource matching the provided combination was found');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function show_category_comments()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $constraints = [];

            $course_id = $this->request->getGet('course_id');
            $category_id = $this->request->getGet('category_id');
            $level_id = $this->request->getGet('level_id');
            $department_id = $this->request->getGet('department_id');
            
            if ($level_id)
                $constraints['category_comments.level_id'] = $level_id;
    
            if ($department_id)
                $constraints['category_comments.department_id'] = $department_id;
    
            if ($category_id)
                $constraints['category_comments.category_id'] = $category_id;
    
            if ($course_id)
                $constraints['category_comments.course_id'] = $course_id;
    
            if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
                $size = $this->request->getGet('size');
            else
                $size = 0;
    
            if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
                $offset = $this->request->getGet('offset');
            else
                $offset = 0;
    
            if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
                $join = TRUE;
            else
                $join = FALSE;
    
            $resourcesModel = \model('App\Models\ResourcesModel', true);
            $data = $resourcesModel->getCategoryComments($constraints, $size, $offset, $join);
    
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('No category comments matching the provided combination was found');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function show_comments()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $constraints = [];

            $resource_id = $this->request->getGet('resource_id');
    
            if ($resource_id != null)
                $constraints['resource_id'] = $resource_id;
    
            if ($this->request->getGet('size') && is_numeric($this->request->getGet('size')))
                $size = $this->request->getGet('size');
            else
                $size = 0;
    
            if ($this->request->getGet('offset') && is_numeric($this->request->getGet('offset')))
                $offset = $this->request->getGet('offset');
            else
                $offset = 0;
    
            if ($this->request->getGet('join') && $this->request->getGet('join') == 'true')
                $join = TRUE;
            else
                $join = FALSE;
    
            $resourcesModel = \model('App\Models\ResourcesModel', true);
            $data = $resourcesModel->getResourceComments($constraints, $size, $offset, $join);
    
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('No resource comments matching the provided resource id was found');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function add_category_comment()
	{
        try {
            // Set the headers
            $this->setDefaultHeaders();
            
            $fields = $this->request->getJSON(true);
            
            if (!$fields)
                $fields = $this->request->getPost();

            if (! $fields)
                return $this->failNotFound('No input data was provided!');
    
            $validationRules = [
                'author' => 'required|max_length[20]',
                'comment' => 'required|max_length[500]',
                'category_id' => 'required|is_not_unique[resource_categories.id]',
                'course_id' => 'required|is_not_unique[courses.id]',
                'department_id' => 'required|is_not_unique[departments.id]',
                'level_id' => 'required|is_not_unique[levels.id]',
            ];
    
            $this->validation->setRules($validationRules);
            if (! $this->validation->run($fields)) {
                return $this->failValidationError($this->array_to_string($this->validator->getErrors()));
            }
    
            $model = \model('App\Models\ResourcesModel', true);
            $entries = [
                'author' => $fields['author'],
                'comment' => $fields['comment'],
                'category_id' => $fields['category_id'],
                'course_id' => $fields['course_id'],
                'department_id' => $fields['department_id'],
                'level_id' => $fields['level_id'],
            ];
    
            if ($model->add_category_comment($entries)) {
                return $this->respond([], 200);
            }
            else {
                return $this->fail('Failed to category add comment!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }
    
    public function add_comment()
	{
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $fields = $this->request->getJSON(true);
            
            if (!$fields)
                $fields = $this->request->getPost();

            if (! $fields)
                return $this->failNotFound('No input data was provided!');
    
            $validationRules = [
                'author' => 'required|max_length[20]',
                'comment' => 'required|max_length[500]',
                'resource_id' => 'required|is_not_unique[resources.id]',
            ];
    
            $this->validation->setRules($validationRules);
            if (! $this->validation->run($fields)) {
                return $this->failValidationError($this->array_to_string($this->validation->getErrors()));
            }
    
            $model = \model('App\Models\ResourcesModel', true);
            $entries = [
                'author' => $fields['author'],
                'comment' => $fields['comment'],
                'resource_id' => $fields['resource_id'],
            ];
    
            if ($model->add_comment($entries)) {
                return $this->respond([], 200);
            }
            else {
                return $this->fail('Failed to add comment!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }
    
    public function download()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            if ($this->request->getGet('resource_id') != null && is_numeric($this->request->getGet('resource_id')))
                $resource_id = $this->request->getGet('resource_id');
            else
                return $this->failNotFound('No resource_id was provided!');

            $resources_model = \model('App\Models\ResourcesModel', true);
            $resource = $resources_model->get_resource($resource_id);
            
            if ($resource) {
                $file = RESOURCES_PATH . $resource['file'];
                
                if (file_exists($file)) {
                    $entry = ['downloads' => $resource['downloads'] + 1];
                    $resources_model->update_resource($resource_id, $entry);
                    $res = $this->response->download($file, NULL, TRUE);

                    // Set the headers
                    $this->setDefaultDownloadHeaders($res);

                    return $res;
                }
                else {
                    return $this->failNotFound('The requested resource file does not exist!');
                }
            }
            else {
                return $this->failNotFound('The requested resource does not exist!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function addSubscription()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $data = $this->request->getJSON(true);
            if (!$data)
                return $this->failNotFound('No valid data was provided!');

            // initial validation rules
            $validationRules = [
                'email' => 'required|trim|valid_email|max_length[50]',
                'faculty_id' => 'required|trim|is_numeric|is_not_unique[faculties.id]',
                'department_id' => 'required|trim|is_numeric|is_not_unique[departments.id]',
                'level_id' => 'required|trim|is_numeric|is_not_unique[levels.id]',
            ];

            $this->validation->setRules($validationRules);
            if ($this->validation->run($data)) {
                // Make sure the subscription is new
                if ($this->model->getSubscription([
                    'faculty_id' => $data['faculty_id'],
                    'department_id' => $data['department_id'],
                    'level_id' => $data['level_id'],
                    'email' => $data['email'],
                ]))
                    return $this->failResourceExists('Subscription exists!');

                $entries = [
                    'faculty_id' => $data['faculty_id'],
                    'department_id' => $data['department_id'],
                    'level_id' => $data['level_id'],
                    'email' => $data['email'],
                ];

                $id = $this->model->addSubscription($entries);
                if ($id) 
                    return $this->respondNoContent('Subscription added.');
                else 
                    return $this->fail('An internal error occured');
            }
            else {
                return $this->failValidationError($this->errorArrayToString($this->validation->getErrors()));
            }
        } catch (\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occured', 500);
        }
    }
}