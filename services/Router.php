<?php

use Couchbase\User;

class Router
{
    public function handleRequest(array $get) : void
    {
        $home= new HomeController();
        $auth = new AuthController();
        $cag = new CategoryController();
        $cont = new ContactController();
        $dash = new DashboardController();
        $order = new OrderController();
        $oi = new OrderItemController();
        $pd = new ProductController();
        $user = new UserController();

        if(!isset($get["route"]))
        {
            $home->home();
        }
        if (isset($get['route']) && $get['route'] === 'about') {
            $about->about();

        } elseif (isset($get['route']) && $get['route'] === 'contact') {
            $cont->ContactController();
        } elseif (isset($get['route']) && $get['route'] === 'faq') {
            $cont->faq();

        } elseif (isset($get['route']) && $get['route'] === 'faq') {
            if (isset($get['categorie']) && $get['categorie'] === 'dev-back') {
                $controller->DevBack();
            } elseif (isset($get['categorie']) && $get['categorie'] === 'dev-front') {
                $controller->DevFront();
            } else {
                $controller->notFound();
            }

        } elseif (isset($get['route']) && $get['route'] === 'article') {
            if (isset($get['article']) && $get['article'] === 'i-love-php') {
                $controller->ilovephp();
            } elseif (isset($get['article']) && $get['article'] === 'i-love-js') {
                $controller->ilovejs();
            } else {
                $controller->notFound();
            }

        } elseif (!isset($get['route'])) {
            $controller->home();
        } else {
            $controller->notFound();
        }
    }
}
