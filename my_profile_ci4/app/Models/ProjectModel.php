<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'description', 'tech_tags', 'link', 'category', 'sort_order'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProjectsByCategory($category = null)
    {
        $builder = $this->orderBy('sort_order', 'ASC');

        if ($category) {
            $builder->where('category', $category);
        }

        return $builder->findAll();
    }

    public function getTechTagsArray($project)
    {
        if (empty($project['tech_tags'])) {
            return [];
        }
        return array_map('trim', explode(',', $project['tech_tags']));
    }
}
