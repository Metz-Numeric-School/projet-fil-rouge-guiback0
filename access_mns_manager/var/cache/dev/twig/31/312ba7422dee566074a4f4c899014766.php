<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* security/login.html.twig */
class __TwigTemplate_e454649009f6b818510b70016a01f444 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "security/login.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "security/login.html.twig"));

        $this->parent = $this->loadTemplate("base.html.twig", "security/login.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        yield "Connexion";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    // line 5
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_body(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        // line 6
        yield "<div class=\"container mt-5\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-6\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h1 class=\"h3 mb-0 text-center\">Connexion</h1>
                </div>
                <div class=\"card-body\">
                    <form method=\"post\">
                        ";
        // line 15
        if ((isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 15, $this->source); })())) {
            // line 16
            yield "                            <div class=\"alert alert-danger\">
                                ";
            // line 17
            if ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 17, $this->source); })()), "messageKey", [], "any", false, false, false, 17) == "Invalid credentials.")) {
                // line 18
                yield "                                    Identifiants invalides.
                                ";
            } else {
                // line 20
                yield "                                    ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\TranslationExtension']->trans(CoreExtension::getAttribute($this->env, $this->source, (isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 20, $this->source); })()), "messageKey", [], "any", false, false, false, 20), CoreExtension::getAttribute($this->env, $this->source, (isset($context["error"]) || array_key_exists("error", $context) ? $context["error"] : (function () { throw new RuntimeError('Variable "error" does not exist.', 20, $this->source); })()), "messageData", [], "any", false, false, false, 20), "security"), "html", null, true);
                yield "
                                ";
            }
            // line 22
            yield "                            </div>
                        ";
        }
        // line 24
        yield "
                        ";
        // line 25
        if (CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 25, $this->source); })()), "user", [], "any", false, false, false, 25)) {
            // line 26
            yield "                            <div class=\"alert alert-info\">
                                Vous êtes connecté en tant que ";
            // line 27
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 27, $this->source); })()), "user", [], "any", false, false, false, 27), "userIdentifier", [], "any", false, false, false, 27), "html", null, true);
            yield ", 
                                <a href=\"";
            // line 28
            yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_logout");
            yield "\">Se déconnecter</a>
                            </div>
                        ";
        }
        // line 31
        yield "
                        <div class=\"mb-3\">
                            <label for=\"username\" class=\"form-label\">Adresse e-mail</label>
                            <input type=\"email\" 
                                   value=\"";
        // line 35
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["last_username"]) || array_key_exists("last_username", $context) ? $context["last_username"] : (function () { throw new RuntimeError('Variable "last_username" does not exist.', 35, $this->source); })()), "html", null, true);
        yield "\" 
                                   name=\"_username\" 
                                   id=\"username\" 
                                   class=\"form-control\" 
                                   autocomplete=\"email\" 
                                   placeholder=\"votre@email.com\"
                                   required 
                                   autofocus>
                        </div>

                        <div class=\"mb-3\">
                            <label for=\"password\" class=\"form-label\">Mot de passe</label>
                            <input type=\"password\" 
                                   name=\"_password\" 
                                   id=\"password\" 
                                   class=\"form-control\" 
                                   autocomplete=\"current-password\" 
                                   placeholder=\"Votre mot de passe\"
                                   required>
                        </div>

                        <input type=\"hidden\" name=\"_csrf_token\" value=\"";
        // line 56
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderCsrfToken("authenticate"), "html", null, true);
        yield "\">

                        ";
        // line 62
        yield "                        <div class=\"form-check mb-3\">
                            <input type=\"checkbox\" name=\"_remember_me\" id=\"_remember_me\" class=\"form-check-input\">
                            <label for=\"_remember_me\" class=\"form-check-label\">Se souvenir de moi</label>
                        </div>

                        <div class=\"d-grid\">
                            <button class=\"btn btn-primary btn-lg\" type=\"submit\">
                                Se connecter
                            </button>
                        </div>
                    </form>

                    <hr class=\"my-4\">
                    
                    <div class=\"text-center\">
                        <p class=\"mb-0\">Pas encore de compte ?</p>
                        <a href=\"";
        // line 78
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_register");
        yield "\" class=\"btn btn-outline-secondary\">
                            Créer un compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "security/login.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  203 => 78,  185 => 62,  180 => 56,  156 => 35,  150 => 31,  144 => 28,  140 => 27,  137 => 26,  135 => 25,  132 => 24,  128 => 22,  122 => 20,  118 => 18,  116 => 17,  113 => 16,  111 => 15,  100 => 6,  87 => 5,  64 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Connexion{% endblock %}

{% block body %}
<div class=\"container mt-5\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-6\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h1 class=\"h3 mb-0 text-center\">Connexion</h1>
                </div>
                <div class=\"card-body\">
                    <form method=\"post\">
                        {% if error %}
                            <div class=\"alert alert-danger\">
                                {% if error.messageKey == 'Invalid credentials.' %}
                                    Identifiants invalides.
                                {% else %}
                                    {{ error.messageKey|trans(error.messageData, 'security') }}
                                {% endif %}
                            </div>
                        {% endif %}

                        {% if app.user %}
                            <div class=\"alert alert-info\">
                                Vous êtes connecté en tant que {{ app.user.userIdentifier }}, 
                                <a href=\"{{ path('app_logout') }}\">Se déconnecter</a>
                            </div>
                        {% endif %}

                        <div class=\"mb-3\">
                            <label for=\"username\" class=\"form-label\">Adresse e-mail</label>
                            <input type=\"email\" 
                                   value=\"{{ last_username }}\" 
                                   name=\"_username\" 
                                   id=\"username\" 
                                   class=\"form-control\" 
                                   autocomplete=\"email\" 
                                   placeholder=\"votre@email.com\"
                                   required 
                                   autofocus>
                        </div>

                        <div class=\"mb-3\">
                            <label for=\"password\" class=\"form-label\">Mot de passe</label>
                            <input type=\"password\" 
                                   name=\"_password\" 
                                   id=\"password\" 
                                   class=\"form-control\" 
                                   autocomplete=\"current-password\" 
                                   placeholder=\"Votre mot de passe\"
                                   required>
                        </div>

                        <input type=\"hidden\" name=\"_csrf_token\" value=\"{{ csrf_token('authenticate') }}\">

                        {#
                            Uncomment this section and add a remember_me option below your firewall to activate remember me functionality.
                            See https://symfony.com/doc/current/security/remember_me.html
                        #}
                        <div class=\"form-check mb-3\">
                            <input type=\"checkbox\" name=\"_remember_me\" id=\"_remember_me\" class=\"form-check-input\">
                            <label for=\"_remember_me\" class=\"form-check-label\">Se souvenir de moi</label>
                        </div>

                        <div class=\"d-grid\">
                            <button class=\"btn btn-primary btn-lg\" type=\"submit\">
                                Se connecter
                            </button>
                        </div>
                    </form>

                    <hr class=\"my-4\">
                    
                    <div class=\"text-center\">
                        <p class=\"mb-0\">Pas encore de compte ?</p>
                        <a href=\"{{ path('app_register') }}\" class=\"btn btn-outline-secondary\">
                            Créer un compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
", "security/login.html.twig", "/Users/guiback/Desktop/OpenClassrooms - MNS/MNS/DFS/MNS - Projet - ACCESS MNS/CODE/access_mns_manager/templates/security/login.html.twig");
    }
}
