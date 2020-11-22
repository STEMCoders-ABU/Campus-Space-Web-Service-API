<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Provider extends BaseController
{
    use ResponseTrait;

    public function get_faculties()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getFaculties();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_departments()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $model = \model('App\Models\ProviderModel', true);
        
            $faculty_id = $this->request->getGet('faculty_id');
    
            $constraints = [];
    
            if ($faculty_id)
                $constraints['faculty_id'] = $faculty_id;
                
            $data = $model->getDepartments($constraints);
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_levels()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getLevels();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_courses()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $model = \model('App\Models\ProviderModel', true);
        
            $department_id = $this->request->getGet('department_id');
            $level_id = $this->request->getGet('level_id');
    
            $constraints = [];
    
            if ($department_id)
                $constraints['department_id'] = $department_id;
    
            if ($level_id)
                $constraints['level_id'] = $level_id;
                
            $data = $model->getCourses($constraints);
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_resource_categories()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getResourceCategories();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_news_categories()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getNewsCategories();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_moderator()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $constraints = [];

            if ($this->request->getGet('faculty_id') && is_numeric($this->request->getGet('faculty_id')))
                $constraints['moderators.faculty_id'] = $this->request->getGet('faculty_id');

            if ($this->request->getGet('department_id') && is_numeric($this->request->getGet('department_id')))
                $constraints['moderators.department_id'] = $this->request->getGet('department_id');

            if ($this->request->getGet('level_id') && is_numeric($this->request->getGet('level_id')))
                $constraints['moderators.level_id'] = $this->request->getGet('level_id');

            $data = $model->getModeratorData($constraints);
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Moderator not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_resources_count()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getResourcesCount();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_downloads_count()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getDownloadsCount();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_departments_count()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $data = $model->getDepartmentsCount();
            if ($data)
                return $this->respond($data, 200);
            else
                return $this->failNotFound('Data not found!');
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function get_stats()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();
            $model = \model('App\Models\ProviderModel', true);
        
            $result = [];

            $data = $model->getDepartmentsCount();
            if (!$data)
                return $this->failNotFound('Data not found!');

            $result['departments'] = $data['total'];

            $data = $model->getDownloadsCount();
            if (!$data)
                return $this->failNotFound('Data not found!');

            $result['downloads'] = $data['total'];

            $data = $model->getResourcesCount();
            if (!$data)
                return $this->failNotFound('Data not found!');

            $result['resources'] = $data['total'];

            return $this->respond($result);
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }

    public function downloadApp()
    {
        try {
            // Set the headers
            $this->setDefaultHeaders();

            $file = APP_PATH;
                
            if (file_exists($file)) {
                $res = $this->response->download($file, NULL, TRUE);

                // Set the headers
                $this->setDefaultDownloadHeaders($res);

                return $res;
            }
            else {
                return $this->failNotFound('The application is not available for download!');
            }
        } catch(\Throwable $th) {
            $this->logException($th);
            return $this->fail('An internal error occurred!', 500);
        }
    }
}