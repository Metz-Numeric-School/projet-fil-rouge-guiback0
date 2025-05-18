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

        yield "Organisation";
        
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
        yield "    <h1>Organisation</h1>

    <table class=\"table\">
        <tbody>
            <tr>
                <th>Id</th>
                <td>";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 12, $this->source); })()), "id", [], "any", false, false, false, 12), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Nom_organisation</th>
                <td>";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 16, $this->source); })()), "nomOrganisation", [], "any", false, false, false, 16), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Telephone</th>
                <td>";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 20, $this->source); })()), "telephone", [], "any", false, false, false, 20), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>";
        // line 24
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 24, $this->source); })()), "email", [], "any", false, false, false, 24), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Site_web</th>
                <td>";
        // line 28
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 28, $this->source); })()), "siteWeb", [], "any", false, false, false, 28), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Date_creation</th>
                <td>";
        // line 32
        yield ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 32, $this->source); })()), "dateCreation", [], "any", false, false, false, 32)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 32, $this->source); })()), "dateCreation", [], "any", false, false, false, 32), "Y-m-d"), "html", null, true)) : (""));
        yield "</td>
            </tr>
            <tr>
                <th>Siret</th>
                <td>";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 36, $this->source); })()), "siret", [], "any", false, false, false, 36), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Ca</th>
                <td>";
        // line 40
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 40, $this->source); })()), "ca", [], "any", false, false, false, 40), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Numero_rue</th>
                <td>";
        // line 44
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 44, $this->source); })()), "numeroRue", [], "any", false, false, false, 44), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Suffix_rue</th>
                <td>";
        // line 48
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 48, $this->source); })()), "suffixRue", [], "any", false, false, false, 48), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Nom_rue</th>
                <td>";
        // line 52
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 52, $this->source); })()), "nomRue", [], "any", false, false, false, 52), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Code_postal</th>
                <td>";
        // line 56
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 56, $this->source); })()), "codePostal", [], "any", false, false, false, 56), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Ville</th>
                <td>";
        // line 60
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 60, $this->source); })()), "ville", [], "any", false, false, false, 60), "html", null, true);
        yield "</td>
            </tr>
            <tr>
                <th>Pays</th>
                <td>";
        // line 64
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 64, $this->source); })()), "pays", [], "any", false, false, false, 64), "html", null, true);
        yield "</td>
            </tr>
        </tbody>
    </table>

    <a href=\"";
        // line 69
        yield $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_index");
        yield "\">back to list</a>

    <a href=\"";
        // line 71
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_organisation_edit", ["id" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["organisation"]) || array_key_exists("organisation", $context) ? $context["organisation"] : (function () { throw new RuntimeError('Variable "organisation" does not exist.', 71, $this->source); })()), "id", [], "any", false, false, false, 71)]), "html", null, true);
        yield "\">edit</a>

    ";
        // line 73
        yield Twig\Extension\CoreExtension::include($this->env, $context, "organisation/_delete_form.html.twig");
        yield "
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
        return array (  217 => 73,  212 => 71,  207 => 69,  199 => 64,  192 => 60,  185 => 56,  178 => 52,  171 => 48,  164 => 44,  157 => 40,  150 => 36,  143 => 32,  136 => 28,  129 => 24,  122 => 20,  115 => 16,  108 => 12,  100 => 6,  87 => 5,  64 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}Organisation{% endblock %}

{% block body %}
    <h1>Organisation</h1>

    <table class=\"table\">
        <tbody>
            <tr>
                <th>Id</th>
                <td>{{ organisation.id }}</td>
            </tr>
            <tr>
                <th>Nom_organisation</th>
                <td>{{ organisation.nomOrganisation }}</td>
            </tr>
            <tr>
                <th>Telephone</th>
                <td>{{ organisation.telephone }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ organisation.email }}</td>
            </tr>
            <tr>
                <th>Site_web</th>
                <td>{{ organisation.siteWeb }}</td>
            </tr>
            <tr>
                <th>Date_creation</th>
                <td>{{ organisation.dateCreation ? organisation.dateCreation|date('Y-m-d') : '' }}</td>
            </tr>
            <tr>
                <th>Siret</th>
                <td>{{ organisation.siret }}</td>
            </tr>
            <tr>
                <th>Ca</th>
                <td>{{ organisation.ca }}</td>
            </tr>
            <tr>
                <th>Numero_rue</th>
                <td>{{ organisation.numeroRue }}</td>
            </tr>
            <tr>
                <th>Suffix_rue</th>
                <td>{{ organisation.suffixRue }}</td>
            </tr>
            <tr>
                <th>Nom_rue</th>
                <td>{{ organisation.nomRue }}</td>
            </tr>
            <tr>
                <th>Code_postal</th>
                <td>{{ organisation.codePostal }}</td>
            </tr>
            <tr>
                <th>Ville</th>
                <td>{{ organisation.ville }}</td>
            </tr>
            <tr>
                <th>Pays</th>
                <td>{{ organisation.pays }}</td>
            </tr>
        </tbody>
    </table>

    <a href=\"{{ path('app_organisation_index') }}\">back to list</a>

    <a href=\"{{ path('app_organisation_edit', {'id': organisation.id}) }}\">edit</a>

    {{ include('organisation/_delete_form.html.twig') }}
{% endblock %}
", "organisation/show.html.twig", "/Users/guiback/Desktop/OpenClassrooms - MNS/MNS/DFS/MNS - Projet - ACCESS MNS/CODE/access_mns_manager/templates/organisation/show.html.twig");
    }
}
