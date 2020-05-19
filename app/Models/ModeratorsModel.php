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

    public function getResources ($conditionals)
    {
        return $this->db->table('resources')
                    ->where($conditionals)
                    ->get()->getResultArray();
    }

    public function getNews ($conditionals)
    {
        return $this->db->table('news')
                    ->where($conditionals)
                    ->get()->getResultArray();
    }

    public function remove_resource ($conditionals)
    {
        $this->db->table('resources')
                    ->where($conditionals)
                    ->delete();

        return $this->db->affectedRows() != 0;
    }

    public function remove_news ($conditionals)
    {
        $this->db->table('news')
                    ->where($conditionals)
                    ->delete();

        return $this->db->affectedRows() != 0;
    }

    public function get_resource ($conditionals)
    {
        return $this->db->table('resources')
                    ->where($conditionals)
                    ->get()->getResultArray();
    }

    public function get_news_item ($conditionals)
    {
        return $this->db->table('news')
                    ->where($conditionals)
                    ->get()->getResultArray();
    }

    public function update_resource ($entries, $conditionals)
    {
        $this->db->table('resources')
                    ->where($conditionals)
                    ->update($entries);

        return $this->db->affectedRows() != 0;
    }

    public function get_resource_title ($id)
    {
        $query = $this->db->table('resources')
                    ->select('title')
                    ->where('id', $id)
                    ->get()->getRowArray();
                    
        if ($query)
            return $query['title'];
        else
            return '';
    }

    public function update_news ($entries, $conditionals)
    {
        $this->db->table('news')
                    ->where($conditionals)
                    ->update($entries);

        return $this->db->affectedRows() != 0;
    }

    public function get_news_title ($id)
    {
        $query = $this->db->table('news')
                    ->select('title')
                    ->where('id', $id)
                    ->get()->getRowArray();
                    
        if ($query)
            return $query['title'];
        else
            return '';
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
}