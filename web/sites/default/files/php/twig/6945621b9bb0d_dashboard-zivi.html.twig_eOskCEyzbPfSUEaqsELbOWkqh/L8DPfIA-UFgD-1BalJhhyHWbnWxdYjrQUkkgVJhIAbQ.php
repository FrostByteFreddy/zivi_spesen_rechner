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

/* modules/custom/zivi_spesen/templates/dashboard-zivi.html.twig */
class __TwigTemplate_4d0c649aaa1ad7769b0ec5f91cdd75cb extends Template
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
        // line 1
        yield "<div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12\">
  <div class=\"flex justify-between items-center mb-10\">
    <div>
      <h1 class=\"text-4xl font-extrabold text-gray-900 tracking-tight\">Meine Spesenabrechnungen</h1>
      <p class=\"mt-2 text-lg text-gray-600\">Verwalte deine Abrechnungen und behalte den Überblick.</p>
    </div>
    <div class=\"flex gap-4\">
      <a href=\"/dashboard/profile\" class=\"inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all\">
        <svg class=\"-ml-1 mr-2 h-5 w-5 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
          <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\" />
        </svg>
        Profil bearbeiten
      </a>
      <a href=\"/dashboard/add\" class=\"inline-flex items-center px-8 py-3 border border-transparent text-base font-bold rounded-xl shadow-lg text-white bg-blue-600 hover:bg-blue-700 transform hover:scale-105 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500\">
        <svg class=\"-ml-1 mr-2 h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
          <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4v16m8-8H4\" />
        </svg>
        Neue Abrechnung
      </a>
    </div>
  </div>

  <div class=\"bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden\">
    <table class=\"min-w-full divide-y divide-gray-200\">
      <thead class=\"bg-gray-50\">
        <tr>
          <th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Title</th>
          <th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Date Range</th>
          <th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Status</th>
          <th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Total Amount</th>
          <th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Actions</th>
        </tr>
      </thead>
      <tbody class=\"bg-white divide-y divide-gray-200\">
        ";
        // line 35
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 36
            yield "          <tr>
            <td class=\"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900\">";
            // line 37
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 37), "html", null, true);
            yield "</td>
            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500\">";
            // line 38
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "date_range", [], "any", false, false, true, 38), "html", null, true);
            yield "</td>
            <td class=\"px-6 py-4 whitespace-nowrap\">
              <span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                ";
            // line 41
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["item"], "status", [], "any", false, false, true, 41) == "Approved")) {
                yield "bg-green-100 text-green-800";
            } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["item"], "status", [], "any", false, false, true, 41) == "Submitted")) {
                yield "bg-yellow-100 text-yellow-800";
            } else {
                yield "bg-gray-100 text-gray-800";
            }
            yield "\">
                ";
            // line 42
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "status", [], "any", false, false, true, 42), "html", null, true);
            yield "
              </span>
            </td>
            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500\">CHF ";
            // line 45
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "total", [], "any", false, false, true, 45), "html", null, true);
            yield "</td>
            <td class=\"px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2\">
              ";
            // line 47
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 47), "edit", [], "any", false, false, true, 47)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 48
                yield "                <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 48), "edit", [], "any", false, false, true, 48), "url", [], "any", false, false, true, 48), "html", null, true);
                yield "\" class=\"text-indigo-600 hover:text-indigo-900\" title=\"Edit\">
                  <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\">
                    <path d=\"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z\" />
                  </svg>
                </a>
              ";
            } else {
                // line 54
                yield "                <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 54), "view", [], "any", false, false, true, 54), "url", [], "any", false, false, true, 54), "html", null, true);
                yield "\" class=\"text-gray-400 hover:text-gray-600\" title=\"View\">
                  <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\">
                    <path d=\"M10 12a2 2 0 100-4 2 2 0 000 4z\" />
                    <path fill-rule=\"evenodd\" d=\"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z\" clip-rule=\"evenodd\" />
                  </svg>
                </a>
              ";
            }
            // line 61
            yield "              ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 61), "delete", [], "any", false, false, true, 61)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 62
                yield "                <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 62), "delete", [], "any", false, false, true, 62), "url", [], "any", false, false, true, 62), "html", null, true);
                yield "\" class=\"text-red-600 hover:text-red-900\" title=\"Delete\">
                  <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\">
                    <path fill-rule=\"evenodd\" d=\"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z\" clip-rule=\"evenodd\" />
                  </svg>
                </a>
              ";
            }
            // line 68
            yield "              ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 68), "pdf", [], "any", false, false, true, 68)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 69
                yield "                <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "actions", [], "any", false, false, true, 69), "pdf", [], "any", false, false, true, 69), "url", [], "any", false, false, true, 69), "html", null, true);
                yield "\" class=\"text-gray-400 hover:text-gray-600\" target=\"_blank\" title=\"Download PDF\">
                  <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-5 w-5\" viewBox=\"0 0 20 20\" fill=\"currentColor\">
                    <path fill-rule=\"evenodd\" d=\"M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z\" clip-rule=\"evenodd\" />
                  </svg>
                </a>
              ";
            }
            // line 75
            yield "            </td>
          </tr>
        ";
            $context['_iterated'] = true;
        }
        // line 77
        if (!$context['_iterated']) {
            // line 78
            yield "          <tr>
            <td colspan=\"5\" class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center\">You haven't submitted any reports yet.</td>
          </tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['item'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 82
        yield "      </tbody>
    </table>
  </div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["items"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/zivi_spesen/templates/dashboard-zivi.html.twig";
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
        return array (  185 => 82,  176 => 78,  174 => 77,  168 => 75,  158 => 69,  155 => 68,  145 => 62,  142 => 61,  131 => 54,  121 => 48,  119 => 47,  114 => 45,  108 => 42,  98 => 41,  92 => 38,  88 => 37,  85 => 36,  80 => 35,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/zivi_spesen/templates/dashboard-zivi.html.twig", "/var/www/html/web/modules/custom/zivi_spesen/templates/dashboard-zivi.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 35, "if" => 41];
        static $filters = ["escape" => 37];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'if'],
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
