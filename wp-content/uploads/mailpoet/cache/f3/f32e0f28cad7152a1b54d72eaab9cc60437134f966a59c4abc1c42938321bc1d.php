<?php

use MailPoetVendor\Twig\Environment;
use MailPoetVendor\Twig\Error\LoaderError;
use MailPoetVendor\Twig\Error\RuntimeError;
use MailPoetVendor\Twig\Extension\SandboxExtension;
use MailPoetVendor\Twig\Markup;
use MailPoetVendor\Twig\Sandbox\SecurityError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedTagError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFilterError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFunctionError;
use MailPoetVendor\Twig\Source;
use MailPoetVendor\Twig\Template;

/* deactivationSurvey/css.html */
class __TwigTemplate_606b2d755b443c3ac527b4de6a63b4455382f725118be6f81de814dfaa67e7c7 extends \MailPoetVendor\Twig\Template
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
        echo "<style type=\"text/css\">
  .mailpoet-deactivate-survey-modal {
    display: none;
    table-layout: fixed;
    position: fixed;
    z-index: 9999;
    width: 100%;
    height: 100%;
    text-align: center;
    font-size: 14px;
    top: 0;
    left: 0;
    background: rgba(0,0,0,0.8);
  }
  .mailpoet-deactivate-survey-wrap {
    display: table-cell;
    vertical-align: middle;
  }

  .mailpoet-deactivate-survey {
    background-color: #f1f1f1;
    border: 0 solid #ccc;
    border-radius: 3px;
    margin: 0 auto;
    padding: 12px;
    width: 340px;
    direction: ltr;
  }

  .mailpoet-deactivate-survey a.button {
    white-space: normal;
    height: auto;
  }
</style>
";
    }

    public function getTemplateName()
    {
        return "deactivationSurvey/css.html";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "deactivationSurvey/css.html", "/home/hfgmgvmwda5f/public_html/v2/wp-content/plugins/mailpoet/views/deactivationSurvey/css.html");
    }
}
