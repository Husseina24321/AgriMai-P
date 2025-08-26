<?php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use JetBrains\PhpStorm\NoReturn;

abstract class AbstractController
{
    private Environment $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader('templates');
        $this->twig = new Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new DebugExtension());
    }

    //Rend un template Twig. Les attributs sont données par PhpStorm
    protected function render(string $name, array $context = []): void
    {
        // Twig  lance des exceptions (LoaderError, RuntimeError, SyntaxError) pour éviter "Unhandled exceptions"
        try {
            echo $this->twig->render("app/views/front/$name.html.twig", $context);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            die("Erreur de rendu Twig : " . $e->getMessage());
        }
    }

    //Redirige vers une autre page.

    #[NoReturn]
    protected function redirect(string $route): void
    {
        header("Location: $route");
        exit();
    }
}
