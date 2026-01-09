<?php

namespace App\Controllers;

use App\Models\ProjectModel;

class Portfolio extends BaseController
{
    public function index(): string
    {
        $projectModel = new ProjectModel();

        $data = [
            'projects'   => $projectModel->getProjectsByCategory('project'),
            'opensource' => $projectModel->getProjectsByCategory('opensource'),
            'title'      => '作品集',
        ];

        return view('portfolio/index', $data);
    }
}
