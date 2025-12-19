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

/* modules/custom/zivi_spesen/templates/page--dashboard.html.twig */
class __TwigTemplate_09cbae4eca75a8bf9fa14e5c709433f2 extends Template
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

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 7
        yield "<script src=\"https://cdn.tailwindcss.com\"></script>
<style>
  body {
    background-image: none !important;
    background-color: #f9fafb !important; /* gray-50 */
  }
  #content {
    display: unset !important;
  }
  /* Fix button styles to prevent bold on hover */
  input[type=\"submit\"], button, .button, a.button {
    font-weight: 500 !important; /* font-medium */
    transition: background-color 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s;
  }
  input[type=\"submit\"]:hover, button:hover, .button:hover, a.button:hover {
    font-weight: 500 !important;
  }
</style>
<div class=\"min-h-screen bg-gray-50 font-sans\">
  <header role=\"banner\" class=\"bg-white shadow-sm sticky top-0 z-50\">
    <div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between\">
      <div class=\"text-xl font-bold text-gray-900\">Zivi Dashboard</div>
      <nav class=\"flex gap-4\">
        <a href=\"/user/logout\" class=\"text-sm font-medium text-gray-500 hover:text-gray-900\">Logout</a>
      </nav>
    </div>
  </header>

  <main role=\"main\" class=\"py-10\">
    <div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8\">
      ";
        // line 37
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 37), "html", null, true);
        yield "
    </div>
  </main>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["page"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/zivi_spesen/templates/page--dashboard.html.twig";
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
        return array (  76 => 37,  44 => 7,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/zivi_spesen/templates/page--dashboard.html.twig", "/var/www/html/web/modules/custom/zivi_spesen/templates/page--dashboard.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = [];
        static $filters = ["escape" => 37];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
