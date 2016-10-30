<?php

/* layout.html.twig */
class __TwigTemplate_2d808708ca2d8eebce4a283f29c7d1ab46222f02a217adeda5348ac82fa457c0 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\">
        <title>";
        // line 5
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : $this->getContext($context, "app")), "getResources", array(), "method"), "project", array()), "name", array()), "html", null, true);
        echo "</title>
    </head>
    <body>
        Hello,
        This is your first application with ZANRA FRAMEWORK
        ";
        // line 10
        $this->displayBlock('content', $context, $blocks);
        // line 11
        echo "    </body>
</html>
";
    }

    // line 10
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  42 => 10,  36 => 11,  34 => 10,  26 => 5,  20 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "layout.html.twig", "/home/clevo/zanra/framework/tests/View/layout.html.twig");
    }
}
