<?php

namespace App\Controllers;

class AppController extends AbstractController
{
    public function index()
    {
        $data = $this->model->testData();


        $this->render('app/index.twig', ['data' => $data]);
    }
}