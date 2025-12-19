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

/* modules/custom/zivi_spesen/templates/expense-report-form.html.twig */
class __TwigTemplate_cc5534da62c66188e149d0cd34980f51 extends Template
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
        // line 10
        yield "<div class=\"max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-sm font-sans\">
  
  ";
        // line 13
        yield "  <div class=\"flex flex-wrap gap-8 mb-8 items-end\">
    <div class=\"w-full sm:w-auto\">
      ";
        // line 15
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "header", [], "any", false, false, true, 15), "date_range_start", [], "any", false, false, true, 15), "html", null, true);
        yield "
    </div>
    <div class=\"w-full sm:w-auto\">
      ";
        // line 18
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "header", [], "any", false, false, true, 18), "date_range_end", [], "any", false, false, true, 18), "html", null, true);
        yield "
    </div>
  </div>

  ";
        // line 23
        yield "  <div class=\"hidden md:grid grid-cols-12 gap-4 mb-2 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 pb-2\">
    <div class=\"col-span-3\">Item</div>
    <div class=\"col-span-2\">Rate (CHF)</div>
    <div class=\"col-span-2\">Days / Qty</div>
    <div class=\"col-span-2\">Total (CHF)</div>
    <div class=\"col-span-2\">Receipt</div>
    <div class=\"col-span-1 text-center\">Actions</div>
  </div>

  ";
        // line 33
        yield "  <div class=\"space-y-0\">
    ";
        // line 34
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "expenses", [], "any", false, false, true, 34), "html", null, true);
        yield "
  </div>

  ";
        // line 38
        yield "  <div class=\"mt-4\">
    ";
        // line 39
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "add_row", [], "any", false, false, true, 39), "html", null, true);
        yield "
  </div>

  ";
        // line 43
        yield "  <div class=\"mt-8 flex flex-col sm:flex-row justify-end items-center gap-4 border-t border-gray-200 pt-6\">
     <label class=\"font-bold text-gray-700 text-lg\">Total Geldleistung:</label>
     <div class=\"w-full sm:w-48\">
       ";
        // line 46
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "total_sum_wrapper", [], "any", false, false, true, 46), "total_sum", [], "any", false, false, true, 46), "html", null, true);
        yield "
     </div>
  </div>

  ";
        // line 51
        yield "  <div class=\"mt-8 flex justify-end\">
    ";
        // line 52
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["form"] ?? null), "actions", [], "any", false, false, true, 52), "html", null, true);
        yield "
  </div>

  ";
        // line 56
        yield "  ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(($context["form"] ?? null), "header", "expenses", "add_row", "total_sum_wrapper", "actions"), "html", null, true);
        yield "

</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["form"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/zivi_spesen/templates/expense-report-form.html.twig";
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
        return array (  115 => 56,  109 => 52,  106 => 51,  99 => 46,  94 => 43,  88 => 39,  85 => 38,  79 => 34,  76 => 33,  65 => 23,  58 => 18,  52 => 15,  48 => 13,  44 => 10,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/zivi_spesen/templates/expense-report-form.html.twig", "/var/www/html/web/modules/custom/zivi_spesen/templates/expense-report-form.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = [];
        static $filters = ["escape" => 15, "without" => 56];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape', 'without'],
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
