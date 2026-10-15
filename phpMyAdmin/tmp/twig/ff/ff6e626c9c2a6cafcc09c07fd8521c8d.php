<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* preferences/two_factor/confirm.twig */
class __TwigTemplate_16a49179b928d4b2c9aebfb0f0b48100 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<div class=\"row\">
  <div class=\"col\">
    <div class=\"card mt-4\">
      <div class=\"card-header\">
        ";
echo _gettext("Confirm disabling two-factor authentication");
        // line 6
        echo "      </div>
      <div class=\"card-body\">
        <form method=\"post\" action=\"";
        // line 8
        echo PhpMyAdmin\Url::getFromRoute("/preferences/two-factor");
        echo "\">
          ";
        // line 9
        echo $this->env->getFilter('notice')->getCallable()(_gettext("By disabling two factor authentication you will be again able to login using password only."));
        echo "
          ";
        // line 10
        echo PhpMyAdmin\Url::getHiddenInputs();
        echo "
          ";
        // line 11
        echo ($context["form"] ?? null);
        echo "
          <input type=\"hidden\" name=\"2fa_remove\" value=\"1\">
          <input class=\"btn btn-secondary\" type=\"submit\" value=\"";
echo _gettext("Disable two-factor authentication");
        // line 13
        echo "\">
        </form>
      </div>
    </div>
  </div>
</div>
";
    }

    public function getTemplateName()
    {
        return "preferences/two_factor/confirm.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  66 => 13,  60 => 11,  56 => 10,  52 => 9,  48 => 8,  44 => 6,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "preferences/two_factor/confirm.twig", "C:\\Users\\Tuco\\Desktop\\FitFuerInfo\\phpMyAdmin\\templates\\preferences\\two_factor\\confirm.twig");
    }
}
