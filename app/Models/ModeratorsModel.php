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

    public function getModeratorData ($username)
    {
        return $this->where('username', $username)->first();
    }

    public function getCourses ($conditionals)
    {
        return $this->db->table('courses')
                    ->where($conditionals)
                    ->get()->getResultArray();
    }

    public function getResourceCategories()
    {
        return $this->db->table('resource_categories')
                    ->get()->getResultArray();
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
}