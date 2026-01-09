<?php

namespace App\Controllers;

use App\Models\ProfileModel;
use App\Models\SkillModel;
use App\Models\ExperienceModel;
use App\Models\EducationModel;
use App\Models\ProjectModel;

class Home extends BaseController
{
    public function index(): string
    {
        $profileModel    = new ProfileModel();
        $skillModel      = new SkillModel();
        $experienceModel = new ExperienceModel();
        $educationModel  = new EducationModel();
        $projectModel    = new ProjectModel();

        $data = [
            'profile'     => $profileModel->getProfile(),
            'skills'      => $skillModel->getSkillsByCategory(),
            'experiences' => $experienceModel->getAllExperiences(),
            'education'   => $educationModel->getAllEducation(),
            'title'       => '首頁',
        ];

        return view('home/index', $data);
    }
}
