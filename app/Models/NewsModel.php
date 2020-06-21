<?php namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    public function getNews ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('news')
                        ->where($constraints)
                        ->orderBy('news.date_added DESC')
                        ->limit($size, $offset)
                        ->get()->getResultArray();
        else
            return $this->db->table('news')
                        ->select('news.id, news.title, news.content, news.category_id, news.faculty_id, news.department_id, news.level_id, news.date_added, ' .
                                'faculties.faculty, departments.department, levels.level, news_categories.category')
                        ->join('faculties', 'faculties.id = news.faculty_id')
                        ->join('departments', 'departments.id = news.department_id')
                        ->join('levels', 'levels.id = news.level_id')
                        ->join('news_categories', 'news_categories.id = news.category_id')
                        ->where($constraints)
                        ->orderBy('news.date_added DESC')
                        ->limit($size, $offset)
                        ->get()->getResultArray();
    }

    public function getSearchedNews ($constraints, $search, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('news')
                            ->where($constraints)
                            ->like('news.title', $search)
                            ->orLike('news.content', $search)
                            ->orderBy('news.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('news')
                            ->select('news.id, news.title, news.content, news.category_id, news.faculty_id, news.department_id, news.level_id, news.date_added, ' .
                                    'faculties.faculty, departments.department, levels.level, news_categories.category')
                            ->join('faculties', 'faculties.id = news.faculty_id')
                            ->join('departments', 'departments.id = news.department_id')
                            ->join('levels', 'levels.id = news.level_id')
                            ->join('news_categories', 'news_categories.id = news.category_id')
                            ->where($constraints)
                            ->like('news.title', $search)
                            ->orLike('news.content', $search)
                            ->orderBy('news.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        }
    }

    public function getCategoryComments ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('news_category_comments')
                            ->where($constraints)
                            ->orderBy('news_category_comments.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
        {
            return $this->db->table('news_category_comments')
                        ->select('news_category_comments.id, news_category_comments.category_id, news_category_comments.department_id, ' .
                                'news_category_comments.level_id, news_category_comments.author, news_category_comments.comment, news_category_comments.date_added, ' .
                                'departments.department, levels.level, news_categories.category')
                        ->join('departments', 'departments.id = news_category_comments.department_id')
                        ->join('levels', 'levels.id = news_category_comments.level_id')
                        ->join('news_categories', 'news_categories.id = news_category_comments.category_id')
                        ->where($constraints)
                        ->orderBy('news_category_comments.date_added DESC')
                        ->limit($size, $offset)
                        ->get()->getResultArray();
        }
    }

    public function getNewsComments ($constraints, $size, $offset, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('news_comments')
                            ->where($constraints)
                            ->orderBy('news_comments.date_added DESC')
                            ->limit($size, $offset)
                            ->get()->getResultArray();
        else
            return $this->db->table('news_comments')
                            ->select('news_comments.id, news_comments.comment, news_comments.author, news_comments.news_id, news_comments.date_added, ' .
                                    'news.title, news.content, news.category_id, news.faculty_id, news.department_id, news.level_id, ' .
                                    'faculties.faculty, departments.department, levels.level, news_categories.category')
                            ->join('news', 'news_comments.news_id = news.id')
                            ->join('faculties', 'faculties.id = news.faculty_id')
                            ->join('departments', 'departments.id = news.department_id')
                            ->join('levels', 'levels.id = news.level_id')
                            ->join('news_categories', 'news_categories.id = news.category_id')
                            ->where($constraints)
                            ->orderBy('news_comments.date_added DESC')
                            ->limit($size, $offset)
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

    public function get_news_item ($constraints, $join = FALSE)
    {
        if ($join == FALSE)
            return $this->db->table('news')
                        ->where($constraints)
                        ->get()->getRowArray();
        else
            return $this->db->table('news')
                    ->select('news.id, news.title, news.content, news.category_id, news.faculty_id, news.department_id, news.level_id, news.date_added, ' .
                            'faculties.faculty, departments.department, levels.level, news_categories.category')
                    ->join('faculties', 'faculties.id = news.faculty_id')
                    ->join('departments', 'departments.id = news.department_id')
                    ->join('levels', 'levels.id = news.level_id')
                    ->join('news_categories', 'news_categories.id = news.category_id')
                    ->where($constraints)
                    ->get()->getRowArray();
    }

    public function update_news ($entries, $constraints)
    {
        return $this->db->table('news')
                    ->where($constraints)
                    ->update($entries);
    }
}