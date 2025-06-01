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

/* dashboard/index.html.twig */
class __TwigTemplate_85d5a602212b597a4787217b0db68381 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "dashboard/index.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "dashboard/index.html.twig"));

        $this->parent = $this->loadTemplate("base.html.twig", "dashboard/index.html.twig", 1);
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

        yield "Tableau de bord";
        
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
        yield "<div class=\"container mt-4\">
    <div class=\"row\">
        <div class=\"col-12\">
            <h1 class=\"mb-4\">Tableau de bord</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class=\"row mb-4\">
        <!-- Users Widget -->
        <div class=\"col-md-6 col-lg-3 mb-3\">
            <div class=\"card bg-primary text-white h-100\">
                <div class=\"card-body\">
                    <div class=\"d-flex justify-content-between align-items-center\">
                        <div>
                            <h6 class=\"card-title text-uppercase mb-1\">Utilisateurs</h6>
                            <h2 class=\"mb-0\">";
        // line 22
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["total_users"]) || array_key_exists("total_users", $context) ? $context["total_users"] : (function () { throw new RuntimeError('Variable "total_users" does not exist.', 22, $this->source); })()), "html", null, true);
        yield "</h2>
                            <small class=\"text-white-50\">Total des utilisateurs</small>
                        </div>
                        <div class=\"text-white-50\">
                            <svg width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\">
                                <path d=\"M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z\"/>
                                <path fill-rule=\"evenodd\" d=\"M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z\"/>
                                <path d=\"M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z\"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class=\"card-footer bg-primary border-0\">
                    <a href=\"";
        // line 35
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_user_index");
        yield "\" class=\"text-white text-decoration-none\">
                        <small>Voir tous les utilisateurs <i class=\"fas fa-arrow-right\"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <!-- Organisations Widget -->
        <div class=\"col-md-6 col-lg-3 mb-3\">
            <div class=\"card bg-success text-white h-100\">
                <div class=\"card-body\">
                    <div class=\"d-flex justify-content-between align-items-center\">
                        <div>
                            <h6 class=\"card-title text-uppercase mb-1\">Organisations</h6>
                            <h2 class=\"mb-0\">";
        // line 49
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["total_organisations"]) || array_key_exists("total_organisations", $context) ? $context["total_organisations"] : (function () { throw new RuntimeError('Variable "total_organisations" does not exist.', 49, $this->source); })()), "html", null, true);
        yield "</h2>
                            <small class=\"text-white-50\">Total des organisations</small>
                        </div>
                        <div class=\"text-white-50\">
                            <svg width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\">
                                <path d=\"M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1ZM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1ZM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1ZM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Z\"/>
                                <path d=\"M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V1Zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3V1Z\"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class=\"card-footer bg-success border-0\">
                    <a href=\"";
        // line 61
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_index");
        yield "\" class=\"text-white text-decoration-none\">
                        <small>Voir toutes les organisations <i class=\"fas fa-arrow-right\"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions Widget -->
        <div class=\"col-md-6 col-lg-3 mb-3\">
            <div class=\"card bg-info text-white h-100\">
                <div class=\"card-body\">
                    <div class=\"d-flex justify-content-between align-items-center\">
                        <div>
                            <h6 class=\"card-title text-uppercase mb-1\">Actions rapides</h6>
                            <p class=\"mb-0\">Créer rapidement</p>
                        </div>
                        <div class=\"text-white-50\">
                            <svg width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\">
                                <path d=\"M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z\"/>
                                <path d=\"M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z\"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class=\"card-footer bg-info border-0\">
                    <div class=\"d-flex justify-content-between\">
                        <a href=\"";
        // line 87
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_user_new");
        yield "\" class=\"text-white text-decoration-none\">
                            <small>+ Utilisateur</small>
                        </a>
                        <a href=\"";
        // line 90
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_new");
        yield "\" class=\"text-white text-decoration-none\">
                            <small>+ Organisation</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Organisations Table -->
    ";
        // line 100
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["recent_organisations"]) || array_key_exists("recent_organisations", $context) ? $context["recent_organisations"] : (function () { throw new RuntimeError('Variable "recent_organisations" does not exist.', 100, $this->source); })())) > 0)) {
            // line 101
            yield "    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"card-title mb-0\">Organisations récentes</h5>
                </div>
                <div class=\"card-body\">
                    <div class=\"table-responsive\">
                        <table class=\"table table-hover\">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ";
            // line 118
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable((isset($context["recent_organisations"]) || array_key_exists("recent_organisations", $context) ? $context["recent_organisations"] : (function () { throw new RuntimeError('Variable "recent_organisations" does not exist.', 118, $this->source); })()));
            foreach ($context['_seq'] as $context["_key"] => $context["organisation"]) {
                // line 119
                yield "                                <tr>
                                    <td>";
                // line 120
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["organisation"], "nomOrganisation", [], "any", false, false, false, 120), "html", null, true);
                yield "</td>
                                    <td>";
                // line 121
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate(CoreExtension::getAttribute($this->env, $this->source, $context["organisation"], "dateCreation", [], "any", false, false, false, 121), "d/m/Y"), "html", null, true);
                yield "</td>
                                    <td>
                                        <a href=\"";
                // line 123
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_show", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["organisation"], "id", [], "any", false, false, false, 123)]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-outline-primary\">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['organisation'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 129
            yield "                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";
        }
        // line 137
        yield "</div>
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
        return "dashboard/index.html.twig";
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
        return array (  273 => 137,  263 => 129,  251 => 123,  246 => 121,  242 => 120,  239 => 119,  235 => 118,  216 => 101,  214 => 100,  201 => 90,  195 => 87,  166 => 61,  151 => 49,  134 => 35,  118 => 22,  100 => 6,  87 => 5,  64 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Tableau de bord{% endblock %}

{% block body %}
<div class=\"container mt-4\">
    <div class=\"row\">
        <div class=\"col-12\">
            <h1 class=\"mb-4\">Tableau de bord</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class=\"row mb-4\">
        <!-- Users Widget -->
        <div class=\"col-md-6 col-lg-3 mb-3\">
            <div class=\"card bg-primary text-white h-100\">
                <div class=\"card-body\">
                    <div class=\"d-flex justify-content-between align-items-center\">
                        <div>
                            <h6 class=\"card-title text-uppercase mb-1\">Utilisateurs</h6>
                            <h2 class=\"mb-0\">{{ total_users }}</h2>
                            <small class=\"text-white-50\">Total des utilisateurs</small>
                        </div>
                        <div class=\"text-white-50\">
                            <svg width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\">
                                <path d=\"M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z\"/>
                                <path fill-rule=\"evenodd\" d=\"M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z\"/>
                                <path d=\"M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z\"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class=\"card-footer bg-primary border-0\">
                    <a href=\"{{ path('app_user_index') }}\" class=\"text-white text-decoration-none\">
                        <small>Voir tous les utilisateurs <i class=\"fas fa-arrow-right\"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <!-- Organisations Widget -->
        <div class=\"col-md-6 col-lg-3 mb-3\">
            <div class=\"card bg-success text-white h-100\">
                <div class=\"card-body\">
                    <div class=\"d-flex justify-content-between align-items-center\">
                        <div>
                            <h6 class=\"card-title text-uppercase mb-1\">Organisations</h6>
                            <h2 class=\"mb-0\">{{ total_organisations }}</h2>
                            <small class=\"text-white-50\">Total des organisations</small>
                        </div>
                        <div class=\"text-white-50\">
                            <svg width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\">
                                <path d=\"M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1ZM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1ZM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1ZM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1Zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1Z\"/>
                                <path d=\"M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V1Zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3V1Z\"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class=\"card-footer bg-success border-0\">
                    <a href=\"{{ path('app_organisation_index') }}\" class=\"text-white text-decoration-none\">
                        <small>Voir toutes les organisations <i class=\"fas fa-arrow-right\"></i></small>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions Widget -->
        <div class=\"col-md-6 col-lg-3 mb-3\">
            <div class=\"card bg-info text-white h-100\">
                <div class=\"card-body\">
                    <div class=\"d-flex justify-content-between align-items-center\">
                        <div>
                            <h6 class=\"card-title text-uppercase mb-1\">Actions rapides</h6>
                            <p class=\"mb-0\">Créer rapidement</p>
                        </div>
                        <div class=\"text-white-50\">
                            <svg width=\"48\" height=\"48\" fill=\"currentColor\" viewBox=\"0 0 16 16\">
                                <path d=\"M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z\"/>
                                <path d=\"M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z\"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class=\"card-footer bg-info border-0\">
                    <div class=\"d-flex justify-content-between\">
                        <a href=\"{{ path('app_user_new') }}\" class=\"text-white text-decoration-none\">
                            <small>+ Utilisateur</small>
                        </a>
                        <a href=\"{{ path('app_organisation_new') }}\" class=\"text-white text-decoration-none\">
                            <small>+ Organisation</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Organisations Table -->
    {% if recent_organisations|length > 0 %}
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"card-title mb-0\">Organisations récentes</h5>
                </div>
                <div class=\"card-body\">
                    <div class=\"table-responsive\">
                        <table class=\"table table-hover\">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {% for organisation in recent_organisations %}
                                <tr>
                                    <td>{{ organisation.nomOrganisation }}</td>
                                    <td>{{ organisation.dateCreation|date('d/m/Y') }}</td>
                                    <td>
                                        <a href=\"{{ path('app_organisation_show', {'id': organisation.id}) }}\" class=\"btn btn-sm btn-outline-primary\">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {% endif %}
</div>
{% endblock %}
", "dashboard/index.html.twig", "/Users/guiback/Desktop/OpenClassrooms - MNS/MNS/DFS/MNS - Projet - ACCESS MNS/CODE/access_mns_manager/templates/dashboard/index.html.twig");
    }
}
