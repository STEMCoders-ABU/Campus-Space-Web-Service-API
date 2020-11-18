<?php namespace App\Models;

use CodeIgniter\Model;
 
class ModeratorsModel extends Model
{
    protected $table      = 'moderators';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['username', 'email', 'password', 'full_name', 'gender', 'phone', 'faculty_id', 
        'department_id', 'level_id'];

    protected $validationRules = [];

    protected $validationMessages = [];

    public function getModeratorData ($username, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->table('moderators')
                        ->where('moderators.username', $username)
                        ->get()->getRowArray();
        else
            return $this->table('moderators')
                        ->select('moderators.id, moderators.username, moderators.email, moderators.password, moderators.full_name, ' .
                            'moderators.gender, moderators.phone, moderators.faculty_id, moderators.department_id, moderators.level_id, ' .
                            'moderators.reg_date, faculties.faculty, departments.department, levels.level')
                        ->join('faculties', 'faculties.id = moderators.faculty_id')
                        ->join('departments', 'departments.id = moderators.department_id')
                        ->join('levels', 'levels.id = moderators.level_id')
                        ->where('moderators.username', $username)
                        ->get()->getRowArray();
    }

    public function getModeratorDataByEmail($email, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->table('moderators')
                        ->where('moderators.email', $email)
                        ->get()->getRowArray();
        else
            return $this->table('moderators')
                        ->select('moderators.id, moderators.username, moderators.email, moderators.password, moderators.full_name, ' .
                            'moderators.gender, moderators.phone, moderators.faculty_id, moderators.department_id, moderators.level_id, ' .
                            'moderators.reg_date, faculties.faculty, departments.department, levels.level')
                        ->join('faculties', 'faculties.id = moderators.faculty_id')
                        ->join('departments', 'departments.id = moderators.department_id')
                        ->join('levels', 'levels.id = moderators.level_id')
                        ->where('moderators.username', $username)
                        ->get()->getRowArray();
    }

    public function getCourses ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('courses')
                        ->where($constraints)
                        ->limit($size, $offset)
                        ->get()->getResultArray();
        else
            return $this->db->table('courses')
                        ->select('courses.id, courses.department_id, courses.level_id, courses.course_code, ' .
                            'courses.course_title, departments.department, levels.level')
                        ->join('departments', 'departments.id = courses.department_id')
                        ->join('levels', 'levels.id = courses.level_id')
                        ->where($constraints)
                        ->limit($size, $offset)
                        ->get()->getResultArray();
    }

    public function getResourceCategories()
    {
        return $this->db->table('resource_categories')
                    ->get()->getResultArray();
    }

    public function add_resource ($entries)
    {
        $this->db->table('resources')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function getNewsCategories()
    {
        return $this->db->table('news_categories')
                    ->get()->getResultArray();
    }

    public function add_news ($entries)
    {
        $this->db->table('news')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function getResources ($constraints)
    {
        return $this->db->table('resources')
                    ->where($constraints)
                    ->get()->getResultArray();
    }

    public function getNews ($constraints)
    {
        return $this->db->table('news')
                    ->where($constraints)
                    ->get()->getResultArray();
    }

    public function remove_resource ($constraints)
    {
        $this->db->table('resources')
                    ->where($constraints)
                    ->delete();

        return $this->db->affectedRows() != 0;
    }

    public function remove_news ($constraints)
    {
        $this->db->table('news')
                    ->where($constraints)
                    ->delete();

        return $this->db->affectedRows() != 0;
    }

    public function add_course ($entries)
    {
        $this->db->table('courses')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function setDefaultValidationRules()
    {
        $validationRules    = 
            [
                'email' => 'required|max_length[50]|valid_email',
                'password' => 'required|max_length[70]',
                'full_name' => 'required|min_length[2]|max_length[50]',
                'gender' => 'required|min_length[4]|max_length[6]',
                'phone' => 'required|min_length[11]|max_length[15]',
            ];
    }

    public function setValidationRulesForUpdate ($fields)
    {
        $validationRules = [];

        if (isset($fields['email']))
            $validationRules['email'] = 'required|max_length[50]|valid_email|is_unique[moderators.email,id,{id}]';
        
        if (isset($fields['password']))
            $validationRules['password'] = 'required|max_length[70]';
        
        if (isset($fields['full_name']))
            $validationRules['full_name'] = 'required|min_length[2]|max_length[50]';
        
        if (isset($fields['gender']))
            $validationRules['gender'] = 'required|min_length[4]|max_length[6]';
        
        if (isset($fields['phone']))
            $validationRules['phone'] = 'required|min_length[11]|max_length[15]';
    }

    public function getPasswordReset($verification_code) 
    {
        return $this->db->table('password_resets')
            ->where('verification_code', $verification_code)
            ->get()->getRowArray();
    }

    public function getPasswordResetByEmail($email) 
    {
        return $this->db->table('password_resets')
            ->where('email', $email)
            ->get()->getRowArray();
    }

    public function deletePasswordReset($id) 
    {
        return $this->db->table('password_resets')
            ->where('id', $id)
            ->delete();
    }

    public function addPasswordReset($entries)
    {
        if ($this->db->table('password_resets')->insert($entries))
            return $this->insertID();
        else
            return FALSE;
    }
}