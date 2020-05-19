<?php namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    public function getNews ($constraints)
    {
        return $this->db->table('news')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function getCategoryComments ($constraints)
    {
        return $this->db->table('news_category_comments')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function getNewsComments ($constraints)
    {
        return $this->db->table('news_comments')
                        ->where($constraints)
                        ->get()->getResultArray();
    }

    public function add_comment ($entries)
    {
        $this->db->table('news_comments')->insert($entries);
        return $this->db->affectedRows() != 0;
    }

    public function add_category_comment ($entries)
    {
        $this->db->table('news_category_comments')->insert($entries);
        return $this->db->affectedRows() != 0;
    }
}