<?php
namespace app\Controllers;

class AboutController extends AbstractController
{
    public function index(): void
    {
        $this->render("/front/about.html.twig", [
            "title" => "About Page",
        ]);
    }
}