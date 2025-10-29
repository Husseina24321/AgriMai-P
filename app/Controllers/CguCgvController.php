<?php
namespace app\Controllers;

class CguCgvController extends AbstractController
{
    public function index(): void
    {
        $this->render("/front/cguCgv.html.twig", [
            "title" => "cgu et cgv page",
        ]);
    }
}