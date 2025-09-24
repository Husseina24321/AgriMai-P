<?php
namespace app\Controllers;

class FaqController extends AbstractController
{
    public function index(): void
    {
        $this->render("/front/faq.html.twig", [
            "title" => "FAQ Statistique"
        ]);
    }
}