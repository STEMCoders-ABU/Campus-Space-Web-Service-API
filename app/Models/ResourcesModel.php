<?php namespace App\Models;

use CodeIgniter\Model;

class ResourcesModel extends Model
{
    public function getResources ($constraints)
    {
        return $this->db->table('resources')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function getCategoryComments ($constraints)
    {
        return $this->db->table('category_comments')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function getResourceComments ($constraints)
    {
        return $this->db->table('comments')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function add_comment ($entries)
    {
        $this->db->table('comments')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function add_category_comment ($entries)
    {
        $this->db->table('category_comments')->insert($entries);
        return $this->db->affectedRows() != 0;
    }
}