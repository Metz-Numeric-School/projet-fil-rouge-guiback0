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

/* registration/register.html.twig */
class __TwigTemplate_9ece15fbf2521afb86362bb3c89649a8 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "registration/register.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "registration/register.html.twig"));

        $this->parent = $this->loadTemplate("base.html.twig", "registration/register.html.twig", 1);
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

        yield "Inscription";
        
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
                    <h1 class=\"h3 mb-0 text-center\">Créer un compte</h1>
                </div>
                <div class=\"card-body\">
                    ";
        // line 14
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock((isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 14, $this->source); })()), 'errors');
        yield "

                    ";
        // line 16
        yield         $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderBlock((isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 16, $this->source); })()), 'form_start');
        yield "
                        <div class=\"mb-3\">
                            ";
        // line 18
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 18, $this->source); })()), "email", [], "any", false, false, false, 18), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Adresse e-mail"]);
        yield "
                            ";
        // line 19
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 19, $this->source); })()), "email", [], "any", false, false, false, 19), 'widget', ["attr" => ["class" => "form-control", "placeholder" => "votre@email.com"]]);
        yield "
                            ";
        // line 20
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 20, $this->source); })()), "email", [], "any", false, false, false, 20), 'errors');
        yield "
                        </div>

                        <div class=\"row\">
                            <div class=\"col-md-6\">
                                <div class=\"mb-3\">
                                    ";
        // line 26
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 26, $this->source); })()), "nom", [], "any", false, false, false, 26), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Nom"]);
        yield "
                                    ";
        // line 27
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 27, $this->source); })()), "nom", [], "any", false, false, false, 27), 'widget', ["attr" => ["class" => "form-control", "placeholder" => "Votre nom"]]);
        yield "
                                    ";
        // line 28
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 28, $this->source); })()), "nom", [], "any", false, false, false, 28), 'errors');
        yield "
                                </div>
                            </div>
                            <div class=\"col-md-6\">
                                <div class=\"mb-3\">
                                    ";
        // line 33
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 33, $this->source); })()), "prenom", [], "any", false, false, false, 33), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Prénom"]);
        yield "
                                    ";
        // line 34
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 34, $this->source); })()), "prenom", [], "any", false, false, false, 34), 'widget', ["attr" => ["class" => "form-control", "placeholder" => "Votre prénom"]]);
        yield "
                                    ";
        // line 35
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 35, $this->source); })()), "prenom", [], "any", false, false, false, 35), 'errors');
        yield "
                                </div>
                            </div>
                        </div>

                        <div class=\"mb-3\">
                            ";
        // line 41
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 41, $this->source); })()), "date_naissance", [], "any", false, false, false, 41), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Date de naissance"]);
        yield "
                            ";
        // line 42
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 42, $this->source); })()), "date_naissance", [], "any", false, false, false, 42), 'widget', ["attr" => ["class" => "form-control"]]);
        yield "
                            ";
        // line 43
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 43, $this->source); })()), "date_naissance", [], "any", false, false, false, 43), 'errors');
        yield "
                        </div>

                        <div class=\"mb-3\">
                            ";
        // line 47
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 47, $this->source); })()), "telephone", [], "any", false, false, false, 47), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Téléphone"]);
        yield "
                            ";
        // line 48
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 48, $this->source); })()), "telephone", [], "any", false, false, false, 48), 'widget', ["attr" => ["class" => "form-control", "placeholder" => "06 12 34 56 78"]]);
        yield "
                            ";
        // line 49
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 49, $this->source); })()), "telephone", [], "any", false, false, false, 49), 'errors');
        yield "
                        </div>

                        <div class=\"mb-3\">
                            ";
        // line 53
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 53, $this->source); })()), "adresse", [], "any", false, false, false, 53), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Adresse"]);
        yield "
                            ";
        // line 54
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 54, $this->source); })()), "adresse", [], "any", false, false, false, 54), 'widget', ["attr" => ["class" => "form-control", "placeholder" => "Votre adresse complète"]]);
        yield "
                            ";
        // line 55
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 55, $this->source); })()), "adresse", [], "any", false, false, false, 55), 'errors');
        yield "
                        </div>

                        <div class=\"mb-3\">
                            ";
        // line 59
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 59, $this->source); })()), "plainPassword", [], "any", false, false, false, 59), 'label', ["label_attr" => ["class" => "form-label"], "label" => "Mot de passe"]);
        yield "
                            ";
        // line 60
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 60, $this->source); })()), "plainPassword", [], "any", false, false, false, 60), 'widget', ["attr" => ["class" => "form-control", "placeholder" => "Votre mot de passe"]]);
        yield "
                            ";
        // line 61
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 61, $this->source); })()), "plainPassword", [], "any", false, false, false, 61), 'errors');
        yield "
                        </div>

                        <div class=\"mb-3\">
                            <div class=\"form-check\">
                                ";
        // line 66
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 66, $this->source); })()), "agreeTerms", [], "any", false, false, false, 66), 'widget', ["attr" => ["class" => "form-check-input"]]);
        yield "
                                ";
        // line 67
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 67, $this->source); })()), "agreeTerms", [], "any", false, false, false, 67), 'label', ["label_attr" => ["class" => "form-check-label"], "label" => "J'accepte les conditions d'utilisation"]);
        yield "
                            </div>
                            ";
        // line 69
        yield $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->searchAndRenderBlock(CoreExtension::getAttribute($this->env, $this->source, (isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 69, $this->source); })()), "agreeTerms", [], "any", false, false, false, 69), 'errors');
        yield "
                        </div>

                        <div class=\"d-grid\">
                            <button type=\"submit\" class=\"btn btn-success btn-lg\">S'inscrire</button>
                        </div>
                    ";
        // line 75
        yield         $this->env->getRuntime('Symfony\Component\Form\FormRenderer')->renderBlock((isset($context["registrationForm"]) || array_key_exists("registrationForm", $context) ? $context["registrationForm"] : (function () { throw new RuntimeError('Variable "registrationForm" does not exist.', 75, $this->source); })()), 'form_end');
        yield "

                    <hr class=\"my-4\">
                    
                    <div class=\"text-center\">
                        <p class=\"mb-0\">Déjà un compte ?</p>
                        <a href=\"";
        // line 81
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_login");
        yield "\" class=\"btn btn-outline-primary\">
                            Se connecter
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
        return "registration/register.html.twig";
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
        return array (  258 => 81,  249 => 75,  240 => 69,  235 => 67,  231 => 66,  223 => 61,  219 => 60,  215 => 59,  208 => 55,  204 => 54,  200 => 53,  193 => 49,  189 => 48,  185 => 47,  178 => 43,  174 => 42,  170 => 41,  161 => 35,  157 => 34,  153 => 33,  145 => 28,  141 => 27,  137 => 26,  128 => 20,  124 => 19,  120 => 18,  115 => 16,  110 => 14,  100 => 6,  87 => 5,  64 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Inscription{% endblock %}

{% block body %}
<div class=\"container mt-5\">
    <div class=\"row justify-content-center\">
        <div class=\"col-md-6\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h1 class=\"h3 mb-0 text-center\">Créer un compte</h1>
                </div>
                <div class=\"card-body\">
                    {{ form_errors(registrationForm) }}

                    {{ form_start(registrationForm) }}
                        <div class=\"mb-3\">
                            {{ form_label(registrationForm.email, 'Adresse e-mail', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.email, {'attr': {'class': 'form-control', 'placeholder': 'votre@email.com'}}) }}
                            {{ form_errors(registrationForm.email) }}
                        </div>

                        <div class=\"row\">
                            <div class=\"col-md-6\">
                                <div class=\"mb-3\">
                                    {{ form_label(registrationForm.nom, 'Nom', {'label_attr': {'class': 'form-label'}}) }}
                                    {{ form_widget(registrationForm.nom, {'attr': {'class': 'form-control', 'placeholder': 'Votre nom'}}) }}
                                    {{ form_errors(registrationForm.nom) }}
                                </div>
                            </div>
                            <div class=\"col-md-6\">
                                <div class=\"mb-3\">
                                    {{ form_label(registrationForm.prenom, 'Prénom', {'label_attr': {'class': 'form-label'}}) }}
                                    {{ form_widget(registrationForm.prenom, {'attr': {'class': 'form-control', 'placeholder': 'Votre prénom'}}) }}
                                    {{ form_errors(registrationForm.prenom) }}
                                </div>
                            </div>
                        </div>

                        <div class=\"mb-3\">
                            {{ form_label(registrationForm.date_naissance, 'Date de naissance', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.date_naissance, {'attr': {'class': 'form-control'}}) }}
                            {{ form_errors(registrationForm.date_naissance) }}
                        </div>

                        <div class=\"mb-3\">
                            {{ form_label(registrationForm.telephone, 'Téléphone', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.telephone, {'attr': {'class': 'form-control', 'placeholder': '06 12 34 56 78'}}) }}
                            {{ form_errors(registrationForm.telephone) }}
                        </div>

                        <div class=\"mb-3\">
                            {{ form_label(registrationForm.adresse, 'Adresse', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.adresse, {'attr': {'class': 'form-control', 'placeholder': 'Votre adresse complète'}}) }}
                            {{ form_errors(registrationForm.adresse) }}
                        </div>

                        <div class=\"mb-3\">
                            {{ form_label(registrationForm.plainPassword, 'Mot de passe', {'label_attr': {'class': 'form-label'}}) }}
                            {{ form_widget(registrationForm.plainPassword, {'attr': {'class': 'form-control', 'placeholder': 'Votre mot de passe'}}) }}
                            {{ form_errors(registrationForm.plainPassword) }}
                        </div>

                        <div class=\"mb-3\">
                            <div class=\"form-check\">
                                {{ form_widget(registrationForm.agreeTerms, {'attr': {'class': 'form-check-input'}}) }}
                                {{ form_label(registrationForm.agreeTerms, 'J\\'accepte les conditions d\\'utilisation', {'label_attr': {'class': 'form-check-label'}}) }}
                            </div>
                            {{ form_errors(registrationForm.agreeTerms) }}
                        </div>

                        <div class=\"d-grid\">
                            <button type=\"submit\" class=\"btn btn-success btn-lg\">S'inscrire</button>
                        </div>
                    {{ form_end(registrationForm) }}

                    <hr class=\"my-4\">
                    
                    <div class=\"text-center\">
                        <p class=\"mb-0\">Déjà un compte ?</p>
                        <a href=\"{{ path('app_login') }}\" class=\"btn btn-outline-primary\">
                            Se connecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
", "registration/register.html.twig", "/Users/guiback/Desktop/OpenClassrooms - MNS/MNS/DFS/MNS - Projet - ACCESS MNS/CODE/access_mns_manager/templates/registration/register.html.twig");
    }
}
