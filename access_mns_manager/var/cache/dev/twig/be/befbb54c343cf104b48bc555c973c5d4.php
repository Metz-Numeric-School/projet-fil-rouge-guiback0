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

/* organisation/show.html.twig */
class __TwigTemplate_4646abbe85fb23222b3352ba260c0016 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "organisation/show.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "organisation/show.html.twig"));

        $this->parent = $this->loadTemplate("base.html.twig", "organisation/show.html.twig", 1);
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

        yield "Organisation - ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 3, $this->source); })()), "nomOrganisation", [], "any", false, false, false, 3), "html", null, true);
        
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
            <div class=\"d-flex justify-content-between align-items-center mb-4\">
                <h1>";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 10, $this->source); })()), "nomOrganisation", [], "any", false, false, false, 10), "html", null, true);
        yield "</h1>
                <div>
                    <a href=\"";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_edit", ["id" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 12, $this->source); })()), "id", [], "any", false, false, false, 12)]), "html", null, true);
        yield "\" class=\"btn btn-warning\">
                        Modifier
                    </a>
                    <a href=\"";
        // line 15
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_index");
        yield "\" class=\"btn btn-secondary\">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Organisation Details -->
    <div class=\"row mb-4\">
        <div class=\"col-md-8\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"card-title mb-0\">Informations de l'organisation</h5>
                </div>
                <div class=\"card-body\">
                    <div class=\"row\">
                        <div class=\"col-md-6\">
                            <p><strong>Email:</strong> ";
        // line 33
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 33, $this->source); })()), "email", [], "any", false, false, false, 33), "html", null, true);
        yield "</p>
                            <p><strong>Téléphone:</strong> ";
        // line 34
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "telephone", [], "any", true, true, false, 34) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 34, $this->source); })()), "telephone", [], "any", false, false, false, 34)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 34, $this->source); })()), "telephone", [], "any", false, false, false, 34), "html", null, true)) : ("Non renseigné"));
        yield "</p>
                            <p><strong>Site web:</strong> 
                                ";
        // line 36
        if (CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 36, $this->source); })()), "siteWeb", [], "any", false, false, false, 36)) {
            // line 37
            yield "                                    <a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 37, $this->source); })()), "siteWeb", [], "any", false, false, false, 37), "html", null, true);
            yield "\" target=\"_blank\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 37, $this->source); })()), "siteWeb", [], "any", false, false, false, 37), "html", null, true);
            yield "</a>
                                ";
        } else {
            // line 39
            yield "                                    Non renseigné
                                ";
        }
        // line 41
        yield "                            </p>
                            <p><strong>SIRET:</strong> ";
        // line 42
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "siret", [], "any", true, true, false, 42) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 42, $this->source); })()), "siret", [], "any", false, false, false, 42)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 42, $this->source); })()), "siret", [], "any", false, false, false, 42), "html", null, true)) : ("Non renseigné"));
        yield "</p>
                        </div>
                        <div class=\"col-md-6\">
                            <p><strong>Date de création:</strong> ";
        // line 45
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 45, $this->source); })()), "dateCreation", [], "any", false, false, false, 45), "d/m/Y"), "html", null, true);
        yield "</p>
                            <p><strong>CA:</strong> ";
        // line 46
        yield ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 46, $this->source); })()), "ca", [], "any", false, false, false, 46)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 46, $this->source); })()), "ca", [], "any", false, false, false, 46) . " €"), "html", null, true)) : ("Non renseigné"));
        yield "</p>
                            <p><strong>Adresse:</strong><br>
                                ";
        // line 48
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "numeroRue", [], "any", true, true, false, 48) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 48, $this->source); })()), "numeroRue", [], "any", false, false, false, 48)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 48, $this->source); })()), "numeroRue", [], "any", false, false, false, 48), "html", null, true)) : (""));
        yield " ";
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "suffixRue", [], "any", true, true, false, 48) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 48, $this->source); })()), "suffixRue", [], "any", false, false, false, 48)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 48, $this->source); })()), "suffixRue", [], "any", false, false, false, 48), "html", null, true)) : (""));
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 48, $this->source); })()), "nomRue", [], "any", false, false, false, 48), "html", null, true);
        yield "<br>
                                ";
        // line 49
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "codePostal", [], "any", true, true, false, 49) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 49, $this->source); })()), "codePostal", [], "any", false, false, false, 49)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 49, $this->source); })()), "codePostal", [], "any", false, false, false, 49), "html", null, true)) : (""));
        yield " ";
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "ville", [], "any", true, true, false, 49) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 49, $this->source); })()), "ville", [], "any", false, false, false, 49)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 49, $this->source); })()), "ville", [], "any", false, false, false, 49), "html", null, true)) : (""));
        yield "<br>
                                ";
        // line 50
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["organisation"] ?? null), "pays", [], "any", true, true, false, 50) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 50, $this->source); })()), "pays", [], "any", false, false, false, 50)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 50, $this->source); })()), "pays", [], "any", false, false, false, 50), "html", null, true)) : (""));
        yield "
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=\"col-md-4\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"card-title mb-0\">Statistiques</h5>
                </div>
                <div class=\"card-body\">
                    <p><strong>Nombre de services:</strong> ";
        // line 63
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 63, $this->source); })()), "services", [], "any", false, false, false, 63)), "html", null, true);
        yield "</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header d-flex justify-content-between align-items-center\">
                    <h5 class=\"card-title mb-0\">Services</h5>
                    <a href=\"";
        // line 75
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_service_new", ["id" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 75, $this->source); })()), "id", [], "any", false, false, false, 75)]), "html", null, true);
        yield "\" class=\"btn btn-success\">
                        <i class=\"fas fa-plus\"></i> Ajouter un service
                    </a>
                </div>
                <div class=\"card-body\">
                    ";
        // line 80
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 80, $this->source); })()), "services", [], "any", false, false, false, 80)) > 0)) {
            // line 81
            yield "                        <div class=\"table-responsive\">
                            <table class=\"table table-hover\">
                                <thead>
                                    <tr>
                                        <th>Nom du service</th>
                                        <th>Niveau</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ";
            // line 91
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 91, $this->source); })()), "services", [], "any", false, false, false, 91));
            foreach ($context['_seq'] as $context["_key"] => $context["service"]) {
                // line 92
                yield "                                    <tr>
                                        <td>";
                // line 93
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["service"], "nomService", [], "any", false, false, false, 93), "html", null, true);
                yield "</td>
                                        <td>
                                            <span class=\"badge bg-primary\">Niveau ";
                // line 95
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["service"], "niveauService", [], "any", false, false, false, 95), "html", null, true);
                yield "</span>
                                        </td>
                                        <td>
                                            <a href=\"";
                // line 98
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_service_show", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["service"], "id", [], "any", false, false, false, 98)]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-outline-primary\">
                                                Voir
                                            </a>
                                            <a href=\"";
                // line 101
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_service_edit", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["service"], "id", [], "any", false, false, false, 101)]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-outline-warning\">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['service'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 107
            yield "                                </tbody>
                            </table>
                        </div>
                    ";
        } else {
            // line 111
            yield "                        <div class=\"text-center py-4\">
                            <p class=\"text-muted\">Aucun service pour cette organisation.</p>
                            <a href=\"";
            // line 113
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_service_new", ["id" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 113, $this->source); })()), "id", [], "any", false, false, false, 113)]), "html", null, true);
            yield "\" class=\"btn btn-success\">
                                Créer le premier service
                            </a>
                        </div>
                    ";
        }
        // line 118
        yield "                </div>
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
        return "organisation/show.html.twig";
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
        return array (  301 => 118,  293 => 113,  289 => 111,  283 => 107,  271 => 101,  265 => 98,  259 => 95,  254 => 93,  251 => 92,  247 => 91,  235 => 81,  233 => 80,  225 => 75,  210 => 63,  194 => 50,  188 => 49,  180 => 48,  175 => 46,  171 => 45,  165 => 42,  162 => 41,  158 => 39,  150 => 37,  148 => 36,  143 => 34,  139 => 33,  118 => 15,  112 => 12,  107 => 10,  101 => 6,  88 => 5,  64 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Organisation - {{ organisation.nomOrganisation }}{% endblock %}

{% block body %}
<div class=\"container mt-4\">
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"d-flex justify-content-between align-items-center mb-4\">
                <h1>{{ organisation.nomOrganisation }}</h1>
                <div>
                    <a href=\"{{ path('app_organisation_edit', {'id': organisation.id}) }}\" class=\"btn btn-warning\">
                        Modifier
                    </a>
                    <a href=\"{{ path('app_organisation_index') }}\" class=\"btn btn-secondary\">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Organisation Details -->
    <div class=\"row mb-4\">
        <div class=\"col-md-8\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"card-title mb-0\">Informations de l'organisation</h5>
                </div>
                <div class=\"card-body\">
                    <div class=\"row\">
                        <div class=\"col-md-6\">
                            <p><strong>Email:</strong> {{ organisation.email }}</p>
                            <p><strong>Téléphone:</strong> {{ organisation.telephone ?? 'Non renseigné' }}</p>
                            <p><strong>Site web:</strong> 
                                {% if organisation.siteWeb %}
                                    <a href=\"{{ organisation.siteWeb }}\" target=\"_blank\">{{ organisation.siteWeb }}</a>
                                {% else %}
                                    Non renseigné
                                {% endif %}
                            </p>
                            <p><strong>SIRET:</strong> {{ organisation.siret ?? 'Non renseigné' }}</p>
                        </div>
                        <div class=\"col-md-6\">
                            <p><strong>Date de création:</strong> {{ organisation.dateCreation|date('d/m/Y') }}</p>
                            <p><strong>CA:</strong> {{ organisation.ca ? organisation.ca ~ ' €' : 'Non renseigné' }}</p>
                            <p><strong>Adresse:</strong><br>
                                {{ organisation.numeroRue ?? '' }} {{ organisation.suffixRue ?? '' }} {{ organisation.nomRue }}<br>
                                {{ organisation.codePostal ?? '' }} {{ organisation.ville ?? '' }}<br>
                                {{ organisation.pays ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=\"col-md-4\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"card-title mb-0\">Statistiques</h5>
                </div>
                <div class=\"card-body\">
                    <p><strong>Nombre de services:</strong> {{ organisation.services|length }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header d-flex justify-content-between align-items-center\">
                    <h5 class=\"card-title mb-0\">Services</h5>
                    <a href=\"{{ path('app_organisation_service_new', {'id': organisation.id}) }}\" class=\"btn btn-success\">
                        <i class=\"fas fa-plus\"></i> Ajouter un service
                    </a>
                </div>
                <div class=\"card-body\">
                    {% if organisation.services|length > 0 %}
                        <div class=\"table-responsive\">
                            <table class=\"table table-hover\">
                                <thead>
                                    <tr>
                                        <th>Nom du service</th>
                                        <th>Niveau</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {% for service in organisation.services %}
                                    <tr>
                                        <td>{{ service.nomService }}</td>
                                        <td>
                                            <span class=\"badge bg-primary\">Niveau {{ service.niveauService }}</span>
                                        </td>
                                        <td>
                                            <a href=\"{{ path('app_service_show', {'id': service.id}) }}\" class=\"btn btn-sm btn-outline-primary\">
                                                Voir
                                            </a>
                                            <a href=\"{{ path('app_service_edit', {'id': service.id}) }}\" class=\"btn btn-sm btn-outline-warning\">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                    {% endfor %}
                                </tbody>
                            </table>
                        </div>
                    {% else %}
                        <div class=\"text-center py-4\">
                            <p class=\"text-muted\">Aucun service pour cette organisation.</p>
                            <a href=\"{{ path('app_organisation_service_new', {'id': organisation.id}) }}\" class=\"btn btn-success\">
                                Créer le premier service
                            </a>
                        </div>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
", "organisation/show.html.twig", "/Users/guiback/Desktop/OpenClassrooms - MNS/MNS/DFS/MNS - Projet - ACCESS MNS/CODE/access_mns_manager/templates/organisation/show.html.twig");
    }
}
